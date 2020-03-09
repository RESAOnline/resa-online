import {Input, Output, OnChanges, OnInit, Component, Inject, EventEmitter, SimpleChanges  } from '@angular/core';
import { DatePipe } from '@angular/common';
import { NgbModal, NgbActiveModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

import { GlobalService } from '../../services/global.service';
import { UserService } from '../../services/user.service';

declare var swal: any;
declare var tinyMCE:any;

@Component({
  selector: 'notification',
  templateUrl: 'notification.html',
  styleUrls:['./notification.css']
})

export class NotificationComponent implements OnChanges,OnInit {
  @Input() booking = null;
  @Input() customer = null;

  @Output() view: EventEmitter<any> = new EventEmitter();

  public settings = null;
  public launchAction:boolean = false;
  public displayEmailContent = false;
  public typeEmailContent = '';
  public paymentsType = {};
  public advancePayment = true;
  public expiredDays = 2;

  public indexTemplate = -1;
  public nameTemplate = '';
  public subject = {};
  public message = {};
  public currentSender = {};
  public currentLanguage = 'fr_FR';
  public currentPreviousEmail = '';

  public checkboxNotCloseAfterSend = false;

  constructor(private userService:UserService, public global:GlobalService, private activeModal: NgbActiveModal, private modalService: NgbModal) {

  }

  ngOnInit(): void {
    this.launchAction = true;
    this.userService.get('notificationsSettings/:token', function(data){
      this.launchAction = false;
      this.settings = data.settings;
      if(this.settings.senders.length > 0){
        this.currentSender = this.settings.senders[0];
        this.setCurrentSender();
      }
      this.paymentsType = {};
			var typeAccount = this.getTypeAccountOfCustomer();
			for(var i = 0; i < this.settings.paymentsTypeList.length; i++){
				var paymentType = this.settings.paymentsTypeList[i];
				if(paymentType.activated && typeAccount.paymentsTypeList[paymentType.id]){
					this.paymentsType[paymentType.id] = paymentType.online;
				}
			}
      if(this.typeEmailContent == 'askPayment'){
        this.displayAskPaymentEmailContent();
      }
      if(this.customer != null && this.customer.locale.length > 0 && this.settings != null && this.settings.languages.indexOf(this.customer.locale) > -1) {
        this.setCurrentLanguage(this.customer.locale);
      }
    }.bind(this), function(error){
      this.launchAction = false;
      var text = 'Impossible de récupérer les données du client';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    }.bind(this));
  }

  ngOnChanges(changes: SimpleChanges) {

  }

  afterSend() {
    if(!this.checkboxNotCloseAfterSend){
      this.close();
    }
  }

  close(){ this.activeModal.close(); }
  dismiss(){ this.activeModal.dismiss(); }

  noBooking(){ return this.booking == null; }
  setBooking(booking){ this.booking = booking; }
  setCustomer(customer){
    this.customer = customer;
    if(customer.locale.length > 0 && this.settings != null && this.settings.languages.indexOf(customer.locale) > -1) {
      this.setCurrentLanguage(customer.locale);
    }
  }

  getExpirationDate(){
    var date = new Date();
    date.setDate(date.getDate() + Number(this.expiredDays));
    return date;
  }

  isNotWpRESACustomer(){
    if(this.customer == null) return false;
    return !this.customer.isWpUser;
  }

  isRESACustomer(){
    if(this.customer == null) return false;
    return this.customer.role == 'RESA_Customer';
  }

  isRESAAdminOrStaff(){
    if(this.customer == null) return false;
    return this.customer.role == 'administrator' || this.customer.role == 'RESA_Manager' || this.customer.role == 'RESA_Staff';
  }

  canPayAdvancePayment(){
    if(this.customer == null) return false;
    return this.settings.askAdvancePaymentTypeAccounts.length == 0 ||
    this.settings.askAdvancePaymentTypeAccounts.indexOf(this.customer.typeAccount) != -1;
  }

  getTypeAccountOfCustomer(){
    if(this.customer != null){
      for(var i = 0; i < this.settings.typesAccounts.length; i++){
        var typeAccount = this.settings.typesAccounts[i];
        if(typeAccount.id == this.customer.typeAccount){
          return typeAccount;
        }
      }
    }
    return null;
  }

  isQuotation(){
    return this.booking!=null && this.booking.quotation;
  }

  isQuotationRequest(){
    return this.booking!=null && this.booking.quotation && this.booking.quotationRequest;
  }

  alreadyAskPayment(){
    if(this.booking == null) return false;
    for(var i = 0; i < this.booking.askPayments.length; i++){
      var askPayment = this.booking.askPayments[i];
      if(!askPayment.expired) return true;
    }
    return false;
  }

  canNotifyCustomer(){
    return this.customer!=null && !this.customer.deactivateEmail && this.customer.email != null && this.customer.email.length > 0;
  }

  notificationCustomerAccountEmailOk(){
    return this.customer!=null && !this.customer.deactivateEmail && this.customer.isWpUser && !this.launchAction && this.settings.notifications.notification_customer_password_reinit && this.settings.customer_account_url.length > 0;
  }

  notificationBookingEmailOk(){
    return this.booking != null && this.settings !=null && this.booking.status != 'cancelled' && this.booking.status != 'abandonned' && !this.customer.deactivateEmail && this.customer.email && !this.launchAction && this.settings.customer_account_url.length > 0 && this.settings.notifications.notification_customer_booking;
  }

  notificationAfterBookingEmailOk(){
    return this.booking != null && this.settings !=null && this.booking.status != 'cancelled' && this.booking.status != 'abandonned' && !this.customer.deactivateEmail && this.customer.email && !this.launchAction && this.settings.customer_account_url.length > 0 && this.settings.notifications.notification_after_appointment;
  }

  generatePaymentTypeArray(){
    var result = [];
    var keys = Object.keys(this.paymentsType);
    keys.map((element) => {
      if(this.paymentsType[element]){
        result.push(element);
      }
    });
    return result;
  }

  canToSendEmail(){
    if(!this.canNotifyCustomer()) return false;
    if(this.typeEmailContent == 'askPayment' || this.typeEmailContent == 'acceptQuotationAndAskPayment'){
      return this.generatePaymentTypeArray().length > 0 /*&& this.paymentLimit!=null*/ && this.booking != null && !this.launchAction;
    }
    if(this.isNotWpRESACustomer()){
      return !this.haveLinkCustomerAccount(this.message[this.currentLanguage]) && !this.haveLinkPaymentBooking(this.message[this.currentLanguage]);
    }
    return true;
  }

  addPicture(content){
    this.modalService.open(content, { windowClass: 'mlg', backdrop:'static' }).result.then((result) => {
      if(result!=null && result.type == 'image'){
        tinyMCE.activeEditor.insertContent('<img src="' + result.src + '" />');
      }
      else if(result!=null && result.type == 'url'){
        tinyMCE.activeEditor.insertContent('<a href="' + result.src + '" target="_blank" rel="noopener">' + result.src +'</a>');
      }
    }, (reason) => {
    });
  }

  displayAskPaymentEmailContent(){
    this.displayEmailContent = true;
    this.typeEmailContent = 'askPayment';
    this.subject = JSON.parse(JSON.stringify(this.settings.notifications.notification_customer_need_payment_subject));
    this.message =  JSON.parse(JSON.stringify(this.settings.notifications.notification_customer_need_payment_text));
  }

  displayEmailBookingContent(){
    this.displayEmailContent = true;
    this.typeEmailContent = 'emailBooking';
    this.subject = JSON.parse(JSON.stringify(this.settings.notifications.notification_customer_booking_subject));
    this.message = JSON.parse(JSON.stringify(this.settings.notifications.notification_customer_booking_text));
  }

  displayAfterAppointmentEmailContent(){
    this.displayEmailContent = true;
    this.typeEmailContent = 'afterAppointment';
    this.subject = JSON.parse(JSON.stringify(this.settings.notifications.notification_after_appointment_subject));
    this.message =  JSON.parse(JSON.stringify(this.settings.notifications.notification_after_appointment_text));
  }

  displayAcceptQuotationContent(){
    this.displayEmailContent = true;
    this.typeEmailContent = 'acceptQuotation';
    this.subject = JSON.parse(JSON.stringify(this.settings.notifications.notification_quotation_accepted_customer_booking_subject));
    this.message = JSON.parse(JSON.stringify(this.settings.notifications.notification_quotation_accepted_customer_booking_text));
  }

  displayAcceptQuotationAndAskPaymentContent(){
    this.displayEmailContent = true;
    this.typeEmailContent = 'acceptQuotationAndAskPayment';
    this.subject = JSON.parse(JSON.stringify(this.settings.notifications.notification_quotation_accepted_customer_booking_subject));
    this.message = JSON.parse(JSON.stringify(this.settings.notifications.notification_quotation_accepted_customer_booking_text));
  }

  displayEmailBookingQuotationContent(){
    this.displayEmailContent = true;
    this.typeEmailContent = 'emailBookingQuotation';
    this.subject = JSON.parse(JSON.stringify(this.settings.notifications.notification_quotation_customer_booking_subject));
    this.message = JSON.parse(JSON.stringify(this.settings.notifications.notification_quotation_customer_booking_text));
  }

  displayCustomEmailContent(){
    this.displayEmailContent = true;
    this.typeEmailContent = 'customEmail';
    this.subject = {};
    this.message = {};
    this.subject[this.currentLanguage] = '';
    this.message[this.currentLanguage] = '';
  }

  loadByTypeEmailContent(typeEmailContent){
    if(typeEmailContent != null){
      if(typeEmailContent == 'askPayment') this.displayAskPaymentEmailContent();
      if(typeEmailContent == 'emailBooking') this.displayEmailBookingContent();
      if(typeEmailContent == 'afterAppointment') this.displayAfterAppointmentEmailContent();
      if(typeEmailContent == 'emailBookingQuotation') this.displayEmailBookingQuotationContent();
      if(typeEmailContent == 'acceptQuotation') this.displayAcceptQuotationContent();
      if(typeEmailContent == 'acceptQuotationAndAskPayment') this.displayAcceptQuotationAndAskPaymentContent();
      if(typeEmailContent == 'customEmail') this.displayCustomEmailContent();
    }
  }

  changeTemplate(){
    if(this.indexTemplate == -1){
      this.nameTemplate = '';
      this.loadByTypeEmailContent(this.typeEmailContent);
    }
    else {
      var notification = this.settings.notifications.notifications_templates[this.indexTemplate];
      this.nameTemplate = notification.name;
      this.subject = JSON.parse(JSON.stringify(notification.subject));
      this.message = JSON.parse(JSON.stringify(notification.message));
    }
  }

  deleteTemplate(dialogTexts){
    if(this.indexTemplate != -1){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Voulez-vous supprimer ce template ?',
        icon: 'warning',
        buttons: ['Annuler', 'Enlever'],
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          this.settings.notifications.notifications_templates.splice(this.indexTemplate, 1);
          this.updateTemplates();
          this.indexTemplate = -1;
          this.changeTemplate();
        }
      });
    }
  }


  editTemplate(){
    if(this.indexTemplate > -1){
      var notification = this.settings.notifications.notifications_templates[this.indexTemplate];
      notification.name = this.nameTemplate;
      notification.subject = JSON.parse(JSON.stringify(this.subject));
      notification.message = JSON.parse(JSON.stringify(this.message));
      this.updateTemplates();
    }
    else this.createTemplate();
  }

  createTemplate(){
    var notification = {
      name: JSON.parse(JSON.stringify(this.nameTemplate)),
      subject: JSON.parse(JSON.stringify(this.subject)),
      message: JSON.parse(JSON.stringify(this.message))
    }
    this.settings.notifications.notifications_templates.push(notification);
    this.updateTemplates();
    this.indexTemplate = (this.settings.notifications.notifications_templates.length - 1);
    this.changeTemplate();
  }

  updateTemplates(){
    if(!this.launchAction){
      this.launchAction = true;
      this.userService.post('settingsLite/:token', {notifications_templates:JSON.stringify(this.settings.notifications.notifications_templates)}, (data) => {
        swal({title: 'OK', icon: 'success'});
        this.launchAction = false;
      }, (error) => {
        this.launchAction = false;
        var text = 'Impossible de sauvegarder les templates';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

  /****************************************************************
  ******************* Needed for askPayment *********************
  *****************************************************************/

  getTotalAmount(){
    var needToPay = 0;
    if(this.booking!=null){
      needToPay = this.booking.needToPay;
    }
    return needToPay;
  }


  /****************************************************************
  ********************** Email edition ***************************
  *****************************************************************/
  setCurrentLanguage(language){
    this.currentLanguage = language;
  }

  setCurrentSender(){
    this.currentSender = null;
    for(var i = 0; i < this.settings.senders.length; i++){
      var sender = this.settings.senders[i];
      if(this.currentSender == null){
        this.currentSender = sender;
      }
      else if(this.global.filterSettings != null &&
        this.global.filterSettings.places != null &&
        this.global.filterSettings.places[sender.idPlace]) {
        this.currentSender = sender;
      }
    }
  }

  haveLinkCustomerAccount(message){
    return message != null && message.indexOf('[RESA_link_account]') != -1;
  }

  haveLinkPaymentBooking(message){
    return message != null && message.indexOf('[RESA_link_payment_booking]') != -1;
  }


  sentMessagePasswordReinitialization(dialogTexts){
    if(!this.launchAction){
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Envoyer l\'email de connexion au compte client ?',
        icon: 'warning',
        buttons: ['Annuler', 'Envoyer'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.launchAction = true;
          this.userService.post('sendNotificationPassword/:token', {idCustomer:this.customer.ID, currentSender:this.currentSender}, (data) => {
            swal({title: 'OK', icon: 'success'});
            this.launchAction = false;
            this.afterSend();
          }, (error) => {
            this.launchAction = false;
            var text = 'Erreur dans l\'envoie de l\'email';
            if(error != null && error.message != null && error.message.length > 0){
              text += ' (' + error.message + ')';
            }
            swal({ title: 'Erreur', text: text, icon: 'error'});
          });
        }
      });
    }
  }

  sendEmailNotification(dialogTexts){
    if(this.canToSendEmail()){
      var close = (this.typeEmailContent != 'askPayment' && this.typeEmailContent != 'acceptQuotationAndAskPayment') || this.haveLinkPaymentBooking(this.message[this.currentLanguage]);
      swal({
        title: 'Êtes-vous sûr ?',
        text: 'Envoyer cet email au client ?',
        icon: 'warning',
        buttons: ['Annuler', 'Envoyer'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          if(this.typeEmailContent == 'askPayment') this.askPaymentEmailLaunch();
          else if(this.typeEmailContent == 'emailBooking') this.sendBookingEmail();
          else if(this.typeEmailContent == 'afterAppointment') this.sendBookingEmail();
          else if(this.typeEmailContent == 'emailBookingQuotation') this.sendBookingEmail();
          else if(this.typeEmailContent == 'acceptQuotation') this.acceptQuotationBackend();
          else if(this.typeEmailContent == 'acceptQuotationAndAskPayment') this.acceptQuotationAndAskPaymentLaunch();
          else if(this.typeEmailContent == 'customEmail') this.sendCustomEmail(this.customer.email, true);
        }
      });
    }
  }


  askPaymentEmailLaunch(){
    if(!this.haveLinkPaymentBooking(this.message[this.currentLanguage])){
      swal({
        title: 'Envoyer ?',
        text: 'Le message a envoyé ne contient pas le "Raccourcie" du lien de paiement, voulez-vous l\'envoyer quand même ?',
        icon: 'warning',
        buttons: ['Annuler', 'Oui'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.askPaymentEmail();
        }
      });
    }
    else {
      this.askPaymentEmail();
    }
  }

  sendBookingEmail(){
    this.launchAction = true;
    this.userService.post('sendEmailToCustomer/:token', {
      idBooking:this.booking.id,
      subject: JSON.stringify(this.subject[this.currentLanguage]),
      message: JSON.stringify(this.message[this.currentLanguage]),
      sender: JSON.stringify(this.currentSender)
    }, (data) => {
      swal({title: 'OK', icon: 'success'});
      this.launchAction = false;
      this.afterSend();
    }, (error) => {
      this.launchAction = false;
      var text = 'Erreur dans l\'envoie de l\'email';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  askPaymentEmail(){
    this.launchAction = true;
      this.userService.post('askPayment/:token', {
      idBookings: JSON.stringify([this.booking.id]),
      //paymentLimit: JSON.stringify(_filter('formatDateTime')(new Date(this.paymentLimit))),
      paymentsType: JSON.stringify(this.generatePaymentTypeArray()),
      stopAdvancePayment: JSON.stringify(!this.advancePayment),
      expiredDays:JSON.stringify(this.expiredDays),
      subject: JSON.stringify(this.subject[this.currentLanguage]),
      message: JSON.stringify(this.message[this.currentLanguage]),
      language:this.currentLanguage
    }, (data) => {
      swal({title: 'OK', icon: 'success'});
      this.launchAction = false;
      this.afterSend();
    }, (error) => {
      this.launchAction = false;
      var text = 'Erreur dans l\'envoie de l\'email';
      if(error != null && error.message != null && error.message.length > 0){
        text += ' (' + error.message + ')';
      }
      swal({ title: 'Erreur', text: text, icon: 'error'});
    });
  }

  sendCustomEmail(email, closeAfterSend){
    if(!this.launchAction){
      var idBooking = -1;
      if(!this.noBooking()){
        idBooking = this.booking.id;
      }
      this.launchAction = true;
      this.userService.post('sendCustomEmail/:token', {
        idBooking: JSON.stringify(idBooking),
        idCustomer: JSON.stringify(this.customer.ID),
        email:JSON.stringify(email),
        subject: JSON.stringify(this.subject[this.currentLanguage]),
        message: JSON.stringify(this.message[this.currentLanguage]),
        sender: JSON.stringify(this.currentSender),
      }, (data) => {
        swal({title: 'OK', icon: 'success'});
        this.launchAction = false;
        this.afterSend();
      }, (error) => {
        this.launchAction = false;
        var text = 'Erreur dans l\'envoie de l\'email';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

  /**
  * Accept quotation
  */
  acceptQuotationAndAskPaymentLaunch(){
    if(!this.haveLinkPaymentBooking(this.message[this.currentLanguage])){
      swal({
        title: 'Envoyer ?',
        text: 'Le message a envoyé ne contient pas le "Raccourcie" du lien de paiement, voulez-vous l\'envoyer quand même ?',
        icon: 'warning',
        buttons: ['Annuler', 'Oui'],
        dangerMode: true,
      })
      .then((ok) => {
        if (ok) {
          this.acceptQuotationAndAskPayment();
        }
      });
    }
    else {
      this.acceptQuotationAndAskPayment();
    }
  }

  /**
  * Accept quotation
  */
  acceptQuotationBackend(){
    if(!this.launchAction){
      this.launchAction = true;
      this.userService.post('acceptQuotationBackend/:token', {
        idBooking: JSON.stringify(this.booking.id),
        subject: JSON.stringify(this.subject[this.currentLanguage]),
        message: JSON.stringify(this.message[this.currentLanguage]),
        language:this.currentLanguage
      }, (data) => {
        swal({title: 'OK', icon: 'success'});
        this.launchAction = false;
        this.afterSend();
      }, (error) => {
        this.launchAction = false;
        var text = 'Erreur dans l\'envoie de l\'email';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }


  /**
  * Accept quotation
  */
  acceptQuotationAndAskPayment(){
    if(!this.launchAction){
      this.launchAction = true;
      this.userService.post('acceptQuotationAndAskPayment/:token', {
        idBooking: JSON.stringify(this.booking.id),
        subject: JSON.stringify(this.subject[this.currentLanguage]),
        message: JSON.stringify(this.message[this.currentLanguage]),
        language:this.currentLanguage
      }, (data) => {
        swal({title: 'OK', icon: 'success'});
        this.launchAction = false;
        this.afterSend();
      }, (error) => {
        this.launchAction = false;
        var text = 'Erreur dans l\'envoie de l\'email';
        if(error != null && error.message != null && error.message.length > 0){
          text += ' (' + error.message + ')';
        }
        swal({ title: 'Erreur', text: text, icon: 'error'});
      });
    }
  }

}
