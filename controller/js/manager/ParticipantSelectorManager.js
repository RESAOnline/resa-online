"use strict";

(function(){
	var _self = null;
	var _scope = null;
	var _formCtrl = null;

	function ParticipantSelectorManagerFactory($log, $filter, FunctionsManager){
		var ParticipantSelectorManager = function(scope){
  		FunctionsManager.call(this);
  		angular.extend(ParticipantSelectorManager.prototype, FunctionsManager.prototype);

      this.participants = [];
      this.participantParametersFields = [];

      this.serviceParameters = null;
      this.numberPrice = null;
      this.index = 0;

      this.opened = false;
			this.notAllSynchro = false;

      _scope = scope;
      _self = this;

      if(_scope.formCtrl != null) _formCtrl = _scope.formCtrl;
			if(_scope.editBookingCtrl != null) _formCtrl = _scope.editBookingCtrl;
			_formCtrl.participantSelectorCtrl = this;
		}

		ParticipantSelectorManager.prototype.initialize = function(participants, participantParametersFields, serviceParameters, numberPrice, index, notAllSynchro) {
			this.participants = [];
			var participants = JSON.parse(JSON.stringify(participants));
			participants = $filter('orderBy')(participants, ['lastname', 'firstname']);
			for(var i = 0; i < participants.length; i++){
				var participantAux = participants[i];
				if(this.allParamsDefined(participantAux) && !this.alreadyInParticipant(this.participants, participantAux)){
					this.participants.push(participantAux);
				}
			}
      this.participantParametersFields = participantParametersFields;
      this.serviceParameters = serviceParameters;
      this.numberPrice = numberPrice;
      this.index = index;
			this.notAllSynchro = notAllSynchro;
		}

		ParticipantSelectorManager.prototype.alreadyInParticipant = function(participants, participant){
			for(var i = 0; i < participants.length; i++) {
				var participantAux = participants[i];
				if(
					(participantAux.lastname != null && participant.lastname &&
					participantAux.firstname != null && participant.firstname &&
				 	participantAux.lastname == participant.lastname &&
					participantAux.firstname == participant.firstname) ||
					(participantAux.lastname != null && participant.lastname != null && participant.firstname == null && participantAux.lastname == participant.lastname) ||
					(participantAux.firstname != null && participant.firstname != null && participant.lastname == null && participantAux.firstname == participant.firstname)
				){
					return true;
				}
			}
			return false;
		}


		ParticipantSelectorManager.prototype.allParamsDefined = function(participant){
			return (participant['lastname'] != null && participant['lastname'].length > 0) ||
				(participant['firstname'] != null && participant['firstname'].length > 0);
		}

    ParticipantSelectorManager.prototype.setParticipant = function(participant){
			var numberPrice = this.numberPrice;
			var serviceParameters = this.serviceParameters;
			if(!this.notAllSynchro){
				numberPrice = _formCtrl.getServiceParametersLinkNumberPrice(this.serviceParameters, this.numberPrice);
				serviceParameters = _formCtrl.getServiceParametersLink(this.serviceParameters);
			}
			if(numberPrice.participants == null){ numberPrice.participants = []; }
      var participantToCopy = numberPrice.participants[this.index];
      if(participantToCopy == null) {
        numberPrice.participants[this.index] = {};
        participantToCopy = numberPrice.participants[this.index];
      }
      for(var key in participant){
        participantToCopy[key] = participant[key];
      }
      _formCtrl.updateCurrentServiceParameters(serviceParameters);
    }

    ParticipantSelectorManager.prototype.close = function() {
      this.customer = null;
      this.booking = null;
      this.opened = false;
    }

		return ParticipantSelectorManager;
	}

	angular.module('resa_app').factory('ParticipantSelectorManager', ParticipantSelectorManagerFactory);
}());
