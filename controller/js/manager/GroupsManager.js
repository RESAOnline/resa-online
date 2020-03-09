"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;
  var _skeletonGroup = null;

	function GroupsManagerFactory($filter, FunctionsManager){

		var GroupsManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(GroupsManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.rapportByServices = null;
      this.service = null;
      this.members = [];
			this.services = [];
			this.displayedMembers = [];
			this.orderByMembers = {};

      this.settings = null;
      this.participantsParameters = [];
			this.filters = {};
			this.filtersAll = {};
			this.orderByParameters = {};
      this.participants = {};
      this.newGroup = null;

			this.launchGetGroups = false;
      this.launchEditGroup = false;
      this.groupsList = [];
			this.week = [];
			this.groups = [];

      this.assignmentMembers = {idGroup:-1, members:{}, all: false};
      this.assignmentParticipants = {idGroup:-1, participants:{}, all: {}};

      _scope = scope;
      _self = this;

      _scope.backendCtrl.groupsCtrl = this;
		}

		GroupsManager.prototype.initialize = function(rapportByServices, skeletonGroup, members, services, settings, ajaxUrl) {
      this.rapportByServices = rapportByServices;
      this.members = members;
			this.services = services;
      this.settings = settings;
      _ajaxUrl = ajaxUrl;
      _skeletonGroup = skeletonGroup;

			this.orderByMembers = {by:'', reverse:false};
			this.generateWeek();
			this.getGroups();
		}

		GroupsManager.prototype.initializeUI = function() {
			this.generateDisplayedMembers();
			this.regroupParticipantsByParticipantPameters();
			this.regenerateNewGroup();
			this.generateGroupsList();
		}

		GroupsManager.prototype.generateWeek = function(){
			this.week = [];
			if(this.rapportByServices.service.typeAvailabilities == 1){
				var currentDate = new Date(this.rapportByServices.startDate);
				var currentDateTimeCleared = this.clearTime(new Date(currentDate));
				for(var i = 0; i < this.rapportByServices.service.serviceAvailabilities.length; i++){
					var serviceAvailability = this.rapportByServices.service.serviceAvailabilities[i];
					for(var j = 0; j < serviceAvailability.groupDates.length; j++){
						var dates = {
							startDate: this.clearTime($filter('parseDate')(serviceAvailability.groupDates[j].startDate)),
							endDate :	this.clearTime($filter('parseDate')(serviceAvailability.groupDates[j].endDate))
						};
						if(dates.startDate <= currentDateTimeCleared  && currentDateTimeCleared <= dates.endDate){
							for(var h = 0; h < serviceAvailability.timeslots.length; h++){
								var timeslot = serviceAvailability.timeslots[h];
								if(timeslot.startTime == $filter('date')(this.rapportByServices.startDate, 'HH:mm:ss') &&
									timeslot.endTime == $filter('date')(this.rapportByServices.endDate, 'HH:mm:ss')){
									var localDate = new Date(dates.startDate);
									while(localDate <= dates.endDate){
										var startTimeslot = new Date(localDate);
										var endTimeslot = new Date(localDate);
										startTimeslot.setHours(this.rapportByServices.startDate.getHours());
										startTimeslot.setMinutes(this.rapportByServices.startDate.getMinutes());
										startTimeslot.setSeconds(this.rapportByServices.startDate.getSeconds());
										endTimeslot.setHours(this.rapportByServices.endDate.getHours());
										endTimeslot.setMinutes(this.rapportByServices.endDate.getMinutes());
										endTimeslot.setSeconds(this.rapportByServices.endDate.getSeconds());
										this.week.push({
											checked:true,
											actual: (this.clearTime(currentDate).getTime() == this.clearTime(new Date(startTimeslot)).getTime()),
											startDate:startTimeslot,
											endDate:endTimeslot});
										localDate.setDate(localDate.getDate() + 1);
									}
								}
							}
						}
					}
				}
			}
		}

		GroupsManager.prototype.switchCheckedDay = function(day){
			day.checked = !day.checked;
			this.generateDisplayedMembers();
		}

    GroupsManager.prototype.getParticipantsParameter = function(idParameter){
			if(this.settings.form_participants_parameters != null){
				for(var i = 0; i < this.settings.form_participants_parameters.length; i++){
					if(this.settings.form_participants_parameters[i].id == idParameter){
						return this.settings.form_participants_parameters[i];
					}
				}
			}
			return null;
		}

		GroupsManager.prototype.getServiceById = function(idService){
			for(var i = 0; i < this.services.length; i++){
				if(this.services[i].id == idService)
					return this.services[i];
			}
			return null;
		}

    GroupsManager.prototype.getServicePrice = function(idService, idPrice){
			var service = this.getServiceById(idService);
			for(var i = 0; i < service.servicePrices.length; i++){
				var servicePrice = service.servicePrices[i];
				if(servicePrice.id == idPrice)
					return servicePrice;
			}
			return null;
		}

    GroupsManager.prototype.getMemberById = function(idMember){
			for(var i = 0; i < this.members.length; i++){
				if(this.members[i].id == idMember)
					return this.members[i];
			}
			return null;
		}

    GroupsManager.prototype.getGroupById = function(idGroup){
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				if(this.rapportByServices.groups[i].id == idGroup)
					return this.rapportByServices.groups[i];
			}
			return null;
		}

		GroupsManager.prototype.setGroupById = function(idGroup, group){
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				if(this.rapportByServices.groups[i].id == idGroup){
					this.rapportByServices.groups[i] = group;
				}
			}
		}

		GroupsManager.prototype.getStateById = function(id){
			for(var i = 0; i < this.settings.statesList.length; i++){
				if(this.settings.statesList[i].id == id) return this.settings.statesList[i];
			}
			return null;
		}

		GroupsManager.prototype.getGroupName = function(group){
			var name = $filter('htmlSpecialDecode')(group.name);
			/*if(group.oneByBooking){
				name += '(#'+group.id+')';
			}*/
			return name;
		}

		GroupsManager.prototype.generateDisplayedMembers = function(){
			this.displayedMembers = [];
			for(var i = 0; i < this.members.length; i++){
				var member = JSON.parse(JSON.stringify(this.members[i]));
				var group = this.getGroupByIdMember(member.id);
				member.group = group;
				if(member.group == null) member.groupName = 'Aucun';
				else member.groupName = this.getGroupName(group);
				if(this.isPresent(member)) member.availability = 'Oui';
				else member.availability = 'Non';
				member.groups = this.getAllGroupsByIdMember(member.id);
				member.groupOut = this.haveGroupsOutPeriods(member);
				this.displayedMembers.push(member);
			}
		}

		/**
		 * have groups out periods
		 */
		GroupsManager.prototype.haveGroupsOutPeriods = function(member){
			for(var i = 0; i < member.groups.length; i++){
				var group = member.groups[i];
				if(group.idService != this.rapportByServices.service.id){
					return true;
				}
			}
			return false;
		}

    GroupsManager.prototype.regroupParticipantsByParticipantPameters = function(){
			this.participantsParameters = [];
      this.participants = {};
			this.displayedParticipants = {};
      for(var i = 0; i < this.rapportByServices.appointments.length; i++){
        var appointment = this.rapportByServices.appointments[i];
        for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
          var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
          var servicePrice = this.getServicePrice(appointment.idService, appointmentNumberPrice.idPrice);
          var participantParameters = this.cloneJSON(this.getParticipantsParameter(servicePrice.participantsParameter));
          if(participantParameters != null){
						participantParameters.fields.push({'varname':'state', 'name':{'fr_FR':'État'}});
						participantParameters.fields.push({'varname':'group', 'name':{'fr_FR':'Groupe'}});
            if(this.participants[participantParameters.id] == null){
							this.participants[participantParameters.id] = [];
              this.participantsParameters.push(participantParameters);
							this.filtersAll[participantParameters.id] = {};
							this.orderByParameters[participantParameters.id] = {by:'', reverse:false};
            }
						if(this.filters[participantParameters.id] == null) this.filters[participantParameters.id] = {};
						var filters = this.filters[participantParameters.id];
						for(var index = 0; index < appointmentNumberPrice.participants.length; index++){
							var participant = appointmentNumberPrice.participants[index];
							participant.group = this.getGroupNameForIdParticipant(participant.uri);
							participant.state = this.getStateById(appointment.state).filterName;
							if(appointment.state == 'waiting'){
								participant.state = _scope.backendCtrl.getWaitingName(_scope.backendCtrl.getBookingById(appointment.idBooking));
							}
							if(appointment.quotation) participant.state = 'Devis';
							for(var fieldsIndex = 0; fieldsIndex < participantParameters.fields.length; fieldsIndex++){
								var varname = participantParameters.fields[fieldsIndex].varname;
								this.filtersAll[participantParameters.id][varname] = true;
								if(filters[varname] == null) filters[varname] = [];
								if(!this.isInFilters(filters[varname], participant[varname])){
									filters[varname] = this.addInFilters(filters[varname], participant[varname]);
								}
							}
							this.participants[participantParameters.id].push(participant);
						}
						this.orderParticipantsBy(participantParameters.id);
          }
        }
      }
    }

		GroupsManager.prototype.isInFilters = function(values, newValue){
			for(var i = 0; i < values.length; i++){
				var value = values[i];
				if(value.value == newValue){
					return true;
				}
			}
			return false;
		}

		GroupsManager.prototype.addInFilters = function(values, newValue){
			var found = false;
			for(var i = 0; i < values.length; i++){
				var value = values[i];
				if(value.value == newValue){
					found = true;
					break;
				}
			}
			if(!found){
				values.push({checked: true, value: newValue});
				values.sort(function(valuesA, valuesB){
					if(typeof valuesA.value == 'string'){
						return valuesA.value.localeCompare(valuesB.value);
					}
					return valuesA.value - valuesB.value;
				});
			}
			return values;
		}

		GroupsManager.prototype.filteredParticipants = function(idParticipantParameters){
			var filters = this.filters[idParticipantParameters];
			var participants = this.participants[idParticipantParameters];
			var displayedParticipants = this.displayedParticipants[idParticipantParameters];
			displayedParticipants = [];
			for(var i = 0; i < participants.length; i++){
				var participant = participants[i];
				var display = true;
				for(var key in filters){
					var values = filters[key];
					for(var k = 0; k < values.length; k++){
						var value = values[k];
						if(participant[key] == value.value) display = display && value.checked;
					}
				}
				if(display){
					displayedParticipants.push(participant);
				}
			}
			this.displayedParticipants[idParticipantParameters] = displayedParticipants;
			this.assignmentParticipants = {idGroup:-1, participants:{}};
		}

		GroupsManager.prototype.changeAllFilters = function(idParticipantParameters, varname){
			var values = this.filters[idParticipantParameters][varname];
			for(var i = 0; i < values.length; i++){
				values[i].checked = this.filtersAll[idParticipantParameters][varname];
			}
			this.filteredParticipants(idParticipantParameters);
		}

		GroupsManager.prototype.reinitFilters = function(){
			for(var idParticipantParameters in this.filters){
				for(var varname in this.filters[idParticipantParameters]){
					var filters = this.filters[idParticipantParameters][varname];
					for(var i = 0; i < filters.length; i++){
						filters[i].checked = true;
					}
				}
			}
			for(var idParticipantParameters in this.filtersAll){
				for(var varname in this.filtersAll[idParticipantParameters]){
					this.filtersAll[idParticipantParameters][varname] = true;
				}
				this.filteredParticipants(idParticipantParameters);
			}
		}

		GroupsManager.prototype.changeOrderBy = function(idParticipantParameters, newBy){
			var by = this.orderByParameters[idParticipantParameters].by;
			if(by == newBy){
				this.orderByParameters[idParticipantParameters].reverse = !this.orderByParameters[idParticipantParameters].reverse;
			}
			else {
				this.orderByParameters[idParticipantParameters].by = newBy;
				this.orderByParameters[idParticipantParameters].reverse = false;
			}
		}

		GroupsManager.prototype.orderParticipantsBy = function(idParticipantParameters){
			var by = this.orderByParameters[idParticipantParameters].by;
			var reverse = this.orderByParameters[idParticipantParameters].reverse;
			this.participants[idParticipantParameters] = $filter('orderBy')(this.participants[idParticipantParameters], by, reverse);
			this.filteredParticipants(idParticipantParameters);
		}

		GroupsManager.prototype.changeOrderByMember = function(newBy){
			var by = this.orderByMembers.by;
			if(by == newBy){
				this.orderByMembers.reverse = !this.orderByMembers.reverse;
			}
			else {
				this.orderByMembers.by = newBy;
				this.orderByMembers.reverse = false;
			}
		}

		GroupsManager.prototype.orderMembersBy = function(){
			var by = this.orderByMembers.by;
			var reverse = this.orderByMembers.reverse;
			this.displayedMembers = $filter('orderBy')(this.displayedMembers, by, reverse);
		}

		GroupsManager.prototype.openDisplayBookingOfParameterUri = function(uri){
			var idBooking = -1;
			for(var i = 0; i < this.rapportByServices.appointments.length; i++){
				var appointment = this.rapportByServices.appointments[i];
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
			if(idBooking != -1){
				_scope.backendCtrl.openDisplayBooking(_scope.backendCtrl.getBookingById(idBooking));
				_self.close();
			}
		}

    GroupsManager.prototype.regenerateNewGroup = function(){
      this.newGroup = JSON.parse(JSON.stringify(_skeletonGroup));
      this.newGroup.idPlace = this.rapportByServices.idPlace;
      this.newGroup.idService = this.rapportByServices.service.id;
      this.newGroup.startDate = this.rapportByServices.startDate;
      this.newGroup.endDate = this.rapportByServices.endDate;
    }

    GroupsManager.prototype.generateGroupsList = function(){
      this.groupsList = [];
      for(var i = 0; i < this.rapportByServices.groups.length; i++){
        var group = this.rapportByServices.groups[i];
        this.groupsList.push({
          id:group.id,
          name: this.getGroupName(group)
        });
      }
      this.groupsList.push({
        id:-1,
        name: 'Aucun'
      });
    }

    GroupsManager.prototype.close = function() {
      this.opened = false;
    }

		GroupsManager.prototype.getNumberParticipantsNotInGroups = function(){
			var number = 0;
			if(this.rapportByServices != null){
				for(var i = 0; i < this.rapportByServices.appointments.length; i++){
	        var appointment = this.rapportByServices.appointments[i];
	        for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
	          var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
						for(var index = 0; index < appointmentNumberPrice.participants.length; index++){
							var participant = appointmentNumberPrice.participants[index];
							if(this.getGroupByIdParticipant(participant.uri) == null){
								number++;
							}
						}
					}
				}
			}
			return number;
  	}

    GroupsManager.prototype.isPresent = function(member){
      if((this.rapportByServices != null && this.rapportByServices.idMembers.indexOf(member.id) != -1) ||
				_scope.backendCtrl.isMemberIsAvailable(member, this.rapportByServices.startDate)){
        return true;
      }
      return false;
    }

		/**
		 *
		 */
		GroupsManager.prototype.groupIsInRapport = function(group){
			if(group == null) return true;
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				var groupLocal = this.rapportByServices.groups[i];
				if(groupLocal.id == group.id){
					return true;
				}
			}
			return false;
		}

		GroupsManager.prototype.getRapportGroupByIdMember = function(idMember){
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				var group = this.rapportByServices.groups[i];
				if(group.idMembers.indexOf(idMember) != -1){
					return group;
				}
			}
			return null;
    }

    GroupsManager.prototype.getGroupByIdMember = function(idMember){
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				var group = this.rapportByServices.groups[i];
				if(group.idMembers.indexOf(idMember) != -1 && this.isDateChecked(group)){
					return group;
				}
			}
			for(var i = 0; i < this.groups.length; i++){
				var group = this.groups[i];
				if(group.idMembers.indexOf(idMember) != -1 && this.isDateChecked(group) && !this.groupIsInRapport(group)){
					return group;
				}
			}
			return null;
    }


		GroupsManager.prototype.getAllGroupsByIdMember = function(idMember){
			var groups = [];
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				var group = this.rapportByServices.groups[i];
				if(group.idMembers.indexOf(idMember) != -1 && this.isDateChecked(group)){
					groups.push(group);
				}
			}
			for(var i = 0; i < this.groups.length; i++){
				var group = this.groups[i];
				if(group.idMembers.indexOf(idMember) != -1  && this.isDateChecked(group) && !this.groupIsInRapport(group)){
					groups.push(group);
				}
			}
			return groups;
    }

		/**
		 * return true if group have day checked
		 */
		GroupsManager.prototype.isDateChecked = function(group){
			var groupDate = this.parseDate(group.startDate);
			if(this.week.length == 0){
				var startDate = this.parseDate(this.rapportByServices.startDate);
				return groupDate.getFullYear() == startDate.getFullYear() &&
					groupDate.getMonth() == startDate.getMonth() &&
						groupDate.getDate() == startDate.getDate();
			}
			else {
				for(var i = 0; i < this.week.length; i++){
					var day = this.week[i];
					if(groupDate.getFullYear() == day.startDate.getFullYear() &&
						groupDate.getMonth() == day.startDate.getMonth() &&
							groupDate.getDate() == day.startDate.getDate()){
						return day.checked;
					}
				}
			}
			return false;
		}

    GroupsManager.prototype.getGroupNameForIdMembre = function(idMember){
      var group = this.getGroupByIdMember(idMember);
      if(group != null) return this.getGroupName(group);
      return 'Aucun';
    }

    GroupsManager.prototype.removeIdMember = function(group, idMembre){
      var index = group.idMembers.indexOf(idMembre);
      if(index != -1) {
        group.idMembers.splice(index, 1);
      }
    }

		GroupsManager.prototype.toggleAssignmentMember = function(member){
			this.assignmentMembers.members[member.id] = !this.assignmentMembers.members[member.id];
		}

		GroupsManager.prototype.putAllMembers = function(){
			for(var i = 0; i < this.members.length; i++){
				var member = this.members[i];
				if(this.groupIsInRapport(this.getGroupByIdMember(member.id))){
					this.assignmentMembers.members[member.id] = this.assignmentMembers.all;
				}
			}
		}

		GroupsManager.prototype.putAllParticipants = function(idParticipantParameters){
			for(var i = 0; i < this.displayedParticipants[idParticipantParameters].length; i++){
				var participant = this.displayedParticipants[idParticipantParameters][i];
				this.assignmentParticipants.participants[participant.uri] = this.assignmentParticipants.all[idParticipantParameters];
			}
		}


    GroupsManager.prototype.getGroupByIdParticipant = function(idParticipant){
      for(var i = 0; i < this.rapportByServices.groups.length; i++){
        var group = this.rapportByServices.groups[i];
        if(group.idParticipants.indexOf(idParticipant) != -1){
          return group;
        }
      }
      return null;
    }

		GroupsManager.prototype.getAllGroupsByIdParticipant = function(idParticipant){
			var groups = [];
      for(var i = 0; i < this.groups.length; i++){
        var group = this.groups[i];
        if(group.idParticipants.indexOf(idParticipant) != -1){
          groups.push(group);
        }
      }
      return groups;
    }


    GroupsManager.prototype.getGroupNameForIdParticipant = function(idParticipant){
      var group = this.getGroupByIdParticipant(idParticipant);
      if(group != null) return this.getGroupName(group);
      return 'Aucun';
    }

    GroupsManager.prototype.removeIdParticipant = function(group, idParticipant){
			do{
				var index = group.idParticipants.indexOf(idParticipant);
				if(index != -1) {
					group.idParticipants.splice(index, 1);
				}
			}while(index != -1);
    }

		GroupsManager.prototype.addToUpdate = function(groupsToUpdate, group){
			var found = false;
			if(group.id != -1){
	      for(var i = 0; i < groupsToUpdate.length; i++){
					if(groupsToUpdate[i].id == group.id){
						found = true;
						groupsToUpdate[i] = group;
					}
				}
			}
			else {
	      for(var i = 0; i < groupsToUpdate.length; i++){
					if(groupsToUpdate[i].idService == group.idService &&
						groupsToUpdate[i].idPlace == group.idPlace &&
						groupsToUpdate[i].name == group.name &&
						groupsToUpdate[i].startDate == group.startDate &&
						groupsToUpdate[i].endDate == group.endDate){
						found = true;
						groupsToUpdate[i] = group;
					}
				}
			}
			if(!found){
				groupsToUpdate.push(group);
			}
			return groupsToUpdate;
    }

		GroupsManager.prototype.allMembersIsNotInGroup = function(idGroup, members){
			var group = null;
      if(idGroup != -1){
        group = this.getGroupById(idGroup);
      }
			for(var key in members){
        if(members[key]){
          var idMember = (key * 1);
          var alreadyInGroup = this.getGroupByIdMember(idMember);
          if(alreadyInGroup != null && this.assignmentMembers.idGroup != alreadyInGroup.id){
            return false;
        	}
        }
      }
			return true;
		}

		GroupsManager.prototype.allMembersIsNotInPlace = function(idGroup, members){
			var group = null;
      if(idGroup != -1){
        group = this.getGroupById(idGroup);
      }
			if(group != null){
				for(var key in members){
	        if(members[key]){
	          var idMember = (key * 1);
	          if(group.idPlace != null && this.getMemberById(idMember).places.indexOf(group.idPlace) == -1){
	            return false;
	        	}
	        }
	      }
			}
			return true;
		}

    /**
     * return true if the new group is ok !
     */
    GroupsManager.prototype.assignmentMembersAskAction = function(dialogTexts, dialogTexts2){
			var allMembersIsNotInGroup = this.allMembersIsNotInGroup(this.assignmentMembers.idGroup, this.assignmentMembers.members);
			var allMembersIsNotInPlace = this.allMembersIsNotInPlace(this.assignmentMembers.idGroup, this.assignmentMembers.members);
			if(allMembersIsNotInGroup){
				if(allMembersIsNotInPlace) this.assignmentMembersAction();
				else {
					sweetAlert({
		  		  title: dialogTexts2.title,
		  		  text: dialogTexts2.text,
		  		  type: "warning",
		  		  showCancelButton: true,
		  		  confirmButtonColor: "#DD6B55",
		  		  confirmButtonText: dialogTexts2.confirmButton,
		  		  cancelButtonText: dialogTexts2.cancelButton,
		  		  closeOnConfirm: true,
		  		  html: false
		  		}, function(){
						_scope.$apply(function(){
							_self.assignmentMembersAction();
						});
					});
				}
			}
			else {
				var text = dialogTexts.text;
				if(!allMembersIsNotInPlace) {
					text += '<br />' + dialogTexts2.text;
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
					_scope.$apply(function(){
						_self.assignmentMembersAction();
					});
				});
			}
    }


		GroupsManager.prototype.assignmentMembersAction = function(){
			var groupsToUpdate = [];
			var group = null;
			if(this.assignmentMembers.idGroup != -1){
				group = this.getGroupById(this.assignmentMembers.idGroup);
			}
			var members = this.assignmentMembers.members;
			for(var key in members){
				if(members[key]){
					var idMember = (key * 1);
					var groups = this.getAllGroupsByIdMember(idMember);
					for(var i = 0; i < groups.length; i++){
						var alreadyInGroup = groups[i];
						if(alreadyInGroup != null && this.assignmentMembers.idGroup != alreadyInGroup.id){
							this.removeIdMember(alreadyInGroup, idMember);
							groupsToUpdate = this.addToUpdate(groupsToUpdate, alreadyInGroup);
						}
					}
					if(group!=null){
						if(group.idMembers.indexOf(idMember) == -1) group.idMembers.push(idMember);
						if(this.week.length == 0){
							groupsToUpdate = this.addToUpdate(groupsToUpdate, group);
						}
						else {
							for(var i = 0; i < this.week.length; i++){
								var day = this.week[i];
								if(day.checked){
									var newGroup = JSON.parse(JSON.stringify(group));
									if(newGroup.startDate !=  day.startDate && newGroup.endDate !=  day.endDate){
										newGroup.id = -1;
										newGroup.startDate = day.startDate;
										newGroup.endDate = day.endDate;
									}
						    	groupsToUpdate = this.addToUpdate(groupsToUpdate, newGroup);
								}
							}
						}
					}
				}
			}
			//this.generateDisplayedMembers();
			this.editGroups(groupsToUpdate);
			this.assignmentMembers = {idGroup:-1, members:{}};
		}

		GroupsManager.prototype.allParticipantsIsNotInGroup = function(idGroup, participants){
			var group = null;
      if(idGroup != -1){
        group = this.getGroupById(idGroup);
      }
			for(var key in participants){
        if(participants[key]){
          var idParticipant = key;
          var alreadyInGroup = this.getGroupByIdParticipant(idParticipant);
          if(alreadyInGroup != null && this.assignmentParticipants.idGroup != alreadyInGroup.id){
          	return false;
          }
        }
      }
			return true;
		}

		/**
     * Ask if ok when assign participants in group
     */
    GroupsManager.prototype.assignmentParticipantsAskAction = function(dialogTexts){
			var allParticipantsIsNotInGroup = this.allParticipantsIsNotInGroup(this.assignmentParticipants.idGroup, this.assignmentParticipants.participants);
			if(allParticipantsIsNotInGroup){
				this.assignmentParticipantsAction();
			}
			else {
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
						_self.assignmentParticipantsAction();
					});
				});
			}
    }


    /**
     * return true if the new group is ok !
     */
    GroupsManager.prototype.assignmentParticipantsAction = function(){
      var groupsToUpdate = [];
      var group = null;
      if(this.assignmentParticipants.idGroup != -1){
        group = this.getGroupById(this.assignmentParticipants.idGroup);
      }
      var participants = this.assignmentParticipants.participants;
      for(var key in participants){
        if(participants[key]){
          var idParticipant = key;
          /*var alreadyInGroup = this.getAllGroupsByIdParticipant(idParticipant);
          if(alreadyInGroup != null && this.assignmentParticipants.idGroup != alreadyInGroup.id){
            this.removeIdParticipant(alreadyInGroup, idParticipant);
          	groupsToUpdate = this.addToUpdate(groupsToUpdate, alreadyInGroup);
          }*/
					var groups = this.getAllGroupsByIdParticipant(idParticipant);
					for(var i = 0; i < groups.length; i++){
						var alreadyInGroup = groups[i];
						if(alreadyInGroup != null && this.assignmentParticipants.idGroup != alreadyInGroup.id){
							this.removeIdParticipant(alreadyInGroup, idParticipant);
							groupsToUpdate = this.addToUpdate(groupsToUpdate, alreadyInGroup);
						}
					}
          if(group!=null && group.idParticipants.indexOf(idParticipant) == -1){
            group.idParticipants.push(idParticipant);
						if(this.week.length == 0){
							groupsToUpdate = this.addToUpdate(groupsToUpdate, group);
						}
						else {
							for(var i = 0; i < this.week.length; i++){
								var day = this.week[i];
								if(day.checked){
									var newGroup = JSON.parse(JSON.stringify(group));
									if(newGroup.startDate !=  day.startDate && newGroup.endDate !=  day.endDate){
										newGroup.id = -1;
										newGroup.startDate = day.startDate;
										newGroup.endDate = day.endDate;
									}
						    	groupsToUpdate = this.addToUpdate(groupsToUpdate, newGroup);
								}
							}
						}
          }
        }
      }
			//this.regroupParticipantsByParticipantPameters();
			this.editGroups(groupsToUpdate);
      this.assignmentParticipants = {idGroup:-1, participants:{}};
    }

    /**
     * return true if the new group is ok !
     */
    GroupsManager.prototype.isOkGroup = function(){
      return this.newGroup != null && this.newGroup.idService!=-1 && this.newGroup.name!=null && this.newGroup.name.length > 0 && !this.launchEditGroup;
    }

		/**
     * Launch get groups
     */
    GroupsManager.prototype.getGroups = function(){
      if(!this.launchGetGroups){
        this.launchGetGroups = true;
				var week = [];
				if(this.week.length > 0){
					for(var i = 0; i < this.week.length; i++){
						var day = JSON.parse(JSON.stringify(this.week[i]));
						day.startDate = $filter('formatDateTime')(day.startDate);
						day.endDate = $filter('formatDateTime')(day.endDate);
						week.push(day);
					}
				}
				else {
					week.push({
						startDate: $filter('formatDateTime')(new Date(this.rapportByServices.startDate)),
						endDate: $filter('formatDateTime')(new Date(this.rapportByServices.endDate))
					});
				}
        var data = {
  				action:'getGroups',
					week:JSON.stringify(week)
  			}
  			jQuery.post(_ajaxUrl, data, function(data) {
  				_scope.$apply(function(){
  					this.launchGetGroups = false;
  					data = JSON.parse(data);
  					if(typeof data === 'string'){
  						sweetAlert('', data, 'error');
  					}
  					else {
							this.groups = data;
							this.initializeUI();
  					}
  				}.bind(this));
  			}.bind(this)).fail(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			}).error(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			});
  		}
  	}


    /**
     * Launch the create of group
     */
    GroupsManager.prototype.addGroup = function(){
      if(this.isOkGroup(group)){
        this.launchEditGroup = true;
        var group = JSON.parse(JSON.stringify(this.newGroup));
				var groups = [];
				if(this.week.length == 0){
					group.presentation = group.presentation.replace(new RegExp('\n', 'g'),'<br />');
	        group.startDate = $filter('formatDateTime')(group.startDate);
	        group.endDate = $filter('formatDateTime')(group.endDate);
					groups.push(group);
				}
  			else {
					for(var i = 0; i < this.week.length; i++){
						var day = JSON.parse(JSON.stringify(this.week[i]));
						if(day.checked){
							var newGroup = JSON.parse(JSON.stringify(group));
				      newGroup.startDate = $filter('formatDateTime')(day.startDate);
				      newGroup.endDate = $filter('formatDateTime')(day.endDate);
							groups.push(newGroup);
						}
					}
				}

        var data = {
  				action:'editGroups',
  				groups: JSON.stringify(groups)
  			}

  			jQuery.post(_ajaxUrl, data, function(data) {
  				_scope.$apply(function(){
  					this.launchEditGroup = false;
  					data = JSON.parse(data);
  					if(typeof data === 'string'){
  						sweetAlert('', data, 'error');
  					}
  					else {
							for(var i = 0; i < data.length; i++){
								var group = data[i];
								if(this.clearTime($filter('parseDate')(group.startDate)).getTime() ==
									this.clearTime(new Date(this.rapportByServices.startDate)).getTime()){
              		this.rapportByServices.groups.push(group);
								}
							}
							_scope.backendCtrl.updateGroups(data);
							_scope.backendCtrl.generateRapportsByServices();
              this.regenerateNewGroup();
              this.generateGroupsList();
  					}
  				}.bind(this));
  			}.bind(this)).fail(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			}).error(function(err){
  				sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
  			});
  		}
  	}

    /**
     * edit group
     */
    GroupsManager.prototype.editGroups = function(groups){
			if(!this.launchEditGroup){
				this.launchEditGroup = true;
				for(var i = 0; i < groups.length; i++){
		      var group = JSON.parse(JSON.stringify(groups[i]));
		      group.presentation = group.presentation.replace(new RegExp('\n', 'g'),'<br />');
		      group.startDate = $filter('formatDateTime')(group.startDate);
		      group.endDate = $filter('formatDateTime')(group.endDate);
					groups[i] = group;
				}
	      var data = {
					action:'editGroups',
					groups: JSON.stringify(groups)
				}

				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						this.launchEditGroup = false;
						data = JSON.parse(data);
						if(typeof data === 'string'){
							sweetAlert('', data, 'error');
						}
						else {
							this.updateLocalGroups(data);
							_scope.backendCtrl.updateGroups(data);
							_scope.backendCtrl.generateRapportsByServices();
							this.initializeUI();
						}
					}.bind(this));
				}.bind(this)).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
    }

    /**
     * update local groups
     */
    GroupsManager.prototype.updateLocalGroups = function(groups){
			for(var i = 0; i < groups.length; i++){
				var group = groups[i];
				for(var j = 0; j < this.groups.length; j++){
					if(this.groups[j].id == group.id){
						this.groups[j] = group;
					}
				}
				for(var j = 0; j < this.rapportByServices.groups.length; j++){
					if(this.rapportByServices.groups[j].id == group.id){
						this.rapportByServices.groups[j] = group;
					}
				}
			}
		}


		return GroupsManager;
	}

	angular.module('resa_app').factory('GroupsManager', GroupsManagerFactory);
}());
