"use strict";
(function(){

  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  function formatMessage(currentMonthsYears, formatDate){
    var result = '';
    for(var j = 0; j < currentMonthsYears.days.length; j++){
      var day = currentMonthsYears.days[j];
      result += ((j>0)?', ':'') + day;
    }
    var dateFormated = capitalizeFirstLetter(formatDate((new Date(currentMonthsYears.years, currentMonthsYears.month-1)),'MMMM yyyy', 'fr'));
    result += ' ' + dateFormated + '<br />';
    return result;
  }

	function FormatManyDatesView($filter){
		return function(dates){
      var result = '';
      if(dates != null){
        var exploded = dates.split(',');
        var currentMonthsYears = null;
        for(var i = 0; i < exploded.length; i++){
          var date = exploded[i];
          var tokens = date.split('-');
          if(currentMonthsYears == null){
            currentMonthsYears = {years:tokens[0], month:tokens[1], days:[tokens[2]]};
          }
          else {
            if(currentMonthsYears.month == tokens[1]){
              currentMonthsYears.days.push(tokens[2]);
            }
            else {
              result += formatMessage(currentMonthsYears, $filter('date'));
              currentMonthsYears = {years:tokens[0], month:tokens[1], days:[tokens[2]]};
            }
          }
        }
        if(currentMonthsYears.days.length > 0){
          result += formatMessage(currentMonthsYears, $filter('date'));
        }
      }
      return result;
		}
	}

	angular.module('resa_app').filter('formatManyDatesView', FormatManyDatesView);
}());
