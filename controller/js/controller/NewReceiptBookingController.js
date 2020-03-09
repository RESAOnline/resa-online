"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NewReceiptBookingController(NewReceiptBookingManager, $scope) {
		NewReceiptBookingManager.call(this, $scope);
		angular.extend(NewReceiptBookingController.prototype, NewReceiptBookingManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NewReceiptBookingController', NewReceiptBookingController);
}());
