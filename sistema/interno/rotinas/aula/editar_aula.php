<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");
require_once("../../entidades/Cidade.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados da aula
    if(isset($_POST["submit"])){

        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);
        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }
        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        // validamos todos os dados recebidos
        $id          = $_POST["idAula"];
        $data        = $_POST["data-edita-aula"];
        $horario     = $_POST["horario"];
        $idCidade    = $_POST["cidade-edita-aula"];
        $etapa       = $_POST["etapa"];
        $idProfessor = $_POST["prof"];
        $descricao   = $_POST["descricao"];

        $idProfessorAdicional  = $_POST["prof-adicional"];
        $idProfessorSecundario = $_POST["prof-secundario"];

        $idValido          = isset($id) && preg_match("/^[0-9]+$/", $id);
        $dataValida        = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
        $horarioValido     = isset($horario) && preg_match("/^\d{2}:\d{2}$/", $horario);
        $idCidadeValido    = isset($idCidade) && preg_match("/^[0-9]+$/", $idCidade);
        $etapaValida       = isset($etapa) && preg_match("/^[1-4]$/", $etapa);
        $idProfessorValido = isset($idProfessor) && 
                             (preg_match("/^[0-9]+$/", $idProfessor) || $idProfessor == -1);
        $descricaoValida = isset($descricao) && mb_strlen($descricao, 'UTF-8') <= 10000;

        $idProfessorPrimarioValido   = isset($idProfessorAdicional) && 
                             (preg_match("/^[0-9]+$/", $idProfessorAdicional) 
                                || $idProfessorAdicional == -1);
        $idProfessorSecundarioValido = isset($idProfessorSecundario) && 
                             (preg_match("/^[0-9]+$/", $idProfessorSecundario) 
                                || $idProfessorSecundario == -1);


        // checamos se a cidade recebida pertence ao ano recebido
        if($dataValida && $idCidadeValido){
            require_once("../../entidades/Cidade.php");

            $ano = date("Y", strtotime($data));
            $cidade = new Cidade();
            $cidade->setIdCidade($idCidade);
            $encontrada = $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);

            if(!$encontrada){
                $idCidadeValido = false;
                $mensagem = "Essa cidade não foi encontrada no sistema";
            }else if($cidade->getAno() != $ano){
                $idCidadeValido = false;
                $mensagem = "Essa cidade não pertence a esse ano";
            }
        }

        // agora checamos se o professor recebido existe
        if($idProfessorValido && $idProfessor != -1){
            require_once("../../entidades/Administrador.php");

            $admin = new Administrador("");
            $admin->setIdAdmin($idProfessor);
            $encontrado = $admin->recebeAdminId($host, "homeopatias", $usuario,
                                                $senhaBD, "professor");
            if(!$encontrado){
                $idProfessorValido = false;
                $mensagem = "Esse professor não foi encontrado no sistema";
            }
        }
        // agora checamos se o professor adicional 1 recebido existe
        if($idProfessorPrimarioValido && $idProfessorAdicional != -1){
            require_once("../../entidades/Administrador.php");

            $admin = new Administrador("");
            $admin->setIdAdmin($idProfessorAdicional);
            $encontrado = $admin->recebeAdminId($host, "homeopatias", $usuario,
                                                $senhaBD, "professor");
            if(!$encontrado){
                $idProfessorValido = false;
                $mensagem = "Esse professor não foi encontrado no sistema";
            }
        }
        // agora checamos se o professor recebido existe
        if($idProfessorSecundarioValido && $idProfessorSecundario != -1){
            require_once("../../entidades/Administrador.php");

            $admin = new Administrador("");
            $admin->setIdAdmin($idProfessorSecundario);
            $encontrado = $admin->recebeAdminId($host, "homeopatias", $usuario,
                                                $senhaBD, "professor");
            if(!$encontrado){
                $idProfessorValido = false;
                $mensagem = "Esse professor não foi encontrado no sistema";
            }
        }

        // se todos os dados estão válidos, a aula é editada
        if($idValido && $dataValida && $horarioValido && $idCidadeValido && $etapaValida &&
           $idProfessorValido && $descricaoValida && $idProfessorPrimarioValido &&
           $idProfessorSecundarioValido){

            require_once("../../entidades/Aula.php");

            $atualizar = new Aula();
            $atualizar->setIdAula($id);
            $atualizar->setCidadeId($idCidade);
            $atualizar->setEtapa($etapa);
            $atualizar->setData(strtotime($data . " " . $horario . ":00"));
            $atualizar->setProfessorId($idProfessor);
            $atualizar->setProfessorAdicionalPrimarioId($idProfessorAdicional);
            $atualizar->setProfessorAdicionalSecundarioId($idProfessorSecundario);
            $atualizar->setDescricao($descricao);
            //var_dump($atualizar->getProfessorAdicionalSecundario());die();

            $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

            if($sucesso){
                $mensagem = "";
            }else{
                $mensagem = "Erro na edição de aula";
            }
        }else if(!$dataValida){
            $mensagem = "Data inválida!";
        }else if(!$horarioValido){
            $mensagem = "Horário inválido!";
        }else if(!$etapaValida){
            $mensagem = "Etapa inválida!";
        }else if(!$descricaoValida){
            $mensagem = "Descrição inválida!";
        }else if(!$idValido){
            $mensagem = "Dados inconsistentes";
        }
    }else if($idProfessorValido && $idCidadeValido){
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_aulas.php'.$mensagem, true, "302");
die();