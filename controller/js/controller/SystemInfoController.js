"use strict";

(function(){


	function SystemInfoController(SystemInfoManager, $scope) {
		SystemInfoManager.call(this, $scope);
		angular.extend(SystemInfoController.prototype, SystemInfoManager.prototype);
	}


	angular.module('resa_app').controller('SystemInfoController', SystemInfoController);
}());
