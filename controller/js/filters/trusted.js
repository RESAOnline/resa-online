"use strict";
(function(){
	
	function trusted($sce){
		return function(html){
            return $sce.trustAsHtml(html);
        }
	}
	
	angular.module('resa_app').filter('trusted', trusted);
	
}());
