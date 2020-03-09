"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function CustomerManagerFactory(FunctionsManager, $filter){

		var CustomerManager = function(scope){
			FunctionsManager.call(this);
			angular.extend(CustomerManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.customer = null;
      this.modelCustomer = null;
			this.isNotWpUser = false;
      this.bookings = [];
			this.bookingsOrderBy = 0;

      this.payments = [];
      this.settings = [];
      this.paymentsTypeList = [];
      this.idPaymentsTypeToName = {};
			this.synchronizationCaisseOnlineAction = false;
			this.launchEditCustomer = false;
			this.editionMode = false;
			this.caisseOnlinePayments = null;
			this.getCaisseOnlinePaymentsLaunched = false;
			this.updateBackendLaunched = false;
			this.timeoutUpdateBackend = null;
			this.balance = null;

			this.lastIdLogNotifications = 0;
			this.logNotifications = [];

			this.lastIdEmailCustomer = 0;
			this.emailsCustomer = [];
			this.historic = [];
			this.page = 0;

			this.displayEmailCustomer = null;

			_self = this;
			_scope = scope;
      _scope.backendCtrl.customerCtrl = this;
		}

		CustomerManager.prototype.initialize = function(idCustomer, settings, paymentsTypeList, idPaymentsTypeToName, ajaxUrl) {
			//reinit
			this.payments = [];
			this.balance = 0;
			this.historic = [];
      this.customer = null;
      this.bookings = [];

			this.caisseOnlinePayments = null;
      this.settings = settings;
      this.paymentsTypeList = paymentsTypeList;
      this.idPaymentsTypeToName = idPaymentsTypeToName;
			this.editionMode = false;
			_ajaxUrl = ajaxUrl;
			this.getCustomerById(idCustomer);
		}

    CustomerManager.prototype.close = function(){
      this.opened = false;
      this.customer = null;
      this.bookings = [];
			this.caisseOnlinePayments = null;
    }

		CustomerManager.prototype.getTypeAccountName = function(){
			return _scope.backendCtrl.getTypeAccountName(this.customer);
		}

		CustomerManager.prototype.updateData = function(customer, bookings, logNotifications, emailsCustomer){
			delete customer.bookings; //Create circular structure when add annotations
			this.updateCustomer(customer);
      this.bookings = bookings;
			this.logNotifications = logNotifications;
			this.emailsCustomer = emailsCustomer;
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				booking = this.addBookingAnnotations(booking);
			}
      this.generateAllPaymentsAndAskPayments();
		}

		/**
		 * order by booking with angular
		 */
		CustomerManager.prototype.executeBookingsOrderBy = function(booking1){
			if(_self.bookingsOrderBy == 0 || _self.bookingsOrderBy == 1) return _self.parseDate(booking1.creationDate);
			return _self.parseDate(booking1.appointments[0].startDate);
		}

		/**
		 * return the last id
		 */
		CustomerManager.prototype.getLastBookingId = function(idBooking){
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				var arrayOldBookings = booking.linkOldBookings.split(',');
				if(booking.id == idBooking || arrayOldBookings.indexOf(idBooking) != -1){
					return booking.id;
				}
			}
			return -1;
		}

		/**
		 * return the last id
		 */
		CustomerManager.prototype.getIdCreationBooking = function(idBooking){
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				var arrayOldBookings = booking.linkOldBookings.split(',');
				if(booking.id == idBooking || arrayOldBookings.indexOf(idBooking) != -1){
					if(booking.idCreation > -1) return booking.idCreation;
					return booking.id;
				}
			}
			return -1;
		}

		/**
		 * return the last id
		 */
		CustomerManager.prototype.getBookingIdCreationOrId = function(idBooking){
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				if(booking.id == idBooking || booking.idCreation == idBooking){
					return booking;
				}
			}
			return null;
		}

		CustomerManager.prototype.openDisplayBooking = function(booking){
			if(booking == null){
				sweetAlert('', 'Réservation non trouvée, elle a peut-être été supprimée ?', 'error');
			}
			else if(_scope.backendCtrl != null){
				_scope.backendCtrl.openDisplayBooking(booking);
			}
		}

		/**
		 * get booking by id
		 */
		CustomerManager.prototype.getBookingById = function(idBooking){
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				if(booking.id == idBooking){
					return booking;
				}
			}
			return null;
		}

		CustomerManager.prototype.removeBookingById = function(idBooking){
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
		 * remove booking and recalculate
		 */
		CustomerManager.prototype.removeBooking = function(booking){
			this.removeBookingById(booking.id);
			this.generateAllPaymentsAndAskPayments();
		}



		/**
		 * update booking and delete oldBooking
		 */
		CustomerManager.prototype.updateBooking = function(newBooking, oldBooking){
			if(oldBooking != null){
				this.removeBookingById(oldBooking.id);
			}
			newBooking = this.addBookingAnnotations(newBooking);
			var found = false;
			for(var i = 0; i < this.bookings.length; i++){
				if(this.bookings[i].id == newBooking.id){
					found = true;
					this.bookings[i] = newBooking;
				}
			}
			if(!found){
				this.bookings.push(newBooking);
			}
      this.generateAllPaymentsAndAskPayments();
		}

		CustomerManager.prototype.justReplaceBooking = function(booking){
			for(var i = 0; i < this.bookings.length; i++){
				if(this.bookings[i].id == booking.id){
					this.bookings[i] = booking;
				}
			}
		}

		CustomerManager.prototype.addBookingAnnotations = function(booking){
			booking.intervals = [];
			booking.cssPaymentState = _scope.backendCtrl.calculateBookingPayment(booking);
			booking.customer = this.customer;
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


    CustomerManager.prototype.generateAllPaymentsAndAskPayments = function(){
			var payments = [];
			var alreadyIdPayments = [];
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				if(booking.typePaymentChosen == 'swikly' && booking.transactionId != ''){
					var payment = {
						id:booking.id + '_' + booking.transactionId,
						paymentDate:new Date(booking.creationDate),
						type:'swikly',
						value:'1',
						repayment:false,
						note:'Dépot avec Swikly',
						state:'ok',
						isReceipt:true,
						idReference:booking.transactionId
					}
					if(alreadyIdPayments.indexOf(payment.id) == -1){
						payment.idBooking = this.getLastBookingId(payment.idBooking);
						payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
						payments.push(payment);
						if(payment.id != null) alreadyIdPayments.push(payment.id);
						payments[payments.length - 1].isAskPayment = false;
					}
				}

				for(var j = 0; j < booking.payments.length; j++){
					var payment = booking.payments[j];
					if(alreadyIdPayments.indexOf(payment.id) == -1){
						payment.idBooking = this.getLastBookingId(payment.idBooking);
						payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
						payments.push(payment);
						if(payment.id != null) alreadyIdPayments.push(payment.id);
						payments[payments.length - 1].isAskPayment = false;
					}
				}
				for(var j = 0; j < booking.askPayments.length; j++){
					var askPayment = booking.askPayments[j];
					askPayment.idBooking = this.getLastBookingId(askPayment.idBooking);
					askPayment.idBookingCreation = this.getIdCreationBooking(askPayment.idBooking);
					payments.push(askPayment);
					payments[payments.length - 1].paymentDate = payments[payments.length - 1].date;
					payments[payments.length - 1].isAskPayment = true;
				}
			}
			for(var i = 0; i < this.customer.payments.length; i++){
				var payment = this.customer.payments[i];
				payment.idBooking = this.getLastBookingId(payment.idBooking);
				payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
				payments.push(payment);
				payments[payments.length - 1].isAskPayment = false;
			}
			if(this.caisseOnlinePayments != null){
				for(var i = 0; i < this.caisseOnlinePayments.length; i++){
					var payment = this.caisseOnlinePayments[i];
					if(alreadyIdPayments.indexOf(payment.id) == -1){
						payment.idBooking = this.getLastBookingId(payment.idBooking);
						payment.idBookingCreation = this.getIdCreationBooking(payment.idBooking);
						payments.push(payment);
						alreadyIdPayments.push(payment.id);
						payments[payments.length - 1].isAskPayment = false;
					}
				}
			}
			this.payments = payments;
			this.balance = this.getBalance();
			this.generateHistorics();
		}

		CustomerManager.prototype.generateHistorics = function(){
			this.historic = [];
			for(var i = 0; i < this.payments.length; i++){
				var payment = this.payments[i];
				payment.isPayment = true;
				this.historic.push(payment);
			}
			for(var i = 0; i < this.logNotifications.length; i++){
				var logNotification = this.logNotifications[i];
				logNotification.isLogNotification = true;
				this.historic.push(logNotification);
				this.lastIdLogNotifications = Math.max(this.lastIdLogNotifications, logNotification.id);
			}
			for(var i = 0; i < this.emailsCustomer.length; i++){
				var emailCustomer = this.emailsCustomer[i];
				emailCustomer.isEmailCustomer = true;
				emailCustomer.idBooking = this.getIdCreationBooking(emailCustomer.idBooking);
				this.historic.push(emailCustomer);
				this.lastIdEmailCustomer = Math.max(this.lastIdEmailCustomer, emailCustomer.id);
			}
			this.historic.sort(function(lineA, lineB){
				var dateA = null;
				if(lineA.isPayment) dateA = new Date(lineA.paymentDate);
				if(lineA.isLogNotification) dateA = new Date(lineA.creationDate);
				if(lineA.isEmailCustomer) dateA = new Date(lineA.creationDate);
				var dateB = null;
				if(lineB.isPayment) dateB = new Date(lineB.paymentDate);
				if(lineB.isLogNotification) dateB = new Date(lineB.creationDate);
				if(lineB.isEmailCustomer) dateB = new Date(lineB.creationDate);
				return dateB.getTime() - dateA.getTime();
			});
		}

		CustomerManager.prototype.isDisplayed = function(line, WPDEBUG){
			if(line.state == 'pending' && !WPDEBUG) return false;
			if(this.page == 0) return true;
			else if(this.page == 1 && line.isPayment) return true;
			else if(this.page == 2 && line.isLogNotification) return true;
			else if(this.page == 3 && line.isEmailCustomer) return true;
			return false;
		}

    CustomerManager.prototype.getPaymentName = function(idPayment, name){
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



		CustomerManager.prototype.getAllPaymentName = function(idPayments){
			var result = '';
			var allPaymentName = idPayments.split(',');
			for(var i = 0; i < allPaymentName.length; i++){
				if(i != 0) result += ', ';
				result += this.getPaymentName(allPaymentName[i]);
			}
			return result;
		}

		CustomerManager.prototype.isAskPaymentState = function(payment){
			if(payment == null || (payment.isAskPayment != null && !payment.isAskPayment)) return '';
			if(this.parseDate(payment.expiredDate).getTime() < new Date().getTime()) return '<span class="bg_rouge">Demande expiré !</span>';
			else return '<span class="bg_vert">Demande en cours...</span>';
		}

		CustomerManager.prototype.round = function(value){
			return Math.round(value * 100) / 100;
		}

    CustomerManager.prototype.isSynchronizedCaisseOnline = function(){
			return this.settings!=null && this.settings.caisse_online_activated && this.customer!=null && this.customer.idCaisseOnline!=null && this.customer.idCaisseOnline != '';
		}

    CustomerManager.prototype.isSynchronizedEmails = function(){
			return this.settings!=null && this.settings.mailbox_activated;
		}

		CustomerManager.prototype.calculateBalance = function(payments){
			var totalPaiements = 0;
			for(var i = 0; i < payments.length; i++){
				var payment = payments[i];
				if(!payment.isCaisseOnline  && !payment.isAskPayment && (payment.state == 'ok' || payment.state == null)){
					var booking = this.getBookingById(payment.idBooking);
					if(booking != null && booking.paymentState != 'complete'){
						totalPaiements += payment.value * 1;
					}
				}
			}
			var totalPaiementsCaisse = 0;
			if(this.caisseOnlinePayments != null){
				for(var i = 0; i < this.caisseOnlinePayments.length; i++){
					var payment = this.caisseOnlinePayments[i];
					if(payment.isReceipt){
						totalPaiementsCaisse += payment.value * 1;
					}
					else if(payment.credit != null && payment.credit) {
						totalPaiementsCaisse -= payment.value * 1;
					}
				}
			}
			var totalPrice = 0;
			if(this.bookings != null){
				for(var i = 0; i < this.bookings.length; i++){
					var booking = this.bookings[i];
					if(booking != null && booking.paymentState != 'complete'){
						totalPrice += booking.totalPrice;
					}
				}
			}
			return (totalPaiements + totalPaiementsCaisse) - totalPrice;
		}

		CustomerManager.prototype.getBalance = function(){
			var balance = this.calculateBalance(this.payments);
			if(balance <= 0) return balance;
			else {
				var newPayments = [];
				for(var i = 0; i < this.payments.length; i++){
					var payment = this.payments[i];
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
				return this.calculateBalance(newPayments);
			}
		}

		CustomerManager.prototype.getCaisseOnlineBalance = function(){
			var totalPaiementsCaisse = 0;
			if(this.caisseOnlinePayments != null){
				for(var i = 0; i < this.caisseOnlinePayments.length; i++){
					var payment = this.caisseOnlinePayments[i];
					if(payment.isReceipt){
						totalPaiementsCaisse += payment.value * 1;
					}
					else if(payment.credit != null && payment.credit) {
						totalPaiementsCaisse -= payment.value * 1;
					}
				}
			}
			return totalPaiementsCaisse;
		}

		CustomerManager.prototype.getCaisseOnlinePaymentForType = function(payment, payments, type){
			for(var i = 0; i < payments.length; i++){
				var localPayment = payments[i];
				if(localPayment.isCaisseOnline != null && localPayment.isCaisseOnline &&
					(payment.value * 1) == localPayment.value && !payment.isCaisseOnline && payment.type == type){
					return localPayment;
				}
			}
			return null;
		}

		CustomerManager.prototype.isOkCustomer = function(){
			return this.modelCustomer!=null && ((!this.modelCustomer.createWpAccount && !this.modelCustomer.isWpUser && this.modelCustomer.phone != null && this.modelCustomer.phone.length > 0) || ((this.modelCustomer.createWpAccount || this.modelCustomer.isWpUser) && this.modelCustomer.email != null && this.modelCustomer.email.length > 0)) && !this.launchEditCustomer;
		}

		CustomerManager.prototype.isDeletable = function(){
			return this.customer!=null && (this.customer.role == 'RESA_Customer' || this.customer.role == '' || this.customer.askAccount) && this.bookings.length == 0;
		}

		CustomerManager.prototype.openEmailCustomer = function(emailCustomer){
			this.emailCustomerDisplayed = emailCustomer;
		}

		CustomerManager.prototype.closeEmailCustomer = function(){
			this.emailCustomerDisplayed = null;
		}

		CustomerManager.prototype.openNotificationDialog = function(){
			this.closeEmailCustomer();
			_scope.backendCtrl.openNotificationDialog(this.customer);
			if(_scope.backendCtrl.notificationCtrl != null){
				_scope.backendCtrl.notificationCtrl.displayCustomEmailContent();
			}
		}

		CustomerManager.prototype.loadBookingPeriod = function(booking){
			if(_scope.backendCtrl != null){
				_scope.backendCtrl.loadBookingPeriod(booking);
				_scope.backendCtrl.setCurrentPage('reservations');
				_scope.backendCtrl.setViewMode('bookingsList');
				this.close()
			}
		}

		CustomerManager.prototype.cancelPaymentAction = function(payment, dialogTexts){
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
					action:'cancelPayment',
					idPayment: JSON.stringify(payment.id)
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						if(typeof data === 'string'){
							sweetAlert('', data, 'error');
						}
						else {
							_self.returnAddPayment(data);
							sweetAlert('', 'Ok', 'success');
						}
					});
				}).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});

			});
		}

		CustomerManager.prototype.modifyCustomer = function(){
  		if(this.isOkCustomer()){
  			this.launchEditCustomer = true;
  			var modelCustomer = JSON.parse(JSON.stringify(this.modelCustomer));
  			modelCustomer.privateNotes = modelCustomer.privateNotes.replace(new RegExp('\n', 'g'),'<br />');
  			var data = {
  				action:'editCustomer',
  				customer: JSON.stringify(modelCustomer)
  			}

  			jQuery.post(_ajaxUrl, data, function(data) {
  				_scope.$apply(function(){
  					data = JSON.parse(data);
						_self.launchEditCustomer = false;
  					if(typeof data === 'string'){
  						sweetAlert('', data, 'error');
  					}
  					else {
  						var customer = JSON.parse(data.customer);
  						customer.privateNotes = customer.privateNotes.replace(new RegExp('&lt;br /&gt;', 'g'),'<br />');
							if(data.status == 'replaced') _self.returnAddPayment(customer, data.oldID);
							else _self.returnAddPayment(customer);
							if(_scope.backendCtrl != null){
								_scope.backendCtrl.searchCustomersAction();
							}
	  					sweetAlert('', 'OK', 'success');
							_self.updateData(customer, _self.bookings, _self.logNotifications, _self.emailsCustomer);
							_self.editionMode = false;
  					}
					});
  			}).fail(function(err){
					_scope.$apply(function(){ _self.launchEditCustomer = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			}).error(function(err){
					_scope.$apply(function(){ _self.launchEditCustomer = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
  		}
  	}

		CustomerManager.prototype.acceptAskAccount = function(){
  		if(this.isOkCustomer()){
				sweetAlert({
				  title: 'Accepter compte',
				  text: 'Voulez-vous accepter cette demande de compte ?',
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonColor: "#DD6B55",
				  confirmButtonText: 'Oui',
				  cancelButtonText: 'Annulé',
				  closeOnConfirm: true,
				  html: false
				}, function(){
	  			_scope.$apply(function(){ _self.launchEditCustomer = true; });
	  			var data = {
	  				action:'acceptAskAccount',
	  				idCustomer: JSON.stringify(_self.customer.ID)
	  			}
	  			jQuery.post(_ajaxUrl, data, function(data) {
	  				_scope.$apply(function(){
	  					data = JSON.parse(data);
							_self.launchEditCustomer = false;
	  					if(typeof data === 'string'){
	  						sweetAlert('', data, 'error');
	  					}
	  					else {
	  						var customer = JSON.parse(data.customer);
	  						customer.privateNotes = customer.privateNotes.replace(new RegExp('&lt;br /&gt;', 'g'),'<br />');
								_self.returnAddPayment(customer, data.oldID);
		  					sweetAlert('', 'OK', 'success');
								_self.editionMode = false;
	  					}
						});
	  			}).fail(function(err){
						_scope.$apply(function(){ _self.launchEditCustomer = false; });
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
	  			}).error(function(err){
						_scope.$apply(function(){ _self.launchEditCustomer = false; });
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					});
				});
			}
  	}

		CustomerManager.prototype.deleteCustomerAction = function(dialogTexts){
			if(this.isDeletable()){
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
						action:'deleteCustomer',
						idCustomer: JSON.stringify(_self.customer.ID)
					}
					jQuery.post(ajaxurl, data, function(data) {
						_scope.$apply(function(){
							console.log(data);
							data = JSON.parse(data);
							if(!data.status){
								sweetAlert('', data.message, 'error');
							}
							else {
								_scope.backendCtrl.deleteCustomer(data.oldID);
								_self.close();
								_scope.backendCtrl.searchCustomersAction();
								sweetAlert('', data.message, 'success');
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


		CustomerManager.prototype.clearSynchronizationCaisseOnline = function(dialogTexts){
			if(!this.synchronizationCaisseOnlineAction){
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
					_scope.$apply(function(){ _self.CustomerManager = true;});
					var data = {
						action:'clearSynchronizationCaisseOnline',
						idCustomer: JSON.stringify(_self.customer.ID)
					}
					jQuery.post(_ajaxUrl, data, function(data) {
						_scope.$apply(function(){
							_self.CustomerManager = false;
							data = JSON.parse(data);
							if(typeof data === 'string'){
								sweetAlert('', data, 'error');
							}
							else {
	  						data.privateNotes = data.privateNotes.replace(new RegExp('&lt;br /&gt;', 'g'),'<br />');
								_self.returnAddPayment(data);
								sweetAlert('', 'Ok !', 'success');
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

		CustomerManager.prototype.getCustomerById = function(idCustomer){
			if(!this.launchEditCustomer){
				this.launchEditCustomer = true;
				var data = {
					action:'getCustomerById',
					idCustomer: JSON.stringify(idCustomer)
				}

				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						_self.launchEditCustomer = false;
						data = JSON.parse(data);
						if(typeof data === 'string'){
							sweetAlert('', data, 'error');
						}
						else {
							_self.updateData(data.customer, data.customer.bookings, data.logNotifications, data.emailsCustomer);
							_self.launchUpdateBackend();
							_self.getAnotherPayments();
						}
					});
				}).fail(function(err){
					_scope.$apply(function(){ _self.launchEditCustomer = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					_scope.$apply(function(){ _self.launchEditCustomer = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		/**
		 * get anothers payment
		 */
		CustomerManager.prototype.getAnotherPayments = function(){
			if(!this.getCaisseOnlinePaymentsLaunched && this.isSynchronizedCaisseOnline()){
				this.getCaisseOnlinePaymentsLaunched = true;
				var url = this.settings.caisse_online_server_url + 'ticketsByIdCustomer/' + this.settings.caisse_online_license_id + '/' + this.customer.ID;
				jQuery.get(url, function(tickets) {
					_scope.$apply(function(){
						this.getCaisseOnlinePaymentsLaunched = false;
						var payments = [];
						for(var i = 0; i < tickets.length; i++){
							var ticket = tickets[i];
							for(var j = 0; j < ticket.payments.length; j++){
								var payment = ticket.payments[j];
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
								payments.push({
									id:'ticket' + ticket.id+ '_' + j,
									isCaisseOnline:true,
									isReceipt:(ticket.type != 'ticket'),
									idBooking:ticket.idBooking,
									idBookings:_scope.backendCtrl.getIdBookingWithTicketIdBooking(ticket.idBookings),
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
						_self.caisseOnlinePayments = payments;
						_self.generateAllPaymentsAndAskPayments();
					}.bind(this));
				}.bind(this)).fail(function(err){
					_scope.$apply(function(){ _self.getCaisseOnlinePaymentsLaunched = false; });
				 sweetAlert('', 'Impossible de se connecter à Caisse Online, veuillez réessayer plus tard !', 'error');
			 }).error(function(err){
				 _scope.$apply(function(){ _self.getCaisseOnlinePaymentsLaunched = false; });
				 sweetAlert('', 'Impossible de se connecter à Caisse Online, veuillez réessayer plus tard !', 'error');
			 });
		 }
		}

		CustomerManager.prototype.updateCustomer = function(customer){
			this.customer = customer;

			this.customer.lastName = $filter('htmlSpecialDecode')(this.customer.lastName);
			this.customer.firstName = $filter('htmlSpecialDecode')(this.customer.firstName);
			this.customer.company = $filter('htmlSpecialDecode')(this.customer.company);

			this.modelCustomer = JSON.parse(JSON.stringify(this.customer));
			this.modelCustomer.company = this.htmlSpecialDecode(this.customer.company);
			this.modelCustomer.privateNotes = this.htmlSpecialDecode(this.customer.privateNotes);
			this.isNotWpUser = !customer.isWpUser;
		}


		CustomerManager.prototype.returnAddPayment = function(customer, oldId){
			var copyCustomer = JSON.parse(JSON.stringify(customer));
			_scope.backendCtrl.updateCustomer(copyCustomer, oldId);
    }

		CustomerManager.prototype.getLastModificationDateBooking = function(){
			var lastModificationDateBooking = null;
			if(this.bookings.length > 0){
				for(var i = 0; i < this.bookings.length; i++){
					var booking = this.bookings[i];
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
		CustomerManager.prototype.launchUpdateBackend = function(){
			if(this.timeoutUpdateBackend){
				clearTimeout(this.timeoutUpdateBackend);
			}
			this.timeoutUpdateBackend = setTimeout(function(){
				this.updateBackend();
			}.bind(this), 15000);
		}

		/**
		 *
		 */
		CustomerManager.prototype.updateBackend = function(){
			if(!this.updateBackendLaunched && this.opened && this.customer != null){
				this.updateBackendLaunched = true;
				var lastModificationDateBooking = $filter('formatDateTime')(this.getLastModificationDateBooking());
				var data = {
					action:'updateBackendCustomer',
					lastModificationDateBooking:JSON.stringify(lastModificationDateBooking),
					idCustomer:JSON.stringify(this.customer.ID),
					lastIdLogNotifications:JSON.stringify(this.lastIdLogNotifications),
					lastIdEmailCustomer:JSON.stringify(this.lastIdEmailCustomer)
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						var bookings = data.bookings;
						for(var i = 0; i < bookings.length; i++){
							var booking = bookings[i];
							if(booking.idCustomer == _self.customer.ID){
								var arrayOldBookings = booking.linkOldBookings.split(',');
								for(var j = 0; j < arrayOldBookings.length; j++){
									_self.removeBookingById(arrayOldBookings[j]);
								}
								_self.updateBooking(booking);
							}
						}
						if(data.logNotifications.length > 0){
							_self.logNotifications = data.logNotifications.concat(_self.logNotifications);
						}
						if(data.emailsCustomer.length > 0){
							_self.emailsCustomer = data.emailsCustomer.concat(_self.emailsCustomer);
						}
						if(data.logNotifications.length > 0 || data.emailsCustomer.length > 0){
							_self.generateHistorics();
						}
						_self.updateBackendLaunched = false;
						_self.launchUpdateBackend();
					});
				}).fail(function(err){
					_scope.$apply(function(){ _self.updateBackendLaunched = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					_scope.$apply(function(){ _self.updateBackendLaunched = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
			else {
				_self.launchUpdateBackend();
			}
		}


		/**
		 *
		 */
		CustomerManager.prototype.calculateBookingPaymentState = function(booking){
			if(!this.getCaisseOnlinePaymentsLaunched && this.isSynchronizedCaisseOnline()){
				this.getCaisseOnlinePaymentsLaunched = true;
				var data = {
					action:'calculateBookingPaymentState',
					idBooking:JSON.stringify(booking.id)
				}
				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						if(typeof data === 'string'){
							sweetAlert('', data, 'error');
						}
						else {
							_self.updateBooking(data.booking);
						}
						_self.getCaisseOnlinePaymentsLaunched = false;
					}.bind(this));
				}).fail(function(err){
					_scope.$apply(function(){ _self.getCaisseOnlinePaymentsLaunched = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					_scope.$apply(function(){ _self.getCaisseOnlinePaymentsLaunched = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		CustomerManager.prototype.forceProcessEmails = function(){
			if(!this.launchEditCustomer){
				this.launchEditCustomer = true;
				var data = {
					action:'forceProcessEmails'
				}

				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						_self.launchEditCustomer = false;
						sweetAlert('', 'Ok', 'success');
						_self.launchUpdateBackend();
					});
				}).fail(function(err){
					_scope.$apply(function(){ _self.launchEditCustomer = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					_scope.$apply(function(){ _self.launchEditCustomer = false; });
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		CustomerManager.prototype.deleteEmailCustomerAction = function(idEmailCustomer){
			if(!this.launchEditCustomer){
				sweetAlert({
				  title: 'Supprimer email ?',
				  text: 'Voulez-vous supprimer cette ligne d\'email',
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonColor: "#DD6B55",
				  confirmButtonText: 'Oui',
				  cancelButtonText: 'Annuler',
				  closeOnConfirm: true,
				  html: false
				}, function(){
					_scope.$apply(function(){ _self.launchEditCustomer = true; });
					var data = {
						action:'deleteEmailCustomer',
						idEmailCustomer:JSON.stringify(idEmailCustomer)
					}

					jQuery.post(_ajaxUrl, data, function(data) {
						_scope.$apply(function(){
							_self.launchEditCustomer = false;
							sweetAlert('', 'Ok', 'success');

							var emailsCustomer = [];
							for(var i = 0; i < _self.emailsCustomer.length; i++){
								var emailCustomer = _self.emailsCustomer[i];
								if(emailCustomer.id != idEmailCustomer){
									emailsCustomer.push(emailCustomer);
								}
							}
							_self.emailsCustomer = emailsCustomer;
							_self.generateHistorics();
						});
					}).fail(function(err){
						_scope.$apply(function(){ _self.launchEditCustomer = false; });
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					}).error(function(err){
						_scope.$apply(function(){ _self.launchEditCustomer = false; });
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					});
				});
			}
		}

		CustomerManager.prototype.payBookingsCaisseOnline = function(){
			if(!this.getCaisseOnlinePaymentsLaunched){
				sweetAlert({
					title: 'Encaisser plusieurs réservations ?',
					text: 'Voulez-vous envoyer toutes les réservations non encaissées sur la caisse ?',
					type: "warning",
					showCancelButton: true,
					confirmButtonColor: "#DD6B55",
					confirmButtonText: 'Oui',
					cancelButtonText: 'Annuler',
					closeOnConfirm: true,
					html: true
				}, function(){
					_scope.$apply(function(){
						this.getCaisseOnlinePaymentsLaunched = true;

						var data = {
							action:'payBookingsCaisseOnline',
							idCustomer: JSON.stringify(this.customer.ID)
						}

						jQuery.post(_ajaxUrl, data, function(data) {
							_scope.$apply(function(){
								_self.getCaisseOnlinePaymentsLaunched = false;
								sweetAlert('', data, ((data=='Les réservations ont été envoyée sur la caisse !')?'success':'error'));
							});
						}.bind(this)).fail(function(err){
							_scope.$apply(function(){ _self.getCaisseOnlinePaymentsLaunched = false; });
							sweetAlert('', 'Impossible de se connecter à Caisse Online, veuillez réessayer plus tard !', 'error');
						}).error(function(err){
							_scope.$apply(function(){ _self.getCaisseOnlinePaymentsLaunched = false; });
							sweetAlert('', 'Impossible de se connecter à Caisse Online, veuillez réessayer plus tard !', 'error');
						});
					}.bind(this));
				}.bind(this));
			}
		}


		return CustomerManager;
	}

	angular.module('resa_app').factory('CustomerManager', CustomerManagerFactory);
}());
