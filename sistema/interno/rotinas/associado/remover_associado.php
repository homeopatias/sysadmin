<?php

session_start();

require("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

// checamos se o usuário está logado e se é administrador
$adminValido = isset($_SESSION["usuario"]);
$adminValido = $adminValido && unserialize($_SESSION["usuario"]) instanceof Administrador;
$adminValido = $adminValido && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador";

if($adminValido){
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
    try{
        $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
    }catch (PDOException $e){
        echo $e->getMessage();
    }

    // deletamos apenas da tabela Usuario, pois devido à propriedade CASCADE da
    // chave estrangeira de Associado, o associado será automaticamente removido
    $sql    = "DELETE FROM Usuario WHERE id = ?";
    $dados  = array($_GET["id"]);
    $query  = $conexao->prepare($sql);
    $sucesso = $query->execute($dados);

    // Fecha a conexão
    $conexao = null;

    if($sucesso) {
        $mensagem = "";
    } else {
        $mensagem = "Erro na remoção de associado";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_associados.php'.$mensagem, true, "302");
die();