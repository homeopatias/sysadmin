<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Cadastro de aluno - Homeopatias.com</title>
        <script>
            $(document).ready(function(){
                $("#li-termos").change(function(){
                    $("#cadastro").prop('disabled', 
                                        $('#li-termos').is(':checked') ? false : true);
                });
            });
        </script>
    </head>
    <body>
        <?php
            // mensagem a ser exibida acima do formulário de cadastro, caso seja necessário
            $mensagem = "";

            include('modulos/navegacao.php');

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
            try{
                $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
            }catch (PDOException $e){
                echo $e->getMessage();
            }

            // se o aluno chegou até aqui através de um formulário, registra-o no sistema
            if(isset($_POST["submit"])){

                // validamos todos os dados recebidos
                $nome           = $_POST["nome"];
                $email          = $_POST["email"];
                $login          = $_POST["login"];
                $senha          = $_POST["senha"];

                $nomeValido     = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                                  mb_strlen($nome, 'UTF-8') <= 100 &&
                                  preg_match("/^.{3,50} .{1,50}$/", $nome);

                $emailValido  = isset($email) && mb_strlen($email, 'UTF-8') <= 100 &&
                                preg_match("/^.+\@.+\..+$/", $email);

                $emailExistente = false;
                if($emailValido){
                    //Checa se ja existe este email no sistema cadastrado como aluno
                    $textoQuery = "SELECT U.email
                                   FROM Usuario U , Aluno A
                                   WHERE U.id = A.idUsuario AND U.email = ?";
    
                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1,$email, PDO::PARAM_STR);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if($linha = $query->fetch()){
                        $emailValido = false;
                        $emailExistente = true;
                    }
                }
                
                $loginValido  = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                                mb_strlen($login, 'UTF-8') <= 100;
                $senhaValida  = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 &&
                                mb_strlen($senha, 'UTF-8') <= 72;

                $sucesso = true;

                // se todos os dados estão válidos, o aluno é cadastrado
                if($nomeValido && $emailValido && $loginValido && $senhaValida){

                    // nesse caso, para efetivar o cadastro conforme necessitamos
                    // fazemos a query diretamente

                    require_once("phpass-0.3/PasswordHash.php");

                    $hasher = new PasswordHash(8, false);
                    $hashSenha = $hasher->HashPassword($senha);

                    // cria conexão com o banco
                    $conexao = null;
                    try{
                        $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
                    }catch (PDOException $e){
                        echo $e->getMessage();
                    }

                    $query = $conexao->prepare("SELECT login FROM Usuario WHERE login=?");
                    $query->bindParam(1, $login, PDO::PARAM_STR);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if ($linha = $query->fetch()){
                        // já existe alguém com esse nome de usuário no sistema
                        $conexao = null;
                        $sucesso = false;
                    }

                    // Usamos as TRANSACTIONs do MySql para garantir que caso haja
                    // algum erro, as tabelas continuem consistentes
                    $conexao->beginTransaction();

                    $dataInscricao = date("Y-m-d H:i:s");
                    $dadosUsuario  = array($dataInscricao, $email, $login, $hashSenha, $nome);
                    $queryUsuario  = "INSERT INTO Usuario (cpf, dataInscricao, email, login, senha, nome) 
                                      VALUES ('99999999999',?,?,?,?,?)";
                    $query         = $conexao->prepare($queryUsuario);
                    $sucessoUsuario = $query->execute($dadosUsuario);


                    // descobrimos o id do usuário que acabamos de inserir
                    $idUsuario = $conexao->lastInsertId();

                    $dadosAluno  = array($idUsuario);
                    $queryAluno  = "INSERT INTO Aluno (idUsuario, status, pais, tipo_curso, tipo_cadastro) VALUES
                                    (?, 'preinscrito', 'BRL', 'extensao', 'faculdade inspirar')";


                    $query = $conexao->prepare($queryAluno);
                    $sucessoAluno = $query->execute($dadosAluno);

                    if($sucessoUsuario && $sucessoAluno) {
                        // deu tudo certo, inserimos o aluno
                        $conexao->commit();
                    } else {
                        // algo deu errado, desfazemos as mudanças
                        $conexao->rollBack();
                    }

                    // Fecha a conexão
                    $conexao = null;
                    $sucesso = $sucessoUsuario && $sucessoAluno;

                    if(!$sucesso){
                        $mensagem = "Já existe um usuário com esse nome 
                                     de usuário no sistema";
                    } else {
                        // criamos o aluno no Moodle
                        $usuarioMoodle = $dados["usuario_moodle"];
                        $senhaMoodle   = $dados["senha_moodle"];

                        $sucessoMoodle = false;

                        $conMoodle = null;
                        try{
                            $conMoodle = new PDO("mysql:host=$host;dbname=moodle;charset=utf8",
                                                 $usuarioMoodle, $senhaMoodle);

                            $queryMoodle = "INSERT INTO mdl_user
                                            (firstname,lastname,email,username,password,
                                             confirmed,mnethostid) VALUES
                                           (?,?,?,?,MD5(?),1,1)";

                            $arrayNome = split(" ", $nome);
                            $dadosMoodle = array($arrayNome[0], array_pop($arrayNome), $email, $login, $senha);

                            $query = $conMoodle->prepare($queryMoodle);
                            $sucessoMoodle = $query->execute($dadosMoodle);

                        }catch (PDOException $e){
                            // echo $e->getMessage();
                        }

                        $mensagem = "";
                        if(!$sucessoMoodle){
                            $mensagem = "O registro foi efetuado, porém não foi possível registrar no Moodle";
                        }
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content=<?= '"index.php?sucessoAval=true&mensagem='.$mensagem.'"'?>>
        <script type="text/javascript">
            window.location = <?= '"index.php?sucessoAval=true&mensagem='.$mensagem.'"'?>;
        </script>
        <?php
                    }
                }else if(!$nomeValido){
                    $mensagem = "Nome inválido!";
                }else if(!$emailValido && !$emailExistente){
                    $mensagem = "E-mail inválido!";
                }else if($emailExistente){
                    $mensagem = "E-mail ja cadastrado!";
                }else if(!$loginValido){
                    $mensagem = "Nome de usuário inválido!";
                }else if(!$senhaValida){
                    $mensagem = "Senha inválida!";
                }
            }

        ?>

        <div class="col-xs-12 vertical-center" style="height:50%">
            <div class="center-block col-sm-8 no-float">
                <form method="POST" class="conteudo" id="form-cadastro" action>
                    <?php
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <br>
                    <div class="form-group">
                        <label for="nome-novo">Nome:</label>
                        <input type="text" name="nome" id="nome-novo" required
                               pattern="^.{3,50} .{1,50}$" title="O nome deve ter de 3 a 100 caracteres, insira seu nome completo"
                               placeholder="Nome" class="form-control" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="email-novo">E-mail:</label>
                        <input type="email" name="email" id="email-novo" required
                               placeholder="E-mail"
                               title="Insira um e-mail válido"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="login-novo">Nome de usuário:</label>
                        <input type="text" name="login" id="login-novo" required
                               pattern="^.{3,100}$" placeholder="Nome de usuário"
                               title="O login deve ter de 3 a 100 caracteres"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="senha-novo">Senha:</label>
                        <input type="password" name="senha" id="senha-novo" required
                               pattern="^.{6,72}$" placeholder="Senha"
                               title="A senha deve ter de 6 a 72 caracteres"
                               class="form-control">
                    </div>
                    <br>
                    <a class="pull-left btn-danger" style="padding: 2px 10%; width: 100%;"
                       href="#" data-toggle="modal" data-target="#modal-info" >
                        <span class="fa-stack fa-lg">
                            <i class="fa fa-circle-o fa-stack-2x"></i>
                            <i class="fa fa-info fa-stack-1x"></i>
                        </span><b> Informações do curso - LEIA ANTES DE SE CADASTRAR</b>
                    </a>
                    <br><br><br>
                    <div class="form-group">
                        <label>
                            Marcando a opção abaixo, você confirma que leu e compreendeu
                            todas as informações do curso expostas na tela de informações
                            referida acima. Confirma que concorda com todos os termos e
                            está ciente dos procedimentos informados referentes às aulas,
                            certificados, trancamento de inscrição, módulos do curso,
                            e todas as outras abrangidas.<br><br>
                            Para se cadastrar, você deve concordar com os
                            termos e clicar no botão "Confirmo" abaixo.
                        </label>
                        <br>
                        <input type="checkbox" id="li-termos">
                        <label for="li-termos">Confirmo</label>
                    </div>
                    <br>
                    <button type="submit" name="submit" value="submit" id="cadastro"
                            class="btn btn-primary pull-right" disabled>
                        Cadastrar
                    </button>
                    <br>
                </form>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para exibição de informação relevante -->
        <div class="modal fade" id="modal-info" tabindex="-1" role="dialog" 
             aria-labelledby="modal-info" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Informação do curso</h4>
                    </div>
                    <div class="modal-body"
                         style="font-size: 0.9em; white-space: pre-line; overflow: none">
                        INFORMAÇÕES GERAIS SOBRE O CURSO:
                        O curso possui 4 etapas divididas em 2 módulos, totalizando 800h:

                        O 1º MÓDULO é composto de:
                          - 1ª etapa = 10 meses – 1 (uma) Inscrição e 11 parcelas;
                        (100 horas presenciais e 100 horas de estudos orientados e trabalhos).
                          - 2ª etapa = 10 meses - 1 (uma) Inscrição e 11 parcelas;
                        (50 horas presenciais e 150 horas de estudos orientados e trabalhos).

                        O INSTITUTO TECNOLÓGICO HAHNEMANN expedirá um certificado de 400h ao final do 1º módulo.

                        O 2º MÓDULO é composto de:

                        - 3ª etapa = 10 meses – 1 (uma) inscrição e 11 parcelas;
                        (50 horas presenciais e 150 horas de estudos orientados, trabalhos e estágio).
                        - 4ª etapa = 10 meses – 1 (uma) inscrição e 11 parcelas;
                        (50 horas presenciais e 150 horas de estudos orientados, trabalhos e estágio).

                        O INSTITUTO TECNOLÓGICO HAHNEMANN expedirá um certificado de 400h ao final do 2º módulo.

                        PRAZO DE ENTREGA:
                        Os Certificados somente serão expedidos pelo INSTITUTO TECNOLÓGICO HAHNEMANN após a conclusão de cada módulo, com um prazo máximo de entrega até o término do 2º semestre do ano seguinte. 
                          
                        O INSTITUTO TECNOLÓGICO HAHNEMANN oferece o curso de Formação em Ciência da Homeopatia em um final de semana por mês, sábado ou domingo.

                        Não incluído o material didático.

                        CERTIFICADO:

                        Cada aluno terá direito a receber o certificado de FORMAÇÃO EM CIÊNCIA DA HOMEOPATIA, DESDE QUE ATENDA AS SEGUINTES CONDIÇÕES:
                        - Ter no mínimo 80% de presença das aulas programadas;
                        - Realizado os trabalhos de aula e obtido a nota mínima de aprovação;
                        - Estar em dia com os pagamentos.

                        TRANCAMENTO DE INSCRIÇÃO:
                        Em caso de desistência ou trancamento, o aluno assume o seguinte compromisso:

                        – Solicitação de desistência ou trancamento do Curso de Homeopatia, faz-se através do envio de e-mail para cursohomeopatias@terra.com.br ou telefonar (31) 3439-2500 com as seguintes informações: nome do aluno, cidade do curso e motivo.
                        Pedimos a descrição do motivo, para assim captarmos os problemas e dificuldades do aluno, como a situação financeira, qualidade do curso, organização administrativa, qualidade das aulas, etc, visando dar formação completa aos nossos alunos, buscando sempre a excelência do ensino-aprendizagem).
                        – Decidindo o aluno pela desistência ou trancamento, este assume o compromisso de quitar a parcela do mês até a data da comunicação de desistência

                        – Caso o aluno tenha a oportunidade de retornar ao curso, basta contatar a administração para reativar seu cadastro e recomeçar as aulas do mês em que havia suspendido anteriormente.

                        DATAS DAS AULAS:
                        As aulas, conforme a etapa, podem ocorrer aos sábados ou domingos de cada mês como também podem ocorrer em meses alternados, dependendo das especificidades do local do curso.
                        As datas previstas para o curso serão mantidas, independentemente de serem feriados ou feriadões.
                        Cabe salientar que uma data de aula pode ser alterada por motivo de força maior.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                            Entendo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php

            include("modulos/rodape.php");
        ?>
    </body>
</html>