angular.module('proyecto')
	.service('estaS',function(abmS){

		var api = "http://donadojuanpizzeria.hol.es/ws1/estadisticas";

		var options = {
		            chart: {
		                type: 'pieChart',
		                height: 500,
		                x: function(d){return d.clave;},
		                y: function(d){return d.valor;},
		                showLabels: false,
		                duration: 500,
		                labelThreshold: 0.01,
		                labelSunbeamLayout: true,
		                legend: {
		                    margin: {
		                        top: 5,
		                        right: 35,
		                        bottom: 5,
		                        left: 0
		                    }
		                }
		            }
		};

        this.traerEstadisticas = function(){
                       
         return abmS.listado(api).then(function(res){
                
            	res.options = options;
            	return res;

            });
        }	
	});