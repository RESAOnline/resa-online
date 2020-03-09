"use strict";

(function(){
  function RESAFacebookLoginFactory($filter) {
    var RESAFacebookLogin = function(){

    }

    RESAFacebookLogin.prototype.isCustomerFacebookConnected = function(){ return false; }

    return RESAFacebookLogin;
  }
  angular.module('resa_app').factory('RESAFacebookLogin', RESAFacebookLoginFactory);
}());
