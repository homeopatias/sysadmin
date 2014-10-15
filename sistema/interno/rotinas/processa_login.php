<?php

// Função que recebe os dados do formulário e
// tenta autenticar a sessão do usuário no sistema,
// validando os dados no processo
//
// Retorna: Uma mensagem de erro, se houver

function processaLogin(){
    $login     = $_POST["login"];
    $senha     = $_POST["senha"];
    $tipoLogin = $_POST["tipoLogin"];

    // checa se os dados sao validos:
    // primeiro checa a existencia e validade do login (atraves do tamanho)
    // depois faz o mesmo para a senha
    // e por fim faz o mesmo para o tipoLogin, conferindo se e um valor dentro dos
    // especificados

    $loginValido     = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                       mb_strlen($login, 'UTF-8') <= 100;
    $senhaValida     = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 &&
                       mb_strlen($senha, 'UTF-8') <= 72;
    $tipoLoginValido = isset($tipoLogin) && ($tipoLogin == 1 || $tipoLogin == 2 || $tipoLogin == 3
                       || $tipoLogin == 4 || $tipoLogin == 5);

    if($loginValido && $senhaValida && $tipoLoginValido){
        // se os dados sao validos, descobre que tipo de login deve ser feito e
        // tenta faze-lo

        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);
        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }
        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        if($tipoLogin == 1){
            // login de aluno
            require_once("entidades/Aluno.php");

            $aluno = new Aluno($login);
            $sucesso = $aluno->autenticaSessao($host, "homeopatias",
                                               $usuario, $senhaBD, $senha);
            
            // caso nao tenha encontrado esse aluno, exibe uma mensagem de erro
            if(!$sucesso){
                return "Nome de usuário ou senha incorretos";
            }
        }else if($tipoLogin == 2){
            // login de associado
            require_once("entidades/Associado.php");

            $assoc = new Associado($login);
            $sucesso = $assoc->autenticaSessao($host, "homeopatias",
                                               $usuario, $senhaBD, $senha);

            // caso nao tenha encontrado esse associado, exibe uma mensagem de erro
            if(!$sucesso){
                return "Nome de usuário ou senha incorretos";
            }
        }else if($tipoLogin == 3){
            // login de administrador
            require_once("entidades/Administrador.php");

            $admin = new Administrador($login);
            $sucesso = $admin->autenticaSessao($host, "homeopatias",
                                               $usuario, $senhaBD, $senha, "administrador");
            
            // caso nao tenha encontrado esse administrador, exibe uma mensagem de erro
            if(!$sucesso){
                return "Nome de usuário ou senha incorretos";
            }
        }else if($tipoLogin == 4){
            // login de professor
            require_once("entidades/Administrador.php");

            $admin = new Administrador($login);
            $sucesso = $admin->autenticaSessao($host, "homeopatias",
                                               $usuario, $senhaBD, $senha, "professor");
            
            // caso nao tenha encontrado esse professor, exibe uma mensagem de erro
            if(!$sucesso){
                return "Nome de usuário ou senha incorretos";
            }
        }else if($tipoLogin == 5){
            // login de coordenador
            require_once("entidades/Administrador.php");

            $admin = new Administrador($login);
            $sucesso = $admin->autenticaSessao($host, "homeopatias",
                                               $usuario, $senhaBD, $senha, "coordenador");
            
            // caso nao tenha encontrado esse coordenador, exibe uma mensagem de erro
            if(!$sucesso){
                return "Nome de usuário ou senha incorretos";
            }
        }else{
            return "Dados inconsistentes";
        }
    }else{
        // algum valor invalido foi enviado
        if(!$loginValido)
            return "Nome de usuário inválido";
        else if(!$senhaValida)
            return "Senha inválida";
        else
            return "Tipo de login inválido";
    }
}