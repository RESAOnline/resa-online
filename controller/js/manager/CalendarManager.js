"use strict";

(function(){

	function CalendarManagerFactory($log, $filter, $uibModal, $window, calendarConfig, calendarEventTitle, calendarDateFilter, moment){

		var CalendarManager = function(eventDisplayer){
			this.eventDisplayer = eventDisplayer;
			this.calendarView = 'day';
			this.viewDate = new Date();
			this.events = [];
			this.isCellOpen = true;

	    var originali18n = angular.copy(calendarConfig.i18nStrings);
	    calendarConfig.i18nStrings.weekNumber = 'Semaine {week}';
			calendarConfig.allDateFormats.angular.title.week = 'Semaine {week} de {year}';
			calendarConfig.allDateFormats.angular.date.hour = 'H\'h\'';

	    $window.moment = $window.moment || moment;
	    moment.locale('fr', {
				week: {
					dow: 1 // Monday is the first day of the week
				}
			});
			moment.locale('fr');

			var originalEventTitle = angular.copy(calendarEventTitle);
	 		calendarEventTitle.monthViewTooltip = function(event){
				if(event.type.indexOf('info') != -1 || event.type.indexOf('constraint') != -1){
					return calendarDateFilter(event.startsAt, 'time', true) + ' - ' + event.title;
				}
				return '';
	 		}
		}

		CalendarManager.prototype.groupEvents = function(cell) {
			if(cell.events != null){
				 cell.groups = {};
				 cell.events.forEach(function(event) {
					 cell.groups[event.type] = cell.groups[event.type] || [];
					 cell.groups[event.type].push(event);
				 });
			 }
			 if((this.eventDisplayer==null || typeof this.eventDisplayer.dateAlreadyLoaded !== "function" || !this.eventDisplayer.dateAlreadyLoaded(cell.date))) {
				 cell.cssClass = 'background_stripes';
			 }
		 };

		CalendarManager.prototype.getViewDate = function(){
			return this.viewDate;
		}

		CalendarManager.prototype.isViewMonth = function(){
			return this.calendarView == 'month';
		}

		CalendarManager.prototype.isViewDay = function(){
			return this.calendarView == 'day';
		}

		CalendarManager.prototype.isViewWeek = function(){
			return this.calendarView == 'week';
		}

		CalendarManager.prototype.createNewEvent = function(text, type, startDate, endDate, color, shouldIncrementBadget, actions, dragEnabled){

			calendarConfig.templates.calendarMonthCell = 'groupedMonthEvents.html';
			if(actions == null){
				actions = [];
			}
			startDate = $filter('parseDate')(startDate);
			endDate = $filter('parseDate')(endDate);
			return {
				title: text, // The title of the event
				color: { primary: color, secondary: color},
				type: type,
				startsAt: startDate, // A javascript date object for when the event starts
				endsAt: endDate, // Optional - a javascript date object for when the event ends
				editable: false, // If edit-event-html is set and this field is explicitly set to false then dont make it editable.
				deletable: false, // If delete-event-html is set and this field is explicitly set to false then dont make it deleteable
				draggable: dragEnabled, //Allow an event to be dragged and dropped
				resizable: dragEnabled, //Allow an event to be resizable
				incrementsBadgeTotal: shouldIncrementBadget, //If set to false then will not count towards the badge total amount on the month and year view
				recursOn: '', // If set the event will recur on the given period. Valid values are year or month
				cssClass: 'roundRect', //A CSS class (or more, just separate with spaces) that will be added to the event when it is displayed on each view. Useful for marking an event as selected / active etc
				actions: actions,
			}
		}

		CalendarManager.prototype.setEvents = function(events){
			this.events = events;
		}

		CalendarManager.prototype.eventClicked = function(event) {
			$log.debug('Clicked : ' + JSON.stringify(event));
			this.eventDisplayer.displayEvent(event);
		};

		CalendarManager.prototype.eventEdited = function(event) {
			$log.debug('Edited : ' + JSON.stringify(event));
		};

		CalendarManager.prototype.eventDeleted = function(event) {
			$log.debug('Deleted : ' + JSON.stringify(event));
		};

		CalendarManager.prototype.eventTimesChanged = function(event) {
			$log.debug('Drop or resize : ' + JSON.stringify(event));
			this.eventDisplayer.dropEvent(event);
		};

		CalendarManager.prototype.toggle = function($event, field, event) {
			$log.debug('toggle : ' + JSON.stringify(event));
		  $event.preventDefault();
		  $event.stopPropagation();
		  event[field] = !event[field];
		};

		CalendarManager.prototype.setDraggable = function(draggable){
			for(var i = 0; i < this.events.length; i++){
				this.events[i].draggable = draggable;
			}
		}

		CalendarManager.prototype.removeEventByTitle = function(title){
			var index = -1;
			for(var i = 0; i < this.events.length; i++){
				var event = this.events[i];
				if(event.title == title){
					index = i;
					break;
				}
			}
			if(index != -1){
				this.events.splice(index, 1);
			}
		}

		return CalendarManager;
	}

	angular.module('resa_app').requires.push('mwl.calendar');
	angular.module('resa_app').factory('CalendarManager', CalendarManagerFactory);
}());
