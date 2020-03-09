"use strict";

(function(){
	var _self = null;
	var _scope = null;
  var _ajaxUrl = null;

	function GroupManagerFactory($filter, FunctionsManager){

		var GroupManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(GroupManager.prototype, FunctionsManager.prototype);

      this.opened = false;
      this.rapportByServices = null;
			this.group = null;
      this.service = null;
      this.members = [];
			this.services = [];
      this.settings = null;
      this.participantsParameters = [];
      this.participants = {};
      this.groupsList = [];
			this.assignmentParticipants = {};
			this.editGroupView = false;
			this.addMemberModel = -1;
			this.currentIdMembers = [];

			this.week = [];
			this.groups = [];
			this.displayedMembers = [];
			this.launchGetGroups = false;
			this.validFormLaunched = false;

      _scope = scope;
      _self = this;
      _scope.backendCtrl.groupCtrl = this;
		}

		GroupManager.prototype.initialize = function(rapportByServices, group, members, services, settings, ajaxUrl) {
			this.rapportByServices = rapportByServices;
			this.group = group;
			this.members = members;
			this.services = services;
			this.settings = settings;
			_ajaxUrl = ajaxUrl;

			this.currentIdMembers = JSON.parse(JSON.stringify(this.group.idMembers));

			this.editGroupView = false;
      this.participantsParameters = [];
			this.participants = {};
			this.assignmentParticipants = {};
			this.generateWeek();
			this.getGroups();
		}

		GroupManager.prototype.initializeUI = function(){
			this.generateDisplayedMembers();
			this.generateGroupsList();
			this.regroupParticipantsByParticipantPameters();
		}

		GroupManager.prototype.generateWeek = function(){
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
						if(dates.startDate <= currentDateTimeCleared && currentDateTimeCleared <= dates.endDate){
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

		GroupManager.prototype.generateDisplayedMembers = function(){
			this.displayedMembers = [];
			for(var i = 0; i < this.currentIdMembers.length; i++){
				var member = JSON.parse(JSON.stringify(this.getMemberById(this.currentIdMembers[i])));
				member.groups = this.getAllGroupsByIdMember(member.id);
				member.groupOut = this.haveGroupsOutPeriods(member);
				this.displayedMembers.push(member);
			}
		}


		GroupManager.prototype.switchCheckedDay = function(day){
			day.checked = !day.checked;
			this.generateDisplayedMembers();
		}

		GroupManager.prototype.regroupParticipantsByParticipantPameters = function(){
      for(var i = 0; i < this.rapportByServices.appointments.length; i++){
        var appointment = this.rapportByServices.appointments[i];
        for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
          var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
          var servicePrice = this.getServicePrice(appointment.idService, appointmentNumberPrice.idPrice);
          var participantParameters = this.cloneJSON(this.getParticipantsParameter(servicePrice.participantsParameter));
          if(participantParameters != null){
						if(!this.settings.staffIsConnected) participantParameters.fields.push({'varname':'state', 'name':{'fr_FR':'État'}});
						if(this.settings.staffIsConnected) participantParameters.fields.push({'varname':'state', 'name':{'fr_FR':'Téléphone'}});
            if(this.participants[participantParameters.id] == null){
              this.participantsParameters.push(participantParameters);
              this.participants[participantParameters.id] = [];
            }
						for(var index = 0; index < appointmentNumberPrice.participants.length; index++){
							var participant = appointmentNumberPrice.participants[index];
							var group  = this.getGroupByIdParticipant(participant.uri);
							if(group != null && this.group.id == group.id){
								participant.state = this.getStateById(appointment.state).filterName;
								if(appointment.state == 'waiting'){
									participant.state = _scope.backendCtrl.getWaitingName(_scope.backendCtrl.getBookingById(appointment.idBooking));
								}
								if(appointment.quotation) participant.state = 'Devis';
								if(!this.settings.staffIsConnected || appointment.state == 'ok'){
									this.participants[participantParameters.id].push(participant);
									this.assignmentParticipants[participant.uri] = group.id;
								}
							}
						}
          }
        }
      }
    }

		GroupManager.prototype.getParticipantsParameter = function(idParameter){
			if(this.settings.form_participants_parameters != null){
				for(var i = 0; i < this.settings.form_participants_parameters.length; i++){
					if(this.settings.form_participants_parameters[i].id == idParameter){
						return this.settings.form_participants_parameters[i];
					}
				}
			}
			return null;
		}

		GroupManager.prototype.getServiceById = function(idService){
			for(var i = 0; i < this.services.length; i++){
				if(this.services[i].id == idService)
					return this.services[i];
			}
			return null;
		}

    GroupManager.prototype.getServicePrice = function(idService, idPrice){
			var service = this.getServiceById(idService);
			for(var i = 0; i < service.servicePrices.length; i++){
				var servicePrice = service.servicePrices[i];
				if(servicePrice.id == idPrice)
					return servicePrice;
			}
			return null;
		}

		GroupManager.prototype.getGroupByIdParticipant = function(idParticipant){
      for(var i = 0; i < this.rapportByServices.groups.length; i++){
        var group = this.rapportByServices.groups[i];
        if(group.idParticipants.indexOf(idParticipant) != -1){
          return group;
        }
      }
      return null;
    }

		GroupManager.prototype.getAllGroupsByIdParticipant = function(idParticipant){
			var groups = [];
      for(var i = 0; i < this.groups.length; i++){
        var group = this.groups[i];
        if(group.idParticipants.indexOf(idParticipant) != -1){
          groups.push(group);
        }
      }
      return groups;
    }

		GroupManager.prototype.getMemberById = function(idMember){
			for(var i = 0; i < this.members.length; i++){
				if(this.members[i].id == idMember)
					return this.members[i];
			}
			return null;
		}

		GroupManager.prototype.getGroupById = function(idGroup){
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				if(this.rapportByServices.groups[i].id == idGroup)
					return this.rapportByServices.groups[i];
			}
			return null;
		}

		GroupManager.prototype.getStateById = function(id){
			for(var i = 0; i < this.settings.statesList.length; i++){
				if(this.settings.statesList[i].id == id) return this.settings.statesList[i];
			}
			return "Devis";
		}

		GroupManager.prototype.getGroupName = function(group){
			var name = $filter('htmlSpecialDecode')(group.name);
			/*if(group.oneByBooking){
				name += '(#'+group.id+')';
			}*/
			return name;
		}

		GroupManager.prototype.generateGroupsList = function(){
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

		GroupManager.prototype.close = function() {
      this.opened = false;
    }

		/**
		 * have groups out periods
		 */
		GroupManager.prototype.haveGroupsOutPeriods = function(member){
			for(var i = 0; i < member.groups.length; i++){
				var group = member.groups[i];
				if(group.idService != this.rapportByServices.service.id){
					return true;
				}
			}
			return false;
		}


		GroupManager.prototype.openDisplayBookingOfParameterUri = function(uri){
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

		GroupManager.prototype.getCustomerOfParameterUri = function(uri){
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
			if(idBooking != -1 && _scope.backendCtrl.getBookingById(idBooking) != null){
				return _scope.backendCtrl.getBookingById(idBooking).customer;
			}
		}

		GroupManager.prototype.isAbsent = function(uri){
			var appointmentURI = null;
			for(var i = 0; i < this.rapportByServices.appointments.length; i++){
				var appointment = this.rapportByServices.appointments[i];
				for(var j = 0; j < appointment.appointmentNumberPrices.length; j++){
					var appointmentNumberPrice = appointment.appointmentNumberPrices[j];
					for(var index = 0; index < appointmentNumberPrice.participants.length; index++){
						var participant = appointmentNumberPrice.participants[index];
						if(participant.uri == uri){
							appointmentURI = appointment;
							break;
						}
					}
				}
			}
			if(appointmentURI == null) return false;
			return appointmentURI.tags.indexOf('absent') != -1;
		}

		GroupManager.prototype.getDisplayOption = function(member){
			var text = '[' + _scope.backendCtrl.getListPlaces(member.places) + '] '+ $filter('htmlSpecialDecode')(member.nickname);
			if(this.getGroupByIdMember(member.id)){
				text += ' - ' + this.getGroupByIdMember(member.id).name;
			}
			else if(!this.isPresent(member)){
				text += ' (non présent)'
			}
			return text;
		}

		GroupManager.prototype.addMemberToThisGroup = function(dialogTexts, dialogTexts2){
			if(this.addMemberModel > -1){
				var idMember = this.addMemberModel * 1;
				var alreadyInGroup = this.getGroupByIdMember(idMember);
				var inSamePlace = (this.getMemberById(idMember).places.indexOf(this.group.idPlace) != -1);
				if(!alreadyInGroup && inSamePlace){
					this.currentIdMembers.push(idMember);
					this.generateDisplayedMembers();
					this.addMemberModel = -1;
				}
				else {
					var text = '';
					if(alreadyInGroup) text += '<br />' + dialogTexts.text;
					if(!inSamePlace) text += '<br />' + dialogTexts2.text;
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
							_self.currentIdMembers.push(idMember);
							_self.generateDisplayedMembers();
							_self.addMemberModel = -1;
						});
					});
				}
			}
    }

		GroupManager.prototype.removeMemberToThisGroup = function(dialogTexts, $index){
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
					_self.currentIdMembers.splice($index, 1);
					_self.generateDisplayedMembers();
				});
			});
		}

		/**
		 *
		 */
		GroupManager.prototype.groupIsInRapport = function(group){
			if(group == null) return true;
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				var groupLocal = this.rapportByServices.groups[i];
				if(groupLocal.id == group.id){
					return true;
				}
			}
			return false;
		}

		GroupManager.prototype.isPresent = function(member){
      if((this.rapportByServices != null && this.rapportByServices.idMembers.indexOf(member.id) != -1) || _scope.backendCtrl.isMemberIsAvailable(member, this.rapportByServices.startDate)){
        return true;
      }
      return false;
    }

		/*
    GroupManager.prototype.getGroupByIdMember = function(idMember){
			var group = null;
			if(this.week.length > 0){
				for(var i = 0; i < this.week.length; i++){
					var day = this.week[i];
					var groups = _scope.backendCtrl.getGroupsByIdMembersByDate(idMember, new Date(day.startDate), new Date(day.endDate));
					if( groups.length > 0 && (group == null || this.groupIsInRapport(groups[0]))){
						group = groups[0];
					}
				}
			}
			else {
				var groups = _scope.backendCtrl.getGroupsByIdMembersByDate(idMember, new Date(this.rapportByServices.startDate), new Date(this.rapportByServices.endDate));
				if(groups.length > 0){
					group = groups[0];
				}
			}
			return group;
    }
		*/

		GroupManager.prototype.getGroupByIdMember = function(idMember){
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				var group = this.rapportByServices.groups[i];
				if((group.idMembers.indexOf(idMember + '') > -1 || group.idMembers.indexOf(idMember) > -1) && this.isDateChecked(group)){
					return group;
				}
			}
			for(var i = 0; i < this.groups.length; i++){
				var group = this.groups[i];
				if((group.idMembers.indexOf(idMember + '') > -1 || group.idMembers.indexOf(idMember) > -1)  && this.isDateChecked(group) && !this.groupIsInRapport(group)){
					return group;
				}
			}
			return null;
    }

		GroupManager.prototype.getAllGroupsByIdMember = function(idMember){
			var groups = [];
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				var group = this.rapportByServices.groups[i];
				if((group.idMembers.indexOf(idMember + '') > -1 || group.idMembers.indexOf(idMember) > -1) && this.isDateChecked(group)){
					groups.push(group);
				}
			}
			for(var i = 0; i < this.groups.length; i++){
				var group = this.groups[i];
				if((group.idMembers.indexOf(idMember + '') > -1 || group.idMembers.indexOf(idMember) > -1)  && this.isDateChecked(group) && !this.groupIsInRapport(group)){
					groups.push(group);
				}
			}
			return groups;
    }

		/**
		 * return true if group have day checked
		 */
		GroupManager.prototype.isDateChecked = function(group){
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

		GroupManager.prototype.removeIdParticipant = function(group, idParticipant){
			do{
				var index = group.idParticipants.indexOf(idParticipant);
				if(index != -1) {
					group.idParticipants.splice(index, 1);
				}
			}while(index != -1);
		}

		GroupManager.prototype.addToUpdate = function(groupsToUpdate, group){
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

		GroupManager.prototype.removeIdMember = function(group, idMember){
      var index = group.idMembers.indexOf(idMember);
      if(index != -1) {
        group.idMembers.splice(index, 1);
      }
			else {
			 var index = group.idMembers.indexOf(idMember + '');
			 if(index != -1) {
				 group.idMembers.splice(index, 1);
			 }
			}
    }

		GroupManager.prototype.haveGroupToDate = function(groups, startDate, endDate){
      for(var i = 0; i < groups.length; i++){
				var group = groups[i];
				var groupDate = this.parseDate(group.startDate);
				if(groupDate.getFullYear() == startDate.getFullYear() &&
					groupDate.getMonth() == startDate.getMonth() &&
						groupDate.getDate() == startDate.getDate()){
					return true;
				}
			}
			return false;
    }


		GroupManager.prototype.validerForm = function(){
			var groupsToUpdate = [];
			//member
			var copyIdMembers = JSON.parse(JSON.stringify(this.group.idMembers));
			for(var i = 0; i < copyIdMembers.length; i++){
				var idMember = copyIdMembers[i];
				if(this.currentIdMembers.indexOf(idMember) == -1){
					var groups = this.getAllGroupsByIdMember(idMember);
					for(var j = 0; j < groups.length; j++){
						var alreadyInGroup = groups[j];
						if(alreadyInGroup != null){
							this.removeIdMember(alreadyInGroup, idMember);
							groupsToUpdate = this.addToUpdate(groupsToUpdate, alreadyInGroup);
						}
					}
				}
			}
			for(var i = 0; i < this.currentIdMembers.length; i++){
				var idMember = this.currentIdMembers[i];
				var groups = this.getAllGroupsByIdMember(idMember);


				if((this.group.idMembers.indexOf(idMember + '') == -1 && this.group.idMembers.indexOf(idMember) == -1)) this.group.idMembers.push(idMember);
				if(this.week.length == 0 && !this.haveGroupToDate(groups, this.parseDate(this.group.startDate), this.parseDate(this.group.endDate))){
					if(this.group.idMembers.indexOf(idMember + '') == -1 && this.group.idMembers.indexOf(idMember) == -1) this.group.idMembers.push(idMember);
					groupsToUpdate = this.addToUpdate(groupsToUpdate, this.group);
				}
				else {
					for(var j = 0; j < this.week.length; j++){
						var day = this.week[j];
						if(day.checked && !this.haveGroupToDate(groups, this.parseDate(day.startDate), this.parseDate(day.endDate))){
							var newGroup = JSON.parse(JSON.stringify(this.group));
							if(newGroup.startDate !=  day.startDate && newGroup.endDate !=  day.endDate){
								newGroup.id = -1;
								newGroup.startDate = day.startDate;
								newGroup.endDate = day.endDate;
							}
							if(newGroup.idMembers.indexOf(idMember + '') == -1 && newGroup.idMembers.indexOf(idMember) == -1) newGroup.idMembers.push(idMember);
				    	groupsToUpdate = this.addToUpdate(groupsToUpdate, newGroup);
						}
					}
				}
			}

			//replace all participants
			this.group.idParticipants = [];
			for(var key in this.assignmentParticipants){
				if(this.assignmentParticipants[key] == this.group.id){
						this.group.idParticipants.push(key);
				}
			}


			var indexGroups = [];
			var updateGroups = [];
			for(var key in this.assignmentParticipants){
				var idParticipant = key;
				if(this.assignmentParticipants[idParticipant] != -1 &&
					this.assignmentParticipants[idParticipant] != this.group.id &&
					indexGroups.indexOf(this.assignmentParticipants[idParticipant]) == -1){
					updateGroups.push(this.getGroupById(this.assignmentParticipants[idParticipant]));
					indexGroups.push(this.assignmentParticipants[idParticipant]);
				}
			}
			for(var index = 0; index < updateGroups.length; index++){
				var group = updateGroups[index];
				for(var key in this.assignmentParticipants){
					var idParticipant = key;
					if(this.assignmentParticipants[idParticipant] != -1 && this.assignmentParticipants[idParticipant] != this.group.id){
						var groups = this.getAllGroupsByIdParticipant(idParticipant);
						for(var i = 0; i < groups.length; i++){
							var alreadyInGroup = groups[i];
							if(alreadyInGroup != null && this.assignmentParticipants[idParticipant] != alreadyInGroup.id){
								this.removeIdParticipant(alreadyInGroup, idParticipant);
								groupsToUpdate = this.addToUpdate(groupsToUpdate, alreadyInGroup);
							}
						}
						if(group != null && group.idParticipants.indexOf(idParticipant) == -1){
							group.idParticipants.push(idParticipant);
							console.log(idParticipant + ' ajouté ' + group.name);
							if(this.week.length == 0){
								groupsToUpdate = this.addToUpdate(groupsToUpdate, group);
							}
							else {
								for(var i = 0; i < this.week.length; i++){
									var day = this.week[i];
									if(day.checked){
										var newGroup = JSON.parse(JSON.stringify(group));
										if(newGroup.startDate != day.startDate && newGroup.endDate != day.endDate){
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
			}

			if(this.week.length > 0){ //Pour changer les textes et la couleur
				for(var j = 0; j < this.week.length; j++){
					var day = this.week[j];
					if(day.checked){
						var newGroup = JSON.parse(JSON.stringify(this.group));
						if(newGroup.startDate !=  day.startDate && newGroup.endDate !=  day.endDate){
							newGroup.id = -1;
							newGroup.startDate = day.startDate;
							newGroup.endDate = day.endDate;
						}
						groupsToUpdate = this.addToUpdate(groupsToUpdate, newGroup);
					}
				}
			}



			this.editGroups(groupsToUpdate, true);
		}

		GroupManager.prototype.deleteGroup = function(idGroup){
			var index = -1;
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				if(this.rapportByServices.groups[i].id == idGroup){
					index = i;
				}
			}
			if(index != -1){
				this.rapportByServices.groups.splice(index, 1);
			}
		}

		GroupManager.prototype.updateGroups = function(groups){
			for(var i = 0; i < groups.length; i++){
				var group = groups[i];
				if(this.clearTime($filter('parseDate')(group.startDate)).getTime() ==
					this.clearTime(new Date(this.rapportByServices.startDate)).getTime()){
					this.updateGroup(group);
				}
			}
		}

		GroupManager.prototype.updateGroup = function(group){
			var found = false;
			for(var i = 0; i < this.rapportByServices.groups.length; i++){
				var localGroup = this.rapportByServices.groups[i];
				if(localGroup.id == group.id){
					found = true;
					if($filter('parseDate')(group.lastModificationDate).getTime() > $filter('parseDate')(localGroup.lastModificationDate).getTime()){
						this.rapportByServices.groups[i] = group;
					}
				}
			}
			if(!found){
				this.rapportByServices.groups.push(group);
			}
		}


		/**
     * Launch get groups
     */
    GroupManager.prototype.getGroups = function(){
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
     * edit group
     */
    GroupManager.prototype.editGroups = function(groups, close){
			if(!this.validFormLaunched){
				this.validFormLaunched = true;
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
						_self.validFormLaunched = false;
						data = JSON.parse(data);
						if(typeof data === 'string'){
							alert(data);
						}
						else {
							if(close) _self.close();
							_self.updateGroups(data);
							_self.updateLocalGroups(data);
							_scope.backendCtrl.updateGroups(data);
							_scope.backendCtrl.generateRapportsByServices();
						}
					}.bind(this));
				}.bind(this)).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
			}
    }

		GroupManager.prototype.deleteGroupAction = function(dialogTexts){
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
				var group = JSON.parse(JSON.stringify(_self.group));
				group.presentation = group.presentation.replace(new RegExp('\n', 'g'),'<br />');
				group.startDate = $filter('formatDateTime')(group.startDate);
				group.endDate = $filter('formatDateTime')(group.endDate);
				var week = [];
				for(var i = 0; i < _self.week.length; i++){
					var day = JSON.parse(JSON.stringify(_self.week[i]));
					if(day.checked){
						day.startDate = $filter('formatDateTime')(day.startDate);
						day.endDate = $filter('formatDateTime')(day.endDate);
						week.push(day);
					}
				}
				var data = {
					action:'deleteGroup',
					week:JSON.stringify(week),
					group: JSON.stringify(group)
				}

				jQuery.post(_ajaxUrl, data, function(data) {
					_scope.$apply(function(){
						data = JSON.parse(data);
						if(typeof data === 'string'){
							alert(data);
						}
						else {
							_self.close();
							for(var i = 0; i < data.length; i++){
								var group = data[i];
								_self.deleteGroup(group.id);
								_scope.backendCtrl.deleteGroup(group.id);
							}
							_scope.backendCtrl.generateRapportsByServices();
						}
					}.bind(this));
				}.bind(this)).fail(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				}).error(function(err){
					sweetAlert('', 'Erreur veuillez recommencer, si le problème persiste contactez un administrateur', 'error');
				});
  		});
  	}

		/**
     * update local groups
     */
    GroupManager.prototype.updateLocalGroups = function(groups){
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

		GroupManager.prototype.printDiv = function(id) {
      var content = document.getElementById(id).innerHTML;
  		var newWindow = window.open ('','', "menubar=yes,scrollbars=yes,resizable=yes");
  		newWindow.document.open();
  		newWindow.document.write("<html><head><title></title></head><body>"+content+"</body></html>");
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

		return GroupManager;
	}

	angular.module('resa_app').factory('GroupManager', GroupManagerFactory);
}());
