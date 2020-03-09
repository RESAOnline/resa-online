"use strict";
(function(){
	
	function DisplayNewLines(){
		return function(value){
			if(value == null) return '';
			return value.replace(/\n/g, '<br \>');
		}
	}

	angular.module('resa_app').filter('displayNewLines', DisplayNewLines);
}());
