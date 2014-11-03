<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
   unserialize($_SESSION["usuario"]).gerPermissoes() & 1 ){

    // se o usuário chegou até aqui através de um formulário, altera os dados do aluno
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
        $insc           = $_POST["insc"];
        $id             = $_POST["id"];
        $nome           = $_POST["nome"];
        $cpf            = $_POST["cpf"];
        $email          = $_POST["email"];
        $login          = $_POST["login"];
        $status         = $_POST["status"];
        $loginIndicador = $_POST["indicador"];
        $telefone       = $_POST["telefone"];
        $escolaridade   = $_POST["escolaridade"];
        $curso          = $_POST["curso"];
        $cep            = $_POST["cep"];
        $rua            = $_POST["rua"];
        $numero         = $_POST["numero"];
        $complemento    = $_POST["complemento"];
        $bairro         = $_POST["bairro"];
        $cidade         = $_POST["cidade"];
        $estado         = $_POST["estado"];
        $pais           = $_POST["pais"];

        $nomeValido   = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                        mb_strlen($nome, 'UTF-8') <= 100;
        $cpfValido    = isset($cpf) &&
                        (preg_match("/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/", $cpf) || 
                         preg_match("/^\d{11}$/", $cpf));

        $cepValido    = isset($cep) &&
                        (preg_match("/^[0-9]{2}.?[0-9]{3}-?[0-9]{3}$/", $cep));

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
        $statusValido = isset($status) && ($status === "preinscrito" ||
                                           $status === "inscrito"    ||
                                           $status === "desistente"  ||
                                           $status === "formado"     );
        $loginIndicadorValido = (isset($loginIndicador) && mb_strlen($loginIndicador, 'UTF-8') >= 3 
                                 && mb_strlen($loginIndicador, 'UTF-8') <= 100)
                                || !isset($loginIndicador) || $loginIndicador === "";

        if($loginIndicadorValido && isset($loginIndicador) && $loginIndicador !== ""){
            // conferimos se o $loginIndicador representa um aluno no sistema
            $conexao = null;
            try{
                $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
            }catch (PDOException $e){
                echo $e->getMessage();
            }

            $textoQuery  = "SELECT A.numeroInscricao FROM Aluno A, Usuario U WHERE 
                            U.login = ? AND A.idUsuario = U.id";

            $query = $conexao->prepare($textoQuery);
            $query->bindParam(1, $loginIndicador, PDO::PARAM_INT);
            $query->setFetchMode(PDO::FETCH_ASSOC);
            $query->execute();

            $linha = "";
            if(!($linha = $query->fetch())){
                $loginIndicadorValido = false;
                $mensagem = "Não foi encontrado nos registros um aluno indicador com esse
                             nome de usuário";
            }else if($insc == $linha["numeroInscricao"]){
                $loginIndicadorValido = false;
                $mensagem = "Um aluno não pode indicar a si mesmo";
            }else{
                $idIndicador = $linha["numeroInscricao"];
            }
        }

        $telefoneValido = isset($telefone) &&
                          preg_match("/^\(\d{2}\)\d{4}-?\d{4,7}$/", $telefone);

        $escolaridadeValida = isset($escolaridade) &&
                   ($escolaridade === "fundamental incompleto" ||
                    $escolaridade === "fundamental completo"   ||
                    $escolaridade === "médio incompleto"       ||
                    $escolaridade === "médio completo"         ||
                    $escolaridade === "superior incompleto"    ||
                    $escolaridade === "superior completo"      ||
                    $escolaridade === "mestrado"               ||
                    $escolaridade === "doutorado");

        // para permitir a validação do curso, conferimos se possui curso superior
        $superior = ($escolaridade === "superior incompleto"    ||
                     $escolaridade === "superior completo"      ||
                     $escolaridade === "mestrado"               ||
                     $escolaridade === "doutorado");
        $cursoValido = ((!isset($curso) || $curso === "") && !$superior) ||
                       (isset($curso) && mb_strlen($curso) > 0 && mb_strlen($curso) <= 200);

        $inscValido = isset($insc) && preg_match("/^[0-9]*$/", $insc);
        $idValido = isset($id) && preg_match("/^[0-9]*$/", $id);

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

        $sucesso = false;
        // se todos os dados estão válidos, o aluno é editado
        if($nomeValido && $cpfValido && $emailValido && $loginValido && $telefoneValido &&
           $statusValido && $loginIndicadorValido && $escolaridadeValida &&
           $cursoValido && $inscValido && $idValido && $enderecoValido){

            require_once("../../entidades/Aluno.php");

            $atualizar = new Aluno($login);
            $atualizar->setNome($nome);
            $atualizar->setCpf($cpf);
            $atualizar->setEmail($email);
            $atualizar->setId($id);
            $atualizar->setNumeroInscricao($insc);
            $atualizar->setEscolaridade($escolaridade);
            $atualizar->setCep($cep);
            $atualizar->setRua($rua);
            $atualizar->setNumero($numero);
            $atualizar->setComplemento($complemento);
            $atualizar->setBairro($bairro);
            $atualizar->setCidade($cidade);
            $atualizar->setEstado($estado);
            $atualizar->setPais("BRL");
            
            if($escolaridade === "superior incompleto" || $escolaridade === "superior completo"   ||
               $escolaridade === "mestrado"            || $escolaridade === "doutorado" ){
                $atualizar->setCurso(isset($curso) ? $curso : null);
            }else{
                $atualizar->setCurso(null);
            }

            $atualizar->setStatus($status);

            $atualizar->setTelefone($telefone);

            $atualizar->setidIndicador(isset($idIndicador) ? $idIndicador : null);

            $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

            if($sucesso){
                $mensagem = "Aluno editado com sucesso";
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
        }else if(!$statusValido){
            $mensagem = "Situação do aluno inválida";
        }else if(!$inscValido || !$idValido){
            $mensagem = "Dados inconsistentes";
        }else if(!$telefoneValido){
            $mensagem = "Telefone inválido!";
        }else if(!$enderecoValido){
            $mensagem = "Endereço inválido!";
        }else if(!$telefoneValido){
            $mensagem = "Telefone inválido!";
        }else if(!$enderecoValido){
            $mensagem = "Endereço inválido!";
        }else if(!$cursoValido){
            if((!isset($curso) || $curso === "") && $superior){
                $mensagem = "Insira o curso superior!";
            }else{
                $mensagem = "Curso inválido!";
            }
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== "" && !$sucesso){
    $mensagem = "mensagem=".$mensagem;
}

header('Location: ../../gerenciar_alunos.php?'.$mensagem.'&sucesso='.$sucesso, true, "302");
die();