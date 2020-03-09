/****
 * TimePicker
 ****/

"use strict";

(function(){

	//'../wp-content/plugins/resa-online/controller/js/directive/templates/timepicker.html',
	function TimePicker($log){

		return {
			restrict: 'E',
			template: '<span ng-include="templateUrl"></span>',
			scope: {
				time: "=",
				templateUrl: "=",
				hoursClass:"=?",
				minutesClass:"=?",
				onChange:"&"
            },
			link:function (scope, elm, attrs) {
				scope.element = {
					hours : 0,
					minutes : 0
				};
				scope.hstep = 1;
				scope.mstep = 5;

				scope.changeHours = function(value){
					scope.element.hours = scope.element.hours * 1 + value;
					scope.changed();
				}

				scope.changeMinutes = function(value){
					scope.element.minutes = scope.element.minutes * 1 + value;
					scope.changed();
				}

				scope.initialize = function(){
					if(typeof scope.time != 'boolean'){
						var array = scope.time.split(':');
						if(array.length==3){
							scope.element.hours = array[0];
							scope.element.minutes = array[1];
						}
					}

					scope.$watch('time', function(newTime, oldTime){
						if(oldTime != null && newTime!=oldTime && scope.onChange!=null){
							scope.onChange();
							scope.loadTime();
						}
					});


					if(scope.hoursClass == null)
						scope.hoursClass = 'col-md-2 col-sm-2 col-xs-4';
					if(scope.minutesClass == null)
						scope.minutesClass = 'col-md-2 col-sm-2 col-xs-4';
				}

				scope.reload = function(){
					scope.onChange();
					scope.loadTime();
				}

				scope.changed = function(){
					var hours = scope.element.hours * 1;
					var minutes =  scope.element.minutes * 1;
					if(minutes < 0) minutes = 0;
					else if(minutes > 55){
						  minutes = 0;
							hours ++;
					}
					scope.element.minutes = minutes;
					if(hours < 0) hours = 0;
					else if(hours > 23)  hours = 0;
					scope.element.hours = hours;

					if(!isNaN(hours) && !isNaN(minutes)){
						if(hours < 10 && hours>=0)
							hours = '0' + hours;
						if(minutes < 10 && minutes>=0)
							minutes = '0' + minutes;
						scope.time = hours + ':' + minutes + ':00';
					}else if(isNaN(hours)) scope.element.hours = '00';
					else if(isNaN(minutes)) scope.element.minutes = '00';
				}

				scope.loadTime =  function(){
					var array = scope.time.split(':');
					if(array.length==3){
						scope.element.hours = array[0] * 1;
						scope.element.minutes = array[1] * 1;
					}
				}

				scope.initialize();
			}
		}
	}

	angular.module('resa_app').directive('timePicker', TimePicker);
}())
