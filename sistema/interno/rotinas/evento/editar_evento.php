<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados do evento
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id        = $_POST["id"];
        $data      = $_POST["data"];
        $horario   = $_POST["horario"];
        $local     = $_POST["local"];
        $descricao = $_POST["descricao"];
        $titulo    = $_POST["titulo"];

        $idValido        = isset($id) && preg_match("/^[0-9]+$/", $id);
        $dataValida      = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
        $horarioValido   = isset($horario) && preg_match("/^\d{2}:\d{2}$/", $horario);
        $localValido     = isset($local) && mb_strlen($local, 'UTF-8') >= 3 &&
                           mb_strlen($local, 'UTF-8') <= 500;
        $descricaoValida = !isset($descricao) || mb_strlen($descricao, 'UTF-8') <= 3000;
        $tituloValido    = isset($titulo) && mb_strlen($titulo, 'UTF-8') >= 3 &&
                           mb_strlen($titulo, 'UTF-8') <= 100;

        // se todos os dados estão válidos, o evento é editado
        if($idValido && $dataValida && $horarioValido && $localValido && $descricaoValida && $tituloValido){
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

            if(!isset($descricao) || $descricao === ""){
                $descricao = null;
            }

            $data = date("Y-m-d H:i:s", strtotime($data . " " . $horario . ":00"));
            $comando  = "UPDATE Evento SET dataEvento=?, titulo=?, local=?, descricao=? ";
            $comando .= "WHERE idEvento = ?";
            $query = $conexao->prepare($comando);
            $dados  = array($data, $titulo, $local, $descricao, $id);
            $sucesso = $query->execute($dados);

            // Encerramos a conexão com o BD
            $conexao = null;

            if($sucesso){
                $mensagem = "";
            }else{
                $mensagem = "Erro na edição de evento";
            }
        }else if(!$dataValida){
            $mensagem = "Data inválida!";
        }else if(!$horarioValido){
            $mensagem = "Horário inválido!";
        }else if(!$localValido){
            $mensagem = "Local inválido!";
        }else if(!$descricaoValida){
            $mensagem = "Descrição inválida!";
        }else if(!$tituloValido){
            $mensagem = "Título inválido!";
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

header('Location: ../../gerenciar_eventos.php'.$mensagem, true, "302");
die();