<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
   unserialize($_SESSION["usuario"])->getPermissoes() & 1 ){

    // lemos as credenciais do banco de dados
    $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
    $dados = json_decode($dados, true);
    foreach($dados as $chave => $valor) {
        $dados[$chave] = str_rot13($valor);
    }
    $host    = $dados["host"];
    $usuario = $dados["nome_usuario"];
    $senhaBD = $dados["senha"];

    $idAluno = $_GET["id"];

    $sucesso = false;
    $atualizar = null;

    if( isset($idAluno) && preg_match("/^[0-9]+$/", $idAluno)){

        require_once("../entidades/Aluno.php");

        $atualizar = new Aluno("");
        $atualizar->setNumeroInscricao($idAluno);
        $atualizar->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);
        $atualizar->setAtivo(true);

        $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);
    }

    if(!$sucesso) {
        $mensagem = "Id de aluno inválida";
    } else {
        // enviamos um email ao aluno avisando que seus documentos foram aprovados
        $assunto = "Homeopatias.com - Documentação aprovada";
        $msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la.</b>";
        $msg .= "<br>Sua documentação já foi aprovada, você já pode acessar o sistema e está inscrito no";
        $msg .= " curso de pós-graduação!";
        $msg .= "<br><br>Obrigado,<br>Equipe Homeobrás.";
        $headers = "Content-type: text/html; charset=utf-8 " .
            "From: Sistema Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
            "Reply-To: noreply@homeopatias.com" . "\r\n" .
            "X-Mailer: PHP/" . phpversion();

        mail($atualizar->getEmail(), $assunto, $msg, $headers);

        $mensagem = "Aluno ativado!";
    }

}

if($mensagem !== ""){
    $mensagem = "mensagem=".$mensagem;
}

header('Location: ../gerenciar_alunos.php?'.$mensagem.'&sucesso='.$sucesso.'&pagina='.htmlentities($_GET["pagina"]), true, "302");
die();
