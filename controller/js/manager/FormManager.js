"use strict";

(function(){

	var _membersByService = [];
	/** TODO maybe use AppointmentManager **/
	var getServiceById = function(idService, services){
		for(var i = 0; i < services.length; i++){
			if(services[i].id == idService)
				return services[i];
		}
		return null;
	}

	/** TODO maybe use AppointmentManager **/
	var getPriceById = function(idPrices, service){
		for(var i = 0; i < service.servicePrices.length; i++){
			if(service.servicePrices[i].id == idPrices)
				return service.servicePrices[i];
		}
		return null;
	}

	var getMemberById = function(idMember, members){
		for(var i = 0; i < members.length; i++){
			if(members[i].id == idMember)
				return members[i];
		}
		return null;
	}

	var _generateMembersByService = function(id, members){
		_membersByService[id] = [];
		for(var i = 0; i < members.length; i++){
			var member = members[i];
			var found = false;
			for(var memberIndexRule = 0 ; memberIndexRule < member.memberAvailabilities.length; memberIndexRule++){
				var memberAvailability =  member.memberAvailabilities[memberIndexRule];
				for(var memberIndexService = 0; memberIndexService <memberAvailability.memberAvailabilityServices.length;  memberIndexService++){
					var memberAvailabilityService = memberAvailability.memberAvailabilityServices[memberIndexService];
					if(memberAvailabilityService){
						found = found || (memberAvailabilityService.idService == id);
					}
				}
			}
			if(found) {
				_membersByService[id].push(member);
			}
		}
	}
	var _basket = null;
	var _self = null;
	var _paymentManager = null;
	var _couponsList = [];
	var _currentUrl = null;
	var _callbackSuccessAdd = null;
	var _callbackErrorAdd = null;
	var _scope = null;
	var _storage = null;

	function FormManagerFactory($log, $window, $filter, $http, FunctionsManager, ServiceParameters, PaymentManagerForm, StorageManager, Basket, Timeslot){

		var FormManager = function(){
			FunctionsManager.call(this);
			angular.extend(FormManager.prototype, FunctionsManager.prototype);
			this.ajaxUrl = null;
			this.allMonths = null;
			this.launchValidForm = false;

			this.redirect = false;
			this.settings = null;
			this.customerName = null;
			this.customer = null;
			this.launchUserConnexion = false;

			this.bookingLoadedIsModified = false;

			this.booking = null;
			this.recapBookingServicesParameters = [];
			this.servicesInBooking = [];
			this.reductionsInBooking = [];
			this.mapIdClientObjectReductionBooking =  [];

			this.currentDate = new Date();
			this.currentTimeslots = [];
			this.currentGetTimeslotsLaunched = false;

			this.services = [];
			this.members = [];
			this.currentPlace = null;
			this.currentCategory = null;
			this.currentService = null;
			this.serviceParameters = null;
			this.servicesParameters = [];
			this.typePayment = null;
			this.checkboxAdvancePayment = false;
			this.checkboxAcceptPayment = false;
			this.formEnd = false;
			this.paymentsTypeList = [];

			this.getReductionsLaunched = false;
			this.relanchGetReductions = false;
			this.reductions = [];
			this.mapIdClientObjectReduction =  [];
			this.customReductions = [];
			this.bookingNote = '';
			this.bookingPublicNote = '';
			this.bookingStaffNote = '';
			this.bookingCustomerNote = '';
			this.sendEmailToCustomer = false;
			this.signs = [{title:'-', value:-1},{title:'+', value:1}];
			this.currentCustomReduction = {description: '', sign: -1, amount: 0, vatValue:0};
			this.idServicesParametersError = null;
			this.quotation = false;
			this.allowInconsistencies = false;
			this.frontForm = true;
			this.idForm = '';
			this.confirmationText = '';

			this.months = [];
			this.monthIndex = 0;

			this.currentPromoCode = '';
			this.errorMessages = {};

			_paymentManager = new PaymentManagerForm();
			_storage = new StorageManager('resa');
			_basket = new Basket();

			_self = this;
		}

		FormManager.prototype.initialize = function(allServices, services, members, customer, settings, paymentsTypeList, ajaxUrl, currentUrl, scope, callback, badCallback){
			this.launchValidForm = false;
			this.bookingLoadedIsModified = false;
			this.currentGetTimeslotsLaunched = false;
			this.allServices = allServices;
			this.services = services;
			this.members = members;
			this.customer = customer;
			this.launchUserConnexion = false;
			this.settings = settings;
			this.allowInconsistencies = false;
			this.paymentsTypeList = paymentsTypeList;
			this.mapIdClientObjectReduction =  [];
			this.customReductions = [];
			this.bookingNote = '';
			this.bookingPublicNote = '';
			this.bookingStaffNote = '';
			this.bookingCustomerNote = '';
			this.quotation = false;
			if(settings.isQuotation){
				this.quotation = true;
			}
			this.sendEmailToCustomer = false;
			this.currentCustomReduction = {description: '', sign: -1, amount: 0, vatValue:0};
			this.idServicesParametersError = null;
			this.currentPromoCode = '';
			_couponsList = [];

			for(var i = 0; i <this.paymentsTypeList.length; i++){
				if(this.paymentsTypeList[i].activated){
					this.typePayment = this.paymentsTypeList[i];
					break;
				}
			}
			this.ajaxUrl = ajaxUrl;
			_scope = scope;
			_currentUrl = currentUrl;
			_callbackSuccessAdd = callback;
			_callbackErrorAdd = badCallback;
			this.generateMembersByService();

			var object = _storage.load();
			if(object != null){
				var allServiceParameters = object.basket;
				var lastUpdate = new Date(object.date);
				var actualDate = new Date();
				if(allServiceParameters != null && ((actualDate.getTime() - lastUpdate.getTime())/1000) <= 3600){
					if(object.couponsList!=null) _couponsList = object.couponsList;
					this.loadBasket(allServiceParameters);
				}
			}
			if(this.services.length > 0){
				this.setCurrentService(this.services[0]);
				this.currentService = null;
			}
		}

		FormManager.prototype.loadBasket = function(allServiceParameters){
			if(allServiceParameters.length > 0){
				for(var i = 0; i < allServiceParameters.length; i++){
					var serviceParameters = new ServiceParameters();
					serviceParameters.fromServiceParametersJSON(allServiceParameters[i]);
					var service = getServiceById(serviceParameters.service.id, this.allServices);
					if(!serviceParameters.deprecatedServiceParameters(service)){
						_basket.addServiceParameters(serviceParameters, true);
					}
				}
				this.servicesParameters = _basket.getAllServicesParameters();
				this.getReductions('');
			}
		}

		/**
		 *
		 */
		FormManager.prototype.updateBooking = function(addInBasket){
			if(addInBasket == null){
				addInBasket = false;
			}
			if(this.booking != null){
				_basket.clear();
				this.quotation = this.booking.quotation;
				var allServiceParameters = [];
				var mapIdClientObjectReductionBooking = [];
				for(var i = 0; i < this.booking.appointments.length ; i++){
					var appointment = this.booking.appointments[i];
					var serviceParameters = new ServiceParameters();
					var service = getServiceById(appointment.idService, this.servicesInBooking);
					if(service != null){
						serviceParameters.id = _basket.getNextId();
						serviceParameters.idAppointment = appointment.id;
						serviceParameters.place = appointment.idPlace;
						serviceParameters.service = service;
						serviceParameters.dateStart = $filter('parseDate')(appointment.startDate);
						serviceParameters.dateEnd = $filter('parseDate')(appointment.endDate);
						serviceParameters.noEnd = appointment.noEnd;
						serviceParameters.tags = appointment.tags;
						serviceParameters.idServiceParametersLink = appointment.internalIdLink;
						serviceParameters.state = appointment.state;
						serviceParameters.idParameter = appointment.idParameter;
						serviceParameters.numberPrices = [];
						for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
							var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
							var price = getPriceById(appointmentNumberPrice.idPrice, service);
							if(price != null){
								serviceParameters.pushInNumberPrice(price, appointmentNumberPrice.number, appointmentNumberPrice.participants);
							}
						}
						if(appointment.appointmentReductions.length > 0){
							mapIdClientObjectReductionBooking['id' + serviceParameters.id] = [];
							var alreadyInserted = [];
							for(var j = 0; j < appointment.appointmentReductions.length; j++){
								var appointmentReductions = JSON.parse(JSON.stringify(appointment.appointmentReductions[j]));
								if(alreadyInserted.indexOf(appointmentReductions.id) == -1){
									mapIdClientObjectReductionBooking['id' + serviceParameters.id].push(appointmentReductions);
								}
								if(appointmentReductions.promoCode!=null && appointmentReductions.promoCode.length > 0 && _couponsList.indexOf(appointmentReductions.promoCode) == -1){
									_couponsList.push(appointmentReductions.promoCode);
								}
								if(appointmentReductions.idsPrice == null){
									appointmentReductions.idsPrice = [];
								}
								if(appointmentReductions.idPrice != -1){
									appointmentReductions.idsPrice.push(appointmentReductions.idPrice + '');
								}
							}
						}
						if(appointment.appointmentMembers.length > 0){
							for(var j = 0; j < appointment.appointmentMembers.length; j++){
								var appointmentMember = appointment.appointmentMembers[j];
								var member = getMemberById(appointmentMember.idMember, this.members);
								if(member != null){
									serviceParameters.staffs.push({
										id: appointmentMember.idMember,
										nickname: member.nickname,
										capacity: appointmentMember.number,
										usedCapacity: 0
									});
								}
							}
						}
						serviceParameters.maxCapacity = serviceParameters.getNumber();
						if(addInBasket){
							this.serviceParameters = serviceParameters;
							this.addBasket(true);
						}
						allServiceParameters.push(serviceParameters);
					}
				}
				mapIdClientObjectReductionBooking['id0'] = [];
				for(var i = 0; i < this.booking.bookingReductions.length; i++){
					var bookingReduction = this.booking.bookingReductions[i];
					if(bookingReduction.promoCode!=null && bookingReduction.promoCode.length > 0 && _couponsList.indexOf(bookingReduction.promoCode) == -1){
						_couponsList.push(bookingReduction.promoCode);
					}
					mapIdClientObjectReductionBooking['id0'].push(JSON.parse(JSON.stringify(bookingReduction)));
				}
				if(allServiceParameters.length > 0){
					this.recapBookingServicesParameters = allServiceParameters;
					$log.debug(mapIdClientObjectReductionBooking);
					this.mapIdClientObjectReductionBooking = mapIdClientObjectReductionBooking;
				}
				if(addInBasket){
					this.reductions = this.reductionsInBooking;
					this.mapIdClientObjectReduction = mapIdClientObjectReductionBooking;
				}
				for(var i = 0; i < this.booking.bookingCustomReductions.length; i++){
					this.customReductions.push(JSON.parse(JSON.stringify(this.booking.bookingCustomReductions[i])));
				}
				this.bookingNote = this.htmlSpecialDecode(this.booking.note);
				this.bookingPublicNote = this.htmlSpecialDecode(this.booking.publicNote);
				this.bookingStaffNote = this.htmlSpecialDecode(this.booking.staffNote);
				this.bookingCustomerNote = this.htmlSpecialDecode(this.booking.customerNote);
			}
		}

		FormManager.prototype.haveExtras = function(service){
			for(var i = 0; i < service.servicePrices.length; i++){
				var servicePrice = service.servicePrices[i];
				if(servicePrice.extra && servicePrice.slug != service.defaultSlugServicePrice) return true;
			}
			return false;
		}

		FormManager.prototype.setCurrentService = function(service){
			this.currentService = service;
			this.serviceParameters = new ServiceParameters();
			this.serviceParameters.id = _basket.getNextId();
			if(service !=null){
				if(this.currentPlace != null){
					this.serviceParameters.place = this.currentPlace.id;
				}
				this.serviceParameters.setService(service);
				if(this.noHaveCategory(service)){
					this.currentCategory = null;
				}
				this.calculateMonth(service);
				var date = new Date(this.currentDate);
				var startBookingDate = $filter('parseDate')(service.startBookingDate);
				if(date < startBookingDate || this.getAllServicesParameters().length == 0){
					date = new Date(startBookingDate);
				}
				this.setCurrentDate(date);
				if(service.typeAvailabilities == 1){
					var groupDates = this.getGroupDates();
					if(groupDates.length > 0){
						this.setGroupDates(groupDates[0]);
					}
				}
				else if(service.typeAvailabilities == 2){
					var manyDates = this.getManyDates();
					if(manyDates.length > 0){
						this.setManyDates(manyDates[0]);
					}
				}
			}
		}

		FormManager.prototype.setCurrentDate = function(date){
			this.currentDate = date;
			this.monthIndex = this.currentDate.getMonth();
			this.getTimeslots(this.currentDate);
			if(this.serviceParameters != null){
				this.serviceParameters.clearDates();
			}
		}

		FormManager.prototype.reloadTimeslots = function(){
			return this.getTimeslots(this.currentDate);
		}


		FormManager.prototype.calculateMonth = function(service){
			this.months = [];
			if(service.typeAvailabilities == 1){
				var actualDate = new Date();
				for(var i = 0; i < this.allMonths.length; i++){
					var date = new Date();
					date.setMonth(i + 1, 0);
					if(date.getTime() < actualDate.getTime()){
						date.setFullYear(date.getFullYear() + 1);
					}
					if(this.haveGroupDates(date, service)){
						this.months.push({
							index: i,
							date:date
						});
					}
				}
				this.months.sort(function(monthA, monthB){
					return monthA.date.getTime() - monthB.date.getTime();
				});
			}
		}


		FormManager.prototype.changeMonthOfDate = function(){
			this.currentDate.setMonth(this.monthIndex);
		}

		FormManager.prototype.addMonth = function(monthIndex){
			this.currentDate.setMonth(this.currentDate.getMonth() + monthIndex);
			this.monthIndex = this.currentDate.getMonth();
		}

		FormManager.prototype.haveGroupDates = function(month, service){
			var serviceAvailabilities = service.serviceAvailabilities;
			var startDate = $filter('parseDate')(service.startBookingDate);
			startDate = this.clearTime(startDate);
			var groupDates = [];
			for(var i = 0; i < serviceAvailabilities.length; i++){
				var serviceAvailability = serviceAvailabilities[i];
				for(var j = 0; j < serviceAvailability.groupDates.length; j++){
					var dates = serviceAvailability.groupDates[j];
					var date =  $filter('parseDate')(dates.startDate);
					var localEndDate = $filter('parseDate')(dates.endDate);
					if((month.getMonth() == date.getMonth() && month.getFullYear() == date.getFullYear())  ||
							(month.getMonth() == localEndDate.getMonth() && month.getFullYear() == localEndDate.getFullYear())){
						groupDates.push(dates);
					}
				}
			}
			return groupDates.length > 0;
		}

		FormManager.prototype.getGroupDates = function(){
			var serviceAvailabilities = this.serviceParameters.service.serviceAvailabilities;
			var startDate = $filter('parseDate')(this.serviceParameters.service.startBookingDate);
			startDate = this.clearTime(startDate);
			var groupDates = [];
			for(var i = 0; i < serviceAvailabilities.length; i++){
				var serviceAvailability = serviceAvailabilities[i];
				for(var j = 0; j < serviceAvailability.groupDates.length; j++){
					var dates = serviceAvailability.groupDates[j];
					var date =  $filter('parseDate')(dates.startDate);
					var localEndDate = $filter('parseDate')(dates.endDate);
					if((startDate.getTime() <= date.getTime() || this.allowInconsistencies) && (this.currentDate.getMonth() == date.getMonth() || this.currentDate.getMonth() == localEndDate.getMonth())){
						groupDates.push(dates);
					}
				}
			}
			if(groupDates.length > 0){
				groupDates.sort(function(datesA, datesB){
					return this.parseDate(datesA.startDate).getTime() - this.parseDate(datesB.startDate).getTime();
				}.bind(this));
			}
			return groupDates;
		}

		FormManager.prototype.setGroupDates = function(dates){
			this.currentDate = $filter('parseDate')(dates.startDate);
			var endDate = $filter('parseDate')(dates.endDate);
			this.serviceParameters.days =  parseInt(Math.ceil((endDate.getTime() - 	this.currentDate.getTime()) / ( 24 * 3600 * 1000)));
			this.getTimeslots(this.currentDate);
		}

		FormManager.prototype.isSelectedGroupDates = function(dates){
			var date = $filter('parseDate')(dates.startDate);
			return this.currentDate.getTime() == date.getTime();
		}

		FormManager.prototype.getEndDateDay = function(){
			var dateEnd = new Date(this.currentDate);
			dateEnd.setDate(dateEnd.getDate() + this.serviceParameters.days);
			return dateEnd;
		}

		FormManager.prototype.getManyDates = function(){
			var serviceAvailabilities = this.serviceParameters.service.serviceAvailabilities;
			var startDate = $filter('parseDate')(this.serviceParameters.service.startBookingDate);
			startDate = this.clearTime(startDate);
			var manyDates = [];
			for(var i = 0; i < serviceAvailabilities.length; i++){
				var serviceAvailability = serviceAvailabilities[i];
				for(var j = 0; j < serviceAvailability.manyDates.length; j++){
					var dates = serviceAvailability.manyDates[j];
					var array = dates.split(',');
					var tokens = array[0].split('-');
					var firstDate = new Date(tokens[0], tokens[1] - 1, tokens[2]);
					if(firstDate >= new Date()){
						manyDates = manyDates.concat(dates);
					}
				}
			}
			return manyDates;
		}

		FormManager.prototype.setManyDates = function(dates){
			var array = dates.split(',');
			var tokens = array[0].split('-');
			this.currentDate = new Date(tokens[0], tokens[1] - 1, tokens[2]);
			this.serviceParameters.manyDates =  array;
			this.getTimeslots(this.currentDate);
		}

		FormManager.prototype.isSelectedManyDates = function(dates){
			return this.serviceParameters.manyDates.toString() == dates.toString();
		}

		FormManager.prototype.needSelectPlace = function(){
			return this.getPlaces().length > 0;
		}

		FormManager.prototype.getTagById = function(idTag){
			if(this.settings != null && this.settings.appointmentTags!=null){
				for(var i = 0; i < this.settings.appointmentTags.length; i++){
	    		if(this.settings.appointmentTags[i].id == idTag){
	          return this.settings.appointmentTags[i];
	        }
	    	}
			}
    	return null;
    }

		FormManager.prototype.getCategories = function(){
			if(this.settings.form_category_services == null || this.settings.form_category_services=='' || this.settings.form_category_services==false){
				return [];
			}
			return this.settings.form_category_services;
		}

		FormManager.prototype.getPlaces = function(){
			if(this.settings == null || this.settings.places == null || this.settings.places=='' || this.settings.places==false){
				return [];
			}
			var places = [];
			for(var i = 0; i < this.settings.places.length; i++){
				var place = this.settings.places[i];
				for(var j = 0; j < this.services.length; j++){
					var service = this.services[j];
					if(service.places.indexOf(place.id) != -1){
						places.push(place);
						break;
					}
				}
			}
			return places;
		}

		FormManager.prototype.getPlaceById = function(idPlace){
			if(this.settings != null){
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

		FormManager.prototype.getServiceById = function(idService){
    	for(var i = 0; i < this.services.length; i++){
    		if(this.services[i].id == idService){
          return this.services[i];
        }
    	}
    	return null;
    }

		FormManager.prototype.getServicesByCategory = function(category){
			var services = [];
			if(category != null){
				var allServicesByPlace = this.getServicesByPlace();
				for(var i = 0; i < allServicesByPlace.length; i++){
					var service = allServicesByPlace[i];
					if(category.id == service.category){
						services.push(service);
					}
				}
			}
			return services;
		}

		FormManager.prototype.getServicesByPlace = function(){
			if(this.currentPlace != null){
				var services = [];
				for(var i = 0; i < this.services.length; i++){
					var service = this.services[i];
					if(service.places.indexOf(this.currentPlace.id) != -1){
						services.push(service);
					}
				}
				return services;
			}
			else return this.services;
		}

		FormManager.prototype.getMembersByPlace = function(){
			if(this.currentPlace != null){
				var members = [];
				for(var i = 0; i < this.members.length; i++){
					var member = this.members[i];
					if(member.places.length == 0 || member.places.indexOf(this.currentPlace.id) != -1){
						members.push(member);
					}
				}
				return members;
			}
			else return this.members;
		}


		FormManager.prototype.filterByCategory = function(service){
			if(_self.getCategories().length == 0) return true;
			if(_self.currentCategory == null || _self.currentCategory == {}) return false;
			else if(_self.currentCategory.id == service.category){
				return true;
			}
			return false;
		}

		FormManager.prototype.getMentionById = function(idMention){
			return this.settings.timeslots_mentions.find(element => element.id == idMention);
		}

		FormManager.prototype.numberOfServices = function(category){
			return this.getServicesByCategory(category).length;
		}

		FormManager.prototype.setCurrentPlace = function(place){
			this.currentPlace = place;
			this.currentService = (null);
		}

		FormManager.prototype.setCurrentCategory = function(category){
			this.currentCategory = category;
			if(this.numberOfServices(category) == 1){
				for(var i = 0; i < this.services.length; i++){
					var service = this.services[i];
					if(service.category == category.id){
						this.setCurrentService(service);
						break;
					}
				}
			}
			else {
				this.currentService = (null);
			}
		}

		FormManager.prototype.noHaveCategory = function(service){
			return service == null || service.category == null || service.category.length == 0;
		}

		FormManager.prototype.getParameter = function(idParameter){
			if(this.settings != null && this.settings.states_parameters != null){
				for(var i = 0; i < this.settings.states_parameters.length; i++){
					if(this.settings.states_parameters[i].id == idParameter){
						return this.settings.states_parameters[i];
					}
				}
			}
			return null;
		}

		FormManager.prototype.getParticipantsParameter = function(idParameter){
			if(this.settings!=null && this.settings.form_participants_parameters != null){
				for(var i = 0; i < this.settings.form_participants_parameters.length; i++){
					if(this.settings.form_participants_parameters[i].id == idParameter){
						return this.settings.form_participants_parameters[i];
					}
				}
			}
			return null;
		}

		FormManager.prototype.getParticipantFieldName = function(participant, field, locale){
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

		FormManager.prototype.setTimeslot = function(timeslot, serviceParameters){
			if(serviceParameters == null) serviceParameters =  this.serviceParameters;
      serviceParameters.dateStart = timeslot.dateStart;
      serviceParameters.dateEnd = timeslot.dateEnd;
      serviceParameters.noEnd = timeslot.noEnd;
			serviceParameters.capacityTimeslot = timeslot.getCapacity();
			serviceParameters.customTimeslot = timeslot.isCustom;
      if(JSON.stringify(serviceParameters.idsServicePrices) != JSON.stringify(timeslot.idsServicePrices) && !this.allowInconsistencies){
        serviceParameters.clearNumberPriceNumber();
      }
      serviceParameters.idsServicePrices = timeslot.idsServicePrices;
			serviceParameters.idParameter = timeslot.idParameter;
      serviceParameters.maxCapacity = timeslot.getCapacity();
			if(timeslot.overCapacity){
				serviceParameters.maxCapacity = 9999;
			}
      serviceParameters.membersUsed = timeslot.membersUsed;
      serviceParameters.mergeMembersUsedWithMembers(this.getMembersByPlace());
      for(var i = 0; i < serviceParameters.staffs.length; i++){
        var staff = serviceParameters.staffs[i];
        for(var j = 0; j < serviceParameters.membersUsedWithMembers.length; j++){
          var memberUsed = serviceParameters.membersUsedWithMembers[j];
          if(staff.id == memberUsed.id){
            serviceParameters.staffs[i] = memberUsed;
          }
        }
      }
      serviceParameters.equipments = timeslot.equipments;
			serviceParameters.equipmentsActivated = timeslot.equipmentsActivated;
    }

		FormManager.prototype.needSelectParticipants = function(){
			var allServiceParameters = this.getAllServicesParameters();
			for(var i = 0; i < allServiceParameters.length; i++){
				var serviceParameters = allServiceParameters[i];
				if(serviceParameters.needSelectParticipants()) return true;
			}
			return false;
		}

		FormManager.prototype.participantsIsSelected = function(){
			var allServiceParameters = this.getAllServicesParameters();
			for(var i = 0; i < allServiceParameters.length; i++){
				var serviceParameters = allServiceParameters[i];
				if(!serviceParameters.participantsIsSelected(this.settings.form_participants_parameters)) return false;
			}
			return true;
		}

		FormManager.prototype.getParticipants = function(serviceParameters){
			var participants = [];
			if(this.customer!= null){
				for(var i = 0; i < this.customer.participants.length; i++){
					participants.push(this.customer.participants[i]);
				}
			}
			var anotherParticipants = _basket.getParticipants(serviceParameters);
			var localParticipants = serviceParameters.getParticipants();
			for(var k = 0; k < localParticipants.length; k++){
				var participant = localParticipants[k];
				if(anotherParticipants.indexOf(localParticipants) == -1){
					anotherParticipants.push(participant);
				}
			}
			if(this.customer != null){
				for(var i = 0; i < anotherParticipants.length; i++){
					var participant = anotherParticipants[i];
					if(participant != null){
						var found = false;
						var j = 0;
						while(!found && j < participants.length){
							var participantAux = participants[j];
							found = participantAux.lastname == participant.lastname && participantAux.firstname == participant.firstname;
							j++;
						}
						if(!found){
							participants.push(participant);
						}
					}
				}
			}
			else return anotherParticipants;
			return participants;
		}

		FormManager.prototype.openParticipantSelectorDialog = function(participants, participantParameters, serviceParameters, numberPrice, index, notAllSynchro){
			if(this.participantSelectorCtrl != null){
				this.participantSelectorCtrl.initialize(participants, participantParameters, serviceParameters, numberPrice, index, notAllSynchro)
				this.participantSelectorCtrl.opened = true;
			}
		}

		FormManager.prototype.setDefaultParticipants = function(numberPrice){
      if(numberPrice.participants == null) numberPrice.participants = [];
      for(var i = 0; i < numberPrice.number; i++){
        if(numberPrice.participants[i] == null){
          numberPrice.participants[i] = {
            lastname:'',
            firstname:''
          };
        }
        if((numberPrice.participants[i].lastname == null || numberPrice.participants[i].lastname.length == 0) && this.customer != null){
          numberPrice.participants[i].lastname = this.customer.lastName;
        }
      }
    }

    FormManager.prototype.setAllDefaultParticipants = function(){
      for(var i = 0; i < this.serviceParameters.numberPrices.length; i++){
        this.setDefaultParticipants(this.serviceParameters.numberPrices[i]);
      }
    }

		FormManager.prototype.getFirstSelectOption = function(value, options){
			if(options!=null && options.length == 1 && (value == null || value.length == 0)) {
				return options[0].id;
			}
			return value;
		}

		FormManager.prototype.getTypeAccountOfCustomer = function(){
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

		FormManager.prototype.displayPriceListWithTypeAccount = function(servicePrice){
			return servicePrice.typesAccounts == null || servicePrice.typesAccounts.length == 0 || (servicePrice.private && !this.frontForm) || (this.customer!=null && servicePrice.typesAccounts.indexOf(this.customer.typeAccount) != -1) || (this.customer == null && servicePrice.typesAccounts.indexOf('type_account_0') != -1);
		}

		FormManager.prototype.generateMembersByService = function(){
			for(var i =0; i < this.services.length;i++){
				var service = this.services[i];
				_generateMembersByService(service.id, this.getMembersByPlace());
			}
		}

		FormManager.prototype.getStaff = function($index){
			if(!this.allowInconsistencies) return this.serviceParameters.getMemberUsed($index);
			else return this.serviceParameters.getMemberUsedMembers($index);
		}

		FormManager.prototype.getRealTotalMax = function(){
			return this.getRealMax(this.serviceParameters, -1);
		}

		FormManager.prototype.getRealMax = function(serviceParameters, index){
			var max = serviceParameters.maxCapacity - serviceParameters.getNumberWithoutIndex(index);
			if(this.allowInconsistencies){
				max = 9999;
			}
			return max;
		}

		FormManager.prototype.getLocalMaxNumberPrice = function(numberPrice, serviceParameters, index){
			var max = serviceParameters.getMaxNumberPrice(numberPrice, this.getRealMax(serviceParameters, index));
			if(this.allowInconsistencies){
				max = 9999;
			}
			return max;
		}

		FormManager.prototype.getMembersAssociatedByService = function(id){
			return _membersByService[id];
		}

		FormManager.prototype.updateServicesParameters = function(promoCode){
			this.servicesParameters = _basket.getAllServicesParameters();
			this.saveInStorage();
			this.getReductions(promoCode);
		}

		FormManager.prototype.isQuotation = function(){
			return this.quotation;
		}

		FormManager.prototype.askPayment = function(){
			if(this.settings!=null && this.settings.payment_activate && !this.quotation){
				var notAskPaymentInServiceParameters = true;
				var servicesParameters = _basket.getAllServicesParameters();
				for(var i = 0; i < servicesParameters.length; i++){
					var serviceParameters = servicesParameters[i];
					if(serviceParameters.idParameter > -1){
						var parameter = this.getParameter(serviceParameters.idParameter);
						notAskPaymentInServiceParameters = notAskPaymentInServiceParameters && !parameter.paiement;
					}
					else notAskPaymentInServiceParameters = false;
				}
				return !notAskPaymentInServiceParameters;
			}
			return false;
		}

		FormManager.prototype.askAdvancePayment = function(){
			return this.settings.payment_ask_advance_payment && (this.settings.payment_ask_advance_payment_type_accounts == null || this.settings.payment_ask_advance_payment_type_accounts.length == 0 || (this.customer!=null && this.settings.payment_ask_advance_payment_type_accounts.indexOf(this.customer.typeAccount) != -1) || (this.customer == null && this.settings.payment_ask_advance_payment_type_accounts.indexOf('type_account_0') != -1));
		}

		FormManager.prototype.switchQuotation = function(){
			this.quotation = !this.quotation;
			this.bookingLoadedIsModified = true;
		}

		FormManager.prototype.recalculateReductions = function(){
			this.bookingLoadedIsModified = true;
			this.getReductions('');
		}

		FormManager.prototype.addCustomReductions = function(){
			this.bookingLoadedIsModified = true;
			this.customReductions.push({description: '', amount: 0});
		}

		FormManager.prototype.addNewCustomReductions = function(){
			this.bookingLoadedIsModified = true;
			this.currentCustomReduction.amount = this.currentCustomReduction.amount * 1;
			this.currentCustomReduction.vatValue = this.currentCustomReduction.vatValue * 1;
			this.customReductions.push(JSON.parse(JSON.stringify(this.currentCustomReduction)));
			this.currentCustomReduction = {description: '', sign: -1, amount: 0, vatValue:0};
		}

		FormManager.prototype.removeCustomReductions = function(index){
			this.customReductions.splice(index, 1);
			this.bookingLoadedIsModified = true;
		}

		FormManager.prototype.canAddInBasket = function(){
			return this.serviceParametersIsOk(this.serviceParameters);
		}

		FormManager.prototype.preAddInBasket = function(){
      if(this.canAddInBasket()){
				var newServiceParameters = new ServiceParameters();
				newServiceParameters.fromServiceParametersJSON(this.serviceParameters);
				newServiceParameters.removeNumberPriceNull();
				newServiceParameters.idServiceParametersLink = this.serviceParameters.id;
				_basket.replaceServiceParameters(newServiceParameters, true);
				for(var i = 1; i <= this.serviceParameters.days; i++){
					var newServiceParameters = new ServiceParameters();
					newServiceParameters.fromServiceParametersJSON(this.serviceParameters);
					newServiceParameters.removeNumberPriceNull();
					newServiceParameters.id += i;
					newServiceParameters.idServiceParametersLink = this.serviceParameters.id;
					newServiceParameters.addDaysToDate(i);
					_basket.replaceServiceParameters(newServiceParameters, true);
				}
				for(var i = 1; i < this.serviceParameters.manyDates.length; i++){
					var date = this.serviceParameters.manyDates[i];
					var newServiceParameters = new ServiceParameters();
					newServiceParameters.fromServiceParametersJSON(this.serviceParameters);
					newServiceParameters.removeNumberPriceNull();
					newServiceParameters.id += i;
					newServiceParameters.idServiceParametersLink = this.serviceParameters.id;
					var tokens = date.split('-');
					newServiceParameters.setTwoDates(tokens[0],(tokens[1] - 1),tokens[2]);
					_basket.replaceServiceParameters(newServiceParameters, true);
				}
				this.applyDefaultSlugServicePrice(newServiceParameters);
				this.saveInStorage();
				this.updateServicesParameters('');
			}
			else {
				this.serviceParameters.clearNumberPriceNumber();
				var newServiceParameters = new ServiceParameters();
				newServiceParameters.fromServiceParametersJSON(this.serviceParameters);
				newServiceParameters.removeNumberPriceNull();
				if(newServiceParameters.numberPrices.length == 0){
					this.removeBasketAll(this.getServiceParametersLink(newServiceParameters));
					this.updateCurrentServiceParameters(this.getServiceParametersLink(newServiceParameters));
				}
			}
    }

		FormManager.prototype.updateCurrentServiceParameters = function(serviceParameters){
			serviceParameters.applyDefaultSlugServicePrice();
			this.serviceParameters.updateServicesParameters(serviceParameters);
			if(serviceParameters.idServiceParametersLink > -1 && serviceParameters.idServiceParametersLink == serviceParameters.id){
				var servicesParameters = _basket.getAllServicesParametersByIdLink(serviceParameters.idServiceParametersLink);
				for(var i = 0; i < servicesParameters.length; i++){
					var serviceParametersAux = servicesParameters[i];
					if(serviceParameters.id != serviceParametersAux.id){
						serviceParametersAux.updateServicesParameters(serviceParameters);
					}
				}
			}
			this.updateServicesParameters('');
		}

		/**
		 * Juste add in basket
		 */
		FormManager.prototype.addServiceParametersInBasket = function(serviceParameters){
			_basket.addServiceParameters(serviceParameters);
		}

		/**
		 * execute the script to default slug service Price
		 */
		FormManager.prototype.applyDefaultSlugServicePrice = function(serviceParameters){
			serviceParameters.applyDefaultSlugServicePrice();
		}

		/**
		 * Add in list
		 */
		FormManager.prototype.addBasket = function(force){
			if(force == null) force = false;
			if(this.canAddInBasket() || force){
				var newServiceParameters = new ServiceParameters();
				newServiceParameters.fromServiceParametersJSON(this.serviceParameters);
				_basket.addServiceParameters(newServiceParameters);
				this.setCurrentService(this.currentService);
				this.serviceParameters.resetFirstNumber();
				this.saveInStorage();
				this.updateServicesParameters('');
			}
		}

		FormManager.prototype.replaceInBasket = function(){
			if(this.canAddInBasket()){
				var newServiceParameters = new ServiceParameters();
				newServiceParameters.fromServiceParametersJSON(this.serviceParameters);
				if(_basket.replaceServiceParameters(newServiceParameters, false)){
					this.bookingLoadedIsModified = true;
				}
				this.setCurrentService(this.currentService);
				this.serviceParameters.resetFirstNumber();
				this.saveInStorage();
				this.updateServicesParameters('');
			}
		}

		FormManager.prototype.replaceInBasketWithParams = function(serviceParameters){
				var newServiceParameters = new ServiceParameters();
				newServiceParameters.fromServiceParametersJSON(serviceParameters);
				if(_basket.replaceServiceParameters(newServiceParameters, false)){
					this.bookingLoadedIsModified = true;
				}
		}

		FormManager.prototype.feedbackAddBasket = function(dialogTexts){
			sweetAlert({
				title: dialogTexts.title,
				type: "success",
				timer: 2000,
				showConfirmButton: true });
		}

		FormManager.prototype.removeBasket = function(serviceParameters, index){
			if(serviceParameters.idServiceParametersLink > -1){
				var servicesParameters = _basket.getAllServicesParametersByIdLink(serviceParameters.idServiceParametersLink);
				for(var i = 0; i < servicesParameters.length; i++){
					var serviceParametersAux = servicesParameters[i];
					if(serviceParametersAux.removeNumberPrice(index)){
						this.removeBasketAll(serviceParametersAux);
					}
				}
			}
			else {
				if(serviceParameters.removeNumberPrice(index)){
					this.removeBasketAll(serviceParameters);
				}
			}
			this.reloadTimeslots();
			this.updateServicesParameters('');
			this.getReductions('');
		}

		FormManager.prototype.removeBasketAll = function(serviceParameters){
			_basket.removeServiceParametersById(serviceParameters);
			this.bookingLoadedIsModified = true;
			if(this.idServicesParametersError!=null &&
				this.idServicesParametersError.id == serviceParameters.id){
				this.idServicesParametersError = null;
			}
			this.updateServicesParameters('');
		}

		FormManager.prototype.getBasket = function(){
			return _basket.getArray();
		}

		FormManager.prototype.getNextId = function(){
			return _basket.getNextId();
		}

		FormManager.prototype.clearBasket = function(){
			_basket.clear();
			_couponsList = [];
			this.updateServicesParameters();
		}

		FormManager.prototype.getAllServicesParameters = function(){
			return _basket.getAllServicesParameters();
		}

		FormManager.prototype.getServiceParameters = function(idService, startDate, endDate){
			return _basket.getServiceParameters(idService, startDate, endDate);
		}

		FormManager.prototype.getAllServiceParametersLink = function(serviceParameters){
			return _basket.getAllServiceParametersLink(serviceParameters);
		}

		FormManager.prototype.getAllServicesParametersByIdLink = function(serviceParameters){
			return _basket.getAllServicesParametersByIdLink(serviceParameters.idServiceParametersLink);
		}

		FormManager.prototype.getServiceParametersLink = function(serviceParameters){
			if(!serviceParameters.isLink()) return serviceParameters;
			var returnServiceParameters = _basket.getServiceParametersLink(serviceParameters);
			if(returnServiceParameters == null) return serviceParameters;
			return returnServiceParameters;
		}

		FormManager.prototype.getServiceParametersLinkNumberPrice = function(serviceParameters, numberPrice){
			if(!serviceParameters.isLink()) return numberPrice;
			var returnServiceParameters = _basket.getServiceParametersLink(serviceParameters);
			if(returnServiceParameters == null)	return numberPrice;
			var anotherNumberPrice = returnServiceParameters.getNumberPriceByIdPrice(numberPrice.price.id);
			if(anotherNumberPrice == null)	return numberPrice;
			return anotherNumberPrice;
		}

		FormManager.prototype.serviceParametersIsOk = function(serviceParameters, index){
			if(index == null)
				index = -1;
			return _basket.serviceParametersIsOk(serviceParameters, index, this.allowInconsistencies);
		}

		FormManager.prototype.userIsConnected = function(){
			return this.customer.ID != -1;
		}

		FormManager.prototype.changeUser = function(){
			this.customer.ID = -1;
			this.customer.firstName = '';
			this.customer.lastName = '';
			this.customer.email = '';
			this.customer.company = '';
			this.customer.phone = '';
			this.customer.address = '';
			this.customer.postalCode = '';
			this.customer.town = '';
			this.customer.country = '';
			this.customer.company = '';
			this.customer.siret = '';
			this.customer.legalForm = '';
			this.customer.registerNewsletters = '';
			this.customer.password = '';
			this.customer.confirmPassword = '';
		}

		FormManager.prototype.customerIsOk = function(){
			return this.customer!=null && (this.customer.ID != -1 ||
				(this.customer.lastName.length > 0 &&
					(this.customer.email!=null && this.customer.email.length > 0) &&
				 ((this.customer.email!=null && this.customer.email.length > 0) ||
				 this.customer.phone.length > 0) &&
				 ((this.customer.password.length > 0 &&
				 this.customer.confirmPassword.length > 0 &&
				 this.customer.confirmPassword == this.customer.password) || this.customer.idFacebook.length > 0)));
		}

		FormManager.prototype.basketIsOk = function(allowInconsistencies){
			return _basket.basketIsOk(allowInconsistencies);
		}

		FormManager.prototype.isServicesParametersError = function(){
			return this.idServicesParametersError != null;
		}

		FormManager.prototype.checkboxPayment = function(){
			return (this.settings != null && (!this.settings.checkbox_payment || this.settings.checkbox_payment && this.checkboxAcceptPayment));
		}

		FormManager.prototype.typePaymentChosen = function(){
			return !this.askPayment() || this.typePayment!=null;
		}

		FormManager.prototype.formIsOk = function(callbacks){
			var ok = this.basketIsOk(this.allowInconsistencies) &&
				!this.isServicesParametersError() &&
				this.typePaymentChosen() &&
				this.checkboxPayment() &&
				(this.booking == null || this.bookingLoadedIsModified);
			if(callbacks != null && callbacks.customerIsOk != null){
				ok = ok && callbacks.customerIsOk('call by formIsOk');
			}
			else {
				ok = ok && this.customerIsOk();
			}
			return ok;
		}

		FormManager.prototype.round = function(value){
			return Math.round(value * 100)/100;
		}

		FormManager.prototype.getSubTotalPrice = function(servicesParameters){
			return Math.round(_basket.getSubTotalPrice(servicesParameters, this.reductions, this.mapIdClientObjectReduction, false) * 100) / 100;
		}

		FormManager.prototype.getSubTotalPriceBooking = function(servicesParameters){
			return Math.round(_basket.getSubTotalPrice(servicesParameters, this.reductionsInBooking, this.mapIdClientObjectReductionBooking, false) * 100) / 100;
		}

		FormManager.prototype.getSubTotalPriceWithReductions = function(servicesParameters, reductions, mapIdClientObjectReduction){
			return Math.round(_basket.getSubTotalPrice(servicesParameters, reductions, mapIdClientObjectReduction, false) * 100) / 100;
		}

		FormManager.prototype.setAllStates = function(state){
			this.bookingLoadedIsModified = true;
			_basket.setAllStates(state);
		}

		FormManager.prototype.getTotalPriceWithoutCustomReductions = function(){
			var amount = _basket.getTotalPrice(this.reductions, this.mapIdClientObjectReduction, false, this.getTypeAccountOfCustomer());
			return Math.round(amount * 100) / 100;
		}

		FormManager.prototype.getTotalPrice = function(){
			var amount = _basket.getTotalPrice(this.reductions, this.mapIdClientObjectReduction, false, this.getTypeAccountOfCustomer());
			for(var i = 0; i < this.customReductions.length; i++){
				var customReductionsAmount = this.customReductions[i].amount;
				amount += customReductionsAmount;
			}
			return Math.round(amount * 100) / 100;
		}

		FormManager.prototype.canPayAdvancePayment = function(){
			return _basket.getTotalPrice(this.reductions, this.mapIdClientObjectReduction, false, this.getTypeAccountOfCustomer()) > _basket.getTotalPrice(this.reductions, this.mapIdClientObjectReduction, true, this.getTypeAccountOfCustomer());
		}

		FormManager.prototype.getTotalPriceAdvancePayment = function(){
			var amount = _basket.getTotalPrice(this.reductions, this.mapIdClientObjectReduction, true, this.getTypeAccountOfCustomer());
			return Math.round(amount * 100) / 100;
		}

		FormManager.prototype.getTotalNumber = function(){
			return Math.round(_basket.getTotalNumber() * 100) / 100;
		}

		FormManager.prototype.isNumberValid = function(serviceParameters, numberPrice){
			return numberPrice.number >= this.getMinNumberPrice(serviceParameters, numberPrice) &&
				numberPrice.number <= this.getMaxNumberPrice(serviceParameters, numberPrice);
		}

		FormManager.prototype.displayNumberToChoose = function(serviceParameters, numberPrice, index){
			if(serviceParameters != null){
				var maxEquipments = serviceParameters.getMaxEquipmentsCanChoose(index);
				if(numberPrice.price != null && numberPrice.price.activateMinQuantity){
					return maxEquipments > this.getMinNumberPriceZero(serviceParameters, numberPrice);
				}
				return maxEquipments > 0;
			}
			return true;
		}

		FormManager.prototype.minMaxValue = function(value, min, max){
			if(max < min) return 0;
			if(value == undefined) return min;
			if(min != null) value = Math.max(min, value);
			if(max != null) value = Math.min(max, value);
			return Math.floor(value);
		}

		FormManager.prototype.getMaxNumberPrice = function(serviceParameters, numberPrice, index){
			var number = _basket.getNumberTimeslotWithSamePrice(serviceParameters, numberPrice);
			var totalMax = this.getRealMax(serviceParameters, index);
			var max = -1;
			if(numberPrice.price != null && numberPrice.price.activateMaxQuantity){
				 max = numberPrice.price.maxQuantity - number;
				 max = Math.min(max, totalMax);
			}
			else max = totalMax;
			max = Math.min(serviceParameters.getMaxEquipmentsCanChoose(index), max);
			if(this.allowInconsistencies){
				max = 9999;
			}
			return max;
		}

		FormManager.prototype.getMaxNumberPriceExtra = function(serviceParameters, numberPrice){
			var max = 9999;
			if(numberPrice.price != null && numberPrice.price.activateMaxQuantity){
				 max = numberPrice.price.maxQuantity;
			}
			if(this.allowInconsistencies){
				max = 9999;
			}
			return max;
		}

		FormManager.prototype.getMinNumberPriceZero = function(serviceParameters, numberPrice){
			var number = 0; //_basket.getNumberTimeslotWithSamePrice(serviceParameters, numberPrice);
			var min = 0;
			if(numberPrice.price != null && numberPrice.price.activateMinQuantity){
				if(number < 0) number = 0;
				min =  numberPrice.price.minQuantity - number;
				min = Math.max(min, 0);
			}
			return min;
		}

		FormManager.prototype.getMinNumberPrice = function(serviceParameters, numberPrice){
			var number = _basket.getNumberTimeslotWithSamePrice(serviceParameters, numberPrice);
			var min = 1;
			if(numberPrice.price != null && numberPrice.price.activateMinQuantity){
				min =  numberPrice.price.minQuantity - number;
				min = Math.max(min, 1);
			}
			return min;
		}

		FormManager.prototype.getMinNumberPriceExtra = function(serviceParameters, numberPrice){
			var min = 1;
			if(numberPrice.price != null && numberPrice.price.activateMinQuantity){
				min =  numberPrice.price.minQuantity;
				min = Math.max(min, 1);
			}
			return min;
		}

		FormManager.prototype.haveGlobalReduction = function(){
			if(this.mapIdClientObjectReduction['id0'] != null){
				for(var i = 0; i < this.mapIdClientObjectReduction['id0'].length; i++){
					if(this.mapIdClientObjectReduction['id0'][i].promoCode.length == 0){
						return true;
					}
				}
			}
			return false;
		}

		FormManager.prototype.getReductionById = function(idReduction){
			var allReductions = this.reductions;
			for(var i = 0; i < allReductions.length; i++){
				if(allReductions[i].id == idReduction){
					return allReductions[i];
				}
			}
			return null;
		}

		FormManager.prototype.getReductionBookingById = function(idReduction){
			var allReductions = this.reductionsInBooking;
			for(var i = 0; i < allReductions.length; i++){
				if(allReductions[i].id == idReduction){
					return allReductions[i];
				}
			}
			return null;
		}

		FormManager.prototype.addReductions = function(allReductions, reductionsArray){
			for(var i = 0; i < allReductions.length; i++){
				var newReduction = allReductions[i];
				var alreadyInserted = false;
				for(var j = 0; j < reductionsArray.length; j++){
					var reduction = reductionsArray[j];
					if(newReduction.id == reduction.id){
						alreadyInserted = true;
					}
				}
				if(!alreadyInserted){
					reductionsArray.push(newReduction);
				}
			}
		}

		FormManager.prototype.deleteCoupon = function(code){
			var index = _couponsList.indexOf(code);
			if(index != -1){
				_couponsList.splice(index, 1);
				this.bookingLoadedIsModified = true;
			}
			this.getReductions('');
		}

		FormManager.prototype.haveCouponInList = function(mapIdClientObjectReduction){
			if(mapIdClientObjectReduction != null){
				for(var i = 0; i < mapIdClientObjectReduction.length; i++){
					if(mapIdClientObjectReduction[i].promoCode.length > 0) return true;
				}
			}
			return false;
		}

		FormManager.prototype.formatText = function(text){
			if(text != null){
				var amount = this.getTotalPrice();
				if(this.checkboxAdvancePayment){
					amount = this.getTotalPriceAdvancePayment();
				}
				text = text.replace('[RESA_advancePayment]', amount + '' + this.settings.currency);
			}
			return text;
		}

		FormManager.prototype.saveInStorage = function(){
			_storage.save({
				basket: this.getAllServicesParameters(),
				couponsList: _couponsList,
				date: new Date()
			});
		}

		FormManager.prototype.clearStorage = function(){
			_storage.save({
				basket: [],
				couponsList: [],
				date: new Date()
			});
		}

		FormManager.prototype.addErrorMessage = function(id, message){
			this.errorMessages[id] = message;
		}


		FormManager.prototype.getTimeslots = function(date){
      if(this.serviceParameters.service != null){
				this.currentTimeslots = [];
				this.currentGetTimeslotsLaunched = true;
        var servicesParametersFormated = [];
        if(this.servicesParameters!=null){
          var servicesParametersFormated = JSON.parse(JSON.stringify(this.servicesParameters));
          for(var i = 0; i < servicesParametersFormated.length; i++){
            var serviceParameters = servicesParametersFormated[i];
            serviceParameters.dateStart = $filter('formatDateTime')(new Date(serviceParameters.dateStart));
            serviceParameters.dateEnd = $filter('formatDateTime')(new Date(serviceParameters.dateEnd));
          }
        }
        var idBooking = -1;
        if(this.booking != null){
          idBooking = this.booking.id;
        }
				var typeAccount = 'type_account_0';
				if(this.customer!=null && this.customer.typeAccount != null && this.customer.typeAccount.length > 0){
					typeAccount = this.customer.typeAccount;
				}
        var data = {
          action:'getTimeslots',
          service:  JSON.stringify(this.serviceParameters.service),
          servicesParameters: JSON.stringify(servicesParametersFormated),
          date: '' + $filter('date')(date, 'dd-MM-yyyy HH:mm:ss'),
          idBooking: JSON.stringify(idBooking),
          allowInconsistencies: JSON.stringify(this.allowInconsistencies),
          frontForm: JSON.stringify(this.frontForm),
					typeAccount:JSON.stringify(typeAccount)
        }

        jQuery.post(this.ajaxUrl, data, function(data) {
          _scope.$apply(function(){
						_self.currentTimeslots = [];
						_self.currentGetTimeslotsLaunched = false;
            var timeslots = JSON.parse(data);
            for(var i = 0; i < timeslots.length; i++){
							if(_self.serviceParameters.service != null &&
								timeslots[i].idService == _self.serviceParameters.service.id &&
								_self.currentDate.getFullYear() == date.getFullYear() &&
								_self.currentDate.getMonth() == date.getMonth() &&
								_self.currentDate.getDate() == date.getDate()){
	              var timeslot = new Timeslot(timeslots[i],date);
              	_self.currentTimeslots.push(timeslot);
							}
            }
          });
        }).fail(function(err){
					_self.currentGetTimeslotsLaunched = false;
    			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
    		}).error(function(err){
					_self.currentGetTimeslotsLaunched = false;
    			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
    		});
      }
    }

		FormManager.prototype.pushPromoCode = function(promoCode){
			var conditionPromoCode = (promoCode!=null && promoCode.length > 0 && _couponsList.indexOf(promoCode) == -1);
			if(conditionPromoCode){
				_couponsList.push(promoCode);
			}
		}

		/**
		 * AJAX
		 */
		FormManager.prototype.getReductions = function(promoCode){
			if(_basket.getAllServicesParameters().length > 0){
				var numberReductionsBefore = _self.reductions.length;
				var conditionPromoCode = (promoCode!=null && promoCode.length > 0 && _couponsList.indexOf(promoCode) == -1);
				if((this.booking == null || this.bookingLoadedIsModified || conditionPromoCode) && !this.getReductionsLaunched){
					this.getReductionsLaunched = true;
					if(conditionPromoCode){
						_couponsList.push(promoCode);
					}
					var servicesParametersFormated = JSON.parse(JSON.stringify(_basket.getAllServicesParameters()));
					for(var i = 0; i < servicesParametersFormated.length; i++){
						var serviceParameters = servicesParametersFormated[i];
						serviceParameters.dateStart = $filter('formatDateTime')(new Date(serviceParameters.dateStart));
						serviceParameters.dateEnd = $filter('formatDateTime')(new Date(serviceParameters.dateEnd));
					}
					var idBooking = -1;
					if(this.booking != null){
						idBooking = this.booking.id;
					}
					var idCustomer = -1;
					if(this.customer != null){
						idCustomer = this.customer.ID;
					}
					var data = {
						action:'getReductions',
						servicesParameters: JSON.stringify(servicesParametersFormated),
						couponsList: JSON.stringify(_couponsList),
		        idCustomer: JSON.stringify(idCustomer),
						idBooking: JSON.stringify(idBooking),
						allowInconsistencies: JSON.stringify(this.allowInconsistencies),
						frontForm: JSON.stringify(this.frontForm)
					}
					jQuery.post(this.ajaxUrl, data, function(data) {
						_scope.$apply(function(){
							_self.getReductionsLaunched = false;
							if(conditionPromoCode) {
								_self.currentPromoCode = '';
							}
							if(_self.booking == null || _self.bookingLoadedIsModified || conditionPromoCode){
								data = JSON.parse(data);
								if(conditionPromoCode){
									_self.bookingLoadedIsModified = true;
									if(JSON.stringify(_self.mapIdClientObjectReduction) == JSON.stringify(data.mapIdClientObjectReduction)){
										var text = _self.errorMessages['voucher_not_found'];
										if(text == null) text = 'Coupon non trouvé ou plus valide';
										sweetAlert('', text + ' !', 'error');
									}
									_self.saveInStorage();
								}
								_self.reductions = data.reductions;
								_self.mapIdClientObjectReduction = data.mapIdClientObjectReduction;
							}
							if(_self.relanchGetReductions){
								_self.relanchGetReductions = false;
								_self.getReductions('');
							}
						});
					}).fail(function(err){
						_self.getReductionsLaunched = false;
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					}).error(function(err){
						_self.getReductionsLaunched = false;
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					});
				}
				else {
					this.relanchGetReductions = true;
				}
			}
		}

		FormManager.prototype.validForm = function(callbacks){
			if(this.formIsOk(callbacks) && !this.launchValidForm) {
				this.launchValidForm = true;
				var servicesParametersFormated = JSON.parse(JSON.stringify(_basket.getAllServicesParameters()));
				for(var i = 0; i < servicesParametersFormated.length; i++){
					var serviceParameters = servicesParametersFormated[i];
					serviceParameters.dateStart = $filter('formatDateTime')(new Date(serviceParameters.dateStart));
					serviceParameters.dateEnd = $filter('formatDateTime')(new Date(serviceParameters.dateEnd));
				}
				var customReductionsFormated = JSON.parse(JSON.stringify(this.customReductions));
				var bookingNote = JSON.parse(JSON.stringify(this.bookingNote));
				bookingNote = this.htmlSpecialDecode(bookingNote);
				var bookingPublicNote = JSON.parse(JSON.stringify(this.bookingPublicNote));
				bookingPublicNote = this.htmlSpecialDecode(bookingPublicNote);
				var bookingStaffNote = JSON.parse(JSON.stringify(this.bookingStaffNote));
				bookingStaffNote = this.htmlSpecialDecode(bookingStaffNote);
				var bookingCustomerNote = JSON.parse(JSON.stringify(this.bookingCustomerNote));
				bookingCustomerNote = this.htmlSpecialDecode(bookingCustomerNote);
				var idBooking = -1;
				if(this.booking != null){
					idBooking = this.booking.id;
				}
				var typePayment = 'later';
				if(this.typePayment != null && this.askPayment()){
					typePayment = this.typePayment.id;
				}
				var data = {
					action:'validFormPublic',
					customer: JSON.stringify(this.customer),
					servicesParameters: JSON.stringify(servicesParametersFormated),
					typePayment: JSON.stringify(typePayment),
					advancePayment: JSON.stringify(this.checkboxAdvancePayment),
					couponsList: JSON.stringify(_couponsList),
					currentUrl:  JSON.stringify(_currentUrl),
					customReductions: JSON.stringify(customReductionsFormated),
					bookingNote: JSON.stringify(bookingNote),
					bookingPublicNote: JSON.stringify(bookingPublicNote),
					bookingStaffNote: JSON.stringify(bookingStaffNote),
					bookingCustomerNote: JSON.stringify(bookingCustomerNote),
					sendEmailToCustomer: JSON.stringify(this.sendEmailToCustomer),
					idBooking: JSON.stringify(idBooking),
					quotation: JSON.stringify(this.quotation),
					allowInconsistencies: JSON.stringify(this.allowInconsistencies),
					frontForm:JSON.stringify(this.frontForm),
					idForm:JSON.stringify(this.idForm)
				}
				jQuery.post(this.ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						_self.launchValidForm = false;
						data = JSON.parse(data);
						$log.debug('Succes : ' + JSON.stringify(data));
						if(data instanceof Object && !data.isError){
							_self.clearBasket();
							if(_callbackSuccessAdd == null){
								_self.basicSuccessForm(data);
							}
							else _callbackSuccessAdd(data);
						}
						else if(data instanceof Object && data.isError){
							sweetAlert('', data.message, 'error');
							_self.idServicesParametersError = data.options;
							if(_callbackErrorAdd != null){
								_callbackErrorAdd();
							}
						}
						else {
							sweetAlert('', data, 'error');
							if(_callbackErrorAdd != null){
								_callbackErrorAdd();
							}
						}
					});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					_scope.$apply(function(){ _self.launchValidForm = false; });
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					_scope.$apply(function(){ _self.launchValidForm = false; });
				});
			}
		}

		FormManager.prototype.basicSuccessForm = function(data){
			this.formEnd = true;
			this.confirmationText = data.confirmationText;
			if(data.successUrl != '' || data.payment != ''){
				this.redirect = true;
				if(data.payment != '') {
					if(data.payment.type == 'systemPay')
						_paymentManager.systemPay(data.payment);
					if(data.payment.type == 'paypal')
						_paymentManager.paypal(data.payment);
					if(data.payment.type == 'monetico')
						_paymentManager.monetico(data.payment);
					if(data.payment.type == 'stripe')
						_paymentManager.stripe(data.payment);
					if(data.payment.type == 'stripeConnect')
						_paymentManager.stripeConnect(data.payment);
					if(data.payment.type == 'paybox')
						_paymentManager.paybox(data.payment);
					if(data.payment.type == 'swikly')
						_paymentManager.swikly(data.payment);
				}
				else $window.location.href  = data.successUrl;
			}
		}

		FormManager.prototype.userConnection = function(){
			if(!this.launchUserConnexion){
				this.launchUserConnexion = true;
				var data = {
					action:'userConnection',
					login: JSON.stringify(this.customer.login),
					password: JSON.stringify(this.customer.passwordConnection)
				}

				jQuery.post(this.ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						_self.launchUserConnexion = false;
						if(data instanceof Object){
							_self.customer = data;
							_self.getReductions('');
						}
						else sweetAlert('', data, 'error');
					});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){ _self.launchUserConnexion = false; });
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){ _self.launchUserConnexion = false; });
				});
			}
		}

		FormManager.prototype.userDeconnection = function(){
			var data = {
				action:'userDeconnection'
			}

			jQuery.post(this.ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					_self.customer = JSON.parse(data);
				});
			}).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		return FormManager;
	}

	angular.module('resa_app').factory('FormManager', FormManagerFactory);
}());
