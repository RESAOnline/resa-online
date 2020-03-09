"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function NewMergeCustomerManagerFactory($log, $filter, FunctionsManager){
		var NewMergeCustomerManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(NewMergeCustomerManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.customer = null;
	    this.mergeCustomer = null;
      this.selectableCustomers = [];
			this.mergeCustomerActionLaunched = false;

      _scope = scope;
      _self = this;

      _scope.backendCtrl.mergeCustomerCtrl = this;
		}

		NewMergeCustomerManager.prototype.initialize = function(customer, settings, ajaxUrl) {
			this.customer = customer;
	    this.selectableCustomers = [];
	    for(var i = 0; i < allCustomers.length; i++){
	      var customer = allCustomers[i];
	      if((this.customer.isWpUser && !customer.isWpUser) || (!this.customer.isWpUser && customer.isWpUser)){
	        this.selectableCustomers.push(customer);
	      }
	    }
	    this.mergeCustomer = null;
			this.mergeCustomerActionLaunched = false;
		}

    NewMergeCustomerManager.prototype.close = function() {
      this.opened = false;
    }

    NewMergeCustomerManager.prototype.isOkForm = function(){
      return this.mergeCustomer != null && (!this.mergeCustomer.isWpUser || !this.customer.isWpUser) && !this.mergeCustomerActionLaunched;
    }

		NewMergeCustomerManager.prototype.mergeCustomerAction = function(){
			if(this.isOkForm()){
				this.mergeCustomerActionLaunched = true;
				var data = {
					action:'mergeCustomer',
					idCustomer: this.customer.ID,
	        idMergeCustomer: this.mergeCustomer.ID
				}

				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						this.mergeCustomerActionLaunched = false;
						if(typeof data === 'string'){
							sweetAlert('', data, 'error');
						}
						else {
							data.customer = JSON.parse(data.customer);
							data.customer.privateNotes = data.customer.privateNotes.replace(new RegExp('&lt;br /&gt;', 'g'),'<br />');
							_self.backendCtrl.updateCustomer(data.customer);
							_self.backendCtrl.deleteCustomer(data.deleteCustomerID);
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
		return NewMergeCustomerManager;
	}

	angular.module('resa_app').factory('NewMergeCustomerManager', NewMergeCustomerManagerFactory);
}());
