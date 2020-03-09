"use strict";

(function(){

	function StorageManagerFactory($log){

		var _naming;
		function StorageManager(naming){
			_naming = naming;
			if(_naming == null)
				_naming = 'default';
		}

		StorageManager.prototype.save = function(object){
			localStorage[_naming] = JSON.stringify(object);
		}

		StorageManager.prototype.load = function(){
			var object = null;
			if(localStorage[_naming] != null){
				object = JSON.parse(localStorage[_naming]);
			}
			return object;
		}

		return StorageManager;
	}


	angular.module('resa_app').factory('StorageManager', StorageManagerFactory)
}());
