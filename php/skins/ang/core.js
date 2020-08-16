// public/core.js
var app = angular.module('rprjApp', ['ngRoute']);

app.run(function($rootScope) {
    $rootScope.cippa='lippa';
});

// app.config(function($routeProvider) {
//   $routeProvider
//   .when("/", {
//     templateUrl: "main.html"
//     , controller: "psCtrl"
//   })
//   .when("/profile", {
//     templateUrl: "profile.html"
//   })
//   .when("/nodes", {
//     templateUrl: "nodes.html"
//   })
//   .when("/users", {
//     templateUrl: "users.html"
//   });
// });

app.controller('mainCtrl', function($scope, $http, $interval) {
//     $scope.formData = {};
//     $scope.current_obj = {'name': 'Test'};
    $scope.mypath = window.location.pathname.replace('main.php','');
    
    console.log("url: " +this);
    
    $http.get('api.php')
        .then(function mySuccess(response) {
            $scope.myui = response.data;
            console.log($scope.myui);
        }, function myError(response) {
            $scope.pmgrError = response;
            console.log('Error: ' + response.data);
        });

//     $scope.changeNode = function(node) {
//         $http.put('/api/ui', {node: node})
//             .then(function mySuccess(response) {
//                 $scope.myui = response.data;
//                 console.log(response.data);
//             }, function myError(response) {
//                 $scope.pmgrError = response.data;
//                 console.log('Error: ' + response.data);
//             });
//     };

    // **** Ping Backend ****
//     var list_name='#robots-list';
//     var refreshPing;
//     $scope.backendOnline = true;
//     $scope.startPing = function() {
//         if(!angular.isDefined(refreshPing)) {
//             refreshPing = $interval(function() {
//                 $http.get('/api/ping')
//                     .then(function mySuccess(response) {
//                         $scope.backendOnline = true;
// //                         console.log(response);
//                     }, function myError(response) {
//                         $scope.backendOnline = false;
// //                         console.log("ERROR");
//                     });
// //                 console.log(angular.element(list_name))
// //                 alert(angular.element(list_name).length);
//             }, 5*1000);
//         };
//     };
//     $scope.stopPing = function() {
//         if(angular.isDefined(refreshPing)) {
//             $interval.cancel(refreshPing);
//             refreshPing = undefined;
//         }
//     };
//     $scope.$watch(function() { return angular.element(list_name) }, function(el) {
//         $scope.startPing();
//     });
});

