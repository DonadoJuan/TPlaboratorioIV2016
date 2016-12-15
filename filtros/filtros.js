angular.module('proyecto')

.filter('usuarioF',function(loginS){


        return function(input) {
            
            var acceso = loginS.acceso();
            var out = [];

            angular.forEach(input, function(usuario) {

                if (usuario.nivel == 'empleado' && !acceso.enc)
                    return;
                if(usuario.nivel == 'encargado' && !acceso.admin)
                    return;
                if(usuario.nivel == 'administrador')
                    return;
                  
                out.push(usuario);
          
            })

            return out;
        }
})

.filter('pedidoF',function(loginS){


        return function(input) {
            
            var acceso = loginS.acceso();
            var out = [];

            angular.forEach(input, function(pedido) {

                if (pedido.idCliente != acceso.id && !acceso.emp)
                    return;

                out.push(pedido);
          
            })

            return out;
        }
})

.filter('clienteF',function(loginS){


        return function(input) {
            
            var acceso = loginS.acceso();
            var out = [];

            angular.forEach(input, function(usuario) {

                if (usuario.nivel != 'cliente')
                    return;

                out.push(usuario);
          
            })

            return out;
        }
})