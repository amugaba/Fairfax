/**
 * Created by David on 2/20/2016.
 */
'use strict';

var myApp = angular.module('TestApp',[]);

myApp.controller('TestController', ['$scope', function($scope) {
    $scope.greeting = 'Hola!';
}]);