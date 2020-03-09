"use strict";

(function(){

  var _self = null;
  var _scope = null;
  var _numberLoadDates = 3;
  var _skeletonCustomer = null;

  var getAlertsTimeslot = function(idAppointment, alerts){
		var alertsArray = [];
		for(var i = 0; i < alerts.length; i++){
			var alert = alerts[i];
      if((alert.idType == 2 && idAppointment == alert.idAppointment)){
				alertsArray.push(alert);
			}
		}
		return alertsArray;
	}

  function EditBookingManagerFactory(FormManager, ServiceParameters, PaymentManagerForm, StorageManager, Basket, Timeslot, $filter, $log, $location) {

    var EditBookingManager = function(scope){
      FormManager.call(this);
      angular.extend(EditBookingManager.prototype, FormManager.prototype);

      this.jsonURL = null;
      this.opened = false;
      this.displayPopupCustomer = false;
      this.customers = [];
      this.equipments = [];
      this.filterCustomers = '';
      this.processActionCustomer = false;
      this.openNotificationAfterRegister = false;

      this.pagination = {step:10, number:1};
      this.displayedCustomers = [];
      this.nbTotalCustomers = 0;
      this.currentPromoCodes = [];

      this.currentDate = new Date();
      this.displayEditMode = false;
      this.timeslots = {};
      this.dates = [];
      this.months = [];
      this.model = {
        currentYear:2016,
        currentMonth:1
      }
      this.isProgressLoad = false;
      this.isNewServiceParameters = false;

      this.customTimeslot = {
        startHours: 12,
        startMinutes: 0,
        endHours: 12,
        endMinutes: 30,
        noEnd: false,
        idParameter:1
      }
      this.waitingsTimeslots = [];
      this.manyTimeslots = false;
      this.weekMode = false;
      this.synchronizeWeek = false;
      this.currentWeekDates = {};
      this.selectedTimeslots = [];
      this.oldParticipants = [];

      this.logNotificationsHistory = [];
      this.loadingLogNotificationsHistory = false;
      this.searchCustomersLaunched = false;

      _self = this;
      _scope = scope;
      _scope.backendCtrl.editBookingCtrl = this;
    }

    EditBookingManager.prototype.initializeEditBooking = function(allServices, services, members, equipments, customers, skeletonCustomer, alerts, settings, paymentsTypeList, ajaxUrl, jsonURL, currentUrl, months, scope, defaultDate, booking){
      this.allMonths = months;
      this.jsonURL = jsonURL;
      this.openNotificationAfterRegister = false;
      this.displayPopupCustomer = false;
      this.displayedCustomers = [];
      this.nbTotalCustomers = 0;
      this.customer = null;
      this.equipments = equipments;
      this.booking = null;
      this.filterCustomers = '';
      if(booking != null){
        this.booking = JSON.parse(JSON.stringify(booking));
      }
      this.frontForm = false;
      this.weekMode = false;
      this.synchronizeWeek = false;
      this.manyTimeslots = false;
      this.initialize(allServices, services, members, null, settings, paymentsTypeList, ajaxUrl, currentUrl, scope, _self.successEditBooking);

      if(this.services.length > 0){
				this.setCurrentService(this.services[0]);
			}
      this.typePayment = 'later';
      this.searchCustomersLaunched = false;
      this.isProgressLoad = false;
      _scope = scope;
      this.alerts = alerts;
      this.customers = customers;
      _skeletonCustomer =  JSON.parse(JSON.stringify(skeletonCustomer));
      this.skeletonCustomer = JSON.parse(JSON.stringify(_skeletonCustomer));
      this.updateBooking(true);
      this.allowInconsistencies = true;
      this.logNotificationsHistory = [];
      if(this.booking != null){
        this.allowInconsistencies = true; //this.booking.allowInconsistencies;
        this.getCustomer(this.booking.idCustomer);
        //Check inconsistances
        /*if(!this.allowInconsistencies){
          var thereIsAnAlert = false;
          for(var i = 0; i < this.booking.appointments.length; i++){
            var appointment = this.booking.appointments[i];
            if(getAlerts(appointment, this.alerts).length > 0){
              thereIsAnAlert = true;
            }
          }
    			this.allowInconsistencies = thereIsAnAlert;
        }*/
      }
      else {
        var booking = $location.search().booking;
        if(booking != null){
          this.getBooking(booking);
        }
        $location.search('booking', null);
      }
      //this.serviceParameters.addNumberPriceForEachServicePrice();
      this.clearStorage();
      var actualDate = new Date();
      if(defaultDate < actualDate){
        defaultDate = actualDate;
      }
      this.currentDate = defaultDate;
    }

    EditBookingManager.prototype.successEditBooking = function(data){
      _self.close();
      _scope.backendCtrl.returnOfForm(data, _self.booking);
      sweetAlert({
          title: 'Ok',
          type: "success",
          timer: 3000,
          showConfirmButton: true
      });
      if(_self.openNotificationAfterRegister){
        _scope.backendCtrl.openNotificationDialog(data.customer, data.booking);
      }
    }

    EditBookingManager.prototype.close = function(){
      this.servicesParameters = [];
      this.clearBasket();
      this.opened = false;
      if(this.displayEditMode){
        this.cancel();
      }
    }

    EditBookingManager.prototype.getBooking = function(booking){
      if(!this.searchCustomersLaunched){
				this.searchCustomersLaunched = true;
				jQuery.ajax({
			    type: "GET",
			    url: this.jsonURL + '/resa/v1/booking/' + _scope.backendCtrl.token + '/' + booking,
			    contentType: "application/json; charset=utf-8",
			    dataType: "json",
			    success: function(data){
						_scope.$apply(function(){
              _self.searchCustomersLaunched = false;
              _self.booking = data;
              if(_self.booking != null){
                _self.servicesInBooking = _scope.backendCtrl.getServicesOnBooking(_self.booking);
      			    _self.reductionsInBooking = _scope.backendCtrl.getReductionsOnBooking(_self.booking);
                _self.updateBooking(true);
                _self.getCustomer(_self.booking.idCustomer);
              }
						});
					},
			    failure: function(errMsg) {
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					 _scope.$apply(function(){ _self.searchCustomersLaunched = false; });
				 },
				 error: function(errMsg) {
  					 sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  					_scope.$apply(function(){ _self.searchCustomersLaunched = false; });
  				},
				});
			}
    }

    EditBookingManager.prototype.getCustomer = function(idCustomer){
      if(!this.searchCustomersLaunched){
				this.searchCustomersLaunched = true;
				jQuery.ajax({
			    type: "GET",
			    url: this.jsonURL + '/resa/v1/customer/' + _scope.backendCtrl.token + '/' + idCustomer,
			    contentType: "application/json; charset=utf-8",
			    dataType: "json",
			    success: function(data){
						_scope.$apply(function(){
              _self.searchCustomersLaunched = false;
              _self.customer = data;
              if(_self.customer != null){
                _self.customerName = _self.customer.displayName;
              }
						});
					},
			    failure: function(errMsg) {
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					 _scope.$apply(function(){ _self.searchCustomersLaunched = false; });
				 },
				 error: function(errMsg) {
  					 sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  					_scope.$apply(function(){ _self.searchCustomersLaunched = false; });
  				},
				});
			}
    }

    EditBookingManager.prototype.isOkCustomer = function(){
  		return this.skeletonCustomer!=null && ((!this.skeletonCustomer.createWpAccount && this.skeletonCustomer.phone != null && this.skeletonCustomer.phone.length > 0) || (this.skeletonCustomer.createWpAccount && this.skeletonCustomer.email != null && this.skeletonCustomer.email.length > 0)) && !this.processActionCustomer;
  	}

    EditBookingManager.prototype.closePopupCustomer = function(){
      this.skeletonCustomer = JSON.parse(JSON.stringify(_skeletonCustomer));
      this.displayPopupCustomer = false;
    }

    EditBookingManager.prototype.editCustomer = function(){
      this.skeletonCustomer = JSON.parse(JSON.stringify(this.customer));
      this.displayPopupCustomer = true;
    }

    EditBookingManager.prototype.createCustomer = function(){
  		if(this.isOkCustomer()){
  			this.processActionCustomer = true;
  			var skeletonCustomer = JSON.parse(JSON.stringify(this.skeletonCustomer));
  			skeletonCustomer.privateNotes = skeletonCustomer.privateNotes.replace(new RegExp('\n', 'g'),'<br />');

  			var data = {
  				action:'editCustomer',
  				customer: JSON.stringify(skeletonCustomer)
  			}

  			jQuery.post(ajaxurl, data, function(data) {
  				_scope.$apply(function(){
  					data = JSON.parse(data);
  					if(typeof data === 'string'){
  						sweetAlert('', data, 'error');
  						_self.processActionCustomer = false;
  					}
  					else {
  						var customer = JSON.parse(data.customer);
  						customer.privateNotes = customer.privateNotes.replace(new RegExp('&lt;br /&gt;', 'g'),'<br />');
  						_self.processActionCustomer = false;
              //_self.customers.push(customer);
        			customer.isCustomer = true;
              _self.customer = customer;
              _self.closePopupCustomer();
    					sweetAlert('', 'Ok', 'succes');
  					}
    			});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
  		}
  	}

    EditBookingManager.prototype.searchCustomers = function(search, page, limit){
			if(!this.searchCustomersLaunched){
				this.searchCustomersLaunched = true;
				jQuery.ajax({
			    type: "POST",
			    url: this.jsonURL + '/resa/v1/customers/' + _scope.backendCtrl.token,
			    data: JSON.stringify({ search:search, page:page, limit:limit }),
			    contentType: "application/json; charset=utf-8",
			    dataType: "json",
			    success: function(data){
						_scope.$apply(function(){
							 _self.searchCustomersLaunched = false;
							_self.displayedCustomers = data.customers;
							_self.nbTotalCustomers = data.nbTotalCustomers;
              jQuery(window).click(function() {
                //Hide the menus if visible
                _scope.$apply(function(){
                  _self.removeClientResults();
                });
              });
              jQuery('#client_results').click(function(event){
                  event.stopPropagation();
              });
						});
					},
			    failure: function(errMsg) {
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					 _scope.$apply(function(){ _self.searchCustomersLaunched = false; });
				 },
				 error: function(errMsg) {
					 sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					_scope.$apply(function(){ _self.searchCustomersLaunched = false; });
				},
				});
			}
		}

    EditBookingManager.prototype.searchCustomersAction = function(){
			this.searchCustomers(this.filterCustomers, this.pagination.number, this.pagination.step);
		}

    EditBookingManager.prototype.removeClientResults = function(){
      jQuery(window).unbind('click');
      jQuery('#client_results').unbind('click');
      _self.displayedCustomers = [];
    }


    EditBookingManager.prototype.setCustomer = function(customer){
      this.customer = customer;
      this.removeClientResults();
      this.setAllDefaultParticipants();
    }

		EditBookingManager.prototype.getEquipmentById = function(idEquipment){
			for(var i = 0; i < this.equipments.length; i++){
				if(this.equipments[i].id == idEquipment)
					return this.equipments[i];
			}
			return null;
		}


    /**
		 * Return service by id service if not old, the new version on this service else
		 */
		EditBookingManager.prototype.getLastVersionOnServiceById = function(idService){
			for(var i = 0; i < this.services.length; i++){
				var service = this.services[i];
				if(!service.oldService){
					if(service.id == idService){
						return service;
					}
					else {
						var linkOldServicesArray = service.linkOldServices.split(',');
						if(linkOldServicesArray.indexOf(idService+'') != -1){
							return service;
						}
					}
				}
			}
			return null;
		}

    EditBookingManager.prototype.isNewBooking = function(){
      return this.booking == null;
    }

    EditBookingManager.prototype.cancelBooking = function(){
      if(this.booking != null) this.booking.status = 'cancelled';
      this.quotation = false;
      this.setAllStates('cancelled');
      this.getReductions('');
    }

    EditBookingManager.prototype.okBooking = function(){
      if(this.booking != null) {
        this.booking.status = 'ok';
        this.quotation = this.booking.quotation;
      }
      this.setAllStates('ok');
    }

    EditBookingManager.prototype.waitingBooking = function(){
      if(this.booking != null) {
        this.booking.status = 'waiting';
        this.quotation = this.booking.quotation;
      }
      this.setAllStates('waiting');
    }

    EditBookingManager.prototype.memberIsInPlace = function(member){
      return member.places.length == 0 || member.places.indexOf(this.currentPlace.id) != -1;
    }

    EditBookingManager.prototype.addCurrentServiceInBasket = function(){
      if(this.canAddInBasket()){
        this.bookingLoadedIsModified = true;
        this.timeslots = {};
        this.serviceParameters.removeNumberPriceNull();
        this.addServiceParametersManyTimeslots();
        if(this.isNewServiceParameters){
          this.addServiceParametersManyDates();
          this.serviceParameters.idServiceParametersLink = this.serviceParameters.id;
          this.addBasket();
        }
        else {
          this.currentServiceParametersUpdate = null;
          this.serviceParameters.update = false;
          if(!this.synchronizeWeek){
            this.replaceInBasket();
          }
          else if(this.synchronizeWeek){
            var allServiceParameters = this.getAllServicesParametersByIdLink(this.serviceParameters);
            for(var i = 0; i < allServiceParameters.length; i++){
              var localServiceParameters = allServiceParameters[i];
              var newServiceParameters = new ServiceParameters();
              newServiceParameters.fromServiceParametersJSON(this.serviceParameters);
              newServiceParameters.id = localServiceParameters.id;
              newServiceParameters.idAppointment = localServiceParameters.idAppointment;
              newServiceParameters.idServiceParametersLink = localServiceParameters.idServiceParametersLink;
              if(this.serviceParameters.id != newServiceParameters.id){
                newServiceParameters.tags = localServiceParameters.tags;
              }
              newServiceParameters.setDate(localServiceParameters.dateStart, localServiceParameters.dateEnd);
              this.replaceInBasketWithParams(newServiceParameters);
            }
    				this.saveInStorage();
    				this.updateServicesParameters('');
          }
        }
        this.setDisplayEditMode(false);
        //this.serviceParameters.addNumberPriceForEachServicePrice();
      }
    }

    EditBookingManager.prototype.addServiceParametersManyTimeslots = function(){
      if(this.manyTimeslots){
        var nextId = this.getNextId();
        for(var i = 0; i < this.selectedTimeslots.length; i++){
          var timeslot = this.selectedTimeslots[i];
          var serviceParameters = new ServiceParameters();
					serviceParameters.fromServiceParametersJSON(this.serviceParameters);
    			serviceParameters.id = nextId + (this.isNewServiceParameters?1:0) + i;
          if(this.weekMode && this.isNewServiceParameters) serviceParameters.idServiceParametersLink = this.serviceParameters.id;
          else serviceParameters.idServiceParametersLink = serviceParameters.id;
          this.setTimeslot(timeslot, serviceParameters);
          this.addServiceParametersInBasket(serviceParameters);
        }
        this.selectedTimeslots = [];
        this.manyTimeslots = false;
      }
    }

    EditBookingManager.prototype.addServiceParametersManyDates = function(){
      for(var i = 1; i < this.serviceParameters.manyDates.length; i++){
				var date = this.serviceParameters.manyDates[i];
				var newServiceParameters = new ServiceParameters();
				newServiceParameters.fromServiceParametersJSON(this.serviceParameters);
				newServiceParameters.id = this.getNextId() + 1;
        if(this.isNewServiceParameters) newServiceParameters.idServiceParametersLink = this.serviceParameters.id;
				var tokens = date.split('-');
				newServiceParameters.setTwoDates(tokens[0],(tokens[1] - 1),tokens[2]);
				this.addServiceParametersInBasket(newServiceParameters);
			}
    }

    EditBookingManager.prototype.createNewServiceParameters =  function(){
      if(this.displayEditMode){
        this.cancel();
      }
      this.tempoMapIdClientObjectReduction = {};
			this.serviceParameters = new ServiceParameters();
      /*
      if(this.getPlaces()!=null && this.getPlaces().length > 0) {
        this.changePlace(this.getPlaces()[0]);
      }
      else this.setCurrentService(this.currentService);
      */
      this.serviceParameters.id = this.getNextId();
      this.serviceParameters.service = null;
      this.serviceParameters.tags = [];
      /*
      this.serviceParameters.resetDates();
      this.serviceParameters.resetFirstNumber();
      this.serviceParameters.addNumberPriceForEachServicePrice();
      */
      this.isNewServiceParameters = true;
      this.isProgressLoad = false;
      this.setDisplayEditMode(true);
    }



    EditBookingManager.prototype.modifyServiceParameters =  function(serviceParameters){
      if(!this.displayEditMode || (this.displayEditMode && (this.currentServiceParametersUpdate == null || serviceParameters.id != this.currentServiceParametersUpdate.id))){
        if(this.displayEditMode){
          this.cancel();
        }
        this.bookingLoadedIsModified = true;
        this.currentServiceParametersUpdate = serviceParameters;
        this.currentServiceParametersUpdate.update = true;
        var newServiceParameters = new ServiceParameters();
        newServiceParameters.fromServiceParametersJSON(serviceParameters, true);
        this.serviceParameters = newServiceParameters;
        var alertTimeslot = (getAlertsTimeslot(serviceParameters.idAppointment, this.alerts).length > 0);
        if(!alertTimeslot){
          this.generateDates(new Date(this.serviceParameters.dateStart));
        }
        else {
          this.createCustomTimeslotWithServiceParameters();
        }
        var service = this.getLastVersionOnServiceById(newServiceParameters.service.id);
        if(service!=null){
          this.serviceParameters.service = service;
          if(this.serviceParameters.service.typeAvailabilities >= 1){
            this.synchronizeWeek = true;
          }
        }
        this.serviceParameters.mergeMembersUsedWithMembers(this.getMembersByPlace());
        this.serviceParameters.update = true;
        this.serviceParameters.completeNumberPriceForEachServicePrice();
        this.setDisplayEditMode(true);
        this.currentPlace = this.getPlaceById(newServiceParameters.place);
        this.serviceParameters.place = newServiceParameters.place;
        this.sendRequests(-1);
      }
    }

    EditBookingManager.prototype.removeServiceParameters = function(serviceParameters){
      if(this.displayEditMode){
        this.cancel();
      }
      this.removeBasketAll(serviceParameters);
    }

    EditBookingManager.prototype.cancel = function($event){
      if($event != null) $event.stopPropagation();
      this.timeslots = {};
      this.manyTimeslots = false;
      this.selectedTimeslots = [];
      this.setDisplayEditMode(false);
      if(this.currentServiceParametersUpdate != null){
        this.currentServiceParametersUpdate.update = false;
        this.currentServiceParametersUpdate = null;
      }
    }

    EditBookingManager.prototype.setDisplayEditMode =  function(displayEditMode){
      this.displayEditMode = displayEditMode;
      if(!this.displayEditMode){
        this.isNewServiceParameters = false;
      }
    }

    EditBookingManager.prototype.formIsOkEditBooking = function(){
      return this.formIsOk() && !this.displayEditMode;
    }

    EditBookingManager.prototype.changePlace = function(place){
      this.currentPlace = place;
      this.serviceParameters.place = place.id;
      this.serviceParameters.service = null;
    }

    EditBookingManager.prototype.changeService = function(service){
      this.serviceParameters.service = service;
      var date = new Date(this.currentDate);
      var startBookingDate = $filter('parseDate')(service.startBookingDate);
      if(date < startBookingDate){ date = new Date(startBookingDate); }
      this.currentDate = date;
      this.calculateMonth(service);
			this.monthIndex = this.currentDate.getMonth();
      this.weekMode = false;
      this.synchronizeWeek = false;
      this.manyTimeslots = false;
      this.currentWeekDates = {};
      this.unselectDates();
      if(this.serviceParameters.service.typeAvailabilities != 1){
        this.changeCurrentDate();
        this.sendReloadTimeslots();
      }
      else {
        this.timeslots = {};
      }
    }

    EditBookingManager.prototype.setWeek = function(dates){
      this.weekMode = true;
      this.manyTimeslots = true;
      this.currentDate = $filter('parseDate')(dates.startDate);
      this.currentWeekDates = dates;
      this.changeCurrentDate();
      this.sendReloadTimeslots();
    }

    EditBookingManager.prototype.isWeekSelected = function(dates){
      return this.currentWeekDates.startDate!=null && $filter('parseDate')(this.currentWeekDates.startDate).getTime() == $filter('parseDate')(dates.startDate).getTime() && $filter('parseDate')(this.currentWeekDates.endDate).getTime() == $filter('parseDate')(dates.endDate).getTime()
    }

    EditBookingManager.prototype.setServiceParametersManyDates = function(dates){
			var array = dates.split(',');
			var tokens = array[0].split('-');
			this.currentDate = new Date(tokens[0], tokens[1] - 1, tokens[2]);
			this.serviceParameters.manyDates =  array;
      this.changeCurrentDate();
      this.sendReloadTimeslots();
		}

    EditBookingManager.prototype.getNumberDays = function(){
      return this.selectedTimeslots.length + 1;
    }

    EditBookingManager.prototype.clearDates = function(){
      if(!this.manyTimeslots){
        this.unselectDates();
      }
    }

    EditBookingManager.prototype.allUnselected = function(){
      if(this.manyTimeslots){
        if(this.weekMode){
          this.currentWeekDates = {};
          this.timeslots = {};
        }
        this.unselectDates();
      }
    }

    EditBookingManager.prototype.unselectDates = function(){
      this.serviceParameters.clearDates();
      this.selectedTimeslots = [];
    }

    EditBookingManager.prototype.setParticipantIfNecessary = function(numberPrice){
      if(numberPrice.participants == null) numberPrice.participants = [];
      for(var i = 0; i < numberPrice.number; i++){
        if(numberPrice.participants[i] == null && this.oldParticipants.length > 0){
          numberPrice.participants.push(JSON.parse(JSON.stringify(this.oldParticipants.shift())));
        }
      }
      this.setDefaultParticipants(numberPrice);
    }



    EditBookingManager.prototype.selectTimeslot = function(timeslot){
      var ifDateSet = this.serviceParameters.isDatesSet();
      if(this.weekMode){
        this.unselectDates();
        var startDate = this.clearTime(new Date($filter('parseDate')(this.currentWeekDates.startDate)));
        var endDate = this.clearTime(new Date($filter('parseDate')(this.currentWeekDates.endDate)));
        var timeslot = new Timeslot(timeslot, startDate);
        this.setTimeslot(timeslot);
        this.serviceParameters.dateStart = new Date(startDate);
        this.serviceParameters.dateStart.setHours(timeslot.dateStart.getHours());
        this.serviceParameters.dateStart.setMinutes(timeslot.dateStart.getMinutes());
        this.serviceParameters.dateStart.setSeconds(timeslot.dateStart.getSeconds());
        this.serviceParameters.dateEnd = new Date(startDate);
        this.serviceParameters.dateEnd.setHours(timeslot.dateEnd.getHours());
        this.serviceParameters.dateEnd.setMinutes(timeslot.dateEnd.getMinutes());
        this.serviceParameters.dateEnd.setSeconds(timeslot.dateEnd.getSeconds());
        while(startDate.getTime() < endDate.getTime()){
          startDate.setTime(startDate.getTime() + 24 * 3600 * 1000);
          startDate = this.clearTime(startDate);
          var newTimeslot = new Timeslot(timeslot, startDate);
          this.selectedTimeslots.push(newTimeslot);
        }
      }
      else {
        if(!this.manyTimeslots) this.setTimeslot(timeslot);
        else {
          if(!this.serviceParameters.isDatesSet()){
            this.setTimeslot(timeslot);
          }
          else if(timeslot.isSameDates(this.serviceParameters.dateStart, this.serviceParameters.dateEnd)){
            this.serviceParameters.clearDates();
            if(this.selectedTimeslots.length > 0){
              timeslot = this.selectedTimeslots[0];
              this.setTimeslot(timeslot);
              this.switchTimeslot(timeslot);
            }
          }
          else {
            this.switchTimeslot(timeslot);
          }
        }
      }
      if(timeslot.isSameDates(this.serviceParameters.dateStart, this.serviceParameters.dateEnd)){
        if(ifDateSet) {
          this.oldParticipants = this.serviceParameters.getParticipants();
        }
        if(ifDateSet || this.isNewServiceParameters) {
          if(this.serviceParameters.idParameter > -1){
    				var parameter = this.getParameter(this.serviceParameters.idParameter);
    				this.serviceParameters.state = parameter.stateBackend;
    			}
        }
        this.serviceParameters.addNumberPriceForEachServicePrice(timeslot);
      }
    }

    EditBookingManager.prototype.getIndexTimeslot = function(timeslot){
      for(var i = 0; i < this.selectedTimeslots.length; i++){
        var selectedTimeslot = this.selectedTimeslots[i];
        if(selectedTimeslot.isSameDates(timeslot.dateStart, timeslot.dateEnd)) return i;
      }
      return -1;
    }

    EditBookingManager.prototype.switchTimeslot = function(timeslot){
      var index = this.getIndexTimeslot(timeslot);
      if(index == -1) this.selectedTimeslots.push(timeslot);
      else this.selectedTimeslots.splice(index, 1);
    }

    EditBookingManager.prototype.sendReloadTimeslots = function(){
      this.timeslots = {};
      this.sendRequests(-1);
    }

    EditBookingManager.prototype.changeCurrentDate = function(){
      this.generateDates(this.currentDate);
    }

    EditBookingManager.prototype.changeCurrentMonthDate = function(){
      this.currentWeekDates = {};
      this.timeslots = {};
      this.changeMonthOfDate();
    }

    EditBookingManager.prototype.generateDates = function(date){
      this.currentDate = date;
      var step = ((_numberLoadDates-1)/2);
      for(var i = 0; i < step; i++){
        this.dates[i] = this.clearTime(new Date(date.getTime()));
        this.dates[i].setDate(this.dates[i].getDate() - (step - i));
      }
      this.dates[step] = this.clearTime(new Date(date.getTime()));
      for(var i = 1; i <= step; i++){
        this.dates[i + step] = this.clearTime(new Date(date.getTime()));
        this.dates[i + step].setDate(this.dates[i + step].getDate() + i);
      }
      this.model.currentMonth = date.getMonth();
      this.model.currentYear =  date.getFullYear();
      if(!this.isProgressLoad){
        this.sendRequests(-1);
      }
    }

    EditBookingManager.prototype.decrementDate = function(){
      for(var i = this.dates.length - 2; i >= 0; i--){
        this.dates[i + 1] = this.dates[i];
      }
      this.dates[0] = new Date(this.dates[1].getTime());
      this.dates[0].setDate(this.dates[0].getDate() - 1);
      this.currentDate = this.dates[((_numberLoadDates-1)/2)];
      if(!this.isProgressLoad){
        this.sendRequests(0);
      }
    }

    EditBookingManager.prototype.incrementDate = function(){
      for(var i = 1; i < this.dates.length; i++){
        this.dates[i - 1] = this.dates[i];
      }
      this.dates[this.dates.length - 1] = new Date(this.dates[this.dates.length - 2].getTime());
      this.dates[this.dates.length - 1].setDate(this.dates[this.dates.length - 1].getDate() + 1);
      this.currentDate = this.dates[((_numberLoadDates-1)/2)];
      if(!this.isProgressLoad){
        this.sendRequests((_numberLoadDates-1));
      }
    }

    EditBookingManager.prototype.minMaxHours = function(input){
      return  Math.max(Math.min(input, 23), 0);
    }

    EditBookingManager.prototype.minMaxMinutes = function(input){
      return  Math.max(Math.min(input, 59), 0);
    }

    EditBookingManager.prototype.isGoodNewCustomTimeslot = function(){
      return (this.customTimeslot.startHours * 3600 + this.customTimeslot.startMinutes * 60) <
        (this.customTimeslot.endHours * 3600 + this.customTimeslot.endMinutes * 60);
    }

    EditBookingManager.prototype.createNewCustomTimeslot = function(){
      var date = this.clearTime(this.currentDate);
      var startHours = this.customTimeslot.startHours;
      if(startHours < 10) startHours = '0' + startHours;
      var startMinutes = this.customTimeslot.startMinutes;
      if(startMinutes < 10) startMinutes = '0' + startMinutes;
      var endHours = this.customTimeslot.endHours;
      if(endHours < 10) endHours = '0' + endHours;
      var endMinutes = this.customTimeslot.endMinutes;
      if(endMinutes < 10) endMinutes = '0' + endMinutes;
      var startTime = startHours + ':' + startMinutes + ':00';
      var endTime = endHours + ':' + endMinutes + ':00';
      var timeslot = new Timeslot({
        id:42,
        startTime:startTime,
        endTime:endTime,
        capacity: 80,
        typeCapacity: 1,
        noStaff: false,
        exclusive: false,
        noEnd: this.customTimeslot.noEnd,
        maxAppointments: 1,
        capacityMembers: 80,
        usedCapacity:0,
        numberOfAppointmentsSaved:0,
        members:[],
        membersUsed:[],
        isCustom:true,
        idParameter:this.customTimeslot.idParameter,
        equipments:[]
      }, date);
      if(_self.timeslots[date.getTime()] != null){
        _self.timeslots[date.getTime()].push(timeslot);
      }
      else {
        _self.waitingsTimeslots.push(timeslot);
        _self.generateDates(date);
      }
      _self.selectTimeslot(timeslot);
    }

    EditBookingManager.prototype.createCustomTimeslotWithServiceParameters = function(){
      this.currentDate = this.clearTime(new Date(this.serviceParameters.dateStart));
      this.customTimeslot.idParameter = this.serviceParameters.idParameter;
      this.customTimeslot.noEnd = this.serviceParameters.noEnd;
      this.customTimeslot.startHours = this.serviceParameters.dateStart.getHours();
      this.customTimeslot.startMinutes = this.serviceParameters.dateStart.getMinutes();
      this.customTimeslot.endHours = this.serviceParameters.dateEnd.getHours();
      this.customTimeslot.endMinutes = this.serviceParameters.dateEnd.getMinutes();
      this.createNewCustomTimeslot();
    }

    EditBookingManager.prototype.sendRequests = function(currentIndex){
      if(this.displayEditMode){
        var step = ((_numberLoadDates-1)/2);
        if(this.timeslots[this.dates[step].getTime()]==null){
          this.isProgressLoad = true;
          this.getTimeslotsAjax(this.dates[step]);
        } else {
          var index = 0;
          while(index < this.dates.length && this.timeslots[this.dates[index].getTime()]!=null){
            index++;
          }
          if(index < this.dates.length && (currentIndex==-1 || index == currentIndex)){
            this.isProgressLoad = true;
            this.getTimeslotsAjax(this.dates[index]);
          }
          else this.isProgressLoad = false;
        }
      }else this.isProgressLoad = false;
    }

    EditBookingManager.prototype.getTimeslotsAjax = function(date){
      var yesterdayDate = new Date();
      yesterdayDate.setDate(yesterdayDate.getDate() - 1);
      //Not load if pass date
      if(date.getTime() <= yesterdayDate.getTime() &&
      (this.currentWeekDates.endDate == null || (this.parseDate(this.currentWeekDates.endDate).getTime() <= yesterdayDate.getTime()))){
        _self.timeslots[date.getTime()] = [];
        _self.sendRequests(-1);
      }
      else {
        if(this.serviceParameters.service != null && _self.timeslots[date.getTime()]==null){
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
            service:  JSON.stringify(this.serviceParameters.service),
            servicesParameters: JSON.stringify(servicesParametersFormated),
            date: '' + $filter('date')(date, 'dd-MM-yyyy HH:mm:ss'),
            idBooking: idBooking,
            allowInconsistencies: this.allowInconsistencies,
            frontForm: false,
            typeAccount:typeAccount
          }
          jQuery.ajax({
            type: "POST",
            url: this.jsonURL + '/resa/v1/timeslots/' + _scope.backendCtrl.token,
            data: JSON.stringify(data),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function(data){
              _scope.$apply(function(){
                var timeslots = data;
                _self.timeslots[date.getTime()] = [];
                var found = false;
                for(var i = 0; i < timeslots.length; i++){
                  if(_self.serviceParameters != null && _self.serviceParameters.service != null && timeslots[i].idService == _self.serviceParameters.service.id){
                    var timeslot = new Timeslot(timeslots[i],date);
                    _self.timeslots[date.getTime()].push(timeslot);
                    if(_self.displayEditMode &&
                      timeslot.isSameDates(_self.serviceParameters.dateStart, _self.serviceParameters.dateEnd)){
                        _self.selectTimeslot(timeslot);
                        found = true;
                      }
                      if(_self.clearTime(_self.currentDate).getTime() == _self.clearTime(date).getTime()){
                        _self.customTimeslot.idParameter = timeslot.idParameter;
                      }
                    }
                  }
                  var newWaitingsTimeslots = [];
                  for(var i = 0; i < _self.waitingsTimeslots.length; i++){
                    var waitingTimeslot = _self.waitingsTimeslots[i];
                    if(date.getTime() == waitingTimeslot.date.getTime()){
                      _self.timeslots[date.getTime()].push(waitingTimeslot);
                      _self.selectTimeslot(waitingTimeslot);
                      found = true;
                    }
                    else newWaitingsTimeslots.push(waitingTimeslot);
                  }
                  _self.waitingsTimeslots = newWaitingsTimeslots;
                  if(_self.clearTime(new Date(_self.serviceParameters.dateStart)).getTime() == _self.clearTime(date).getTime() && !found && !_self.isNewServiceParameters){
                    _self.createCustomTimeslotWithServiceParameters();
                  }
                  _self.sendRequests(-1);
                });
              },
              failure: function(errMsg) {
                sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
              },
              error: function(errMsg) {
                sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
              },
            });
          }
        }
      }

    EditBookingManager.prototype.getBookingId = function(booking){
			if(booking == null) return 0;
			if(booking.idCreation > -1) return booking.idCreation;
			return booking.id;
		}

    EditBookingManager.prototype.getLogNotificationsHistory = function(){
      if(!this.loadingLogNotificationsHistory && _self.booking != null){
  			var data = {
  				action:'getLogNotificationsHistory',
  				idBooking: JSON.stringify(_self.getBookingId(_self.booking))
  			}
        _self.loadingLogNotificationsHistory = true;
  			jQuery.post(ajaxurl, data, function(data) {
  				_scope.$apply(function(){
            _self.loadingLogNotificationsHistory = false;
  					data = JSON.parse(data);
            _self.logNotificationsHistory = data;
  				});
  			}).fail(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
          _scope.$apply(function(){ _self.loadingLogNotificationsHistory = false; });
  			}).error(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
          _scope.$apply(function(){ _self.loadingLogNotificationsHistory = false; });
  			});
      }
  	}

    EditBookingManager.prototype.deleteBooking = function(dialogTexts){
      if(!this.launchValidForm){
        var text = dialogTexts.text;
        if(_self.booking.paymentState != 'noPayment' && _scope.backendCtrl.isCaisseOnlineActivated()){
          text += '<br /><span style="color:red; font-weight:bold">La réservation possède des paiements</span>';
          if(_self.booking.status == 'cancelled' || _self.booking.status == 'abandonned'){
            text = '<span style="color:red; font-weight:bold">Des remboursements sont nécessaires pour cette réservation</span>';
          }
          text +=', êtes vous sur de vouloir la supprimer ?<br /><br /><i>(le lien sur la caisse entre cette réservation et ses paiements sera effacé)</i>';
        }

    		sweetAlert({
    		  title: dialogTexts.title,
    		  text: text,
    		  type: "warning",
    		  showCancelButton: true,
    		  confirmButtonColor: "#DD6B55",
    		  confirmButtonText: dialogTexts.confirmButton,
    		  cancelButtonText: dialogTexts.cancelButton,
    		  closeOnConfirm: true,
    		  html: true
    		}, function(){
          _self.deleteBookingAction();
    		});
      }
  	}

    EditBookingManager.prototype.deleteBookingAction = function(){
      var data = {
        action:'deleteBooking',
        idBooking: JSON.stringify(this.booking.id)
      }
      _scope.$apply(function(){ _self.launchValidForm = true; });
      jQuery.post(ajaxurl, data, function(data) {
        _scope.$apply(function(){
          _self.launchValidForm = false;

          data = JSON.parse(data);
          _scope.backendCtrl.removeBooking(data);
          _self.close();

          sweetAlert({
            title: 'OK !',
            type: "success",
            timer: 2000,
            showConfirmButton: true });
        });
      }).fail(function(err){
        sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
        _scope.$apply(function(){ _self.launchValidForm = false; });
      }).error(function(err){
        sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
        _scope.$apply(function(){ _self.launchValidForm = false; });
      });
    }

    return EditBookingManager;
  }

  angular.module('resa_app').factory('EditBookingManager', EditBookingManagerFactory);
}());
