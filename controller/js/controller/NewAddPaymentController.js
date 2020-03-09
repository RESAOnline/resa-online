"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NewAddPaymentController(NewAddPaymentManager, $scope) {
		NewAddPaymentManager.call(this, $scope);
		angular.extend(NewAddPaymentController.prototype, NewAddPaymentManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NewAddPaymentController', NewAddPaymentController);
}());
