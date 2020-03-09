import {Input, Output, OnChanges, OnInit, Component, EventEmitter, SimpleChanges  } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { GlobalService } from '../../services/global.service';
import { UserService } from '../../services/user.service';
import { NavService } from '../../services/nav.service';
import { NotificationComponent } from '../../components/notification/notification';
import { ReceiptComponent } from '../../components/receipt/receipt';

import { DatePipe } from '@angular/common';


declare var swal: any;

@Component({
  selector: 'booking',
  templateUrl: 'booking.html',
  styleUrls:['./booking.css']
})

export class BookingComponent implements OnChanges,OnInit {
  @Input() booking = null;
  @Input() settings = null;
  @Input() displayButtons = {};

  @Output() close: EventEmitter<any> = new EventEmitter();
  @Output() change: EventEmitter<any> = new EventEmitter();

  public newPaymentState:string = '';
  public launchAction:boolean = false;
  public formRefundStripePayment:any = {reason:'requested_by_customer', message:'', amount:0, maxAmount:0, typeAmount:'all', allPayments:[], payment:null};

  constructor(public global:GlobalService, private userService:UserService, private navService:NavService, private modalService: NgbModal) {

  }

  ngOnInit(): void {

  }

  ngOnChanges(changes: SimpleChanges) {

  }

  goCalendar(date){
    this.global.setCurrentDate(date);
    if(this.isRESAManager()) this.navService.changeRoute(this.global.filterSettings.favPage);
    else this.navService.changeRoute('');
    this.emitClose();
  }
  goCustomer(idCustomer){
    this.navService.changeRoute('customer/' + idCustomer);
    this.emitClose();
  }
  goEditBooking(){
    this.navService.changeRoute('planningBookings', {booking: this.booking.id});
    this.emitClose();
  }

  isDisplayButton(name){ return this.displayButtons[name]==null || this.displayButtons[name]; }

  isRESAManager(){
    return this.userService.getCurrentUser().role == 'administrator' || this.userService.getCurrentUser().role == 'RESA_Manager';
  }

  isDeposit(){
    return this.booking.paymentState == 'deposit';
  }

  round(value){
    return Math.round(value * 100) / 100;
  }

  getTagById(idTag){
    var tag:any = {name:'', color:''};
    if(this.settings != null && this.settings.tags != null){
      tag = this.settings.tags.find(element => element.id == idTag);
    }
    return tag;
  }

  getPlaceById(id){
    if(this.settings != null && this.settings.places != null){
      return this.settings.places.find(element => element.id == id);
    }
    return null;
  }

  getQuotationName(){
    if(this.booking==null || !this.booking.quotation) return '';
    var name = '';
    if(this.booking.quotationRequest) name += 'Demande de devis';
    else {
      name += 'Devis en attente';
      if(this.booking.quotationExpired){
        name += '(expiré)';
      }
    }
    return name;
  }

  getWaitingName(){
    if(this.booking==null || this.booking.status != 'waiting') return '';
    var name = 'En attente';
    if(this.booking.waitingSubState == 0) name = 'Non confirmée';
    else if(this.booking.waitingSubState == 1) name = 'Attente paiement';
    else if(this.booking.waitingSubState == 2) name = 'Paiement expiré';
    return name;
  }

  getParticipantsParameter(id){
    if(this.settings.form_participants_parameters != null){
      return this.settings.form_participants_parameters.find(element => element.id == id);
    }
    return null;
  }

  getParticipantFieldName(participant, field){
    if(field.type == 'select'){
      for(var i = 0; i < field.options.length; i++){
        var option = field.options[i];
        if(option.id == participant[field.varname]){
          return this.global.getTextByLocale(option.name);
        }
      }
    }
    return participant[field.varname];
  }


  isCaisseOnlineActivated(){
    return this.settings != null && this.settings.caisse_online_activated;
  }

  isStripeConnect(){
    return this.global.stripeConnect != null && this.global.stripeConnect.activated && this.booking != null && this.booking.transactionId != '' && this.booking.typePaymentChosen == 'stripeConnect';
  }

  canStripeRefound(){
    return this.isStripeConnect() && this.booking.payments != null && this.booking.payments.length > 0 &&
      this.booking.payments.findIndex(element => !element.refunded) > -1;
  }

  getIdBookingWithTicketIdBooking(idBookings){
    var result = [];
    if(idBookings != null){
      var idBookings = idBookings.split('&&');
      for(var i = 0; i < idBookings.length; i++){
        var localIdBookings = idBookings[i].split('&');
        if(localIdBookings.length == 1) result.push(localIdBookings[0]);
        else if(localIdBookings.length == 2) {
          if(localIdBookings[0].length > 0) result.push(localIdBookings[0]);
          else if(localIdBookings[1].length > 0) result.push(localIdBookings[1]);
        }
      }
    }
    return result;
  }

  getTotalPayments(booking, payments){
    var totalPaiements = 0;
    for(var i = 0; i < payments.length; i++){
      var payment = payments[i];
      if(payment.state == 'ok' || payment.state == null){
        if(payment.isReceipt == null ||
          (!payment.isReceipt && booking.paymentState == 'complete') ||
          (payment.isReceipt && (booking.paymentState == 'advancePayment' || booking.paymentState == 'deposit'))){
          totalPaiements += payment.value * 1;
        }
      }
    }
    return totalPaiements;
  }

  getCaisseOnlinePaymentForType(payment, payments, type){
    for(var i = 0; i < payments.length; i++){
      var localPayment = payments[i];
      if(localPayment.isCaisseOnline != null && localPayment.isCaisseOnline &&
        (payment.value * 1) == localPayment.value && !payment.isCaisseOnline && payment.type == type){
        return localPayment;
      }
    }
    return null;
  }

  getPaymentsForBooking(booking){
    if(this.isCaisseOnlineActivated()){
      var idsBooking = booking.linkOldBookings;
      if(idsBooking != '') idsBooking += ',';
      idsBooking += booking.id;
      var url = this.settings.caisse_online_server_url + 'ticketsByIdBooking/' + this.settings.caisse_online_license_id + '/' + idsBooking;
      this.launchAction = true;
      this.userService.getGlobal(url, (tickets) => {
        var idPaymentsList = [];
        for(var i = 0; i < booking.payments.length; i++){
          if(booking.payments[i].id != null){
            idPaymentsList.push(booking.payments[i].id);
          }
        }
        for(var i = 0; i < tickets.length; i++){
          var ticket = tickets[i];
          for(var j = 0; j < ticket.payments.length; j++){
            var payment = ticket.payments[j];
            var idPayment = 'ticket' + ticket.id + '_' + j;
            var note = '';
            var idBookings = [];
            if(ticket.idBookings != null){
              idBookings = this.getIdBookingWithTicketIdBooking(ticket.idBookings);
              note = 'MULTI-ENCAISSEMENT - réservations n°';
              for(var k = 0; k < idBookings.length; k++){
                if(k != 0) note += ', ';
                note += idBookings[k];
              }
            }
            if(idPaymentsList.indexOf(idPayment) == -1){
              idPaymentsList.push(idPayment);
              booking.payments.push({
                id:idPayment,
                isCaisseOnline:true,
                isReceipt:(ticket.type != 'ticket'),
                idBooking:booking.id,
                idBookings:idBookings,
                paymentDate:new Date(ticket.date),
                type:payment.type,
                value:payment.amount,
                name:payment.name,
                idReference:payment.externalId,
                credit:(payment.credit == 'true'),
                vendor:ticket.settings.vendor,
                note:note
              });
            }
          }
        }
        var newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, booking.payments);
        if(newNeedToPay < 0){
          var newPayments = [];
          for(var i = 0; i < booking.payments.length; i++){
            var payment = booking.payments[i];
            if(payment.isCaisseOnline || payment.type != 'systempay' || payment.type != 'paypal' || payment.type != 'monetico' || payment.type != 'stripe' || payment.type != 'stripeConnect' || payment.type != 'paybox'){
              newPayments.push(payment);
            }
            else if(payment.type == 'systempay' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'systempay') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'paypal' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paypal') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'monetico' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'monetico') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'stripe' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripe') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'stripeConnect' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripeConnect') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'paybox' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paybox') == null){
              newPayments.push(payment);
            }
          }
          newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, newPayments);
        }
        booking.needToPay = newNeedToPay;
        booking.paymentsCaisseOnlineDone = true;
        this.launchAction = false;
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de récupérer les données de la caisse';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'})
      });
    }
  }

  getReason(reason){
    if(reason == 'requested_by_customer') return 'Annulation';
    else if(reason == 'duplicate') return 'Duplication';
    return 'Fraude';
  }


  getStripeChargeForTransactionId(booking){
    if(this.isStripeConnect()){
      var idPaymentsList = [];
      if(booking.payments == null) booking.payments = [];
      for(var i = 0; i < booking.payments.length; i++){
        if(booking.payments[i].id != null){
          idPaymentsList.push(booking.payments[i].id);
        }
      }
      var url = this.global.stripeConnect.apiStripeConnectUrl + 'getCharges.php';
      this.launchAction = true;
      this.userService.postGlobal(url, {
        transactionId:booking.transactionId,
        stripeConnectId:this.global.stripeConnect.stripeConnectId
      }, (charges) => {
        for(var i = 0; i < charges.length; i++){
          var charge = charges[i];
          for(var j = 0; j < charge.length; j++){
            var payment = charge[j];
            var idPayment = payment.id;
            var idBookings = [];
            if(idPaymentsList.indexOf(idPayment) == -1){
              idPaymentsList.push(idPayment);
              booking.payments.push({
                id:idPayment,
                payment_intent:payment.payment_intent,
                isStripeConnect:true,
                isReceipt:booking.paymentState == 'advancePayment',
                idBooking:payment.metadata?payment.metadata.idBooking:-1,
                paymentDate:new Date(payment.created * 1000),
                type:'stripeConnect',
                value:payment.amount/100,
                name:'TEST',
                idReference:payment.id,
                credit:false,
                vendor:'Stripe',
                refunded:payment.refunded,
                isRefund:false,
                note:'',
                receipt_url:payment.receipt_url
              });
              if(payment.refunds.data.length > 0){
                for(var k = 0; k < payment.refunds.data.length; k++){
                  var refund = payment.refunds.data[k];
                  booking.payments.push({
                    id:idPayment + '_r' + k,
                    payment_intent:payment.payment_intent + '_r' + k,
                    isStripeConnect:true,
                    isReceipt:booking.paymentState == 'advancePayment',
                    idBooking:payment.metadata?payment.metadata.idBooking:-1,
                    paymentDate:new Date(refund.created * 1000),
                    type:'stripeConnect',
                    value:(refund.amount/100) * -1,
                    name:'Stripe',
                    idReference:payment.id,
                    credit:false,
                    vendor:'Stripe',
                    refunded:true,
                    isRefund:true,
                    note:this.getReason(refund.reason),
                    receipt_url:payment.receipt_url
                  });
                }
              }
            }
          }
        }
        booking.payments.sort((payment1, payment2) => {
          return payment1.paymentDate.getTime() - payment2.paymentDate.getTime();
        });
        var newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, booking.payments);
        if(newNeedToPay < 0){
          var newPayments = [];
          for(var i = 0; i < booking.payments.length; i++){
            var payment = booking.payments[i];
            if(payment.isCaisseOnline || payment.type != 'systempay' || payment.type != 'paypal' || payment.type != 'monetico' || payment.type != 'stripe' || payment.type != 'stripeConnect' || payment.type != 'paybox'){
              newPayments.push(payment);
            }
            else if(payment.type == 'systempay' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'systempay') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'paypal' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paypal') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'monetico' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'monetico') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'stripe' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripe') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'stripeConnect' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripeConnect') == null){
              newPayments.push(payment);
            }
            else if(payment.type == 'paybox' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paybox') == null){
              newPayments.push(payment);
            }
          }
          newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, newPayments);
        }
        booking.needToPay = newNeedToPay;
        booking.paymentsCaisseOnlineDone = true;
        this.launchAction = false;
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de récupérer les données de stripe';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'})
      });
    }
  }

  openNotification(){
    const modalRef = this.modalService.open(NotificationComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setBooking(this.booking);
    modalRef.componentInstance.setCustomer(this.booking.customer);
  }

  openAskPayment(){
    const modalRef = this.modalService.open(NotificationComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.setBooking(this.booking);
    modalRef.componentInstance.setCustomer(this.booking.customer);
    modalRef.componentInstance.typeEmailContent = 'askPayment';
    if(modalRef.componentInstance.settings != null){
      modalRef.componentInstance.displayAskPaymentEmailContent();
    }
  }

  openReceipt(){
    const modalRef = this.modalService.open(ReceiptComponent, { windowClass: 'mlg' });
    modalRef.componentInstance.loadBookingReceipt(this.booking);
  }

  reloadBooking(){
    this.launchAction = true;
    this.userService.get('booking/:token/' + this.booking.id, (data) => {
      this.launchAction = false;
      this.booking = data;
      this.emitChange();
      swal({ title: 'OK', text: '', icon: 'success'})
    }, (error) => {
      this.launchAction = false;
      var text = 'Impossible de recharger les données';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  emitClose(){ this.close.emit(this.booking); }
  emitChange(){ this.change.emit(this.booking); }

  payBookingCaisseOnline(booking){
    if(!this.launchAction){
      if(booking.paymentState != 'complete'){
        this.payBookingCaisseOnlineAction(booking);
      }
      else {
        swal({
          title: 'La réservation est déjà encaissée ?',
          text: 'La réservation est déjà encaissée, voulez-vous vraiment la pousser sur Caisse Online ?',
          icon: 'warning',
          buttons: ['Annuler', 'Oui'],
          dangerMode: true,
        })
        .then((ok) => {
          if (ok) {
            this.payBookingCaisseOnlineAction(booking);
          }
        });
      }
    }
  }

  getCaisseOnlineLink(){
    return this.settings.caisse_online_site_url + '/login/' + this.settings.caisse_online_license_id;
  }

  payBookingCaisseOnlineAction(booking){
    this.launchAction = true;
    this.userService.put('pushBookingInCaisseOnline/:token', {idBooking: booking.id}, (data) => {
      this.launchAction = false;
      swal({
        title: 'Aller sur la caisse ?',
        text: 'Voulez-vous ouvrir un nouvel onglet vers Caisse Online ?',
        icon: 'success',
        buttons: ['Annuler', 'Ouvrir'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          window.open(this.getCaisseOnlineLink(), '_blank');
        }
      });
    }, (error) => {
      this.launchAction = false;
      var text = 'Impossible de pousser la réservation sur la caisse';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }


  getPaymentName(idPayment, name){
    var customerType = false;
    if(idPayment.indexOf('customer_') != -1) {
      idPayment = idPayment.substring('customer_'.length);
      customerType = true;
    }

    var result = this.settings.paymentsTypeList[idPayment];
    if(result == null){
      for(var i = 0; i < this.settings.paymentsTypeList.length; i++){
        var paymentType = this.settings.paymentsTypeList[i];
        if(paymentType.id == idPayment){
          result = paymentType.title;
        }
      }
      if(result == null && this.settings.idPaymentsTypeToName[idPayment]){
        result = this.settings.idPaymentsTypeToName[idPayment];
      }
      if(result == null){
        for(var i = 0; i < this.settings.custom_payment_types.length; i++){
          var paymentType = this.settings.custom_payment_types[i];
          if(paymentType.id == idPayment){
            result = paymentType.label;
          }
        }
      }
      if(result == null && name != null){
        result = name;
      }
      if(result == null){
        result = idPayment;
      }
    }
    if(customerType) result = this.getPaymentName('customer', 'Compte client') + '('+ result +')';
    return result;
  }

  openPaymentStateDialog(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result){
        this.changePaymentStateBooking();
      }
    }, (reason) => {

    });
  }


  isOkPaymentStatus(){
    return this.newPaymentState != null && this.newPaymentState != this.booking.paymentState && !this.launchAction;
  }

  changePaymentStateBooking(){
    this.launchAction = true;
    this.userService.post('changePaymentState/:token', {idBooking:this.booking.id, newPaymentState:this.newPaymentState}, (data) => {
      this.launchAction = false;
      this.booking = data.booking;
      this.emitChange();
      swal({ title: 'OK', text: '', icon: 'success'})
    }, (error) => {
      this.launchAction = false;
      var text = 'Impossible de pousser la réservation sur la caisse';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  calculateBookingPaymentState(){
    if(!this.launchAction && this.isCaisseOnlineActivated()){
      this.launchAction = true;
      this.userService.post('calculateBookingPaymentState/:token', {idBooking:this.booking.id}, (data) => {
        this.launchAction = false;
        this.booking = data.booking;
        this.emitChange();
        swal({ title: 'OK', text: '', icon: 'success'});
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de pousser la réservation sur la caisse';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

  /**
   *
   */
  isValidRefundStripePayment(){
    return (this.formRefundStripePayment.reason.length > 0 || (this.formRefundStripePayment.reason === 'other' && this.formRefundStripePayment.message.length > 0)) && this.formRefundStripePayment.amount > 0 && this.formRefundStripePayment.amount <= this.formRefundStripePayment.maxAmount && this.formRefundStripePayment.payment !=null && !this.launchAction;
  }

  confirmRefund(close){
    if(this.isValidRefundStripePayment()){
      swal({
        title: 'Rembourser ?',
        text: 'Voulez-vous vraiment rembourser ce paiement fait avec stripe ?',
        icon: 'warning',
        buttons: ['Annuler', 'Oui'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          close();
        }
      });
    }
  }

  /**
   *
   */
  openRefundStripePayment(content){
    if(this.canStripeRefound()){
      this.formRefundStripePayment = {reason:'requested_by_customer', message:'', amount:0, maxAmount:0, typeAmount:'all', allPayments:[], payment:null};
      for(var i = 0; i < this.booking.payments.length; i++){
        var payment = this.booking.payments[i];
        if(!payment.refunded){
          var amountRefundable = 0;
          this.booking.payments.forEach(element => {
            if(element.id.indexOf(payment.id) > -1){
              amountRefundable += element.value;
            }
          });
          payment.amountRefundable = amountRefundable;
          this.formRefundStripePayment.allPayments.push(payment);
        }
      }
      if(this.formRefundStripePayment.allPayments.length == 1){
        this.selectPaymentToRefund(this.formRefundStripePayment.allPayments[0]);
      }
      this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
        if(this.isValidRefundStripePayment()){
          this.refundPayment(this.formRefundStripePayment.payment.idBooking, this.formRefundStripePayment.payment.id, ((this.formRefundStripePayment.reason!=='other')?this.formRefundStripePayment.reason:this.formRefundStripePayment.message), this.formRefundStripePayment.amount);
        }
      }, (reason) => {

      });
    }
  }

  selectPaymentToRefund(payment){
    this.formRefundStripePayment.maxAmount = payment.amountRefundable;
    this.formRefundStripePayment.amount = payment.amountRefundable;
    this.formRefundStripePayment.payment = payment;
  }

  /**
   *
   */
  refundPayment(idBooking, chargeId, reason, amount){
    if(!this.launchAction){
      this.launchAction = true;
      this.userService.post('stripeConnect/refundPayment/:token', {
        idBooking:idBooking,
        chargeId:chargeId,
        reason:reason,
        amount:amount
      }, (data) => {
        this.launchAction = false;
        this.booking.paymentState = data.paymentState;
        this.booking.idPaymentState = data.idPaymentState;
        this.emitChange();
        this.booking.payments = [];
        this.getStripeChargeForTransactionId(this.booking);
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de rembourser ce paiement stripe';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }
}
