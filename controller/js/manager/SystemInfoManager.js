"use strict";

(function(){
	function SystemInfoManagerFactory($log, $window, $document){

    var _self = null;
    var _scope = null;
		var _ajaxUrl = null;

		function SystemInfoManager(scope){
      _self = this;
      _scope = scope;

			this.extractForNewIntall = false;
			this.extractInitData = false;
			this.deleteAllBookingsLaunched = false;
			this.deleteWpRESACustomers = false;
			this.clearAllDataLaunched = false;

			this.fileImport = '';
			this.loadOptionsImport = true;
			this.loadCustomersImport = false;
			this.importationProgress = false;

			this.allCustomersImport = [];
			this.allServicesImport = [];
			this.allStaticGroupsImport = [];
			this.allMembersImport = [];
			this.allReductionsImport = [];
			this.allBookingsImport = [];
			this.allGroupsImport = [];
			this.allInfoCalendarsImport = [];
			this.allServiceContraints = [];
			this.allOptions = {};
			this.maxToSynchronize = 0;
			this.currentSynchronized = 0;
			this.nextCurrentSynchronized = 0
			this.timeRequest = 0;
			this.lastNumberProcessData = 5;

			this.mapOldIdClientNewIdClient = [];
			this.mapOldServiceNewService = [];
			this.mapOldIdMemberNewIdMember = [];
			this.mapOldIdReductionNewIdReduction = [];
			this.mapOldIdBookingNewIdBooking = [];

			this.launchForceUpdateDatabase = false;
			this.launchCustomersDiagnostic = false;
			this.launchSetTokensValidations = false;
			this.statistics = null;
		}

		SystemInfoManager.prototype.initialize = function(ajaxurl){
			_ajaxUrl = ajaxurl;
			this.getStatistics();
		}

		SystemInfoManager.prototype.getStatistics = function(){
			var data = {
				action: 'getStatistics'
			}
			jQuery.post(_ajaxUrl, data, function(data) {
				_scope.$apply(function(){
					data = JSON.parse(data);
					_self.statistics = data.data;
					_self.statistics.total = Number(_self.statistics.nbBookingsOnline) + Number(_self.statistics.nbBookingsBackend);
				});
			}).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.extractAllData = function(){
			var data = {
				action: 'ExtractAllData',
				extractForNewIntall: JSON.stringify(this.extractForNewIntall)
			}
			jQuery.post(_ajaxUrl, data, function(data) {
				$window.location.href = data;
			}).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.launchDeleteAllBookings = function(dialogTexts){
			if(!this.deleteAllBookingsLaunched){
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
					_scope.$apply(function(){ _self.deleteAllBookingsLaunched = true; });
					var data = {
						action: 'DeleteAllBookings'
					}
					jQuery.post(_ajaxUrl, data, function(data) {
						_scope.$apply(function(){
							_self.deleteAllBookingsLaunched = false;
							if(data == 'OK'){
								sweetAlert({
									title: 'Suppression OK !',
									type: "success",
									timer: 2000,
									showConfirmButton: true });
							}
						});
					}).fail(function(err){
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					}).error(function(err){
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
					});
				}.bind(this));
			}
		}

		/******************************
		 ******************************
		 *****************************/
	 	SystemInfoManager.prototype.launchClearAllData = function(dialogTexts){
			if(this.clearAllDataLaunched) return false;
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
				_scope.$apply(function(){
	 				_self.clearAllData();
				});
	 		}.bind(this));
	 	}

	 	SystemInfoManager.prototype.clearAllData = function(){
			this.clearAllDataLaunched = true;
	 		var data = {
	 			action: 'ClearAllData',
				deleteWpRESACustomers: this.deleteWpRESACustomers
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
	 			sweetAlert({
					title: data,
					type: "success",
					timer: 80000,
					showConfirmButton: true
				});
				_scope.$apply(function(){
					_self.clearAllDataLaunched = false;
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
	 	}

		/**********************************************************************
		*************************** IMPORT ************************************
		**********************************************************************/

		SystemInfoManager.prototype.importAllData = function(){
			var formData = new FormData();
			formData.append('action', 'ImportAllDataAJAX');
			formData.append('file', this.fileImport);
			formData.append('loadOptions', this.loadOptionsImport);
			formData.append('loadCustomers', this.loadCustomersImport);
			this.importationProgress = true;

			jQuery.ajax({
				url:_ajaxUrl,
				type: 'POST',
				data: formData,
				cache: false,
        contentType: false,
        processData: false,
				success : function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						_self.allCustomersImport = data.customers.sort(function(obj1, obj2){return obj1.ID-obj2.ID});
			 			_self.allServicesImport = data.services.sort(function(obj1, obj2){return obj1.id-obj2.id});
			 			_self.allStaticGroupsImport = data.staticGroups.sort(function(obj1, obj2){return obj1.id-obj2.id});
			 			_self.allMembersImport = data.members.sort(function(obj1, obj2){return obj1.id-obj2.id});
			 			_self.allReductionsImport = data.reductions.sort(function(obj1, obj2){return obj1.id-obj2.id});
			 			_self.allBookingsImport = data.bookings.sort(function(obj1, obj2){return obj1.id-obj2.id});
			 			_self.allGroupsImport = data.groups.sort(function(obj1, obj2){return obj1.id-obj2.id});
			 			_self.allInfoCalendarsImport = data.infoCalendars.sort(function(obj1, obj2){return obj1.id-obj2.id});
			 			_self.allServiceContraints = data.serviceConstraints.sort(function(obj1, obj2){return obj1.id-obj2.id});
						_self.allOptions = data.options;
						_self.maxToSynchronize = _self.allCustomersImport.length +
							_self.allServicesImport.length +
							_self.allStaticGroupsImport.length +
							_self.allMembersImport.length +
							_self.allReductionsImport.length +
							_self.allBookingsImport.length +
							_self.allGroupsImport.length +
							_self.allInfoCalendarsImport.length +
							_self.allServiceContraints.length + 1;
						_self.currentSynchronized = 0;
						_self.progressImportation();
					});

					//this.importationProgress = false;
       	},
				error : function(data){
					alert('error');
					_scope.$apply(function(){
						 _self.importationProgress = false;
					 });
				}
			});
		}

		SystemInfoManager.prototype.importPurcent = function(){
			if(this.currentSynchronized == 0) return 0;
			return Math.floor((this.currentSynchronized / this.maxToSynchronize) * 100);
		}

		SystemInfoManager.prototype.remainingTime = function(){
			var numberToProcess = (this.maxToSynchronize - this.currentSynchronized) / this.lastNumberProcessData;
			var minutes = Math.floor(( numberToProcess * this.timeRequest) / 60);
			if(minutes <= 0){
				return Math.floor(numberToProcess * this.timeRequest) + 's';
			}
			return minutes + 'min';
		}

		SystemInfoManager.prototype.launchSave = function(index, number, array, callback, progress){
			var maxIndex = index + number;
			if(maxIndex >= array.length){
				maxIndex = array.length;
				progress = true;
			}
			var subArray = array.slice(index, maxIndex);
			callback(subArray, progress);
			if(maxIndex != array.length && !progress){
				this.launchSave(maxIndex, number, array, callback, true);
			}
		}

		SystemInfoManager.prototype.progressImportation = function(){
			if(!this.importationProgress) return false;
			if(this.currentSynchronized < this.allCustomersImport.length){
				var index = this.currentSynchronized;
				this.launchSave(index, 10, this.allCustomersImport, this.saveCustomers, true);
			}
			else if(this.currentSynchronized < this.allCustomersImport.length + this.allServicesImport.length){
				var index = this.currentSynchronized - this.allCustomersImport.length;
				this.launchSave(index, this.allServicesImport.length,  this.allServicesImport, this.saveServices, true);
			}
			else if(this.currentSynchronized < this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length){
				var index = this.currentSynchronized - (this.allCustomersImport.length + this.allServicesImport.length);
				this.launchSave(index, 10,  this.allStaticGroupsImport, this.saveStaticGroups, true);
			}
			else if(this.currentSynchronized < this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length){
				var index = this.currentSynchronized - (this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length);
				this.launchSave(index, this.allMembersImport.length, this.allMembersImport, this.saveMembers, true);
			}
			else if(this.currentSynchronized < this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length + this.allReductionsImport.length){
				var index = this.currentSynchronized - (this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length);
				this.launchSave(index, this.allReductionsImport.length, this.allReductionsImport, this.saveReductions, true);
			}
			else if(this.currentSynchronized < this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length + this.allReductionsImport.length + this.allBookingsImport.length){
				var index = this.currentSynchronized - (this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length  + this.allReductionsImport.length);
				this.launchSave(index, 10, this.allBookingsImport, this.saveBookings, true);
			}
			else if(this.currentSynchronized < this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length + this.allReductionsImport.length + this.allBookingsImport.length + this.allGroupsImport.length){
				var index = this.currentSynchronized - (this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length  + this.allReductionsImport.length + this.allBookingsImport.length);
				this.launchSave(index, 10, this.allGroupsImport, this.saveGroups, true);
			}
			else if(this.currentSynchronized < this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length + this.allReductionsImport.length + this.allBookingsImport.length + this.allGroupsImport.length + this.allInfoCalendarsImport.length){
				var index = this.currentSynchronized - (this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length  + this.allReductionsImport.length + this.allBookingsImport.length + this.allGroupsImport.length);
				this.launchSave(index, 10, this.allInfoCalendarsImport, this.saveInfoCalendars, true);
			}
			else if(this.currentSynchronized < this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length + this.allReductionsImport.length + this.allBookingsImport.length + this.allGroupsImport.length + this.allInfoCalendarsImport.length + this.allServiceContraints.length){
				var index = this.currentSynchronized - (this.allCustomersImport.length + this.allServicesImport.length + this.allStaticGroupsImport.length + this.allMembersImport.length  + this.allReductionsImport.length + this.allBookingsImport.length + this.allGroupsImport.length + this.allInfoCalendarsImport.length);
				this.launchSave(index, 10, this.allServiceContraints, this.saveServiceConstraints, true);
			}
			else if(this.currentSynchronized != this.maxToSynchronize){
				this.saveOptions();
			}
		}

		SystemInfoManager.prototype.saveCustomers = function(customers, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveCustomers',
				customers: JSON.stringify(customers)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date();
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = customers.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.mapOldIdClientNewIdClient = _self.mapOldIdClientNewIdClient.concat(data);
					_self.currentSynchronized += customers.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveServices = function(services, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveServices',
				services: JSON.stringify(services)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date()
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = services.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.mapOldServiceNewService = _self.mapOldServiceNewService.concat(data);
					_self.currentSynchronized += services.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveStaticGroups = function(staticGroups, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveStaticGroups',
				staticGroups: JSON.stringify(staticGroups),
				mapOldServiceNewService: JSON.stringify(_self.mapOldServiceNewService)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date()
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = staticGroups.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.currentSynchronized += staticGroups.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveMembers = function(members, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveMembers',
				members: JSON.stringify(members),
				mapOldServiceNewService: JSON.stringify(_self.mapOldServiceNewService)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date()
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = members.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.mapOldIdMemberNewIdMember = _self.mapOldIdMemberNewIdMember.concat(data);
					_self.currentSynchronized += members.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveReductions = function(reductions, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveReductions',
				reductions: JSON.stringify(reductions),
				mapOldServiceNewService: JSON.stringify(_self.mapOldServiceNewService)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date()
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = reductions.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.mapOldIdReductionNewIdReduction = _self.mapOldIdReductionNewIdReduction.concat(data);
					_self.currentSynchronized += reductions.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveBookings = function(bookings, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveBookings',
				bookings: JSON.stringify(bookings),
				mapOldIdClientNewIdClient: JSON.stringify(_self.mapOldIdClientNewIdClient),
				mapOldIdMemberNewIdMember: JSON.stringify(_self.mapOldIdMemberNewIdMember),
				mapOldIdReductionNewIdReduction: JSON.stringify(_self.mapOldIdReductionNewIdReduction),
				mapOldServiceNewService: JSON.stringify(_self.mapOldServiceNewService),
				mapOldIdBookingNewIdBooking: JSON.stringify(_self.mapOldIdBookingNewIdBooking),
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date()
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = bookings.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.mapOldIdBookingNewIdBooking = data;
					_self.currentSynchronized += bookings.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveGroups = function(groups, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveGroups',
				groups: JSON.stringify(groups),
				mapOldServiceNewService: JSON.stringify(_self.mapOldServiceNewService)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date()
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = groups.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.currentSynchronized += groups.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveInfoCalendars = function(infoCalendars, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveInfoCalendars',
				infoCalendars: JSON.stringify(infoCalendars)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date()
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = infoCalendars.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.currentSynchronized += infoCalendars.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveServiceConstraints = function(serviceConstraints, progress){
			var beginDate = new Date();
			var data = {
	 			action: 'SaveServiceConstraints',
				serviceConstraints: JSON.stringify(serviceConstraints),
				mapOldServiceNewService: JSON.stringify(_self.mapOldServiceNewService)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				var endDate = new Date()
				_self.timeRequest = ((endDate.getTime() - beginDate.getTime())/1000);
				_self.lastNumberProcessData = serviceConstraints.length;
				data = JSON.parse(data);
				_scope.$apply(function(){
					_self.currentSynchronized += serviceConstraints.length;
					if(progress) _self.progressImportation();
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.saveOptions = function(){
			var data = {
	 			action: 'SaveOptions',
				options: JSON.stringify(this.allOptions)
	 		}
	 		jQuery.post(_ajaxUrl, data, function(data) {
				//data = JSON.parse(data);
				_scope.$apply(function(){
					_self.currentSynchronized++;
					sweetAlert({
						title: data,
						type: "success",
						timer: 80000,
						showConfirmButton: true
					});
				});
	 		}.bind(this)).fail(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			}).error(function(err){
				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
			});
		}

		SystemInfoManager.prototype.forceLastUpdateDatabase = function(){
			if(!this.launchForceUpdateDatabase){
				this.launchForceUpdateDatabase = true;
				var data = {
		 			action: 'ForceLastUpdateDatabase'
		 		}
		 		jQuery.post(_ajaxUrl, data, function(data) {
					sweetAlert({
						title: 'OK',
						type: "success",
						timer: 80000,
						showConfirmButton: true
					});
					_scope.$apply(function(){
						_self.launchForceUpdateDatabase = false;
					});
		 		}.bind(this)).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}

		/**
		 *
		 */
		SystemInfoManager.prototype.customersDiagnostic = function(){
			if(!this.launchCustomersDiagnostic){
				this.launchCustomersDiagnostic = true;
				var data = {
		 			action: 'CustomersDiagnostic'
		 		}
		 		jQuery.post(_ajaxUrl, data, function(data) {
					data = JSON.parse(data);
					sweetAlert({
						title: 'OK',
						type: "success",
						timer: 80000,
						showConfirmButton: true
					});
					_scope.$apply(function(){
						_self.launchCustomersDiagnostic = false;
						_self.customersDiagnosticResults = data;
					});
		 		}.bind(this)).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
		}


		SystemInfoManager.prototype.resolveCustomerConflict = function(customer){
			if(!this.launchCustomersDiagnostic){
				var idsBookings = prompt('Quelles sont les ids des réservations appartement au client ' +
					customer.lastName + ' ' + customer.firstName + '(séparée par une virgule) ?');
				if(idsBookings != null){
					this.launchCustomersDiagnostic = true;
					var data = {
			 			action: 'ResolveCustomerConflict',
						idCustomer:JSON.stringify(customer.ID),
						idsBookings:JSON.stringify(idsBookings)
			 		}
			 		jQuery.post(_ajaxUrl, data, function(data) {
						data = JSON.parse(data);
						if(data.result == 'ok'){
							sweetAlert({
								title: 'OK',
								type: "success",
								timer: 80000,
								showConfirmButton: true
							});
							var newCustomersDiagnosticResults = [];
							for(var i = 0; i < _self.customersDiagnosticResults.length; i++){
								var diagnostic = _self.customersDiagnosticResults[i];
								if(diagnostic[0].ID != customer.ID){
									newCustomersDiagnosticResults.push(diagnostic);
								}
							}
							_self.customersDiagnosticResults = newCustomersDiagnosticResults;
						}
						else {
							sweetAlert({
								title: data.message,
								type: "error",
								timer: 80000,
								showConfirmButton: true
							});
						}
						_scope.$apply(function(){
							_self.launchCustomersDiagnostic = false;
						});
			 		}.bind(this)).fail(function(err){
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){	_self.launchCustomersDiagnostic = false;	});
					}).error(function(err){
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){	_self.launchCustomersDiagnostic = false;	});
					});
				}
			}
		}

		SystemInfoManager.prototype.setTokensValidations = function(){
			if(!this.launchSetTokensValidations){
					this.launchSetTokensValidations = true;
					var data = {
			 			action: 'SetTokensValidations'
			 		}
			 		jQuery.post(_ajaxUrl, data, function(data) {
						data = JSON.parse(data);
						if(data.result == 'ok'){
							sweetAlert({
								title: 'OK - Mise à jour : ' + data.updated,
								type: "success",
								timer: 80000,
								showConfirmButton: true
							});
						}
						else {
							sweetAlert({
								title: data.message,
								type: "error",
								timer: 80000,
								showConfirmButton: true
							});
						}
						_scope.$apply(function(){ _self.launchSetTokensValidations = false;	});
			 		}.bind(this)).fail(function(err){
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){	_self.launchCustomersDiagnostic = false;	});
					}).error(function(err){
						sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						_scope.$apply(function(){	_self.launchCustomersDiagnostic = false;	});
					});
				}
			}

		return SystemInfoManager;
	}


	angular.module('resa_app').factory('SystemInfoManager', SystemInfoManagerFactory)
}());
