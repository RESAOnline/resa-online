"use strict";

(function(){
	function AccountManagerFactory($location, $filter, FunctionsManager, AppointmentManager, PaymentManagerForm, RESAFacebookLogin){

		var _self = null;
		var _scope = null;
		var _ajaxUrl = null;
		var _paymentManager = null;
		var _currentUrl = null;

		function AccountManager(scope){
			FunctionsManager.call(this);
			angular.extend(AccountManager.prototype, FunctionsManager.prototype);
			AppointmentManager.call(this);
			angular.extend(AccountManager.prototype, AppointmentManager.prototype);
			RESAFacebookLogin.call(this);
			angular.extend(AccountManager.prototype, RESAFacebookLogin.prototype);

			_paymentManager = new PaymentManagerForm();

			this.countries = [];

			this.customer = null;
			this.modelCustomer = null;
			this.changePassword = false;
			this.services = [];
			this.reductions = [];
			this.booking = null;
			this.typePayment = null;
			this.settings = {};
			this.paymentsTypeList = [];
			this.idPaymentsTypeToName = [];
			this.fieldStates = {};

			this.filterDateBooking = 0;
			this.filterDateQuotation = 0;
			this.subPage = 'bookings';

			this.checkboxCustomer = false;
			this.checkboxAdvancePayment = false;
			this.checkboxAcceptPayment = false;
			this.launchPayment = false;
			this.launchModifyCustomer = false;
			this.launchAcceptQuotation = false;
			this.launchRefuseQuotation = false;
			this.updateDataAccountsLaunche = false;
			this.timeoutUpdateDataAccounts = null;

			this.dataInitializated = false;

			_scope = scope;
			_self = this;
		}

		AccountManager.prototype.initialize = function(ajaxUrl, currentUrl, countries) {
			_ajaxUrl = ajaxUrl;
			_currentUrl = currentUrl;
			this.countries = countries;
			this.dataInitializated = false;
			this.initializationData();
		}

		AccountManager.prototype.initializationData = function(){
			var data = {
				action:'initializationDataAccounts'
			}
			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					data = JSON.parse(data);
					_self.dataInitializated = true;
					_self.setCustomer(data.customer);
					_self.changePassword = false;
					_self.services = data.services;
					_self.reductions = data.reductions;
					_self.paymentsTypeList = data.paymentsTypeList;
					_self.idPaymentsTypeToName = data.idPaymentsTypeToName;
					_self.settings = data.settings;
					if(_self.settings.facebook_activated){
						_self.setAjaxUrl(_ajaxUrl);
						_self.setCallback(function(data){
							_scope.$apply(function(){
								_self.facebookConnected(data);
							});
						});
						_self.setData(_self.settings.facebook_api_id, _self.settings.facebook_api_version);
					}
					var booking =  _self.getBooking($location.search().id);
					_self.setBooking(booking);
					_self.getBookingDetails(booking);
					for(var i = 0; i < _self.settings.typesAccounts.length; i++){
						if(_self.customer.typeAccount == _self.settings.typesAccounts[i].id){
							_self.fieldStates = _self.settings.typesAccounts[i].fields;
						}
					}
					_self.launchUpdateDataAccounts();
				});
			}).fail(function(err){
				console.log(err);
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				console.log(err);
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		AccountManager.prototype.setCustomer = function(customer){
			this.customer = customer;
			this.customer.lastName = $filter('htmlSpecialDecode')(this.customer.lastName);
			this.customer.firstName = $filter('htmlSpecialDecode')(this.customer.firstName);
			this.customer.company = $filter('htmlSpecialDecode')(this.customer.company);
			this.modelCustomer = JSON.parse(JSON.stringify(this.customer));
			for(var i = 0; i < this.customer.bookings.length; i++){
				var localBooking = this.customer.bookings[i];
				if(localBooking.intervals == null){
					localBooking.intervals = this.getIntervals(localBooking);
				}
			}
		}

		AccountManager.prototype.isBrowserCompatibility = function(){
			if(is == null) { return true; }
			return is.not.ie() && is.not.safari();
		}

		AccountManager.prototype.getTypeAccountOfCustomer = function(){
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

		AccountManager.prototype.getParticipantFieldName = function(participant, field, locale){
			if(field.type == 'select'){
				for(var i = 0; i < field.options.length; i++){
					var option = field.options[i];
					if(option.id == participant[field.varname]){
						return this.getTextByLocale(option.name, locale);
					}
				}
			}
			return participant[field.varname];
		}

		AccountManager.prototype.isConnected = function(){ return this.customer != null && this.customer.ID > -1; }
		AccountManager.prototype.seeBookings = function(){ this.subPage = 'bookings'; }
		AccountManager.prototype.seeQuotations = function(){ this.subPage = 'quotations'; }
		AccountManager.prototype.seePersonalInformations = function(){ this.subPage = 'personalInformations'; }
		AccountManager.prototype.isSeeBookings = function(){ return this.subPage == 'bookings'; }
		AccountManager.prototype.isSeeQuotations = function(){ return this.subPage == 'quotations'; }
		AccountManager.prototype.isSeePersonalInformations = function(){ return this.subPage == 'personalInformations'; }


		AccountManager.prototype.getParticipantsParameter = function(idParameter){
			if(this.settings.form_participants_parameters != null){
				for(var i = 0; i < this.settings.form_participants_parameters.length; i++){
					if(this.settings.form_participants_parameters[i].id == idParameter){
						return this.settings.form_participants_parameters[i];
					}
				}
			}
			return null;
		}

		AccountManager.prototype.getServiceById = function(idService){
			for(var i = 0; i < this.services.length; i++){
				if(this.services[i].id == idService){
					return this.services[i];
				}
			}
			return null;
		}

		AccountManager.prototype.getReductionById = function(idReduction){
			for(var i = 0; i < this.reductions.length; i++){
				if(this.reductions[i].id == idReduction){
					return this.reductions[i];
				}
			}
			return null;
		}

		AccountManager.prototype.getPlaceById = function(idPlace){
			if(this.settings != null)  {
				if(this.settings.places == null || this.settings.places=='' || this.settings.places==false){
					this.settings.places = [];
				}
				for(var i = 0; i < this.settings.places.length; i++){
					var place = this.settings.places[i];
					if(place.id == idPlace){
						return place;
					}
				}
			}
			return null;
		}


		AccountManager.prototype.getServicePriceAppointment = function(service, idPrice){
			if(service != null){
				for(var i = 0; i < service.servicePrices.length; i++){
					var servicePrice = service.servicePrices[i];
					if(servicePrice.id == idPrice)
						return servicePrice;
				}
			}
			return null;
		}

		AccountManager.prototype.getStateById = function(id){
			for(var i = 0; i < _self.settings.statesList.length; i++){
				if(_self.settings.statesList[i].id == id) return _self.settings.statesList[i];
			}
			return null;
		}

		/**
		 * return the sub state of waiting states
		 * -1  no waiting
		 *  0 just waiting
		 *  1 paiement
		 *  2 expiré
		 */
		AccountManager.prototype.getWaitingSubState = function(booking){
			if(booking==null || booking.status != 'waiting') return -1;
			var paiement = false;
			var expired = false;
			for(var i = 0; i < booking.askPayments.length; i++){
				var askPayment = booking.askPayments[i];
				if(this.parseDate(askPayment.expiredDate).getTime() < new Date().getTime()){
					expired = true;
				}
				else {
					paiement = true;
				}
			}
			var substate = 0;
			if(paiement) substate = 1;
			else if(expired) substate = 2;
			return substate;
		}

		AccountManager.prototype.getWaitingName = function(booking, paymentWord, expiredWord){
			if(booking==null || booking.status != 'waiting') return '';
			var subState = this.getWaitingSubState(booking);
			var state = this.getStateById('waiting'); //filterName
			var name = state.filterName;
			if(subState == 1) name += '('+paymentWord+')';
			else if(subState == 2) name +=  '('+expiredWord+')';
			return name;
		}

		AccountManager.prototype.haveAskPaymentNoExpired = function(booking){
			return this.getWaitingSubState(booking) == 1;
		}

		AccountManager.prototype.isFiltered = function(booking){
			if(booking.status != 'ok' && booking.status != 'waiting') return false;
			if(this.filterDateBooking == 2) return false;
			else {
				var endDate = this.parseDate(booking.intervals[booking.intervals.length - 1].interval.endDate);
				var date = new Date();
				if(date > endDate && this.filterDateBooking == 0) return true;
				else if(date <= endDate && this.filterDateBooking == 1) return true;
			}
			return false;
		}

		AccountManager.prototype.isFilteredQuotation = function(booking){
			if(this.filterDateQuotation == 2) return false;
			else {
				var endDate = this.parseDate(booking.intervals[booking.intervals.length - 1].interval.endDate);
				var date = new Date();
				if(date > endDate && this.filterDateQuotation == 0) return true;
				else if(date <= endDate && this.filterDateQuotation == 1) return true;
			}
			return false;
		}


		AccountManager.prototype.isDisplayed = function(booking, appointment){
			if(appointment.internalIdLink == -1) return true;
			var appointments = this.getAppointmentsByInternalIdLink(booking, appointment.internalIdLink);
			return appointments.length <= 1 || appointments[0].id == appointment.id;
		}

		AccountManager.prototype.isCombinedRow = function(booking, appointment){
			if(appointment.internalIdLink == -1) return false;
			var appointments = this.getAppointmentsByInternalIdLink(booking, appointment.internalIdLink);
			return appointments.length > 1 && appointments[0].id == appointment.id;
		}

		AccountManager.prototype.getNumberCombined = function(booking, internalIdLink){
			if(internalIdLink == -1) return 1;
			var appointments = this.getAppointmentsByInternalIdLink(booking, internalIdLink);
			if(appointments.length == 0) return 1;
			return appointments.length;
		}

		AccountManager.prototype.getAppointmentsByInternalIdLink = function(booking, internalIdLink){
			var appointments = [];
			for(var i = 0; i < booking.appointments.length; i++){
				var appointment = booking.appointments[i];
				if(appointment.internalIdLink == internalIdLink){
					appointments.push(appointment);
				}
			}
			return appointments
		}

		AccountManager.prototype.getPriceNumberPrice = function(idService, appointmentNumberPrice, appointment){
			var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), appointmentNumberPrice.idPrice)
			var total = 0;
			if(priceModel != null && appointmentNumberPrice.number > 0){
				total = appointmentNumberPrice.number * priceModel.price;
				if(!priceModel.notThresholded){
					total = 0;
					var hours = ((new Date(appointment.endDate)).getTime() - (new Date(appointment.startDate)).getTime()) / (3600 * 1000);
					var found = false;
					for(var i = 0; i < priceModel.thresholdPrices.length; i++){
						var thresholdPrice = priceModel.thresholdPrices[i];
						if(thresholdPrice.min <= appointmentNumberPrice.number && appointmentNumberPrice.number <= thresholdPrice.max){
							found = true;
							total = Number(appointmentNumberPrice.number * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)) + Number(thresholdPrice.price);
							if(thresholdPrice.byHours!=null && thresholdPrice.byHours){
								total *= hours;
							}
						}
					}
					if(!found){
						var thresholdPrice = priceModel.thresholdPrices[priceModel.thresholdPrices.length - 1];
						total = Number(thresholdPrice.max * Number(thresholdPrice.byPerson!=null?thresholdPrice.byPerson:0)) +
							Number(thresholdPrice.price) +
							Number(Number(thresholdPrice.supPerson!=null?thresholdPrice.supPerson:0) * (appointmentNumberPrice.number - thresholdPrice.max));
						if(thresholdPrice.byHours!=null && thresholdPrice.byHours){
							total *= hours;
						}
					}
				}
			}
			return total;
		}

		AccountManager.prototype.getServicesOnBooking = function(booking){
			var services = [];
			if(booking!=null){
				for(var i = 0; i < booking.appointments.length; i++){
					var appointment = booking.appointments[i];
					var service = _self.getServiceById(appointment.idService);
					if(service != null){
						services.push(service);
					}
				}
			}
			return services;
		}

		AccountManager.prototype.getReductionsOnBooking = function(booking){
			var reductions = [];
			var allIdReductions = [];
			if(booking!=null){
				for(var i = 0; i < booking.appointments.length; i++){
					var appointment = booking.appointments[i];
					for(var j = 0; j < appointment.appointmentReductions.length; j++){
						allIdReductions.push(appointment.appointmentReductions[j].idReduction);
					}
				}
				for(var i = 0; i < booking.bookingReductions.length; i++){
					allIdReductions.push(booking.bookingReductions[i].idReduction);
				}
				for(var i = 0; i < allIdReductions.length; i++){
					var reduction = _self.getReductionById(allIdReductions[i]);
					if(reduction != null){
						reductions.push(reduction);
					}
				}
			}
			return reductions;
		}

		/**
		 * open receipt booking dialog
		 */
		AccountManager.prototype.openReceiptBookingDialog = function(booking){
			if(this.receiptBookingCtrl!=null){
				this.receiptBookingCtrl.opened = true;
				var customer = this.customer;
				var services = this.getServicesOnBooking(booking);
				var reductions = this.getReductionsOnBooking(booking);
				this.receiptBookingCtrl.initialize(customer, booking, services, reductions, this.idPaymentsTypeToName, false, this.settings);
			}
		}

		AccountManager.prototype.nbQuotations = function(){
			var number = 0;
			if(this.customer != null){
				for(var i = 0; i < this.customer.bookings.length; i++){
					if(this.customer.bookings[i].quotation) number++;
				}
			}
			return number;
		}

		AccountManager.prototype.nbBookings = function(){
			var number = 0;
			if(this.customer != null){
				for(var i = 0; i < this.customer.bookings.length; i++){
					if(!this.customer.bookings[i].quotation) number++;
				}
			}
			return number;
		}

		AccountManager.prototype.round = function(value){
			return Math.round(value * 100) / 100;
		}

		AccountManager.prototype.isMethodsPayment = function(booking, id){
			if(booking == null) return false;
			if(booking.status == 'ok') return true;
			for(var i = booking.askPayments.length - 1; i >= 0; i--){
				var askPayment = booking.askPayments[i];
				if(!askPayment.expired){
					if(askPayment.types.split(',').indexOf(id) > -1 || askPayment.types.length == 0 || askPayment.types == null){
						return true;
					}
				}
			}
			return false;
		}

		AccountManager.prototype.getTypeAdvancePayment = function(booking){
			if(booking == null) return false;
			for(var i = booking.askPayments.length - 1; i >= 0; i--){
				var askPayment = booking.askPayments[i];
				if(!askPayment.expired){
					return askPayment.typeAdvancePayment;
				}
			}
			return false;
		}

		AccountManager.prototype.isComplete = function(booking){
			return booking!=null && ((booking.needToPay == 0 && booking.totalPrice != 0) || booking.paymentState == 'complete' || booking.paymentState == 'deposit');
		}

		AccountManager.prototype.removeBookingById = function(idBooking){
			var newBookings = [];
			for(var i = 0 ; i < this.customer.bookings.length; i++){
				if(this.customer.bookings[i].id != idBooking){
					newBookings.push(this.customer.bookings[i]);
				}
			}
			this.customer.bookings = newBookings;
		}

		AccountManager.prototype.getBookingId = function(booking){
			if(booking == null) return 0;
			if(booking.idCreation > -1) return booking.idCreation;
			if(booking.idBooking != null) return booking.idBooking;
			return booking.id;
		}

		AccountManager.prototype.getBooking = function(idBooking){
			for(var i = 0 ; i < this.customer.bookings.length; i++){
				var booking = this.customer.bookings[i];
				if(booking.id == idBooking || booking.idCreation == idBooking){
					return booking;
				}
			}
			return null;
		}

		AccountManager.prototype.setBooking = function(booking){
			this.booking = booking;
			if(this.booking != null){
				if(booking.quotation) this.seeQuotations();
				else this.seeBookings();
			}
		}

		AccountManager.prototype.isBookingLoaded = function(){
			return this.booking != null;
		}

		AccountManager.prototype.getIntervals = function(booking){
			var intervals = null;
			for(var i = 0; i < booking.appointments.length; i++){
				var appointment = booking.appointments[i];
				intervals = this.addInterval(intervals, appointment.startDate, appointment.endDate, appointment.state);
			}
			return intervals;
		}


		AccountManager.prototype.askAdvancePayment = function(){
			return this.settings.payment_ask_advance_payment && this.settings.payment_ask_advance_payment_type_accounts == null || this.settings.payment_ask_advance_payment_type_accounts.length == 0 || (this.customer!=null && this.settings.payment_ask_advance_payment_type_accounts.indexOf(this.customer.typeAccount) != -1) || (this.customer == null && this.settings.payment_ask_advance_payment_type_accounts.indexOf('type_account_0') != -1);
		}

		AccountManager.prototype.canPayAdvancePayment = function(booking){
			return booking.totalPrice > booking.advancePayment;
		}

		AccountManager.prototype.alreadyAdvancedPayment = function(booking){
			return booking.needToPay != booking.totalPrice || booking.paymentState == 'advancePayment';
		}

		AccountManager.prototype.isPaymentFormOk = function(booking){
			return (!this.settings.checkbox_payment || this.settings.checkbox_payment && this.checkboxAcceptPayment) && this.typePayment != '' && booking!=null && !this.launchPayment;
		}

		AccountManager.prototype.clearBooking = function(){
			this.setBooking(null);
		}

		AccountManager.prototype.formatText = function(booking, text){
			if(text != null){
				var amount = booking.totalPrice;
				if(this.checkboxAdvancePayment){
					amount = booking.advancePayment;
				}
				text = text.replace('[RESA_advancePayment]', amount + '' + this.settings.currency);
			}
			return text;
		}

		AccountManager.prototype.payBookingPart = function(booking){
			this.setBooking(booking);
			this.getBookingDetails(booking);
		}

		AccountManager.prototype.showDetails = function(booking){
			booking.showDetails = !booking.showDetails;
			if(booking.showDetails && booking.details==null){
				this.getBookingDetails(booking);
			}
		}

		AccountManager.prototype.paymentData = function(){
			return _paymentManager.data;
		}

		AccountManager.prototype.userConnection = function(){
			var data = {
				action:'userConnection',
				login: JSON.stringify(this.customer.login),
				password: JSON.stringify(this.customer.passwordConnection)
			}

			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					data = JSON.parse(data);
					if(data instanceof Object){
						_self.setCustomer(data);
						_self.launchUpdateDataAccounts();
					}
					else sweetAlert('', data, 'error');
				});
			}).fail(function(err){

			});
		}

		AccountManager.prototype.getPlaceolder = function(text, id){
			if(this.fieldStates[id] == 1){ text += '*' }
			return text;
		}

		AccountManager.prototype.customerIsOk = function(){
			var isOk = this.checkboxCustomer && this.modelCustomer!=null && this.modelCustomer.ID != -1 &&
				 (this.modelCustomer.lastName.length > 0 || this.fieldStates['lastName'] != 1) &&
				 (this.modelCustomer.firstName.length > 0 || this.fieldStates['firstName'] != 1) &&
				 (this.modelCustomer.email!=null && this.modelCustomer.email.length > 0) &&
				 (this.modelCustomer.company.length > 0 || this.fieldStates['company'] != 1) &&
				 (this.modelCustomer.phone.length > 0 || this.fieldStates['phone'] != 1) &&
				 (this.modelCustomer.address.length > 0 || this.fieldStates['address'] != 1) &&
				 (this.modelCustomer.postalCode.length > 0 || this.fieldStates['postalCode'] != 1) &&
				 (this.modelCustomer.town.length > 0 || this.fieldStates['town'] != 1) &&
				 (this.modelCustomer.country.length > 0 || this.fieldStates['country'] != 1) &&
	 		 	 (this.modelCustomer.siret.length > 0 || this.fieldStates['siret'] != 1) &&
	 			 (this.modelCustomer.legalForm.length > 0 || this.fieldStates['legalForm'] != 1) &&
				 !this.launchModifyCustomer;
			if(isOk && this.changePassword){
				isOk = this.modelCustomer.password!=null && this.modelCustomer.password.length > 0 &&
					this.modelCustomer.confirmPassword!=null && this.modelCustomer.confirmPassword.length > 0 &&
					this.modelCustomer.password == this.modelCustomer.confirmPassword &&
					this.checkPassword(this.modelCustomer.password);
			}
			return isOk;
		}

		AccountManager.prototype.checkPassword = function(str) {
	    var re = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/;
	    return re.test(str);
	  }

		AccountManager.prototype.modifyCustomer = function(dialogTexts){
			if(this.customerIsOk()){
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
					var data = {
						action:'modifyCustomer',
						changePassword:_self.changePassword,
						customer:JSON.stringify(_self.modelCustomer)
					}
					_scope.$apply(function(){	_self.launchModifyCustomer = true; });
					jQuery.post(_ajaxUrl, data, function(data) {
						_scope.$apply(function(){
							_self.launchModifyCustomer = false;
							data = JSON.parse(data);
							if(data.response == 'success'){
								_self.customer = data.customer;
								_self.modelCustomer = JSON.parse(JSON.stringify(_self.customer));
								_self.changePassword = false;
								sweetAlert('', 'Ok', 'success');
							}
							else {
								sweetAlert('', data.error, 'error');
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

		AccountManager.prototype.displayBookingBill = function(booking){
			if(booking.bill == null){
				this.getBill(booking);
			}
			var resultModal = _uibModal.open({
			  animation: true,
			  templateUrl: 'displayBill.html',
			  controller: 'ModalBillDisplayController as displayCtrl',
			  size: 'lg',
			  resolve: {
				booking : function(){
					return booking;
				},
			  }
			});

			resultModal.result.then(function (data) {

			}, function () {

			});
		}



		AccountManager.prototype.getBookingDetails = function(booking){
			if(booking != null && booking.details == null){
				var data = {
					action:'getBookingDetails',
					idBooking: JSON.stringify(booking.id)
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						booking.details = data;
					});
				}).fail(function(err){

				});
			}
		}

		AccountManager.prototype.getBill = function(booking){
			var data = {
				action:'getBill',
				idBooking: JSON.stringify(booking.id)
			}
			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					booking.bill = data;
				});
			}).fail(function(err){

			});
		}

		AccountManager.prototype.payBooking = function(booking){
			if(this.isPaymentFormOk(booking)) {
				this.launchPayment = true;
				var data = {
					action:'payBooking',
					typePayment: JSON.stringify(this.typePayment.id),
					idBooking: JSON.stringify(booking.id),
					advancePayment: JSON.stringify(this.checkboxAdvancePayment),
					currentUrl:  JSON.stringify(_currentUrl)
				}

				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						 _self.launchPayment = false;
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
								else if(data.payment.type == 'swikly')
									_paymentManager.swikly(data.payment);
								else {
									_paymentManager.setData(data.payment);
								}
							}
							else {
								sweetAlert('', 'error', 'error');
							}
						}
						else if(data instanceof Object && data.isError){
							sweetAlert('', data.message, 'error');
						}
						else sweetAlert('', data, 'error');
					});
				}).fail(function(err){
					_scope.$apply(function(){ _self.launchPayment = false; });
					sweetAlert('', 'error', 'error');
				});
			}
		}

		AccountManager.prototype.acceptQuotation = function(booking, dialogTexts, successText){
			if(!this.launchAcceptQuotation){
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
					var data = {
						action:'acceptQuotation',
						idBooking:JSON.stringify(booking.id)
					}
					_scope.$apply(function(){	_self.launchAcceptQuotation = true; });
					jQuery.post(_ajaxUrl, data, function(data) {
						_scope.$apply(function(){
							_self.launchAcceptQuotation = false;
							data = JSON.parse(data);
							if(data.response == 'success'){
								_self.removeBookingById(data.oldIdBooking);
								data.booking.intervals = _self.getIntervals(data.booking);
								_self.customer.bookings.push(data.booking);
								if(_self.isBookingLoaded()){
									_self.setBooking(data.booking);
								}
								_self.seeBookings();
								sweetAlert('', successText, 'success');
							}
							else {
								sweetAlert('', data.message, 'error');
							}
						});
					}).fail(function(err){
						_scope.$apply(function(){ _self.launchAcceptQuotation = false; });
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					}).error(function(err){
						_scope.$apply(function(){ _self.launchAcceptQuotation = false; });
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					});
	  		});
			}
		}

		AccountManager.prototype.refuseQuotation = function(booking, dialogTexts, successText){
			if(!this.launchRefuseQuotation){
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
					var data = {
						action:'refuseQuotation',
						idBooking:JSON.stringify(booking.id)
					}
					_scope.$apply(function(){	_self.launchRefuseQuotation = true; });
					jQuery.post(_ajaxUrl, data, function(data) {
						_scope.$apply(function(){
							_self.launchRefuseQuotation = false;
							data = JSON.parse(data);
							if(data.response == 'success'){
								_self.removeBookingById(data.oldIdBooking);
								sweetAlert('', successText, 'success');
							}
							else {
								sweetAlert('', data.message, 'error');
							}
						});
					}).fail(function(err){
						_scope.$apply(function(){ _self.launchRefuseQuotation = false; });
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					}).error(function(err){
						_scope.$apply(function(){ _self.launchRefuseQuotation = false; });
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					});
	  		});
			}
		}

		AccountManager.prototype.userDeconnection = function(){
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

		AccountManager.prototype.fotgottenPassword = function(dialogTexts){
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
				jQuery.post(_ajaxUrl, data, function(data) {
					var json = JSON.parse(data);
					sweetAlert('', json.message, json.status);
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			});
		}

		AccountManager.prototype.getLastModificationDateBooking = function(){
			var lastModificationDateBooking = null;
			if(this.customer.bookings.length > 0){
				for(var i = 0; i < this.customer.bookings.length; i++){
					var booking = this.customer.bookings[i];
					var modificationDate = $filter('parseDate')(booking.modificationDate);
					if(lastModificationDateBooking == null || modificationDate > lastModificationDateBooking){
						lastModificationDateBooking = new Date(modificationDate);
					}
				}
			}
			else {
				lastModificationDateBooking = new Date();
			}
			return lastModificationDateBooking;
		}

		/**
		 *
		 */
		AccountManager.prototype.updateDataAccounts = function(){
			if(!this.updateDataAccountsLaunched && this.isConnected()){
				this.updateDataAccountsLaunched = true;
				var lastModificationDateBooking = $filter('formatDateTime')(this.getLastModificationDateBooking());
				var data = {
					action:'updateDataAccounts',
					lastModificationDateBooking:JSON.stringify(lastModificationDateBooking)
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						var bookings = data.bookings;
						for(var i = 0; i < bookings.length; i++){
							var booking = bookings[i];
							var arrayOldBookings = booking.linkOldBookings.split(',');
							for(var j = 0; j < arrayOldBookings.length; j++){
								_self.removeBookingById(arrayOldBookings[j]);
							}
							_self.updateBooking(booking);
						}
						_self.updateDataAccountsLaunched = false;
						_self.launchUpdateDataAccounts();
					});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					_scope.$apply(function(){ _self.updateDataAccountsLaunche = false; });
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					_scope.$apply(function(){ _self.updateDataAccountsLaunche = false; });
				});
			}
		}

		/**
		 *
		 */
		AccountManager.prototype.launchUpdateDataAccounts = function(){
			if(this.timeoutUpdateDataAccounts){
				clearTimeout(this.timeoutUpdateDataAccounts);
			}
			this.timeoutUpdateDataAccounts = setTimeout(function(){
				this.updateDataAccounts();
			}.bind(this), 15000);
		}

		/**
		 *
		 */
		AccountManager.prototype.removeBookingById = function(idBooking){
			var newBookings = [];
			for(var i = 0; i < this.customer.bookings.length; i++){
				var booking = this.customer.bookings[i];
				if(booking.id != idBooking){
					newBookings.push(booking);
				}
			}
			this.customer.bookings = newBookings;
		}

		/**
		 * update booking and delete oldBooking
		 */
		AccountManager.prototype.updateBooking = function(newBooking){
			newBooking.intervals = _self.getIntervals(newBooking);
			var found = false;
			for(var i = 0; i < this.customer.bookings.length; i++){
				if(this.customer.bookings[i].id == newBooking.id){
					found = true;
					this.customer.bookings[i] = newBooking;
				}
			}
			if(!found){
				this.customer.bookings.push(newBooking);
			}
		}

		AccountManager.prototype.facebookConnected = function(data){
			this.customer.lastName = data.last_name;
			this.customer.firstName =data.first_name;
			this.customer.email = data.email;
			this.customer.idFacebook = data.id;

			this.userConnectionFacebook(this.customer.idFacebook, this.customer.email, true, function(customer){
				_scope.$apply(function(){
					_self.setCustomer(customer);
				});
			})
		}

		return AccountManager;
	}


	angular.module('resa_app').factory('AccountManager', AccountManagerFactory)
}());
