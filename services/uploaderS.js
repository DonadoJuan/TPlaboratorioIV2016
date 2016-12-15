angular.module('proyecto')
	.service('uploaderS',function(FileUploader){

		var options = {

			url:'http://donadojuanpizzeria.hol.es/ws1/fotos',
			queueLimit: 3,
			filters: [{
				name: 'filtroLoco',
				fn: function(item) {
    					if(item.type == 'image/jpeg' && item.size <= 1500000)
    						return true;
    					else
    						return false;
				}
			}]		
    	};

    	var uploader = new FileUploader(options);
    	var modUploader = new FileUploader(options);

    	this.retornarUploader = function(){ 
    		return uploader;
    	}

    	this.retornarModUploader = function(){
    		return modUploader;
    	}
			
		this.subirFotos = function(tipoFoto,idElemento){

	    	var idElemento = (String(idElemento)).trim();
        	var cont = 1;
        	if(uploader.queue.length != 3)
        		var uploaderLoco = modUploader;
        	else
        		var uploaderLoco = uploader;

        	angular.forEach(uploaderLoco.queue,function(value,key){
                value.file.name = tipoFoto + idElemento + String(cont) + ".jpg";
                cont++;
        	});

			uploaderLoco.uploadAll();
		}

		this.fuenteFotos = function(tipoFoto,object){

			var id = 0;

			if(tipoFoto == 'P'){
				angular.forEach(object,function(value,key){
				var id = value.idProducto;
	            var imageArray = [
		                { src:'P' + id + '1' + '.jpg'},
		                { src:'P' + id + '2' + '.jpg'},
		                { src:'P' + id + '3' + '.jpg'}
	            ]
	                value.imageArray = imageArray;  
	    		});
	    		return object;
			}
			else{
				angular.forEach(object,function(value,key){
				var id = value.idLocal;
	            var imageArray = [
		                { src:'L' + id + '1' + '.jpg'},
		                { src:'L' + id + '2' + '.jpg'},
		                { src:'L' + id + '3' + '.jpg'}
	            ]
	                value.imageArray = imageArray;  
	    		});
	    		return object;
			}
		}
		
	})