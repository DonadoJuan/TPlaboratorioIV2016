angular.module('proyecto')
	.service('loginS',function($auth,$state,$stateParams){


			this.login = function(loginForm){

			return $auth.login({
		            email: loginForm.email,
		            password: loginForm.password  
		        })
		        .then(function(response){

		        	var infoLogin = {
		        		datos : false,
		        		estado : false,
		        		noatm : false
		        	}

		            if($auth.isAuthenticated()){

		            	var estado = $auth.getPayload().usuarioLogin[0].estado;
		            	console.info('datos correctos...');
		            	if(estado == 'bloqueado'){
		            		$auth.logout();
		            		console.info('CUENTA BLOQUEADA!');
		            		infoLogin.datos = true;
		            		infoLogin.estado = false;
		            		return infoLogin;
		   				}
		   				else{
		   					console.info('SESION INICIADA!');
		   					$state.go('locales');
		   					infoLogin.datos = true;
		   					infoLogin.estado = true;
		            		return infoLogin;
		   				}
		            }
		            else{
		                console.info("Info isAuthenticated: ", $auth.isAuthenticated());
		                console.info("Info response ", response.data);
		                infoLogin.datos = false;
		            	infoLogin.estado = false;
		                return infoLogin;
		            }
		        },
		        function(err){
		            console.log("Error de conexión", err);
		        });
		    }

		    this.logout = function(){
		    	$auth.logout();
		    	$state.go('inicio');
		    }

		    this.checkLogin = function(){
		    	if($auth.isAuthenticated())
		    		$state.go('locales');
		    	else
		    		$state.go('inicio');
		    }

		    this.acceso = function(){

		    	var usuario = $auth.getPayload().usuarioLogin[0];
		    	var idLocal = $stateParams.id;
		    	var privilegios = {
		    		id:usuario.idUsuario,
		    		nombre:usuario.nombre, 
		    		emp: false, 
		    		enc: false, 
		    		admin: false
		    	};
		    	
		    	if(usuario.nivel == 'empleado' && idLocal == usuario.idLocal){
		    		privilegios.emp = true;
		    		return privilegios;
		    	}

		    	else if(usuario.nivel == 'encargado' && idLocal == usuario.idLocal){
		    		privilegios.emp = true;
		    		privilegios.enc = true;
		    		return privilegios;
		    	}

		    	else if(usuario.nivel == 'administrador'){
					privilegios.emp = true;
		    		privilegios.enc = true;
		    		privilegios.admin = true;
		    		return privilegios;		    		
		    	}

		    	else
		    		return privilegios;
		    }


	})