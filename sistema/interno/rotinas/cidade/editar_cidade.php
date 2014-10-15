<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados da cidade
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id          = $_POST["idCidade"];
        $nome        = $_POST["nome"];
        $UF          = $_POST["UF"];
        $ano         = $_POST["ano"];
        $local       = $_POST["local"];
        $idCoord     = $_POST["coord"];
        $inscricao   = $_POST["inscricao"];
        $parcela     = $_POST["parcela"];
        $limite      = $_POST["limite"];
        $nomeEmpresa = $_POST["nomeEmpresa"];
        $cnpjEmpresa = $_POST["cnpjEmpresa"];

        $idValido      = isset($id) && preg_match("/^[0-9]*$/", $id);
        $nomeValido    = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                         mb_strlen($nome, 'UTF-8') <= 100;
        $UfValido      = isset($UF) && mb_strlen($UF, 'UTF-8') === 2;
        $anoValido     = isset($ano) && intval($ano) > 2000 && intval($ano) < 3000;
        $localValido   = isset($local) && mb_strlen($local, 'UTF-8') >= 3 &&
                         mb_strlen($local, 'UTF-8') <= 200;
        $idCoordValido = isset($idCoord) && preg_match("/^[0-9]*$/", $idCoord);
        $inscricaoValida = isset($inscricao) && preg_match("/^[0-9]*\.?[0-9]+$/", $inscricao);
        $parcelaValida   = isset($parcela) && preg_match("/^[0-9]*\.?[0-9]+$/", $parcela);
        $limiteValido    = isset($limite) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $limite);
        $empresaValida   = isset($nomeEmpresa) && mb_strlen($nomeEmpresa, 'UTF-8') <= 100 &&
                           mb_strlen($nomeEmpresa, 'UTF-8') >= 3;
        $cnpjValido      = isset($cnpjEmpresa) &&
                           preg_match("/^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/",
                           $cnpjEmpresa);

        // se todos os dados estão válidos, a cidade é editada
        if($idValido && $nomeValido && $UfValido && $anoValido && $localValido && $idCoordValido &&
           $inscricaoValida && $parcelaValida && $limiteValido && $empresaValida && $cnpjValido){

            // lemos as credenciais do banco de dados
            $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
            $dados = json_decode($dados, true);
            foreach($dados as $chave => $valor) {
                $dados[$chave] = str_rot13($valor);
            }
            $host    = $dados["host"];
            $usuario = $dados["nome_usuario"];
            $senhaBD = $dados["senha"];

            require_once("../../entidades/Cidade.php");

            $atualizar = new Cidade();
            $atualizar->setIdCidade($id);
            $atualizar->setNome($nome);
            $atualizar->setUF($UF);
            $atualizar->setAno($ano);
            $atualizar->setLocal($local);
            $atualizar->setInscricao($inscricao);
            $atualizar->setParcela($parcela);
            $atualizar->setLimiteInscricao($limite);
            $atualizar->setNomeEmpresa($nomeEmpresa);
            $atualizar->setCnpjEmpresa($cnpjEmpresa);
            $coordExiste = $atualizar->setCoordenadorId($idCoord);

            if($coordExiste){
                $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

                if($sucesso){
                    $mensagem = "";
                }else{
                    $mensagem = "Erro na edição de cidade";
                }
            }else{
                // o coordenador informado não existe
                $mensagem = "Esse coordenador não existe no sistema";
            }
        }else if(!$nomeValido){
            $mensagem = "Nome inválido!";
        }else if(!$UfValido){
            $mensagem = "Estado inválido!";
        }else if(!$anoValido){
            $mensagem = "Ano inválido!";
        }else if(!$localValido){
            $mensagem = "Local inválido!";
        }else if(!$idCoordInvalido){
            $mensagem = "Id de coordenador inválido!";
        }else if(!$inscricaoValida){
            $mensagem = "Valor de inscrição inválido!";
        }else if(!$parcelaValida){
            $mensagem = "Valor de parcela inválido!";
        }else if(!$limiteValido){
            $mensagem = "Data limite de matrícula inválida!";
        }else if(!$idValido){
            $mensagem = "Dados inconsistentes";
        }else if(!$empresaValida) {
            $mensagem = "Nome da empresa inválida!";
        }else if(!$cnpjValido) {
            $mensagem = "CNPJ inválido!";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_cidades.php'.$mensagem, true, "302");
die();