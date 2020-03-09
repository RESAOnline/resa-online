"use strict";
(function(){

	function ParseDate($filter){
		return function(date){
			if(date instanceof String || typeof(date) === 'string'){
        var split = date.split(' ');
        if(split.length == 2){
          var date = split[0].split('-');
          date[1] = date[1] * 1 - 1;
          var time = split[1].split(':');
          //time[0] = time[0] * 1 + 1;
  				date = new Date(date[0], date[1], date[2], time[0], time[1], time[2], 0);
        }
				else if(date.split('-').length == 3 && date.length == 10){
					var date = date.split('-');
					date = new Date(date[0], (date[1] * 1) - 1, date[2] * 1);
				}
				else {
					date = new Date(date);
				}
			}
			return date;
		}
	}
	angular.module('resa_app').filter('parseDate', ParseDate);
}());
