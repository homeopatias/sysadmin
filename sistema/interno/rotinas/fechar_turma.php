<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require("../entidades/Administrador.php");

if(!isset($_SESSION['usuario']) || 
   !(unserialize($_SESSION['usuario']) instanceof Administrador &&
     unserialize($_SESSION['usuario'])->getNivelAdmin === "coordenador")){
    header('Location: ../index.php'.$sucesso, true, "302");
    die();
}

// Recebe os dados da turma e confere se estão válidos
$idCidade = $_GET["idCidade"];
$etapa    = $_GET["etapa"];

$idCidadeValido = preg_match("/^[0-9]+$/", $idCidade);
$etapaValida = $etapa == 1 || $etapa == 2 || $etapa == 3 || $etapa == 4;

// mensagem a ser exibida em caso de erro
$mensagem = "";
$sucesso = false;

if($idCidadeValido && $etapaValida){
    // lemos as credenciais do banco de dados
    $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
    $dados = json_decode($dados, true);
    foreach($dados as $chave => $valor) {
        $dados[$chave] = str_rot13($valor);
    }
    $host    = $dados["host"];
    $usuario = $dados["nome_usuario"];
    $senhaBD = $dados["senha"];

    // cria a conexão com o banco
    $conexao = null;
    try{
        $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8",
                           $usuario, $senhaBD);
    }catch (PDOException $e){
        echo $e->getMessage();
    }

    // Confere os dados de cada aluno para determinar
    // quem está aprovado e quem está reprovado
    $textoQuery  = "SELECT AVG(IFNULL(T.nota, 0)) as mediaTrabalhos,
                    AVG(IFNULL(F.presenca, 0)) as frequenciaMedia, M.idMatricula
                    FROM Matricula M INNER JOIN Cidade C ON
                    C.idCidade = M.chaveCidade INNER JOIN Aluno A ON 
                    M.chaveAluno = A.numeroInscricao LEFT JOIN Trabalho T ON
                    T.chaveAluno = M.chaveAluno LEFT JOIN TrabalhoDefinicao TD ON
                    TD.idDefTrabalho = T.chaveDefinicao AND IFNULL(TD.ano, 0) = C.ano AND
                    TD.etapa = M.etapa INNER JOIN Aula Au ON
                    Au.chaveCidade = C.idCidade AND Au.etapa = M.etapa LEFT JOIN Frequencia F ON
                    F.chaveAluno = M.chaveAluno AND F.chaveAula = Au.idAula WHERE 
                    C.idCidade = ? AND M.etapa = ? AND C.idCoordenador = ?
                    GROUP BY T.chaveAluno, F.chaveAluno";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, $idCidade, PDO::PARAM_INT);
    $query->bindParam(2, $etapa, PDO::PARAM_INT);
    $query->bindParam(3, $coordenadorId, PDO::PARAM_INT);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();

    $numAlunos = 0;

    // Usamos as transactions do MySQL para garantir a integridade do código
    $conexao->beginTransaction():
    $sucesso = true;

    while ($linha = $query->fetch() && $sucesso){
        $aprovado = $linha['mediaTrabalhos'] >= 70 && $linha['mediaFrequencia'] >= 80;
        $fechaAluno = $conexao->prepare("UPDATE Matricula SET aprovado = ? WHERE idMatricula = ?");
        $fechaAluno->bindParam(1, $aprovado, PDO::PARAM_INT);
        $fechaAluno->bindParam(2, $linha['idMatricula'], PDO::PARAM_INT);
        $sucesso = $fechaAluno->execute();
    }

    if ($sucesso) {
        // todas as operações foram bem-sucedidas, confirmamos as mudanças
        $conexao->commit();
        $mensagem = "Turma fechada!";
    } else {
        // alguma operação falhou, exibimos um erro
        $conexao->rollback();
        $mensagem = "Erro no fechamento de turma";
    }
    // fecha a conexão com o bd
    $conexao = null;
} else {
    if(!$idCidadeValido)
        $mensagem = "Cidade inválida!";
    else if(!$etapaValida)
        $mensagem = "Etapa inválida!"
}

header('Location: ../visualizar_turmas.php?mensagem='.$mensagem.'&sucesso='.$sucesso, true, "302");
die();