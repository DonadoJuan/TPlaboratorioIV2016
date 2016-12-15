angular.module('proyecto')
	.service('abmS',function($http,$stateParams){

			function idLocal (){
				return $stateParams.id;
			}

		    this.modificar = function(api,object){

		    	var objectCoded = null;

		    	if(typeof idLocal() != "undefined")
		    		objectCoded = JSON.stringify({ objectElemento: object,  idLocal: idLocal() });
		    	else
		    		objectCoded = JSON.stringify({ objectElemento: object });

				return $http.put(api + "/" + objectCoded)
					.then(function (respuesta){
						console.info('respuesta desde abmS',respuesta.data);
				        return respuesta.data;


				    },function(error){

				        console.info("Error modifacion: ", error);

				    });
		    }

		    this.alta = function(api,object){

		    	var objectCoded = null;

		    	if(typeof idLocal() != "undefined")
		    		objectCoded = JSON.stringify({ objectElemento: object,  idLocal: idLocal() });
		    	else
		    		objectCoded = JSON.stringify({ objectElemento: object });

				return $http.post(api + "/" + objectCoded)
				    .then(function (respuesta){
				    	return respuesta.data;

				    },function(error){
				        console.info("error alta: ", error.data);

				    });
	  		}

			this.listado = function(api){

		    	var objectCoded = null;

		    	if(typeof idLocal() != "undefined")
		    		objectCoded = JSON.stringify({idLocal: idLocal()});
		    	else
		    		objectCoded = JSON.stringify({idLocal: "undefined"});

				return $http.get(api + "/" + objectCoded)
				    .then(function (respuesta){
				    	
				        return respuesta.data;


				    },function(error){

				        console.info("Error listado: ", error);

				    });
	  		}

		    this.eliminar = function(api,id){

		    		var objectCoded = JSON.stringify({ idElemento: id,  idLocal: idLocal() });

			       	return $http.delete(api +"/"+ objectCoded)
			            .then(function (respuesta){

			            	return respuesta.data;
			 

			            },function(error){

			                console.info("Error eliminando: ", error.data);

			        });    
		    }
	})