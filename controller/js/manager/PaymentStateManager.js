"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;
	var _paymentManager = null;

	function PaymentStateManagerFactory($log, $filter, FunctionsManager){
		var PaymentStateManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(PaymentStateManager.prototype, FunctionsManager.prototype);

      this.opened = false;

			this.booking = null;
			this.newPaymentState = null;
			this.settings = {};

      _scope = scope;
      _self = this;
			this.setPaymentStateActionLaunched = false;

      _scope.backendCtrl.paymentStateCtrl = this;
		}

		PaymentStateManager.prototype.initialize = function(booking, settings, ajaxUrl) {
			this.booking = booking;
			this.newPaymentState = booking.paymentState;
			this.settings = settings;
			this.setPaymentStateActionLaunched = false;
			_ajaxUrl = ajaxUrl;
		}

    PaymentStateManager.prototype.close = function() {
      this.opened = false;
    }

    PaymentStateManager.prototype.isOkForm = function(){
      return this.newPaymentState != null && this.newPaymentState != this.booking.paymentState && !this.setPaymentStateActionLaunched;
    }

		PaymentStateManager.prototype.setPaymentStateBooking = function(){
			if(this.isOkForm()){
				this.setPaymentStateActionLaunched = true;

				var data = {
					action:'setPaymentStateBooking',
					idBooking: JSON.stringify(this.booking.id),
					newPaymentState: JSON.stringify(this.newPaymentState)
				}

				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						_self.setPaymentStateActionLaunched = false;
						data = JSON.parse(data);
						if(typeof data === 'string'){
							sweetAlert('', data, 'error');
						}
						else if(_scope.backendCtrl.customerCtrl != null){
							_scope.backendCtrl.updateBooking(data.booking, true);
							_self.close();
						}
					});
				}.bind(this)).fail(function(err){
					_scope.$apply(function(){ _self.setPaymentStateActionLaunched = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					_scope.$apply(function(){ _self.setPaymentStateActionLaunched = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		return PaymentStateManager;
	}

	angular.module('resa_app').factory('PaymentStateManager', PaymentStateManagerFactory);
}());
