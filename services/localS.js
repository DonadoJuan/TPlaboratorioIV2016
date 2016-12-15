angular.module('proyecto')
	.service('localS',function(abmS,$http,$stateParams,$geolocation,uploaderS){

		var api = "http://donadojuanpizzeria.hol.es/ws1/locales";
		var idLocal = $stateParams.id;

		this.coordenadasUsu = function(){

	     return	$geolocation.getCurrentPosition({
            		timeout: 60000
 				}).then(function(position) {
            		return position.coords;
     		 	});
		}

		this.altaMod = function(objectLocal){
			if(typeof objectLocal.idLocal == 'undefined')
        		return abmS.alta(api,objectLocal);
        	else
        		return abmS.modificar(api,objectLocal);
		}

		this.listado = function(){

			return abmS.listado(api).then(function(locales){
				return uploaderS.fuenteFotos('L',locales);
			});
		}

		this.eliminar = function(){
			return abmS.eliminar(api,0);
		}

		this.cargarLocal = function(){
			var apiLocal = "http://donadojuanpizzeria.hol.es/ws1/local";
			return abmS.listado(apiLocal).then(function(local){
				return local[0];
			});
		}

		this.retornarUploader = function(){
			return uploaderS.retornarUploader();
		}

		this.subirFotos = function(idLocal){
			uploaderS.subirFotos('L',idLocal);
		}

	})