<?php
	include_once '../vendor/autoload.php';
	use \Firebase\JWT\JWT;
	require_once 'Usuario.php';

		$post = file_get_contents("php://input");
		$respuesta = json_decode($post);
		$usuarioLogueado = Usuario::TraerUsuarioLogueado($respuesta);

		if($usuarioLogueado){

			$key = "1234";
			$token["usuarioLogin"] = $usuarioLogueado;
			$token["exp"] = time()+20;
			$jwt = JWT::encode($token, $key);
			$array["miToken"] = $jwt;
		}
		else
			$array["miToken"] = false;

		echo json_encode($array);

		die();

	$decoded = JWT::decode($jwt, $key, array('HS256'));
	print_r($decoded);
	/*
	 NOTE: This will now be an object instead of an associative array. To get
	 an associative array, you will need to cast it as such:
	*/
	$decoded_array = (array) $decoded;
	/**
	 * You can add a leeway to account for when there is a clock skew times between
	 * the signing and verifying servers. It is recommended that this leeway should
	 * not be bigger than a few minutes.
	 *
	 * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
	 */
	JWT::$leeway = 60; // $leeway in seconds
	$decoded = JWT::decode($jwt, $key, array('HS256'));
?>