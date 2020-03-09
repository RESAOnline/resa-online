"use strict";
(function(){
	function FormatPhone(){
		return function(phone){
      var newPhone = '';
			if(phone!=null){
	      var size = phone.length / 2;
	      var begin = 0;
	      if(size != Math.floor(size)){
	        newPhone = phone[0];
	        begin = 1;
	      }
	      for(var i = 0; i < size; i++){
	        newPhone += ' ' + phone.substr(begin + (i * 2), 2);
	      }
			}
			return newPhone;
		}
	}
	angular.module('resa_app').filter('formatPhone', FormatPhone);
}());
