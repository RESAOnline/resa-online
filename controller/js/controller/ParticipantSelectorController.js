"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function ParticipantSelectorController(ParticipantSelectorManager, $scope) {
		ParticipantSelectorManager.call(this, $scope);
		angular.extend(ParticipantSelectorController.prototype, ParticipantSelectorManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('ParticipantSelectorController', ParticipantSelectorController);
}());
