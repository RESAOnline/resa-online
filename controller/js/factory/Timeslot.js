"use strict";

(function(){

  var CAPACITY_MEMBERS = 0;
	var CAPACITY_EQUIPMENTS = 1;
	var CAPACITY_FIXED = 1;

  var createDate = function(date, stringTime){
    var split = stringTime.split(':');
    var date = new Date(date.getTime());
    date.setHours(split[0] * 1);
    date.setMinutes(split[1] * 1);
    date.setSeconds(split[2] * 1);
    date.setMilliseconds(0);
    return date;
  }

  function TimeslotFactory(){

    var Timeslot = function(timeslotJSON, date){
      this.id = timeslotJSON.id;
      this.date = date;
      this.dateStart = createDate(date, timeslotJSON.startTime);
      this.dateEnd = createDate(date, timeslotJSON.endTime);
      if(this.dateEnd < this.dateStart){
        this.dateEnd.setDate(this.dateEnd.getDate() + 1);
      }
      this.startTime = timeslotJSON.startTime;
      this.endTime = timeslotJSON.endTime;
      this.capacity = timeslotJSON.capacity;
      this.typeCapacity = timeslotJSON.typeCapacity;
      this.noStaff = timeslotJSON.noStaff;
      this.exclusive = timeslotJSON.exclusive;
      this.membersExclusive = timeslotJSON.membersExclusive;
      this.activateExclusiveFixedCapacity = timeslotJSON.activateExclusiveFixedCapacity;
      this.maxExclusiveFixedCapacity = timeslotJSON.maxExclusiveFixedCapacity;
      this.overCapacity = timeslotJSON.overCapacity;
      this.display_remaining_position_overcapacity = timeslotJSON.display_remaining_position_overcapacity;
      this.noEnd = timeslotJSON.noEnd;
      this.isCustom = timeslotJSON.isCustom;
      this.idsServicePrices = timeslotJSON.idsServicePrices;
      this.idMention = timeslotJSON.idMention;
      this.maxAppointments = timeslotJSON.maxAppointments;
      this.capacityMembers = timeslotJSON.capacityMembers;
      this.idParameter = timeslotJSON.idParameter;
      this.usedCapacity = timeslotJSON.usedCapacity;
      this.numberOfAppointmentsSaved = timeslotJSON.numberOfAppointmentsSaved;
      this.members = timeslotJSON.members;
      this.membersUsed = timeslotJSON.membersUsed;
      this.equipments = timeslotJSON.equipments;
      this.capacityEquipments = timeslotJSON.capacityEquipments;
      this.equipmentsActivated = timeslotJSON.equipmentsActivated;
    }

    Timeslot.prototype.getClass = function(dateStart, dateEnd, selectedNumber){
      var backgroundColorClass = '';
      if(this.isCustom){
        backgroundColorClass = 'custom';
      }
      else if(this.isSameDates(dateStart, dateEnd)){
        backgroundColorClass = 'active';
      }
      else if(this.getCapacity() <= 0){
        backgroundColorClass = 'full';
      }
      else if(this.getCapacity() - selectedNumber < 0){
        backgroundColorClass = 'almost_full';
      }
      return backgroundColorClass;
    }

    Timeslot.prototype.isSameDates = function(dateStart, dateEnd){
      return this.dateStart.getTime() == dateStart.getTime() && this.dateEnd.getTime() == dateEnd.getTime();
    }

    Timeslot.prototype.getCapacity = function(){
      var capacity = this.capacity;
      if(this.typeCapacity == CAPACITY_MEMBERS) capacity = this.capacityMembers;
      if(this.equipmentsActivated && this.capacityEquipments != -1){
        capacity = Math.min(capacity, this.capacityEquipments);
      }
      if((this.exclusive || this.membersExclusive) && this.numberOfAppointmentsSaved >= this.maxAppointments)
        return 0;
      if(this.activateExclusiveFixedCapacity || this.membersExclusive)
        return this.maxExclusiveFixedCapacity;
      else return capacity - this.usedCapacity;
    }

    Timeslot.prototype.getNumberAppointmentsPossible = function(){
      var value = 0;
      if(this.exclusive || this.membersExclusive){
        value = this.maxAppointments - this.numberOfAppointmentsSaved;
      }
      return value;
    }

    Timeslot.prototype.getCalculateCapacity = function(serviceParameters){
      var capacity = this.getCapacity();
      if(this.isSameDates(serviceParameters.dateStart, serviceParameters.dateEnd)){
        capacity -= serviceParameters.getNumber();
      }
      return capacity;
    }

    Timeslot.prototype.getMaxCapacity = function(){
      var capacity = this.capacity;
      if(this.typeCapacity == CAPACITY_MEMBERS) capacity = this.capacityMembers;
      if(this.equipmentsActivated && this.capacityEquipments != -1){
        capacity = Math.min(capacity, this.capacityEquipments);
      }
      return capacity;
    }

    Timeslot.prototype.isInMorning = function(){
      var midday = new Date(this.dateStart);
      midday.setHours(12);
      midday.setMinutes(0);
      midday.setSeconds(0);
      return this.dateStart.getTime() < midday.getTime();
    }

    Timeslot.prototype.getDuration = function(){
      var dateStart = new Date(this.dateStart);
      var dateEnd = new Date(this.dateEnd);

      var diff = {sec:0, min:0, hour:0, day:0};
      var tmp = dateEnd.getTime() - dateStart.getTime();

      tmp = Math.floor(tmp/1000);
      diff.sec = tmp % 60;

      tmp = Math.floor((tmp-diff.sec)/60);
      diff.min = tmp % 60;

      tmp = Math.floor((tmp-diff.min)/60);
      diff.hour = tmp % 24;

      tmp = Math.floor((tmp-diff.hour)/24);
      diff.day = tmp;

      var date = new Date();
      date.setHours(diff.hour);
      date.setMinutes(diff.min);
      return new Date(date);
    }

    Timeslot.prototype.numberExclusiveTimeslot = function(){
      if(this.maxAppointments > 1 && ((this.exclusive && this.activateExclusiveFixedCapacity) || this.membersExclusive)){
        return this.maxAppointments - this.numberOfAppointmentsSaved;
      }
      return 0;
    }

  	return Timeslot;
	}

	angular.module('resa_app').factory('Timeslot', TimeslotFactory);
}());
