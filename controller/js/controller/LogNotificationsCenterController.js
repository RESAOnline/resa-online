"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function LogNotificationsCenterController(LogNotificationsCenterManager, $scope) {
		LogNotificationsCenterManager.call(this, $scope);
		angular.extend(LogNotificationsCenterController.prototype, LogNotificationsCenterManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('LogNotificationsCenterController', LogNotificationsCenterController);
}());
