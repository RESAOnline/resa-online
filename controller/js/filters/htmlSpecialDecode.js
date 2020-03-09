"use strict";
(function(){

	function HtmlSpecialDecode(){
		return function(value, withoutBr){
      if(value != null){
        value = value.replace(new RegExp('&#039;', 'g'),'\'');
        value = value.replace(new RegExp('&quot;', 'g'),'"');
        value = value.replace(new RegExp('&lt;', 'g'),'<');
        value = value.replace(new RegExp('&gt;', 'g'),'>');
        value = value.replace(new RegExp('&amp;', 'g'),'&');
        value = value.replace(new RegExp('\\\\', 'g'),'\\');
        if(withoutBr == null || !withoutBr){
					value = value.replace(new RegExp('<br />', 'g'), '\n');
				}
      }
      return value;
		}
	}

	angular.module('resa_app').filter('htmlSpecialDecode', HtmlSpecialDecode);
}());
