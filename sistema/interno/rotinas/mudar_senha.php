<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require("../phpass-0.3/PasswordHash.php");
require("../entidades/Administrador.php");
require("../entidades/Aluno.php");
require("../entidades/Associado.php");

// Recebe a senha antiga do usuário
// e a senha nova a ser colocada no lugar
// Para alunos:
//  Caso a senha antiga esteja correta, altera
//  a senha do usuário armazenado na sessão
// Para administradores:
//  Altera a senha do aluno cujo id foi passado

$senhaAntiga = $_POST["antiga"];
$idAluno     = $_POST["idaluno"];
$senhaNova   = $_POST["nova"];

// checa se os dados sao validos

$antigaValida  = isset($senhaAntiga) && mb_strlen($senhaAntiga, 'UTF-8') >= 6 &&
                 mb_strlen($senhaAntiga, 'UTF-8') <= 72;
$novaValida    = isset($senhaNova) && mb_strlen($senhaNova, 'UTF-8') >= 6 &&
                 mb_strlen($senhaNova, 'UTF-8') <= 72;
$idAlunoValido = isset($idAluno) && preg_match("/^[0-9]*$/", $idAluno);

// mensagem a ser exibida em caso de erro
$mensagem = "";
$sucesso = false;

// lemos as credenciais do banco de dados
$dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
$dados = json_decode($dados, true);
foreach($dados as $chave => $valor) {
    $dados[$chave] = str_rot13($valor);
}
$host    = $dados["host"];
$usuario = $dados["nome_usuario"];
$senhaBD = $dados["senha"];

// cria a conexão com o banco
$conexao = null;
try{
    $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8",
                       $usuario, $senhaBD);
}catch (PDOException $e){
    echo $e->getMessage();
}

if($antigaValida){

    $textoQuery  = "SELECT senha FROM Usuario WHERE login=?";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, unserialize($_SESSION["usuario"])->getLogin(), PDO::PARAM_STR);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();

    if ($linha = $query->fetch()){
        $hasher = new PasswordHash(8, false);
        $senhaCorreta = $hasher->CheckPassword($senhaAntiga, $linha["senha"]);
        if(!$senhaCorreta){
            $antigaValida = false;
        }
    }else{
        $antigaValida = false;
    }
}

// variável que armazena se o usuário logado é aluno ou administrador
$admin = unserialize($_SESSION["usuario"]) instanceof Administrador;

if(($antigaValida && $novaValida) || ($admin && $novaValida && $idAlunoValido)){

    $hasher = new PasswordHash(8, false);

    $aluno = null;
    if($admin) {
        $aluno = new Aluno("");
        $aluno->setNumeroInscricao($idAluno);
        $sucesso = $aluno->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);

				if ($sucesso) {
						$mensagem = "Senha alterada com sucesso!";
						$sucesso = true;
				}else {
						$sucesso = false;
						$mensagem = "";
						if(!$sucesso){
								$mensagem = "Erro ao alterar senha!";
						}
				}
    } else {
        $aluno = unserialize($_SESSION["usuario"]);
    }

    if($aluno) {
        // se os dados sao validos, muda a senha
        $sql = "UPDATE Usuario SET senha=? WHERE id=?";
        $query = $conexao->prepare($sql);
        $query->bindParam(1, $hasher->HashPassword($senhaNova), PDO::PARAM_INT);
        $query->bindParam(2, $aluno->getId(), PDO::PARAM_INT);
        $sucesso = $query->execute();

				// altera dados no moodle
				$usuarioMoodle = $dados["usuario_moodle"];
				$senhaMoodle   = $dados["senha_moodle"];

				$sucessoMoodle = false;

				$conMoodle = null;
				try{
						$conMoodle = new PDO("mysql:host=$host;dbname=moodle;charset=utf8", $usuarioMoodle, $senhaMoodle);
				}catch (PDOException $e){
						echo $e->getMessage();
				}
				$queryMoodle = "UPDATE mdl_user SET password=MD5(?) WHERE username=?";
				$query = $conMoodle->prepare($queryMoodle);
				$dadosMoodle = array($senhaNova, $aluno->getLogin());
				$sucessoMoodle = $query->execute($dadosMoodle);

				if ($sucesso && $sucessoMoodle) {
						$mensagem = "Senha alterada com sucesso!";
						$sucesso = true;
				}else {
						$sucesso = false;
						$mensagem = "";
						if(!$sucesso){
								$mensagem = "Erro ao alterar senha!";
						}
						if (!$sucessoMoodle) {
								$mensagem .= "<br>Erro ao alterar no Moodle!";
						}
				}
						
    } else {
        $mensagem = "Erro ao encontrar usuário no banco de dados!";
    }
}else{
    // algum valor invalido foi enviado
    if(!$admin) {
        if(!$antigaValida)
            $mensagem = "Senha incorreta";
        else if(!$novaValida)
            $mensagem = "Senha nova inválida";
    } else {
        if(!$novaValida)
            $mensagem = "Senha nova inválida";
        else if(!$idAlunoValido) {
            $mensagem = "Aluno inválido";
        }
    }
}

// fecha a conexão com o bd
$conexao = null;
$conMoodle = null;

if($admin) {
    header('Location: ../gerenciar_alunos.php?mensagem='.$mensagem.'&sucesso='.$sucesso, true, "302");
} else {
    header('Location: ../index.php?mensagem='.$mensagem.'&sucessoSenha='.$sucesso, true, "302");
}

die();
