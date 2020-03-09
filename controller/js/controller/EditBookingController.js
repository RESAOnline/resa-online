"use strict";

(function(){

  var _self = null;
  var _scope = null;

  function EditBookingController(EditBookingManager, $scope) {
    EditBookingManager.call(this, $scope);
    angular.extend(EditBookingController.prototype, EditBookingManager.prototype);


    _self = this;
    _scope = $scope;
  }



  angular.module('resa_app').controller('EditBookingController', EditBookingController);
}());
