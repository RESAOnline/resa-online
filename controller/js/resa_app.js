if(resa_app == null){
  var resa_app = angular.module('resa_app',['ngAnimate','ngSanitize', 'ui.bootstrap']).
  config(['$locationProvider', function($locationProvider) { $locationProvider.html5Mode({ enabled: true, requireBase: false }); }]);
}
