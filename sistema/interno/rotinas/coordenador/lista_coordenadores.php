<?php

// Função que lista todos os coordenadores do sistema
//
// Retorna: Vetor de Administradores, com apenas id, nome e login preenchidos

function listaCoordenadores(){
    require_once("entidades/Administrador.php");

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
    $db      = "homeopatias";
    try{
        $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
    }catch (PDOException $e){
        echo $e->getMessage();
    }

    $textoQuery  = "SELECT U.id, U.cpf, U.dataInscricao, U.email, 
                    U.nome, U.login, A.idAdmin 
                    FROM Usuario U, Administrador A WHERE A.idUsuario = U.id AND 
                    A.nivel = \"coordenador\" ORDER BY U.nome ASC";

    $query = $conexao->prepare($textoQuery);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();

    $numeroRegistros = 0;
    $tabela = "";
    $resultado = [];

    while ($linha = $query->fetch()){
        $novo = new Administrador($linha["login"]);
        $novo->setNome($linha["nome"]);
        $novo->setIdAdmin($linha["idAdmin"]);
        // insere $novo na última posição do vetor
        $resultado[] = $novo;
    }

    return $resultado;
}