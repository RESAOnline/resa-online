"use strict";

(function(){

	var resetTimeToDate = function(date){
		date.setHours(0);
		date.setMinutes(0);
		date.setSeconds(0);
		date.setMilliseconds(0);
		return date;
	}

	function DateComputationFactory($filter) {
		var DateComputation = function(){
		}

		DateComputation.prototype.dateIsAvailable = function(date, service){
			return this.algorithm(date, service).available;
		}

		DateComputation.prototype.getTimeSlots = function(date, service){
			return this.algorithm(date, service).timeslots;
		}

		DateComputation.prototype.algorithm = function(date, service){
			if(service == null) return true;
			var allAvailables = (date >= new Date(service.startBookingDate));
			if(allAvailables){
				allAvailables = false;
				for(var i = 0; i < service.serviceAvailabilities.length; i++) {
					var serviceAvailability = service.serviceAvailabilities[i];
					var dates = serviceAvailability.dates.split(',');
					var stringDate = $filter('formatDateTime')(date, 'yyyy-MM-dd');
					var available = (dates.indexOf(stringDate) > -1);
					allAvailables = allAvailables || available;
				}
			}
			return {available: allAvailables, timeslots : []};
		}

		//TODO : is copy paste of another function
		DateComputation.prototype.algorithmMember = function(date, member, serviceId){
			if(member == null && member.memberAvailabilities==null) return true;
			var available = false;
			var timeslots = [];
			for(var i = 0; i < member.memberAvailabilities.length; i++) {
				var memberAvailability = member.memberAvailabilities[i];
				var dates = memberAvailability.dates.split(',');
				var stringDate = $filter('formatDateTime')(date, 'yyyy-MM-dd');
				var available = (dates.indexOf(stringDate) > -1);
				if(available) {
					timeslots = [];
					timeslots.push(memberAvailability.startTime);
					timeslots.push(memberAvailability.endTime);
					var capacity = 0;
					for(var memberIndexService = 0; memberIndexService <memberAvailability.memberAvailabilityServices.length;  memberIndexService++){
						var memberAvailabilityService = memberAvailability.memberAvailabilityServices[memberIndexService];
						if(memberAvailabilityService && memberAvailabilityService.idService == serviceId){
							capacity += memberAvailabilityService.capacity;
						}
					}
					timeslots.push(capacity);
				}
			}
			return {available: available, timeslots : timeslots};
		}


		DateComputation.prototype.getMemberCapacity = function(date, member, serviceId, startTime, endTime){
			var timeslots = this.algorithmMember(date, member, serviceId).timeslots;
			var capacity = 0;
			if(this.timeToDate(timeslots[0]).getTime() <= this.timeToDate(startTime).getTime() && this.timeToDate(timeslots[1]).getTime()>=this.timeToDate(endTime).getTime()){
				capacity = timeslots[2];
			}
			return capacity;
		}

		DateComputation.prototype.getCapacity = function(date, members, serviceId, startTime, endTime){
			var capacity = 0;
			var membersHaveCapacity = [];
			if(members != null) {
				for(var i = 0; i < members.length; i++){
					var member = members[i];
					var localCapacity = this.getMemberCapacity(date, member, serviceId, startTime, endTime);
					if(localCapacity > 0) {
						capacity += localCapacity;
						membersHaveCapacity.push(member);
					}
				}
			}
			return {capacity: capacity, members: membersHaveCapacity};
		}


		DateComputation.prototype.timeToDate = function(time, date){
			if(date == null)
				date = new Date();

			if(time == null) return date;
			var tokens = time.split(':');
			if(tokens.length > 2){
				date.setHours(tokens[0]);
				date.setMinutes(tokens[1]);
				date.setSeconds(tokens[2]);
				date.setMilliseconds(0);
			}
			return date;
		}


		return DateComputation;
	}




	angular.module('resa_app').factory('DateComputation', DateComputationFactory);


}());
