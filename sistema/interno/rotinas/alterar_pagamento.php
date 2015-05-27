<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require("../entidades/Administrador.php");

$usuarioLogado = isset($_SESSION["usuario"]) ? unserialize($_SESSION["usuario"]) : false;
$mensagem = "";

$idAluno = $_POST["idAluno"];

if( $usuarioLogado && $usuarioLogado instanceof Administrador) {

    // Recebe os dados
    $idPag     = $_POST["idPag"];
    $parcela   = $_POST["parcela"];
    $valorPago = $_POST["pago"];
    $desconto  = $_POST["desconto"];
    $data      = $_POST["data"];
    $metodo    = $_POST["metodo"];

    $idPagValido     = isset($idPag) && preg_match("/^[0-9]*$/", $idPag);
    $parcelaValida   = isset($parcela) && preg_match("/^[0-9]*\.?[0-9]+$/", $parcela);
    $valorPagoValido = isset($valorPago) && preg_match("/^[0-9]*\.?[0-9]+$/", $valorPago);
    $descValido      = isset($desconto) && ($desconto >= 0 && $desconto <= 100 && !is_nan($desconto));

    if($idPagValido && $parcelaValida && $valorPagoValido && $descValido) {

        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);
        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }
        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        // Cria conexão com o banco
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario,
                               $senhaBD);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        // determinamos se o pagamento foi fechado
        $fechado = false;
        if($valorPago >= $parcela - ($desconto * $parcela)/100) {
            $fechado = true;
        }

        $textoQuery  = "UPDATE PgtoMensalidade SET valorTotal=?, valorPago=?, desconto=?, fechado=?, data=?, metodo=? ";
        $textoQuery .= "WHERE idPagMensalidade = ?";

        $query = $conexao->prepare($textoQuery);

        $query->bindParam(1, $parcela);
        $query->bindParam(2, $valorPago);
        $query->bindParam(3, $desconto);
        $query->bindParam(4, $fechado);
        $query->bindParam(5, $data);
        $query->bindParam(6, $metodo);
        $query->bindParam(7, $idPag);

        $sucesso = $query->execute();
        
        if(!$sucesso) {
            $mensagem = 'Falha na edição de pagamento';
        }
    }
}

if($mensagem !== ""){
    $mensagem = "&erro=".$mensagem;
}

header('Location: ../visualizar_aluno.php?id=' . $idAluno . $mensagem, true, "302");
die();