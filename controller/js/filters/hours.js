"use strict";
(function(){

	function Hours($filter){		
		return function(date){
			if(date instanceof String || typeof(date) === 'string'){
				date = $filter('parseDate')(date);
			}
			var format = '';
			if(date!=null && date instanceof Date){
				format = date.getHours();
			}
			else format = date;
			return format;
		}
	}



	angular.module('resa_app').filter('hours', Hours);

}());
