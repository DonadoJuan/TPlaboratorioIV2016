angular.module('proyecto')
	.service('usuS',function(abmS){

			var api = "http://donadojuanpizzeria.hol.es/ws1/usuarios";

			this.listado = function(){

				return abmS.listado(api);
	  		}

	  		this.altaMod = function(objectUsu){

	  			if(typeof objectUsu.idUsuario == "undefined")
	  				return	abmS.alta(api,objectUsu);
	  			else
	  				return	abmS.modificar(api,objectUsu);

	  		}

		    this.eliminar = function(idUsu){

		    	return abmS.eliminar(api,idUsu); 
		    }

		    this.autoLogin = function(nivel){

		    	switch(nivel){

		    		case 1: return {email: 'cl@cl.com', password: 123};
		    		break;
		    		case 2: return {email: 'emp@emp.com', password: 123};
		    		break;
		    		case 3: return {email: 'enc@enc.com', password: 123};
		    		break;
		    		case 4: return {email: 'admin@admin.com', password: 123};
		    		break;
		    		default: return false;
		    	}
		    }
	})