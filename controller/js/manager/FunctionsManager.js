/**
 * Global function for many managers
 */
"use strict";

(function(){
	function FunctionsManagerFactory($filter, $location, $anchorScroll){
    var FunctionsManager = function(){

    }

		FunctionsManager.prototype.getRepeat = function(num){
			return new Array(num);
		}

		FunctionsManager.prototype.cloneJSON = function(value){
			return JSON.parse(JSON.stringify(value));
		}


		FunctionsManager.prototype.htmlSpecialDecode = function(value, withoutBr){
			if(withoutBr == null){
				withoutBr = false;
			}
			return $filter('htmlSpecialDecode')(value, withoutBr);
		}

		FunctionsManager.prototype.htmlSpecialDecodeArray = function(array){
			for(var index in array) {
				array[index] =  this.htmlSpecialDecode(array[index]);
			}
		}

    FunctionsManager.prototype.getTitle = function(title, defaultTitle){
			if(title != '' && title != null) return title;
			return defaultTitle;
		}

		FunctionsManager.prototype.getTextByLocale = function(object, locale){
			var result = '';
			if(object != null){
				result = object[locale];
				if((result == null || result == '') && typeof object === "object" && object !== null){
					var languages = Object.keys(object);
					if(languages.length > 0){
              for(var i = 0; i < languages.length; i++){
                result = object[languages[i]];
                if(result != null && result != '')
                  break;
              }
					}
					else result = '';
				}
			}
			return result;
		}

		FunctionsManager.prototype.displayParticipant = function(participantFields, participant, locale){
			var result = '';
			if(participant != null && participantFields != null){
				for(var i = 0; i < participantFields.length; i++){
					if(result != '') result += ' ';
					if(participant[participantFields[i].varname] != null){
						if(participantFields[i].prefix!=null){
							result += this.getTextByLocale(participantFields[i].prefix, locale);
						}
						result += participant[participantFields[i].varname];
						if(participantFields[i].suffix!=null){
							result += this.getTextByLocale(participantFields[i].suffix, locale);
						}
					}
				}
			}
			return result;
		}

		FunctionsManager.prototype.displayParticipants = function(participantFields, participants, locale) {
			var result = '';
	  	for(var i = 0; i < participants.length; i++){
				var participant = participants[i];
				result += this.displayParticipant(participantFields, participant, locale) + '<br />';
			}
			return result;
	  }

		FunctionsManager.prototype.scrollTo = function(id) {
	     var el = document.getElementById(id);
			 el.scrollIntoView();
	  }

		FunctionsManager.prototype.parseDate = function(date){
			return $filter('parseDate')(date);
		}

		FunctionsManager.prototype.clearTime = function(date){
	    date.setHours(0);
	    date.setMinutes(0);
	    date.setSeconds(0);
	    date.setMilliseconds(0);
	    return date;
	  }

		/**
		 * format of string time = HH:mm
		 */
		FunctionsManager.prototype.setTimeWithStringTime = function(date, stringTime){
			var tokens = stringTime.split(':');
			if(tokens.length == 2){
				date.setHours(tokens[0]);
				date.setMinutes(tokens[1]);
		    date.setSeconds(0);
		    date.setMilliseconds(0);
			}
			return date;
		}

		/**
		 */
		FunctionsManager.prototype.isSameDay = function(date1, date2){
			return date1.getFullYear() == date2.getFullYear() &&
							date1.getMonth() == date2.getMonth() &&
											date1.getDate() == date2.getDate();
		}

    return FunctionsManager;
  }
  angular.module('resa_app').factory('FunctionsManager', FunctionsManagerFactory);
}());
