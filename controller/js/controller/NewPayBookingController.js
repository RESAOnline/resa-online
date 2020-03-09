"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NewPayBookingController(NewPayBookingManager, $scope) {
		NewPayBookingManager.call(this, $scope);
		angular.extend(NewPayBookingController.prototype, NewPayBookingManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NewPayBookingController', NewPayBookingController);
}());
