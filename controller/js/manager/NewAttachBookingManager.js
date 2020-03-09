"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function NewAttachBookingManagerFactory($log, $filter, FunctionsManager){
		var NewAttachBookingManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(NewAttachBookingManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.customer = null;
      this.bookings = [];
      this.payment = null;
			this.attachBookingActionLaunched = false;

      _scope = scope;
      _self = this;

      _scope.backendCtrl.attachBookingCtrl = this;
		}

		NewAttachBookingManager.prototype.initialize = function(customer, bookings, payment, settings, ajaxUrl) {
      this.customer = customer;
      this.bookings = bookings;
      this.payment = payment;
      this.settings = settings;
      _ajaxUrl = ajaxUrl;
			this.attachBookingActionLaunched = false;
		}

    NewAttachBookingManager.prototype.close = function() {
      this.opened = false;
    }

    NewAttachBookingManager.prototype.displayNeedToPay = function(needToPay){
  		return needToPay;
  	}

  	NewAttachBookingManager.prototype.filterBookings = function(booking){
  		if(_self.payment!=null){
  			return booking.needToPay > 0;
  		}
  	}

    NewAttachBookingManager.prototype.isOkForm = function(){
      return this.payment != null && this.payment.idBooking != -1 && !this.attachBookingActionLaunched;
    }

    NewAttachBookingManager.prototype.attachBooking = function(){
      if(this.isOkForm()){
        this.attachBookingActionLaunched = true;
    		var payment = JSON.parse(JSON.stringify(this.payment));
    		var payments = [payment];
    		var data = {
    			action:'attachBooking',
    			payments: JSON.stringify(payments)
    		}

    		jQuery.post(_ajaxUrl, data, function(data) {
          _scope.$apply(function(){
      			data = JSON.parse(data);
            _self.attachBookingActionLaunched = false;
      			if(typeof data === 'string'){
      				alert(data);
      			}
      			else {
      				_self.returnAddPayment(data);
              _self.close();
      			}
          });
    		}).fail(function(err){
    			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
    		}).error(function(err){
    			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
    		});
      }
  	}

    NewAttachBookingManager.prototype.returnAddPayment = function(customer, oldId){
			var copyCustomer = JSON.parse(JSON.stringify(customer));
			_scope.backendCtrl.updateCustomer(copyCustomer, oldId);
    }

		return NewAttachBookingManager;
	}

	angular.module('resa_app').factory('NewAttachBookingManager', NewAttachBookingManagerFactory);
}());
