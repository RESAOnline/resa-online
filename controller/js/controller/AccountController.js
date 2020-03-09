"use strict";

(function(){

	function AccountController(AccountManager, $scope) {
		AccountManager.call(this, $scope);
		angular.extend(AccountController.prototype, AccountManager.prototype);
	}

	angular.module('resa_app').controller('AccountController', AccountController);
}());
