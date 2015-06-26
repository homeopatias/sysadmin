<?php

session_start();

require_once("../entidades/Administrador.php");
require_once("../entidades/Aluno.php");

$logado = isset($_SESSION['usuario']) ? unserialize($_SESSION["usuario"]) : false;

if($logado && $logado instanceof Aluno && $logado->getIdAdminLogado() != -1 ) {

    $idAdmin = $logado->getIdAdminLogado();
    $dadosBusca = $logado->getDadosBusca();

    session_destroy();
    session_start();

    // lemos as credenciais do banco de dados
    $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
    $dados = json_decode($dados, true);
    foreach($dados as $chave => $valor) {
        $dados[$chave] = str_rot13($valor);
    }
    $host    = $dados["host"];
    $usuario = $dados["nome_usuario"];
    $senhaBD = $dados["senha"];

    $admin = new Administrador('');
    $admin->setIdAdmin($idAdmin);

    $sucesso = $admin->recebeAdminId($host, "homeopatias", $usuario, $senhaBD, "administrador");
    if(!$sucesso) {
        header('Location: ../index.php?mensagem='. 'Houve um erro em obter o administrador atual'.
               '&sucessoEdicao=0', true, "302");
        die();
    }

    $_SESSION['usuario'] = serialize($admin);

    header('Location: ../gerenciar_alunos.php?' . $dadosBusca, true, "302");
    die();
}

session_destroy();
header('Location: ../index.php', true, "302");
die();