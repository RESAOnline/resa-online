"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NewAskPaymentController(NewAskPaymentManager, $scope) {
		NewAskPaymentManager.call(this, $scope);
		angular.extend(NewAskPaymentController.prototype, NewAskPaymentManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NewAskPaymentController', NewAskPaymentController);
}());
