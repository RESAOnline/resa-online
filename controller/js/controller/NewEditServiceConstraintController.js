"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NewEditServiceConstraintController(NewEditServiceConstraintManager, $scope) {
		NewEditServiceConstraintManager.call(this, $scope);
		angular.extend(NewEditServiceConstraintController.prototype, NewEditServiceConstraintManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NewEditServiceConstraintController', NewEditServiceConstraintController);
}());
