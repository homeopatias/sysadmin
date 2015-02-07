<?php

// Função que recebe os dados do formulário e
// tenta autenticar a sessão do usuário no sistema,
// validando os dados no processo
//
// Retorna: Uma mensagem de erro, se houver

function processaLogin($login, $senha){

    // checa se os dados sao válidos:
    // primeiro checa a existência e validade do login (através do tamanho)
    // depois faz o mesmo para a senha

    $loginValido     = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                       mb_strlen($login, 'UTF-8') <= 100;
    $senhaValida     = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 &&
                       mb_strlen($senha, 'UTF-8') <= 72;

    if($loginValido && $senhaValida){
        // se os dados sao validos, tenta fazer o login para cada caso possível

        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);
        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }
        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        // login de aluno
        require_once("entidades/Aluno.php");

        $aluno = new Aluno($login);
        $sucesso = $aluno->autenticaSessao($host, "homeopatias",
                                           $usuario, $senhaBD, $senha);
        
        // caso o login tenha sido bem sucedido, checamos se esse aluno está ativo
        if($sucesso){
            if(!$aluno->getAtivo()) {
                // caso não esteja ativo, avisamos ao aluno que deve enviar sua documentação
                session_destroy();
                header('Location: index.php?mensagem='.
                      'Seu acesso somente será liberado após a aprovação de sua documentação. Verifique seu e-mail.', true, "302");

                $assunto = "Homeopatias.com - Enviar Documentos";
                $msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la.</b><br><br>";
                $msg .= "<h3>Envie uma <strong>cópia autenticada</strong> do seu histórico e diploma do ensino superior para:</h3>";
                $msg .= "<br><br>R. Prof. Rubens Romanelli, 77 - Bandeirantes (Pampulha)
								Belo Horizonte - MG
								31365-350";
                $msg .= "<br><br><b>Quando sua documentação for aprovada, você será notificado por e-mail.</b><br><br>";
                $msg .= "<br><br>Obrigado,<br>Equipe Homeobrás.";
                $headers = "Content-type: text/html; charset=utf-8 " .
                    "From: Sistema Administrativo Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
                    "Reply-To: noreply@homeopatias.com" . "\r\n" .
                    "X-Mailer: PHP/" . phpversion();

                mail($aluno->getEmail(), $assunto, $msg, $headers);

                die();
            }

						// FIXME: Bloqueio criado até resolvermos a questão das cidades
						session_destroy();
						header('Location: index.php?mensagem='.
									'Ainda estamos preparando a área administrativa para você. Em breve lhe avisaremos por e-mail. Desculpe-nos pelo inconveniente.', true, "302");
						die();
            return;
        }

        // login de associado
        require_once("entidades/Associado.php");

        $assoc = new Associado($login);
        $sucesso = $assoc->autenticaSessao($host, "homeopatias",
                                           $usuario, $senhaBD, $senha);

        // caso o login tenha sido bem sucedido, a função terminou seu trabalho
        if($sucesso){
            return;
        }

        // login de administrador/coordenador/professor
        require_once("entidades/Administrador.php");

        $admin = new Administrador($login);
        $sucesso = $admin->autenticaSessao($host, "homeopatias",
                                           $usuario, $senhaBD, $senha);
        
        // caso ainda não tenhamos obtido sucesso no login após tentar as
        // três possibilidades existentes, os dados estão incorretos
        if(!$sucesso){
            return "Nome de usuário ou senha incorretos";
        }
    }else{
        // algum valor invalido foi enviado
        if(!$loginValido)
            return "Nome de usuário inválido";
        else if(!$senhaValida)
            return "Senha inválida";
    }
}
