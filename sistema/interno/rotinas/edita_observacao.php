<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../entidades/Administrador.php");

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

    $idAluno = $_POST["id"];
    $obs     = $_POST["observacoes"];

    $sucesso = false;
    $atualizar = null;

    if( isset($idAluno) && preg_match("/^[0-9]+$/", $idAluno)){

        require_once("../entidades/Aluno.php");

        $atualizar = new Aluno("");
        $atualizar->setNumeroInscricao($idAluno);
        $atualizar->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);
        $atualizar->setObservacao($obs);

        $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);
    }

}

header('Location: ../visualizar_aluno.php?id=' . $idAluno, true, "302");
die();
