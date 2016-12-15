angular.module('proyecto')
	.service('pedS',function(abmS){

		var api = "http://donadojuanpizzeria.hol.es/ws1/pedidos";

		this.listado = function(){
			return abmS.listado(api);
  		}

  		this.altaMod = function(objectPed){

        if(typeof objectPed.cs != "undefined"){
          var apiEnc = "http://donadojuanpizzeria.hol.es/ws1/pedidos/encuestas";
          return abmS.alta(apiEnc,objectPed); 
        }

  			else if(typeof objectPed.idPedido == "undefined")
  				return abmS.alta(api,objectPed);
  			else
  				return abmS.modificar(api,objectPed);

  		}

	    this.eliminar = function(idPed){
	    	return abmS.eliminar(api,idPed);
	    }

	    this.calcularTotal = function(pedidoForm){
	   		var total = 0;

        	angular.forEach(pedidoForm.productos,function(value,key){
            if(!isNaN(value.cantidad))
            	total += parseInt(value.precio) * parseInt(value.cantidad);
        	});

        	angular.forEach(pedidoForm.ofertas,function(value,key){
            if(!isNaN(value.cantidad))
            	total += parseInt(value.precio) * parseInt(value.cantidad);

        	});

        	return total;
	    }

      this.fechaLimite = function(){

            var fechaMin = new Date();
            var fechaMax = new Date();
            fechaMin.setDate(fechaMin.getDate() + 1);
            fechaMax.setDate(fechaMax.getDate() + 5);

            return {min:fechaMin,max:fechaMax};
      }

      this.cambiarEstado = function(pedido){
        var apiEstado = 'http://donadojuanpizzeria.hol.es/ws1/pedidos/estado';
        if(pedido.estado == 'no entregado')
            pedido.estado = 'entregado';
        else
            pedido.estado = 'no entregado';

        return abmS.modificar(apiEstado,pedido).then(function(res){
          return res;
        });
      }
	})