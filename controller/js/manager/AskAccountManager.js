"use strict";

(function(){
	function AskAccountManagerFactory($location, $filter, FunctionsManager, RESAFacebookLogin){

		var _self = null;
		var _scope = null;
		var _ajaxUrl = null;
		var _currentUrl = null;

		function AskAccountManager(scope){
			FunctionsManager.call(this);
			angular.extend(AskAccountManager.prototype, FunctionsManager.prototype);
			RESAFacebookLogin.call(this);
			angular.extend(AskAccountManager.prototype, RESAFacebookLogin.prototype);
			this.countries = [];

      this.customer = null;
      this.settings = null;
      this.typeAccount = null;
			this.fieldStates = {};
			this.dataFacebook = null;

      this.checkboxCustomer = false;
      this.askAccountRequestLaunch = false;

      this.askAccountRequestLaunched = false;

			_scope = scope;
			_self = this;
		}

		AskAccountManager.prototype.initialize = function(customer, settings, typeAccount, countries, ajaxUrl, currentUrl) {
      this.customer = customer;
      this.settings = settings;
      this.typeAccount = typeAccount;
			this.countries = countries;
			for(var i = 0; i < this.settings.typesAccounts.length; i++){
				if(this.typeAccount == this.settings.typesAccounts[i].id){
					this.fieldStates = this.settings.typesAccounts[i].fields;
				}
			}
			_ajaxUrl = ajaxUrl;
			_currentUrl = currentUrl;
			if(_self.settings.facebook_activated){
				_self.setAjaxUrl(ajaxUrl);
				_self.setCallback(function(data){
					_scope.$apply(function(){
						_self.facebookConnected(data);
					});
				});
				_self.setData(_self.settings.facebook_api_id, _self.settings.facebook_api_version);
			}
		}

		AskAccountManager.prototype.isBrowserCompatibility = function(){
			if(is == null) { return true; }
			return is.not.ie() && is.not.safari();
		}

		AskAccountManager.prototype.getPlaceolder = function(text, id){
			if(this.fieldStates[id] == 1){ text += '*' }
			return text;
		}

    AskAccountManager.prototype.customerIsOkForForm = function(){
  		return (this.customerIsOk() || _self.isCustomerFacebookConnected()) && this.checkboxCustomer;
  	}

		AskAccountManager.prototype.isCustomerFacebookConnected = function(){
			return this.isFacebookConnected() &&
			(this.customer.lastName.length > 0 || this.fieldStates['lastName'] != 1) &&
			(this.customer.firstName.length > 0 || this.fieldStates['firstName'] != 1) &&
			 (this.customer.email!=null && this.customer.email.length > 0) &&
			(this.customer.company.length > 0 || this.fieldStates['company'] != 1) &&
			(this.customer.phone.length > 0 || this.fieldStates['phone'] != 1) &&
			(this.customer.phone2.length > 0 || this.fieldStates['phone2'] != 1) &&
			(this.customer.address.length > 0 || this.fieldStates['address'] != 1) &&
			(this.customer.postalCode.length > 0 || this.fieldStates['postalCode'] != 1) &&
			(this.customer.town.length > 0 || this.fieldStates['town'] != 1) &&
			(this.customer.country.length > 0 || this.fieldStates['country'] != 1) &&
			(this.customer.siret.length > 0 || this.fieldStates['siret'] != 1) &&
			(this.customer.legalForm.length > 0 || this.fieldStates['legalForm'] != 1);
		}

    AskAccountManager.prototype.customerIsOk = function(){
			return this.customer!=null && (this.customer.ID != -1 ||
				((this.customer.lastName.length > 0 || this.fieldStates['lastName'] != 1) &&
				(this.customer.firstName.length > 0 || this.fieldStates['firstName'] != 1) &&
				(this.customer.email!=null && this.customer.email.length > 0) &&
 				(this.customer.company.length > 0 || this.fieldStates['company'] != 1) &&
			  (this.customer.phone.length > 0 || this.fieldStates['phone'] != 1) &&
			  (this.customer.address.length > 0 || this.fieldStates['address'] != 1) &&
			  (this.customer.postalCode.length > 0 || this.fieldStates['postalCode'] != 1) &&
			  (this.customer.town.length > 0 || this.fieldStates['town'] != 1) &&
			  (this.customer.country.length > 0 || this.fieldStates['country'] != 1) &&
				(this.customer.siret.length > 0 || this.fieldStates['siret'] != 1) &&
				(this.customer.legalForm.length > 0 || this.fieldStates['legalForm'] != 1) &&
				 this.customer.password.length > 0 &&
				 this.customer.confirmPassword.length > 0 &&
				 this.customer.confirmPassword == this.customer.password  &&
				 this.checkPassword(this.customer.password)));
		}

		AskAccountManager.prototype.checkPassword = function(str) {
	    var re = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/;
	    return re.test(str);
	  }

    AskAccountManager.prototype.userDeconnection = function(){
			var data = {
				action:'userDeconnection'
			}

			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					_self.customer = JSON.parse(data);
				});
			}).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

    AskAccountManager.prototype.askAccountRequest = function(){
      if(!this.askAccountRequestLaunch){
        this.askAccountRequestLaunch = true;
        this.customer.typeAccount = this.typeAccount;
  			var data = {
  				action:'askAccountRequest',
          customer:JSON.stringify(this.customer)
  			}

  			jQuery.post(_ajaxUrl, data, function(data) {
  				_scope.$apply(function(){
            _self.askAccountRequestLaunch = false;
            data = JSON.parse(data);
            if(data.status == 'error'){
              sweetAlert('', data.message, 'error');
            }
            else {
              _self.askAccountRequestLaunched = true;s
              sweetAlert('', 'Ok', 'success');
            }
  				});
  			}).fail(function(err){
          _scope.$apply(function(){ _self.askAccountRequestLaunch = true; });
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			}).error(function(err){
          _scope.$apply(function(){ _self.askAccountRequestLaunch = true; });
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			});
      }
		}

		AskAccountManager.prototype.isFacebookConnected = function(){
			return this.customer.idFacebook.length > 0 && this.settings.facebook_activated && this.dataFacebook != null;
		}

		AskAccountManager.prototype.facebookConnected = function(data){
			this.dataFacebook = data;
			this.customer.lastName = data.last_name;
			this.customer.firstName =data.first_name;
			this.customer.email = data.email;
			this.customer.idFacebook = data.id;
			this.userConnectionFacebook(this.customer.idFacebook, this.customer.email, false, function(customer){
				_scope.$apply(function(){
					_self.customer = customer;
				});
			})
		}

		return AskAccountManager;
	}


	angular.module('resa_app').factory('AskAccountManager', AskAccountManagerFactory)
}());
