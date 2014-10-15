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
        $cpf         = $_POST["cpf"];
        $email       = $_POST["email"];
        $login       = $_POST["login"];
        $permissoes  = $_POST["permissoes"];

        $nomeValido   = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                        mb_strlen($nome, 'UTF-8') <= 100;
        $cpfValido    = isset($cpf) &&
                        (preg_match("/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/", $cpf) || 
                         preg_match("/^\d{11}$/", $cpf));

        if($cpfValido){
            // checamos se os dígitos verificadores do cpf conferem
            $cpfChecar = str_replace(".","",$cpf);
            $cpfChecar = str_replace("-","",$cpfChecar);
            $cpfChecar = str_split($cpfChecar);
            $somaChecagem = 0;
            for($i = 10; $i >= 2; $i = $i - 1){
                $somaChecagem += (int)($cpfChecar[10 - $i]) * $i;
            }
            $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
            if($digito != $cpfChecar[9]){
                $cpfValido = false;
            }else{
                // agora checamos o segundo dígito
                $somaChecagem = 0;
                for($i = 11; $i >= 2; $i = $i - 1){
                    $somaChecagem += (int)($cpfChecar[11 - $i]) * $i;
                }
                $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
                if($digito != $cpfChecar[10]){
                    $cpfValido = false;
                }
            }
        }

        $emailValido  = isset($email) && mb_strlen($email, 'UTF-8') <= 100 &&
                        preg_match("/^.+\@.+\..+$/", $email);
        $loginValido  = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                        mb_strlen($login, 'UTF-8') <= 100;

        $idAdminValido = isset($idAdmin) && preg_match("/^[0-9]*$/", $idAdmin);
        $idValido = isset($id) && preg_match("/^[0-9]*$/", $id);

        // se todos os dados estão válidos, o administrador é editado
        if($nomeValido && $cpfValido && $emailValido && $loginValido &&
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
        }else if(!$cpfValido){
            $mensagem = "CPF inválido!";
        }else if(!$emailValido){
            $mensagem = "E-mail inválido!";
        }else if(!$loginValido){
            $mensagem = "Nome de usuário inválido!";
        }else if(!$idAdminValido || !$idValido){
            $mensagem = "Dados inconsistentes";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_administradores.php'.$mensagem, true, "302");
die();