"use strict";
(function(){

	function Negative(){
		return function(value){
			if(value > 0)
				value *= -1;
			return value;
		}
	}


	angular.module('resa_app').filter('negative', Negative);
}());
