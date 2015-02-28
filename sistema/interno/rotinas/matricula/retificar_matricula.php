<?php

session_start();

require("../../entidades/Administrador.php");
require("../../entidades/Aluno.php");

$mensagem = "Você não possui permissão para fazer isso";

// checamos se o usuário está logado e se é administrador
$usuarioValido = isset($_SESSION["usuario"]);

$adminValido = $usuarioValido && unserialize($_SESSION["usuario"]) instanceof Administrador;
$adminValido = $adminValido && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador";
$adminValido = $adminValido && (unserialize($_SESSION["usuario"])->getPermissoes() & 16);

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
    try {
        $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    $idMatricula = $_GET['id'];
    $etapa = $_POST['etapa-retificacao'];
    $idCidade = $_POST['cidade-retificacao'];

    $idValida = isset($idMatricula) && preg_match("/^[0-9]+$/", $idMatricula);
    $etapaValida = isset($etapa) && ($etapa == 1 || $etapa == 2 || $etapa == 3 || $etapa == 4);

    $cidadeValida = isset($idCidade);

    // checamos se a cidade recebida é do ano atual
    if($cidadeValida){
        require_once("../../entidades/Cidade.php");

        $cidade = new Cidade();
        $cidade->setIdCidade($idCidade);
        $encontrada = $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);

        if(!$encontrada) {
            $cidadeValida = false;
            $mensagem = "Essa cidade não foi encontrada no sistema";
        } else if($cidade->getAno() != date("Y")) {
            $cidadeValida = false;
            $mensagem = "Essa cidade não pertence ao ano atual";
        }
    }

    if ($idValida && $etapaValida && $cidadeValida) {
        // Usamos as TRANSACTIONs do MySql para garantir que caso haja
        // algum erro, as tabelas continuem consistentes
        $conexao->beginTransaction();

        $sql = "DELETE FROM PgtoMensalidade WHERE chaveMatricula = ? AND ano = YEAR(CURDATE())";
        $query  = $conexao->prepare($sql);
        $query->bindParam(1, $idMatricula);
        $remocaoMensalidade = $query->execute();

        $sql    = "UPDATE Matricula SET etapa = ?, chaveCidade = ? WHERE idMatricula = ?";
        $query  = $conexao->prepare($sql);
        $query->bindParam(1, $etapa);
        $query->bindParam(2, $idCidade);
        $query->bindParam(3, $idMatricula);
        $sucessoMatricula = $query->execute();

        $sql  = "SELECT ";

        //pega as parcelas de acordo com tipo e modalidade
        //do aluno
        $aluno = new Aluno("");
        $aluno->setNumeroInscricao($_POST["idaluno"]);
        $aluno->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);

        if($aluno->getTipoCurso() === "extensao"){
            if($modalidade == "regular"){
                $sql .= "C.inscricao_extensao_regular
                                as inscricao,
                                C.parcela_extensao_regular
                                as parcela";
            }
            if($modalidade == "intensivo"){
                $sql .= "C.inscricao_extensao_intensivo
                                as inscricao,
                                C.parcela_extensao_intensivo
                                as parcela";
            }
        }else if($aluno->getTipoCurso() === "pos"){
            if($modalidade == "regular"){
                $sql .= "C.inscricao_pos_regular
                                as inscricao,
                                C.parcela_pos_regular
                                as parcela";
            }
            if($modalidade == "intensivo"){
                $sql .= "C.inscricao_pos_intensivo
                                as inscricao,
                                C.parcela_pos_intensivo
                                as parcela";
            }
        }else if($aluno->getTipoCurso() === "instituto"){
            if($modalidade == "regular"){
                $sql .= "C.inscricao_instituto_regular
                                as inscricao,
                                C.parcela_instituto_regular
                                as parcela";
            }
            if($modalidade == "intensivo"){
                $sql .= "C.inscricao_instituto_intensivo
                                as inscricao,
                                C.parcela_instituto_intensivo
                                as parcela";
            }
        }
        $sql .= " FROM Cidade WHERE idCidade = ?";
        $query  = $conexao->prepare($sql);
        $query->bindParam(1, $idCidade);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $sucessoCriaPgto = $query->execute();
        $precoInscricao = -1;
        $precoParcela = -1;
        
        if ($linha = $query->fetch()) {
            $precoInscricao = $linha['inscricao'];
            $precoParcela = $linha['parcela'];
        } else {
            $sucessoCriaPgto = false;
        }

        $queryInsert = "";
        for($i = 0; $i < 12 && $sucessoCriaPgto; $i++){
            if($i == 0){ // parcela numero 0 será considerada valor da
                         // inscrição
                $queryInsert = "INSERT INTO PgtoMensalidade 
                                (chaveMatricula, numParcela, valorTotal, valorPago, 
                                    desconto, fechado,ano) 
                                VALUES (?, '0', ?, '0', '0', '0', YEAR(CURDATE())) ";
                $insertArray  = array($idMatricula, $precoInscricao);

            } 
            else{
                $queryInsert    .= ", (?, ?, ?, '0', '0', '0', YEAR(CURDATE())) ";
                $insertArray[]  = $idMatricula;
                $insertArray[]  = $i;
                $insertArray[]  = $precoParcela;
            }
        }
        $query = $conexao->prepare($queryInsert);
        $sucessoCriaPgto = $sucessoCriaPgto && $query->execute($insertArray);

        if($sucessoCriaPgto && $remocaoMensalidade && $sucessoMatricula) {
            $conexao->commit();
        } else {
            $conexao->rollback();  
            $mensagem = "Erro na edição de dados no banco";
        }

    }

    // Fecha a conexão
    $conexao = null;
}

if($mensagem !== ""){
    $mensagem = "&erro=".$mensagem;
}

if($adminValido) {
    // redirecionamos o admin para o aluno que ele estava visualizando
    header('Location: ../../visualizar_aluno.php?id='.$_POST['idaluno'].$mensagem, true, "302");
} else {
    header('Location: ../../index.php', true, "302");
}
die();