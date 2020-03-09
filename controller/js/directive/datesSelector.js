/****
 * datetimepicker
 ****/

"use strict";

(function(){
	//'../wp-content/plugins/resa-online/controller/js/directive/templates/datesSelector.html'

	function DatesSelector($log, $http, $filter, $timeout){

		return {
			restrict: 'E',
			template: '<div ng-include="templateUrl"></div>',
			scope: {
				templateUrl:"=",
        dates:'=',
				onChange: "&"
      },
			link:function (scope, element, attrs) {

				function getDayClass(data){
		      var date = data.date, mode = data.mode;
		      var stringDate = $filter('formatDateTime')(date, 'yyyy-MM-dd');
		      if (mode === 'day' && scope.model.localDates.indexOf(stringDate) != -1) {
		        return 'full';
		      }
		      return '';
		    }

				scope.clickOnSave = false;
        scope.model = {
          date: null,
          selected: true,
          lastDate: new Date(),
          datepicker_options : {
            showWeeks: true,
            customClass: getDayClass,
            datepickerMode: 'day'
          },
					changed: false,
          localDates: []
        };

        scope.initialize = function(){
          if(scope.dates.length > 0){
            scope.model.localDates = scope.dates.split(',');
						var currentDate = new Date();
						var stringDate = $filter('formatDateTime')(currentDate, 'yyyy-MM-dd');
						if(scope.model.localDates.indexOf(stringDate) == -1 && scope.model.localDates.length > 0){
							for(var i = 0; i < scope.model.localDates.length; i++){
								var date = new Date(scope.model.localDates[i]);
								if(date.getTime() > currentDate.getTime()){
									scope.model.date = new Date(date);
									break;
								}
							}
						}
          }
        }

        scope.$watch('model.date', function(newValue, oldValue){
          if(newValue != oldValue && newValue != null){
            scope.switchDate(newValue);
            scope.model.lastDate = new Date(newValue);
            $timeout(function(){ scope.model.date = null; }, 100);
          }
        });

				scope.$watch('dates', function(newValue, oldValue){
          if(newValue != oldValue && newValue != null && !scope.clickOnSave){
            scope.initialize();
          }
					scope.clickOnSave = false;
        });

        /**
         * remove or add date in dates
         */
        scope.switchDate = function(date){
          var stringDate = $filter('formatDateTime')(date, 'yyyy-MM-dd');
          var index = scope.model.localDates.indexOf(stringDate);
          if(index == -1 && scope.model.selected){
            scope.model.localDates.push(stringDate);
						scope.model.changed = true;
          }
          else if(index != -1 && !scope.model.selected){
            scope.model.localDates.splice(index, 1);
						scope.model.changed = true;
          }
          //scope.dates = scope.model.localDates.toString();
        }

        /**
         * Select all days
         */
        scope.selectAllDaysOnThisMounth = function(select){
          var month = scope.model.lastDate.getMonth();
          var date = new Date(scope.model.lastDate.getFullYear(), month, 1);
          while(date.getMonth() === month)  {
            scope.switchDate(date);
            date.setDate(date.getDate() + 1);
          }
          scope.model.date = scope.model.lastDate;
        }

        scope.selectAllWeekEndOnThisMounth = function(select){
          var month = scope.model.lastDate.getMonth();
          var date = new Date(scope.model.lastDate.getFullYear(), month, 1);
          while(date.getMonth() === month)  {
            if(date.getDay() == 0 || date.getDay() == 6){
              scope.switchDate(date);
            }
            date.setDate(date.getDate() + 1);
          }
          scope.model.date = scope.model.lastDate;
        }

        scope.clearAll = function(){
          scope.model.localDates = [];
        }

        scope.save = function(){
					scope.model.changed = false;
          scope.dates = scope.model.localDates.toString();
					scope.clickOnSave = true;
          scope.onChange();
        }

        scope.initialize();
			}
		}
	}

	angular.module('resa_app').directive('datesSelector', DatesSelector);
}())
