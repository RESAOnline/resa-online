"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NewAttachBookingController(NewAttachBookingManager, $scope) {
		NewAttachBookingManager.call(this, $scope);
		angular.extend(NewAttachBookingController.prototype, NewAttachBookingManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NewAttachBookingController', NewAttachBookingController);
}());
