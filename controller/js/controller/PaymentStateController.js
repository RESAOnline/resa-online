"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function PaymentStateController(PaymentStateManager, $scope) {
		PaymentStateManager.call(this, $scope);
		angular.extend(PaymentStateController.prototype, PaymentStateManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('PaymentStateController', PaymentStateController);
}());
