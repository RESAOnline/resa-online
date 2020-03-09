"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function NewAskPaymentManagerFactory($log, $filter, FunctionsManager){
		var NewAskPaymentManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(NewAskPaymentManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.customer = null;
      this.booking = null;
      this.settings = {};
			this.paymentsType = [];
      this.paymentsTypeList = [];

			this.stopAdvancePayment = false;
			this.askPaymentActionLaunched = false;

      _scope = scope;
      _self = this;

      _scope.backendCtrl.askPaymentCtrl = this;
		}

		NewAskPaymentManager.prototype.initialize = function(customer, booking, settings, paymentsTypeList, ajaxUrl) {
      this.customer = customer;
      this.booking = booking;
      this.settings = settings;
      this.paymentsTypeList = paymentsTypeList;
      _ajaxUrl = ajaxUrl;
			this.paymentsType = [];
			this.askPaymentActionLaunched = false;
		}

    NewAskPaymentManager.prototype.displayNeedToPay = function(booking){
  		if(this.newPayment.repayment){
  			if(booking.status == 'cancelled'){
  				return booking.needToPay * -1;
  			}
  			else {
  				return (booking.totalPrice - booking.needToPay);
  			}
  		}
  		return booking.needToPay;
  	}

    NewAskPaymentManager.prototype.getTotalAmount = function(){
      var needToPay = 0;
  		if(this.booking!=null){
  			needToPay = this.booking.needToPay;
  		}
  		return needToPay;
    }

    NewAskPaymentManager.prototype.close = function() {
      this.customer = null;
      this.booking = null;
      this.opened = false;
    }

    NewAskPaymentManager.prototype.isOkForm = function(){
			var paymentsTypeOk = false;
			for(var i = 0; i < this.paymentsType.length; i++){
				paymentsTypeOk = paymentsTypeOk || (this.paymentsType[i] != '' && this.paymentsType[i]!=null);
			}
			return paymentsTypeOk /*&& this.paymentLimit!=null*/ && this.booking != null && !this.askPaymentActionLaunched;
  	}

    NewAskPaymentManager.prototype.askPayment = function(){
			if(this.isOkForm()){
				this.askPaymentActionLaunched = true;
				var data = {
					action:'askPayment',
					idBookings: JSON.stringify([this.booking.id]),
					//paymentLimit: JSON.stringify(_filter('formatDateTime')(new Date(this.paymentLimit))),
					paymentsType: JSON.stringify(this.paymentsType),
					stopAdvancePayment: JSON.stringify(this.stopAdvancePayment)
				}

				jQuery.post(_ajaxUrl, data, function(data) {
  				_scope.$apply(function(){
						_self.askPaymentActionLaunched = false;
						data = JSON.parse(data);
						if(typeof data === 'string'){
							alert(data);
						}
						else {
							_self.returnAddPayment(data);
						}
					});
				}.bind(this)).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
  	}

    NewAskPaymentManager.prototype.returnAddPayment = function(customer, oldId){
			var copyCustomer = JSON.parse(JSON.stringify(customer));
			_scope.backendCtrl.updateCustomer(copyCustomer, oldId);
      _self.close();
			sweetAlert('', 'Ok', 'success');
    }


		return NewAskPaymentManager;
	}

	angular.module('resa_app').factory('NewAskPaymentManager', NewAskPaymentManagerFactory);
}());
