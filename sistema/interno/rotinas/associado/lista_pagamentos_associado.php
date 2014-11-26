<?php

// Essa página serve para retornar a lista de pagamentos do associado usando JSON para que
// possa ser recebido no AJAX

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

$textoQuery = "SELECT inscricao, valorTotal, valorPago,
                data, ano
                FROM PgtoAnuidade
                WHERE chaveAssoc = :idAssoc";
$queryPagamentos = $conexao->prepare($textoQuery);
$idAssoc = htmlspecialchars($_POST["idAssoc"]);


$queryPagamentos->bindParam(":idAssoc",$idAssoc,PDO::PARAM_INT);
$queryPagamentos->setFetchMode(PDO::FETCH_ASSOC);
$queryPagamentos->execute();


$resultado = array();

// O JSON de cada coordenador é feito manualmente,
// pois a função json_encode está convertendo com falhas
while( $linha = $queryPagamentos->fetch() ){
    $json_objeto  = '';
    $json_objeto .= '{';
    $json_objeto .= '"inscricao": "' . $linha["inscricao"] . '",';
    $json_objeto .= '"valorTotal": "' . $linha["valorTotal"] . '",';
    $json_objeto .= '"valorPago": "' . $linha["valorPago"] . '",';
    $json_objeto .= '"data": "' . (!is_null($linha["data"]) ?
                                  date("d/m/Y H:i:s", strtotime($linha["data"])) :
                                  null) . '",';
    $json_objeto .= '"ano": "' . $linha["ano"] . '"';
    $json_objeto .= '}';
    $resultado[] = $json_objeto;
}

echo json_encode($resultado);