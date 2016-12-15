angular.module('proyecto')

    .directive('numericbinding', function () {
        return {
            restrict: 'A',
            require: 'ngModel',
            scope: {
                model: '=ngModel',
            },                
            link: function (scope, element, attrs, ngModelCtrl) {
               if (scope.model && typeof scope.model == 'string') {
                   scope.model = parseInt(scope.model);
               } 
               scope.$watch('model', function(val, old) {
                   if (typeof val == 'string') {
                       scope.model = parseInt(val);
                   }
               });                 
            }
        };
    })

    .directive('datebinding', function () {
        return {
            restrict: 'A',
            require: 'ngModel',
            scope: {
                model: '=ngModel',
            },                
            link: function (scope, element, attrs, ngModelCtrl) {
               if (scope.model && typeof scope.model == 'string') {
                   scope.model = new Date(scope.model);
               } 
               scope.$watch('model', function(val, old) {
                   if (typeof val == 'string') {
                       scope.model = new Date(val);
                   }
               });                 
            }
        };
    });