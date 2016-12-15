<?php 
class Usuario{

	public $id;
	public $nombre;
	public $email;
	public $perfil;
	public $password;

	public function __construct($id = NULL)
	{
		if($id != NULL){
			$usuario = self::TraerUnUsuarioPorId($id);
			$this->id = $usuario->id;
			$this->nombre = $usuario->nombre;
			$this->email = $usuario->email;
			$this->perfil = $usuario->perfil;
		}
	}

	public static function TraerUsuarioLogueado($usuario){
		$conexion = self::CrearConexion();
		$sql = "SELECT * FROM usuarios U WHERE U.email = :email AND U.password = :password";
		$consulta = $conexion->prepare($sql);
		$consulta->bindValue(":email", $usuario->email, PDO::PARAM_STR);
		$consulta->bindValue(":password", $usuario->password, PDO::PARAM_STR);
		$consulta->execute();
		$usuarioLogeado = $consulta->fetchAll();
		return $usuarioLogeado;
	}

	public static function CrearConexion(){
		try
		{
			$conexion = new PDO("mysql:host=mysql.hostinger.com.ar;dbname=u606025973_pizza;charset=utf8;",'u606025973_pizza','viejo123');
			return $conexion;
		}
		catch (Exception $e) {
			print_r("Error: " . $e->GetMessage());
			die();
			return;
		}
	}
}
 ?>