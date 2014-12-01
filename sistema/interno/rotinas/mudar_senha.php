<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require("../phpass-0.3/PasswordHash.php");
require("../entidades/Administrador.php");
require("../entidades/Aluno.php");
require("../entidades/Associado.php");

// Recebe a senha antiga do usuário
// e a senha nova a ser colocada no lugar
// Caso a senha antiga esteja correta, altera
// a senha do usuário armazenado na sessão

$senhaAntiga = $_POST["antiga"];
$senhaNova   = $_POST["nova"];

// checa se os dados sao validos

$antigaValida = isset($senhaAntiga) && mb_strlen($senhaAntiga, 'UTF-8') >= 6 &&
                mb_strlen($senhaAntiga, 'UTF-8') <= 72;
$novaValida   = isset($senhaNova) && mb_strlen($senhaNova, 'UTF-8') >= 6 &&
                mb_strlen($senhaNova, 'UTF-8') <= 72;

// mensagem a ser exibida em caso de erro
$mensagem = "";
$sucesso = false;

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

if($antigaValida){

    $textoQuery  = "SELECT senha FROM Usuario WHERE login=?";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, unserialize($_SESSION["usuario"])->getLogin(), PDO::PARAM_STR);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();

    if ($linha = $query->fetch()){
        $hasher = new PasswordHash(8, false);
        $senhaCorreta = $hasher->CheckPassword($senhaAntiga, $linha["senha"]);
        if(!$senhaCorreta){
            $antigaValida = false;
        }
    }else{
        $antigaValida = false;
    }
}

if($antigaValida && $novaValida){

    $hasher = new PasswordHash(8, false);

    // se os dados sao validos, muda a senha
    $sql = "UPDATE Usuario SET senha=? WHERE id=?";
    $query = $conexao->prepare($sql);
    $query->bindParam(1, $hasher->HashPassword($senhaNova), PDO::PARAM_INT);
    $query->bindParam(2, unserialize($_SESSION["usuario"])->getId(), PDO::PARAM_INT);
    $sucesso = $query->execute();
    if($sucesso){
        $sucesso = true;
    }else{
        $mensagem = "Erro!";
    }
}else{
    // algum valor invalido foi enviado
    if(!$antigaValida)
        $mensagem = "Senha incorreta";
    else if(!$novaValida)
        $mensagem = "Senha nova inválida";
}

// fecha a conexão com o bd
$conexao = null;

header('Location: ../index.php?mensagem='.$mensagem.'&sucessoSenha='.$sucesso, true, "302");
die();