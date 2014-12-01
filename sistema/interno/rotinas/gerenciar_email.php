<?php
	require_once($_SERVER["DOCUMENT_ROOT"].'/interno/entidades/Administrador.php');
	$admin = unserialize($_SESSION["usuario"]);
	if(isset($_SESSION['usuario']) && ($admin instanceof Administrador ) && 
		($admin->getNivelAdmin() === "administrador" || $admin->getNivelAdmin() === "coordenador") ){

		$conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

		if( isset($_POST["title"]) && isset($_POST["conteudo"]) && isset($_POST["sendType"]) ){
			$mensagem = $_POST["conteudo"];
			$titulo   = $_POST["title"];
			$headers  = "Content-type: text/plain; charset=utf-8 " .
                    "From: ".$admin->getEmail() . "\r\n" .
                    "Reply-To: noreply@homeopatias.com";

            if($_POST["sendType"] === "todos"){
            	$textoQuery = "SELECT U.email
            				   FROM Usuario U, Aluno A, Matricula M, Cidade C
            				   WHERE U.id = A.idUsuario AND M.chaveAluno = A.numeroInscricao AND
            				    C.idCidade = M.chaveCidade AND M.aprovado IS NULL 
            				    AND C.idCoordenador = ?";
            	$query = $conexao->prepare($textoQuery);
            	$query->bindParam(1,$admin->getIdAdmin(), PDO::PARAM_INT);
            	$query->execute();
            	while($linha = $query->fetch()){
            		mail($linha["email"],$titulo, $mensagem, $headers);
            	}
            }
		}
	}

	else{
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="0; url=../index.php">
        <script type="text/javascript">
            window.location = "../index.php";
        </script>
        <?php
                die();
    }

?>