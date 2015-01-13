<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // lemos as credenciais do banco de dados
    $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
    $dados = json_decode($dados, true);
    foreach($dados as $chave => $valor) {
        $dados[$chave] = str_rot13($valor);
    }
    $host    = $dados["host"];
    $usuario = $dados["nome_usuario"];
    $senhaBD = $dados["senha"];

    // se o usuário chegou até aqui através de um formulário, altera os dados da definição de trabalho
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id        = $_POST["id"];
        $titulo    = $_POST["titulo"];
        $etapa     = $_POST["etapa"];
        $data      = $_POST["data"];
        $descricao = $_POST["descricao"];

        $idValido        = isset($id) && preg_match("/^\d+$/", $id);
        $tituloValido    = isset($titulo) && mb_strlen($titulo, 'UTF-8') >= 3 &&
                           mb_strlen($titulo, 'UTF-8') <= 300;
        $etapaValida     = isset($etapa) && preg_match("/^[1-4]$/", $etapa);
        $dataValida      = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
        $descricaoValida = isset($descricao) && mb_strlen($descricao, 'UTF-8') <= 10000;

        // se todos os dados estão válidos, a definição de trabalho é criada
        if($idValido && $tituloValido && $etapaValida && $dataValida && $descricaoValida){
            // Cria conexão com o banco
            $conexao = null;
            try{
                $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
            }catch (PDOException $e){
                echo $e->getMessage();
            }

            $comando  = "UPDATE TrabalhoDefinicao SET titulo=?, etapa=?, descricao=?,";
            $comando .= " dataLimite=? WHERE idDefTrabalho=?";
            $query = $conexao->prepare($comando);

            $data = date("Y-m-d H:i:s", strtotime($data));
            $dados  = array($titulo, $etapa, $descricao, $data, $id);
            $sucesso = $query->execute($dados);

            // Encerramos a conexão com o BD
            $conexao = null;

            if($sucesso){
                $mensagem = "";
            }else{
                $mensagem = "Erro na inserção de definição de trabalho";
            }
        }else if(!$tituloValido){
            $mensagem = "Título inválido!";
        }else if(!$etapaValida){
            $mensagem = "Etapa inválida!";
        }else if(!$dataValida){
            $mensagem = "Data inválida!";
        }else if(!$descricaoValida){
            $mensagem = "Descrição inválida!";
        }else if(!$idValido){
            $mensagem = "Essa definição de trabalho não existe";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_definicoes_trabalho.php'.$mensagem, true, "302");
die();