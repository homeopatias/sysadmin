<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados do professor
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id              = $_POST["id"];
        $idAdmin         = $_POST["idAdmin"];
        $nome            = $_POST["nome"];
        $cpf             = $_POST["cpf"];
        $email           = $_POST["email"];
        $login           = $_POST["login"];
        $corrigeTrabalho = $_POST["corrigeTrabalho"] === "on";

        $nomeValido   = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                        mb_strlen($nome, 'UTF-8') <= 100;
        $cpfValido    = validaCpf($cpf,$id);

        $emailValido  = validaEmail($email,$id);
        $loginValido  = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                        mb_strlen($login, 'UTF-8') <= 100;

        $idAdminValido = isset($idAdmin) && preg_match("/^[0-9]*$/", $idAdmin);
        $idValido = isset($id) && preg_match("/^[0-9]*$/", $id);

        // se todos os dados estão válidos, o professor é editado
        if($nomeValido && $cpfValido[0] && $emailValido[0] && $loginValido &&
           $idAdminValido && $idValido){

            // lemos as credenciais do banco de dados
            $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
            $dados = json_decode($dados, true);
            foreach($dados as $chave => $valor) {
                $dados[$chave] = str_rot13($valor);
            }
            $host    = $dados["host"];
            $usuario = $dados["nome_usuario"];
            $senhaBD = $dados["senha"];

            require_once("../../entidades/Administrador.php");

            $atualizar = new Administrador($login);
            $atualizar->setNome($nome);
            $atualizar->setCpf($cpf);
            $atualizar->setEmail($email);
            $atualizar->setId($id);
            $atualizar->setIdAdmin($idAdmin);
            $atualizar->setNivelAdmin("professor");
            $atualizar->setCorrigeTrabalho($corrigeTrabalho);

            $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

            if($sucesso){
                $mensagem = "";
            }else{
                $mensagem = "Já existe alguém com esse nome de usuário no sistema";
            }
        }else if(!$nomeValido){
            $mensagem = "Nome inválido!";
        }else if(!$cpfValido[0]){
            $mensagem = $cpfValido[1];
        }else if(!$emailValido[0]){
            $mensagem = $emailValido[1];
        }else if(!$loginValido){
            $mensagem = "Nome de usuário inválido!";
        }else if(!$idAdminValido || !$idValido){
            $mensagem = "Dados inconsistentes";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

function ValidaCpf($cpf , $id){

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

    // [0] = 1 = houve erro ou 0 = não houve erro
    // [1] = mensagem do erro
    $return = array(1,"");

    // checamos se os dígitos verificadores do cpf conferem
    $cpfChecar = str_replace(".","",$cpf);
    $cpfChecar = str_replace("-","",$cpfChecar);
    $cpfNumerico = $cpfChecar;
    $cpfChecar = str_split($cpfChecar);
    $somaChecagem = 0;
    for($i = 10; $i >= 2; $i = $i - 1){
        $somaChecagem += (int)($cpfChecar[10 - $i]) * $i;
    }
    $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
    if($digito != $cpfChecar[9]){
        $return[0] = 0;
        $return[1] = "CPF inválido";
    }else{
        // agora checamos o segundo dígito
        $somaChecagem = 0;
        for($i = 11; $i >= 2; $i = $i - 1){
            $somaChecagem += (int)($cpfChecar[11 - $i]) * $i;
        }
        $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
        if($digito != $cpfChecar[10]){
            $return[0] = 0;
            $return[1] = "CPF inválido";
        }
    }

    //Checa se o CPF é diferente de 00000000000 e 99999999999
    $todosZero = true;
    $todosNove = true;
    for($i = 0; $i <11; $i++){
        if($cpfChecar[$i] != '0'){
            $todosZero = false;
        }
        if($cpfChecar[$i] != '9'){
            $todosNove = false;
        }
    }

    if($todosZero || $todosNove){
        $return[0] = 0;
        $return[1] = "CPF inválido";
    }

    //Checa se ja existe este cpf no sistema cadastrado como Professor
    $textoQuery = "SELECT U.cpf , U.id
                   FROM Usuario U , Administrador A
                   WHERE U.id = A.idUsuario AND U.cpf = ?
                   AND A.nivel LIKE 'professor'";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, $cpfNumerico, PDO::PARAM_STR);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();
    
    if($linha = $query->fetch()){
        if($linha["id"] != $id){
            $return[0] = 0;
            $return[1] = "CPF ja registrado no sistema";
        }
        
    }
  
   if( !(isset($cpf) && (preg_match("/^\d{3}\.?\d{3}\.?\d{3}\-?\d{2}$/", $cpf) || 
       preg_match("/^\d{11}$/", $cpf)) ) ){
        $return[0] = 0;
        $return[1] = "CPF em formato inválido";
   }

    return $return;
}

function validaEmail($email, $id){
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

    // [0] = 1 = houve erro ou 0 = não houve erro
    // [1] = mensagem do erro
    $return = array(1,"");

    //Checa se ja existe este email no sistema cadastrado como Professor
    $textoQuery = "SELECT U.email
                   FROM Usuario U , administrador A
                   WHERE U.id = A.idUsuario AND U.email = ?
                   AND A.nivel LIKE 'professor'";
    
    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1,$email, PDO::PARAM_STR);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();
    
    if($linha = $query->fetch()){
        if($linha["id"] != $id){
            $return[0] = 0;
            $return[1] = "E-mail ja registrado no sistema";
        }
    }

    if( !(isset($email) && mb_strlen($email, 'UTF-8') <= 100 &&
       preg_match("/^.+\@.+\..+$/", $email) ) ) {
        $return[0] = 0;
        $return[1] = "E-mail em formato inválido";
    }

    return $return;
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_professores.php'.$mensagem, true, "302");
die();