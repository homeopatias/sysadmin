<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados do artigo
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id       = $_POST["id"];
        $titulo   = $_POST["titulo"];
        $autor    = $_POST["autor"];
        $conteudo = $_POST["conteudo"];

        $idValido       = isset($id) && preg_match("/^[0-9]+$/", $id);
        $tituloValido   = isset($titulo) && mb_strlen($titulo, 'UTF-8') >= 3 &&
                          mb_strlen($titulo, 'UTF-8') <= 100;
        $autorValido    = isset($autor) && mb_strlen($autor, 'UTF-8') >= 3 &&
                          mb_strlen($autor, 'UTF-8') <= 100;
        $conteudoValido = isset($conteudo) && mb_strlen($conteudo, 'UTF-8') <= 65535;

        // se todos os dados estão válidos, o artigo é editado
        if($idValido && $tituloValido && $autorValido && $conteudoValido){
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
                $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
            }catch (PDOException $e){
                echo $e->getMessage();
            }

            $comando  = "UPDATE Artigo SET autor=?, titulo=?, conteudo=? ";
            $comando .= "WHERE idArtigo = ? AND tipo = 'artigo'";
            $query = $conexao->prepare($comando);
            $dados  = array($autor, $titulo, $conteudo, $id);
            $sucesso = $query->execute($dados);

            // Encerramos a conexão com o BD
            $conexao = null;

            if($sucesso){
                $mensagem = "";
            }else{
                $mensagem = "Erro na edição de artigo";
            }
        }else if(!$tituloValido){
            $mensagem = "Título inválido!";
        }else if(!$autorValido){
            $mensagem = "Autor inválido!";
        }else if(!$conteudoValido){
            $mensagem = "Conteúdo inválido!";
        }else if(!$idValido){
            $mensagem = "Dados inconsistentes";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_artigos.php'.$mensagem, true, "302");
die();