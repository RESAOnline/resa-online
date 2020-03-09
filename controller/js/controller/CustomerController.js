"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function CustomerController(CustomerManager, $scope) {
		CustomerManager.call(this, $scope);
		angular.extend(CustomerController.prototype, CustomerManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('CustomerController', CustomerController);
}());
