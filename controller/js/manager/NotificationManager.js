"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function NotificationManagerFactory($log, $filter, FunctionsManager){
		var NotificationManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(NotificationManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.customer = null;
      this.booking = null
      this.settings = null;

      this.launchNotificationAction = false;
			this.displayEmailContent = false;
			this.typeEmailContent = '';
			this.paymentsType = [];
      this.paymentsTypeList = [];
			this.stopAdvancePayment = false;
			this.expiredDays = 2;

			this.indexTemplate = -1;
			this.nameTemplate = '';
			this.subject = {};
			this.message = {};
			this.currentSender = {};
			this.currentLanguage = 'fr_FR';
			this.currentPreviousEmail = '';

			this.checkboxNotCloseAfterSend = false;

      _scope = scope;
      _self = this;

      _scope.backendCtrl.notificationCtrl = this;
		}

		NotificationManager.prototype.initialize = function(customer, booking, settings, paymentsTypeList, ajaxUrl, typeEmailContent) {
      this.customer = customer;
      this.booking = booking;
      this.settings = settings;

			if(this.settings.languages.length > 0 && customer.locale.length == 0) {
				this.setCurrentLanguage(this.settings.languages[0]);
			}
			else if(customer.locale.length > 0 && this.settings.languages.indexOf(customer.locale) > -1) {
				this.setCurrentLanguage(customer.locale);
			}
			this.setCurrentSender();

      this.paymentsTypeList = paymentsTypeList;
			this.paymentsType = [];
			var typeAccount = this.getTypeAccountOfCustomer();
			for(var i = 0; i < this.paymentsTypeList.length; i++){
				var paymentType = this.paymentsTypeList[i];
				if(typeAccount.paymentsTypeList[paymentType.id]){
					this.paymentsType[i] = paymentType.online?paymentType.id+'':'';
				}
			}

			this.stopAdvancePayment = !this.canPayAdvancePayment();
			this.subject[this.currentLanguage] = '';
			this.message[this.currentLanguage] = '';

			if(this.settings['notifications'].notifications_templates == null || this.settings['notifications'].notifications_templates == ''){
				this.settings['notifications'].notifications_templates = [];
			}

      _ajaxUrl = ajaxUrl;
      this.launchNotificationAction = false;
			this.displayEmailContent = false;
			this.typeEmailContent = '';
			this.indexTemplate = -1;
			this.loadByTypeEmailContent(typeEmailContent);

			this.checkboxNotCloseAfterSend = false;
		}

    NotificationManager.prototype.close = function() {
      this.customer = null;
      this.booking = null;
      this.opened = false;
    }

		NotificationManager.prototype.noBooking = function(){
			return this.booking == null;
		}

		NotificationManager.prototype.updateCustomer = function(customer){
	    this.customer = customer;
		}

		NotificationManager.prototype.updateBooking = function(booking){
      this.booking = booking;
		}

		NotificationManager.prototype.getExpirationDate = function(){
			var date = new Date();
			date.setDate(date.getDate() + Number(this.expiredDays));
			return date;
		}

		NotificationManager.prototype.isNotWpRESACustomer = function(){
			if(this.customer == null) return false;
			return !this.customer.isWpUser;
		}

		NotificationManager.prototype.isRESACustomer = function(){
			if(this.customer == null) return false;
			return this.customer.role == 'RESA_Customer';
		}

		NotificationManager.prototype.isRESAAdminOrStaff = function(){
			if(this.customer == null) return false;
			return this.customer.role == 'administrator' || this.customer.role == 'RESA_Manager' || this.customer.role == 'RESA_Staff';
		}

		NotificationManager.prototype.canPayAdvancePayment = function(){
			if(this.customer == null) return false;
			return this.settings.askAdvancePaymentTypeAccounts.length == 0 ||
				this.settings.askAdvancePaymentTypeAccounts.indexOf(this.customer.typeAccount) != -1;
		}

		NotificationManager.prototype.getTypeAccountOfCustomer = function(){
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

		NotificationManager.prototype.isQuotation = function(){
      return this.booking!=null && this.booking.quotation;
    }

		NotificationManager.prototype.isQuotationRequest = function(){
      return this.booking!=null && this.booking.quotation && this.booking.quotationRequest;
    }

		NotificationManager.prototype.alreadyAskPayment = function(){
			if(this.booking == null) return false;
			for(var i = 0; i < this.booking.askPayments.length; i++){
				var askPayment = this.booking.askPayments[i];
				if(!askPayment.expired) return true;
			}
			return false;
		}

		NotificationManager.prototype.canNotifyCustomer = function(){
      return this.customer!=null && !this.customer.deactivateEmail && this.customer.email != null && this.customer.email.length > 0;
    }

    NotificationManager.prototype.notificationCustomerAccountEmailOk = function(){
      return this.customer!=null && !this.customer.deactivateEmail && this.customer.isWpUser && !this.launchNotificationAction && this.settings.notification_customer_password_reinit && this.settings.customer_account_url.length > 0;
    }

    NotificationManager.prototype.notificationBookingEmailOk = function(){
      return this.booking != null && this.settings !=null && this.booking.status != 'cancelled' && this.booking.status != 'abandonned' && !this.customer.deactivateEmail && this.customer.email && !this.launchNotificationAction && this.settings.customer_account_url.length > 0 && this.settings.notification_customer_booking;
    }

    NotificationManager.prototype.notificationAfterBookingEmailOk = function(){
      return this.booking != null && this.settings !=null && this.booking.status != 'cancelled' && this.booking.status != 'abandonned' && !this.customer.deactivateEmail && this.customer.email && !this.launchNotificationAction && this.settings.customer_account_url.length > 0 && this.settings.notification_after_appointment;
    }

		NotificationManager.prototype.canToSendEmail = function(){
			if(!this.canNotifyCustomer()) return false;
			if(this.typeEmailContent == 'askPayment' || this.typeEmailContent == 'acceptQuotationAndAskPayment'){
				var paymentsTypeOk = false;
				for(var i = 0; i < this.paymentsType.length; i++){
					paymentsTypeOk = paymentsTypeOk || (this.paymentsType[i] != '' && this.paymentsType[i]!=null);
				}
				return paymentsTypeOk /*&& this.paymentLimit!=null*/ && this.booking != null && !this.launchNotificationAction;
			}
			if(this.isNotWpRESACustomer()){
				return !this.haveLinkCustomerAccount(_self.message[_self.currentLanguage]) && !this.haveLinkPaymentBooking(_self.message[_self.currentLanguage]);
	 	 	}
			return true;
		}

		NotificationManager.prototype.displayAskPaymentEmailContent = function(){
		 this.displayEmailContent = true;
		 this.typeEmailContent = 'askPayment';
		 this.subject = JSON.parse(JSON.stringify(this.settings['notifications'].notification_customer_need_payment_subject));
		 this.message =  JSON.parse(JSON.stringify(this.settings['notifications'].notification_customer_need_payment_text));
		}

		NotificationManager.prototype.displayEmailBookingContent = function(){
		 this.displayEmailContent = true;
		 this.typeEmailContent = 'emailBooking';
		 this.subject = JSON.parse(JSON.stringify(this.settings['notifications'].notification_customer_booking_subject));
		 this.message = JSON.parse(JSON.stringify(this.settings['notifications'].notification_customer_booking_text));
		}

		NotificationManager.prototype.displayAfterAppointmentEmailContent = function(){
		 this.displayEmailContent = true;
		 this.typeEmailContent = 'afterAppointment';
		 this.subject = JSON.parse(JSON.stringify(this.settings['notifications'].notification_after_appointment_subject));
		 this.message =  JSON.parse(JSON.stringify(this.settings['notifications'].notification_after_appointment_text));
		}

		NotificationManager.prototype.displayAcceptQuotationContent = function(){
		 this.displayEmailContent = true;
		 this.typeEmailContent = 'acceptQuotation';
		 this.subject = JSON.parse(JSON.stringify(this.settings['notifications'].notification_quotation_accepted_customer_booking_subject));
		 this.message = JSON.parse(JSON.stringify(this.settings['notifications'].notification_quotation_accepted_customer_booking_text));
		}

		NotificationManager.prototype.displayAcceptQuotationAndAskPaymentContent = function(){
		 this.displayEmailContent = true;
		 this.typeEmailContent = 'acceptQuotationAndAskPayment';
		 this.subject = JSON.parse(JSON.stringify(this.settings['notifications'].notification_quotation_accepted_customer_booking_subject));
		 this.message = JSON.parse(JSON.stringify(this.settings['notifications'].notification_quotation_accepted_customer_booking_text));
		}

		NotificationManager.prototype.displayEmailBookingQuotationContent = function(){
		 this.displayEmailContent = true;
		 this.typeEmailContent = 'emailBookingQuotation';
		 this.subject = JSON.parse(JSON.stringify(this.settings['notifications'].notification_quotation_customer_booking_subject));
		 this.message = JSON.parse(JSON.stringify(this.settings['notifications'].notification_quotation_customer_booking_text));
		}

		NotificationManager.prototype.displayCustomEmailContent = function(){
		 this.displayEmailContent = true;
		 this.typeEmailContent = 'customEmail';
		 this.subject = {};
		 this.message = {};
		 this.subject[this.currentLanguage] = '';
		 this.message[this.currentLanguage] = '';
		}

		NotificationManager.prototype.loadByTypeEmailContent = function(typeEmailContent){
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

		NotificationManager.prototype.changeTemplate = function(){
			if(this.indexTemplate == -1){
				this.nameTemplate = '';
				this.loadByTypeEmailContent(this.typeEmailContent);
			}
			else {
				var notification = this.settings['notifications'].notifications_templates[this.indexTemplate];
				this.nameTemplate = notification.name;
				this.subject = JSON.parse(JSON.stringify(notification.subject));
				this.message = JSON.parse(JSON.stringify(notification.message));
			}
		}

		NotificationManager.prototype.deleteTemplate = function(dialogTexts){
			if(this.indexTemplate != -1){
				sweetAlert({
	  		  title: dialogTexts.title,
	  		  text: dialogTexts.text,
	  		  type: "warning",
	  		  showCancelButton: true,
	  		  confirmButtonColor: "#DD6B55",
	  		  confirmButtonText: dialogTexts.confirmButton,
	  		  cancelButtonText: dialogTexts.cancelButton,
	  		  closeOnConfirm: true,
	  		  html: false
	  		}, function(){
					this.settings['notifications'].notifications_templates.splice(this.indexTemplate, 1);
					this.updateTemplates();
					this.indexTemplate = -1;
					this.changeTemplate();
				}.bind(this));
			}
		}


		NotificationManager.prototype.editTemplate = function(){
			if(this.indexTemplate > -1){
				var notification = this.settings['notifications'].notifications_templates[this.indexTemplate];
				notification.name = this.nameTemplate;
				notification.subject = JSON.parse(JSON.stringify(this.subject));
				notification.message = JSON.parse(JSON.stringify(this.message));
				this.updateTemplates();
			}
			else this.createTemplate();
		}

		NotificationManager.prototype.createTemplate = function(){
			var notification = {
				name: JSON.parse(JSON.stringify(this.nameTemplate)),
				subject: JSON.parse(JSON.stringify(this.subject)),
				message: JSON.parse(JSON.stringify(this.message))
			}
			this.settings['notifications'].notifications_templates.push(notification);
			this.updateTemplates();
			this.indexTemplate = (this.settings['notifications'].notifications_templates.length - 1);
			this.changeTemplate();
		}

		NotificationManager.prototype.updateTemplates = function(){
			if(!this.launchNotificationAction){
				this.launchNotificationAction = true;
			  var data = {
					action:'updateNotificationTemplates',
					notificationTemplates: JSON.stringify(this.settings['notifications'].notifications_templates)
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
	          _self.launchNotificationAction = false;

						sweetAlert({
							title: 'Ok',
							type: "success",
							timer: 2000,
							showConfirmButton: true
						});

					});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		/****************************************************************
		 ******************* Needed for askPayment *********************
		 *****************************************************************/

		NotificationManager.prototype.getTotalAmount = function(){
      var needToPay = 0;
  		if(this.booking!=null){
  			needToPay = this.booking.needToPay;
  		}
  		return needToPay;
    }


		/****************************************************************
		 ********************** Email edition ***************************
		 *****************************************************************/
		 NotificationManager.prototype.setCurrentLanguage = function(language){
 			this.currentLanguage = language;
 		}

		NotificationManager.prototype.setCurrentSender = function(){
		 this.currentSender = null;
		 for(var i = 0; i < this.settings.senders.length; i++){
			 var sender = this.settings.senders[i];
			 if(this.currentSender == null){
				 this.currentSender = sender;
			 }
			 else if(_scope.backendCtrl.filters.places[sender.idPlace]) {
				 this.currentSender = sender;
			 }
		 }
	 }

	 NotificationManager.prototype.haveLinkCustomerAccount = function(message){
		 return message.indexOf('[RESA_link_account]') != -1;
	 }

	 NotificationManager.prototype.haveLinkPaymentBooking = function(message){
		 return message.indexOf('[RESA_link_payment_booking]') != -1;
	 }

    NotificationManager.prototype.sentMessagePasswordReinitialization = function(dialogTexts){
			if(!this.launchNotificationAction){
				sweetAlert({
	  		  title: dialogTexts.title,
	  		  text: dialogTexts.text,
	  		  type: "warning",
	  		  showCancelButton: true,
	  		  confirmButtonColor: "#DD6B55",
	  		  confirmButtonText: dialogTexts.confirmButton,
	  		  cancelButtonText: dialogTexts.cancelButton,
	  		  closeOnConfirm: true,
	  		  html: false
	  		}, function(){
	        _scope.$apply(function(){ _self.launchNotificationAction = true; });
				  var data = {
						action:'sentMessagePasswordReinitialization',
						idCustomer: JSON.stringify(_self.customer.ID),
						currentSender: JSON.stringify(_self.currentSender),
					}
					jQuery.post(_ajaxUrl, data, function(data) {
						_scope.$apply(function(){
		          _self.launchNotificationAction = false;
							data = JSON.parse(data);
							if(typeof data === 'string'){
								sweetAlert({
									title: data,
									type: "error",
									timer: 2000,
									showConfirmButton: true
								});
							}
							else {
								sweetAlert({
									title: 'OK',
									type: "success",
									timer: 2000,
									showConfirmButton: true
								});
								if(!this.checkboxNotCloseAfterSend){
									_self.close();
								}
							}
						});
					}).fail(function(err){
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					}).error(function(err){
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					});
				});
			}
  	}

		NotificationManager.prototype.sendEmailNotification = function(dialogTexts){
			if(this.canToSendEmail()){
				var close = (_self.typeEmailContent != 'askPayment' && _self.typeEmailContent != 'acceptQuotationAndAskPayment') || _self.haveLinkPaymentBooking(_self.message[_self.currentLanguage]);
				sweetAlert({
	  		  title: dialogTexts.title,
	  		  text: dialogTexts.text,
	  		  type: "warning",
	  		  showCancelButton: true,
	  		  confirmButtonColor: "#DD6B55",
	  		  confirmButtonText: dialogTexts.confirmButton,
	  		  cancelButtonText: dialogTexts.cancelButton,
	  		  closeOnConfirm: close,
	  		  html: false
	  		}, function(){
	  		  if(_self.typeEmailContent == 'askPayment') _self.askPaymentEmailLaunch();
					else if(_self.typeEmailContent == 'emailBooking') _self.sendBookingEmail();
					else if(_self.typeEmailContent == 'afterAppointment') _self.sendBookingEmail();
					else if(_self.typeEmailContent == 'emailBookingQuotation') _self.sendBookingEmail();
					else if(_self.typeEmailContent == 'acceptQuotation') _self.acceptQuotationBackend();
					else if(_self.typeEmailContent == 'acceptQuotationAndAskPayment') _self.acceptQuotationAndAskPaymentLaunch();
					else if(_self.typeEmailContent == 'customEmail') _self.sendCustomEmail(_self.customer.email, true);
	  		});
			}
		}


    NotificationManager.prototype.sendBookingEmail = function(){
			_scope.$apply(function(){ _self.launchNotificationAction = true; });
		  var data = {
				action:'sendEmailToCustomer',
				idBooking: JSON.stringify(_self.booking.id),
				subject: JSON.stringify(this.subject[this.currentLanguage]),
				message: JSON.stringify(this.message[this.currentLanguage]),
				sender: JSON.stringify(_self.currentSender)
			}
			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
          _self.launchNotificationAction = false;
					data = JSON.parse(data);
					if(typeof data === 'string'){
						sweetAlert({
							title: data,
							type: "error",
							timer: 2000,
							showConfirmButton: true
						});
					}
					else {
						sweetAlert({
							title: 'OK',
							type: "success",
							timer: 2000,
							showConfirmButton: true
						});
						if(!_self.checkboxNotCloseAfterSend){
							_self.close();
						}
					}
				});
			}).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
        _self.launchNotificationAction = false;
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
        _self.launchNotificationAction = false;
			});
  	}

		NotificationManager.prototype.askPaymentEmailLaunch = function(){
			if(!_self.haveLinkPaymentBooking(_self.message[_self.currentLanguage])){
				sweetAlert({
    		  title: 'Envoyer ?',
    		  text: 'Le message a envoyé ne contient pas le "Raccourcie" du lien de paiement, voulez-vous l\'envoyer quand même ?',
    		  type: "warning",
    		  showCancelButton: true,
    		  confirmButtonColor: "#DD6B55",
    		  confirmButtonText: 'Oui',
    		  cancelButtonText: 'Annulé',
    		  closeOnConfirm: true,
    		  html: false
    		}, function(){
					_self.askPaymentEmail();
				});
			}
			else {
				_self.askPaymentEmail();
			}
		}

		NotificationManager.prototype.askPaymentEmail = function(){
			_scope.$apply(function(){ _self.launchNotificationAction = true; });
			var data = {
				action:'askPayment',
				idBookings: JSON.stringify([this.booking.id]),
				//paymentLimit: JSON.stringify(_filter('formatDateTime')(new Date(this.paymentLimit))),
				paymentsType: JSON.stringify(this.paymentsType),
				stopAdvancePayment: JSON.stringify(this.stopAdvancePayment),
				expiredDays:JSON.stringify(this.expiredDays),
				subject: JSON.stringify(this.subject[this.currentLanguage]),
				message: JSON.stringify(this.message[this.currentLanguage]),
				language:this.currentLanguage
			}

			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					_self.launchNotificationAction = false;
					data = JSON.parse(data);
					if(typeof data === 'string'){
						sweetAlert({
							title: data,
							type: "error",
							timer: 2000,
							showConfirmButton: true
						});
					}
					else {
						var copyCustomer = JSON.parse(JSON.stringify(data));
						_scope.backendCtrl.updateCustomer(copyCustomer, null);
						if(!_self.checkboxNotCloseAfterSend){
							_self.close();
						}
						sweetAlert('', 'Ok', 'success');
					}
				});
			}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				_self.launchNotificationAction = false;
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
        _self.launchNotificationAction = false;
			});
  	}

		NotificationManager.prototype.sendCustomEmail = function(email, closeAfterSend){
			if(!this.launchNotificationAction){
				var idBooking = -1;
				if(!this.noBooking()){
					idBooking = this.booking.id;
				}
				this.launchNotificationAction = true;
			  var data = {
					action:'sendCustomEmail',
					idBooking: JSON.stringify(idBooking),
					idCustomer: JSON.stringify(this.customer.ID),
					email:JSON.stringify(email),
					subject: JSON.stringify(this.subject[this.currentLanguage]),
					message: JSON.stringify(this.message[this.currentLanguage]),
					sender: JSON.stringify(_self.currentSender),
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
	          _self.launchNotificationAction = false;
						if(data == 'Ok'){
							sweetAlert({
								title: data,
								type: "success",
								timer: 2000,
								showConfirmButton: true
							});
							if(closeAfterSend && !_self.checkboxNotCloseAfterSend){
								 _self.close();
							}
						}
						else {
							sweetAlert({
								title: 'Erreur dans l\'envoie de l\'email',
								type: "error",
								timer: 2000,
								showConfirmButton: true
							});
						}
					});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
  	}

		/**
		 * Accept quotation
		 */
		NotificationManager.prototype.acceptQuotationBackend = function(){
			if(!this.launchNotificationAction){
					_scope.$apply(function(){ _self.launchNotificationAction = true; });
				var data = {
					action:'acceptQuotationBackend',
					idBooking: JSON.stringify(this.booking.id),
					subject: JSON.stringify(this.subject[this.currentLanguage]),
					message: JSON.stringify(this.message[this.currentLanguage]),
					language:this.currentLanguage
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						_self.launchNotificationAction = false;
						data = JSON.parse(data);
						if(typeof data === 'string'){
							sweetAlert('', data, 'error');
						}
						else {
							_scope.backendCtrl.returnOfForm(data, _self.booking);
							if(!_self.checkboxNotCloseAfterSend){
								_self.close();
							}
						}
					});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		/**
		 * Accept quotation
		 */
		NotificationManager.prototype.acceptQuotationAndAskPaymentLaunch = function(){
			if(!_self.haveLinkPaymentBooking(_self.message[_self.currentLanguage])){
				sweetAlert({
    		  title: 'Envoyer ?',
    		  text: 'Le message a envoyé ne contient pas le "Raccourcie" du lien de paiement, voulez-vous l\'envoyer quand même ?',
    		  type: "warning",
    		  showCancelButton: true,
    		  confirmButtonColor: "#DD6B55",
    		  confirmButtonText: 'Oui',
    		  cancelButtonText: 'Annulé',
    		  closeOnConfirm: true,
    		  html: false
    		}, function(){
					_self.acceptQuotationAndAskPayment();
				});
			}
			else {
				_self.acceptQuotationAndAskPayment();
			}
		}


		/**
		 * Accept quotation
		 */
		NotificationManager.prototype.acceptQuotationAndAskPayment = function(){
			if(!this.launchNotificationAction){
				_scope.$apply(function(){ _self.launchNotificationAction = true; });
				var data = {
					action:'acceptQuotationAndAskPayment',
					idBooking: JSON.stringify(this.booking.id),
					paymentsType: JSON.stringify(this.paymentsType),
					stopAdvancePayment: JSON.stringify(this.stopAdvancePayment),
					expiredDays:JSON.stringify(this.expiredDays),
					subject: JSON.stringify(this.subject[this.currentLanguage]),
					message: JSON.stringify(this.message[this.currentLanguage]),
					language:this.currentLanguage
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						_self.launchNotificationAction = false;
						data = JSON.parse(data);
						if(typeof data === 'string'){
							sweetAlert('', data, 'error');
						}
						else {
							_scope.backendCtrl.returnOfForm(data, _self.booking);
							if(!_self.checkboxNotCloseAfterSend){
								_self.close();
							}
						}
					});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		return NotificationManager;
	}

	angular.module('resa_app').factory('NotificationManager', NotificationManagerFactory);
}());
