"use strict";
(function(){

	function FormatDateTime($filter){
		return function(date, wpFormat, utc){
			if(date instanceof String || typeof(date) === 'string'){
				date = $filter('parseDate')(date);
			}
			var angularFormat = "dd-MM-yyyy HH:mm:ss";
			if(wpFormat!=null){
				angularFormat = wpFormat;
			}
			var format = '';
			if(utc){
				format = $filter('date')(date, angularFormat, '+0000');
			}
			else format = $filter('date')(date, angularFormat);
			return format;
		}
	}

	angular.module('resa_app').filter('formatDateTime', FormatDateTime);

}());
