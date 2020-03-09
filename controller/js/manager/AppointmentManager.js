"use strict";

(function(){

	function AppointmentManagerFactory($log, $filter){

		var AppointmentManager = function(){

		}

		AppointmentManager.prototype.getAppointmentByPresentation = function(appointments, presentation){
			for(var i = 0; i < appointments.length; i++){
				var appointment = appointments[i];
				if(appointment.presentation == presentation)
					return appointment;
			}
			return null;
		}

		AppointmentManager.prototype.formatNoteInfoCalendar = function(id, note){
			return '<div id="info'+ id +'">' + note +'</div>';
		}

		/**
		 * ok les variables sont pas super mais j'avais pas envi !
		 */
		AppointmentManager.prototype.getInfoByNote = function(array, note){
			for(var i = 0; i < array.length; i++){
				var value = array[i];
				if(note.indexOf('<div id="info'+ value.id +'">') != -1){
					return value;
				}
			}
			return null;
		}

		AppointmentManager.prototype.getServiceConstraint = function(array, title){
			for(var i = 0; i < array.length; i++){
				var value = array[i];
				if(value.title == title)
					return value;
			}
			return null;
		}

		AppointmentManager.prototype.getMemberConstraint = function(array, title){
			for(var i = 0; i < array.length; i++){
				var value = array[i];
				if(value.title == title)
					return value;
			}
			return null;
		}


		AppointmentManager.prototype.getAppointmentNumber = function(appointment){
			var number = 0;
			for(var i = 0; i < appointment.appointmentNumberPrices.length; i++){
				var numberPrice = appointment.appointmentNumberPrices[i];
				number += numberPrice.number;
			}
			return number;
		}

		AppointmentManager.prototype.getAppointmentPrice = function(appointment, service){
			var price = 0;
			for(var i = 0; i < appointment.appointmentNumberPrices.length; i++){
				var numberPrice = appointment.appointmentNumberPrices[i];
				var servicePrice = this.getPriceAppointment(numberPrice.idPrice, service);
				price += numberPrice.number * servicePrice.price;
			}
			return price;
		}

		AppointmentManager.prototype.getServiceAppointment = function(appointment, allServices){
			var idService = appointment.idService;
			for(var i = 0; i < allServices.length; i++){
				var service = allServices[i];
				if(service.id == idService)
					return service;
			}
			return null;
		}

		AppointmentManager.prototype.getPriceAppointment = function(idPrice, service){
			for(var i = 0; i < service.servicePrices.length; i++){
				var servicePrice = service.servicePrices[i];
				if(servicePrice.id == idPrice)
					return servicePrice;
			}
			return null;
		}

		/**
		 * return members associated to the appointment
		 */
		AppointmentManager.prototype.getMembers = function(appointment, allMembers){
			var members = [];
			for(var i = 0; i < appointment.appointmentMembers.length; i++){
				var appointmentMember = appointment.appointmentMembers[i];
				var found = false;
				var j = 0;
				while(!found && j < allMembers.length){
					var member = allMembers[j];
					if(appointmentMember.idMember == member.id){
						members.push(member);
						found = true;
					}
					j++;
				}
			}
			return members;
		}

		AppointmentManager.prototype.addInterval = function(intervals, startDate, endDate, state){
			var found = false;
			if(intervals==null)
				intervals = [];
			for(var i = 0; i < intervals.length; i++){
				var interval = intervals[i].interval;
				if(interval.startDate == startDate && interval.endDate && interval.state == state){
					found = true;
					intervals[i].number += 1;
				}
			}
			if(!found){
				intervals.push({
					interval: {startDate: startDate, endDate: endDate},
					number: 1,
					state: state
				})
			}
			return intervals;
		}

		AppointmentManager.prototype.isSameDay = function(appointment, startDate, endDate){
			var startDate = $filter('formatDateTime')(startDate, 'dd-MM-yyyy');
			var endDate = $filter('formatDateTime')(endDate, 'dd-MM-yyyy');
			var appointmentStart = $filter('formatDateTime')(appointment.startDate, 'dd-MM-yyyy');
			var appointmentEnd = $filter('formatDateTime')(appointment.endDate, 'dd-MM-yyyy');
			return appointmentStart == startDate && appointmentEnd == endDate;
		}

		AppointmentManager.prototype.timeslotEqualsInService = function(service, startDate, endDate){
			var startTime = $filter('formatDateTime')(startDate, 'HH:mm:ss');
			var endTime = $filter('formatDateTime')(endDate, 'HH:mm:ss');
			for(var i = 0; i < service.serviceAvailabilities.length; i++){
				var serviceAvailability = service.serviceAvailabilities[i];
				for(var j = 0; j < serviceAvailability.timeslots.length; j++){
					var timeslot = serviceAvailability.timeslots[j];
					if(timeslot.startTime == startTime && timeslot.endTime == endTime){
						return true;
					}
				}
			}
			return false;
		}


		return AppointmentManager;
	}

	angular.module('resa_app').factory('AppointmentManager', AppointmentManagerFactory);
}());
