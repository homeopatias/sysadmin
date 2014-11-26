<?php

require_once("../../entidades/Associado.php");

session_start();

$logado = isset($_SESSION['usuario']) ? unserialize($_SESSION['usuario']) : false;
if ($logado instanceof Associado && $logado->getEnviouDocumentos() && isset($_POST['submit'])) {

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

    $textoQuery = "SELECT EXISTS(SELECT nome FROM Instituicao WHERE inicioInsc <= CURDATE() AND
                              fimInsc >= CURDATE() AND nome = ?)
                          as inscAberta,
                    
                          EXISTS(SELECT P.idPagAnuidade FROM PgtoAnuidade P, Instituicao I
                              WHERE P.ano = I.ano AND I.nome = ? AND P.chaveAssoc = ?)
                          as inscritoAno,

                          valorInscricao, valorAnuidade, ano

                   FROM Instituicao WHERE inicioInsc <= CURDATE() AND
                                          fimInsc >= CURDATE() AND nome = ?";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, $logado->getInstituicao());
    $query->bindParam(2, $logado->getInstituicao());
    $query->bindParam(3, $logado->getIdAssoc());
    $query->bindParam(4, $logado->getInstituicao());
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();

    $linha = $query->fetch();
    $inscAberta = $linha['inscAberta'];
    $inscritoAno = $linha['inscritoAno'];
    $valorInsc = $linha['valorInscricao'];
    $valorAnuidade = $linha['valorAnuidade'];
    $ano = $linha['ano'];

    if ($inscAberta && !$inscritoAno) {
        // agora registramos os pagamentos que esse associado deverá fazer
        $queryPgtos = "INSERT INTO PgtoAnuidade (chaveAssoc, inscricao,
                       valorTotal, valorPago, data, ano, fechado) VALUES
                       (?, 1, ?, 0, NULL, ?, 0), (?, 0, ?, 0, NULL, ?, 0)";
        $query = $conexao->prepare($queryPgtos);
        $dados = array($logado->getIdAssoc(), $valorInsc, $ano, $logado->getIdAssoc(),
                       $valorAnuidade, $ano);
        $sucessoPgtos = $query->execute($dados);

        if ($sucessoPgtos) {
            header('Location: ../../index.php?assocRenovada=true', true, "302");
            die();
        } else {
            header('Location: ../../index.php?mensagem=Não foi possível renovar a associação', true, "302");
            die();
        }
    }
} else {
    header('Location: ../../index.php?mensagem=Não foi possível renovar a associação', true, "302");
    die();
}