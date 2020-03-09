"use strict";

(function(){
	var _self = null;
	var _scope = null;

	function DisplayBookingsManagerFactory($log, $filter, FunctionsManager){
		var DisplayBookingsManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(DisplayBookingsManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.bookings = null
      this.settings = null;

      _scope = scope;
      _self = this;

      _scope.backendCtrl.displayBookingsCtrl = this;
		}

		DisplayBookingsManager.prototype.initialize = function(bookings, settings) {
      this.bookings = bookings;
      this.settings = settings;
		}

    DisplayBookingsManager.prototype.close = function() {
      this.bookings = null;
      this.opened = false;
    }

		DisplayBookingsManager.prototype.noBookings = function(){
			return this.bookings == null;
		}

		DisplayBookingsManager.prototype.updateBooking = function(newBooking, oldBooking){
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

    DisplayBookingsManager.prototype.justReplaceBooking = function(booking){
			for(var i = 0; i < this.bookings.length; i++){
				if(this.bookings[i].id == booking.id){
  			  booking = this.addBookingAnnotations(booking);
					this.bookings[i] = booking;
				}
			}
		}

		DisplayBookingsManager.prototype.addBookingAnnotations = function(booking){
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

    /**
		 * get booking by id
		 */
		DisplayBookingsManager.prototype.getBookingById = function(idBooking){
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				if(booking.id == idBooking){
					return booking;
				}
			}
			return null;
		}

		DisplayBookingsManager.prototype.removeBookingById = function(idBooking){
			var newBookings = [];
			for(var i = 0; i < this.bookings.length; i++){
				var booking = this.bookings[i];
				if(booking.id != idBooking){
					newBookings.push(booking);
				}
			}
			this.bookings = newBookings;
		}

		return DisplayBookingsManager;
	}

	angular.module('resa_app').factory('DisplayBookingsManager', DisplayBookingsManagerFactory);
}());
