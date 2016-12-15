var proyecto = angular.module('proyecto',['ui.router','satellizer','angularFileUpload','ngMap','nvd3','angularSpinner','ngGeolocation']);
proyecto.config(function ($stateProvider, $urlRouterProvider,$authProvider){

 
$authProvider.loginUrl = 'http://donadojuanpizzeria.hol.es/servidor/php/auth.php'
$authProvider.tokenName = "miToken"
$authProvider.tokenPrefix = "pizzeria"
$authProvider.authHeader = "data"

  $stateProvider

      .state('inicio', {
                url : '/inicio',
                templateUrl : 'vistas/main.html',
                controller: 'loginCtrl',
                resolve:{
                    test: function(loginS){
                            loginS.checkLogin();
                          }
                }

            })

      .state('locales', {
                url : '/locales',
                templateUrl : 'vistas/locales.html',
                controller: 'localesCtrl',
                resolve:{
                    test: function(loginS){
                            loginS.checkLogin();
                          }
                }
            })

      .state('estadisticas', {
                url : '/estadisticas',
                templateUrl : 'vistas/estadisticas.html',
                controller: 'estadisticasCtrl',
                resolve:{
                    test: function(loginS){
                            loginS.checkLogin();
                          }
                }
            })

      .state('navbar', {
                url : '/navbar/:id',
                abtract: true, 
                templateUrl : 'vistas/navbarLocal.html',
                controller: 'navbarCtrl', 
                resolve:{
                    test: function(loginS){
                            loginS.checkLogin();
                          }
                }               
            })
  
      .state('navbar.pedidos', {
                url: '/pedidos',
                views: {
                    'contenido': {
                        templateUrl: 'vistas/pedidos.html',
                        controller: 'pedidosCtrl'
                    }
                }
            })  
      .state('navbar.ofertas', {
                url: '/ofertas',
                views: {
                    'contenido': {
                        templateUrl: 'vistas/ofertas.html',
                        controller: 'ofertasCtrl'
                    }
                }
            })

      .state('navbar.productos', {
                url: '/productos',
                views: {
                    'contenido': {
                        templateUrl: 'vistas/productos.html',
                        controller: 'productosCtrl'
                    }
                }
            })

      .state('navbar.usuarios', {
                url: '/usuarios',
                views: {
                    'contenido': {
                        templateUrl: 'vistas/usuarios.html',
                        controller: 'usuariosCtrl'
                    }
                }
            })

      .state('navbar.infoLocal', {
                url: '/infoLocal',
                views: {
                    'contenido': {
                        templateUrl: 'vistas/infoLocal.html',
                        controller: 'infoLocalCtrl'
                    }
                }
            })
   
   $urlRouterProvider.otherwise('/inicio');
})

.controller('localesCtrl',function($scope,localS,loginS){
    $scope.locales = {};
    $scope.localForm = {};
    $scope.acceso = loginS.acceso();
    $scope.uploader = localS.retornarUploader();
    listadoLocales();

    function listadoLocales(){
     localS.listado().then(function(res){
        console.info('locales',res);
        $scope.locales = res;
     });

    }

    $scope.altaLocal = function (objectLocal){
        localS.altaMod(objectLocal).then(function(idLocal){
            localS.subirFotos(idLocal);
            $('#altaLocal').modal('hide');
        });
    }

    $scope.uploader.onCompleteAll = function(){
        $scope.uploader.clearQueue();
        $scope.localForm = {};
        listadoLocales();
    }

    $scope.logout = function(){
        loginS.logout();
    }

    
})

.controller('productosCtrl',function($scope,prodS,loginS,$window,usSpinnerService){ 

    $scope.acceso = loginS.acceso();
    $scope.productoForm = {};
    $scope.modForm = {};
    $scope.exitop = true;
    $scope.uploader = prodS.retornarUploader();
    $scope.modUploader = prodS.retornarModUploader();
    listadoProd();

    function listadoProd(){
        prodS.listado().then(function(res){
            $scope.productos = res;
        });
    }

    $scope.eliminarProd = function (idProd){
        usSpinnerService.spin('spinner');
        prodS.eliminar(idProd).then(function(res){
            usSpinnerService.stop('spinner');
            $scope.exitop = res.valor;
            if(res.valor)        
                listadoProd();
        });
    }

    $scope.altaMod = function (objectProd){
        usSpinnerService.spin('spinner');
        prodS.altaMod(objectProd).then(function(idProd){
            prodS.subirFotos(idProd);
            $scope.productoForm = {};
            $scope.modForm = {};
            $('#modProd').modal('hide');
        });    
    }

    $scope.modoMod = function(producto){
        $scope.modForm = JSON.parse(JSON.stringify(producto));
    }

    $scope.uploader.onCompleteAll = function(){
        usSpinnerService.stop('spinner');    
        listadoProd();
        $scope.uploader.clearQueue();
    }

    $scope.modUploader.onCompleteAll = function(){
        $window.location.reload();
        $scope.modUploader.clearQueue();
    } 

})

.controller('ofertasCtrl',function($scope,loginS,prodS,oferS,usSpinnerService){

    $scope.acceso = loginS.acceso();
    $scope.ofertaForm = {};
    $scope.exitof = true;
    $scope.modForm = {};

    ListadOfertas();
    cargarProductos();

     function ListadOfertas(){
       oferS.listado().then(function(res){
            $scope.ofertas = res;
       });
     }

     function cargarProductos(){
        prodS.listado().then(function(res){
            $scope.productos = res;
        });
     }

     $scope.altaMod = function(ofertaForm){
        usSpinnerService.spin('spinner');
        console.info('ofertaForm',ofertaForm);
        oferS.altaMod(ofertaForm).then(function(res){
            usSpinnerService.stop('spinner');
            ListadOfertas();
            $scope.ofertaForm = {};
            $('#modOfer').modal('hide');
        });
     }

    $scope.modoMod = function(oferta){
        $scope.modForm = JSON.parse(JSON.stringify(oferta));
    }

     $scope.eliminarOfer = function(idOferta){
        usSpinnerService.spin('spinner');
        oferS.eliminar(idOferta).then(function(res){
            usSpinnerService.stop('spinner');
            console.info('DEL OFER', res);
            $scope.exitof = res.valor;
            if(res.valor)
                ListadOfertas();
        });
     }
})

.controller('pedidosCtrl',function($scope,prodS,oferS,pedS,usuS,loginS,usSpinnerService){

    $scope.acceso = loginS.acceso();
    $scope.pedidoForm = {};
    $scope.modForm = {};
    $scope.encuestaForm = {};
    $scope.pedidoForm.total = 0;
    $scope.modForm.total = 0;
    $scope.date = pedS.fechaLimite();
    cargarInfo();

    $scope.altaEnc = function(encuesta){
        console.info('encuesta',encuesta);
        pedS.altaMod(encuesta).then(function(res){
            $('#encForm').modal('hide');
        });
    }

    $scope.altaMod = function(pedidoForm){
        usSpinnerService.spin('spinner');
        pedidoForm.estado = "no entregado";

        if(!$scope.acceso.emp){
            pedidoForm.idCliente = $scope.acceso.id;
        }
        pedS.altaMod(pedidoForm).then(function(res){
            usSpinnerService.stop('spinner');
            cargarInfo();
            $scope.pedidoForm = {};
            $('#modForm').modal('hide');
        });
    }

    $scope.cambiarEstado = function(pedido){
        pedS.cambiarEstado(pedido).then(function(res){
            console.info('cambio estado',res);
            cargarInfo();
        });
    }

    $scope.modoMod = function(pedido){
        $scope.modForm = JSON.parse(JSON.stringify(pedido));
    }

    $scope.eliminarPed = function(idPedido){
        usSpinnerService.spin('spinner');
        pedS.eliminar(idPedido).then(function(res){
            usSpinnerService.stop('spinner');
            cargarInfo();
        });
    }

    $scope.calcularTotal = function(pedidoForm){
        $scope.pedidoForm.total = pedS.calcularTotal(pedidoForm);
        $scope.modForm.total = pedS.calcularTotal(pedidoForm);
    }

    function cargarInfo(){
        $scope.tituloForm = "Alta";
        prodS.listado().then(function(res){
            $scope.productos = res;
        });
        oferS.listado().then(function(res){
            $scope.ofertas = res;
       });
       pedS.listado().then(function(res){
            $scope.pedidos = res;
        });
       usuS.listado().then(function(res){
            $scope.clientes = res;
       });
    }

})

.controller('infoLocalCtrl',function($scope,$window,localS,loginS,$state){
    $scope.acceso = loginS.acceso();
    $scope.local = {};
    $scope.localForm = {};
    $scope.uploader = localS.retornarUploader();
    cargarLocal();

    function cargarLocal(){
        localS.cargarLocal().then(function(local){
            $scope.local = local;
        });

        localS.coordenadasUsu().then(function(coords){
            $scope.coords = coords;
        });
    }

    $scope.modLocal = function(localForm){
        localS.altaMod(localForm).then(function(res){
            localS.subirFotos(localForm.idLocal);
        });
    }

    $scope.eliminarLocal = function(){
        localS.eliminar().then(function(res){
            console.info('eliminarLocal',res);
            $state.go('locales');
        });
    }

    $scope.uploader.onCompleteAll = function(){
        $scope.uploader.clearQueue();
        $window.location.reload();
    }

    $scope.modoMod = function(local){
        $scope.localForm = JSON.parse(JSON.stringify(local));
    }

})

.controller('usuariosCtrl',function($scope,usuS,loginS,usSpinnerService){

    $scope.acceso = loginS.acceso();
    $scope.usuarioForm = {};
    $scope.modForm = {};

    cargarUsuarios();

    function cargarUsuarios(){
        usuS.listado().then(function(res){
            $scope.usuarios = res;
        });
    }

    $scope.altaMod = function(usuarioForm){
        usSpinnerService.spin('spinner');
        usuS.altaMod(usuarioForm).then(function(res){
            usSpinnerService.stop('spinner');
            cargarUsuarios();
            $scope.usuarioForm = {};
            $('#modForm').modal('hide');
        });
    }

    $scope.modoMod = function(usuario){
        $scope.modForm = JSON.parse(JSON.stringify(usuario));
    }

    $scope.eliminarUsu = function(idUsuario){
        usSpinnerService.spin('spinner');
        usuS.eliminar(idUsuario).then(function(res){
            usSpinnerService.stop('spinner');
            cargarUsuarios();
        });
    }


})

.controller('loginCtrl',function($scope,loginS,usuS){

    $scope.loginForm = {};
    $scope.infoLogin = {};
    $scope.infoLogin.noatm = true;

    $scope.login = function (loginForm){
        loginS.login(loginForm).then(function(res){
            $scope.infoLogin = res;
        });        
    }

    $scope.altaCliente = function(clienteForm){
        clienteForm.nivel = 'cliente';
        clienteForm.estado = 'habilitado';
        clienteForm.idLocal = 0;
        usuS.altaMod(clienteForm).then(function(res){
            $scope.regForm = {};
             $('#regForm').modal('hide');
        });
    }

    $scope.autoLogin = function(nivel){
        $scope.loginForm = usuS.autoLogin(nivel);
    }


})

.controller('navbarCtrl',function($scope,loginS,localS){

    $scope.acceso = loginS.acceso();
    $scope.local = {};
    cargarLocal();

    function cargarLocal(){
        localS.cargarLocal().then(function(local){
            $scope.local = local;
        });
    }
})

.controller('estadisticasCtrl',function($scope,estaS){

    traerEstadisticas();

    function traerEstadisticas(){
        estaS.traerEstadisticas().then(function(estadisticas){

            $scope.options = estadisticas.options;
            $scope.ventalocales = estadisticas.ventalocales;
            $scope.comprausuarios = estadisticas.comprausuarios;
            $scope.cs = estadisticas.cs;
            $scope.volver = estadisticas.volver;
            $scope.prodven = estadisticas.prodven;
        })
    }


});


