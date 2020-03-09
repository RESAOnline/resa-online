"use strict";

(function(){

	var _maxStep = 7;
	var _self = null;
	var _scope = null;
  var _DateComputation = null;
	var _location = null;
	var _filter = null;

	function NewFormController(FormManager, $scope, $location, $filter, DateComputation, RESAFacebookLogin){
		FormManager.call(this);
		RESAFacebookLogin.call(this);
		angular.extend(NewFormController.prototype, FormManager.prototype);
		angular.extend(NewFormController.prototype, RESAFacebookLogin.prototype);
		this.countries = [];
		this.initializationOk = false;
		this.launchUserConnexion = false;
		this.displayFormConnectionFirstStep = false;
		this.step = 1;
		this.displayFormConnection = false;
		this.displayFormRegister = true;
		this.checkboxCustomer = false;
		this.forgottenPasswordCustomerLaunched = false;

		this.dataFacebook = null;

		this.lastBooking = null;

		this.typesAccounts = [];
		this.fieldStates = {};

		this.serviceParametersAbsences = null;

		_location = $location;
		_scope = $scope;
		_filter = $filter;
		_self = this;
    _DateComputation = new DateComputation();
	}

	NewFormController.prototype.init = function(months, countries, ajaxUrl, currentUrl, form, serviceSlugs, quotation, typesAccounts, words){
		this.initializationOk = false;
		this.idForm = form;
		this.addErrorMessage('voucher_not_found', words[0]);
		var data = {
			action:'initializationDataForm',
			form:JSON.stringify(form),
			serviceSlugs:JSON.stringify(serviceSlugs),
			quotation:JSON.stringify(quotation),
			typesAccounts:JSON.stringify(typesAccounts)
		}
		jQuery.post(ajaxUrl, data, function(data) {
			data = JSON.parse(data);
			_self.initialization(data.allServices, data.services, data.members, data.customer, months, countries, data.settings, data.paymentsTypeList, typesAccounts, ajaxUrl, currentUrl);

			if(data.booking != null && data.booking.id != -1){
				_self.lastBooking = data.booking;
			}
			if(_self.settings.facebook_activated){
				_self.setAjaxUrl(ajaxUrl);
				_self.setCallback(function(data){
					_scope.$apply(function(){
						_self.facebookConnected(data);
					});
				});
				_self.setData(_self.settings.facebook_api_id, _self.settings.facebook_api_version);
			}
			_scope.$apply(function(){
				_self.initializationOk = true;
			});
		}).fail(function(err){
			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
		}).error(function(err){
			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
		});
	}


	NewFormController.prototype.initialization = function(allServices, services, members, customer, months, countries, settings, paymentsTypeList, typesAccounts, ajaxUrl, currentUrl){
		this.allMonths = months;
		this.forgottenPasswordCustomerLaunched = false;
		this.typesAccounts = typesAccounts;
		this.initialize(allServices, services,members, customer, settings, paymentsTypeList, ajaxUrl, currentUrl, _scope, function(data){ _self.basicSuccessForm(data);
		_self.step = 7; });
    if(!this.needSelectPlace()) this.step = 2;
		this.countries = countries;

		var typeAccount = 'type_account_0';
		if(this.typesAccounts.length > 0){
			typeAccount = this.typesAccounts[0];
		}
		for(var i = 0; i < this.settings.typesAccounts.length; i++){
			if(typeAccount == this.settings.typesAccounts[i].id){
				this.fieldStates = this.settings.typesAccounts[i].fields;
			}
		}

    this.options = {
      dateDisabled: _self.disabled,
      customClass: _self.getDayClass,
      minDate: null,
      showWeeks: true
    }

		//http://localhost/wordpress_v0.2/formulaire-resa/#?place=lieu1&service=service3
		var idPlace = _location.search().place;
		if(idPlace != null && this.getPlaces().length > 0){
			var places = this.getPlaces();
			for(var i = 0; i < places.length; i++){
				var place = places[i];
				if(place.slug == idPlace){
					this.setCurrentPlace(place);
					this.incrementStep();
				}
			}
		}
		if((idPlace != null && this.getPlaces().length > 0) || this.getPlaces().length == 0){
			var idService = _location.search().service;
			var allServices = this.getServicesByPlace();
			for(var i = 0; i < this.allServices.length; i++){
				var service = this.allServices[i];
				if(service.slug == idService){
					this.setCurrentService(service);
					this.incrementStep();
				}
			}
		}
		var voucher = _location.search().voucher;
		if(voucher != null && voucher.length > 0){
			this.pushPromoCode(voucher);
			this.saveInStorage();
		}
	}

	NewFormController.prototype.isBrowserCompatibility = function(){
		if(is == null) { return true; }
		return is.not.ie() && is.not.safari();
	}

	NewFormController.prototype.getSlugServiceImage = function(service, locale){
		var slug = '';
		var imageSrc = this.getTextByLocale(service.image, locale);
		if(imageSrc == null || imageSrc.length == 0){
			slug = service.slug + '_img';
		}
		return slug;
	}

	NewFormController.prototype.newBooking = function(){
		this.lastBooking = null;
	}

	NewFormController.prototype.needConnection = function(){
		return this.typesAccounts != null && this.typesAccounts.length > 0;
	}

	NewFormController.prototype.isSameTypeAccount = function(){
		return this.typesAccounts != null && this.typesAccounts.length > 0 && this.typesAccounts.indexOf(this.customer.typeAccount) != -1;
	}

	NewFormController.prototype.displayFormConnected = function(){
		return this.typesAccounts == null || this.typesAccounts.length == 0 || (this.customer.ID > -1 && this.isSameTypeAccount());
	}

	NewFormController.prototype.formatTimeslotText = function(timeslot,  text, time_format){
		text = text.replace('[heure_debut]', _filter('formatDateTime')(timeslot.dateStart, time_format));
		text = text.replace('[heure_fin]', _filter('formatDateTime')(timeslot.dateEnd, time_format));
		text = text.replace('[duree]', _filter('formatDateTime')(timeslot.getDuration(), time_format));
		var numberAppointmentsPossible = timeslot.getNumberAppointmentsPossible()
		text = text.replace('[nb_creneau_disponible]', numberAppointmentsPossible);
		if(timeslot.getCalculateCapacity(this.serviceParameters) == 0 && numberAppointmentsPossible > 0){
			numberAppointmentsPossible = numberAppointmentsPossible - 1;
		}
		text = text.replace('[nb_creneau_disponible_restant]', numberAppointmentsPossible);
		text = text.replace('[capacite_par_creneau]', timeslot.getCapacity());
		text = text.replace('[capacite_par_creneau_restante]', timeslot.getCalculateCapacity(this.serviceParameters));
		return text;
	}

	NewFormController.prototype.formatSelectedPlaceSentence = function(locale){
		var sentence = this.getTextByLocale(this.settings.form_selected_place_sentence, locale);
		return sentence.replace('[place]', this.getTextByLocale(this.currentPlace.name, locale));
	}

	NewFormController.prototype.formatSelectedServiceSentence = function(locale){
		var sentence = this.getTextByLocale(this.settings.form_selected_service_sentence, locale);
		return sentence.replace('[activity]', this.getTextByLocale(this.serviceParameters.service.name, locale));
	}

	NewFormController.prototype.formatSelectedDateSentence = function(locale, dateText){
		var sentence = this.getTextByLocale(this.settings.form_selected_date_sentence, locale);
		return sentence.replace('[date]', dateText);
	}

	NewFormController.prototype.formatSelectedTimeslotSentence = function(locale, timeslotText){
		var sentence = this.getTextByLocale(this.settings.form_selected_timeslot_sentence, locale);
		return sentence.replace('[timeslot]', timeslotText);
	}

	NewFormController.prototype.formatRemainingEquipments = function(locale, number){
		var sentence = this.getTextByLocale(this.settings.form_remaining_equipments, locale);
		return sentence.replace('[number]', number);
	}

  NewFormController.prototype.getNumberStep = function(step){
		var number = this.needSelectPlace()?step:step-1;
		if(!this.needSelectParticipants() && step >= 5){
			number--;
		}
		if(this.userIsConnected() && step > 4){
			number--;
		}
		return number;
	}

	NewFormController.prototype.getPlaceolder = function(text, id){
		if(this.fieldStates[id] == 1){ text += '*' }
		return text;
	}

	NewFormController.prototype.facebookConnected = function(data){
		this.dataFacebook = data;
		this.customer.lastName = data.last_name;
		this.customer.firstName =data.first_name;
		this.customer.email = data.email;
		this.customer.idFacebook = data.id;
		this.userConnectionFacebook(this.customer.idFacebook, this.customer.email, false, function(customer){
			_scope.$apply(function(){
				_self.customer = customer;
				_self.getReductions('');
			});
		})
	}

	NewFormController.prototype.customerIsOkForForm = function(){
		return (_self.overrideCustomerIsOk() || _self.isCustomerFacebookConnected()) && (_self.customer.ID != -1 || _self.checkboxCustomer);
	}

	NewFormController.prototype.isFacebookConnected = function(){
		return this.customer.idFacebook.length > 0 && this.settings.facebook_activated && this.dataFacebook != null;
	}

	NewFormController.prototype.isCustomerFacebookConnected = function(){
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

	NewFormController.prototype.overrideCustomerIsOk = function(){
		return this.customer!=null && (this.customer.ID != -1 ||
			((this.customer.lastName.length > 0 || this.fieldStates['lastName'] != 1) &&
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
			(this.customer.legalForm.length > 0 || this.fieldStates['legalForm'] != 1) &&
			 this.customer.password.length > 0 &&
			 this.customer.confirmPassword.length > 0 &&
			 this.customer.confirmPassword == this.customer.password &&
		 		this.checkPassword(this.customer.password)));
	}

	NewFormController.prototype.checkPassword = function(str) {
    var re = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/;
    return re.test(str);
  }

	NewFormController.prototype.isLocked = function(step){
		if(step != 7 && this.step == 7) return true;
		if(step == 1 && !this.needSelectPlace()) return true;
		if(step == 2 && this.needSelectPlace() && this.currentPlace==null) return true;
		if(step == 3 && this.currentService == null) return true;
		if(step == 4 && !this.basketIsOk()) return true;
		if(step == 5 && (!this.customerIsOkForForm() || !this.basketIsOk())) return true;
		if(step == 6 && (!this.customerIsOkForForm() || !this.basketIsOk() || !this.participantsIsSelected())) return true;
		if(step == 7 && this.step != 7) return true;
		return false;
	}

	NewFormController.prototype.numberStep = function(index){
		if(!this.needSelectParticipants() && index > 5) index--;
		if(this.userIsConnected() && index > 4) index--;
		if(!this.needSelectPlace()) index--;
		return index;
	}

	NewFormController.prototype.numberSteps = function(){
		var steps = _maxStep;
		if(!this.needSelectPlace()) steps--;
		if(!this.needSelectParticipants()) steps--;
		if(this.userIsConnected()) steps--;
		return steps;
	}

	NewFormController.prototype.setStep = function(step){
		if(!this.isLocked(step)){
			this.step = step;
		}
	}

	NewFormController.prototype.setDisplayFormConnection = function(displayFormConnection){
		this.displayFormConnection = displayFormConnection;
		this.displayFormRegister = !this.displayFormConnection;
		this.customer.password = '';
	}

	NewFormController.prototype.haveBefore= function(){
		var haveNext = this.step > 1 && this.step < _maxStep;
		if(this.step == 2 && !this.needSelectPlace()){
			haveNext = false;
		}
		return haveNext;
	}

	NewFormController.prototype.haveNext = function(){
		return this.step >= 1 && this.step < 6 && !this.isLocked(this.step + 1);
	}

	NewFormController.prototype.incrementStep = function(){
		if(this.haveNext()){
			if(this.step == 3 && this.userIsConnected()) this.step++;
			if(this.step == 4 && !this.needSelectParticipants()) this.step++;
			this.step++;
		}
	}

	NewFormController.prototype.decrementStep = function(){
		if(this.haveBefore()){
			if(this.step == 6 && !this.needSelectParticipants()) this.step--;
			if(this.step == 5 && this.userIsConnected()) this.step--;
			this.step--;
		}
	}

	NewFormController.prototype.chooseNewDate = function(){
		this.setCurrentService(this.currentService);
	}

	NewFormController.prototype.chooseNewActivity = function(){
		if(!this.isLocked(2)){
			this.setStep(2);
		}
		else {
			this.setStep(1);
		}
	}

	NewFormController.prototype.setServiceParameters = function(serviceParameters){
		this.serviceParametersAbsences = serviceParameters;
	}

	NewFormController.prototype.isServiceParametersAbsencesOpened = function(){
		return this.serviceParametersAbsences;
	}

	NewFormController.prototype.closeServiceParametersAbsences = function(){
		this.serviceParametersAbsences = null;
	}

  NewFormController.prototype.disabled = function(data) {
    var actualDate = new Date();
    var date = data.date,
    mode = data.mode;
    var disabled = false;
    if(_DateComputation.dateIsAvailable){
			if(mode === 'day'){
      	disabled = (!_DateComputation.dateIsAvailable(date, _self.currentService) || date < actualDate);
			}
			else if(mode === 'month'){
				var minDateAvailabilities = _self.clearTime(_self.parseDate(_self.currentService.minDateAvailabilities));
				minDateAvailabilities.setDate(1);
				var maxDateAvailabilities = _self.clearTime(_self.parseDate(_self.currentService.maxDateAvailabilities));
				date = _self.clearTime(date);
				disabled = !(minDateAvailabilities <= date && date <= maxDateAvailabilities && date >= actualDate);
			}
			else if(mode === 'year'){
				var minDateAvailabilities = _self.clearTime(_self.parseDate(_self.currentService.minDateAvailabilities));
				minDateAvailabilities.setDate(1);
				var maxDateAvailabilities = _self.clearTime(_self.parseDate(_self.currentService.maxDateAvailabilities));
				date = _self.clearTime(date);
				disabled = !(minDateAvailabilities.getYear() <= date.getYear() && date.getYear() <= maxDateAvailabilities.getYear() && date.getYear() >= actualDate.getYear());
			}
    }
    return disabled;
  }

  NewFormController.prototype.getDayClass = function(data) {
    var date = data.date,
    mode = data.mode;

    if(_self.disabled(data)) return 'resa_disabled';
    else return 'resa_enabled';
  }

	NewFormController.prototype.fotgottenPassword = function(dialogTexts){
		if(!this.forgottenPasswordCustomerLaunched){
			sweetAlert({
				title: dialogTexts.title,
				text: dialogTexts.text,
				type: "input",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: dialogTexts.confirmButton,
				cancelButtonText: dialogTexts.cancelButton,
				closeOnConfirm: true,
				html: false,
				inputPlaceholder:dialogTexts.inputPlaceholder
			}, function(inputValue){
				if (inputValue === false) return false;
				if (inputValue === "") return false
				var data = {
					action:'forgottenPasswordCustomer',
					email:inputValue
				}
				_scope.$apply(function(){ _self.forgottenPasswordCustomerLaunched = true; });
				jQuery.post(_self.ajaxUrl, data, function(data) {
					_scope.$apply(function(){ _self.forgottenPasswordCustomerLaunched = false; });
					var json = JSON.parse(data);
					sweetAlert('', json.message, json.status);
				}).fail(function(err){
					_scope.$apply(function(){ _self.forgottenPasswordCustomerLaunched = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					_scope.$apply(function(){ _self.forgottenPasswordCustomerLaunched = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			});
		}
	}



	angular.module('resa_app').controller('NewFormController', NewFormController);
}());
