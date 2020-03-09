/****
 * datetimepicker
 ****/

"use strict";

(function(){
	//'../wp-content/plugins/resa-online/controller/js/directive/templates/datetimepicker.html'
	var _self;
	function DateTimePicker($log, $http, $filter, DateComputation){

		var _DateComputation = new DateComputation();

		return {
			restrict: 'E',
			template: '<div ng-include="templateUrl"></div>',
			scope: {
				displayMembers: "=",
        service: "=",
				members: "=",
				dateStart: "=",
				dateEnd: "=",
				noEnd: "=",
				idsServicePrices: "=",
				capacity: "=",
				membersUsed: "=",
				url: "=",
				servicesParameters: "=",
				traductionsText: "=",
				dateFormat:"=",
				timeFormat:"=",
				templateUrl:"=",
				idBooking:"=",
				allowInconsistencies:"=",
				frontForm:"=",
				enabledColor:'=?',
				disabledColor:'=?',
      },
			link:function (scope, element, attrs) {

				function disabled(data) {
					var actualDate = new Date();
					var date = data.date,
					mode = data.mode;
					var disabled = (mode === 'day');
					if(_DateComputation.dateIsAvailable){
						disabled = disabled && (!_DateComputation.dateIsAvailable(date, scope.service) || date < actualDate);
					}
					return disabled;
				}

				function getDayClass(data) {
					var date = data.date,
					mode = data.mode;
					if (mode === 'day') {
						if(disabled(data)) return 'resa_disabled';
						else return 'resa_enabled';
					}
					return '';
				}

				_self = scope;
				var date = new Date();
				if(scope.service != null && scope.service.startBookingDate!=null){
					date = $filter('parseDate')(scope.service.startBookingDate);
				}
				scope.model = {
					date : date,
					options: {
						dateDisabled: disabled,
						customClass: getDayClass,
						minDate: date,
						showWeeks: true
					}
				};

				scope.getTimeSlots = function(){
					return _DateComputation.getTimeSlots(scope.model.date, scope.service);
				}

				scope.$watch('service.serviceAvailabilities', function (service, oldService){
					if(oldService!=null && service != oldService){
						scope.initialize();
						scope.getTimeslotsAjax();
					}
				}, true);
				scope.$watch('servicesParameters', function (servicesParameters, old){
					if(old!=null && servicesParameters != old){
						scope.getTimeslotsAjax();
					}
				}, true);
				scope.$watch('model.date', function (date, old){
					if(old!= null && date != old){
						scope.getTimeslotsAjax();
					}
				}, true);

				scope.getDate = function(){
					return scope.model.date;
				}

				scope.translate = function(id){
					if(scope.traductionsText != null && scope.traductionsText[id]!=null)
						return scope.traductionsText[id];
					return id;
				}

				scope.initialize = function(){
					scope.timeslot = null;
					scope.timeslots = [];
					if(scope.allowInconsistencies == null){
						scope.allowInconsistencies = false;
					}

					var minDate = new Date();
					if(scope.service != null && scope.service.startBookingDate!=null){
						minDate = $filter('parseDate')(scope.service.startBookingDate);
					}
					scope.model.date = minDate;
					scope.model.options = {
						dateDisabled: disabled,
						customClass: getDayClass,
						minDate: minDate,
						showWeeks: true
					};
				}

				scope.setTimeslot = function(timeslot){
					if(scope.dateStart !=null && scope.getCapacity(timeslot) > 0){
						scope.timeslot = timeslot;
						scope.dateStart = _DateComputation.timeToDate(timeslot.startTime, new Date(scope.model.date));
						scope.dateEnd = _DateComputation.timeToDate(timeslot.endTime, new Date(scope.model.date));
						scope.noEnd = timeslot.noEnd;
						scope.idsServicePrices = timeslot.idsServicePrices;
						scope.capacity = scope.getCapacity(timeslot);
						if(timeslot.overCapacity){
							scope.capacity = 9999;
						}
						scope.membersUsed = [];
						for(var i = 0; i < timeslot.membersUsed.length; i++){
							if(timeslot.membersUsed[i].capacity > 0)
								scope.membersUsed.push(timeslot.membersUsed[i]);
						}
					}
					else {
						scope.capacity = -1;
					}
				}

				scope.isSelectedTimeslot= function(timeslot){
					if(scope.timeslot==null) return false;
					return timeslot.id == scope.timeslot.id;
				}

				scope.getCapacity = function(timeslot){
					var capacity = timeslot.capacity;
					if(!timeslot.active) capacity = timeslot.capacityMembers;
					if(scope.displayMembers) return capacity;
					else {
						if(timeslot.exclusive && timeslot.numberOfAppointmentsSaved >= timeslot.maxAppointments)
							return 0;
						else return capacity - timeslot.usedCapacity;
					}
				}

				scope.getTimeslotsAjax = function(){
					scope.loading = true;
					scope.timeslots = [];
					var ajaxURL = scope.url;
					if(ajaxURL == null){
						ajaxURL = ajaxurl;
					}
					//servicesParametersFormated
					var servicesParametersFormated = [];
					if(scope.servicesParameters!=null){
						var servicesParametersFormated = JSON.parse(JSON.stringify(scope.servicesParameters));
						for(var i = 0; i < servicesParametersFormated.length; i++){
							var serviceParameters = servicesParametersFormated[i];
							serviceParameters.dateStart = $filter('formatDateTime')(new Date(serviceParameters.dateStart));
							serviceParameters.dateEnd = $filter('formatDateTime')(new Date(serviceParameters.dateEnd));
						}
					}
					if(scope.service != null){
						var idBooking = -1;
						if(scope.idBooking != null){
							idBooking = scope.idBooking;
						}
						var data = {
							action:'getTimeslots',
							service:  JSON.stringify(scope.service),
							servicesParameters: JSON.stringify(servicesParametersFormated),
							date: '' + $filter('date')(scope.getDate(), 'dd-MM-yyyy HH:mm:ss'),
							idBooking: JSON.stringify(idBooking),
							allowInconsistencies: JSON.stringify(scope.allowInconsistencies),
							frontForm: JSON.stringify(scope.frontForm),
						}

						jQuery.post(ajaxURL, data, function(data) {
							scope.$apply(function(){
								scope.timeslots = JSON.parse(data);
								for(var i = 0; i < scope.timeslots.length; i++){
									if(scope.isSelectedTimeslot(scope.timeslots[i])){
										scope.setTimeslot(scope.timeslots[i]);
									}
								}
								if(scope.timeslot == null && scope.timeslots.length == 1){
									scope.setTimeslot(scope.timeslots[0]);
								}
								scope.loading = false;
							});
						}).fail(function(err){
							sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						}).error(function(err){
							sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
						});
					}
				}
				scope.initialize();
				if(!scope.displayMembers){
					scope.getTimeslotsAjax();
				}
			}
		}
	}

	angular.module('resa_app').directive('dateTimePicker', DateTimePicker);
}())
