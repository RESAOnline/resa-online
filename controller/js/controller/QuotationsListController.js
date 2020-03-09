"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function QuotationsListController(QuotationsListManager, $scope) {
		QuotationsListManager.call(this, $scope);
		angular.extend(QuotationsListController.prototype, QuotationsListManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('QuotationsListController', QuotationsListController);
}());
