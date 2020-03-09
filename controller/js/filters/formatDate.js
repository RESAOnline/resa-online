"use strict";
(function(){

	function FormatDate($filter){

		return function(date, wpFormat){
			if(date instanceof String || typeof(date) === 'string'){
				date = $filter('parseDate')(date);
			}
			var angularFormat = 'dd-MM-yyyy';
			if(wpFormat!=null){
				angularFormat = wpFormat;
			}
			var format = $filter('date')(date, angularFormat);
			return format;
		}
	}



	angular.module('resa_app').filter('formatDate', FormatDate);

}());
