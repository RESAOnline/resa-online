import { ComponentCanDeactivate } from './ComponentCanDeactivate';
import { DatePipe } from '@angular/common';
import { NgbDate } from '@ng-bootstrap/ng-bootstrap';

import { NavService } from '../services/nav.service';
import { UserService } from '../services/user.service';
import { GlobalService } from '../services/global.service';

declare var swal: any;
declare var $: any;

export abstract class AbstractPlanning extends ComponentCanDeactivate {

  public ngbDate:NgbDate;
  public date = new Date();
  public currentDate = new Date();
  public planning = { min:8, max:20 };
  public selectedTimeslot = null;
  public selectedTimeslotOpened = false;
  public selectedIdTimeslot = '';
  public appointments = [];
  public pageAppointments = 1;
  public nbMaxAppointments = 0;
  public selectedAppointment = null;
  public selectedBooking = null;
  public handler = false;
  public settings = null;

  public newPaymentState = '';
  public loadDataAppointmentsLaunched = false;
  public loadDataBookingLaunched = false;
  public processingData = false;

  constructor(title:string,
    protected userService:UserService,
    protected navService:NavService,
    public global:GlobalService,
    protected datePipe:DatePipe){
    super(title);
    this.ngbDate = new NgbDate(this.date.getFullYear(), this.date.getMonth() + 1, this.date.getDate());
  }

  protected abstract firstLoad():void;
  protected abstract loadDataWithDate(date):void;
  canDeactivate():boolean{ return true; }

  goCustomer(idCustomer){ this.navService.changeRoute('customer/' + idCustomer); }

  changeDate(){
    this.date = this.toDate(this.ngbDate);
    this.loadDataIfNecessary();
  }

  toDate(ngbDate:NgbDate):Date{
    var date = new Date();
    date.setFullYear(ngbDate.year);
    date.setMonth(ngbDate.month - 1);
    date.setDate(ngbDate.day);
    date.setHours(0);
    return date;
  }
  toNgbDate(date:Date):NgbDate{
    return new NgbDate(date.getFullYear(), date.getMonth() + 1, date.getDate());
  }

  previousDate(){
    var date = new Date(this.date);
    date.setDate(date.getDate() - 1);
    this.date = date;
    this.loadDataIfNecessary();
  }

  nextDate(){
    var date = new Date(this.date);
    date.setDate(date.getDate() + 1);
    this.date = date;
    this.loadDataIfNecessary();
  }

  newDate(dateString){
    var date = new Date(dateString);
    this.date = date;
    this.loadDataIfNecessary();
  }

  loadDataIfNecessary(){
    if(this.currentDate.getTime() != this.date.getTime()){
      this.loadDataWithDate(this.date);
    }
  }

  getHours(){
    return Number(this.planning.max) - Number(this.planning.min);
  }

  getMinutes(){
    return this.getHours() * 4;
  }

  getScale(){
    var startDate = new Date(this.date);
    startDate.setHours(this.planning.min);
    var endDate = new Date(this.date);
    endDate.setHours(this.planning.max);
    return Math.floor((endDate.getTime() - startDate.getTime()) / (1000 * 60)) / 5;
  }

  getScaleCSS(){
    return 'repeat(' + this.getScale() + ', 1fr)';
  }

  getScaleHours(){
    return Math.floor(this.getScale() / this.getHours());
  }

  getScaleMinutes(){
    return this.getScaleHours() / 4;
  }

  getRepeat(num){
    return new Array(num);
  }


  getBeginMinutes(timeslot){
    var startDate = this.global.parseDate(timeslot.startDate);
    startDate.setHours(this.planning.min);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);
    var timeslotStartDate = this.global.parseDate(timeslot.startDate);
    if(timeslotStartDate < startDate){
      timeslotStartDate = this.global.parseDate(startDate);
    }
    return (Math.floor(Math.abs((timeslotStartDate.getTime() - startDate.getTime()) / (1000 * 60))) / 5) + 1;
  }

  getDurationMinutes(timeslot){
    var startDate = this.global.parseDate(timeslot.startDate);
    startDate.setHours(this.planning.min);
    startDate.setMinutes(0);
    startDate.setSeconds(0);
    startDate.setMilliseconds(0);

    var endDate = this.global.parseDate(timeslot.endDate);
    endDate.setHours(this.planning.max);
    endDate.setMinutes(0);
    endDate.setSeconds(0);
    endDate.setMilliseconds(0);

    var timeslotStartDate = this.global.parseDate(timeslot.startDate);
    if(timeslotStartDate < startDate){
      timeslotStartDate = this.global.parseDate(startDate);
    }
    var timeslotEndDate = this.global.parseDate(timeslot.endDate);
    if(timeslotEndDate > endDate){
      timeslotEndDate = this.global.parseDate(endDate);
    }
    if(timeslotEndDate.getMinutes()%5 != 0){
      timeslotEndDate.setMinutes(timeslotEndDate.getMinutes() - (timeslotEndDate.getMinutes()%5));
    }
    return Math.floor(Math.abs((timeslotEndDate.getTime() - timeslotStartDate.getTime()) / (1000 * 60))) / 5;
  }

  selectTimeslot(timeslot, id){
    if(timeslot.type == 'timeslot'){
      this.selectedTimeslot = timeslot;
      this.selectedTimeslotOpened = true;
      this.selectedIdTimeslot = id;
      this.appointments = [];
      this.pageAppointments = 1;
      this.nbMaxAppointments = 0;
      this.setPositionCreaneauDetails(id);
      this.loadDataAppointments(timeslot);
    }
  }

  setPositionCreaneauDetails(id){
    var pos = $('#' + id).offset();
    var sizeOfDetails = $('.creneau_detail').width();
    var left = pos.left;
    if((left + sizeOfDetails) > ($(window).width() - 100)){
      left -= sizeOfDetails/2;
    }
    $('.creneau_detail').show().offset({ top: pos.top, left: left });
    $('#planning').unbind('click');
    $('#planning').click((event) => {
      if((!$(event.target).closest('.creneau_detail').length) && (!$(event.target).closest('.pl_selected').length))  {
        this.closeTimeslotDetails();
      }
      if (!$(event.target).closest('.cd_rdv').length) {
        $('.cd_rdv').removeClass("rdv_selected");
        $('.cd_rdv_actions').hide().offset({ top: 0, left: 0 });
      }
    });
  }

  closeTimeslotDetails(){
    this.selectedTimeslotOpened = false;
    this.selectedTimeslot = null;
    this.selectedIdTimeslot = '';
    //this.selectedAppointment = null;
    $('.creneau_detail').hide().offset({ top: 0, left: 0 });
    $('.cd_rdv_actions').hide().offset({ top: 0, left: 0 });
    $('#planning').unbind('click');
  }

  selectAppointment(appointment){
    this.selectedAppointment = appointment;
    this.seeBooking();
    //$('.cd_rdv').removeClass("rdv_selected");
    //$('#rdv_' + appointment.idAppointment).addClass("rdv_selected");
    //var pos = $('#rdv_' + appointment.idAppointment).offset();
    //$('.cd_rdv_actions').show().css("display", "inline-block").offset({ top: pos.top, left: pos.left+$('.rdv_selected').width()+20 });
  }

  switchHandler(){
    this.handler = !this.handler;
  }

  seeBooking(){
    this.handler = true;
    this.loadDataBooking(this.selectedAppointment.id, false);
  }

  openModifyRESALink(id){
    return this.userService.getCurrentHost() + '/wp-admin/admin.php?page=resa_appointments&view=addBooking&booking=' + id;
  }

  /**
   * load appointments
   */
  protected abstract loadDataAppointments(timeslot);

  formatBooking(booking){
    var newAppointments = {};
    for(var i = 0; i < booking.appointments.length; i++){
      var appointment = booking.appointments[i];
      var stringCurrentDate = this.datePipe.transform(this.global.parseDate(appointment.startDate), 'yyyy-MM-dd');
      if(newAppointments[stringCurrentDate] == null){
        newAppointments[stringCurrentDate] = [];
      }
      newAppointments[stringCurrentDate].push(appointment);
    }
    booking.formatAppointments = Object.values(newAppointments);
    return booking;
  }

  /**
  * load appointments
  */
  loadDataBooking(idBooking, force, callback = null){
    this.loadDataBookingLaunched = true;
    this.userService.get('booking/:token/' + idBooking, function(data){
      this.loadDataBookingLaunched = false;
      if(force || (this.selectedAppointment != null &&
      (this.selectedAppointment.id == data.id ||
      this.selectedAppointment.id == data.idCreation))){
        this.selectedBooking = this.formatBooking(data);
        if(callback != null){
          callback();
        }
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  loadDataHistoricBookingIfNecessary(){
    if(this.selectedBooking.historic == null){
      this.loadDataHistoricBooking(this.selectedBooking.id);
    }
  }

  /**
  * load historic
  */
  loadDataHistoricBooking(idBooking){
    this.loadDataBookingLaunched = true;
    this.userService.get('historic/:token/' + idBooking, function(data){
      this.loadDataBookingLaunched = false;
      if(this.selectedBooking != null &&
        this.selectedBooking.id == idBooking){
        this.selectedBooking.historic = data;
      }
    }.bind(this), function(error){
      var text = 'Impossible de récupérer les données des activitées';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  /***
   * PAYMENTS - CAISSE ONLINE
   */

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

  isCaisseOnlineActivated(){
    return this.settings != null && this.settings.caisse_online_activated;
  }

  isStripeActivated(booking){
    return this.global.stripeConnect != null && this.global.stripeConnect.activated && booking.transactionId != '';
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
    var idsBooking = booking.linkOldBookings;
    if(idsBooking != '') idsBooking += ',';
    idsBooking += booking.id;
    var url = this.settings.caisse_online_server_url + 'ticketsByIdBooking/' + this.settings.caisse_online_license_id + '/' + idsBooking;
    this.processingData = true;
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
      this.processingData = false;
    }, (error) => {
      this.processingData = false;
      var text = 'Impossible de récupérer les données de la caisse';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'})
    });
  }

  getStripeChargeForTransactionId(booking){
    var idPaymentsList = [];
    for(var i = 0; i < booking.payments.length; i++){
      if(booking.payments[i].id != null){
        idPaymentsList.push(booking.payments[i].id);
      }
    }

    var url = this.global.stripeConnect.apiStripeConnectUrl + 'getCharges.php';
    this.processingData = true;
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
              note:''
            });
            if(payment.refunded){
              booking.payments.push({
                id:idPayment + '_r',
                payment_intent:payment.payment_intent + '_r',
                isStripeConnect:true,
                isReceipt:booking.paymentState == 'advancePayment',
                isCancellable:!payment.refunded,
                idBooking:payment.metadata?payment.metadata.idBooking:-1,
                paymentDate:new Date(payment.refunds.data[0].created * 1000),
                type:'stripeConnect',
                value:(payment.amount_refunded/100) * -1,
                name:'Stripe',
                idReference:payment.id,
                credit:false,
                vendor:'Stripe',
                note:payment.refunds.data[0].reason
              });
            }
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
      this.processingData = false;
    }, (error) => {
      this.processingData = false;
      var text = 'Impossible de récupérer les données de stripe';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'})
    });
  }

  payBookingCaisseOnline(booking){
    if(!this.processingData){
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
    this.processingData = true;
    this.userService.put('pushBookingInCaisseOnline/:token', {idBooking: booking.id}, (data) => {
      this.processingData = false;
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
      this.processingData = false;
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

  isOkPaymentStatus(){
    return this.newPaymentState != null && this.newPaymentState != this.selectedBooking.paymentState && !this.processingData;
  }

  changePaymentStateBooking(){
    this.processingData = true;
    this.userService.post('changePaymentState/:token', {idBooking:this.selectedBooking.id, newPaymentState:this.newPaymentState}, (data) => {
      this.processingData = false;
      this.selectedBooking = this.formatBooking(data.booking);
      swal({ title: 'OK', text: '', icon: 'success'})
    }, (error) => {
      this.processingData = false;
      var text = 'Impossible de pousser la réservation sur la caisse';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  calculateBookingPaymentState(){
    if(!this.processingData && this.isCaisseOnlineActivated()){
      this.processingData = true;
      this.userService.post('calculateBookingPaymentState/:token', {idBooking:this.selectedBooking.id}, (data) => {
        this.processingData = false;
        this.selectedBooking = this.formatBooking(data.booking);
        swal({ title: 'OK', text: '', icon: 'success'});
      }, (error) => {
        this.processingData = false;
        var text = 'Impossible de pousser la réservation sur la caisse';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

  getPlaceName(idPlace){
    if(this.settings != null && this.settings.places != null){
      var place = this.settings.places.find(element => element.id == idPlace);
      if(place != null) return place.name[this.global.currentLanguage];
    }
    return '';
  }



}
