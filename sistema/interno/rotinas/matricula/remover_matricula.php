<?php

session_start();

require("../../entidades/Administrador.php");
require("../../entidades/Aluno.php");

$mensagem = "Você não possui permissão para fazer isso";

// checamos se o usuário está logado e se é administrador ou aluno
$usuarioValido = isset($_SESSION["usuario"]);

$adminValido = $usuarioValido && unserialize($_SESSION["usuario"]) instanceof Administrador;
$adminValido = $adminValido && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador";

$alunoValido = $usuarioValido && unserialize($_SESSION["usuario"]) instanceof Aluno;

if($adminValido || $alunoValido){
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

    $sql = "DELETE FROM Matricula WHERE idMatricula = ?";

    // verificação de segurança para que um aluno não possa
    // deletar a matrícula de outro aluno
    if ($alunoValido) {
        $sql .= " AND chaveAluno = ?";
    }

    $dados  = array($_GET["id"]);
    $query  = $conexao->prepare($sql);

    // continuando verificação de segurança acima
    if ($alunoValido) {
        $dados[] = unserialize($_SESSION['usuario'])->getNumeroInscricao();        
    }

    $sucessoMat = $query->execute($dados);

    /*$sql    = "DELETE FROM PgtoMensalidade WHERE chaveMatricula = ?
               AND valorPago = 0 AND fechado = 0";
    $dados  = array($_GET["id"]);
    $query  = $conexao->prepare($sql);
    $sucessoPgto = $query->execute($dados);
    */

    if(!$sucessoMat || $query->rowCount() == 0) {
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

if($adminValido) {
    // redirecionamos o admin para o aluno que ele estava visualizando
    header('Location: ../../visualizar_aluno.php?id='.$_GET["aluno"].$mensagem, true, "302");
} else if ($alunoValido) {
    // redirecionamos o aluno para sua tela
    header('Location: ../../visualizar_informacoes_curso.php?'.$mensagem, true, "302");
} else {
    header('Location: ../../index.php', true, "302");
}
die();