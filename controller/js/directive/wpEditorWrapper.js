/****
 * Wp Editor Wrapper.js
 ****/

"use strict";

(function(){

	var _instance = null;
	var _tinyMCEFocused = false;
	var _textarea = null;

	function WpEditorWrapper($log, $timeout){
		return {
			restrict: 'E',
			template: '',
			scope: {
				id: "=",
				ngModel: "=",
				ngChange: "&?"
      },
			link:function (scope, element, attrs) {
				scope.setNgModel = function(value){
					scope.$apply(function(){
						scope.ngModel = value;
						if(scope.ngChange) scope.ngChange();
					});
				}

				scope.$watch('ngModel', function(newValue, oldValue){
					if(newValue != null){
						if(_instance != null && !_tinyMCEFocused) {
							_instance.setContent(newValue);
						}
						if(_textarea != null && !_textarea.is(':focus')){
							_textarea.val(newValue);
						}
					}
				});

				scope.initialize = function(){
					scope.initTinyMCE();
					scope.initTextarea();
					jQuery('#' + scope.id + '-tmce').on('click',function(){
						scope.tryInitTinyMCE();
					});
					jQuery('#' + scope.id + '-html').on('click',function(){
						scope.initTextarea();
					});
				}

				scope.initTextarea = function(){
					//if(!_textarea){
						_textarea = jQuery('textarea[name=' + scope.id + ']');
						if(_textarea){
							_textarea.val(scope.ngModel);
							_textarea.on('input change', function() {
								scope.setNgModel(_textarea.val());
							});
						}
				//	}
				}

				scope.initTinyMCE = function(setContent){
					//if (!_instance) {
						_instance = tinymce.get(scope.id);
						if (_instance) {
							if((setContent == null || setContent) && scope.ngModel) {
								_instance.setContent(scope.ngModel);
							}
							_instance.on('KeyUp', function() {
								scope.setNgModel(_instance.getContent());
							});
							_instance.on('ExecCommand', function() {
								scope.setNgModel(_instance.getContent());
							});
							_instance.on('change', function() {
								scope.setNgModel(_instance.getContent());
							});
							_instance.on('focus', function(e) {
								_tinyMCEFocused = true;
			        });
			        _instance.on('blur', function(e) {
								_tinyMCEFocused = false;
			        });
						}
					//}
				}

				scope.tryInitTinyMCE = function(){
					this.initTinyMCE(false);
					if (!_instance) {
						$timeout(function(){
							scope.tryInitTinyMCE();
						}, 500);
					}
				}

				scope.initialize();
			}
		};
	}

	angular.module('resa_app').directive('wpEditorWrapper', WpEditorWrapper);
}())
