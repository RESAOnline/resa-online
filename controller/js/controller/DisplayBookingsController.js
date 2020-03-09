"use strict";

(function(){

	function DisplayBookingsController(DisplayBookingsManager, $scope) {
		DisplayBookingsManager.call(this, $scope);
		angular.extend(DisplayBookingsController.prototype, DisplayBookingsManager.prototype);
	}

	angular.module('resa_app').controller('DisplayBookingsController', DisplayBookingsController);
}());
