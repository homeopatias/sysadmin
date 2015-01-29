<?php

require_once(dirname(__FILE__).'/../../entidades/Associado.php');

// função que confere se o associado logado está com todos os pagamentos em dia

function checa_situacao_pagamentos(){
    $usuarioLogado = unserialize($_SESSION['usuario']);
    if($usuarioLogado instanceof Associado) {
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

        // selecionamos todos os pagamentos em aberto dos outros anos, e também selecionamos
        // a inscrição desse ano caso ela esteja em aberto
        $textoQuery = "SELECT NOT EXISTS
                           (SELECT idPagAnuidade 
                            FROM PgtoAnuidade
                            WHERE chaveAssoc = ? AND fechado = 0 AND (ano <> YEAR(CURDATE())
                            OR inscricao = 1))
                       AND EXISTS 
                           (SELECT idPagAnuidade FROM PgtoAnuidade WHERE ano = YEAR(CURDATE())
                            AND chaveAssoc = ?) as emDia";

        // se o associado estiver em dia com os pagamentos, essa query não deve retornar nada
        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $usuarioLogado->getIdAssoc());
        $query->bindParam(2, $usuarioLogado->getIdAssoc());
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        $emDia = $query->fetch()['emDia'];

        $conexao = null;

        return $emDia;
    }
    return false;
}

function checa_situacao_pagamentos_por_id($idAssociado){
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

        // selecionamos todos os pagamentos em aberto dos outros anos, e também selecionamos
        // a inscrição desse ano caso ela esteja em aberto
        $textoQuery = "SELECT NOT EXISTS
                           (SELECT idPagAnuidade 
                            FROM PgtoAnuidade
                            WHERE chaveAssoc = ? AND fechado = 0 AND (ano <> YEAR(CURDATE())
                            OR inscricao = 1))
                       AND EXISTS 
                           (SELECT idPagAnuidade FROM PgtoAnuidade WHERE ano = YEAR(CURDATE())
                            AND chaveAssoc = ?) as emDia";

        // se o associado estiver em dia com os pagamentos, essa query não deve retornar nada
        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $idAssociado);
        $query->bindParam(2, $idAssociado);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        $emDia = $query->fetch()['emDia'];

        $conexao = null;

        return $emDia;

}