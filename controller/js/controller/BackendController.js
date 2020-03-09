"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function BackendController(BackendManager, $scope) {
		BackendManager.call(this, $scope);
		angular.extend(BackendController.prototype, BackendManager.prototype);

    _self = this;
    _scope = $scope;
		this.displayCurrentPage();
		this.displayCurrentView();
	}

	angular.module('resa_app').controller('BackendController', BackendController);
}());
