<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
 ?>
 <link rel="stylesheet" href="../estilos/estilo.css">

 <?php
	require_once($_SERVER["DOCUMENT_ROOT"].'/interno/entidades/Administrador.php');
    require_once($_SERVER["DOCUMENT_ROOT"].'/interno/entidades/Aluno.php');
	$admin = unserialize($_SESSION["usuario"]);
	if(isset($_SESSION['usuario']) && ($admin instanceof Administrador ) && 
		($admin->getNivelAdmin() === "administrador" || $admin->getNivelAdmin() === "coordenador") ){

		// lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);

        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }

        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        // cria conexão com o banco
        $conexao = null;
        $db      = "homeopatias";
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        // check se foram passados o titulo , conteudo e a forma de envio do e-mail
		if( isset($_POST["title"]) && isset($_POST["conteudo"]) && isset($_POST["sendType"]) ){

            //Prepara o cabeçalho e instancia as variaveis necessarias
			$mensagem = $_POST["conteudo"];
			$titulo   = $_POST["title"];
			$headers = "Content-type: text/plain; charset=utf-8 " .
                    "From: ". $admin->getNome() ." <".$admin->getEmail().">" . "\r\n" .
                    "Reply-To: noreply@homeopatias.com" . "\r\n" .
                    "X-Mailer: PHP/" . phpversion();

            // Se o tipo do email e´ para todos, buscamos cada aluno matriculado em uma cidade
            // que o coordenador e responsavel e enviamos o e-mail
            if($_POST["sendType"] === "todos"){
            	$textoQuery = "SELECT U.email, U.nome
            				   FROM Usuario U, Aluno A, Matricula M, Cidade C
            				   WHERE U.id = A.idUsuario AND M.chaveAluno = A.numeroInscricao AND
            				    C.idCidade = M.chaveCidade AND M.aprovado IS NULL 
            				    AND C.idCoordenador = ?";
            	$query = $conexao->prepare($textoQuery);
            	$query->bindParam(1,$admin->getIdAdmin(), PDO::PARAM_INT);
            	$query->execute();
                $table = "<table>";
            	while($linha = $query->fetch()){
                    $table .= "<tr><td>".$linha['nome']."</td>";

                    if( mail($linha["email"],$titulo, $mensagem, $headers) ){
                        $table .= "<td class = 'sucesso'>Enviado</td>";
                    }else{
                        $table .= "<td class = 'warning'>Erro no Envio</td>";
                    }

                    $table .= "</tr>";
            	}
                $table .= "</table>";
                echo $table;
            }

            // Se o tipo do email e´ para selecionados, buscamos cada aluno selecionado e enciamos o e-mail
            if( isset($_POST['selecionados']) && mb_strlen($_POST['selecionados']) > 0 
                && $_POST['sendType'] === "selecionados"){
                    
                $ids = split(",", $_POST['selecionados']);
                $tabela = "<table>";
                foreach($ids as $id){
                    if( !is_nan( (double)$id) && $id != "" ){ 
                        $aluno = new Aluno("");
                        $aluno->setNumeroInscricao($id);
                        $aluno->recebeAlunoId($host,$db,$usuario,$senhaBD);
                        $tabela .= "<tr><td>".$aluno->getNome()."</td>";

                        if(mail($aluno->getEmail(),$titulo, $mensagem, $headers) ){
                            $tabela .= "<td class = 'sucesso'>Enviado</td>";
                        }else{
                            $tabela .= "<td class = 'warning'>Erro no Envio</td>";
                        }

                        $tabela .= "</tr>";
                    }
                }
                $tabela .= "</table>";
                echo $tabela;
            }
            echo "<a href='../visualizar_turmas.php'>Continuar</a>";
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