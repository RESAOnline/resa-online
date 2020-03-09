"use strict";

(function(){

	function PlanningServiceController(PlanningServiceManager, $scope) {
		PlanningServiceManager.call(this, $scope);
		angular.extend(PlanningServiceController.prototype, PlanningServiceManager.prototype);
	}

	angular.module('resa_app').controller('PlanningServiceController', PlanningServiceController);
}());
