"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NotificationController(NotificationManager, $scope) {
		NotificationManager.call(this, $scope);
		angular.extend(NotificationController.prototype, NotificationManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NotificationController', NotificationController);
}());
