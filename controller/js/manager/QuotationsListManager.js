"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;
	var _backendCtrl = null;

	function QuotationsListManagerFactory($filter, FunctionsManager){

		var QuotationsListManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(QuotationsListManager.prototype, FunctionsManager.prototype);

			this.lastModificationDateBooking = new Date();
			this.bookings = [];
			this.filteredBookings = [];
			this.paginationBookings = {step:10, number:1};
			this.displayedBookings = [];
			this.filters = {};
			this.getQuotationsLaunched = false;

			this.customers = [];

      _scope = scope;
			_self = this;
			_backendCtrl = _scope.backendCtrl;
      _scope.backendCtrl.quotationsListCtrl = this;
		}

		QuotationsListManager.prototype.launchGetQuotations = function(){
			this.bookings = [];
			this.reinitFilters();
			this.getQuotationsLaunched = true;
			this.getQuotations(0, 5);
		}

		QuotationsListManager.prototype.stopGetQuotations = function(){
			this.getQuotationsLaunched = false;
		}


		QuotationsListManager.prototype.getQuotations = function(offset, limit){
			var data = {
				action:'getQuotations',
				filters:JSON.stringify({places:this.filters.places}),
				offset: offset,
				limit: limit
			}
			jQuery.post(_backendCtrl.getAjaxUrl(), data, function(data) {
				_scope.$apply(function(){
					data = JSON.parse(data);
					for(var i = 0; i < data.customers.length; i++){
						var customer = data.customers[i];
						if(_self.getCustomerById(customer.ID) == null){
							_self.addCustomer(customer);
						}
					}
					for(var i = 0; i < data.bookings.length; i++){
						var booking = data.bookings[i];
						booking = this.addBookingAnnotations(booking);
						this.bookings.push(booking);
					}

					if(data.countData >= limit  && this.getQuotationsLaunched){
						this.getQuotations(offset + limit, limit);
					}
					else {
						this.getQuotationsLaunched = false;
						this.generateFilteredBookings();
						setInterval(function(){ _self.updateBackend() }, 15000);
					}
				}.bind(this));
			}.bind(this)).fail(function(err){
				_self.getQuotationsLaunched = false;
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				setInterval(function(){ _self.updateBackend() }, 15000);
			}).error(function(err){
				_self.getQuotationsLaunched = false;
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				setInterval(function(){ _self.updateBackend() }, 15000);
			});
		}


		/**
		 *
		 */
		QuotationsListManager.prototype.updateBackend = function(){
			if(!this.getQuotationsLaunched){
				var lastModificationDateBooking = $filter('formatDateTime')(this.lastModificationDateBooking);
				var allIdCreation = [];
				for(var i = 0; i < this.bookings.length; i++){
					var booking = this.bookings[i];
					if(booking.idCreation > -1){
						allIdCreation.push(booking.idCreation);
					}
					else {
						allIdCreation.push(booking.id);
				}
				}

				var data = {
					action:'updateBackendQuotations',
					lastModificationDateBooking:JSON.stringify(lastModificationDateBooking),
					filters:JSON.stringify({places:this.filters.places}),
					allIdCreation:JSON.stringify(allIdCreation)
				}
				jQuery.post(_backendCtrl.getAjaxUrl(), data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						for(var i = 0; i < data.customers.length; i++){
							var customer = data.customers[i];
							if(_self.getCustomerById(customer.ID) != null){
								_self.updateCustomer(customer);
							}
							else _self.addCustomer(customer);
						}
						var bookings = data.bookings;
						for(var i = 0; i < bookings.length; i++){
							var booking = bookings[i];
							booking = _self.addBookingAnnotations(booking);
							_self.updateBookingBackend(booking);
						}
						_self.generateFilteredBookings();
					});
				});
			}
		}

		QuotationsListManager.prototype.initStateFilters = function(){
			for(var i = 0; i < _backendCtrl.settings.statesList.length; i++){
				_self.filters.states[_backendCtrl.settings.statesList[i].id] = _backendCtrl.settings.statesList[i].selected;
			}
			_self.filters.states['quotation_waiting'] = true;
			_self.filters.states['quotation_waiting_customer'] = true;
			_self.filters.states['quotation_waiting_customer_expired'] = true;
			_self.filters.states['waiting_payment'] = true;
			_self.filters.states['waiting_expired'] = true;
		}

		QuotationsListManager.prototype.getBookingById = function(idBooking){
			for(var i = 0; i < this.bookings.length; i++){
				if(this.bookings[i].id == idBooking)
					return this.bookings[i];
			}
			return null;
		}


		QuotationsListManager.prototype.reinitFilters = function(){
			this.filters.typeFilters = {};
			this.filters.search = '';
			this.filters.places = {};
			this.filters.states = {};
			this.filters.statePaymentsList = {};
			this.filters.paymentsTypeList = {};
			this.filters.tags = {};
			this.filters.services = {};
			this.filters.members = {};
			this.filters.seeBookingsValue = '1';

			if(_backendCtrl.settings != null){
				var filterSettings = _backendCtrl.currentRESAUser.filterSettings;
				if(_backendCtrl.settings.places!=null){
					for(var i = 0; i < _backendCtrl.settings.places.length; i++){
						this.filters.places[_backendCtrl.settings.places[i].id] = filterSettings == null || filterSettings.places == null || filterSettings.places[_backendCtrl.settings.places[i].id] == null || filterSettings.places[_backendCtrl.settings.places[i].id];
					}
				}

				if(_backendCtrl.settings.appointmentTags!=null){
					_self.initStateFilters();
					this.filters.tags['no_tag'] = true;
					for(var i = 0; i < _backendCtrl.settings.appointmentTags.length; i++){
						this.filters.tags[_backendCtrl.settings.appointmentTags[i].id] = true;
					}
				}
			}
			var services = _backendCtrl.getNotOldServices();
			for(var i = 0; i < services.length; i++){
				this.filters.services[services[i].id] = true;
			}
			for(var i = 0; i < _backendCtrl.members.length; i++){
				this.filters.members[_backendCtrl.members[i].id] = true;
			}
			this.generateFilteredBookings();
		}

		QuotationsListManager.prototype.setFilterSettings = function(){
			_backendCtrl.currentRESAUser.filterSettings = {places:this.filters.places};
		}

		QuotationsListManager.prototype.generateFilteredBookings = function(){
			this.filteredBookings = [];
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.applyFilters(this.bookings[i]);
				if(booking != null){
					this.filteredBookings.push(booking);
				}
			}
			this.changeOrderByBookings();
			this.applyPaginationBookings();
		}


		QuotationsListManager.prototype.applyFilters = function(booking){
			booking = JSON.parse(JSON.stringify(booking));
			var ok = true;
			//Filters booking
			if(ok){
				if(booking.quotationRequest) ok = this.filters.states['quotation_waiting'];
				else {
					var expired = false;
					if(_backendCtrl.settings != null && booking.numberSentEmailQuotation >= _backendCtrl.settings.no_response_quotation_times){
						expired = true;
					}
					ok = (this.filters.states['quotation_waiting_customer'] && !expired) ||
					(this.filters.states['quotation_waiting_customer_expired'] && expired);
				}
			}
			if(ok && this.filters.search.length >= 3 && isNaN(Number(this.filters.search))){
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
				ok = (booking.id == this.filters.search * 1);
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

		QuotationsListManager.prototype.applyFiltersDate = function(booking){
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
			return booking;
		}

		QuotationsListManager.prototype.applyFiltersAppointmentDate = function(appointment){
			var actualDate = new Date();
			var date = this.parseDate(appointment.startDate);
			if(this.filters.seeBookingsValue == 2) return date < actualDate;
			else if(this.filters.seeBookingsValue == 3) return date >= actualDate;
			return true;
		}

		QuotationsListManager.prototype.applyFiltersAppointment = function(appointment, booking){
			var ok = appointment.idPlace == '' || this.filters.places[appointment.idPlace];
			if(ok){
				if(appointment.state == 'waiting'){
					var state = _backendCtrl.getWaitingSubState(booking);
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
					var service = _backendCtrl.getServiceById(key);
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
			if(ok && _backendCtrl.members.length > 0 && appointment.appointmentMembers.length > 0){
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

		QuotationsListManager.prototype.changeOrderByBookings = function(){
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

		QuotationsListManager.prototype.applyPaginationBookings = function(){
			this.displayedBookings = this.filteredBookings.slice((this.paginationBookings.number - 1) * this.paginationBookings.step, this.paginationBookings.step * this.paginationBookings.number);
		}

		QuotationsListManager.prototype.getCustomerById = function(idCustomer){
			for(var i = 0; i < this.customers.length; i++){
				if(this.customers[i].ID == idCustomer)
					return this.customers[i];
			}
			return null;
		}

		QuotationsListManager.prototype.addCustomer = function(customer){
			this.customers.push(customer);
		}

		QuotationsListManager.prototype.updateCustomer = function(customer){
			for(var i = 0; i < this.customers.length; i++){
				if(this.isSameCustomer(this.customers[i], customer)){
					this.customers[i] = customer;
				}
			}
		}

		QuotationsListManager.prototype.isSameCustomer = function(customerA, customerB){
			return customerA.ID == customerB.ID && customerA.isWpUser == customerB.isWpUser;
		}

		QuotationsListManager.prototype.getCustomerById = function(idCustomer){
			for(var i = 0; i < this.customers.length; i++){
				if(this.customers[i].ID == idCustomer)
					return this.customers[i];
			}
			return null;
		}


		QuotationsListManager.prototype.addBookingAnnotations = function(booking){
			var lastModificationDateBooking = $filter('parseDate')(booking.modificationDate);
			if(lastModificationDateBooking > this.lastModificationDateBooking){
				this.lastModificationDateBooking = new Date(lastModificationDateBooking);
			}
			booking.cssPaymentState = _backendCtrl.calculateBookingPayment(booking);
			booking.intervals = [];
			booking.customer = this.getCustomerById(booking.idCustomer);
			var appointments = booking.appointments;
			if(appointments != null){
				for(var k = 0; k < appointments.length; k++){
					var appointment = appointments[k];
					appointment.customer = booking.customer;
				}
			}
			return booking;
		}

		/**
		 *
		 */
		QuotationsListManager.prototype.updateBooking = function(newBooking, oldBooking){
			if(oldBooking != null && this.getBookingById(oldBooking.id)){
				newBooking = this.addBookingAnnotations(newBooking);
				this.removeBookingById(oldBooking.id);
				this.bookings.push(newBooking);
				this.generateFilteredBookings();
			}
			else if(this.getBookingById(newBooking.id)){
				newBooking = this.addBookingAnnotations(newBooking);
				for(var i = 0; i < this.bookings.length; i++){
					if(this.bookings[i].id == newBooking.id){
						this.bookings[i] = newBooking;
					}
				}
				this.generateFilteredBookings();
			}
		}

		/**
		 *
		 */
		QuotationsListManager.prototype.removeBookingById = function(idBooking){
			var newBookings = [];
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				if(booking.id != idBooking){
					newBookings.push(booking);
				}
			}
			this.bookings = newBookings;
		}

		/**
		 *
		 */
		QuotationsListManager.prototype.updateBookingBackend = function(booking){
			var arrayOldBookings = booking.linkOldBookings.split(',');
			for(var j = 0; j < arrayOldBookings.length; j++){
				this.removeBookingById(arrayOldBookings[j], false);
			}
			this.removeBookingById(booking.id);
			if(booking.quotation){
				this.bookings.push(booking);
			}
		}

		/**
		 * open edit booking popup
		 */
		QuotationsListManager.prototype.openEditBooking = function(){
			_backendCtrl.openEditBooking();
			_backendCtrl.editBookingCtrl.quotation = true;
		}


		return QuotationsListManager;
	}

	angular.module('resa_app').factory('QuotationsListManager', QuotationsListManagerFactory);
}());
