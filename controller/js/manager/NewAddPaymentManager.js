"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function NewAddPaymentManagerFactory($log, $filter, FunctionsManager){
		var NewAddPaymentManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(NewAddPaymentManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.customer = null;
      this.booking = null;
      this.settings = {};
      this.paymentsTypeList = [];
  		this.addPaymentActionLaunched = false;

  		this.newPayment = null;
  		this.date = new Date();
      this.time = new Date(0, 0, 0, this.date.getHours(), this.date.getMinutes());
      _scope = scope;
      _self = this;

      _scope.backendCtrl.addPaymentCtrl = this;
		}

		NewAddPaymentManager.prototype.initialize = function(customer, booking, settings, paymentsTypeList, skeletonPayment, repayment, ajaxUrl) {
      this.customer = customer;
      this.booking = booking;
      this.settings = settings;
      this.paymentsTypeList = paymentsTypeList;
      this.newPayment = JSON.parse(JSON.stringify(skeletonPayment));
			this.newPayment.value = this.getMaximumValue();
  		this.newPayment.repayment = repayment;
      this.newPayment.idBooking = booking.id;
  		this.date = new Date();
      this.time = new Date(0, 0, 0, this.date.getHours(), this.date.getMinutes());
      _ajaxUrl = ajaxUrl;
		}

    NewAddPaymentManager.prototype.displayNeedToPay = function(booking){
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

    NewAddPaymentManager.prototype.getMaximumValue = function(){
      var needToPay = 0;
  		if(this.newPayment!=null && this.booking!=null){
  			var needToPay = this.displayNeedToPay(this.booking);
  		}
  		return needToPay;
    }

    NewAddPaymentManager.prototype.close = function() {
      this.customer = null;
      this.booking = null;
      this.opened = false;
    }

    NewAddPaymentManager.prototype.isOkForm = function(){
  		return !this.addPaymentActionLaunched && this.newPayment!=null && this.newPayment.idBooking != -1 && this.newPayment.type !='' && this.newPayment.value > 0;
  	}

    NewAddPaymentManager.prototype.addNewPayment = function(wpDebug){
  		if(this.isOkForm()){
  			var force = false;
  			if(wpDebug!=null && (wpDebug == 1 || wpDebug)){
  				force = true;
  			}
  			_self.addPaymentActionLaunched = true;
  			var newPayment = JSON.parse(JSON.stringify(this.newPayment));
  			var paymentDate = new Date(this.date);
  			paymentDate.setHours(this.time.getHours());
  			paymentDate.setMinutes(this.time.getMinutes());
  			newPayment.paymentDate = $filter('formatDateTime')(paymentDate);
  			newPayment.note = this.htmlSpecialDecode(newPayment.note);
  			var payments = [newPayment];
  			var data = {
  				action:'addPayments',
  				payments: JSON.stringify(payments),
  				force: JSON.stringify(force)
  			}

  			jQuery.post(ajaxurl, data, function(data) {
  				_scope.$apply(function(){
  					_self.addPaymentActionLaunched = false;
  					data = JSON.parse(data);
  					if(typeof data === 'string'){
  						alert(data);
  					}
  					else {
  						_self.returnAddPayment(data);
  					}
  				}.bind(this));
  			}.bind(this)).fail(function(err){
  				console.log('Error : ' + JSON.stringify(err));
  			});
  		}
  	}

    NewAddPaymentManager.prototype.returnAddPayment = function(customer, oldId){
			var copyCustomer = JSON.parse(JSON.stringify(customer));
			_scope.backendCtrl.updateCustomer(copyCustomer, oldId);
      this.close();
			sweetAlert('', 'Ok', 'success');
    }


		return NewAddPaymentManager;
	}

	angular.module('resa_app').factory('NewAddPaymentManager', NewAddPaymentManagerFactory);
}());
