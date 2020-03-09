"use strict";

(function(){

	function AskAccountController(AskAccountManager, $scope) {
		AskAccountManager.call(this, $scope);
		angular.extend(AskAccountController.prototype, AskAccountManager.prototype);
	}

	angular.module('resa_app').controller('AskAccountController', AskAccountController);
}());
