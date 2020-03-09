"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;
	var _paymentManager = null;

	function NewPayBookingManagerFactory($log, $filter, FunctionsManager, PaymentManagerForm){
		var NewPayBookingManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(NewPayBookingManager.prototype, FunctionsManager.prototype);

      this.opened = false;
			_paymentManager = new PaymentManagerForm();

			this.customer = null;
			this.paymentsType = [];
			this.booking = null;
			this.paymentsTypeList = [];
			this.checkboxAdvancePayment = false;
			this.typePayment = null;
			this.settings = {};
			this.payBookingActionLaunched = false;

      _scope = scope;
      _self = this;

      _scope.backendCtrl.payBookingCtrl = this;
		}

		NewPayBookingManager.prototype.initialize = function(customer, booking, paymentsTypeList, settings, ajaxUrl) {
			this.customer = customer;
			this.paymentsType = [];
			this.booking = booking;
			this.paymentsTypeList = paymentsTypeList;
			this.checkboxAdvancePayment = false;
			this.typePayment = null;
			this.settings = settings;
			this.payBookingActionLaunched = false;
			_ajaxUrl = ajaxUrl;
		}

		NewPayBookingManager.prototype.alreadyAdvancedPayment = function(){
			return this.booking.payments.length > 0 || this.booking.haveAdvancePayment || this.booking.paymentState != 'noPayment';
		}

    NewPayBookingManager.prototype.close = function() {
      this.opened = false;
    }

		NewPayBookingManager.prototype.isSynchronizedCaisseOnline = function(){
			return this.settings!=null && this.settings.caisse_online_activated && this.customer!=null && this.customer.idCaisseOnline!=null && this.customer.idCaisseOnline != '';
		}

    NewPayBookingManager.prototype.isOkForm = function(){
      return this.typePayment != null && (this.typePayment.id == 'systempay' || this.typePayment.id == 'monetico' || this.typePayment.id == 'stripe' || this.typePayment.id == 'stripeConnect' || this.typePayment.id == 'paypal' || this.typePayment.id == 'paybox') && !this.payBookingActionLaunched;
    }

		NewPayBookingManager.prototype.payBooking = function(currentUrl){
			if(this.isOkForm()){
				this.payBookingActionLaunched = true;
				var data = {
					action:'payBooking',
					typePayment: JSON.stringify(this.typePayment.id),
					idBooking: JSON.stringify(this.booking.id),
					advancePayment: JSON.stringify(this.checkboxAdvancePayment),
					currentUrl:  JSON.stringify(currentUrl)
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						_self.payBookingActionLaunched = false;
						data = JSON.parse(data);
						if(data instanceof Object && !data.isError){
							if(data.payment != '') {
								if(data.payment.type == 'systemPay')
									_paymentManager.systemPay(data.payment);
								else if(data.payment.type == 'paypal')
									_paymentManager.paypal(data.payment);
								else if(data.payment.type == 'monetico')
									_paymentManager.monetico(data.payment);
								else if(data.payment.type == 'stripe')
									_paymentManager.stripe(data.payment);
								else if(data.payment.type == 'stripeConnect')
										_paymentManager.stripeConnect(data.payment);
								else if(data.payment.type == 'paybox')
									_paymentManager.paybox(data.payment);
								else {
									_paymentManager.setData(data.payment);
								}
							}
						}
						else if(data instanceof Object && data.isError){
							sweetAlert('', data.message, 'error');
						}
						else sweetAlert('', data, 'error');
					});
				}.bind(this)).fail(function(err){
					_self.payBookingActionLaunched = false;
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					_self.payBookingActionLaunched = false;
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		return NewPayBookingManager;
	}

	angular.module('resa_app').factory('NewPayBookingManager', NewPayBookingManagerFactory);
}());
