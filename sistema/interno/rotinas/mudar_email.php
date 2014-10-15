<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require("../phpass-0.3/PasswordHash.php");
require("../entidades/Administrador.php");
require("../entidades/Aluno.php");
require("../entidades/Associado.php");

// Recebe a senha  do usuário
// e ao novo E-mail
// Caso a senha esteja correta, altera
// o E-mail do usuário armazenado na sessão

$senha = $_POST["senha"];
$email = $_POST["novo"];

// checa se os dados sao validos

$senhaValida = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 && mb_strlen($senha, 'UTF-8') <= 72;
$emailValido = isset($email) && mb_strlen($email, 'UTF-8') <= 100 && preg_match("/^.+\@.+\..+$/", $email);

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
    $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario,
                       $senhaBD);
}catch (PDOException $e){
    echo $e->getMessage();
}

if($senhaValida){
    $textoQuery  = "SELECT senha FROM Usuario WHERE login=?";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, unserialize($_SESSION["usuario"])->getLogin(), PDO::PARAM_STR);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();

    if ($linha = $query->fetch()){
        $hasher = new PasswordHash(8, false);
        $senhaCorreta = $hasher->CheckPassword($senha, $linha["senha"]);
        if(!$senhaCorreta){
            $senhaValida = false;
        }
    }else{
        $senhaValida = false;
    }
}

if($senhaValida && $emailValido){

    $hasher = new PasswordHash(8, false);

    // se os dados sao validos, muda o E-mail
    $sql = "UPDATE Usuario SET email=? WHERE id=?";
    $query = $conexao->prepare($sql);
    $query->bindParam(1, $email, PDO::PARAM_STR);
    $query->bindParam(2, unserialize($_SESSION["usuario"])->getId(), PDO::PARAM_INT);
    $sucesso = $query->execute();
    if($sucesso){
        $usuario = unserialize($_SESSION["usuario"]);
        $usuario->setEmail($email);
        $_SESSION["usuario"] = serialize($usuario);
        $sucesso = true;
    }else{
        $mensagem = "Erro!";
    }
}else{
    // algum valor invalido foi enviado
    if(!$senhaValida)
        $mensagem = "Senha incorreta";
    else if(!$emailValido)
        $mensagem = "E-mail inválido";
}

// fecha a conexão com o bd
$conexao = null;

header('Location: ../index.php?mensagem='.$mensagem.'&sucessoEmail='.$sucesso, true, "302");
die();