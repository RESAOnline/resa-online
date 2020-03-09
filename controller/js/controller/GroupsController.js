"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function GroupsController(GroupsManager, $scope) {
		GroupsManager.call(this, $scope);
		angular.extend(GroupsController.prototype, GroupsManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('GroupsController', GroupsController);
}());
