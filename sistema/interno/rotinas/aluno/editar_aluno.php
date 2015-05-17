<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
   unserialize($_SESSION["usuario"])->getPermissoes() & 1 ){

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
        $insc               = $_POST["insc"];
        $id                 = $_POST["id"];
        $nome               = $_POST["nome"];
        $cpf                = $_POST["cpf"];
        $email              = $_POST["email"];
        $login              = $_POST["login"];
        $status             = $_POST["status"];
        $idIndicador        = $_POST["indicador"];
        $telefone           = $_POST["telefone"];
        $telefone2          = $_POST["telefone2"];
        $telefone3          = $_POST["telefone3"];
        $escolaridade       = $_POST["escolaridade"];
        $curso              = $_POST["curso"];
        $cep                = $_POST["cep"];
        $rua                = $_POST["rua"];
        $numero             = $_POST["numero"];
        $complemento        = $_POST["complemento"];
        $bairro             = $_POST["bairro"];
        $cidade             = $_POST["cidade"];
        $estado             = $_POST["estado"];
        $pais               = "BRL";
        $tipoCurso          = $_POST["tipo_curso"];
        $modalidadeCurso    = $_POST["modalidade-curso"];
        $tipoCadastro       = $_POST["tipo_cadastro"];
        $senha              = (!isset($_POST["senha"]) || $_POST["senha"] == "") 
                                        ? false : $_POST["senha"];
        $recebeEmail        = isset($_POST["deseja-email"]);


        $nomeValido   = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                        mb_strlen($nome, 'UTF-8') <= 100 &&
                        preg_match("/^.{3,50} .{1,50}$/", $nome);
                        

        $cpfValido = validaCpf($cpf,$id);

        $emailValido  = validaEmail($email, $id);

        $loginValido  = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                        mb_strlen($login, 'UTF-8') <= 100;
        $statusValido = isset($status) && ($status === "preinscrito" ||
                                           $status === "inscrito"    ||
                                           $status === "desistente"  ||
                                           $status === "formado"     ||
                                           $status === "inativo"     );
        $idIndicadorValido = (isset($idIndicador) && !is_nan($idIndicador))
                                || !isset($idIndicador) || $idIndicador === "";

        if($idIndicadorValido && isset($idIndicador) && $idIndicador !== ""){
            // conferimos se o $idIndicador representa um aluno no sistema
            $conexao = null;
            try{
                $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
            }catch (PDOException $e){
                echo $e->getMessage();
            }

            $textoQuery  = "SELECT A.numeroInscricao FROM Aluno A, Usuario U WHERE 
                            A.numeroInscricao = ?";

            $query = $conexao->prepare($textoQuery);
            $query->bindParam(1, $idIndicador, PDO::PARAM_INT);
            $query->setFetchMode(PDO::FETCH_ASSOC);
            $query->execute();

            $linha = "";
            if(!($linha = $query->fetch())){
                $idIndicadorValido = false;
                $mensagem = "Não foi encontrado nos registros um aluno indicador com esse
                             número de matrícula";
            }else if($insc == $linha["numeroInscricao"]){
                $idIndicadorValido = false;
                $mensagem = "Um aluno não pode indicar a si mesmo";
            }
        }

        $telefoneValido = isset($telefone);
        $telefonesOpcValidos = (isset($telefone2) || isset($telefone3) );

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
        $cursoValido = ((!isset($curso) || $curso === "")) ||
                       (isset($curso) && mb_strlen($curso) > 0 && mb_strlen($curso) <= 200);

        $inscValido = isset($insc) && preg_match("/^[0-9]*$/", $insc);
        $idValido = isset($id) && preg_match("/^[0-9]*$/", $id);

        $enderecoValido = false;

        // formata CEP
        $cep = str_replace(".","",$cep);
        $cep = str_replace("-","",$cep);
        

        $cepValido = (isset($cep) && mb_strlen($cep, 'UTF-8') == 8 ) ||
                     (!isset($cep) || mb_strlen($cep, 'UTF-8') == 0);
                    

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

        $tipoCursoValido = $tipoCurso === "extensao" || $tipoCurso === "pos" ||
                           $tipoCurso === "instituto" ;

        $tipoCadastroValido = $tipoCadastro == "instituto" || 
                              $tipoCadastro == "faculdade inspirar";
        $senhaValida = !$senha || (mb_strlen($senha, 'UTF-8') >= 6 && mb_strlen($senha, 'UTF-8') <= 72);

        $modalidadeCursoValido = $modalidadeCurso === "regular" || 
                                        $modalidadeCurso === "intensivo";

        $sucesso = false;
        // se todos os dados estão válidos, o aluno é editado
        if($nomeValido && $cpfValido[0] && $emailValido[0] && $loginValido && $telefoneValido &&
           $statusValido && $idIndicadorValido && $escolaridadeValida &&
           $cursoValido && $inscValido && $idValido && $enderecoValido && $tipoCadastroValido && 
           $tipoCursoValido && $senhaValida && $modalidadeCursoValido){

            require_once("../../entidades/Aluno.php");

            $atualizar = new Aluno($login);
            $atualizar->setId($id);
            $atualizar->setNumeroInscricao($insc);
            $atualizar->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);
            $atualizar->setNome($nome);
            $atualizar->setCpf($cpf);
            $atualizar->setEmail($email);
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
            $atualizar->setTipoCurso($tipoCurso);
            $atualizar->setModalidadeCurso($modalidadeCurso);
            $atualizar->setTipoCadastro($tipoCadastro);
            $atualizar->setRecebeEmail($recebeEmail);
            
            if($escolaridade === "superior incompleto" || $escolaridade === "superior completo"   ||
               $escolaridade === "mestrado"            || $escolaridade === "doutorado" ){
                $atualizar->setCurso(isset($curso) ? $curso : null);
            }else{
                $atualizar->setCurso(null);
            }

            $atualizar->setStatus($status);

            $atualizar->setTelefone($telefone);
            if(isset($telefone2))
                $atualizar->setTelefone2($telefone2);
            if(isset($telefone3))
                $atualizar->setTelefone3($telefone3);

            $atualizar->setidIndicador(isset($idIndicador) ? $idIndicador : null);

            $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

            if($sucesso){
                $mensagem = "Aluno editado com sucesso";
                if($senha) {
                    $sucesso = $atualizar->mudaSenha($senha);
                    if(!$sucesso){
                        $mensagem = "Erro ao alterar a senha";
                    }
                }
            }else{
                $mensagem = "Já existe alguém com esse nome de usuário no sistema";
            }
        }else if(!$nomeValido){
            $mensagem = "Nome inválido!";
        }else if(!$cpfValido[0]){
            $mensagem = $cpfValido[1];
        }else if(!$emailValido[0]){
            $mensagem = $emailValido[1];
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
        }else if(!$enderecoValido){
            $mensagem = "Endereço inválido!";
        }else if(!$cursoValido){
            if((!isset($curso) || $curso === "") && $superior){
                $mensagem = "Insira o curso superior!";
            }else{
                $mensagem = "Curso inválido!";
            }
        }else if(!$senhaValida){
            $mensagem = "Nova senha inválida!";
        }else if(!$modalidadeCursoValido){
            $mensagem = "Modalidade de curso inválido!";
        }else if(!$telefonesOpcValidos) {
            $mensagem = "Telefones opcionais inválidos!";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "mensagem=".$mensagem;
}

function ValidaCpf($cpf, $id){

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

    // [0] = 0= houve erro ou 1 = não houve erro
    // [1] = mensagem do erro
    $return = array(1,"");

    // checamos se os dígitos verificadores do cpf conferem
    $cpfChecar = str_replace(".","",$cpf);
    $cpfChecar = str_replace("-","",$cpfChecar);
    $cpfNumerico = $cpfChecar;
    $cpfChecar = str_split($cpfChecar);
    $somaChecagem = 0;
    for($i = 10; $i >= 2; $i = $i - 1){
        $somaChecagem += (int)($cpfChecar[10 - $i]) * $i;
    }
    $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
    if($digito != $cpfChecar[9]){
        $return[0] = 0;
        $return[1] = "CPF inválido";
    }else{
        // agora checamos o segundo dígito
        $somaChecagem = 0;
        for($i = 11; $i >= 2; $i = $i - 1){
            $somaChecagem += (int)($cpfChecar[11 - $i]) * $i;
        }
        $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
        if($digito != $cpfChecar[10]){
            $return[0] = 0;
            $return[1] = "CPF inválido";
        }
    }

    //Checa se o CPF é diferente de 00000000000 e 99999999999
    $todosZero = true;
    $todosNove = true;
    for($i = 0; $i <11; $i++){
        if($cpfChecar[$i] != '0'){
            $todosZero = false;
        }
        if($cpfChecar[$i] != '9'){
            $todosNove = false;
        }
    }

    if($todosZero || $todosNove){
        $return[0] = 0;
        $return[1] = "CPF inválido";
    }

    //Checa se ja existe este cpf no sistema cadastrado como aluno
    $textoQuery = "SELECT U.cpf, U.id
                   FROM Usuario U , Aluno A
                   WHERE U.id = A.idUsuario AND U.cpf = ?";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, $cpfNumerico, PDO::PARAM_STR);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();
    
    if($linha = $query->fetch()){
        if($linha["id"] != $id){
            $return[0] = 0;
            $return[1] = "CPF ja registrado no sistema";
        }
    }
  
   if( !(isset($cpf) && (preg_match("/^\d{3}\.?\d{3}\.?\d{3}\-?\d{2}$/", $cpf) || 
       preg_match("/^\d{11}$/", $cpf)) ) ){
        $return[0] = 0;
        $return[1] = "CPF em formato inválido";
   }

    return $return;
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

    // [0] = 0 = houve erro ou 1 = não houve erro
    // [1] = mensagem do erro
    $return = array(1,"");

    //Checa se ja existe este email no sistema cadastrado como Aluno
    $textoQuery = "SELECT U.email, U.id
                   FROM Usuario U , Aluno A
                   WHERE U.id = A.idUsuario AND U.email = ?";
    
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

header('Location: ../../gerenciar_alunos.php?'.$mensagem.'&sucesso='.$sucesso . "&" . $_POST['filtros'], true, "302");
die();
