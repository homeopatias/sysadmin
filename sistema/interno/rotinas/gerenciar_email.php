<?php
	require_once("entidades/Administrador.php");
	require_once("entidades/Coordenador.php");
	if(isset($_SESSION['usuario']) && (unserialize($_SESSION['usuario']) instanceof Administrador ) || 
		unserialize($_SESSION['usuario']) = instanceof Coodernador ){
		
	}

?>