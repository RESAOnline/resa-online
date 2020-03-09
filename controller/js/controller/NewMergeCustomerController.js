"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NewMergeCustomerController(NewMergeCustomerManager, $scope) {
		NewMergeCustomerManager.call(this, $scope);
		angular.extend(NewMergeCustomerController.prototype, NewMergeCustomerManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NewMergeCustomerController', NewMergeCustomerController);
}());
