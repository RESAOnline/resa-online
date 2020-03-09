"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function GroupController(GroupManager, $scope) {
		GroupManager.call(this, $scope);
		angular.extend(GroupController.prototype, GroupManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('GroupController', GroupController);
}());
