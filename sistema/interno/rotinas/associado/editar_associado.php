<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados do associado
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id              = $_POST["id"];
        $idAssoc         = $_POST["idAssoc"];
        $nome            = $_POST["nome"];
        $cpf             = $_POST["cpf"];
        $email           = $_POST["email"];
        $login           = $_POST["login"];
        $instituicao     = $_POST["instituicao"];
        $telefone        = $_POST["telefone"];
        $cep             = $_POST["cep"];
        $rua             = $_POST["rua"];
        $numero          = $_POST["numero"];
        $complemento     = $_POST["complemento"];
        $bairro          = $_POST["bairro"];
        $cidade          = $_POST["cidade"];
        $estado          = $_POST["estado"];
        $pais            = $_POST["id"];
        $numObjeto       = $_POST["nobjeto"];
        $dataEnvio       = $_POST["data-envio"];
        $formTerapeutica = $_POST["form-terapeutica"];
        $documentos      = $_POST["documentos"] === "on";

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
        $instituicaoValida = isset($instituicao) && ($instituicao == 1 || $instituicao == 2);

        $idAssocValido   = isset($idAssoc) && preg_match("/^[0-9]*$/", $idAssoc);
        $idValido        = isset($id) && preg_match("/^[0-9]*$/", $id);

        $telefoneValido  = isset($telefone) &&
                          preg_match("/^\(?\d{2}\)?\d{4}-?\d{4,7}$/", $telefone);

        $enderecoValido = false;

        // formata CEP
        $cep = str_replace(".","",$cep);
        $cep = str_replace("-","",$cep);
        

        $cepValido = (isset($cep) && mb_strlen($cep, 'UTF-8') == 8 );
                    

        $ruaValida = (isset($rua) && mb_strlen($rua, 'UTF-8') >= 3 &&
                          mb_strlen($rua, 'UTF-8') <= 200);

        $numeroValido = (isset($numero) && mb_strlen($numero, 'UTF-8') >= 0 &&
                          mb_strlen($numero, 'UTF-8') <= 200);

        $bairroValido = (isset($bairro) && mb_strlen($bairro, 'UTF-8') >= 3 &&
                          mb_strlen($bairro, 'UTF-8') <= 200);

        $cidadeValida = (isset($cidade) && mb_strlen($cidade, 'UTF-8') >= 3 &&
                          mb_strlen($cidade, 'UTF-8') <= 200);

        $estadoValido = (isset($estado) && mb_strlen($estado, 'UTF-8') ==2);

        $enderecoValido = ($cepValido && $ruaValida && $numeroValido &&
                            $bairroValido && $cidadeValida
                           && $estadoValido);
        $numObjetoValido = (!isset($numObjeto) || mb_strlen($numObjeto, 'UTF-8') === 0)
                        || (mb_strlen($numObjeto, 'UTF-8') >= 3 &&
                            mb_strlen($numObjeto, 'UTF-8') <= 100);
        $dataEnvioValida = (!isset($dataEnvio) ||  mb_strlen($dataEnvio, 'UTF-8') === 0) ||
                                  preg_match("/^\d{4}-\d{2}-\d{2}$/", $dataEnvio);

        $formTerapeuticaValida = isset($formTerapeutica) &&
                             mb_strlen($formTerapeutica, "UTF-8") >= 3 &&
                             mb_strlen($formTerapeutica, "UTF-8") <= 200;

        // se todos os dados estão válidos, o associado é editado
        if($nomeValido && $cpfValido && $emailValido && $loginValido &&
           $instituicaoValida && $idValido && $idAssocValido && $telefoneValido
           && $enderecoValido  && $numObjetoValido
           && $dataEnvioValida && $formTerapeuticaValida){

            // lemos as credenciais do banco de dados
            $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
            $dados = json_decode($dados, true);
            foreach($dados as $chave => $valor) {
                $dados[$chave] = str_rot13($valor);
            }
            $host    = $dados["host"];
            $usuario = $dados["nome_usuario"];
            $senhaBD = $dados["senha"];

            require_once("../../entidades/Associado.php");

            $atualizar = new Associado($login);
            $atualizar->setNome($nome);
            $atualizar->setCpf($cpf);
            $atualizar->setEmail($email);
            $atualizar->setId($id);
            $atualizar->setIdAssoc($idAssoc);
            if($instituicao == 1)
                $atualizar->setInstituicao("atenemg");
            else if($instituicao == 2)
                $atualizar->setInstituicao("conahom");
            $atualizar->setFormacaoTerapeutica($formTerapeutica);
            $atualizar->setTelefone($telefone);
            $atualizar->setCep($cep);
            $atualizar->setRua($rua);
            $atualizar->setNumero($numero);
            $atualizar->setComplemento($complemento);
            $atualizar->setBairro($bairro);
            $atualizar->setCidade($cidade);
            $atualizar->setEstado($estado);
            $atualizar->setPais("BRL");
            $atualizar->setNumObjeto($numObjeto);
            $atualizar->setDataEnvioCarteirinha($dataEnvio);
            $atualizar->setEnviouDocumentos($documentos);
            $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

            if($sucesso){
                $mensagem = "?sucesso=true&msg=Associado editado com sucesso";
            }else{
                $mensagem = "Já existe alguém com esse nome de usuário no sistema";
            }
        } else if (!$nomeValido){
            $mensagem = "Nome inválido!";
        } else if (!$cpfValido){
            $mensagem = "CPF inválido!";
        } else if (!$emailValido){
            $mensagem = "E-mail inválido!";
        } else if (!$loginValido){
            $mensagem = "Nome de usuário inválido!";
        } else if (!$instituicaoValida){
            $mensagem = "Instituição inválida";
        } else if (!$telefoneValido){
            $mensagem = "Telefone inválido!";
        } else if (!$enderecoValido){
            $mensagem = "Endereço inválido!";
        } else if (!$cidadeValida) {
            $mensagem = "Cidade inválida!";
        } else if (!$estadoValido) {
            $mensagem = "Estado inválido";
        } else if (!$numObjetoValido) {
            $mensagem = "Número do objeto inválido";
        } else if(!$dataEnvioValida) {
            $mensagem = "Data de envio da carteirinha inválida";
        } else if (!$formTerapeuticaValida) {
            $mensagem = "Formação terapeutica inválida";
        } else if (!$idValido || !$idAssocValido){
            $mensagem = "Dados inconsistentes";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== "" && !$sucesso){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_associados.php'.$mensagem, true, "302");
die();