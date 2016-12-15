angular.module('proyecto')
	.service('oferS',function(prodS,abmS){

			var api = "http://donadojuanpizzeria.hol.es/ws1/ofertas";

			this.listado = function(){

				return abmS.listado(api);
	  		}

	  		this.altaMod = function(objectOfer){

	  			if(typeof objectOfer.idOferta == "undefined")
	  				return abmS.alta(api,objectOfer);
	  			else
	  				return abmS.modificar(api,objectOfer);

	  		}

		    this.eliminar = function(idOfer){

		   		return abmS.eliminar(api,idOfer);    
		    }
	})