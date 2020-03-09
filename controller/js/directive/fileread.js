/****
 *
 ****/

"use strict";

(function(){

	function FileRead(){

		return {
			scope: {
        fileread: "="
      },
			link:function (scope, element, attrs) {
         element.bind("change", function (changeEvent) {
          scope.$apply(function () {
            scope.fileread = changeEvent.target.files[0];
          });
        });
      }
		}
	}

	angular.module('resa_app').directive('fileread', FileRead);
}())
