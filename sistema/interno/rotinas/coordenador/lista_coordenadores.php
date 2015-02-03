<?php

// Função que lista todos os coordenadores do sistema
//
// Recebe: O ano no qual devemos procurar os administradores. Se não for
//         passado, procuramos em todos os anos. Caso o ano seja passado, apenas
//         os coordenadores que não estão coordenando esse ano são listados.
// Retorna: Vetor de Administradores, com apenas id, nome e login preenchidos

function listaCoordenadores($ano = false){
    require_once(dirname(__FILE__) . "/../../entidades/Administrador.php");

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

    if(!$ano) {
        $textoQuery  = 'SELECT U.nome, U.login, A.idAdmin
                        FROM Usuario U INNER JOIN Administrador A ON A.idUsuario = U.id
                        WHERE A.idUsuario = U.id AND A.nivel = "coordenador" ORDER BY U.nome ASC';
        $query = $conexao->prepare($textoQuery);
    } else {
        $textoQuery  = 'SELECT U.nome, U.login, A.idAdmin
                        FROM Usuario U INNER JOIN Administrador A ON A.idUsuario = U.id
                        WHERE A.nivel = "coordenador" AND NOT EXISTS(
                            SELECT Ad.idAdmin FROM Administrador Ad LEFT JOIN Cidade C
                            ON C.idCoordenador = Ad.idAdmin WHERE C.idCidade IS NOT NULL
                            AND C.ano = ? AND C.idCoordenador = A.idAdmin
                        )
                        ORDER BY U.nome ASC';
        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $ano);

    }

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