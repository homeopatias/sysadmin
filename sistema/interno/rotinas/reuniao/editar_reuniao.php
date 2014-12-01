<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados da notícia
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id        = $_POST["id"];
        $tema      = $_POST["tema"];
        $data      = $_POST["data"];
        $horario   = $_POST["horario"];
        $local     = $_POST["local"];
        $descricao = $_POST["descricao"];

        $idValido        = isset($id) && preg_match("/^[0-9]+$/", $id);
        $temaValido      = isset($tema) && mb_strlen($tema, 'UTF-8') >= 3 &&
                           mb_strlen($tema, 'UTF-8') <= 200;
        $dataValida      = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
        $horarioValido   = isset($horario) && preg_match("/^\d{2}:\d{2}$/", $horario);
        $localValido     = isset($local) && mb_strlen($local, 'UTF-8') >= 3 &&
                           mb_strlen($local, 'UTF-8') <= 500;
        $descricaoValida = isset($descricao) && mb_strlen($descricao, 'UTF-8') <= 3000;

        // se todos os dados estão válidos, a reunião é criada
        if($idValido && $temaValido && $dataValida && $horarioValido && $localValido &&
           $descricaoValida){
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

            $comando  = "UPDATE Reuniao SET tema=?, data=?, local=?, descricao=? 
                         WHERE idReuniao = ?";
            $query = $conexao->prepare($comando);
            $dados  = array($tema, $data . " " . $horario, $local, $descricao, $id);
            $sucesso = $query->execute($dados);

            // Encerramos a conexão com o BD
            $conexao = null;

            if($sucesso){
                $mensagem = "";
            }else{
                $mensagem = "Erro na edição de reunião";
            }
        }else if(!$temaValido){
            $mensagem = "Tema inválido!";
        }else if(!$dataValida){
            $mensagem = "Data inválida!";
        }else if(!$horarioValido){
            $mensagem = "Horário inválido!";
        }else if(!$localValido){
            $mensagem = "Local inválido!";
        }else if(!$descricaoValida){
            $mensagem = "Descrição inválida!";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_reunioes.php'.$mensagem, true, "302");
die();