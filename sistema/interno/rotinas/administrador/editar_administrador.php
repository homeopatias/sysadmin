<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados do administrador
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id          = $_POST["id"];
        $idAdmin     = $_POST["idAdmin"];
        $nome        = $_POST["nome"];
        $email       = $_POST["email"];
        $login       = $_POST["login"];
        $permissoes  = $_POST["permissoes"];


        $nomeValido   = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                        mb_strlen($nome, 'UTF-8') <= 100;

        $emailValido  = validaEmail($email , $id);
        $loginValido  = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                        mb_strlen($login, 'UTF-8') <= 100;

        $idAdminValido = isset($idAdmin) && preg_match("/^[0-9]*$/", $idAdmin);
        $idValido = isset($id) && preg_match("/^[0-9]*$/", $id);

        // se todos os dados estão válidos, o administrador é editado
        if($id != "1" && $nomeValido && $emailValido && $loginValido && $idAdminValido &&
           $idValido){

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
            $atualizar->setEmail($email);
            $atualizar->setId($id);
            $atualizar->setPermissoes($permissoes);
            $atualizar->setIdAdmin($idAdmin);
            $atualizar->setNivelAdmin("administrador");

            $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

            if($sucesso){
                $mensagem = "";
            }else{
                $mensagem = "Já existe alguém com esse nome de usuário no sistema";
            }
        }else if(!$nomeValido){
            $mensagem = "Nome inválido!";
        }else if(!$emailValido[0]){
            $mensagem = $emailValido[1];
        }else if(!$loginValido){
            $mensagem = "Nome de usuário inválido!";
        }else if(!$idAdminValido || !$idValido){
            $mensagem = "Dados inconsistentes";
        }else if($id == "1"){
            $mensagem = "Dados inconsistentes";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
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

    //Checa se ja existe este email no sistema cadastrado como Administrador
    $textoQuery = "SELECT U.email, U.id
                   FROM Usuario U , Administrador A
                   WHERE U.id = A.idUsuario AND U.email = ?
                   AND A.nivel LIKE 'administrador'";
    
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

header('Location: ../../gerenciar_administradores.php'.$mensagem, true, "302");
die();
