<?php

require 'vendor/autoload.php';
require 'AccesoDatos.php';

$app = new Slim\App();

// ******** IMAGENES ******** ////////////////////////////////////////////////////////////////////////////////////////////
$app->post('/fotos', function ($request, $response, $args) {

    if ( !empty( $_FILES ) ) {
    $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];

    $uploadPath = '../img/'.$_FILES[ 'file' ][ 'name' ];
    move_uploaded_file( $tempPath, $uploadPath );
    $answer = array( 'answer' => 'Archivo Cargado!' );
    $json = json_encode( $answer );
    return $json;
    } else {

    return 'No se cargo el archivo';
    }   
    
});

// ****** PRODUCTOS ******** /////////////////////////////////////////////////////////////////////////////////////////////

$app->get('/productos/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $sentencia = "SELECT * FROM productos WHERE idLocal = :idLocal";
    $consulta = $objetoAccesoDato->RetornarConsulta($sentencia);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();

    $productos = json_encode($consulta->fetchAll());

    return $productos;
});

$app->delete('/productos/{object}', function ($request, $response, $args) {
   
    $decoded = json_decode($args["object"]);
    $idProducto = $decoded->idElemento;
    $respuesta['valor'] = false;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

    $query = "SELECT * FROM prodofer WHERE idProducto = :idProducto" ;  
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idProducto',$idProducto, PDO::PARAM_STR);
    $consulta->execute();
    $existeProdofer = $consulta->fetchAll();

    $query = "SELECT * FROM pedprod WHERE idProducto = :idProducto" ;  
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idProducto',$idProducto, PDO::PARAM_STR);
    $consulta->execute();
    $existePedprod = $consulta->fetchAll();

    if(!$existeProdofer && !$existePedprod){    
        $query = "DELETE FROM productos WHERE idProducto = :idProducto";  
        $consulta =$objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idProducto',$idProducto, PDO::PARAM_STR);
        $consulta->execute();

        $query = "DELETE FROM prodofer WHERE idProducto = :idProducto";  
        $consulta =$objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idProducto',$idProducto, PDO::PARAM_STR);
        $consulta->execute();

        $query = "DELETE FROM pedprod WHERE idProducto = :idProducto";  
        $consulta =$objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idProducto',$idProducto, PDO::PARAM_STR);
        $consulta->execute();

        unlink('../img/P'.$idProducto.'1.jpg');
        unlink('../img/P'.$idProducto.'2.jpg');
        unlink('../img/P'.$idProducto.'3.jpg');
        $respuesta['valor'] = true;
        return json_encode($respuesta); 
    }
    else
        return json_encode($respuesta);

});

$app->post('/productos/{object}', function ($request, $response, $args) {
   
    $decoded = json_decode($args["object"]);
    $objectProd = $decoded->objectElemento;
    $idLocal = $decoded->idLocal;
    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $sentencia = "INSERT INTO productos (nombre,idLocal,precio) values (:nombre,:idLocal,:precio)";  
    $consulta = $objetoAccesoDato->RetornarConsulta($sentencia);
    $consulta->bindValue(':nombre',$objectProd->nombre, PDO::PARAM_STR);
    $consulta->bindValue(':precio',$objectProd->precio, PDO::PARAM_STR);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();
    
    return $objetoAccesoDato->RetornarUltimoIdInsertado();

});

$app->put('/productos/{object}', function ($request, $response, $args) {
   
    $decoded = json_decode($args["object"]);
    $objectProd = $decoded->objectElemento;
    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $sentencia = "UPDATE productos SET nombre = :nombre, precio = :precio WHERE idProducto = :idProducto";  
    $consulta = $objetoAccesoDato->RetornarConsulta($sentencia);
    $consulta->bindValue(':nombre',$objectProd->nombre, PDO::PARAM_STR);
    $consulta->bindValue(':precio',$objectProd->precio, PDO::PARAM_STR);
    $consulta->bindValue(':idProducto',$objectProd->idProducto, PDO::PARAM_STR);
    $consulta->execute();

    unlink('../img/P'.$objectProd->idProducto.'1.jpg');
    unlink('../img/P'.$objectProd->idProducto.'2.jpg');
    unlink('../img/P'.$objectProd->idProducto.'3.jpg');

    return json_encode($objectProd->idProducto);

});


/////////////////////////////////////////////////////////////////////////////////////////////////////////////

///******** OFERTAS *********** ////////////////////////////////////////////////////////////////////////////
$app->get('/ofertas/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;

     $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

    $query = "SELECT OF.idOferta, OF.nombre, OF.precio, PR.idProducto, PR.nombre, PRODOF.cantidad 
    FROM ofertas AS OF, productos AS PR,prodofer AS PRODOF 
    WHERE OF.idOferta = PRODOF.idOferta AND PR.idProducto = PRODOF.idProducto 
    AND OF.idLocal = :idLocal ORDER BY OF.idOferta"; 

    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();
    $ofertas = $consulta->fetchAll();
    $currentIdOferta = "";
    $contOfer = -1;
    $contProd = -1;
    $ofertasFormateadas = array();

    foreach($ofertas as $oferta){

        if($currentIdOferta != $oferta[0]){
            $contOfer++;
            $contProd = -1;
            $productos = null;
            $ofertasFormateadas[$contOfer]['idOferta'] = $oferta[0];
            $ofertasFormateadas[$contOfer]['nombre'] = $oferta[1];
            $ofertasFormateadas[$contOfer]['precio'] = $oferta[2];
            $currentIdOferta = $oferta[0];
        }

        $contProd++;
        $productos[$contProd]['idProducto'] = $oferta[3];
        $productos[$contProd]['nombre'] = $oferta[4];
        $productos[$contProd]['cantidad'] = $oferta[5];
        $ofertasFormateadas[$contOfer]['productos'] = $productos;
    }

    return json_encode($ofertasFormateadas);
});

$app->post('/ofertas/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;
    $objectOferta = $decoded->objectElemento;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "INSERT INTO ofertas (idLocal,nombre,precio) values (:idLocal,:nombre,:precio)";
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':nombre',$objectOferta->nombre, PDO::PARAM_STR);
    $consulta->bindValue(':precio',$objectOferta->precio, PDO::PARAM_STR);
    $consulta->bindValue(':idLocal', $idLocal, PDO::PARAM_STR); 
    $consulta->execute();
    $idOferta = $objetoAccesoDato->RetornarUltimoIdInsertado();

    foreach($objectOferta->productos as $producto){
        $query = "INSERT INTO prodofer (idOferta,idProducto,cantidad) values (:idOferta,:idProducto,:cantidad)";
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idOferta',$idOferta, PDO::PARAM_STR);
        $consulta->bindValue(':idProducto',$producto->idProducto, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad',$producto->cantidad, PDO::PARAM_STR);
        $consulta->execute();
    }
    return $objetoAccesoDato->RetornarUltimoIdInsertado();

});

$app->delete('/ofertas/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idOferta = $decoded->idElemento;
    $respuesta['valor'] = false;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 

    $query = "SELECT * FROM pedofer WHERE idOferta = :idOferta"; 
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idOferta',$idOferta, PDO::PARAM_STR);
    $consulta->execute();
    $existePedofer = $consulta->fetchAll();

    if(!$existePedofer){
        $query = "DELETE FROM ofertas WHERE idOferta = :idOferta"; 
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idOferta',$idOferta, PDO::PARAM_STR);
        $consulta->execute();

        $query = "DELETE FROM prodofer WHERE idOferta = :idOferta"; 
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idOferta',$idOferta, PDO::PARAM_STR);
        $consulta->execute();

        $query = "DELETE FROM pedofer WHERE idOferta = :idOferta";  
        $consulta =$objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idOferta',$idOferta, PDO::PARAM_STR);
        $consulta->execute(); 
        $respuesta['valor'] = true;
        return json_encode($respuesta);
    }
    else
       return json_encode($respuesta);
});

$app->put('/ofertas/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $objectOferta = $decoded->objectElemento;
    
    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "UPDATE ofertas SET nombre = :nombre, precio = :precio WHERE idOferta = :idOferta"; 
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idOferta',$objectOferta->idOferta, PDO::PARAM_STR);
    $consulta->bindValue(':nombre',$objectOferta->nombre, PDO::PARAM_STR);
    $consulta->bindValue(':precio',$objectOferta->precio, PDO::PARAM_STR);
    $consulta->execute();

    $query = "DELETE FROM prodofer WHERE idOferta = :idOferta"; 
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idOferta',$objectOferta->idOferta, PDO::PARAM_STR);
    $consulta->execute();
    
    foreach($objectOferta->productos as $producto){
        $query = "INSERT INTO prodofer (idOferta,idProducto,cantidad) values (:idOferta,:idProducto,:cantidad)";
        $consulta = $objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idOferta',$objectOferta->idOferta, PDO::PARAM_STR);
        $consulta->bindValue(':idProducto',$producto->idProducto, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad',$producto->cantidad, PDO::PARAM_STR);
        $consulta->execute();

    }

    return $objetoAccesoDato->RetornarUltimoIdInsertado();
});
////////////////////////////////////////////////////////////////////////////////////////////////////////////

///********** PEDIDOS *********/////////////////////////////////////////////////////////////////////////////

$app->get('/pedidos/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

    $query = "SELECT P.idPedido,P.idCliente,USU.nombre,P.total,P.fecha,P.estado 
    FROM pedidos as P, usuarios as USU 
    WHERE P.idLocal = :idLocal AND P.idCliente = USU.idUsuario ORDER BY P.idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();
    $pedidos = $consulta->fetchAll();
  
    $query = "SELECT PED.idPedido,PROD.nombre,PROD.precio,PPRO.cantidad,PROD.idProducto 
    FROM pedidos as PED, pedprod as PPRO, productos as PROD
    WHERE PED.idLocal = :idLocal AND PED.idPedido = PPRO.idPedido AND PPRO.idProducto = PROD.idProducto
    ORDER BY PED.idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();
    $productosPed = $consulta->fetchAll();

    $query = "SELECT PED.idPedido,OF.nombre,OF.precio,PEDO.cantidad ,OF.idOferta 
    FROM pedidos as PED, pedofer as PEDO, ofertas as OF
    WHERE PED.idLocal = :idLocal AND PED.idPedido = PEDO.idPedido AND PEDO.idOferta = OF.idOferta
    ORDER BY PED.idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();
    $ofertasPed = $consulta->fetchAll();

    foreach ($pedidos as $key => $value) {

        $arrayProductos = array();
        $arrayOfertas = array();

        foreach ($productosPed as $producto) {

            if($pedidos[$key][0] == $producto[0]){
                $arrayProductos[] = $producto;
            }
        }

        foreach ($ofertasPed as $oferta) {

            if($pedidos[$key][0] == $oferta[0]){
                $arrayOfertas[] = $oferta;
            }
        }

      $pedidos[$key]['productos'] = $arrayProductos;
      $pedidos[$key]['ofertas'] = $arrayOfertas;
    }

    return json_encode($pedidos);
    

});

$app->post('/pedidos/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;
    $objectPedido = $decoded->objectElemento;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

    $query = "INSERT INTO pedidos (idLocal,idCliente,total,fecha,estado) VALUES
    (:idLocal,:idCliente,:total,:fecha,:estado)"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->bindValue(':idCliente',$objectPedido->idCliente, PDO::PARAM_STR);
    $consulta->bindValue(':total',$objectPedido->total, PDO::PARAM_STR);
    $consulta->bindValue(':fecha',$objectPedido->fecha, PDO::PARAM_STR);
    $consulta->bindValue(':estado',$objectPedido->estado, PDO::PARAM_STR);
    $consulta->execute();
    $idPedido = $objetoAccesoDato->RetornarUltimoIdInsertado();

    if(property_exists($objectPedido,'productos')){

        foreach ($objectPedido->productos as $producto) {
            $query = "INSERT INTO pedprod (idPedido,idProducto,cantidad) VALUES
            (:idPedido,:idProducto,:cantidad)"; 
            $consulta =$objetoAccesoDato->RetornarConsulta($query);
            $consulta->bindValue(':idPedido',$idPedido, PDO::PARAM_STR);
            $consulta->bindValue(':idProducto',$producto->idProducto, PDO::PARAM_STR);
            $consulta->bindValue(':cantidad',$producto->cantidad, PDO::PARAM_STR);
            $consulta->execute();
        }
    }

    if(property_exists($objectPedido,'ofertas')){

        foreach ($objectPedido->ofertas as $oferta) {
            $query = "INSERT INTO pedofer (idPedido,idOferta,cantidad) VALUES
            (:idPedido,:idOferta,:cantidad)"; 
            $consulta =$objetoAccesoDato->RetornarConsulta($query);
            $consulta->bindValue(':idPedido',$idPedido, PDO::PARAM_STR);
            $consulta->bindValue(':idOferta',$oferta->idOferta, PDO::PARAM_STR);
            $consulta->bindValue(':cantidad',$oferta->cantidad, PDO::PARAM_STR);
            $consulta->execute();
        }
    }

    return $objetoAccesoDato->RetornarUltimoIdInsertado();
});

$app->put('/pedidos/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;
    $objectPedido = $decoded->objectElemento;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

    $query = "UPDATE pedidos SET idCliente = :idCliente, total = :total, fecha = :fecha, estado = :estado
    WHERE idPedido = :idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idCliente',$objectPedido->idCliente, PDO::PARAM_STR);
    $consulta->bindValue(':total',$objectPedido->total, PDO::PARAM_STR);
    $consulta->bindValue(':fecha',$objectPedido->fecha, PDO::PARAM_STR);
    $consulta->bindValue(':estado',$objectPedido->estado, PDO::PARAM_STR);
    $consulta->bindValue(':idPedido',$objectPedido->idPedido, PDO::PARAM_STR);
    $consulta->execute();

    $query = "DELETE FROM pedprod WHERE idPedido = :idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idPedido',$objectPedido->idPedido, PDO::PARAM_STR);
    $consulta->execute();

    $query = "DELETE FROM pedofer WHERE idPedido = :idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idPedido',$objectPedido->idPedido, PDO::PARAM_STR);
    $consulta->execute();    

    foreach ($objectPedido->productos as $producto) {
        $query = "INSERT INTO pedprod (idPedido,idProducto,cantidad) VALUES
        (:idPedido,:idProducto,:cantidad)"; 
        $consulta =$objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido',$objectPedido->idPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idProducto',$producto->idProducto, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad',$producto->cantidad, PDO::PARAM_STR);
        $consulta->execute();
    }      

    foreach ($objectPedido->ofertas as $oferta) {
        $query = "INSERT INTO pedofer (idPedido,idOferta,cantidad) VALUES
        (:idPedido,:idOferta,:cantidad)"; 
        $consulta =$objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idPedido',$objectPedido->idPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idOferta',$oferta->idOferta, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad',$oferta->cantidad, PDO::PARAM_STR);
        $consulta->execute();
    }


    return $objetoAccesoDato->RetornarUltimoIdInsertado();
});

$app->put('/pedidos/estado/{object}',function($request,$response,$args){
    $decoded = json_decode($args["object"]);
    $objectPedido = $decoded->objectElemento;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "UPDATE pedidos SET estado = :estado WHERE idPedido = :idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':estado',$objectPedido->estado, PDO::PARAM_STR);
    $consulta->bindValue(':idPedido',$objectPedido->idPedido, PDO::PARAM_STR);
    $consulta->execute();

});

$app->delete('/pedidos/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idPedido = $decoded->idElemento;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

    $query = "DELETE FROM pedidos WHERE idPedido = :idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idPedido',$idPedido, PDO::PARAM_STR);
    $consulta->execute();
    $query = "DELETE FROM pedofer WHERE idPedido = :idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idPedido',$idPedido, PDO::PARAM_STR);
    $consulta->execute();
    $query = "DELETE FROM pedprod WHERE idPedido = :idPedido"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idPedido',$idPedido, PDO::PARAM_STR);
    $consulta->execute();

    return $consulta->rowCount();
});

$app->post('/pedidos/encuestas/{object}', function ($request, $response, $args) {

        $decoded = json_decode($args["object"]);
        $objectEncuesta = $decoded->objectElemento;
        $stringIdProducto = "";
        foreach ($objectEncuesta->prodfav as $key => $value) {
            if($stringIdProducto == "")
                $stringIdProducto = $value;
            else
                $stringIdProducto .= '-'.$value;

        }

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $query = "INSERT INTO encuestas values (:cs,:prodfav,:motivo,:opinion,:volver)"; 
        $consulta =$objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':cs',$objectEncuesta->cs, PDO::PARAM_STR);
        $consulta->bindValue(':prodfav',$stringIdProducto, PDO::PARAM_STR);
        $consulta->bindValue(':motivo',$objectEncuesta->motivo, PDO::PARAM_STR);
        $consulta->bindValue(':opinion',$objectEncuesta->opinion, PDO::PARAM_STR);
        $consulta->bindValue(':volver',$objectEncuesta->volver, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();

});
////////////////////////////////////////////////////////////////////////////////////////////////////////////

///********** USUARIOS *********/////////////////////////////////////////////////////////////////////////////
$app->get('/usuarios/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "SELECT * FROM usuarios WHERE idLocal = :idLocal OR nivel = 'administrador'
    OR nivel = 'cliente'"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();
    $usuarios = json_encode($consulta->fetchAll());
    
    return $usuarios;

});

$app->post('/usuarios/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $objectPersona = $decoded->objectElemento;

    if(property_exists($objectPersona,'idLocal'))
        $idLocal = $objectPersona->idLocal;
    else
        $idLocal = $decoded->idLocal;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "INSERT INTO usuarios (idLocal,nombre,email,password,nivel,estado) 
    VALUES (:idLocal,:nombre,:email,:password,:nivel,:estado)";
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->bindValue(':nombre',$objectPersona->nombre, PDO::PARAM_STR);
    $consulta->bindValue(':email',$objectPersona->email, PDO::PARAM_STR);
    $consulta->bindValue(':password',$objectPersona->password, PDO::PARAM_STR);
    $consulta->bindValue(':nivel',$objectPersona->nivel, PDO::PARAM_STR);
    $consulta->bindValue(':estado',$objectPersona->estado, PDO::PARAM_STR);
    $consulta->execute();

    return $objetoAccesoDato->RetornarUltimoIdInsertado();

});

$app->put('/usuarios/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $objectUsuario = $decoded->objectElemento;
    $idLocal = $decoded->idLocal;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

    if($objectUsuario->nivel != 'cliente'){
        $query = "DELETE FROM pedidos WHERE idCliente = :idUsuario"; 
        $consulta =$objetoAccesoDato->RetornarConsulta($query);
        $consulta->bindValue(':idUsuario',$objectUsuario->idUsuario, PDO::PARAM_STR);
        $consulta->execute();
    }

    $query = "UPDATE usuarios SET email = :email, nombre = :nombre, password = :password, nivel = :nivel,
    estado = :estado, idLocal = :idLocal WHERE idUsuario = :idUsuario"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':email',$objectUsuario->email, PDO::PARAM_STR);
    $consulta->bindValue(':nombre',$objectUsuario->nombre, PDO::PARAM_STR);
    $consulta->bindValue(':password',$objectUsuario->password, PDO::PARAM_STR);
    $consulta->bindValue(':nivel',$objectUsuario->nivel, PDO::PARAM_STR);
    $consulta->bindValue(':estado',$objectUsuario->estado, PDO::PARAM_STR);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->bindValue(':idUsuario',$objectUsuario->idUsuario, PDO::PARAM_STR);
    $consulta->execute();
    
    return $objetoAccesoDato->RetornarUltimoIdInsertado();

});

$app->delete('/usuarios/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idUsuario = $decoded->idElemento;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "DELETE FROM usuarios WHERE idUsuario = :idUsuario"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idUsuario',$idUsuario, PDO::PARAM_STR);
    $consulta->execute();

    $query = "DELETE FROM pedidos WHERE idCliente = :idUsuario"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idUsuario',$idUsuario, PDO::PARAM_STR);
    $consulta->execute();
    
    return $consulta->rowCount();

});

////////////////////////////////////////////////////////////////////////////////////////////////////////////

///********** LOCALES *********/////////////////////////////////////////////////////////////////////////////

$app->get('/locales/{object}', function ($request, $response, $args) {

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
    $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM locales");
    $consulta->execute();
    $locales = json_encode($consulta->fetchAll());
    $response->getBody()->write($locales);
    return $response;

});

$app->get('/local/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "SELECT * FROM locales WHERE idLocal = :idLocal"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();
    
    $local = json_encode($consulta->fetchAll());
    return $local;

});

$app->post('/locales/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $objectLocal = $decoded->objectElemento;
    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "INSERT INTO locales (nombre,direccion,tel) values (:nombre,:direccion,:tel)"; 
    $consulta =$objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':nombre',$objectLocal->nombre, PDO::PARAM_STR);
    $consulta->bindValue(':direccion',$objectLocal->direccion, PDO::PARAM_STR);
    $consulta->bindValue(':tel', $objectLocal->tel, PDO::PARAM_STR);
    $consulta->execute();
    
    return $objetoAccesoDato->RetornarUltimoIdInsertado();
});

$app->delete('/locales/{object}', function ($request, $response, $args) {

    $decoded = json_decode($args["object"]);
    $idLocal = $decoded->idLocal;
    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
    $consulta =$objetoAccesoDato->RetornarConsulta("DELETE  FROM locales WHERE idLocal = :idLocal");
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();

    $consulta =$objetoAccesoDato->RetornarConsulta("DELETE  FROM pedidos WHERE idLocal = :idLocal");
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();

    $consulta =$objetoAccesoDato->RetornarConsulta("DELETE  FROM ofertas WHERE idLocal = :idLocal");
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();

    $consulta =$objetoAccesoDato->RetornarConsulta("DELETE  FROM productos WHERE idLocal = :idLocal");
    $consulta->bindValue(':idLocal',$idLocal, PDO::PARAM_STR);
    $consulta->execute();

    return $consulta->rowCount();    
});

$app->put('/locales/{object}', function ($request, $response, $args) {
    
    $decoded = json_decode($args["object"]);
    $objectLocal = $decoded->objectElemento; 
    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
    $query = "UPDATE locales SET nombre = :nombre, direccion = :direccion, tel = :tel WHERE idLocal = :idLocal";
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->bindValue(':nombre',$objectLocal->nombre, PDO::PARAM_STR);
    $consulta->bindValue(':direccion',$objectLocal->direccion, PDO::PARAM_STR);
    $consulta->bindValue(':tel', $objectLocal->tel, PDO::PARAM_STR);
    $consulta->bindValue(':idLocal', $objectLocal->idLocal, PDO::PARAM_STR);
    $consulta->execute();

    unlink('../img/L'.$objectLocal->idLocal.'1.jpg');
    unlink('../img/L'.$objectLocal->idLocal.'2.jpg');
    unlink('../img/L'.$objectLocal->idLocal.'3.jpg');
    
    return $objetoAccesoDato->RetornarUltimoIdInsertado();
});

$app->get('/estadisticas/{object}', function($request, $response, $args){

    $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

    //VENTAS X LOCAL
    $query = "SELECT L.nombre as clave, count(*) as valor FROM pedidos as P, locales as L
    WHERE P.idLocal = L.idLocal GROUP BY P.idLocal";
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->execute();
    $ventalocales = $consulta->fetchAll();


    // COMPRAS X USUARIO
    $query = "SELECT U.nombre as clave, count(*) as valor FROM pedidos as P, usuarios as U
    WHERE P.idCliente = U.idUsuario GROUP BY P.idCliente";
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->execute();
    $comprausuarios = $consulta->fetchAll();

    // CALIDAD SERVICIO

    $query = "SELECT cs as clave, count(*) as valor FROM encuestas GROUP BY cs";
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->execute();
    $cs = $consulta->fetchAll();

    foreach ($cs as $key => $value) {

        switch ($value['clave']) {
            case '1':
                $cs[$key]['clave'] = 'Malo';
                break;
            case '2':
                $cs[$key]['clave'] = 'Regular';
                break;
             case '3':
                $cs[$key]['clave'] = 'Bueno';
                break;
            case '4':
                $cs[$key]['clave'] = 'Muybueno';
                break;
            case '5':
                $cs[$key]['clave'] = 'Excelente';
                break;
        }
    }

    //VOLVER

    $query = "SELECT volver as clave, count(*) as valor FROM encuestas GROUP BY volver";
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->execute();
    $volver = $consulta->fetchAll();

    foreach ($volver as $key => $value) {
        switch ($value['clave']) {
            case '1':
                $volver[$key]['clave'] = 'Si'; 
                break;
            case '2':
                $volver[$key]['clave'] = 'No';
                break;
             case '3':
                $volver[$key]['clave'] = 'Tal vez';
                break;
        }
    }

    // PRODUCTOS VENDIDOS

    $query = "SELECT PR.nombre as clave, sum(PEDPR.cantidad) as valor 
    FROM pedprod as PEDPR, productos as PR
    WHERE PEDPR.idProducto = PR.idProducto
    GROUP BY PEDPR.idProducto";
    $consulta = $objetoAccesoDato->RetornarConsulta($query);
    $consulta->execute();
    $prodven = $consulta->fetchAll();
    
    $estadisticas['volver'] = $volver;
    $estadisticas['cs'] = $cs;
    $estadisticas['ventalocales'] = $ventalocales;
    $estadisticas['comprausuarios'] = $comprausuarios;
    $estadisticas['prodven'] = $prodven;

    return json_encode($estadisticas);

});
///////////////////////////////////////////////////////////////////////////////////////////////////////////

$app->run();
