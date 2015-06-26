<?php
session_start();

require_once("../entidades/Administrador.php");
require_once("../entidades/Aluno.php");

$mensagem = '';

$logado = isset($_SESSION['usuario']) ? unserialize($_SESSION['usuario']) : false;
if($logado && $logado instanceof Administrador && $logado->getNivelAdmin() === "administrador" &&
              (1 & $logado->getPermissoes()) ) {

    $idAdminAtual = $logado->getIdAdmin();
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

    $aluno = new Aluno('');
    $aluno->setNumeroInscricao($_GET['idAluno']);

    $sucesso = $aluno->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);
    if(!$sucesso) {
        header('Location: ../index.php?mensagem='. 'Houve um erro em obter o aluno para visualização'.
               '&sucessoEdicao=0', true, "302");
        die();
    }

    $aluno->setIdAdminLogado($idAdminAtual);
    $aluno->setDadosBusca($_GET['filtros']);
    $_SESSION['usuario'] = serialize($aluno);

    header('Location: ../index.php', true, "302");
    die();
}

header('Location: ../index.php?mensagem='. 'Seu usuário não tem permissão para fazer isso'.'&sucessoEdicao=0',
       true, "302");
die();