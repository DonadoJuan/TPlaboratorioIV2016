angular.module('proyecto')
	.service('prodS',function(abmS,$http,uploaderS){

			var api = "http://donadojuanpizzeria.hol.es/ws1/productos";

			this.listado = function(){

				return abmS.listado(api).then(function(productos){
            		
            		return uploaderS.fuenteFotos('P',productos);

				});
	  		}

	  		this.altaMod = function(objectProd){

	  			if(typeof objectProd.idProducto == "undefined")
	  				return	abmS.alta(api,objectProd);
	  			else
	  				return	abmS.modificar(api,objectProd);

	  		}

		    this.eliminar = function(idProd){

		    	return abmS.eliminar(api,idProd); 
		    }

		    this.retornarUploader = function(){
		    	return uploaderS.retornarUploader();
		    }

		    this.retornarModUploader = function(){
		    	return uploaderS.retornarModUploader();
		    }

		    this.subirFotos = function(idProducto){
		    	uploaderS.subirFotos('P',idProducto);
		    }




	})