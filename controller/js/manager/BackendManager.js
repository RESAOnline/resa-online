"use strict";

(function(){
	function BackendManagerFactory(FunctionsManager, CalendarManager, AppointmentManager, LogNotificationsCenterManager, RESANewsManager, FormManager, $filter, $location, $window){

		var _locale = null;
		var _ajaxUrl = null;
		var _self = null;
		var _scope = null;
		var _currentUrl = null;
		var _months = null;

		var ADD_BOOKING_VIEW = 'addBooking';

		//in another manager ?
		var _skeletonInfoCalendar = null;
		var _skeletonServiceConstraint = null;
		var _skeletonMemberConstraint = null;
		var _skeletonCustomer = null;
		var _skeletonPayment = null;
		var _skeletonGroup = null;

		function BackendManager(scope){
			FunctionsManager.call(this);
			angular.extend(BackendManager.prototype, FunctionsManager.prototype);
			CalendarManager.call(this, this);
			angular.extend(BackendManager.prototype, CalendarManager.prototype);
			AppointmentManager.call(this);
			angular.extend(BackendManager.prototype, AppointmentManager.prototype);
			LogNotificationsCenterManager.call(this, scope, this);
			angular.extend(BackendManager.prototype, LogNotificationsCenterManager.prototype);
			RESANewsManager.call(this, scope, this);
			angular.extend(BackendManager.prototype, RESANewsManager.prototype);

			this.countries = [];
			this.locale = 'fr_FR';
			this.jsonURL = '';
			this.currentRESAUser = null;
			this.token = null;
			this.loadingData = true;
			this.firstLoadingData = true;
			this.words = {};
			this.oldCustomers = [];
			this.customers = [];
			this.customersManagers = [];
			this.members = [];
			this.equipments = [];
			this.services = [];
			this.reductions = [];
			this.alerts = [];
			this.infoCalendars = [];
			this.serviceConstraints = [];
			this.memberConstraints = [];
			this.paymentsTypeList = [];
			this.idPaymentsTypeToName = {};
			this.appointments = [];
			this.oldGroups = [];
			this.oldCustomers = [];
			this.oldAlerts = [];
			this.oldInfoCalendars = [];
			this.oldServiceConstraint = [];
			this.oldMemberConstraints = [];
			this.groups = [];
			this.currentPromoCodes = [];
			this.timeformat = '';

			this.lastDateBackend = null;
			this.alertNotSameDateBackend = false;

			this.actionDropProcess = false;
			this.lastModificationDateGroup = new Date();
			this.lastModificationDateBooking = new Date();
			this.launchUpdateBackend = false;
			this.oldBookings = [];
			this.bookings = [];
			this.filteredBookings = [];
			this.paginationBookings = {step:10, number:1};
			this.displayedBookings  = [];
			this.payBookingActionLaunched = false;

			this.oldDateAppointmentsToLoad = [];
			this.dateAppointmentsToLoad = {minDate:new Date(), maxDate:new Date()};
			this.loadingAppointments = [];
			this.loadingAppointmentsStartDate = [];

			this.displayPopupCustomer = false;
			this.newCustomer = null;
			this.launchAddNewCustomer = false;

			//Customers
			this.displayedCustomers = [];
			this.paginationCustomers = {step:10, number:1};
			this.nbTotalCustomers = 0;
			this.searchCustomer = '';
			this.searchCustomersLaunched = false;

			//Filters
			this.filters = {};
			this.currentPage = '';
			this.viewMode = '';
			this.seeBookingDetails = true;
			this.fullScreenMode = false;
			this.seeBookingParticipants = false;
			this.filtersMonths = [];

			//Rapports
			this.rapportsBy = 'services';
			this.rapportsByServices = [];
			this.filteredRapportsByServices = [];
			this.paginationRapportsByServices = {step:10, number:1};
			this.displayedRapportsByServices = [];
			this.filteredRapportsByServicesPlanning = [];
			this.rapportsByServicesWithParticipantsNotAttribuated = [];
			this.paginationRapportsByServicesPlanning = {step:10, number:1};
			this.displayedRapportsByServicesPlanning = [];
			this.datePlanningMembers = null;
			this.displayMembersNotAvailables = false;

			this.editBookingCtrl = null;
			this.customerCtrl = null;
			this.quotationsListCtrl = null;

			this.dateOptions = {formatYear: 'yy', maxDate: new Date(2022, 5, 22),startingDay: 1};

			_scope = scope;
			_self = this;
		}

		BackendManager.prototype.initialize = function(words, timeformat, groupsManagement, locale, ajaxUrl, currentUrl, months, countries){
			this.words = words;
			this.timeformat = timeformat;
			this.settings = {firstParameterDone:true, groupsManagement:groupsManagement};
			this.locale = locale;
			_ajaxUrl = ajaxUrl;
			_currentUrl = currentUrl;
			_months = months;
			this.countries = countries;
			this.filtersMonths = this.calculateMonths();
			this.initializationData();
		}

		BackendManager.prototype.initializationData = function(){
			var data = {
				action:'initializationDataAppointments'
			}
			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					data = JSON.parse(data);
					_self.services = data.services;
					_self.settings = data.settings;
					_self.reductions = data.reductions;
					_self.paymentsTypeList = data.paymentsTypeList;
					_self.idPaymentsTypeToName = data.idPaymentsTypeToName;
					_self.appointments = [];
					_self.logNotificationsWaitingNumber = data.logNotificationsWaitingNumber;
					_self.logNotifications = data.logNotifications;
					_self.currentRESAUser = data.currentRESAUser;
					_self.token = data.token;
					_self.jsonURL = data.jsonURL;
					_self.associatedMember = data.associatedMember;

					_self.bookings = [];
					_self.customers = [];
					_self.groups = [];
					_self.customersManagers = data.customersManagers;

					_self.members = data.members;
					_self.equipments = data.equipments;
					_self.currentPromoCodes = data.currentPromoCodes;

					_skeletonInfoCalendar = data.skeletonInfoCalendar;
					_skeletonServiceConstraint = data.skeletonServiceConstraint;
					_skeletonMemberConstraint = data.skeletonMemberConstraint;
					_skeletonCustomer = data.skeletonCustomer;
					_skeletonPayment = data.skeletonPayment;
					_skeletonGroup = data.skeletonGroup;
		      _skeletonCustomer.createWpAccount = true;
		      _skeletonCustomer.notify = true;
					_self.newCustomer = JSON.parse(JSON.stringify(_skeletonCustomer));
					_self.paymentsTypeList = _self.generatePaymentsTypeList();

					_self.reinitFilters(true);
					_self.loadingData = false;
					_self.displayCurrentPage();
					_self.displayCurrentView();
					_self.displayCurrentRapports();

					_self.RESANewsLastViewNumber = data.settings.lastRESANewsId;
					_self.getRESANews(1, 10);

					setInterval(function(){ _self.updateBackend() }, 15000);
					_self.launchBackgroundAppointmentsLoading();
					_self.searchCustomers('', 1, 10);
				});
			}).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		BackendManager.prototype.searchCustomers = function(search, page, limit){
			if(!this.searchCustomersLaunched && !this.settings.staffIsConnected){
				this.searchCustomersLaunched = true;
				jQuery.ajax({
			    type: "POST",
			    url: this.jsonURL + '/resa/v1/customers/' + this.token,
			    data: JSON.stringify({ search:search, page:page, limit:limit }),
			    contentType: "application/json; charset=utf-8",
			    dataType: "json",
			    success: function(data){
						_scope.$apply(function(){
							 _self.searchCustomersLaunched = false;
							_self.displayedCustomers = data.customers;
							_self.nbTotalCustomers = data.nbTotalCustomers;
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



		/**
		 * return the ajax url
		 */
		BackendManager.prototype.getAjaxUrl = function(){
			return _ajaxUrl;
		}

		/**
		 * return the ajax url
		 */
		BackendManager.prototype.getJSONUrl = function(){
			return this.jsonURL;
		}

		BackendManager.prototype.displayCurrentPage = function(){
			this.currentPage = $location.search().subPage;
			if(this.currentPage == null){
				this.currentPage  = 'reservations';
			}
			if(this.settings != null && this.settings.staffIsConnected && this.currentPage != 'planning' && this.currentPage != 'reservations'){
				this.currentPage = 'planning';
				this.setViewMode('members');
			}
			if(this.settings != null && this.settings.staffIsConnected && !this.settings.staff_display_bookings_tab && this.currentPage == 'reservations'){
				this.currentPage = 'planning';
				this.setViewMode('members');
			}
		}

		BackendManager.prototype.displayCurrentView = function(){
			var view = $location.search().view;
			if(view != null && view == ADD_BOOKING_VIEW && (this.settings != null && !this.settings.staffIsConnected)){
				_self.setViewMode('calendar', true);
			}
			else {
				if(view == null){
					if(this.currentPage == 'planning') view  = 'members';
					else view  = 'calendar';
				}
				else if(view == 'display'){
					var id = $location.search().id;
					if(id != null){
						this.openCustomerDialog(id);
					}
				}
				if(this.settings != null && this.settings.staffIsConnected && this.currentPage == 'planning'){
					view  = 'members';
				}
				_self.setViewMode(view);
			}
		}

		BackendManager.prototype.displayCurrentRapports = function(){
			this.rapportsBy = $location.search().rapportsBy;
			if(this.rapportsBy == null){
				this.rapportsBy  = 'services';
			}
		}

		BackendManager.prototype.setCurrentPage = function(page){
			if(this.settings != null){
				$location.search('subPage', page);
				this.currentPage = page;
				if(this.settings.staffIsConnected){
					if(this.settings != null && this.settings.staffIsConnected && this.currentPage != 'planning' && this.currentPage != 'reservations'){
						this.currentPage = 'planning';
					}
					if(this.settings != null && this.settings.staffIsConnected && !this.settings.staff_display_bookings_tab && this.currentPage == 'reservations'){
						this.currentPage = 'planning';
					}
				}
				if(this.currentPage == 'planning' && this.settings != null){
					this.setViewMode('members');
				}
				else if(this.currentPage == 'reservations' && (this.viewMode != 'calendar' && this.viewMode != 'bookingsList' && this.viewMode != 'rapports')){
					this.setViewMode('calendar');
				}
				else if(this.currentPage == 'quotations' && this.loadCompleted && this.quotationsListCtrl!=null && this.quotationsListCtrl.bookings.length == 0) {
					this.quotationsListCtrl.launchGetQuotations();
				}
				else if(this.currentPage == 'reservations' && this.viewMode == 'calendar') {
					this.changeCalendarView();
				}else if(this.currentPage == 'logNotifications' && this.loadCompleted) {
					this.openLogNotificationsPage();
				}

			}
		}

		BackendManager.prototype.setViewMode = function(viewMode, addBooking){
			if(viewMode == ADD_BOOKING_VIEW || addBooking){
				//this.openDialogAddAppointments();
				if(viewMode != ADD_BOOKING_VIEW){
					$location.search('view', viewMode);
					this.viewMode = viewMode;
					this.openEditBooking();
				}
			}
			else {
				var oldView = this.viewMode;
				$location.search('view', viewMode);
				this.viewMode = viewMode;
			}
		}

		BackendManager.prototype.setCurrentRapports = function(rapportsBy){
			$location.search('rapportsBy', rapportsBy);
			this.rapportsBy = rapportsBy;
		}

		BackendManager.prototype.isAllAppointmentsLoaded = function(){
			if(this.loadCompleted && this.launchUpdateBackend) return true;
			for(var i = 0; i < this.loadingAppointments.length; i++){
				if(this.loadingAppointments[i] != null && this.loadingAppointments[i]){
					return false
				}
			}
			return true;
		}

		BackendManager.prototype.canLoadPassedDate = function(){
			if(!this.settings.staffIsConnected) return true;
			var minDate = new Date(this.filters.dates.startDate);
			var actualDate = this.clearTime(new Date());
			return this.settings.staff_old_appointments_displayed || minDate.getTime() >= actualDate.getTime();
		}

		BackendManager.prototype.dateAlreadyLoaded = function(date){
			date = new Date(date);
			var minDate = new Date(this.dateAppointmentsToLoad.minDate);
			var maxDate = new Date(this.dateAppointmentsToLoad.maxDate);
			return minDate.getTime() <= date.getTime() && date.getTime() <= maxDate.getTime();
		}

		BackendManager.prototype.datesAlreadyLoaded = function(){
			var startDate = new Date(this.filters.dates.startDate);
			var endDate = new Date(this.filters.dates.endDate);
			var minDate = new Date(this.dateAppointmentsToLoad.minDate);
			var maxDate = new Date(this.dateAppointmentsToLoad.maxDate);

			return minDate.getTime() <= startDate.getTime() && endDate.getTime() <= maxDate.getTime();
		}

		BackendManager.prototype.setDatesInAllViews = function(date){
			this.datePlanningMembers = new Date(date);
			this.viewDate = new Date(date);
			if(this.planningServiceCtrl != null){
				this.planningServiceCtrl.render();
			}
		}

		BackendManager.prototype.launchBackgroundAppointmentsLoading = function(){
			if(this.canLoadPassedDate()){
				this.oldDateAppointmentsToLoad = {
					minDate: new Date(this.dateAppointmentsToLoad.minDate),
					maxDate: new Date(this.dateAppointmentsToLoad.maxDate)
				}
				this.oldBookings = this.bookings;
				this.oldCustomers = this.customers;
				this.oldGroups = this.groups;
				this.oldAlerts = this.alerts;
				this.oldInfoCalendars = this.infoCalendars;
				this.oldServiceConstraints = this.serviceConstraints;
				this.oldMemberConstraints = this.memberConstraints;
				this.bookings = [];
				this.customers = [];
				this.groups = [];
				this.alerts = [];
				this.infoCalendars = [];
				this.loadCompleted = false;
				this.loadingData = true;

				var startDate = new Date(this.filters.dates.startDate);
				var endDate = new Date(this.filters.dates.endDate);
				console.log('launchBackgroundAppointmentsLoading - date : ' + startDate + ' ' + endDate);
				this.dateAppointmentsToLoad.minDate = startDate;
				this.dateAppointmentsToLoad.maxDate = endDate;
				this.backgroundAppointmentsLoading(startDate);
			}
			else {
				sweetAlert('', 'Vous n\'avez pas le droit de charger une date dans le passée', 'error');
			}
		}


		BackendManager.prototype.cancelBackgroundAppointmentsLoading  = function(){
			this.loadingData = false;
			this.loadCompleted = true;
			this.bookings = this.oldBookings;
			this.groups = this.oldGroups;
			this.customers = this.oldCustomers;
			this.oldInfoCalendars = this.oldAlerts;
			this.infoCalendars = this.oldInfoCalendars;
			this.serviceConstraints = this.oldServiceConstraints;
			this.memberConstraints = this.oldMemberConstraints;
			this.dateAppointmentsToLoad.minDate = new Date(this.oldDateAppointmentsToLoad.minDate);
			this.dateAppointmentsToLoad.maxDate = new Date(this.oldDateAppointmentsToLoad.maxDate);
			this.datePlanningMembers = new Date(this.dateAppointmentsToLoad.minDate);
			this.viewDate = new Date(this.dateAppointmentsToLoad.minDate);
		}

		BackendManager.prototype.backgroundAppointmentsLoading = function(startDate){
			if(!this.loadCompleted){
				var days = 7;
				var maxSameTime = 3;
				this.loadingAppointments = [];
				this.loadingAppointmentsStartDate = [];
				var i = 0;
				var stopped = false;
				startDate = new Date(startDate);
				var endDate = new Date(startDate);
				endDate.setDate(endDate.getDate() + days)
				do{
					if(endDate > this.dateAppointmentsToLoad.maxDate){
						endDate = new Date(this.dateAppointmentsToLoad.maxDate);
						stopped = true;
					}
					this.getAppointmentsByDates(new Date(startDate), new Date(endDate), i);
					startDate.setDate(startDate.getDate() + days);
					endDate.setDate(endDate.getDate() + days);
					i++;
				}
				while(i < maxSameTime && !stopped);
				if(stopped){
					this.loadCompleted = true;
				}
			}
			else if(this.loadingData){
				this.loadingData = false;
				this.firstLoadingData = false;
				this.oldBookings = [];
				this.oldGroups = [];
				this.oldCustomers = [];
				this.oldAlerts = [];
				this.oldInfoCalendars = [];
				this.oldServiceConstraints = [];
				this.oldMemberConstraints = [];
				this.datePlanningMembers = new Date(this.dateAppointmentsToLoad.minDate);
				this.viewDate = new Date(this.dateAppointmentsToLoad.minDate);
				this.updateBookings(this.bookings);
				if($location.search().subPage == 'quotations' && this.loadCompleted && this.quotationsListCtrl!=null && this.quotationsListCtrl.bookings.length == 0) {
					this.quotationsListCtrl.launchGetQuotations();
				}
				if($location.search().subPage == 'logNotifications' && this.loadCompleted) {
					this.openLogNotificationsPage();
				}
			}
		}


		BackendManager.prototype.getAppointmentsByDates = function(startDate, endDate, i){
			this.loadingAppointments[i] = true;
			this.loadingAppointmentsStartDate[i] = startDate.toUTCString();
			var data = {
				action:'getAppointmentsByDates',
				filters:JSON.stringify({places:this.filters.places}),
				startDate: $filter('formatDateTime')(startDate),
				endDate: $filter('formatDateTime')(endDate)
			}
			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					var index = _self.loadingAppointmentsStartDate.indexOf(startDate.toUTCString());
					if(index != -1){
						_self.loadingAppointments[index] = false;
					}
					data = JSON.parse(data);
					if(_self.loadingData){
						var groups = data.groups;
						_self.updateGroups(groups);
						var bookings = data.bookings;
						for(var i = 0; i < bookings.length; i++){
							var booking = bookings[i];
							_self.updateBooking(booking, false);
						}
						var customers = data.customers;
						for(var i = 0; i < customers.length; i++){
							var customer = customers[i];
							for(var i = 0; i < customers.length; i++){
								if(_self.getCustomerById(customers[i].ID) != null){
									_self.updateCustomer(customers[i]);
								}
								else _self.addCustomer(customers[i]);
							}
						}
						var alerts = data.alerts;
						for(var i = 0; i < alerts.length; i++){
							var alert = alerts[i];
							_self.updateAlert(alert);
						}
						var infoCalendars = data.infoCalendars;
						for(var i = 0; i < infoCalendars.length; i++){
							var infoCalendar = infoCalendars[i];
							_self.updateInfoCalendar(infoCalendar);
						}
						var serviceConstraints = data.serviceConstraints;
						for(var i = 0; i < serviceConstraints.length; i++){
							var serviceConstraint = serviceConstraints[i];
							_self.updateServiceConstraints(serviceConstraint, false);
						}
						var memberConstraints = data.memberConstraints;
						for(var i = 0; i < memberConstraints.length; i++){
							var memberConstraint = memberConstraints[i];
							_self.updateMemberConstraints(memberConstraint, false);
						}
					}
					if(_self.isAllAppointmentsLoaded() && _self.loadingAppointmentsStartDate.length > 0){
						var date = new Date(_self.loadingAppointmentsStartDate[_self.loadingAppointmentsStartDate.length - 1]);
						date.setDate(date.getDate() + 7);
						_self.backgroundAppointmentsLoading(date);
					}
				});
			}).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		/**
		 *
		 */
		BackendManager.prototype.updateBackend = function(){
			if(this.loadCompleted && this.isAllAppointmentsLoaded()){
				this.launchUpdateBackend = true;
				var lastModificationDateCustomer = null;
				for(var i = 0; i < this.customers.length; i++){
					var customer = this.customers[i];
					if(lastModificationDateCustomer == null || lastModificationDateCustomer.getTime() < new Date(customer.modificationDate).getTime()){
						lastModificationDateCustomer = new Date(customer.modificationDate);
					}
				}
				if(lastModificationDateCustomer == null) lastModificationDateCustomer = new Date();
				var lastModificationDateGroup = $filter('formatDateTime')(this.lastModificationDateGroup);
				var lastModificationDateBooking = $filter('formatDateTime')(this.lastModificationDateBooking);
				var lastModificationDateCustomer = $filter('formatDateTime')(lastModificationDateCustomer);
				var lastIdLogNotifications = this.getLastIdLogNotifications();

				var data = {
					action:'updateBackend',
					filters:JSON.stringify({places:this.filters.places}),
					startDate:$filter('formatDateTime')(this.dateAppointmentsToLoad.minDate),
					endDate:$filter('formatDateTime')(this.dateAppointmentsToLoad.maxDate),
					lastModificationDateBooking:JSON.stringify(lastModificationDateBooking),
					lastModificationDateCustomer:JSON.stringify(lastModificationDateCustomer),
					lastModificationDateGroup:JSON.stringify(lastModificationDateGroup),
					lastModificationDateNotificationsTemplates:JSON.stringify(this.settings.notifications.notifications_templates_last_modification_date),
					lastIdLogNotifications:JSON.stringify(lastIdLogNotifications)
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						var bookings = data.bookings;
						var groups = data.groups;
						var settings = data.settings;
						if(groups.length > 0){
							_self.updateGroups(groups);
						}
						if(bookings.length == 0 && groups.length > 0){
							_self.generateRapportsByServices();
						}
						var customers = data.customers;
						for(var i = 0; i < customers.length; i++){
							if(_self.getCustomerById(customers[i].ID) != null){
								_self.updateCustomer(customers[i]);
							}
							else {
								_self.addCustomer(customers[i]);
							}
						}
						var alerts = data.alerts;
						for(var i = 0; i < alerts.length; i++){
							var alert = alerts[i];
							_self.updateAlert(alert);
						}
						var needRefreshBookings = false;
						for(var i = 0; i < bookings.length; i++){
							var booking = bookings[i];
							if(_self.applyFiltersDate(booking) != null){
								needRefreshBookings = true;
								var arrayOldBookings = booking.linkOldBookings.split(',');
								for(var j = 0; j < arrayOldBookings.length; j++){
									_self.removeBookingById(arrayOldBookings[j], false);
								}
								_self.updateBooking(booking, false);
							}
						}
						if(needRefreshBookings){
							_self.updateBookings(_self.bookings);
						};
						_self.logNotificationsWaitingNumber = data.logNotificationsWaitingNumber;
						_self.addNewLogNotifications(data.logNotifications);
						if(settings.notifications != null){
							_self.settings.notifications.notifications_templates = settings.notifications.notifications_templates;
							_self.settings.notifications.notifications_templates_last_modification_date = settings.notifications.notifications_templates_last_modification_date;
						}
						if(settings.currentVersion != null){
							_self.settings.currentVersion = settings.currentVersion;
						}
						_self.lastDateBackend = data.lastDateBackend;
						_self.isNotSameTimeZone();
						_self.launchUpdateBackend = false;
					});
				});
			}
		}


		/**
		 *
		 */
		BackendManager.prototype.displayLogIfPlaceIsSelected = function(log, filters){
			var places = log.idPlaces.split(',');
			if(log.idPlaces.length == 0 || filters == null || filters.places == null) return true;
			var ok = false;
			for(var i = 0; i < places.length; i++){
				var idPlace = places[i];
				if(idPlace.length > 0 && filters.places[idPlace] != null){
					ok = ok || filters.places[idPlace];
				}
			}
			return ok;
		}

		/**
		 * open the log notification center
		 */
		BackendManager.prototype.switchLogNotificationCenter = function(){
			if(this.logNotificationsCenterDisplayed){
				this.closeLogNotificationCenter();
			}
			else {
				this.logNotificationsCenterDisplayed = true;
				this.openLogNotificationsCenter();
				//Listen click
				jQuery('body').click(function(){ _scope.$apply(function(){ _self.closeLogNotificationCenter(); }); });
				jQuery('#notif_center_wrapper').click(function(event){ event.stopPropagation(); });
				jQuery('.resa_popup').click(function(event){ event.stopPropagation(); });
			}
		}

		/**
		 * close the log notification center
		 */
		BackendManager.prototype.closeLogNotificationCenter = function(){
			this.logNotificationsCenterDisplayed = false;
			//Deactive listened
			jQuery('body').unbind('click');
			jQuery('#notif_center_wrapper').unbind('click');
		}


		BackendManager.prototype.printDiv = function(id) {
      var content = document.getElementById(id).innerHTML;
  		var newWindow = window.open ('','', "menubar=yes,scrollbars=yes,resizable=yes");
  		newWindow.document.open();
  		newWindow.document.write("<html><head><title></title></head><body class='printing'>"+content+"</body></html>");
  		var arrStyleSheets = document.getElementsByTagName("link");
  		for (var i = 0; i < arrStyleSheets.length; i++){
  			newWindow.document.head.appendChild(arrStyleSheets[i].cloneNode(true));
  		}
  		var arrStyle = document.getElementsByTagName("style");
  		for (var i = 0; i < arrStyle.length; i++){
  			newWindow.document.head.appendChild(arrStyle[i].cloneNode(true));
  		}
  		newWindow.document.close();
  		newWindow.focus();
  		setTimeout(function(){
				newWindow.print();
				newWindow.close(); },
				1000);
    }

		BackendManager.prototype.today = function(){
			return new Date();
		}

		BackendManager.prototype.tomorrow = function(){
			var tomorrow = new Date();
			tomorrow.setDate(tomorrow.getDate() + 1);
			return tomorrow;
		}

		BackendManager.prototype.yesterday = function(){
			var tomorrow = new Date();
			tomorrow.setDate(tomorrow.getDate() - 1);
			return tomorrow;
		}


		BackendManager.prototype.changeDates = function(){
			var dates = this.filters.dates;
			if(this.filters.seeBookingsValue == 1){
				this.setCustomDate(new Date());
			}
			else if(this.filters.seeBookingsValue == 2){
				var tomorrow = new Date();
				tomorrow.setDate(tomorrow.getDate() + 1);
				this.setCustomDate(tomorrow);
			}
			else if(this.filters.seeBookingsValue == 3){
				var today = new Date();
				var end = new Date();
				end.setDate(end.getDate() + 7);
				dates.startDate = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 0, 0, 0, 0);
				dates.endDate = new Date(end.getFullYear(), end.getMonth(), end.getDate(), 23, 59, 59, 99);
			}
			else if(this.filters.seeBookingsValue == 4){
				var yesterday = new Date();
				yesterday.setDate(yesterday.getDate() - 1);
				this.setCustomDate(yesterday);
			}
			else if(this.filters.seeBookingsValue == 5){
				this.setCustomDate(dates.startDate);
			}
		}

		BackendManager.prototype.setCustomDate = function(date){
			this.filters.dates.startDate = this.clearTime(new Date(date));
			this.filters.dates.endDate = new Date(this.filters.dates.startDate.getFullYear(), this.filters.dates.startDate.getMonth(), this.filters.dates.startDate.getDate(), 23, 59, 59, 99);
		}

		BackendManager.prototype.changeCurrentMonthDate = function(){
			this.filters.dates.endDate = new Date(this.filters.month.date);
			this.filters.dates.endDate.setHours(23);
			this.filters.dates.endDate.setMinutes(59);
			this.filters.dates.endDate.setSeconds(59);
			this.filters.dates.endDate.setMilliseconds(99);
			this.filters.dates.startDate = new Date(this.filters.dates.endDate.getFullYear(), this.filters.dates.endDate.getMonth(), 1);
		}

		/**
		 * calculates months vue
		 */
		BackendManager.prototype.calculateMonths = function(){
			var months = [];
			var actualDate = new Date();
			for(var i = 0; i < 12; i++){
				var date = new Date();
				date.setMonth(actualDate.getMonth() - i, 0);
				months.push({ date:date });
				if(i > 0){
					var date = new Date();
					date.setMonth(actualDate.getMonth() + i, 0);
					months.push({ date:date });
				}
			}
			months.sort(function(monthA, monthB){
				return monthA.date.getTime() - monthB.date.getTime();
			})
			return months;
		}

		BackendManager.prototype.changeDatePlanningMembers = function(){
			this.viewDate = new Date(this.datePlanningMembers);
			if(this.datePlanningMembers < this.dateAppointmentsToLoad.minDate || this.datePlanningMembers > this.dateAppointmentsToLoad.maxDate){
				this.filters.seeBookingsValue = 5;
				this.setCustomDate(this.datePlanningMembers);
				this.launchBackgroundAppointmentsLoading();
			}
			else if(this.planningServiceCtrl != null){
				this.planningServiceCtrl.render();
			}
		}

		BackendManager.prototype.incrementDatePlanningMembers = function(){
			this.datePlanningMembers.setDate(this.datePlanningMembers.getDate() + 1);
			this.viewDate = new Date(this.datePlanningMembers);
			if(this.datePlanningMembers > this.dateAppointmentsToLoad.maxDate){
				this.filters.seeBookingsValue = 5;
				this.setCustomDate(this.datePlanningMembers);
				this.launchBackgroundAppointmentsLoading();
			}
			else {
				this.generateRapportsServicesByDateWithNotInGroups();
			}
		}

		BackendManager.prototype.decrementDatePlanningMembers = function(){
			this.datePlanningMembers.setDate(this.datePlanningMembers.getDate() - 1);
			this.viewDate = new Date(this.datePlanningMembers);
			if(this.datePlanningMembers < this.dateAppointmentsToLoad.minDate){
				this.filters.seeBookingsValue = 5;
				this.setCustomDate(this.datePlanningMembers);
				this.launchBackgroundAppointmentsLoading();
			}
			else {
				this.generateRapportsServicesByDateWithNotInGroups();
			}
		}

		BackendManager.prototype.loadBookingPeriod = function(booking){
			var startDate = null;
			var endDate = null;
			for(var i = 0; i < booking.appointments.length; i++){
				var appointment = booking.appointments[i];
				var startDateAppointment = new Date(appointment.startDate);
				var endDateAppointment = new Date(appointment.endDate);
				if(startDate == null || startDateAppointment < startDate) startDate = startDateAppointment;
				if(endDate == null || endDateAppointment > endDate) endDate = endDateAppointment;
			}
			endDate.setHours(23);
			endDate.setMinutes(59);
			endDate.setSeconds(59);
			endDate.setMilliseconds(99);
			startDate.setHours(0);
			startDate.setMinutes(0);
			startDate.setSeconds(0);
			startDate.setMilliseconds(0);

			this.filters.dates.startDate = startDate;
			this.filters.dates.endDate = endDate;

			if(startDate.getDate() == endDate.getDate() &&
				startDate.getMonth() == endDate.getMonth() &&
				startDate.getFullYear() == endDate.getFullYear()){
				this.filters.seeBookingsValue = 5;
			}
			else {
				this.filters.seeBookingsValue = 6;
			}
			this.launchBackgroundAppointmentsLoading();
		}

		BackendManager.prototype.reinitFilters = function(init){
			this.seeBookingDetails = true;
			this.filters.typeFilters = {};
			this.filters.search = '';
			if(init != null && init){
				this.filters.seeBookingsValue = 1;
				this.filters.dates = {
					startDate: new Date(2016, 6, 1),
					endDate: new Date()
				};
			}
			this.filters.places={};
			this.filters.states={};
			this.filters.statePaymentsList={};
			this.filters.paymentsTypeList={};
			this.filters.tags={};
			this.filters.services={};
			this.filters.members={};

			if(this.settings != null){
				var filterSettings = this.currentRESAUser.filterSettings;
				if(this.settings.places!=null){
					for(var i = 0; i < this.settings.places.length; i++){
						_self.filters.places[this.settings.places[i].id] = filterSettings == null || filterSettings.places == null || filterSettings.places[this.settings.places[i].id] == null || filterSettings.places[this.settings.places[i].id];
					}
				}
				if(this.settings.statePaymentsList!=null){
					for(var i = 0; i < _self.settings.statePaymentsList.length; i++){
						_self.filters.statePaymentsList[_self.settings.statePaymentsList[i].id] = true
					}
				}
				if(this.settings.appointmentTags!=null){
					_self.initStateFilters();
					_self.filters.tags['no_tag'] = true;
					for(var i = 0; i < _self.settings.appointmentTags.length; i++){
						_self.filters.tags[_self.settings.appointmentTags[i].id] = true;
					}
				}
			}
			var services = this.getNotOldServices();
			for(var i = 0; i < services.length; i++){
				this.filters.services[services[i].id] = true;
			}
			for(var i = 0; i < this.members.length; i++){
				var member = this.members[i];
				if(this.displayMemberFilter(member)){
					this.filters.members[member.id] = true;
				}
				else {
					this.filters.members[member.id] = false;
				}
			}
			/*
			for(var i = 0; i < this.paymentsTypeList.length; i++){
				this.filters.paymentsTypeList[i] = this.paymentsTypeList[i].id;
			}
			*/
			this.filters.dates.endDate.setDate(this.filters.dates.endDate.getDate() + 1);
			this.changeDates();
			this.generateFilteredBookings();
		}

		BackendManager.prototype.setFilterSettings = function(){
			this.currentRESAUser.filterSettings = {places:this.filters.places};
		}

		BackendManager.prototype.initStateFilters = function(){
			_self.filters.states['quotation'] = true;
			for(var i = 0; i < _self.settings.statesList.length; i++){
				_self.filters.states[_self.settings.statesList[i].id] = _self.settings.statesList[i].selected;
			}
			_self.filters.states['waiting_payment'] = true;
			_self.filters.states['waiting_expired'] = true;
		}

		BackendManager.prototype.setOnlyQuotationFilter = function(){
			if(this.filters.states != null){
				this.filters.states['quotation'] = true;
				for(var i = 0; i < this.settings.statesList.length; i++){
					this.filters.states[this.settings.statesList[i].id] = false;
				}
				_self.filters.states['waiting_payment'] = false;
				_self.filters.states['waiting_expired'] = false;
				this.generateFilteredBookings();
			}
		}

		/**
		 * all filters is off
		 */
		BackendManager.prototype.allFiltersIsOff = function(filters){
			var value = true;
			for(var key in filters){
				value = value && !filters[key];
			}
			return value;
		}

		/**
		 * all filters is off
		 */
		BackendManager.prototype.setAllValueFilters = function(filters, value){
			for(var key in filters){
				filters[key] = value;
			}
		}



		/**
		 * return true if only appointments members
		 */
		BackendManager.prototype.isStaffOnlyAppointmentsDisplayed = function(){
			return this.settings.staffIsConnected && this.settings.staff_only_appointments_displayed != null && this.settings.staff_only_appointments_displayed;
		}

		/**
		 * return true if only appointments members
		 */
		BackendManager.prototype.displayMemberFilter = function(member){
			return !this.settings.staffIsConnected || this.settings.staff_only_appointments_displayed == null || !this.settings.staff_only_appointments_displayed || this.associatedMember.id == -1 ||
			this.associatedMember.id == member.id;
		}

		/**
		 * generate the payments types.
		 */
		BackendManager.prototype.generatePaymentsTypeList = function(){
			var paymentTypes = [];
			//paymentsType not activated but already used in booking
			var paymentsTypeAlreadyUsed = [];
			for(var i = 0; i < this.bookings.length; i++){
				for(var j = 0; j < this.bookings[i].payments.length; j++){
					var payment = this.bookings[i].payments[j];
					var paymentType = payment.type;
					if(payment.type == 'cash' || payment.type == 'card'){
						paymentType = 'later';
					}
					if(paymentsTypeAlreadyUsed.indexOf(paymentType) == -1){
						paymentsTypeAlreadyUsed.push(paymentType);
					}
				}
			}
			for(var i = 0; i < this.paymentsTypeList.length; i++){
				if(this.paymentsTypeList[i].activated ||	paymentsTypeAlreadyUsed.indexOf(this.paymentsTypeList[i].id) != -1){
					paymentTypes.push(this.paymentsTypeList[i]);
				}
			}
			for(var i = 0; i < this.settings.custom_payment_types.length; i++){
				var customPaymentTypes = this.settings.custom_payment_types[i];
				paymentTypes.push({id: customPaymentTypes.id, activated: true, title: customPaymentTypes.label, custom:true});
			}
			return paymentTypes;
		}

		/**
		 *
		 */
		BackendManager.prototype.getPaymentName = function(idPayment, name){
			var customerType = false;
			if(idPayment.indexOf('customer_') != -1) {
				idPayment = idPayment.substring('customer_'.length);
				customerType = true;
			}

			var result = this.paymentsTypeList[idPayment];
			if(result == null){
				for(var i = 0; i < this.paymentsTypeList.length; i++){
					var paymentType = this.paymentsTypeList[i];
					if(paymentType.id == idPayment){
						result = paymentType.title;
					}
				}
				if(result == null && this.idPaymentsTypeToName[idPayment]){
					result = this.idPaymentsTypeToName[idPayment];
				}
				if(result == null){
					for(var i = 0; i < this.settings.custom_payment_types.length; i++){
						var paymentType = this.settings.custom_payment_types[i];
						if(paymentType.id == idPayment){
							result = paymentType.label;
						}
					}
				}
				if(result == null && name != null){
					result = name;
				}
				if(result == null){
					result = idPayment;
				}
			}
			if(customerType) result = this.getPaymentName('customer', 'Compte client') + '('+ result +')';
			return result;
		}

		/**
		 *
		 */
		BackendManager.prototype.setAlerts = function(alerts){
			if(alerts != null){
				for(var i = 0; i < alerts.length; i++){
					var alert = alerts[i];
					_self.updateAlert(alert);
				}
			}
		}

		/**
		 *
		 */
		BackendManager.prototype.deleteGroup = function(idGroup){
			var index = -1;
			for(var i = 0; i < this.groups.length; i++){
				if(this.groups[i].id == idGroup){
					index = i;
				}
			}
			if(index != -1){
				this.groups.splice(index, 1);
			}
		}

		BackendManager.prototype.updateGroups = function(groups){
			for(var i = 0; i < groups.length; i++){
				var group = groups[i];
				var localLastModificationDate = $filter('parseDate')(group.lastModificationDate);
				if(localLastModificationDate > this.lastModificationDateGroup){
					this.lastModificationDateGroup = new Date(localLastModificationDate);
				}
				this.updateGroup(group);
			}
		}

		BackendManager.prototype.updateGroup = function(group){
			var found = false;
			var lastModificationDate = new Date();
			for(var i = 0; i < this.groups.length; i++){
				var localGroup = this.groups[i];
				if(localGroup.id == group.id){
					found = true;
					if($filter('parseDate')(group.lastModificationDate).getTime() > $filter('parseDate')(localGroup.lastModificationDate).getTime()){
						this.groups[i] = group;
					}
				}
			}
			if(!found){
				this.groups.push(group);
			}
		}

		BackendManager.prototype.updateAlert = function(alert){
			var found = false;
			for(var i = 0; i < this.alerts.length; i++){
				var local = this.alerts[i];
				if(local.id == alert.id){
					found = true;
					this.alerts[i] = alert;
				}
			}
			if(!found){
				this.alerts.push(alert);
			}
		}


		BackendManager.prototype.updateInfoCalendar = function(infoCalendar){
			var found = false;
			for(var i = 0; i < this.infoCalendars.length; i++){
				var localInfoCalendar = this.infoCalendars[i];
				if(localInfoCalendar.id == infoCalendar.id){
					found = true;
					this.infoCalendars[i] = infoCalendar;
				}
			}
			if(!found){
				this.infoCalendars.push(infoCalendar);
			}
		}

		BackendManager.prototype.addBookingAnnotations = function(booking){
			var lastModificationDateBooking = $filter('parseDate')(booking.modificationDate);
			if(lastModificationDateBooking > this.lastModificationDateBooking){
				this.lastModificationDateBooking = new Date(lastModificationDateBooking);
			}
			booking.cssPaymentState = this.calculateBookingPayment(booking);
			booking.intervals = [];
			booking.customer = this.getCustomerById(booking.idCustomer);
			var appointments = booking.appointments;
			if(appointments != null){
				for(var k = 0; k < appointments.length; k++){
					var appointment = appointments[k];
					//booking.intervals = this.addInterval(booking.intervals, appointment.startDate, appointment.endDate, appointment.state);
					appointment.customer = booking.customer;
				}
			}
			return booking;
		}

		BackendManager.prototype.calculateBookingPayment = function(booking){
			var result = 'paiement_none';
			if(booking != null){
				var isCancelledOrAbandonned = (booking.status == 'cancelled' || booking.status == 'abandonned');
				if(!isCancelledOrAbandonned){
					if(booking.paymentState == 'noPayment') result = 'paiement_none';
					else if(booking.paymentState == 'advancePayment') result = 'paiement_incomplete';
					else if(booking.paymentState == 'deposit') result = 'paiement_incomplete';
					else if(booking.paymentState == 'over') result = 'paiement_overpaiement';
					else result = 'paiement_done';
				}
				else {
					if(booking.paymentState == 'noPayment') result = 'paiement_remboursement_done';
					else if(booking.paymentState == 'advancePayment') result = 'paiement_remboursement';
					else if(booking.paymentState == 'deposit') result = 'paiement_remboursement';
					else if(booking.paymentState == 'complete') result = 'paiement_remboursement';
					else result = 'paiement_remboursement_done';
				}
			}
			return result;
		}

		BackendManager.prototype.isDeposit = function(booking){
			return booking.paymentState == 'deposit';
		}

		BackendManager.prototype.updateBookings = function(bookings){
			this.bookings = bookings;
			var newAppointments = [];
			for(var j = 0; j < bookings.length; j++){
				var booking = bookings[j];
				booking = this.addBookingAnnotations(booking);
			}
			this.appointments = newAppointments;
			this.generateRapportsByServices();
			this.generateFilteredBookings();
		}

		/**
		 * update booking
		 */
		BackendManager.prototype.updateBooking = function(booking, notUpdate){
			booking = this.addBookingAnnotations(booking);
			var found = false;
			for(var i = 0; i < this.bookings.length; i++){
				if(this.bookings[i].id == booking.id){
					found = true;
					this.bookings[i] = this.mergeBooking(this.bookings[i], booking);
				}
			}
			if(!found){
				this.bookings.push(booking);
			}
			else {
				if(this.notificationCtrl != null && this.notificationCtrl.opened){
					if(!this.notificationCtrl.noBooking() && this.notificationCtrl.booking.id == booking.id){
						this.notificationCtrl.updateBooking(booking);
					}
				}
				if(this.displayBookingsCtrl != null && this.displayBookingsCtrl.opened){
					if(!this.displayBookingsCtrl.noBookings()){
						this.displayBookingsCtrl.justReplaceBooking(booking);
					}
				}
				if(this.customerCtrl != null && this.customerCtrl.opened){
					if(this.customerCtrl.customer.ID == booking.idCustomer){
						this.customerCtrl.justReplaceBooking(booking);
					}
				}
			}
			if(notUpdate == null || notUpdate){
				this.updateBookings(this.bookings);
			}
		}

		/**
		 * copy if same or merge isn't same
		 */
		BackendManager.prototype.mergeBooking = function(booking, bookingToMerge){
			var allOldAppointments = [];
			for(var i = 0; i < booking.appointments.length; i++){
				var oldAppointment = booking.appointments[i];
				var found = false;
				for(var j = 0; j < bookingToMerge.appointments.length; j++){
					var appointment = bookingToMerge.appointments[j];
					if(oldAppointment.id == appointment.id){
						booking.appointments[i] = JSON.parse(JSON.stringify(bookingToMerge.appointments[j]));
						found = true;
						break;
					}
				}
				if(!found){
					allOldAppointments.push(oldAppointment);
				}
			}
			bookingToMerge.appointments = bookingToMerge.appointments.concat(allOldAppointments);
			return bookingToMerge;
		}

		BackendManager.prototype.removeBooking = function(booking){
			var newBookings = [];
			for(var i = 0; i < this.bookings.length; i++){
				if(this.bookings[i].id != booking.id){
					newBookings.push(this.bookings[i]);
				}
			}
			if(this.customerCtrl != null && this.customerCtrl.opened){
				if(booking.idCustomer == this.customerCtrl.customer.ID){
					this.customerCtrl.removeBooking(booking);
				}
			}
			this.bookings = newBookings;
			this.updateBookings(this.bookings);
			this.reloadCalendar();
		}

		BackendManager.prototype.removeBookingById = function(idBooking, reload){
			if(reload == null) reload = true;
			var newBookings = [];
			for(var i = 0; i < this.bookings.length; i++){
				if(this.bookings[i].id != idBooking){
					newBookings.push(this.bookings[i]);
				}
			}
			this.bookings = newBookings;
			if(reload){
				this.updateBookings(this.bookings);
				this.reloadCalendar();
			}
		}

		BackendManager.prototype.addCustomer = function(customer){
			this.customers.push(customer);
		}

		BackendManager.prototype.deleteCustomer = function(idCustomer){
			var allCustomers = [];
			for(var i = 0; i < this.customers.length; i++){
				if(this.customers[i].ID != idCustomer){
					allCustomers.push(this.customers[i]);
				}
			}
			this.customers = allCustomers;
		}

		BackendManager.prototype.updateCustomer = function(customer, oldID){
			if(oldID != null) {
				this.deleteCustomer(oldID);
				this.addCustomer(customer);
			}
			else {
				var copyCustomer = JSON.parse(JSON.stringify(customer));
				copyCustomer.bookings = [];
				for(var i = 0; i < this.customers.length; i++){
					if(this.isSameCustomer(this.customers[i], copyCustomer)){
						this.customers[i] = copyCustomer;
					}
				}
			}
			var bookingToUpdate = [];
			for(var i = 0; i  < customer.bookings.length; i++){
				var booking = customer.bookings[i];
				if(this.applyFiltersDate(booking) != null){
					bookingToUpdate.push(booking)
				}
			}
			for(var i = 0; i < bookingToUpdate.length; i++){
				this.updateBooking(bookingToUpdate[i], (i == bookingToUpdate.length - 1));
			}

			if(this.customerCtrl != null){
				if(this.customerCtrl.opened && (this.isSameCustomer(this.customerCtrl.customer, customer) || (oldID!=null && this.customerCtrl.customer.ID == oldID))){
					this.customerCtrl.updateCustomer(customer);
				}
			}
			if(this.notificationCtrl != null && this.notificationCtrl.opened){
				if(this.isSameCustomer(this.notificationCtrl.customer, customer) || (oldID!=null && this.notificationCtrl.customer.ID == oldID)){
					this.notificationCtrl.updateCustomer(customer);
				}
			}
		}

		BackendManager.prototype.isSameCustomer = function(customerA, customerB){
			return customerA.ID == customerB.ID && customerA.isWpUser == customerB.isWpUser;
		}

		BackendManager.prototype.getInfosCalendarByDate = function(date){
			var infos = [];
			for(var i = 0; i < this.infoCalendars.length; i++){
				var info = this.infoCalendars[i];
				if((info.idPlaces.length == 0 || this.trueIfPlacesNotFilteredPlaces(this.getInfoCalendarPlaces(info), this.filters)) &&
					this.clearTime(this.parseDate(info.date)).getTime() <= this.clearTime(date).getTime() &&
					this.clearTime(date).getTime() <= this.clearTime(this.parseDate(info.dateEnd)).getTime()){
					infos.push(info);
				}
			}
			return infos;
		}

		BackendManager.prototype.getInfoWidth = function(info){
			var currentStartDate = this.setTimeWithStringTime(new Date(this.datePlanningMembers), info.startTime);
			var currentEndDate = this.setTimeWithStringTime(new Date(this.datePlanningMembers), info.endTime);

			var seconds = Math.floor((currentEndDate.getTime() - currentStartDate.getTime()) / 1000);
			return (10/3600) * seconds;
		}

		BackendManager.prototype.getInfoLeft = function(info){
			var currentStartDate = this.setTimeWithStringTime(new Date(this.datePlanningMembers), info.startTime);
			var borderDate = new Date(currentStartDate);
			borderDate = this.clearTime(borderDate);
			borderDate.setHours(9);
			var seconds = Math.floor((currentStartDate.getTime() - borderDate.getTime()) / 1000);
			return (10 / 3600) * seconds;
		}

		BackendManager.prototype.updateInfoCalendar = function(infoCalendar){
			var find = false;
			for(var i = 0; i < this.infoCalendars.length; i++){
				if(this.infoCalendars[i].id == infoCalendar.id){
					this.infoCalendars[i] = infoCalendar;
					find = true;
				}
			}
			if(!find){
				this.infoCalendars.push(infoCalendar);
			}
			this.reloadCalendar();
		}


		BackendManager.prototype.deleteInfoCalendar = function(infoCalendar){
			var newInfoCalendars = [];
			for(var i = 0; i < this.infoCalendars.length; i++){
				if(this.infoCalendars[i].id != infoCalendar.id){
					newInfoCalendars.push(this.infoCalendars[i]);
				}
			}
			this.infoCalendars = newInfoCalendars;
			this.reloadCalendar();
		}

		BackendManager.prototype.updateServiceConstraints = function(serviceConstraint, reload = true){
			var find = false;
			for(var i = 0; i < this.serviceConstraints.length; i++){
				if(this.serviceConstraints[i].id == serviceConstraint.id){
					this.serviceConstraints[i] = serviceConstraint;
					find = true;
				}
			}
			if(!find){
				this.serviceConstraints.push(serviceConstraint);
			}
			if(reload) this.reloadCalendar();
		}

		BackendManager.prototype.updateMemberConstraints = function(constraint, reload = true){
			var find = false;
			for(var i = 0; i < this.memberConstraints.length; i++){
				if(this.memberConstraints[i].id == constraint.id){
					this.memberConstraints[i] = constraint;
					find = true;
				}
			}
			if(!find){
				this.memberConstraints.push(constraint);
			}
			if(reload) this.reloadCalendar();
		}

		BackendManager.prototype.deleteServiceConstraint = function(serviceConstraint){
			var newServiceConstraints = [];
			for(var i = 0; i < this.serviceConstraints.length; i++){
				if(this.serviceConstraints[i].id != serviceConstraint.id){
					newServiceConstraints.push(this.serviceConstraints[i]);
				}
			}
			this.serviceConstraints = newServiceConstraints;
			this.reloadCalendar();
		}

		BackendManager.prototype.deleteMemberConstraint = function(constraint){
			var newMemberConstraints = [];
			for(var i = 0; i < this.memberConstraints.length; i++){
				if(this.memberConstraints[i].id != constraint.id){
					newMemberConstraints.push(this.memberConstraints[i]);
				}
			}
			this.memberConstraints = newMemberConstraints;
			this.reloadCalendar();
		}

		BackendManager.prototype.getBookingsByIdCustomer = function(idCustomer){
			var bookings = [];
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				if(booking.idCustomer == idCustomer){
					bookings.push(booking);
				}
			}
			return bookings;
		}

		BackendManager.prototype.getBookingById = function(idBooking){
			for(var i = 0; i < this.bookings.length; i++){
				if(this.bookings[i].id == idBooking)
					return this.bookings[i];
			}
			return null;
		}

		BackendManager.prototype.haveAppointmentInBooking = function(booking, idAppointment){
			if(booking != null && booking.appointments != null){
				for(var i = 0; i < booking.appointments.length; i++){
					var appointment = booking.appointments[i];
					if(appointment.id == idAppointment)
						return true;
				}
			}
			return false;
		}

		BackendManager.prototype.getCustomerManagerById = function(idCustomer){
			for(var i = 0; i < this.customersManagers.length; i++){
				if(this.customersManagers[i].ID == idCustomer)
					return this.customersManagers[i];
			}
			return null;
		}

		BackendManager.prototype.getCustomerById = function(idCustomer){
			for(var i = 0; i < this.customers.length; i++){
				if(this.customers[i].ID == idCustomer)
					return this.customers[i];
			}
			return null;
		}

		BackendManager.prototype.getServiceById = function(idService){
			for(var i = 0; i < this.services.length; i++){
				if(this.services[i].id == idService)
					return this.services[i];
			}
			return null;
		}

		/**
		 * Return service by id service if not old, the new version on this service else
		 */
		BackendManager.prototype.getLastVersionOnServiceById = function(idService){
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


		BackendManager.prototype.getCountryByCode = function(code){
			for(var i = 0; i < this.countries.length; i++){
				var country = this.countries[i];
				if(country.code == code){
					return country.name;
				}
			}
			return code;
		}

		BackendManager.prototype.getMemberById = function(idMember){
			for(var i = 0; i < this.members.length; i++){
				if(this.members[i].id == idMember)
					return this.members[i];
			}
			return null;
		}

		BackendManager.prototype.getEquipmentById = function(idEquipment){
			for(var i = 0; i < this.equipments.length; i++){
				if(this.equipments[i].id == idEquipment)
					return this.equipments[i];
			}
			return null;
		}

		BackendManager.prototype.getListPlaces = function(places){
			var text = '';
			for(var i = 0; i < places.length; i++){
				var place = this.getPlaceById(places[i]);
				if(place != null){
					if(text.length > 0) text += ', ';
					text += $filter('htmlSpecialDecode')(this.getTextByLocale(place.name, this.locale));
				}
			}
			return text;
		}

		BackendManager.prototype.getPlaceById = function(idPlace){
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

		BackendManager.prototype.getParticipantsParameter = function(idParameter){
			if(this.settings.form_participants_parameters != null){
				for(var i = 0; i < this.settings.form_participants_parameters.length; i++){
					if(this.settings.form_participants_parameters[i].id == idParameter){
						return this.settings.form_participants_parameters[i];
					}
				}
			}
			return null;
		}

		BackendManager.prototype.getTypeAccountName = function(customer){
			var typeAccountName = '';
			if(customer != null){
				if(customer.role == 'administrator') typeAccountName = 'Administrateur';
				else if(customer.role == 'RESA_Manager') typeAccountName = 'Manageur RESA';
				else if(customer.role == 'RESA_Staff') typeAccountName = $filter('htmlSpecialDecode')(this.getTextByLocale(this.settings.staff_word_single, this.locale));
				else {
					var typeAccount = this.settings.typesAccounts.find(element => {
						if(element.id == customer.typeAccount) return element;
					});
					if(typeAccount){
						typeAccountName = $filter('htmlSpecialDecode')(this.getTextByLocale(typeAccount.name, this.locale));
					}
				}
			}
			return typeAccountName;
		}

		BackendManager.prototype.isDisplaySum = function(fields){
			if(fields != null){
				for(var i = 0; i < fields.length; i++){
					if(fields[i].displaySum != null && fields[i].displaySum){
						return true;
					}
				}
			}
			return false;
		}

		BackendManager.prototype.getDisplaySum = function(field, participants){
			if(field.type != 'number' || field.displaySum == null || field.displaySum == false) return '';
			var sum = 0;
			for(var i = 0; i < participants.length; i++){
				var participant = participants[i];
				if(!isNaN(participant[field.varname])){
					sum += participant[field.varname];
				}
			}
			return sum;
		}

		BackendManager.prototype.getParticipantFieldName = function(participant, field, locale){
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

		BackendManager.prototype.getReductionById = function(idReduction){
			for(var i = 0; i < this.reductions.length; i++){
				if(this.reductions[i].id == idReduction)
					return this.reductions[i];
			}
			return null;
		}

		BackendManager.prototype.getTagById = function(idTag){
			if(this.settings != null && this.settings.appointmentTags!=null){
				for(var i = 0; i < this.settings.appointmentTags.length; i++){
	    		if(this.settings.appointmentTags[i].id == idTag){
	          return this.settings.appointmentTags[i];
	        }
	    	}
			}
    	return null;
    }

		BackendManager.prototype.getNotOldServices = function(){
			var services = [];
			for(var i = 0; i < this.services.length; i++){
				var service = this.services[i];
				if(!service.oldService){
					services.push(service);
				}
			}
			return services;
		}

		BackendManager.prototype.getServicePriceAppointment = function(service, idPrice){
			if(service != null){
				for(var i = 0; i < service.servicePrices.length; i++){
					var servicePrice = service.servicePrices[i];
					if(servicePrice.id == idPrice)
						return servicePrice;
				}
			}
			return null;
		}


		BackendManager.prototype.getServicePriceAppointmentName = function(idService, idPrice){
			var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), idPrice);
			if(priceModel == null) return 'Inconnu';
			return this.getTextByLocale(priceModel.name, this.locale);
		}

		BackendManager.prototype.haveEquipments = function(idService, idPrice){
			var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), idPrice);
			if(priceModel == null) return false;
			return priceModel.equipments.length > 0;
		}

		BackendManager.prototype.getServicePriceAppointmentEquipmentName = function(idService, idPrice){
			var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), idPrice);
			if(priceModel == null) return '';
			if(priceModel.equipments.length == 0) return '';
			return this.getTextByLocale(this.getEquipmentById(parseInt(priceModel.equipments[0])).name, this.locale);
		}

		BackendManager.prototype.getPriceNumberPrice = function(idService, appointmentNumberPrice){
			var priceModel = this.getServicePriceAppointment(this.getServiceById(idService), appointmentNumberPrice.idPrice)
			var total = 0;
			if(priceModel != null){
				total = appointmentNumberPrice.number * priceModel.price;
				if(!priceModel.notThresholded){
					total = 0;
					for(var i = 0; i < priceModel.thresholdPrices.length; i++){
						var thresholdPrice = priceModel.thresholdPrices[i];
						if(thresholdPrice.min <= appointmentNumberPrice.number && appointmentNumberPrice.number <= thresholdPrice.max){
							total = thresholdPrice.price;
						}
					}
				}
			}
			return total;
		}

		BackendManager.prototype.getServicesByCategory = function(category){
			var services = [];
			if(category != null){
				var allServicesByPlace = this.getServicesByPlace();
				for(var i = 0; i < allServicesByPlace.length; i++){
					var service = allServicesByPlace[i];
					if(category.id == service.category && this.trueIfPlacesNotFiltered(service, this.filters)){
						services.push(service);
					}
				}
			}
			return services;
		}

		BackendManager.prototype.numberOfServices = function(category){
			return this.getServicesByCategory(category).length;
		}

		BackendManager.prototype.getServicesByPlace = function(){
			var services = this.getNotOldServices();
			var returnServices = [];
			for(var i = 0; i < services.length; i++){
				var service = services[i];
				if(service.places.length > 0){
					returnServices.push(service);
				}
			}
			return returnServices;
		}

		BackendManager.prototype.getServicesByNoPlace = function(){
			var services = this.getNotOldServices();
			var returnServices = [];
			for(var i = 0; i < services.length; i++){
				var service = services[i];
				if(service.places.length == 0){
					returnServices.push(service);
				}
			}
			return returnServices;
		}

		BackendManager.prototype.getStateById = function(id){
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
		BackendManager.prototype.getWaitingSubState = function(booking){
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

		BackendManager.prototype.getBookingId = function(booking){
			if(booking == null) return 0;
			if(booking.idCreation > -1) return booking.idCreation;
			if(booking.idBooking != null) return booking.idBooking;
			return booking.id;
		}

		BackendManager.prototype.getWaitingName = function(booking){
			if(booking==null || booking.status != 'waiting') return '';
			var subState = this.getWaitingSubState(booking);
			var state = this.getStateById('waiting'); //filterName
			var name = state.filterName;
			if(subState == 0) name = 'Non confirmée';
			else if(subState == 1) name = 'Attente paiement';
			else if(subState == 2) name = 'Paiement expiré';
			return name;
		}

		BackendManager.prototype.getQuotationName = function(booking){
			if(booking==null || !booking.quotation) return '';
			var name = '';
			if(booking.quotationRequest) name += 'Demande de devis';
			else {
				name += 'Devis en attente';
				if(this.settings != null && booking.numberSentEmailQuotation >= this.settings.no_response_quotation_times){
					name += '(expiré)';
				}
			}
			return name;
		}

		BackendManager.prototype.noHaveCategory = function(service){
			return service.category == null || service.category.length == 0;
		}

		BackendManager.prototype.getCategories = function(){
			if(this.settings==null) return [];
			if(this.settings.form_category_services == null || this.settings.form_category_services=='' || this.settings.form_category_services==false){
				return [];
			}
			return this.settings.form_category_services;
		}

		BackendManager.prototype.getAlerts = function(appointment){
			var alertsArray = [];
			for(var i = 0; i < this.alerts.length; i++){
				var alert = this.alerts[i];
				if(((alert.idType == 0 || alert.idType == 3) &&
					appointment.idService == alert.idService &&
					appointment.startDate == alert.startDate &&
					appointment.endDate == alert.endDate) ||
					(alert.idType == 1 && appointment.idBooking == alert.idBooking) ||
					(alert.idType == 2 && appointment.id == alert.idAppointment)){
						alertsArray.push(alert);
				}
			}
			return alertsArray;
		}

		BackendManager.prototype.getGroupsByAppointment = function(appointment){
			var groups = [];
			for(var i = 0; i < this.groups.length; i++){
				var group = this.groups[i];
				var service = this.getLastVersionOnServiceById(appointment.idService);
				if(appointment.idPlace == group.idPlace &&
					service.id == group.idService &&
					appointment.startDate == group.startDate &&
					appointment.endDate == group.endDate){
						groups.push(group);
				}
			}
			return groups;
		}

		BackendManager.prototype.round = function(value){
			return Math.round(value * 100) / 100;
		}

		BackendManager.prototype.isAvailable = function(member, startDate, endDate){
			var date = this.clearTime(new Date(this.parseDate(startDate)));
			for(var i = 0; i < member.memberAvailabilities.length; i++) {
				var memberAvailability = member.memberAvailabilities[i];
				var dates = memberAvailability.dates.split(',');
				var stringDate = $filter('formatDateTime')(date, 'yyyy-MM-dd');
				var available = (dates.indexOf(stringDate) > -1);
				if(available) {
					return true;
				}
			}
			return false;
		}

		BackendManager.prototype.isMemberIsAvailable = function(member, date){
			if(!this.isAvailable(member, date, date)) return false;
			for(var i = 0; i < this.memberConstraints.length; i++){
				var memberConstraint = this.memberConstraints[i];
				var startDate = new Date(this.parseDate(memberConstraint.startDate));
				var endDate = new Date(this.parseDate(memberConstraint.endDate));
				date = new Date(this.parseDate(date));
				date.setHours(12);
				if(memberConstraint.idMember == member.id && startDate <= date && date <= endDate){
					return false;
				}
			}
			return true;
		}

		BackendManager.prototype.getTagInCalendarFilters = function(idTag){
			for(var j = 0; j < this.filters.tags.length; j++){
				var tag = this.filters.tags[j];
				if(tag.id == idTag){
					return tag;
				}
			}
			return null;
		}

		BackendManager.prototype.addManyAppointentsInCalendar = function(appointments){
			for(var i = 0; i < appointments.length; i++) {
				var appointment = appointments[i];
				var booking = this.getBookingById(appointment.idBooking);
				booking = this.applyFilters(booking);
				if(booking != null && this.haveAppointmentInBooking(booking, appointment.id)){
					this.addAppointmentInCalendar(booking, appointment);
				}
			}
		}

		BackendManager.prototype.addAppointmentInCalendar = function(booking, appointment){
			var service = this.getServiceById(appointment.idService);
			var color = '#ffff00';
			if(service != null){
				color = service.color;
			}
			if(appointment.customer == null){
				appointment.customer = {lastName:'Unknown', firstName:'Unkown'};
			}
			//need calendarView bug else when change calendar display
			var serviceName = 'Unknown service';
			if(service){
				serviceName = this.getTextByLocale(service.name, this.locale);
				if(appointment.idPlace != null){
					var place = this.getPlaceById(appointment.idPlace);
					if(place != null){
						serviceName = '['+ this.getTextByLocale(place.name, this.locale)+'] '+serviceName;
					}
				}
			}

			var diffHours = ($filter('parseDate')(appointment.endDate)).getHours() - ($filter('parseDate')(appointment.startDate)).getHours();
			diffHours = Math.min(4, diffHours);
			diffHours = Math.max(1, diffHours);

			var selectedClass = '';
			var presentation = '<div id="appointment'+appointment.id+'_'+this.calendarView+'" class="creneau '+selectedClass+' t'+diffHours+'"> <div class="creneau_content" style="{background-color:'+color+'}">';
			if(appointment.state == 'ok') presentation += '<div class="creneau_state valid" title="Confirmé"></div>';
			if(appointment.state == 'waiting') presentation += '<div class="creneau_state pending" title="Non confirmé"></div>';
			if(appointment.state == 'cancelled') presentation += '<div class="creneau_state cancelled" title="Annulé"></div>';
			if(appointment.state == 'abandonned') presentation += '<div class="creneau_state cancelled" title="Abandonné"></div>';


			presentation += '<div class=""> '+ (booking.quotation?'Devis':'Réservation')  + ' N°' + this.getBookingId(booking)+'</div>';
			if(!appointment.noEnd){
				presentation += '<div class="creneau_time">'+$filter('formatDateTime')(appointment.startDate, this.timeformat)+' - '+$filter('formatDateTime')(appointment.endDate, this.timeformat)+'</div>';
			}
			else {
				presentation += '<div class="creneau_time">'+this.words['begin_word']+' <span class="resa_cal_creneau_debut">'+$filter('formatDateTime')(appointment.startDate, this.timeformat)+'</div>';
			}
			presentation += '<div class="creneau_service">'+serviceName+'</div>';
			presentation += '<div class="creneau_client">';
			if(!this.settings['staffIsConnected'] || (this.settings['staffIsConnected'] && this.settings['staff_display_customer'])){
				presentation += '<div class="client_name">';
				if(appointment.customer.company!=null && appointment.customer.company.length > 0){
					presentation += '['+appointment.customer.company+'] ';
				}
				presentation += appointment.customer.lastName + ' ' +appointment.customer.firstName+'</div>';
			}
			if(!this.settings['staffIsConnected'] || (this.settings['staffIsConnected'] && this.settings['staff_display_numbers'])){
				if(appointment.numbers > 1) presentation += '<div class="client_number">'+appointment.numbers+' '+this.words['persons_word']+'</div>';
				if(appointment.numbers == 1) presentation += '<div class="client_number">'+appointment.numbers+' '+this.words['person_word']+'</div>';
			}
			presentation += '</div>';
			if(!this.settings['staffIsConnected'] || (this.settings['staffIsConnected'] && this.settings['staff_display_total'])){
				presentation += '<div class="creneau_resa_price">'+this.words['Total_word']+' : '+ booking.totalPrice + this.settings.currency + '</div>';
			}
			var displayMembersSentence = '';
			for(var j = 0; j < appointment.appointmentMembers.length; j++){
				var member = this.getMemberById(appointment.appointmentMembers[j].idMember);
				if(appointment.appointmentMembers[j].number == 0){
					displayMembersSentence += '<div class="creneau_member">' + member.nickname + '</div>';
				}
				else if(member != null){
					if(displayMembersSentence != '') displayMembersSentence += ', ';
					displayMembersSentence += member.nickname;
				}
			}
			if(displayMembersSentence != ''){
				presentation += '<div class="creneau_members">'+displayMembersSentence+'</div>';
			}
			if(this.settings['staffIsConnected'] && booking.staffNote.length > 0) presentation += '<div class="creneau_note">'+$filter('htmlSpecialDecode')(booking.staffNote)+'</div>';
			else if(booking.note.length > 0) presentation += '<div class="creneau_note">'+$filter('htmlSpecialDecode')(booking.note)+'</div>';

			var displayAlertsSentence = '';
			var alerts = this.getAlerts(appointment, this.alerts);
			for(var indexAlert = 0; indexAlert < alerts.length; indexAlert++){
				var alert = alerts[indexAlert];
				displayAlertsSentence += '<div class="creneau_alert">'+alert.name+'</div>';
			}
			if(displayAlertsSentence != ''){
				presentation += '<div class="creneau_alerts">'+displayAlertsSentence+'</div>';
			}
			presentation += '</div></div>';
			var actions = [];
			if(!this.settings['staffIsConnected'] && !this.isViewMonth()){
				actions = [{ // an array of actions that will be displayed next to the event title
					label: '<i class=\'glyphicon glyphicon-pencil\'></i>', // the label of the action
					cssClass: 'edit-action', // a CSS class that will be added to the action element so you can implement custom styling
					onClick: function(args) { // the action that occurs when it is clicked. The first argument will be an object containing the parent event
						var appointment = _self.getAppointmentByPresentation(_self.appointments, args.calendarEvent.title);
						_self.openDialogAddAppointmentsForAppointment(appointment);
					}
				}];
			}

			appointment.presentation  = presentation;
			var startDate = _self.parseDate(appointment.startDate);
			var endDate = _self.parseDate(appointment.endDate);
			if(startDate.getTime() > endDate.getTime()){
				endDate = new Date(startDate);
			}
			if(startDate.getTime() == endDate.getTime()){
				endDate.setMinutes(endDate.getMinutes() + 30);
			}
			var dragAndDrop = (!this.settings['staffIsConnected'] && !this.isViewMonth()) && this.settings['calendar']['drag_and_drop_activated'];
			this.events.push(this.createNewEvent(
					appointment.presentation,
					serviceName,
					startDate,
					endDate,
					color,
					(alerts.length > 0),
					actions,
					dragAndDrop));
		}

		BackendManager.prototype.getInfoCalendarPlaces = function(infoCalendar){
			var places = [];
			if(infoCalendar.idPlaces.length > 0){
				var placesAux = infoCalendar.idPlaces.split(',');
				for(var index = 0; index < placesAux.length; index++){
					var place = placesAux[index];
					if(place!=null && place.length > 0) places.push(place);
				}
			}
			return places;
		}

		BackendManager.prototype.getInfoCalendarNote = function(infoCalendar){
			var placeInfoNote = '';
			if(infoCalendar.idPlaces.length > 0){
				placeInfoNote = '[' + this.getListPlaces(this.getInfoCalendarPlaces(infoCalendar)) + ']';
			}
			return placeInfoNote + this.htmlSpecialDecode(infoCalendar.note, true);
		}

		BackendManager.prototype.reloadCalendar = function(){
			this.appointments = [];
			for(var i = 0; i < this.filteredBookings.length; i++){
				var booking = this.filteredBookings[i];
				this.appointments = this.appointments.concat(booking.appointments);
			}
			this.setEvents([]);
			this.addManyAppointentsInCalendar(this.appointments);
			for(var i = 0; i < this.infoCalendars.length; i++){
				var infoCalendar = this.infoCalendars[i];
				if(infoCalendar.idPlaces.length == 0 || this.trueIfPlacesNotFilteredPlaces(this.getInfoCalendarPlaces(infoCalendar), this.filters)){
					var color = this.settings.calendar.info_calendar_color;
					var actions = [];
					if(!this.settings['staffIsConnected']){
						actions = [{
							label: '<i class=\'glyphicon glyphicon-pencil\'></i>',
							cssClass: 'edit-action',
							onClick: function(args) {
								var infoCalendar = _self.getInfoByNote(_self.infoCalendars, args.calendarEvent.title);
								_self.openEditInfoCalendarDialog(infoCalendar);
							}
						},
						{
							label: '<i class=\'glyphicon glyphicon-remove\'></i>',
							cssClass: 'edit-action',
							onClick: function(args) {
								_self.deleteInfoCalendarAction(infoCalendar, {
									title: _self.words['delete_info_calendar_title_dialog'],
									text: _self.words['delete_info_calendar_text_dialog'],
									confirmButton: _self.words['delete_info_calendar_confirmButton_dialog'],
									cancelButton: _self.words['delete_info_calendar_cancelButton_dialog']
								});
							}
						}];
					}

					var dateStart = _self.parseDate(infoCalendar.date);
					var dateEnd = _self.parseDate(infoCalendar.dateEnd);
					if(dateEnd.getTime() <= dateStart.getTime()){
						dateEnd.setTime(dateStart.getTime() + 3600 * 1000);
					}
					var infoCalendarNote = this.getInfoCalendarNote(infoCalendar);
					var security = 500; //TO DELETE
					do {
						var currentStartDate = _self.setTimeWithStringTime(new Date(dateStart), infoCalendar.startTime);
						var currentEndDate = _self.setTimeWithStringTime(new Date(dateStart), infoCalendar.endTime);
						if(infoCalendar.endTime == ''){
							currentEndDate = new Date(dateEnd);
						}
						var dragAndDrop = (!this.settings['staffIsConnected'] && this.isSameDay(currentEndDate, dateEnd)) && this.settings['calendar']['drag_and_drop_activated'];
						this.events.push(this.createNewEvent(
							_self.formatNoteInfoCalendar(infoCalendar.id, infoCalendarNote),
							'info'+infoCalendar.id,
							currentStartDate,
							currentEndDate,
							color,
							false,
							actions,
							dragAndDrop));
						dateStart.setDate(dateStart.getDate() + 1);
						security--;
					}
					while(!this.isSameDay(currentEndDate, dateEnd) && security > 0);
				}
			}
			for(var i = 0; i < this.serviceConstraints.length; i++){
				var serviceConstraint = this.serviceConstraints[i];
				var color = this.settings.calendar.service_constraint_color;
				var actions = [];
				if(!this.settings['staffIsConnected']){
					actions = [{
						label: '<i class=\'glyphicon glyphicon-pencil\'></i>',
						cssClass: 'edit-action',
						onClick: function(args) {
							var serviceConstraint = _self.getServiceConstraint(_self.serviceConstraints, args.calendarEvent.title);
							_self.openEditServiceConstraintDialog(serviceConstraint);
						}
					},
					{
						label: '<i class=\'glyphicon glyphicon-remove\'></i>',
						cssClass: 'edit-action',
						onClick: function(args) {
							var serviceConstraint = _self.getServiceConstraint(_self.serviceConstraints, args.calendarEvent.title);
							_self.deleteServiceConstraintAction(serviceConstraint, {
								title: _self.words['delete_service_constraint_title_dialog'],
								text: _self.words['delete_service_constraint_text_dialog'],
								confirmButton: _self.words['delete_service_constraint_confirmButton_dialog'],
								cancelButton: _self.words['delete_service_constraint_cancelButton_dialog']
							});
						}
					}];
				}
				var service = _self.getServiceById(serviceConstraint.idService);
				var name = 'Unknown service';
				if(service != null) name = this.getTextByLocale(service.name, this.locale);
				serviceConstraint.title =  '<p id="serviceConstraint'+serviceConstraint.id+'" class="resa_cal_nom_service"> Contrainte sur ' + name + '</p>';
				var dragAndDrop = (!this.settings['staffIsConnected']) && this.settings['calendar']['drag_and_drop_activated'];
				this.events.push(this.createNewEvent(
						serviceConstraint.title,
						'constraint' + serviceConstraint.id,
						serviceConstraint.startDate,
						serviceConstraint.endDate,
						color,
						false,
						actions,
						dragAndDrop));
			}
			for(var i = 0; i < this.memberConstraints.length; i++){
				var memberConstraint = this.memberConstraints[i];
				var color = this.settings.calendar.service_constraint_color;
				var actions = [];
				if(!this.settings['staffIsConnected']){
					actions = [{
						label: '<i class=\'glyphicon glyphicon-pencil\'></i>',
						cssClass: 'edit-action',
						onClick: function(args) {
							var memberConstraint = _self.getMemberConstraint(_self.memberConstraints, args.calendarEvent.title);
							_self.openEditServiceConstraintDialog(null, memberConstraint);
						}
					},
					{
						label: '<i class=\'glyphicon glyphicon-remove\'></i>',
						cssClass: 'edit-action',
						onClick: function(args) {
							var memberConstraint = _self.getMemberConstraint(_self.memberConstraints, args.calendarEvent.title);
							_self.deleteMemberConstraintAction(memberConstraint, {
								title: _self.words['delete_service_constraint_title_dialog'],
								text: _self.words['delete_service_constraint_text_dialog'],
								confirmButton: _self.words['delete_service_constraint_confirmButton_dialog'],
								cancelButton: _self.words['delete_service_constraint_cancelButton_dialog']
							});
						}
					}];
				}
				var member = _self.getMemberById(memberConstraint.idMember);
				var name = 'Unknown member';
				if(member != null) name = member.nickname;
				memberConstraint.title =  '<p id="memberConstraint'+memberConstraint.id+'" class="resa_cal_nom_service"> Contrainte sur ' + name + '</p>';
				var dragAndDrop = (!this.settings['staffIsConnected']) && this.settings['calendar']['drag_and_drop_activated'];
				this.events.push(this.createNewEvent(
						memberConstraint.title,
						'memberConstraint' + memberConstraint.id,
						memberConstraint.startDate,
						memberConstraint.endDate,
						color,
						false,
						actions,
						dragAndDrop));
			}
		}

		BackendManager.prototype.regenerateFilteredBookingsByName = function(){
			this.generateFilteredBookings();
		}

		BackendManager.prototype.generateFilteredBookings = function(){
			this.filteredBookings = [];
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.applyFilters(this.bookings[i]);
				if(booking != null){
					this.filteredBookings.push(booking);
				}
			}
			this.changeOrderByBookings();
			this.applyPaginationBookings();
			//this.generateFilteredRapportsByServices();
			this.generateFilteredRapportsByServicesPlanning();
			this.reloadCalendar();
			if(this.planningServiceCtrl != null){
				this.planningServiceCtrl.render();
			}
		}

		BackendManager.prototype.trueIfPlacesNotFiltered = function(service, filters){
			if(service.places.length <= 0 || filters == null || filters.places == null) return true;
			var ok = false;
			for(var i = 0; i < service.places.length; i++){
				if(filters.places[service.places[i]] != null){
					ok = ok || filters.places[service.places[i]];
				}
			}
			return ok;
		}

		BackendManager.prototype.trueIfPlacesNotFilteredPlaces = function(places, filters){
			if(places.length <= 0 || filters == null || filters.places == null) return true;
			var ok = false;
			for(var i = 0; i < places.length; i++){
				if(filters.places[places[i]] != null){
					ok = ok || filters.places[places[i]];
				}
			}
			return ok;
		}

		BackendManager.prototype.applyFilters = function(booking){
			booking = JSON.parse(JSON.stringify(booking));
			if(booking == null) return false;
			var ok = true;
			//Filters booking
			if(booking.quotation){
				ok = this.filters.states['quotation'];
			}
			if(ok){
				var status = this.calculateBookingPayment(booking);
				if(status == 'paiement_none'){
					ok = this.filters.statePaymentsList['none'];
				}
				else if(status == 'paiement_incomplete'){
					ok = this.filters.statePaymentsList['advancePayment'] || this.filters.statePaymentsList['deposit'];
				}
				else if(status == 'paiement_done'){
					ok = this.filters.statePaymentsList['completed'];
				}
				else if(status == 'paiement_overpaiement'){
					ok = this.filters.statePaymentsList['over'];
				}
				else if(status == 'paiement_remboursement'){
					ok = this.filters.statePaymentsList['repayment_incompleted'];
				}
				else if(status == 'paiement_remboursement_done'){
					ok = this.filters.statePaymentsList['repayment_completed'];
				}
			}
			if(ok && this.filters.search.length >= 3){
				var customer = booking.customer;
				if(customer != null){
					ok = ok && ((customer.displayName!=null && customer.displayName.toUpperCase().indexOf(this.filters.search.toUpperCase())!=-1) ||
						(customer.firstName!=null && customer.firstName.toUpperCase().indexOf(this.filters.search.toUpperCase())!=-1) ||
						(customer.lastName!=null && customer.lastName.toUpperCase().indexOf(this.filters.search.toUpperCase())!=-1) ||
						(customer.company!=null && customer.company.toUpperCase().indexOf(this.filters.search.toUpperCase())!=-1) ||
						(customer.phone!=null && customer.phone.toUpperCase().indexOf(this.filters.search.toUpperCase())!=-1) ||
						(customer.email!=null && customer.email.toUpperCase().indexOf(this.filters.search.toUpperCase())!=-1));
				}
				else ok = false;
			}
			else if(ok && this.filters.search.length > 0 && !isNaN(Number(this.filters.search))){
				ok = (this.getBookingId(booking) == this.filters.search * 1);
			}
			if(ok){
				var newAppointments = [];
				for(var i = 0; i < booking.appointments.length; i++){
					var appointment = booking.appointments[i];
					if(this.applyFiltersAppointment(appointment, booking)){
						newAppointments.push(appointment);
					}
				}
				booking.appointments = newAppointments;
				if(booking.appointments.length == 0) return null;
				return booking;
			}
			return null;
		}

		BackendManager.prototype.applyFiltersDate = function(booking){
			if(booking != null){
				booking = JSON.parse(JSON.stringify(booking));
				var newAppointments = [];
				for(var i = 0; i < booking.appointments.length; i++){
					var appointment = booking.appointments[i];
					if(this.applyFiltersAppointmentDate(appointment)){
						newAppointments.push(appointment);
					}
				}
				booking.appointments = newAppointments;
				if(booking.appointments.length == 0) return null;
			}
			return booking;
		}

		BackendManager.prototype.applyFiltersAppointmentDate = function(appointment){
			var date = this.parseDate(appointment.startDate);
			return this.dateAppointmentsToLoad.minDate <= date && date <= this.dateAppointmentsToLoad.maxDate;
		}

		BackendManager.prototype.applyFiltersAppointment = function(appointment, booking){
			var ok = appointment.idPlace == '' || this.filters.places[appointment.idPlace];
			if(ok && !booking.quotation){
				if(appointment.state == 'waiting'){
					var state = this.getWaitingSubState(booking);
					if(state == 0)	ok = ok && this.filters.states[appointment.state];
					else if(state == 1) ok = ok && this.filters.states['waiting_payment'];
					else if(state == 2) ok = ok && this.filters.states['waiting_expired'];
				}
				else ok = ok && this.filters.states[appointment.state];
			}
			if(ok) ok = ok && this.applyFiltersAppointmentDate(appointment);
			if(ok){
				if(appointment.tags.length == 0) ok = this.filters.tags['no_tag'];
				else {
					var okTags = false;
					for(var i = 0; i < appointment.tags.length; i++){
						okTags = okTags || this.filters.tags[appointment.tags[i]];
					}
					ok = ok && okTags;
				}
			}
			if(ok){
				for(var key in this.filters.services){
					var service = this.getServiceById(key);
					if(appointment.idService == service.id){
						ok = this.filters.services[key];
					}
					else {
						var linkOldServicesArray = service.linkOldServices.split(',');
						if(linkOldServicesArray.indexOf(appointment.idService+'') != -1){
							ok = this.filters.services[key];
						}
					}
				}
			}
			if(ok && this.members.length > 0 && (appointment.appointmentMembers.length > 0 || this.isStaffOnlyAppointmentsDisplayed())){
				var okMembers =  false;
				for(var key in this.filters.members){
					var okMember = false;
					if(this.filters.members[key]){
						for(var i = 0; i < appointment.appointmentMembers.length; i++){
							okMember = okMember || (key == appointment.appointmentMembers[i].idMember);
						}
					}
					okMembers = okMembers || okMember;
				}
				ok = ok && okMembers;
			}
			return ok;
		}

		BackendManager.prototype.changeOrderByBookings = function(){
			this.filteredBookings.sort(function(bookingA, bookingB){
				var actualDate = new Date();
				var resultDateA = new Date();
				if(bookingA.appointments.length > 0){
					resultDateA = _self.parseDate(bookingA.appointments[0].startDate);
					if(bookingA.intervals != null && bookingA.intervals.length > 1){
						for(var i = 0; i < bookingA.appointments.length; i++){
							var date = _self.parseDate(bookingA.appointments[i].startDate);
							if((date < resultDateA && date >= actualDate) || (resultDateA < actualDate)){
								resultDateA = date;
							}
						}
					}
				}
				var resultDateB = new Date();
				if(bookingB.appointments != null && bookingB.appointments.length > 0){
					resultDateB = _self.parseDate(bookingB.appointments[0].startDate);
					if(bookingB.intervals != null && bookingB.intervals.length > 1){
						for(var i = 0; i < bookingB.appointments.length; i++){
							var date = _self.parseDate(bookingB.appointments[i].startDate);
							if((date < resultDateB && date >= actualDate) || (resultDateB < actualDate)){
								resultDateB = date;
							}
						}
					}
				}
				return _self.parseDate(resultDateA).getTime() - _self.parseDate(resultDateB).getTime();
			});
		}

		BackendManager.prototype.applyPaginationBookings = function(){
			this.displayedBookings = this.filteredBookings.slice((this.paginationBookings.number - 1) * this.paginationBookings.step, this.paginationBookings.step * this.paginationBookings.number);
		}

		BackendManager.prototype.getNumberBookings = function(){
			var number = 0;
			for(var i = 0; i < this.filteredBookings.length; i++){
				var booking = this.filteredBookings[i];
				if(booking.status == 'ok' && !booking.quotation){
					number++;
				}
			}
			return number;
		}

		BackendManager.prototype.getNumberAppointments = function(){
			var number = 0;
			for(var i = 0; i < this.filteredBookings.length; i++){
				var booking = this.filteredBookings[i];
				if(!booking.quotation){
					for(var j = 0; j < booking.appointments.length; j++){
						var appointment = booking.appointments[j];
						if(appointment.state == 'ok'){
							number++;
						}
					}
				}
			}
			return number;
		}

		BackendManager.prototype.getNumberPersons = function(){
			var number = 0;
			for(var i = 0; i < this.filteredBookings.length; i++){
				var booking = this.filteredBookings[i];
				if(!booking.quotation){
					for(var j = 0; j < booking.appointments.length; j++){
						var appointment = booking.appointments[j];
						if(appointment.state == 'ok'){
							number += appointment.numbers;
						}
					}
				}
			}
			return number;
		}


		BackendManager.prototype.searchCustomersAction = function(){
			this.searchCustomers(this.searchCustomer, this.paginationCustomers.number, this.paginationCustomers.step);
		}


		/**
		 * return the rapport by services in function of appointment
		 */
		BackendManager.prototype.getRapportByServices = function(appointment){
			for(var i = 0; i < this.rapportsByServices.length; i++){
				var rapportByServices = this.rapportsByServices[i];
				var service = this.getLastVersionOnServiceById(appointment.idService);
				if(rapportByServices.idPlace == appointment.idPlace &&
					service != null && rapportByServices.service != null && rapportByServices.service.id == service.id &&
					rapportByServices.startDate.getTime() == ($filter('parseDate')(appointment.startDate)).getTime() &&
					rapportByServices.endDate.getTime() == ($filter('parseDate')(appointment.endDate)).getTime()){
					return rapportByServices;
				}
			}
			return null;
		}

		/**
		 * return all rapports by services in function of id service
		 */
		BackendManager.prototype.getAllFilteredRapportsByServicesWithIdService = function(idService, date){
			var rapportsByServices = [];
			for(var i = 0; i < this.filteredRapportsByServicesPlanning.length; i++){
				var rapportByServices = this.filteredRapportsByServicesPlanning[i];
				var service = this.getLastVersionOnServiceById(idService);
				if(rapportByServices.service.id == service.id &&
					rapportByServices.startDate.getFullYear() == date.getFullYear() &&
					rapportByServices.startDate.getMonth() == date.getMonth() &&
					rapportByServices.startDate.getDate() == date.getDate()){
					rapportsByServices.push(rapportByServices)
				}
			}
			return rapportsByServices;
		}

		BackendManager.prototype.getRapportByServicesWithFiltered = function(oneFilteredRapportByServices){
			return this.getRapportByServices(oneFilteredRapportByServices.appointments[0]);
		}

		/**
		 * replace and generate rapport by service with one booking
		 */
		BackendManager.prototype.replaceAndGenerateRapportByServices = function(booking){
			this.generateRapportByServices(booking);
			//this.generateFilteredRapportsByServices();
			this.generateFilteredRapportsByServicesPlanning();
			if(this.planningServiceCtrl != null){
				this.planningServiceCtrl.render();
			}
		}

		/**
		 * generate rapport by service with one booking
		 */
		BackendManager.prototype.generateRapportByServices = function(booking){
			if(booking.status != 'cancelled' && booking.status != 'abandonned'){
				for(var j = 0; j < booking.appointments.length; j++){
					var appointment = booking.appointments[j];
					var rapportByServices = this.getRapportByServices(appointment);
					var state = this.getStateById(booking.status);
					if(rapportByServices == null){
						rapportByServices = {
							id:this.rapportsByServices.length,
							idPlace:appointment.idPlace,
							service:this.getLastVersionOnServiceById(appointment.idService),
							startDate:new Date($filter('parseDate')(appointment.startDate)),
							endDate:new Date($filter('parseDate')(appointment.endDate)),
							noEnd:appointment.noEnd,
							appointments:[],
							idMembers:[],
							numberPersons:0,
							groups:this.getGroupsByAppointment(appointment)
						};
						this.rapportsByServices.push(rapportByServices);
					}
					appointment.idCreation = booking.idCreation;
					appointment.quotation = booking.quotation;
					appointment.bookingTotalPrice = booking.totalPrice;
					appointment.bookingNeedToPay = booking.needToPay;
					rapportByServices = this.pushAppointmentInRapport(rapportByServices, appointment);
				}
			}
		}

		/**
		 * generate all reports by services
		 */
		BackendManager.prototype.generateRapportsByServices = function(){
			this.rapportsByServices = [];
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				this.generateRapportByServices(booking);
			}
			//this.generateFilteredRapportsByServices();
			this.generateFilteredRapportsByServicesPlanning();
			if(this.planningServiceCtrl != null){
				this.planningServiceCtrl.render();
			}
		}

		BackendManager.prototype.pushAppointmentInRapport = function(rapportByServices, appointment){
			var found = false;
			for(var i = 0; i < rapportByServices.appointments.length; i++){
				var localAppointment = rapportByServices.appointments[i];
				found = found || localAppointment.id == appointment.id;
			}
			if(!found){
				rapportByServices.appointments.push(appointment);
				for(var k = 0; k < appointment.appointmentMembers.length; k++){
					var appointmentMember = appointment.appointmentMembers[k];
					if(rapportByServices.idMembers.indexOf(appointmentMember.idMember) == -1){
						rapportByServices.idMembers.push(appointmentMember.idMember);
					}
				}
				rapportByServices.numberPersons += appointment.numbers;
			}
			return rapportByServices;
		}

		/*
		BackendManager.prototype.generateFilteredRapportsByServices = function(){
			this.filteredRapportsByServices = [];
			for(var i = 0; i < this.rapportsByServices.length; i++){
				var rapportByServices = JSON.parse(JSON.stringify(this.rapportsByServices[i]));
				rapportByServices.startDate = this.parseDate(rapportByServices.startDate);
				rapportByServices.endDate = this.parseDate(rapportByServices.endDate);
				rapportByServices.appointments = [];
				rapportByServices.idMembers = [];
				rapportByServices.numberPersons = 0;
				for(var j = 0; j < this.rapportsByServices[i].appointments.length; j++){
					var appointment = this.rapportsByServices[i].appointments[j];
					var booking = this.getBookingById(appointment.idBooking);
					booking = this.applyFilters(booking);
					if(booking != null && this.haveAppointmentInBooking(booking, appointment.id)){
						rapportByServices = this.pushAppointmentInRapport(rapportByServices, appointment);
					}
				}
				if(rapportByServices.appointments.length > 0){
					this.filteredRapportsByServices.push(rapportByServices);
				}
			}
			this.filteredRapportsByServices = $filter('orderBy')(this.filteredRapportsByServices, 'startDate', false);
			this.applyPaginationRapportsByServices();
		}
		*/

		BackendManager.prototype.getNumberHoursCalendar = function(){
			var startDate = this.clearTime(new Date(this.datePlanningMembers));
			startDate.setHours(this.settings.calendar.start_time);
			var endDate = this.clearTime(new Date(this.datePlanningMembers));
			endDate.setHours(this.settings.calendar.end_time);
			var seconds = Math.floor((endDate.getTime() - startDate.getTime()) / 1000);
			return seconds/3600;
		}

		BackendManager.prototype.getNumberOfPourcent = function(){
			var timeslotBlock = ((this.getNumberHoursCalendar()) * 2)+ 1;
			return 100 / timeslotBlock;
		};

		BackendManager.prototype.getNumberOfPourcentFloor = function(){
			return this.getNumberOfPourcent();
		};

		BackendManager.prototype.getAppointmentWidth = function(appointment){
			var startDate = new Date(this.parseDate(appointment.startDate));
			if(this.clearTime(startDate) != this.clearTime(this.datePlanningMembers)) {
				startDate = new Date(this.parseDate(appointment.startDate));
				var localStartDate = this.clearTime(new Date(this.datePlanningMembers));
				localStartDate.setHours(startDate.getHours());
				localStartDate.setMinutes(startDate.getMinutes());
				startDate = new Date(localStartDate);
			}
			var seconds = Math.floor((this.parseDate(appointment.endDate).getTime() - startDate.getTime()) / 1000);
			return (this.getNumberOfPourcent() * seconds/3600 * 2);
		}

		BackendManager.prototype.getAppointmentLeft = function(appointment){
			var borderDate = this.clearTime(new Date(this.datePlanningMembers));
			borderDate.setHours(this.settings.calendar.start_time);
			var startDateTimeCleared = this.clearTime(new Date(this.parseDate(appointment.startDate)));
			var startDate = new Date(this.parseDate(appointment.startDate));
			if(startDateTimeCleared.getTime() != this.clearTime(this.datePlanningMembers).getTime()) {
				startDate = this.clearTime(new Date(this.datePlanningMembers));
			}
			var seconds = Math.floor((startDate.getTime() - borderDate.getTime()) / 1000);
			return (this.getNumberOfPourcent() * seconds/3600 * 2);
		}


		BackendManager.prototype.generateFilteredRapportsByServicesPlanning = function(){
			this.filteredRapportsByServicesPlanning = [];
			for(var i = 0; i < this.rapportsByServices.length; i++){
				var rapportByServices = JSON.parse(JSON.stringify(this.rapportsByServices[i]));
				rapportByServices.startDate = this.parseDate(rapportByServices.startDate);
				rapportByServices.endDate = this.parseDate(rapportByServices.endDate);
				if(this.filters.dates.startDate <= rapportByServices.startDate &&
					rapportByServices.startDate <= this.filters.dates.endDate &&
					rapportByServices.service != null &&
					this.filters.services[rapportByServices.service.id]){
					rapportByServices.groups = [];
					for(var j = 0; j < this.rapportsByServices[i].groups.length; j++){
						var group = JSON.parse(JSON.stringify(this.rapportsByServices[i].groups[j]));
						if(this.applyFiltersGroupPlanning(group)){
							rapportByServices.groups.push(group);
						}
					}
					if(this.rapportsByServices[i].groups.length == 0 || rapportByServices.groups.length > 0){
						this.filteredRapportsByServicesPlanning.push(rapportByServices);
					}
				}
			}
			this.filteredRapportsByServicesPlanning = $filter('orderBy')(this.filteredRapportsByServicesPlanning, 'startDate', false);
			this.applyPaginationRapportsByServicesPlanning();
			this.generateRapportsServicesByDateWithNotInGroups();
		}

		/**
		 * return true if group is not filtered (is displayed)
		 */
		BackendManager.prototype.applyFiltersGroupPlanning = function(group){
			var ok = group.idPlace == '' || this.filters.places[group.idPlace];
			if(ok) ok = this.filters.services[group.idService];
			if(ok && this.members.length > 0 && group.idMembers.length > 0){
				var ok = false;
				for(var i = 0; i < group.idMembers.length; i++){
					var idMember = group.idMembers[i];
					ok = ok || this.filters.members[idMember];
				}
			}
			return ok;
		}


		/**
		 *
		 *
		BackendManager.prototype.applyPaginationRapportsByServices = function(){
			this.displayedRapportsByServices = this.filteredRapportsByServices.slice((this.paginationRapportsByServices.number - 1) * this.paginationRapportsByServices.step, this.paginationRapportsByServices.step * this.paginationRapportsByServices.number);
		}
		*/

		/**
		 *
		 */
		BackendManager.prototype.applyPaginationRapportsByServicesPlanning = function(){
			this.displayedRapportsByServicesPlanning = this.filteredRapportsByServicesPlanning.slice((this.paginationRapportsByServicesPlanning.number - 1) * this.paginationRapportsByServicesPlanning.step, this.paginationRapportsByServicesPlanning.step * this.paginationRapportsByServicesPlanning.number);
		}

		/**
		 * generation rapport by service
		 */
		BackendManager.prototype.generateRapportsServicesByDateWithNotInGroups = function(){
			this.rapportsByServicesWithParticipantsNotAttribuated = [];
			var date = this.datePlanningMembers;
			if(date != null){
				for(var i = 0; i < this.filteredRapportsByServicesPlanning.length; i++){
					var rapportByServices = this.filteredRapportsByServicesPlanning[i];
					if(this.getNumberParticipantsNotInGroups(rapportByServices) > 0){
						var startDateLocal = new Date(rapportByServices.startDate);
						var endDateLocal = new Date(rapportByServices.endDate);
						if(date.getFullYear() == startDateLocal.getFullYear() && date.getMonth() == startDateLocal.getMonth() && date.getDate() == startDateLocal.getDate()){
							var rapports = this.rapportsByServicesWithParticipantsNotAttribuated.find(function(element){
								if(element.startDate.getTime() == startDateLocal.getTime() && element.endDate.getTime() == endDateLocal.getTime()){
									return element;
								}
							}.bind(this));
							if(rapports == null){
								rapports = {startDate:startDateLocal, endDate:endDateLocal, rapportsByServices:[]};
								this.rapportsByServicesWithParticipantsNotAttribuated.push(rapports);
							}
							rapports.rapportsByServices.push(JSON.parse(JSON.stringify(rapportByServices)));
						}
					}
				}
			}
		}

		/**
		 * get rapport services by date
		 */
		BackendManager.prototype.getRapportsServicesByDate = function(date){
			var rapportsByServices = [];
			if(date != null){
				for(var i = 0; i < this.rapportsByServices.length; i++){
					var rapportByServices = this.rapportsByServices[i];
					var startDateLocal = new Date(rapportByServices.startDate);
					var endDateLocal = new Date(rapportByServices.endDate);
					if(date.getFullYear() == startDateLocal.getFullYear() && date.getMonth() == startDateLocal.getMonth() && date.getDate() == startDateLocal.getDate()){
						rapportsByServices.push(rapportByServices);
					}
				}
			}
			return rapportsByServices;
		}

		BackendManager.prototype.getAllPersonsAttribuatedByDate = function(date){
			if(date != null){
				for(var i = 0; i < this.rapportsByServices.length; i++){
					var rapportByServices = this.rapportsByServices[i];
					var startDateLocal = new Date(rapportByServices.startDate);
					var endDateLocal = new Date(rapportByServices.endDate);
					if(date.getFullYear() == startDateLocal.getFullYear() && date.getMonth() == startDateLocal.getMonth() && date.getDate() == startDateLocal.getDate() && this.getNumberParticipantsNotInGroups(rapportByServices) > 0){
						return false;
					}
				}
			}
			return true;
		}



		/**
		 * get groups to idMember by date
		 */
		BackendManager.prototype.getGroupsByIdMembersByDate = function(idMember, startDate, endDate){
			var groups = [];
			for(var i = 0; i < this.rapportsByServices.length; i++){
				var rapportByServices = this.rapportsByServices[i];
				var startDateLocal = new Date(rapportByServices.startDate);
				var endDateLocal = new Date(rapportByServices.endDate);
				if((startDateLocal.getTime() <= startDate.getTime() && startDate.getTime() < endDateLocal.getTime()) ||
					(startDate.getTime() <= startDateLocal.getTime() && startDateLocal.getTime() < endDate.getTime())){
					for(var j = 0; j < rapportByServices.groups.length; j++){
						var group = rapportByServices.groups[j];
						if(group.idMembers.indexOf(idMember + '') > -1 || group.idMembers.indexOf(idMember) > -1 ){
							groups.push(group);
						}
					}
				}
			}
			return groups;
		}

		/**
		 * get member constraints
		 */
		BackendManager.prototype.getMemberConstraintsByIdMember = function(idMember){
			var memberConstraints = [];
			if(this.datePlanningMembers != null){
				for(var i = 0; i < this.memberConstraints.length; i++){
					var memberConstraint = this.memberConstraints[i];
					var datePlanningMembers = this.clearTime(this.datePlanningMembers);
					var startDate = this.clearTime(new Date(memberConstraint.startDate));
					var endDate = this.clearTime(new Date(memberConstraint.endDate));
					if(startDate <= datePlanningMembers && datePlanningMembers <= endDate && memberConstraint.idMember == idMember){
						memberConstraints.push(memberConstraint);
					}
				}
			}
			return memberConstraints;
		}

		/**
		 * get groups to display in planning
		 */
		BackendManager.prototype.getGroupsByIdMembers = function(idMember){
			var groups = [];
			if(this.datePlanningMembers != null){
				for(var i = 0; i < this.filteredRapportsByServicesPlanning.length; i++){
					var rapportByServices = this.filteredRapportsByServicesPlanning[i];
					var startDate = new Date(rapportByServices.startDate);
					if(this.clearTime(startDate).getTime() == this.clearTime(this.datePlanningMembers).getTime()){
						for(var j = 0; j < rapportByServices.groups.length; j++){
							var group = rapportByServices.groups[j];
							if(group.idMembers.indexOf(idMember + '') > -1 || group.idMembers.indexOf(idMember) > -1 ){
								groups.push(group)
							}
						}
					}
				}
			}
			return groups;
		}

		BackendManager.prototype.getGroupsWithNoMember = function(){
			var groups = [];
			if(this.datePlanningMembers != null){
				for(var i = 0; i < this.filteredRapportsByServicesPlanning.length; i++){
					var rapportByServices = this.filteredRapportsByServicesPlanning[i];
					var startDate = new Date(rapportByServices.startDate);
					if(this.clearTime(startDate).getTime() == this.clearTime(this.datePlanningMembers).getTime()){
						for(var j = 0; j < rapportByServices.groups.length; j++){
							var group = rapportByServices.groups[j];
							if(group.idMembers.length == 0){
								groups.push(group)
							}
						}
					}
				}
			}
			return groups;
		}

		BackendManager.prototype.getBookingCustomerWithGroup = function(group){
			var customer = null;
			if(group.oneByBooking && group.idParticipants.length > 0){
				var uri = group.idParticipants[0];
				var idBooking = -1;
				if(this.datePlanningMembers != null){
					var rapportsByServices = this.getRapportsServicesByDate(this.datePlanningMembers);
					for(var k = 0; k < this.rapportsByServices.length; k++){
						var rapportByServices = this.rapportsByServices[k];
						for(var i = 0; i < rapportByServices.appointments.length; i++){
							var appointment = rapportByServices.appointments[i];
							for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
								var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
								for(var index = 0; index < appointmentNumberPrice.participants.length; index++){
									var participant = appointmentNumberPrice.participants[index];
									if(participant.uri == uri){
										idBooking = appointment.idBooking;
										break;
									}
								}
							}
						}
					}
					if(idBooking > -1){
						var booking = this.getBookingById(idBooking);
						if(booking != null){
							customer = booking.customer;
						}
					}
				}
			}
			return customer;
		}

		BackendManager.prototype.getDisplayBookingCustomerWithGroup = function(group){
			var customer = this.getBookingCustomerWithGroup(group)
			var result = '';
			if(customer != null){
				if(customer.company.length > 0) result = '[' + customer.company + '] ';
				else result = '';
				result += customer.lastName + ' ' + customer.firstName + ' - ' + $filter('formatPhone')(customer.phone);
			}
			return result;
		}

		BackendManager.prototype.getRapportByServicesByGroup = function(group){
			for(var i = 0; i < this.filteredRapportsByServicesPlanning.length; i++){
				var rapportByServices = this.filteredRapportsByServicesPlanning[i];
				for(var j = 0; j < rapportByServices.groups.length; j++){
					var groupLocal = rapportByServices.groups[j];
					if(groupLocal.id == group.id){
						return rapportByServices;
					}
				}
			}
			return null;
		}

		BackendManager.prototype.getGroupWidth = function(group){
			var startDate = new Date(this.parseDate(group.startDate));
			if(this.clearTime(startDate) != this.clearTime(this.datePlanningMembers)) {
				startDate = new Date(this.parseDate(group.startDate));
				var localStartDate = this.clearTime(new Date(this.datePlanningMembers));
				localStartDate.setHours(startDate.getHours());
				localStartDate.setMinutes(startDate.getMinutes());
				startDate = new Date(localStartDate);
			}
			if(startDate.getHours() < 9) startDate.setHours(9);
			var seconds = Math.floor((this.parseDate(group.endDate).getTime() - startDate.getTime()) / 1000);
			return (10/3600) * seconds;
		}

		BackendManager.prototype.getGroupLeft = function(group){
			var borderDate = this.clearTime(new Date(this.datePlanningMembers));
			borderDate.setHours(9);
			var startDateTimeCleared = this.clearTime(new Date(this.parseDate(group.startDate)));
			var startDate = new Date(this.parseDate(group.startDate));
			if(startDateTimeCleared.getTime() != this.clearTime(this.datePlanningMembers).getTime()) {
				startDate = this.clearTime(new Date(this.datePlanningMembers));
			}
			if(startDate.getHours() < borderDate.getHours()) startDate.setHours(9);
			var seconds = Math.floor((startDate.getTime() - borderDate.getTime()) / 1000);
			return (10 / 3600) * seconds;
		}

		BackendManager.prototype.getNumberParticipantsInGroup = function(rapportByServices, group){
			var number = 0;
			if(rapportByServices != null){
				for(var i = 0; i < rapportByServices.appointments.length; i++){
	        var appointment = rapportByServices.appointments[i];
	        for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
	          var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
						for(var index = 0; index < appointmentNumberPrice.participants.length; index++){
							var participant = appointmentNumberPrice.participants[index];
							var groupLocal = this.getGroupByIdParticipant(rapportByServices, participant.uri);
							if(groupLocal != null && groupLocal.id == group.id){
								number++;
							}
						}
					}
				}
			}
			return number;
  	}

		BackendManager.prototype.getNumberParticipantsNotInGroups = function(rapportByServices){
			var number = 0;
			if(rapportByServices != null){
				for(var i = 0; i < rapportByServices.appointments.length; i++){
	        var appointment = rapportByServices.appointments[i];
	        for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
	          var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
						for(var index = 0; index < appointmentNumberPrice.participants.length; index++){
							var participant = appointmentNumberPrice.participants[index];
							if(this.getGroupByIdParticipant(rapportByServices, participant.uri) == null){
								number++;
							}
						}
					}
				}
			}
			return number;
  	}

		BackendManager.prototype.getGroupByIdParticipant = function(rapportByServices, idParticipant){
      for(var i = 0; i < rapportByServices.groups.length; i++){
        var group = rapportByServices.groups[i];
        if(group.idParticipants.indexOf(idParticipant) != -1){
          return group;
        }
      }
      return null;
    }

		BackendManager.prototype.getGroupByIdParticipantName = function(rapportByServices, idParticipant){
    	var group  = this.getGroupByIdParticipant(rapportByServices, idParticipant);
			if(group) return this.getGroupName(group);
			return 'Aucun';
    }

		BackendManager.prototype.getGroupName = function(group){
			if(group == null) return '';
			var name = $filter('htmlSpecialDecode')(group.name);
			/*if(group.oneByBooking){
				name += '(#'+group.id+')';
			}*/
			return name;
		}

		BackendManager.prototype.getGroupNameFunctionOfSize = function(group){
			var times = ((new Date(group.endDate)).getTime() - (new Date(group.startDate)).getTime()) / 1000;
			if(times > (3600 + 1800)) return this.getGroupName(group);
			return group.name;
		}

		BackendManager.prototype.getServicesOnBooking = function(booking){
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

		BackendManager.prototype.getReductionsOnBooking = function(booking){
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
		 * Update with the result of add/update booking
		 */
		BackendManager.prototype.returnOfForm = function(data, booking){
			if(data.alerts != null) this.setAlerts(data.alerts);
			if(data.booking != null){
			 	if(this.applyFiltersDate(data.booking) != null || this.applyFiltersDate(booking) != null){
					if(this.getCustomerById(data.customer) != null){
						this.updateCustomer(data.customer);
					}
					else {
						this.addCustomer(data.customer);
					}

					if(booking != null){
						this.removeBooking(booking);
					}
					this.updateBooking(data.booking);
					this.replaceAndGenerateRapportByServices(data.booking);
				}
				if(this.customerCtrl != null && this.customerCtrl.opened){
					if(data.booking.idCustomer == this.customerCtrl.customer.ID){
						this.customerCtrl.updateBooking(data.booking, booking);
					}
				}
				if(this.quotationsListCtrl != null){
					this.quotationsListCtrl.updateBooking(data.booking, booking);
				}
			}
		}

		BackendManager.prototype.closePopupCustomer = function(){
			this.newCustomer = JSON.parse(JSON.stringify(_skeletonCustomer));
      this.displayPopupCustomer = false;
    }

		BackendManager.prototype.isOkNewCustomer = function(){
			return this.newCustomer!=null && ((!this.newCustomer.createWpAccount && this.newCustomer.phone != null && this.newCustomer.phone.length > 0) || (this.newCustomer.createWpAccount && this.newCustomer.email != null && this.newCustomer.email.length > 0)) && !this.launchAddNewCustomer;
		}


		BackendManager.prototype.createCustomer = function(){
  		if(this.isOkNewCustomer()){
  			this.launchAddNewCustomer = true;
  			var newCustomer = JSON.parse(JSON.stringify(this.newCustomer));
  			newCustomer.privateNotes = newCustomer.privateNotes.replace(new RegExp('\n', 'g'),'<br />');

  			var data = {
  				action:'editCustomer',
  				customer: JSON.stringify(newCustomer)
  			}

  			jQuery.post(_ajaxUrl, data, function(data) {
  				_scope.$apply(function(){
  					data = JSON.parse(data);
  					if(typeof data === 'string'){
  						sweetAlert('', data, 'error');
  						_self.launchAddNewCustomer = false;
  					}
  					else {
  						var customer = JSON.parse(data.customer);
  						customer.privateNotes = customer.privateNotes.replace(new RegExp('&lt;br /&gt;', 'g'),'<br />');
  						_self.launchAddNewCustomer = false;
							_self.closePopupCustomer();
							_self.searchCustomersAction();
	  					sweetAlert('', 'OK', 'success');
  					}
					});
  			}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
  		}
  	}

		BackendManager.prototype.changeCalendarView = function(){
			if(this.calendarView == 'month'){
				this.filters.seeBookingsValue = 6;
				this.filters.dates.startDate = new Date(this.viewDate.getFullYear(), this.viewDate.getMonth(), 1);
				this.filters.dates.endDate = new Date(this.viewDate.getFullYear(), this.viewDate.getMonth() + 1, 1, 23, 59, 59, 99);
				this.filters.dates.endDate.setDate(this.filters.dates.endDate.getDate() - 1);
			}
			else if(this.calendarView == 'week'){
				this.filters.seeBookingsValue = 6;
				this.filters.dates.startDate = new Date(this.viewDate.getFullYear(), this.viewDate.getMonth(), this.viewDate.getDate());
				this.filters.dates.endDate = new Date(this.filters.dates.endDate.getFullYear(), this.filters.dates.endDate.getMonth(), this.viewDate.getDate() + 6, 23, 59, 59, 99);
			}
			else if(this.calendarView == 'day'){
				this.filters.seeBookingsValue = 5;
				this.setCustomDate(this.viewDate);
			}
		}

		BackendManager.prototype.changeCalendarViewDate = function(){
			if(this.calendarView == 'day' && !this.dateAlreadyLoaded(this.viewDate)){
				this.filters.seeBookingsValue = 5;
				this.setCustomDate(this.viewDate);
				this.launchBackgroundAppointmentsLoading();
			}
			else {
				this.setDatesInAllViews(this.viewDate);
				this.changeCalendarView();
			}
		}

		BackendManager.prototype.dateClicked = function(date){
			this.viewDate = new Date(date);
			this.calendarView = 'day';
			this.changeCalendarViewDate();
			this.reloadCalendar();
		}

		BackendManager.prototype.displayEvent = function(event){
			var appointment = this.getAppointmentByPresentation(this.appointments, event.title);
			if(appointment != null){
				if(this.isViewMonth()){
					this.dateClicked(event.startsAt);
				}
				else {
					this.openDisplayBooking(this.getBookingById(appointment.idBooking));
					this.reloadCalendar();
				}
			}
			else {
				var infoCalendar = this.getInfoByNote(this.infoCalendars, event.title);
				if(infoCalendar != null){
					this.openEditInfoCalendarDialog(infoCalendar);
				}
				else {
					var serviceConstraint = this.getServiceConstraint(this.serviceConstraints, event.title);
					if(serviceConstraint != null){
						this.openEditServiceConstraintDialog(serviceConstraint);
					}
					else {
						var memberConstraint = this.getMemberConstraint(this.memberConstraints, event.title);
						if(memberConstraint != null){
							this.openEditServiceConstraintDialog(null, memberConstraint);
						}
					}
				}
			}
		}

		BackendManager.prototype.dropEvent = function(event){
			if(!this.actionDropProcess){
				sweetAlert({
    		  title: 'Modifier cet object ?',
    		  text: 'Voulez-vous vraiment modifier cet object du calendrier ?',
    		  type: "warning",
    		  showCancelButton: true,
    		  confirmButtonColor: "#DD6B55",
    		  confirmButtonText: 'Oui',
    		  cancelButtonText: 'Annuler',
    		  closeOnConfirm: true,
    		  html: true
    		}, function(isConfirm){
					if(isConfirm){
						_self.actionDropProcess = true;
						_self.setDraggable(!_self.actionDropProcess);
						var appointment = _self.getAppointmentByPresentation(_self.appointments, event.title);
						if(appointment != null){
							var service = _self.getServiceById(appointment.idService);
							_self.launchDropEvent(appointment, event);
						}
						else {
							var infoCalendar = _self.getInfoByNote(_self.infoCalendars, event.title);
							if(infoCalendar != null){
								_self.launchInfoCalendarDropEvent(event);
							}
							else {
								var serviceConstraint = _self.getServiceConstraint(_self.serviceConstraints, event.title);
								if(serviceConstraint != null){
									_self.launchServiceConstraintDropEvent(event);
								}
								else {
									var memberConstraint = _self.getMemberConstraint(_self.memberConstraints, event.title);
									if(memberConstraint != null){
										_self.launchServiceConstraintDropEvent(event);
									}
								}
							}
						}
					}
					else {
						_self.reloadCalendar();
					}
    		});
			}
			else _self.reloadCalendar();
		}

		/**
		 * Launch drop event
		 */
		 BackendManager.prototype.launchDropEvent = function(appointment, event){
			 var booking = this.getBookingById(appointment.idBooking);
			 var services = this.getNotOldServices();
			 var servicesOnBooking = this.getServicesOnBooking(booking);
			 var members = this.members;
			 var formManager = new FormManager();
			 formManager.initialize(this.services, services, members, null, this.settings, this.paymentsTypeList, _ajaxUrl, '', _scope, function(data){
				 _self.returnOfForm(data, booking);
				 _self.stopDropEvent(false);
			 }, function(){
				 _self.stopDropEvent(true);
			 });
			 formManager.servicesInBooking = servicesOnBooking;
			 formManager.booking = booking;
			 formManager.updateBooking(true);
			 formManager.frontForm = false;
			 formManager.allowInconsistencies = true;
			 if(formManager.booking != null){
				 formManager.customer = this.getCustomerById(formManager.booking.idCustomer);
			 }
			 var serviceParameters = formManager.getServiceParameters(
				 appointment.idService, _self.parseDate(appointment.startDate), _self.parseDate(appointment.endDate));
			 if(serviceParameters != null &&
				 (serviceParameters.dateStart.getTime() != event.startsAt.getTime() ||
				 serviceParameters.dateEnd.getTime() != event.endsAt.getTime())){
				 serviceParameters.dateStart = event.startsAt;
				 serviceParameters.dateEnd = event.endsAt;
				 formManager.replaceInBasketWithParams(serviceParameters);
				 if(formManager.formIsOk()){
					 formManager.validForm();
				 }
				 else {
					 _self.stopDropEvent(true);
				 }
			 }
			 else {
				 _self.stopDropEvent(true);
			 }
		}

		/**
		 * Launch drop event
		 */
		 BackendManager.prototype.launchInfoCalendarDropEvent = function(event){
			 var infoCalendar = this.getInfoByNote(this.infoCalendars, event.title);
			 infoCalendar.date = $filter('formatDateTime')(event.startsAt);
			 infoCalendar.dateEnd = $filter('formatDateTime')(event.endsAt);

			 var data = {
				 action:'editInfoCalendar',
				 infoCalendar: JSON.stringify(infoCalendar)
			 }

			 jQuery.post(ajaxurl, data, function(data) {
				 _scope.$apply(function(){
					 data = JSON.parse(data);
					 if(typeof data === 'string'){
						 alert(data);
					 }
					 else {
						 _self.updateInfoCalendar(data);
						 _self.stopDropEvent(false);
					 }
				 }.bind(this));
			 }.bind(this)).fail(function(err){
	 			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
	 		}).error(function(err){
	 			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
	 		});
		}

		/**
		 * Launch drop event
		 */
		 BackendManager.prototype.launchServiceConstraintDropEvent = function(event){
			 var constraint = this.getServiceConstraint(this.serviceConstraints, event.title);
			 var isServiceConstraint = true;
			 if(constraint == null){
				 isServiceConstraint = false;
				 constraint = this.getMemberConstraint(this.memberConstraints, event.title);
			 }
			 constraint.startDate = $filter('formatDateTime')(event.startsAt);
			 constraint.endDate = $filter('formatDateTime')(event.endsAt);

			 var data = {
				 action:'editServiceConstraint',
				 constraint: JSON.stringify(constraint),
				 isServiceConstraint:JSON.stringify(isServiceConstraint)
			 }

			 jQuery.post(ajaxurl, data, function(data) {
				 _scope.$apply(function(){
					 data = JSON.parse(data);
					 if(typeof data === 'string'){
						 alert(data);
					 }
					 else {
						 if(isServiceConstraint) _self.updateServiceConstraints(data);
						 else _self.updateMemberConstraints(data);
						 _self.stopDropEvent(false);
					 }
				 }.bind(this));
			 }.bind(this)).fail(function(err){
	 			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
	 		}).error(function(err){
	 			sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
	 		});
		}

		BackendManager.prototype.stopDropEvent = function(reload){
			if(reload) {
				_self.reloadCalendar();
			}
			_self.actionDropProcess = false;
			_self.setDraggable(!_self.actionDropProcess);
		}

		BackendManager.prototype.payBookingCaisseOnline = function(booking){
			if(!this.payBookingActionLaunched){
				if(booking.paymentState != 'complete'){
					this.payBookingCaisseOnlineAction(booking);
				}
				else {
					sweetAlert({
	    		  title: 'La réservation est déjà encaissée ?',
	    		  text: 'La réservation est déjà encaissée, voulez-vous vraiment la pousser sur la caisse ?',
	    		  type: "warning",
	    		  showCancelButton: true,
	    		  confirmButtonColor: "#DD6B55",
	    		  confirmButtonText: 'Oui',
	    		  cancelButtonText: 'Annuler',
	    		  closeOnConfirm: true,
	    		  html: true
	    		}, function(){
						_scope.$apply(function(){
							this.payBookingCaisseOnlineAction(booking);
						}.bind(this));
					}.bind(this));
				}
			}
		}

		BackendManager.prototype.getCaisseOnlineLink = function(){
			return this.settings.caisse_online_site_url + '/login/' + this.settings.caisse_online_license_id;
		}

		BackendManager.prototype.payBookingCaisseOnlineAction = function(booking){
			this.payBookingActionLaunched = true;

			var data = {
				action:'payBookingCaisseOnline',
				idBooking: JSON.stringify(booking.id)
			}

			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					data = JSON.parse(data);
					_self.payBookingActionLaunched = false;
					sweetAlert({
	    		  title: '',
	    		  text: data.message,
	    		  type: data.result,
	    		  showCancelButton: true,
	    		  confirmButtonColor: "#DD6B55",
	    		  confirmButtonText: 'Aller sur la caisse',
	    		  cancelButtonText: 'Fermer',
	    		  closeOnConfirm: true,
	    		  html: true
	    		}, function(){
						$window.open(_self.getCaisseOnlineLink(), '_blank');
					}.bind(this));
				});
			}.bind(this)).fail(function(err){
				_scope.$apply(function(){ _self.payBookingActionLaunched = false; });
				sweetAlert('', 'Impossible de se connecter à Caisse Online, veuillez réessayer plus tard !', 'error');
			}).error(function(err){
				_scope.$apply(function(){ _self.payBookingActionLaunched = false; });
				sweetAlert('', 'Impossible de se connecter à Caisse Online, veuillez réessayer plus tard !', 'error');
			});
		}

		/**
		 *
		 */
		BackendManager.prototype.getTotalPayments = function(booking, payments){
			var totalPaiements = 0;
			for(var i = 0; i < payments.length; i++){
				var payment = payments[i];
				if(payment.state == 'ok' || payment.state == null){
					if(payment.isReceipt == null ||
						(!payment.isReceipt && booking.paymentState == 'complete') ||
						(payment.isReceipt && (booking.paymentState == 'advancePayment' || booking.paymentState == 'deposit'))){
						totalPaiements += payment.value * 1;
					}
				}
			}
			return totalPaiements;
		}

		BackendManager.prototype.getCaisseOnlinePaymentForType = function(payment, payments, type){
			for(var i = 0; i < payments.length; i++){
				var localPayment = payments[i];
				if(localPayment.isCaisseOnline != null && localPayment.isCaisseOnline &&
					(payment.value * 1) == localPayment.value && !payment.isCaisseOnline && payment.type == type){
					return localPayment;
				}
			}
			return null;
		}

		/**
		 * get id booking with ticket id bookings
		 */
		BackendManager.prototype.getIdBookingWithTicketIdBooking = function(idBookings){
			var result = [];
		  if(idBookings != null){
		    var idBookings = idBookings.split('&&');
		    for(var i = 0; i < idBookings.length; i++){
		      var localIdBookings = idBookings[i].split('&');
		      if(localIdBookings.length == 1) result.push(localIdBookings[0]);
		      else if(localIdBookings.length == 2) {
		        if(localIdBookings[0].length > 0) result.push(localIdBookings[0]);
		        else if(localIdBookings[1].length > 0) result.push(localIdBookings[1]);
		      }
		    }
		  }
		  return result;
		}

		/**
		 * get payment for booking
		 */
		BackendManager.prototype.getPaymentsForBooking = function(booking){
			var idsBooking = booking.linkOldBookings;
			if(idsBooking != '') idsBooking += ',';
			idsBooking += booking.id;
			var url = this.settings.caisse_online_server_url + 'ticketsByIdBooking/' + this.settings.caisse_online_license_id + '/' + idsBooking;
			jQuery.get(url, function(tickets) {
				_scope.$apply(function(){
					var idPaymentsList = [];
					for(var i = 0; i < booking.payments.length; i++){
						if(booking.payments[i].id != null){
							idPaymentsList.push(booking.payments[i].id);
						}
					}
					for(var i = 0; i < tickets.length; i++){
						var ticket = tickets[i];
						for(var j = 0; j < ticket.payments.length; j++){
							var payment = ticket.payments[j];
							var idPayment = 'ticket' + ticket.id + '_' + j;
							var note = '';
							var idBookings = [];
							if(ticket.idBookings != null){
								idBookings = _scope.backendCtrl.getIdBookingWithTicketIdBooking(ticket.idBookings);
								note = 'MULTI-ENCAISSEMENT - réservations n°';
								for(var k = 0; k < idBookings.length; k++){
									if(k != 0) note += ', ';
									note += idBookings[k];
								}
							}
							if(idPaymentsList.indexOf(idPayment) == -1){
								idPaymentsList.push(idPayment);
								booking.payments.push({
									id:idPayment,
									isCaisseOnline:true,
									isReceipt:(ticket.type != 'ticket'),
									idBooking:booking.id,
									idBookings:idBookings,
									paymentDate:new Date(ticket.date),
									type:payment.type,
									value:payment.amount,
									name:payment.name,
									idReference:payment.externalId,
									credit:(payment.credit == 'true'),
									vendor:ticket.settings.vendor,
									note:note
								});
							}
						}
					}
					var newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, booking.payments);
					if(newNeedToPay < 0){
						var newPayments = [];
						for(var i = 0; i < booking.payments.length; i++){
							var payment = booking.payments[i];
							if(payment.isCaisseOnline || payment.type != 'systempay' || payment.type != 'paypal' || payment.type != 'monetico' || payment.type != 'stripe' || payment.type != 'stripeConnect' || payment.type != 'paybox'){
								newPayments.push(payment);
							}
							else if(payment.type == 'systempay' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'systempay') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'paypal' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paypal') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'monetico' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'monetico') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'stripe' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripe') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'stripeConnect' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'stripeConnect') == null){
								newPayments.push(payment);
							}
							else if(payment.type == 'paybox' && this.getCaisseOnlinePaymentForType(payment, booking.payments, 'paybox') == null){
								newPayments.push(payment);
							}
						}
						newNeedToPay = booking.totalPrice - this.getTotalPayments(booking, newPayments);
					}
					booking.needToPay = newNeedToPay;
					booking.paymentsCaisseOnlineDone = true;
				}.bind(this));
			}.bind(this)).fail(function(err){
			 sweetAlert('', 'Impossible de se connecter à Caisse Online, veuillez réessayer plus tard !', 'error');
		 }).error(function(err){
			 sweetAlert('', 'Impossible de se connecter à Caisse Online, veuillez réessayer plus tard !', 'error');
		 });
		}

		/**
		 *
		 */
		BackendManager.prototype.isFirstParameterNotDone = function(){
			return this.settings != null && !this.settings.firstParameterDone;
		}

		/**
		 *
		 */
		BackendManager.prototype.isCaisseOnlineActivated = function(){
			return this.settings != null && this.settings.caisse_online_activated;
		}

		/**
		 *
		 */
		BackendManager.prototype.isNotSameTimeZone = function(){
			this.alertNotSameDateBackend = this.lastDateBackend != null && (new Date(this.lastDateBackend)).getHours() != (new Date()).getHours();
		}

		/**
		 *
		 */
		BackendManager.prototype.isCaisseOnlineActivatedButNoCaisseOnline = function(){
			return this.settings != null && this.settings.caisse_online_activated_but_nocaisseonline;
		}


		/**
		 * open edit booking popup
		 */
		BackendManager.prototype.openEditBooking = function(booking){
			if(this.editBookingCtrl!=null && !this.settings.staffIsConnected){
				this.editBookingCtrl.opened = true;
				this.editBookingCtrl.servicesInBooking = this.getServicesOnBooking(booking);
				this.editBookingCtrl.reductionsInBooking = this.getReductionsOnBooking(booking);
				this.editBookingCtrl.currentPromoCodes = this.currentPromoCodes;
				this.editBookingCtrl.initializeEditBooking(this.services, this.getNotOldServices(), this.members, this.equipments, this.customers, _skeletonCustomer, this.alerts, this.settings, this.paymentsTypeList, _ajaxUrl, this.jsonURL, _currentUrl, _months, _scope, this.getViewDate(), booking);
			}
		}

		/**
		 * open edit booking popup
		 */
		BackendManager.prototype.openEditBookingCustomer = function(customer){
			if(this.editBookingCtrl!=null && !this.settings.staffIsConnected){
				this.editBookingCtrl.opened = true;
				this.editBookingCtrl.servicesInBooking = this.getServicesOnBooking();
				this.editBookingCtrl.reductionsInBooking = this.getReductionsOnBooking();
				this.editBookingCtrl.initializeEditBooking(this.services, this.getNotOldServices(), this.members, this.equipments, this.customers, _skeletonCustomer, this.alerts, this.settings, this.paymentsTypeList, _ajaxUrl, this.jsonURL, _currentUrl, _months, _scope, this.getViewDate(), null);
				this.editBookingCtrl.getCustomer(customer.ID);
			}
		}

		/**
		 * open display booking popup
		 */
		BackendManager.prototype.openDisplayBooking = function(booking){
			this.openDisplayBookings([booking]);
		}

		/**
		 * open display bookings popup
		 */
		BackendManager.prototype.openDisplayBookings = function(bookings){
			if(this.displayBookingsCtrl!=null){
				this.displayBookingsCtrl.opened = true;
				this.displayBookingsCtrl.initialize(bookings, this.settings);
			}
		}

		/**
		 * open receipt booking dialog
		 */
		BackendManager.prototype.openReceiptBookingDialog = function(booking, customer = null){
			if(this.receiptBookingCtrl!=null){
				this.receiptBookingCtrl.opened = true;
				if(customer == null){
					customer = this.getCustomerById(booking.idCustomer);
				}
				if(customer == null) customer = booking.customer;
				var services = this.getServicesOnBooking(booking);
				var reductions = this.getReductionsOnBooking(booking);
				this.receiptBookingCtrl.initialize(customer, booking, services, reductions, this.idPaymentsTypeToName, true, this.settings);
			}
		}

		/**
		 * open edit booking popup
		 */
		BackendManager.prototype.openCustomerDialog = function(idCustomer){
			if(this.customerCtrl!=null){
				this.customerCtrl.opened = true;
				this.customerCtrl.initialize(idCustomer, this.settings, this.paymentsTypeList, this.idPaymentsTypeToName, _ajaxUrl);
			}
		}

		/**
		 * open add payment popup
		 */
		BackendManager.prototype.openAddPaymentDialog = function(customer, booking, repayment){
			if(this.addPaymentCtrl!=null){
				this.addPaymentCtrl.opened = true;
				this.addPaymentCtrl.initialize(customer, booking, this.settings, this.paymentsTypeList, _skeletonPayment, repayment, _ajaxUrl);
			}
		}

		/**
		 * open ask payment popup
		 */
		BackendManager.prototype.openAskPaymentDialog = function(customer, booking){
			if(this.askPaymentCtrl!=null){
				this.askPaymentCtrl.opened = true;
				this.askPaymentCtrl.initialize(customer, booking, this.settings, this.paymentsTypeList, _ajaxUrl);
			}
		}

		/**
		 * open notification popup
		 */
		BackendManager.prototype.openNotificationDialog = function(customer, booking, typeEmailContent){
			if(this.notificationCtrl!=null){
				this.notificationCtrl.opened = true;
				this.notificationCtrl.initialize(customer, booking, this.settings, this.paymentsTypeList, _ajaxUrl, typeEmailContent);
			}
		}

		/**
		 * open edit info calendar popup
		 */
		BackendManager.prototype.openEditInfoCalendarDialog = function(infoCalendar){
			if(this.newEditInfoCalendarCtrl!=null){
				this.newEditInfoCalendarCtrl.opened = true;
				if(infoCalendar == null) infoCalendar = _skeletonInfoCalendar;
				this.newEditInfoCalendarCtrl.initialize(infoCalendar, this.getViewDate(), this.settings, _ajaxUrl);
			}
		}

		/**
		 * open edit service constraint dialog
		 */
		BackendManager.prototype.openEditServiceConstraintDialog = function(serviceConstraint, memberConstraint){
			if(this.editServiceConstraintCtrl!=null){
				this.editServiceConstraintCtrl.opened = true;
				if(serviceConstraint == null) serviceConstraint = _skeletonServiceConstraint;
				if(memberConstraint == null) memberConstraint = _skeletonMemberConstraint;
				this.editServiceConstraintCtrl.initialize(serviceConstraint, memberConstraint, this.getNotOldServices(), this.members, this.getViewDate(), this.settings, _ajaxUrl);
			}
		}

		/**
		 * open attach booking dialog
		 */
		BackendManager.prototype.openAttachBookingDialog = function(customer, bookings, payment){
			if(this.attachBookingCtrl!=null){
				this.attachBookingCtrl.opened = true;
				this.attachBookingCtrl.initialize(customer, bookings, payment, this.settings, _ajaxUrl);
			}
		}

		/**
		 * open merge customer dialog
		 */
		BackendManager.prototype.openMergeCustomerDialog = function(customer){
			if(this.mergeCustomerCtrl!=null){
				this.mergeCustomerCtrl.opened = true;
				this.mergeCustomerCtrl.initialize(customer, this.settings, _ajaxUrl);
			}
		}

		/**
		 * open payment booking dialog
		 */
		BackendManager.prototype.openPayBookingDialog = function(customer, booking){
			if(this.payBookingCtrl!=null){
				this.payBookingCtrl.opened = true;
				this.payBookingCtrl.initialize(customer, booking, this.paymentsTypeList,this.settings, _ajaxUrl);
			}
		}

		/**
		 * open groups manager
		 */
		BackendManager.prototype.openGroupsManagerDialog = function(rapportByServices){
			if(this.groupsCtrl!=null){
				this.groupsCtrl.opened = true;
				this.groupsCtrl.initialize(rapportByServices, _skeletonGroup, this.members, this.services, this.settings, _ajaxUrl);
			}
		}

		/**
		 * open group manager
		 */
		BackendManager.prototype.openGroupManagerDialog = function(rapportByServices, group, editGroupView){
			if(this.groupCtrl!=null && !this.groupCtrl.opened){
				this.groupCtrl.opened = true;
				this.groupCtrl.initialize(rapportByServices, group, this.members, this.services, this.settings, _ajaxUrl);
				if(editGroupView != null)	this.groupCtrl.editGroupView = editGroupView;
			}
		}

		/**
		 * open payment state manager
		 */
		BackendManager.prototype.openPaymentStateDialog = function(booking){
			if(!this.isCaisseOnlineActivated() && this.paymentStateCtrl!=null && !this.paymentStateCtrl.opened){
				this.paymentStateCtrl.opened = true;
				this.paymentStateCtrl.initialize(booking, this.settings, _ajaxUrl);
			}
		}



		return BackendManager;
	}


	angular.module('resa_app').factory('BackendManager', BackendManagerFactory)
}());
