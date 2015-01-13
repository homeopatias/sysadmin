<?php
session_start();
require_once("../entidades/Aluno.php");

if(isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Aluno) {
    // lemos as credenciais do banco de dados
    $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
    $dados = json_decode($dados, true);

    foreach($dados as $chave => $valor) {
        $dados[$chave] = str_rot13($valor);
    }

    $host    = $dados["host"];
    $usuario = $dados["nome_usuario"];
    $senhaBD = $dados["senha"];

    // cria conexão com o banco para uso ao longo da página
    $conexao = null;
    $db      = "homeopatias";
    try {
        $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    $textoQuery = "UPDATE Notificacao SET lida = 1 WHERE chaveAluno = ?";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, unserialize($_SESSION['usuario'])->getNumeroInscricao());
    $sucesso = $query->execute();

    if($sucesso) {
        echo "1";
    } else {
        echo "0";
    }
}

echo "0";