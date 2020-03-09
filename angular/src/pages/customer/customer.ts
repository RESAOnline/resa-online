import { Component, OnInit, OnDestroy  } from '@angular/core';
import { NgModel } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

import { Subject } from 'rxjs';
import { debounceTime  } from 'rxjs/operators';
import { DatePipe, formatDate } from '@angular/common';

import { NavService } from '../../services/nav.service';
import { UserService } from '../../services/user.service';
import { GlobalService } from '../../services/global.service';
import { ComponentCanDeactivate } from '../../others/ComponentCanDeactivate';
import { NotificationComponent } from '../../components/notification/notification';

declare var swal: any;

@Component({
  selector: 'page-customer',
  templateUrl: 'customer.html',
  styleUrls:['./customer.css']
})

export class CustomerComponent extends ComponentCanDeactivate implements OnInit, OnDestroy {

  public settings = null;
  public customer = null;
  public bookings = [];
  public modelCustomer = null;
  public caisseOnlinePayments = null;
  public stripeConnectPayments = null;

  public bookingsOrderBy = 0;
  public historic = [];
  public payments = [];
  public logNotifications = [];
  public emailsCustomer = [];

  public emailCustomerDisplayed = null;

  public page:number = 0;
  public balance:any = null;

  public launchAction:boolean = false;
  public editionMode:boolean = false;
  public lastIdLogNotifications = 0;
  public lastIdEmailCustomer = 0;
  public lastSetInterval = null;

  public formRefundStripePayment = {reason:'requested_by_customer', message:'', amount:0, maxAmount:0, typeAmount:'all'};

  constructor(private userService:UserService, private navService:NavService, private global:GlobalService, private route: ActivatedRoute, private modalService: NgbModal) {
    super('Client');
    this.modalService.dismissAll();
  }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      var idCustomer = params['id'];
      if(idCustomer != ''){
        this.launchAction = true;
        this.userService.get('customerData/:token/' + idCustomer, function(data){
          this.launchAction = false;
          this.settings = data.settings;
          this.customer = data.customer;
          this.bookings = data.customer.bookings;
          this.logNotifications = data.logNotifications;
          this.emailsCustomer = data.emailsCustomer;
          this.clearModelCustomer();
          this.ordinatesBookings();
          this.getAnotherPayments();
          this.getStripeChargeForTransactionId();
          this.generateAllPaymentsAndAskPayments();
          clearInterval(this.lastSetInterval);
          this.lastSetInterval = setInterval(() => { this.updateBackend(); }, 15000);
        }.bind(this), function(error){
          this.launchAction = false;
          var text = 'Impossible de récupérer les données du client';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        }.bind(this));
      }
      else {
        this.goCustomers();
      }
    });
  }

  ngOnDestroy(): void {
    clearInterval(this.lastSetInterval);
  }

  canDeactivate(){ return true; }
  goCustomers(){ this.navService.changeRoute('customers'); }
  goAddBooking(){ this.navService.changeRoute('planningBookings', {customer:this.customer.ID}); }
  goEditBooking(booking){ this.navService.changeRoute('planningBookings', {booking:booking.id}); }
  scrollTo(id) {
     var el = document.getElementById(id);
     el.scrollIntoView();
  }

  getCurrentUser(){
    return this.userService.getCurrentUser();
  }

  isSeeSettings(){
    return this.getCurrentUser().seeSettings || this.getCurrentUser().role == 'administrator';
  }

  isRESAManager(customer){
    return this.customer.role == 'RESA_Manager';
  }

  clearModelCustomer(){
    this.customer.privateNotes = this.customer.privateNotes.replace(new RegExp('&lt;br /&gt;', 'g'),'<br />');
    this.modelCustomer = JSON.parse(JSON.stringify(this.customer));
    this.modelCustomer.company = this.global.htmlSpecialDecode(this.customer.company);
    this.modelCustomer.privateNotes = this.global.htmlSpecialDecode(this.customer.privateNotes);
    this.modelCustomer.address = this.global.htmlSpecialDecode(this.customer.address);
    this.modelCustomer.town = this.global.htmlSpecialDecode(this.customer.town);
    this.modelCustomer.createWpAccount = this.modelCustomer.isWpUser;
    this.modelCustomer.notify = true;
  }

  getTypeAccountName(customer){
    var typeAccountName = '';
    if(customer != null && this.settings != null){
      if(customer.role == 'administrator') typeAccountName = 'Administrateur';
      else if(customer.role == 'RESA_Manager') typeAccountName = 'Manageur RESA';
      else if(customer.role == 'RESA_Staff') typeAccountName = this.global.getTextByLocale(this.settings.staff_word_single);
      else {
        var typeAccount = this.settings.typesAccounts.find(element => {
          if(element.id == customer.typeAccount) return element;
        });
        if(typeAccount){
          typeAccountName = this.global.getTextByLocale(typeAccount.name);
        }
      }
    }
    return typeAccountName;
  }

  isSynchronizedCaisseOnline(){
    return this.settings!=null && this.settings.caisse_online_activated && this.customer!=null && this.customer.idCaisseOnline!=null && this.customer.idCaisseOnline != '';
  }

  isDeletable(){
    return this.customer!=null && (this.customer.role == 'RESA_Customer' || this.customer.role == '' || this.customer.askAccount) && this.bookings.length == 0;
  }

  isOkCustomer(){
    return this.modelCustomer!=null && ((!this.modelCustomer.createWpAccount && !this.modelCustomer.isWpUser && this.modelCustomer.phone != null && this.modelCustomer.phone.length > 0) || ((this.modelCustomer.createWpAccount || this.modelCustomer.isWpUser) && this.modelCustomer.email != null && this.modelCustomer.email.length > 0)) && !this.launchAction;
  }

  modifyCustomer(){
    this.launchAction = true;
    this.modelCustomer.privateNotes = this.modelCustomer.privateNotes.replace(new RegExp('\n', 'g'),'<br />');
    this.modelCustomer.privateNotes = this.modelCustomer.privateNotes.replace(new RegExp('\r', 'g'),'');
    this.editionMode = false;
    this.userService.post('customer/:token', {customer:JSON.stringify(this.modelCustomer)}, function(data){
      this.customer = data.customer;
      this.clearModelCustomer();
      this.launchAction = false;
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de sauvegarder les modification';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  acceptAskAccount(){
    this.launchAction = true;
    this.editionMode = false;
    this.userService.post('acceptAskAccount/:token', {idCustomer:this.customer.ID}, function(data){
      this.customer = data;
      this.clearModelCustomer();
      this.launchAction = false;
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible d\'accepter la demande';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  deleteCustomerAction(){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Êtes-vous sûr de supprimer ce client ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        this.launchAction = true;
        this.userService.delete('customer/:token/' + this.customer.ID, () => {
          this.launchAction = false;
          this.goCustomers();
        }, (error) => {
          this.launchAction = false;
          var text = 'Impossible de supprimer le client';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        });
      }
    });
  }

  openNotificationDialog(content, booking, type){
    const modalRef = this.modalService.open(NotificationComponent, { windowClass: 'mlg' });
    if(booking != null) modalRef.componentInstance.setBooking(booking);
    modalRef.componentInstance.setCustomer(this.customer);
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

  getAnotherPayments(){
    this.caisseOnlinePayments = null;
    if(!this.launchAction && this.isSynchronizedCaisseOnline()){
      this.launchAction = true;
      var url = this.settings.caisse_online_server_url + 'ticketsByIdCustomer/' + this.settings.caisse_online_license_id + '/' + this.customer.ID;
      this.userService.getGlobal(url, (tickets) => {
        this.launchAction = false;
        var payments = [];
        for(var i = 0; i < tickets.length; i++){
          var ticket = tickets[i];
          for(var j = 0; j < ticket.payments.length; j++){
            var payment = ticket.payments[j];
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
            payments.push({
              id:'ticket' + ticket.id+ '_' + j,
              isCaisseOnline:true,
              isReceipt:(ticket.type != 'ticket'),
              idBooking:ticket.idBooking,
              idBookings:this.getIdBookingWithTicketIdBooking(ticket.idBookings),
              paymentDate:new Date(ticket.date),
              type:payment.type,
              value:payment.amount,
              name:payment.name,
              idReference:payment.externalId!=null?payment.externalId:'',
              credit:(payment.credit == 'true'),
              vendor:ticket.settings.vendor,
              note:note
            });
          }
        }
        this.caisseOnlinePayments = payments;
        this.generateAllPaymentsAndAskPayments();
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
    else if(reason == 'fraudulent') return 'Fraude';
    return reason;
  }

  getStripeChargeForTransactionId(){
    this.stripeConnectPayments = null;
    if(this.global.stripeConnect.activated){
      var transactionId = '';
      for(var i = 0; i < this.bookings.length; i++){
        var booking = this.bookings[i];
        if(booking.transactionId.length > 0 && booking.typePaymentChosen == 'stripeConnect'){
          if(transactionId != '') transactionId += ','
          transactionId += booking.transactionId;
        }
      }
      if(transactionId.length > 0){
        var url = this.global.stripeConnect.apiStripeConnectUrl + 'getCharges.php';
        var payments = [];
        this.launchAction = true;
        this.userService.postGlobal(url, {
          transactionId:transactionId,
          stripeConnectId:this.global.stripeConnect.stripeConnectId
        }, (charges) => {
          for(var i = 0; i < charges.length; i++){
            var charge = charges[i];
            for(var j = 0; j < charge.length; j++){
              var payment = charge[j];
              payments.push({
                id:payment.id,
                payment_intent:payment.payment_intent,
                isStripeConnect:true,
                isReceipt:booking.paymentState == 'advancePayment',
                isCancellable:!payment.refunded,
                idBooking:payment.metadata?payment.metadata.idBooking:-1,
                paymentDate:new Date(payment.created * 1000),
                type:'stripeConnect',
                value:payment.amount/100,
                name:'Stripe',
                idReference:payment.id,
                credit:false,
                vendor:'Stripe',
                note:'',
                receipt_url:payment.receipt_url
              });
              if(payment.refunds.data.length > 0){
                for(var k = 0; k < payment.refunds.data.length; k++){
                  var refund = payment.refunds.data[k];
                  payments.push({
                    id:payment.id + '_r' + k,
                    payment_intent:payment.payment_intent + '_r' + k,
                    isStripeConnect:true,
                    isReceipt:booking.paymentState == 'advancePayment',
                    isCancellable:false,
                    idBooking:payment.metadata?payment.metadata.idBooking:-1,
                    paymentDate:new Date(refund.created * 1000),
                    type:'stripeConnect',
                    value:(refund.amount/100) * -1,
                    name:'TEST',
                    idReference:payment.id,
                    credit:false,
                    vendor:'Stripe',
                    note:this.getReason(refund.reason),
                    receipt_url:payment.receipt_url
                  });
                }
              }
            }
          }
          this.stripeConnectPayments = payments;
          this.generateAllPaymentsAndAskPayments();
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
  }


  forceProcessEmails(){
    this.launchAction = true;
    this.userService.post('forceProcessEmails/:token', {}, function(data){
      swal({ title: 'OK', text: '', icon: 'success'});
      this.launchAction = false;
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de sauvegarder les modification';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  generateAllPaymentsAndAskPayments(){
    var payments = [];
    var alreadyIdPayments = [];
    for(var i = 0; i < this.bookings.length; i++){
      var booking = this.bookings[i];
      if(booking.typePaymentChosen == 'swikly' && booking.transactionId != ''){
        var payment:any = {
          id:booking.id + '_' + booking.transactionId,
          idBooking:-1,
          idBookingCreation:-1,
          paymentDate:new Date(booking.creationDate),
          type:'swikly',
          value:'1',
          repayment:false,
          note:'Dépot avec Swikly',
          state:'ok',
          isReceipt:true,
          idReference:booking.transactionId
        }
        if(alreadyIdPayments.indexOf(payment.id) == -1){
          payment.idBooking = this.getLastBookingId(payment.idBooking);
          payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
          payments.push(payment);
          if(payment.id != null) alreadyIdPayments.push(payment.id);
          payments[payments.length - 1].isAskPayment = false;
        }
      }
      for(var j = 0; j < booking.payments.length; j++){
        var payment = booking.payments[j];
        if(alreadyIdPayments.indexOf(payment.id) == -1){
          payment.idBooking = this.getLastBookingId(payment.idBooking);
          payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
          payments.push(payment);
          if(payment.id != null) alreadyIdPayments.push(payment.id);
          payments[payments.length - 1].isAskPayment = false;
        }
      }
      for(var j = 0; j < booking.askPayments.length; j++){
        var askPayment = booking.askPayments[j];
        askPayment.idBooking = this.getLastBookingId(askPayment.idBooking);
        askPayment.idBookingCreation = this.getIdCreationBooking(askPayment.idBooking);
        payments.push(askPayment);
        payments[payments.length - 1].paymentDate = payments[payments.length - 1].date;
        payments[payments.length - 1].isAskPayment = true;
      }
    }
    for(var i = 0; i < this.customer.payments.length; i++){
      var payment = this.customer.payments[i];
      payment.idBooking = this.getLastBookingId(payment.idBooking);
      payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
      payments.push(payment);
      payments[payments.length - 1].isAskPayment = false;
    }
    if(this.caisseOnlinePayments != null){
      for(var i = 0; i < this.caisseOnlinePayments.length; i++){
        var payment = this.caisseOnlinePayments[i];
        if(alreadyIdPayments.indexOf(payment.id) == -1){
          payment.idBooking = this.getLastBookingId(payment.idBooking);
          payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
          payments.push(payment);
          alreadyIdPayments.push(payment.id);
          payments[payments.length - 1].isAskPayment = false;
        }
      }
    }
    if(this.stripeConnectPayments != null){
      for(var i = 0; i < this.stripeConnectPayments.length; i++){
        var payment = this.stripeConnectPayments[i];
        if(alreadyIdPayments.indexOf(payment.id) == -1){
          payment.idBooking = this.getLastBookingId(payment.idBooking);
          payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
          payments.push(payment);
          alreadyIdPayments.push(payment.id);
          payments[payments.length - 1].isAskPayment = false;
        }
      }
    }
    this.payments = payments;
    this.balance = this.getBalance();
    this.generateHistorics();
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

  getCaisseOnlineBalance(){
    var totalPaiementsCaisse = 0;
    if(false /*this.caisseOnlinePayments != null*/){
      for(var i = 0; i < this.caisseOnlinePayments.length; i++){
        var payment = this.caisseOnlinePayments[i];
        if(payment.isReceipt){
          totalPaiementsCaisse += payment.value * 1;
        }
        else if(payment.credit != null && payment.credit) {
          totalPaiementsCaisse -= payment.value * 1;
        }
      }
      for(var i = 0; i < this.stripeConnectPayments.length; i++){
        var payment = this.stripeConnectPayments[i];
        if(payment.isReceipt){
          totalPaiementsCaisse += payment.value * 1;
        }
        else if(payment.credit != null && payment.credit) {
          totalPaiementsCaisse -= payment.value * 1;
        }
      }
    }
    return totalPaiementsCaisse;
  }

  getBalance(){
    var balance = this.calculateBalance(this.payments);
    if(balance <= 0) return balance;
    else {
      var newPayments = [];
      for(var i = 0; i < this.payments.length; i++){
        var payment = this.payments[i];
        if(payment.isCaisseOnline || payment.type != 'systempay' || payment.type != 'paypal' || payment.type != 'monetico' || payment.type != 'stripe' || payment.type != 'stripeConnect' || payment.type != 'paybox'){
          newPayments.push(payment);
        }
        else if(payment.type == 'systempay' && this.getCaisseOnlinePaymentForType(payment, this.payments, 'systempay') == null){
          newPayments.push(payment);
        }
        else if(payment.type == 'paypal' && this.getCaisseOnlinePaymentForType(payment, this.payments, 'paypal') == null){
          newPayments.push(payment);
        }
        else if(payment.type == 'monetico' && this.getCaisseOnlinePaymentForType(payment, this.payments, 'monetico') == null){
          newPayments.push(payment);
        }
        else if(payment.type == 'stripe' && this.getCaisseOnlinePaymentForType(payment, this.payments, 'stripe') == null){
          newPayments.push(payment);
        }
        else if(payment.type == 'stripeConnect' && this.getCaisseOnlinePaymentForType(payment, this.payments, 'stripeConnect') == null){
          newPayments.push(payment);
        }
        else if(payment.type == 'paybox' && this.getCaisseOnlinePaymentForType(payment, this.payments, 'paybox') == null){
          newPayments.push(payment);
        }
      }
      return this.calculateBalance(newPayments);
    }
  }

  calculateBalance(payments){
    var totalPaiements = 0;
    for(var i = 0; i < payments.length; i++){
      var payment = payments[i];
      if(!payment.isCaisseOnline  && !payment.isAskPayment && (payment.state == 'ok' || payment.state == null)){
        var booking = this.getBookingById(payment.idBooking);
        if(booking != null && booking.status != 'cancelled' && booking.status != 'abandonned' && booking.paymentState != 'complete'){
          totalPaiements += payment.value * 1;
        }
        else if(booking != null && (booking.status == 'cancelled' || booking.status == 'abandonned') && booking.paymentState != 'noPayment'){
          totalPaiements += payment.value * 1;
        }
      }
    }
    var totalPaiementsCaisse = 0;
    if(this.caisseOnlinePayments != null){
      for(var i = 0; i < this.caisseOnlinePayments.length; i++){
        var payment = this.caisseOnlinePayments[i];
        if(payment.isReceipt){
          totalPaiementsCaisse += payment.value * 1;
        }
        else if(payment.credit != null && payment.credit) {
          totalPaiementsCaisse -= payment.value * 1;
        }
      }
    }
    var totalPaiementsStripe = 0;
    if(this.stripeConnectPayments != null){
      for(var i = 0; i < this.stripeConnectPayments.length; i++){
        var payment = this.stripeConnectPayments[i];
        if(payment.isReceipt){
          totalPaiementsCaisse += payment.value * 1;
        }
        else if(payment.credit != null && payment.credit) {
          totalPaiementsCaisse -= payment.value * 1;
        }
      }
    }
    var totalPrice = 0;
    if(this.bookings != null){
      for(var i = 0; i < this.bookings.length; i++){
        var booking = this.bookings[i];
        if(booking != null && booking.status != 'cancelled' && booking.status != 'abandonned' && booking.paymentState != 'complete'){
          totalPrice += booking.totalPrice;
        }
        else if(booking != null && (booking.status == 'cancelled' || booking.status == 'abandonned') && booking.paymentState != 'noPayment'){
          totalPrice += booking.totalPrice;
        }
      }
    }
    return (totalPaiements + totalPaiementsCaisse + totalPaiementsStripe) - totalPrice;
  }

  isDisplayed(line){
    if(line.state == 'pending' && this.global.wpDebugMode) return false;
    if(this.page == 0) return true;
    else if(this.page == 1 && line.isPayment) return true;
    else if(this.page == 2 && line.isLogNotification) return true;
    else if(this.page == 3 && line.isEmailCustomer) return true;
    return false;
  }


  generateHistorics(){
    this.historic = [];
    for(var i = 0; i < this.payments.length; i++){
      var payment = this.payments[i];
      payment.isPayment = true;
      this.historic.push(payment);
    }
    for(var i = 0; i < this.logNotifications.length; i++){
      var logNotification = this.logNotifications[i];
      logNotification.isLogNotification = true;
      this.historic.push(logNotification);
      this.lastIdLogNotifications = Math.max(this.lastIdLogNotifications, logNotification.id);
    }
    for(var i = 0; i < this.emailsCustomer.length; i++){
      var emailCustomer = this.emailsCustomer[i];
      emailCustomer.isEmailCustomer = true;
      emailCustomer.idBooking = this.getIdCreationBooking(emailCustomer.idBooking);
      this.historic.push(emailCustomer);
      this.lastIdEmailCustomer = Math.max(this.lastIdEmailCustomer, emailCustomer.id);
    }
    this.historic.sort(function(lineA, lineB){
      var dateA = null;
      if(lineA.isPayment) dateA = new Date(lineA.paymentDate);
      if(lineA.isLogNotification) dateA = new Date(lineA.creationDate);
      if(lineA.isEmailCustomer) dateA = new Date(lineA.creationDate);
      var dateB = null;
      if(lineB.isPayment) dateB = new Date(lineB.paymentDate);
      if(lineB.isLogNotification) dateB = new Date(lineB.creationDate);
      if(lineB.isEmailCustomer) dateB = new Date(lineB.creationDate);
      return dateB.getTime() - dateA.getTime();
    });
  }

  isAskPaymentState(payment){
    if(payment == null || (payment.isAskPayment != null && !payment.isAskPayment)) return '';
    if((new Date(payment.expiredDate)).getTime() < new Date().getTime()) return '<span class="text-danger">Demande expiré !</span>';
    else return '<span class="text-success">Demande en cours...</span>';
  }

  getBookingById(idBooking){
    for(var i = 0; i < this.bookings.length; i++){
      var booking = this.bookings[i];
      if(booking.id == idBooking || booking.idCreation == idBooking){
        return booking;
      }
    }
    return null;
  }

  getBookingIdCreationOrId(idBooking){
    for(var i = 0; i < this.bookings.length; i++){
      var booking = this.bookings[i];
      if(booking.id == idBooking || booking.idCreation == idBooking){
        return booking;
      }
    }
    return null;
  }

  getLastBookingId(idBooking){
    for(var i = 0; i < this.bookings.length; i++){
      var booking = this.bookings[i];
      var arrayOldBookings = booking.linkOldBookings.split(',');
      if(booking.id == idBooking || arrayOldBookings.indexOf(idBooking) != -1){
        return booking.id;
      }
    }
    return -1;
  }

  getIdCreationBooking(idBooking){
    for(var i = 0; i < this.bookings.length; i++){
      var booking = this.bookings[i];
      var arrayOldBookings = booking.linkOldBookings.split(',');
      if(booking.id == idBooking || arrayOldBookings.indexOf(idBooking) != -1){
        if(booking.idCreation > -1) return booking.idCreation;
        return booking.id;
      }
    }
    return -1;
  }

  getAllPaymentName(idPayments){
    var result = '';
    var allPaymentName = idPayments.split(',');
    for(var i = 0; i < allPaymentName.length; i++){
      if(i != 0) result += ', ';
      result += this.getPaymentName(allPaymentName[i], '');
    }
    return result;
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

  isSynchronizedEmails(){
    return this.settings!=null && this.settings.mailbox_activated;
  }

  openEmailCustomer(content, line){
    this.emailCustomerDisplayed = line;
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {

    }, (reason) => {
    });
  }

  cancelPaymentAction(line){
    alert('Veuillez nous contacter pour pouvoir effectuer cette action !');
  }

  deleteEmailCustomerAction(id){
    swal({
      title: 'Êtes-vous sûr ?',
      text: 'Êtes-vous sûr de supprimer cet email ?',
      icon: 'warning',
      buttons: ['Annuler', 'Supprimer'],
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        this.launchAction = true;
        this.userService.delete('deleteEmailCustomer/:token/' + id, () => {
          var index = this.emailsCustomer.findIndex(element => element.id == id);
          if(index > -1){
            this.emailsCustomer.splice(index, 1);
          }
          this.generateHistorics();
          this.launchAction = false;
          swal({ title: 'OK', text: '', icon: 'success'});
        }, (error) => {
          this.launchAction = false;
          var text = 'Impossible de supprimer le client';
          if(error != null && error.message != null && error.message.length > 0){
            text += ' (' + error.message + ')';
          }
          swal({ title: 'Erreur', text: text, icon: 'error'});
        });
      }
    });
  }


  ordinatesBookings(){
    this.bookings.sort((b1, b2) => {
      var b1Date = new Date(b1.creationDate);
      var b2Date = new Date(b2.creationDate);
      if(this.bookingsOrderBy >= 2){
        b1Date = new Date(b1.startDate);
        b2Date = new Date(b2.startDate);
      }
      if(this.bookingsOrderBy == 0) return b2Date.getTime() - b1Date.getTime();
      if(this.bookingsOrderBy == 1) return b1Date.getTime() - b2Date.getTime();
      if(this.bookingsOrderBy == 2) return b2Date.getTime() - b1Date.getTime();
      return b1Date.getTime() - b2Date.getTime();
    });
  }

  payBookingsCaisseOnline(){
    if(!this.launchAction){
      swal({
        title: 'Encaisser plusieurs réservations ?',
        text: 'Voulez-vous envoyer toutes les réservations non encaissées sur la caisse ?',
        icon: 'warning',
        buttons: ['Annuler', 'Oui'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.launchAction = true;
          this.userService.put('pushBookingsInCaisseOnline/:token', {idCustomer: this.customer.ID}, (data) => {
            this.launchAction = false;
            swal({ title: '', text: data.message, icon: ((data.message=='Les réservations ont été envoyée sur la caisse !')?'success':'error')});
          }, (error) => {
            this.launchAction = false;
            var text = 'Impossible de pousser la réservation sur la caisse';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          });
        }
      });
    }
  }


  getLastModificationDateBooking(){
    var lastModificationDateBooking = null;
    if(this.bookings.length > 0){
      for(var i = 0; i < this.bookings.length; i++){
        var booking = this.bookings[i];
        var modificationDate =  new Date(booking.modificationDate);
        if(lastModificationDateBooking == null || modificationDate > lastModificationDateBooking){
          lastModificationDateBooking = new Date(modificationDate);
        }
      }
    }
    else {
      lastModificationDateBooking = new Date();
    }
    return lastModificationDateBooking;
  }


  removeBookingById(idBooking){
    var index = this.bookings.findIndex(element => element.id == idBooking);
    if(index > -1){
      this.bookings.splice(index, 1);
    }
  }

  /**
   * update booking
   */
  updateBooking(booking){
    var found = false;
    for(var i = 0; i < this.bookings.length; i++){
      if(this.bookings[i].id == booking.id){
        found = true;
        this.bookings[i] = booking;
      }
    }
    if(!found){
      this.bookings.push(booking);
    }
    this.generateAllPaymentsAndAskPayments();
  }

  /**
   *
   */
  updateBackend(){
    var lastModificationDateBooking = formatDate(this.getLastModificationDateBooking(), 'yyyy-MM-dd HH:mm:ss', 'fr');
    this.userService.post('updateCustomer/:token', {
      lastModificationDateBooking:lastModificationDateBooking,
      idCustomer: this.customer.ID,
      lastIdLogNotifications:this.lastIdLogNotifications,
      lastIdEmailCustomer:this.lastIdEmailCustomer
    }, (data) => {
      var bookings = data.bookings;
      for(var i = 0; i < bookings.length; i++){
        var booking = bookings[i];
        if(booking.idCustomer == this.customer.ID){
          var arrayOldBookings = booking.linkOldBookings.split(',');
          for(var j = 0; j < arrayOldBookings.length; j++){
            this.removeBookingById(arrayOldBookings[j]);
          }
          this.updateBooking(booking);
        }
      }
      if(data.logNotifications.length > 0){
        this.logNotifications = data.logNotifications.concat(this.logNotifications);
      }
      if(data.emailsCustomer.length > 0){
        this.emailsCustomer = data.emailsCustomer.concat(this.emailsCustomer);
      }
      if(data.logNotifications.length > 0 || data.emailsCustomer.length > 0){
        this.generateHistorics();
      }
    }, (error) => {
      /*var text = 'Impossible de pousser la réservation sur la caisse';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});*/
    });
  }

  goReceiptURL(receiptUrl){
    this.navService.goToLink(receiptUrl);
  }

  /**
   *
   */
  isValidRefundStripePayment(){
    return (this.formRefundStripePayment.reason.length > 0 || (this.formRefundStripePayment.reason === 'other' && this.formRefundStripePayment.message.length > 0)) && this.formRefundStripePayment.amount > 0 && this.formRefundStripePayment.amount <= this.formRefundStripePayment.maxAmount && !this.launchAction;
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
  openRefundStripePayment(content, idBooking, id){
    var amountRefundable = 0;
    this.stripeConnectPayments.forEach(element => {
      if(element.id.indexOf(id) > -1){
        amountRefundable += element.value;
      }
    });
    this.formRefundStripePayment = {reason:'requested_by_customer', message:'', amount:0, maxAmount:0, typeAmount:'all'};
    this.formRefundStripePayment.maxAmount = amountRefundable;
    this.formRefundStripePayment.amount = amountRefundable;
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(this.isValidRefundStripePayment()){
        this.refundPayment(idBooking, id, ((this.formRefundStripePayment.reason!=='other')?this.formRefundStripePayment.reason:this.formRefundStripePayment.message), this.formRefundStripePayment.amount);
      }
    }, (reason) => {

    });
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
        this.bookings.map((element) => {
          if(element.id == idBooking){
            element.paymentState = data.paymentState;
            element.idPaymentState = data.idPaymentState;
          }
        });
        this.getStripeChargeForTransactionId();
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
