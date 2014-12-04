<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require("../phpass-0.3/PasswordHash.php");
require("../entidades/Administrador.php");
require("../entidades/Aluno.php");
require("../entidades/Associado.php");

// Recebe a senha  do usuário
// e os novos dados
// Caso a senha esteja correta, altera
// os dados do usuário armazenado na sessão

$senha = $_POST["senha"];
$nome  = $_POST["nome"];
$cpf   = $_POST["cpf"];
$email = $_POST["email"];
$login = $_POST["login"];

// checa se os dados sao validos

$senhaValida = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 && mb_strlen($senha, 'UTF-8') <= 72;
$nomeValido  = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 && mb_strlen($nome, 'UTF-8') <= 100;
$loginValido = isset($login) && mb_strlen($login, 'UTF-8') >= 3 && mb_strlen($login, 'UTF-8') <= 100;
$emailValido = isset($email) && mb_strlen($email, 'UTF-8') <= 100 && preg_match("/^.+\@.+\..+$/", $email);

$cpfValido = isset($cpf);
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

//Checa se o CPF é diferente de 00000000000 e 99999999999
$todosZero = true;
$todosNove = true;
for($i = 0; $i < 11; $i++){
    if($cpfChecar[$i] != '0'){
        $todosZero = false;
    }
    if($cpfChecar[$i] != '9'){
        $todosNove = false;
    }
}

if($todosZero ||
  ($todosNove && unserialize($_SESSION["usuario"])->getNivelAdmin() !== "administrador")){
    $cpfValido = false;
}

// mensagem a ser exibida em caso de erro
$mensagem = "";
$sucesso = false;

// lemos as credenciais do banco de dados
$dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
$dados = json_decode($dados, true);
foreach($dados as $chave => $valor) {
    $dados[$chave] = str_rot13($valor);
}
$host    = $dados["host"];
$usuario = $dados["nome_usuario"];
$senhaBD = $dados["senha"];

// cria a conexão com o banco
$conexao = null;
try{
    $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario,
                       $senhaBD);
}catch (PDOException $e){
    echo $e->getMessage();
}

if($senhaValida){
    $textoQuery  = "SELECT senha FROM Usuario WHERE login=?";

    $query = $conexao->prepare($textoQuery);
    $query->bindParam(1, unserialize($_SESSION["usuario"])->getLogin(), PDO::PARAM_STR);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $query->execute();

    if ($linha = $query->fetch()){
        $hasher = new PasswordHash(8, false);
        $senhaCorreta = $hasher->CheckPassword($senha, $linha["senha"]);
        if(!$senhaCorreta){
            $senhaValida = false;
        }
    }else{
        $senhaValida = false;
    }
}

if($senhaValida && $nomeValido && $loginValido && $emailValido && $cpfValido){

    $usuarioLogado = unserialize($_SESSION["usuario"]);
    $sucesso = false;

    // se os dados sao validos, muda os dados
    if($usuarioLogado instanceof Administrador) {
        // caso seja um administrador, ele só pode editar os dados relativos
        // à entidade usuários, portanto fazemos as alterações
        $usuarioLogado->setNome($nome);
        $usuarioLogado->setLogin($login);
        $usuarioLogado->setEmail($email);
        $usuarioLogado->setCpf($cpf);
        $sucesso = $usuarioLogado->atualizar($host, "homeopatias", $usuario, $senhaBD);
    } else if($usuarioLogado instanceof Associado) {
        // caso seja um associado, validamos os estados restantes
        $telefone        = $_POST["telefone"];
        $cep             = $_POST["cep"];
        $rua             = $_POST["rua"];
        $numero          = $_POST["numero"];
        $complemento     = $_POST["complemento"];
        $bairro          = $_POST["bairro"];
        $cidade          = $_POST["cidade"];
        $estado          = $_POST["estado"];
        $pais            = $_POST["id"];

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
                           $bairroValido && $cidadeValida && $estadoValido);

        $usuarioLogado->setNome($nome);
        $usuarioLogado->setCpf($cpf);
        $usuarioLogado->setEmail($email);
        $usuarioLogado->setLogin($login);
        $usuarioLogado->setTelefone($telefone);
        $usuarioLogado->setCep($cep);
        $usuarioLogado->setRua($rua);
        $usuarioLogado->setNumero($numero);
        $usuarioLogado->setComplemento($complemento);
        $usuarioLogado->setBairro($bairro);
        $usuarioLogado->setCidade($cidade);
        $usuarioLogado->setEstado($estado);
        $sucesso = $usuarioLogado->atualizar($host, "homeopatias", $usuario, $senhaBD);

        if(!$sucesso) {
            if (!$telefoneValido){
                $mensagem = "Telefone inválido!";
            } else if (!$enderecoValido){
                $mensagem = "Endereço inválido!";
            } else if (!$cidadeValida) {
                $mensagem = "Cidade inválida!";
            } else if (!$estadoValido) {
                $mensagem = "Estado inválido";
        }
    } else if($usuarioLogado instanceof Aluno) {
        // caso seja um aluno, validamos os estados restantes
        $telefone        = $_POST["telefone"];
        $cep             = $_POST["cep"];
        $rua             = $_POST["rua"];
        $numero          = $_POST["numero"];
        $complemento     = $_POST["complemento"];
        $bairro          = $_POST["bairro"];
        $cidade          = $_POST["cidade"];
        $estado          = $_POST["estado"];
        $pais            = $_POST["id"];

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
                           $bairroValido && $cidadeValida && $estadoValido);

        $usuarioLogado->setNome($nome);
        $usuarioLogado->setCpf($cpf);
        $usuarioLogado->setEmail($email);
        $usuarioLogado->setLogin($login);
        $usuarioLogado->setTelefone($telefone);
        $usuarioLogado->setCep($cep);
        $usuarioLogado->setRua($rua);
        $usuarioLogado->setNumero($numero);
        $usuarioLogado->setComplemento($complemento);
        $usuarioLogado->setBairro($bairro);
        $usuarioLogado->setCidade($cidade);
        $usuarioLogado->setEstado($estado);
        $sucesso = $usuarioLogado->atualizar($host, "homeopatias", $usuario, $senhaBD);

        if(!$sucesso) {
            if (!$telefoneValido){
                $mensagem = "Telefone inválido!";
            } else if (!$enderecoValido){
                $mensagem = "Endereço inválido!";
            } else if (!$cidadeValida) {
                $mensagem = "Cidade inválida!";
            } else if (!$estadoValido) {
                $mensagem = "Estado inválido";
        }
    }

    if($sucesso){
        $_SESSION["usuario"] = serialize($usuarioLogado);
    }
}else{
    // algum valor invalido foi enviado
    if(!$senhaValida)
        $mensagem = "Senha incorreta";
    else if(!$emailValido)
        $mensagem = "E-mail inválido";
    else if(!$nomeValido)
        $mensagem = "Nome inválido";
    else if(!$loginValido)
        $mensagem = "Login inválido";
    else if(!$cpfValido)
        $mensagem = "CPF inválido";
}

// fecha a conexão com o bd
$conexao = null;

header('Location: ../index.php?mensagem='.$mensagem.'&sucessoEdicao='.$sucesso, true, "302");
die();