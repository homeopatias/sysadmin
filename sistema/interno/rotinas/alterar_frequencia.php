<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require("../entidades/Administrador.php");
require("../entidades/Aluno.php");

$usuarioLogado = isset($_SESSION["usuario"]) ? unserialize($_SESSION["usuario"]) : false;
$mensagem = "";

if( $usuarioLogado && $usuarioLogado instanceof Administrador) {

    // Recebe os dados da frequência, aluno e aula a alterar

    $idAluno  = $_POST["idAluno"];
    $idAula   = $_POST["idAula"];
    $presenca = $_POST["presenca"];

    $idAlunoValido  = isset($idAluno) && preg_match("/^[0-9]*$/", $idAluno);
    $idAulaValido   = isset($idAula) && preg_match("/^[0-9]*$/", $idAula);
    $presencaValida = isset($presenca) && ($presenca == 0 || $presenca == 1);

    if($idAlunoValido && $idAulaValido && $presencaValida) {

        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);
        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }
        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        // Cria conexão com o banco
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario,
                               $senhaBD);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

				// checa se existea presença lançada
				$query = "SELECT * FROM Frequencia WHERE chaveAluno = :aluno AND chaveAula = :aula";
				$query = $conexao->prepare($query);
				$query->bindParam("aluno",	$idAluno,	PDO::PARAM_INT);
				$query->bindParam("aula",		$idAula,	PDO::PARAM_INT);
				$query->execute();

				if ($query->rowCount() == 0) {

						// criamos a query para inserir todas as presenças
						$textoQuery = 'INSERT INTO Frequencia (chaveAluno, chaveAula, presenca)
													 VALUES (:chaveAluno, :chaveAula, :presenca)';

						$query = $conexao->prepare($textoQuery);

						$query->bindParam("chaveAluno", $idAluno,  PDO::PARAM_INT);
						$query->bindParam("chaveAula",  $idAula,   PDO::PARAM_INT);
						$query->bindParam("presenca",   $presenca, PDO::PARAM_INT);

						$sucesso = $query->execute();
						
						if(!$sucesso) {
								$mensagem = 'Erro no envio dos dados de frequências';
						} else if(!$presenca) {
								// caso o aluno esteja ausente, enviamos um e-mail
								// avisando que a ausência foi registrada

								// primeiro descobrimos de que data é essa aula
								$query = $conexao->prepare("SELECT data FROM Aula WHERE idAula = ?");
								$query->bindParam(1, $idAula);

								$query->setFetchMode(PDO::FETCH_ASSOC);
								$query->execute();

								$dataAula = $query->fetch()['data'];
								$dataAula = date("d/m/Y", strtotime($dataAula));

								$textoQuery = "SELECT U.email, U.nome, A.numeroInscricao FROM Aluno A INNER JOIN Usuario U ON 
															 A.idUsuario = U.id WHERE A.numeroInscricao = :idAluno";

								$query = $conexao->prepare($textoQuery);

								$query->bindParam("idAluno", $idAluno);

								$query->setFetchMode(PDO::FETCH_ASSOC);
								$query->execute();

								if($linha = $query->fetch()) {
										// enviamos o email
										$emailAluno = $linha['email'];
										$nomeAluno = $linha['nome'];
										$numInscricao = $linha['numeroInscricao'];
										$assunto = "Homeopatias.com - Ausência registrada na aula do dia " . $dataAula;
										$msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la</b>";
										$msg .= "<br><br>Foi registrada uma ausência do(a) aluno(a) " . $nomeAluno . " na aula do dia ";
										$msg .= $dataAula . "<br>Caso esse dado não esteja correto, favor contatar o ";
										$msg .= "coordenador da sua cidade ou registrar uma justificativa no sistema.";
										$msg .= "<br><br>Obrigado,<br>Equipe Homeobrás.";
										$headers = "Content-type: text/html; charset=utf-8 " .
												"From: Sistema Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
												"Reply-To: noreply@homeopatias.com" . "\r\n" .
												"X-Mailer: PHP/" . phpversion();
										mail($emailAluno, $assunto, $msg, $headers);

										// agora registramos no sistema uma notificação para o aluno
										$textoNotificacao = "Uma ausência sua foi registrada para a aula do dia " . $dataAula;
										$textoNotificacao .= "\nCaso esse dado não esteja correto, favor contatar o coordenador";
										$textoNotificacao .= " da sua cidade ou registrar uma justificativa no sistema.";
										$queryNotificacao = $conexao->prepare("INSERT INTO Notificacao 
																				(titulo, texto, chaveAluno, lida) VALUES (?, ?, ?, 0)");
										$dados = array("Ausência na aula do dia " . $dataAula, $textoNotificacao, $numInscricao);
										$queryNotificacao->execute($dados);
								}
						}
				} else {
						$query = "UPDATE Frequencia SET presenca = :presenca WHERE chaveAluno = :aluno AND chaveAula = :aula";
						$query = $conexao->prepare($query);
						$query->bindParam("presenca", $presenca);
						$query->bindParam("aluno", $idAluno);
						$query->bindParam("aula", $idAula);

						$query->execute();

				}
		}
}

if($mensagem !== ""){
    $mensagem = "&erro=".$mensagem;
}

header('Location: ../visualizar_aluno.php?id=' . $idAluno . $mensagem, true, "302");
die();
