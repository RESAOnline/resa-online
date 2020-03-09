"use strict";

(function(){

	var _self = null;
  var _scope = null;

	function NewEditInfoCalendarController(NewEditInfoCalendarManager, $scope) {
		NewEditInfoCalendarManager.call(this, $scope);
		angular.extend(NewEditInfoCalendarController.prototype, NewEditInfoCalendarManager.prototype);

    _self = this;
    _scope = $scope;
	}

	angular.module('resa_app').controller('NewEditInfoCalendarController', NewEditInfoCalendarController);
}());
