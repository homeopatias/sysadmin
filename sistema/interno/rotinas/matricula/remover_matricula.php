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

    // Usamos as TRANSACTIONs do MySql para garantir que caso haja
    // algum erro, as tabelas continuem consistentes
    $conexao->beginTransaction();

    $sql    = "DELETE FROM Matricula WHERE idMatricula = ?";
    $dados  = array($_GET["id"]);
    $query  = $conexao->prepare($sql);
    $sucessoMat = $query->execute($dados);

    /*$sql    = "DELETE FROM PgtoMensalidade WHERE chaveMatricula = ?
               AND valorPago = 0 AND fechado = 0";
    $dados  = array($_GET["id"]);
    $query  = $conexao->prepare($sql);
    $sucessoPgto = $query->execute($dados);
    */

    if(!$sucessoMat) {
        $conexao->rollBack();
        $mensagem = "Erro na remoção de matrícula";
    } /*else if(!$sucessoPgto){
        $conexao->rollBack();
        $mensagem = "Erro na remoção dos pagamentos referentes a essa matrícula";
    } */else {
        $conexao->commit();  
        $mensagem = "";
    }

    // Fecha a conexão
    $conexao = null;
}

if($mensagem !== ""){
    $mensagem = "&erro=".$mensagem;
}

// redirecionamos o usuário para o aluno que ele estava visualizando
header('Location: ../../visualizar_aluno.php?id='.$_GET["aluno"].$mensagem, true, "302");
die();