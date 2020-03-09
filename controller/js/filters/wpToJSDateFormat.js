"use strict";
(function(){
	
	function wpToJSDateFormat(){
		return function(wpFormat){
			var angularFormat = wpFormat;
			angularFormat = angularFormat.replace(new RegExp('F', 'g'),'MMMM');
			angularFormat = angularFormat.replace(new RegExp('j', 'g'),'d');
			angularFormat = angularFormat.replace(new RegExp('Y', 'g'),'yyyy');
			angularFormat = angularFormat.replace(new RegExp('g', 'g'),'h');
			angularFormat = angularFormat.replace(new RegExp('i', 'g'),'mm');
			return angularFormat;
		}
	}
	
	angular.module('resa_app').filter('wpToJSDateFormat', wpToJSDateFormat);
	
}());
