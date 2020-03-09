"use strict";
(function(){
	
	function FormatTime($filter){
		return function(time, wpFormat){
			var date = new Date();
			var tokens = time.split(':');
			date.setHours(tokens[0]);
			date.setMinutes(tokens[1]);
			date.setSeconds(tokens[2]);
			
			var angularFormat = 'h:mm a';
			if(wpFormat!=null){
				angularFormat = wpFormat;
			}
			var format = $filter('date')(date, angularFormat);
			return format;
		}
	}
	
	angular.module('resa_app').filter('formatTime', FormatTime);
	
}());
