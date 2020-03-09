"use strict";

/** 
 * This manager watch a value, if this value change and if the user close the tab
 * this manager call the callback.
 */
(function(){

	function ChangeDetectionManagerFactory($log, $window){
		
		var _notFirstTime = false;
		var _callback = null;
		var _variable = null;
		var _variableHaveChanged = false;
		var _oldWindowTitle = '';
				
		var _ChangeTitleFrame = function(){
			if(!_variableHaveChanged){
				_oldWindowTitle =  $window.document.title;
				$window.document.title = '* ' + $window.document.title;
				_variableHaveChanged = true;
				$window.onbeforeunload = function(){ return '' };
			}
		}
		
		var _ResetTitle = function(){
			if(_variableHaveChanged){
				$window.document.title = _oldWindowTitle;
				_variableHaveChanged = false;
				$window.onbeforeunload = null;
			}
		}
		
		var ChangeDetectionManager = function(){
			
		}
		
		//variables is variableName;
		ChangeDetectionManager.prototype.changeTitle = function(scope, variable){
			_ChangeTitleFrame();
		}
		
		ChangeDetectionManager.prototype.resetTitle = function(callback){
			_ResetTitle();
		}
		
		return ChangeDetectionManager;
	}
	
	angular.module('resa_app').factory('ChangeDetectionManager', ChangeDetectionManagerFactory);
}());