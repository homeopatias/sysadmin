<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Cadastro no curso de Pós-Graduação - Homeopatias.com</title>
        <script>
            // aqui recebemos os dados das cidades existentes para cada ano
            // assim podemos atualizar a lista de cidades dinamicamente durante a inserção de
            // matrícula
            
            var cidades = new Array();
            <?php
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

                $textoQuery  = "SELECT idCidade, UF, nome, ano, modalidadeCidade
                                FROM Cidade WHERE
                                CURDATE() < limiteInscricao AND 
                                tipo_curso = 'pos' 
                                OR tipo_curso = 'todos' ORDER BY ano DESC, nome ASC";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                // variável para garantir que inicializaremos o vetor para cada
                // ano sempre que estivermos utilizando-o pela primeira vez
                $anos = [];

                // Armazenamos uma lista de cidades do ano atual para facilitar a retificação
                // de matrícula
                $cidadesAnoAtual = array();

                while ($linha = $query->fetch()){
                    // para cada cidade encontrada criamos um objeto no
                    // código javascript para representá-la
                    $id         = "\"".htmlspecialchars($linha["idCidade"])."\"";
                    $uf         = "\"".htmlspecialchars($linha["UF"])."\"";
                    $nome       = "\"".htmlspecialchars($linha["nome"])."\"";
                    $ano        = "\"".htmlspecialchars($linha["ano"])."\"";
                    $modalidade = "\"".htmlspecialchars($linha["modalidadeCidade"])."\"";

                    if($linha["ano"] == date("Y")) {
                        $nomeCidade = htmlspecialchars($linha["nome"]). "/" . htmlspecialchars($linha["UF"]);
                        $cidadesAnoAtual[$nomeCidade] = htmlspecialchars($linha["idCidade"]);
                    }

                    if(!in_array($linha["ano"], $anos)){
                        $anos[] = $linha["ano"];
            ?>
            
            cidades[ <?= $ano ?> ] = new Array();
            <?php } ?>

            cidades[ <?= $ano ?> ].push({
                id:     <?= $id ?>,
                uf:     <?= $uf ?>,
                nome:   <?= $nome ?>,
                ano:    <?= $ano ?>,
                modalidade: <?= $modalidade ?>
            });

            <?php
                }
            ?>

            $(document).ready(function(){
                $("#li-termos").change(function(){
                    $("#cadastro").prop('disabled', 
                                        $('#li-termos').is(':checked') ? false : true);
                });
                
                $("#escolaridade-novo").change(function(){
                    if($(this).val() === "superior incompleto" || $(this).val() === "superior completo"   ||
                       $(this).val() === "mestrado"            || $(this).val() === "doutorado" ){
                        $("#curso-novo").parent().show(500);
                    }else{
                        $("#curso-novo").parent().hide(500);
                    }
                });

                // quando altera a modalidade atualiza as cidades
                $("#modalidade_curso").change(function(){
                    atualizaCamposCidade();
                });

                // faz a primeira atualização de campos para limpar o campo
                // select de cidades
                atualizaCamposCidade();
            });

            // Atualiza os campos de acordo com a modalidade desejada
            function atualizaCamposCidade(){
                var modalidades = $("#modalidade_curso");
                var cidadeMat   = $("#cidadeMat");
                var ano = (new Date).getFullYear();

                cidadeMat.find('option').remove().end();

                if(modalidades.val() === "regular"){

                    if(cidades[ano]){

                        cidades[ano].forEach(function(cidade){
                            if(cidade.modalidade == "regular" || 
                                cidade.modalidade == "ambos"){

                                cidadeMat.append('<option value="' + cidade.id + '">' + 
                                    cidade.nome + "/"
                                    + cidade.uf + '</option>')
                            }
                            
                        });
                    }
                }else if(modalidades.val() === "intensivo"){
                    if(cidades[ano]){
                        cidades[ano].forEach(function(cidade){
                            if(cidade.modalidade == "intensivo"|| 
                                cidade.modalidade == "ambos"){
                                $("#cidadeMat")
                                .append('<option value="' + cidade.id + '">' + cidade.nome + "/"
                                        + cidade.uf + '</option>')
                            }
                            
                        });
                    }
                }
            }
        </script>
    </head>
    <body>
        <?php
            // mensagem a ser exibida acima do formulário de cadastro, caso seja necessário
            $mensagem = "";

            include('modulos/navegacao.php');

            // se o aluno chegou até aqui através de um formulário, registra-o no sistema
            if(isset($_POST["submit"])){

                // validamos todos os dados recebidos
                $nome           = $_POST["nome"];
                $email          = $_POST["email"];
                $login          = $_POST["login"];
                $senha          = $_POST["senha"];
                $escolaridade   = $_POST["escolaridade"];
                $curso          = $_POST["curso"];
                $recebeEmail    = isset($_POST["deseja-email"]);

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

                $escolaridadeValida = isset($escolaridade) &&
                           ($escolaridade === "superior completo"      ||
                            $escolaridade === "mestrado"               ||
                            $escolaridade === "doutorado");

                $cursoValido = isset($curso) && mb_strlen($curso) > 0 && mb_strlen($curso) <= 200;


                $sucesso = true;

                // se todos os dados estão válidos, o aluno é cadastrado
                if($nomeValido && $emailValido && $loginValido && $senhaValida && $escolaridadeValida && $cursoValido
                   ){

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

												echo "<script> alert(\"Usuário existente, faça o cadastro novamente! \"); window.location = \"cadastro_aluno_extensao.php\";</script>";
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

                    $dadosAluno  = array($idUsuario, $escolaridade, $curso, $recebeEmail);
                    $queryAluno  = "INSERT INTO Aluno (idUsuario, status, pais, tipo_curso, tipo_cadastro,"
                                    .                   " escolaridade, curso,"
                                    .                   " ativo, recebeEmail) VALUES
                                    (?, 'preinscrito', 'BRL', 'pos', 'faculdade inspirar',"
                                    ." ?, ?,"
                                    ." 0, ?)";


                    $query = $conexao->prepare($queryAluno);
                    $sucessoAluno = $query->execute($dadosAluno);

                    if($sucessoUsuario && $sucessoAluno) {
                        // deu tudo certo, inserimos o aluno
                        $conexao->commit();

						$assunto = "Homeopatias.com - Enviar Documentos";
						$msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la.</b><br><br>";
						$msg .= "<h3>Você está pré-inscrito no Curso de Pós-Graduação, para a inscrição se concretizar, você deve enviar-nos:</h3><br>";
						$msg .= "<ul><li>Cópia autenticada do diploma de Graduação</li><li>Cópia autenticada do CPF e RG</li><li>Copia de comprovante de residencia</li><li> Uma foto 3x4</ul><br>";
						$msg .= "<br><h4>Endereço:</h4><br><br>R. Prof. Rubens Romanelli, 77 - Bandeirantes (Pampulha)
						Belo Horizonte - MG
						31365-350";
						$msg .= "<br><b>ou entregar ao coordenador local da sua cidade na primeira aula em março.</b><br>";
						$msg .= "<br>Após a aprovação da sua documentação  e o pagamento da taxa de inscrição, sua inscrição estará concluida<br>";
						$msg .= "<br><br><b>Quando sua documentação for aprovada, você será notificado por e-mail.</b><br>Qualquer dúvida envie mensagem para cursohomeopatias@terra.com.br / financeiro homeopatia@terra.com.br. Ligar para (31) 3439-2500 - central nacional custo de ligação local 3003-6777.<br><br>";
						$msg .= "<br><br>Obrigado,<br>Equipe Homeobrás e Instituto Hahnemann.";
						$headers = "Content-type: text/html; charset=utf-8 " .
								"From: Sistema Administrativo Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
								"Reply-To: noreply@homeopatias.com" . "\r\n" .
								"X-Mailer: PHP/" . phpversion();

						mail($email, $assunto, $msg, $headers);

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
                } else if(!$escolaridadeValida){
                    $mensagem = "Escolaridade inválida!";
                }else if(!$cursoValido){
                    $mensagem = "Curso inválido!";
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
                        <label for="nome-novo">Nome Completo:</label>
                        <input type="text" name="nome" id="nome-novo" required
                               pattern="^.{3,100} .{1,50}$" title="O nome deve ter de 3 a 100 caracteres, insira seu nome completo"
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
                        <label for="escolaridade-novo">Nível de escolaridade:</label>
                        <select name="escolaridade" id="escolaridade-novo" class="form-control">
                            <option value="superior completo">
                                Ensino Superior Completo
                            </option>
                            <option value="mestrado">
                                Mestrado
                            </option>
                            <option value="doutorado">
                                Doutorado
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="curso-novo">Curso superior cursado:</label>
                        <input type="text" name="curso" id="curso-novo" required
                               pattern="^.{1,200}$" placeholder="Curso superior cursado"
                               title="O curso deve estar preenchido e ter no máximo 200 caracteres"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="login-novo">Nome de usuário <span style="font-weight: bold; color: red;" >(APENAS LETRAS E NÚMEROS SEM ESPAÇOS VAZIOS)</span>:</label>
                        <input type="text" name="login" id="login-novo" required
                               pattern="[a-z0-9]+" placeholder="Nome de usuário"
                               title="O login deve ter de 3 a 100 caracteres com apenas letras(minúsculas e sem acentos) e números!"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="senha-novo">Senha:</label>
                        <input type="password" name="senha" id="senha-novo" required
                               pattern="^.{6,72}$" placeholder="Senha"
                               title="A senha deve ter de 6 a 72 caracteres"
                               class="form-control">
                    </div>
                    <!--
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
                    -->
                    <!-- Até o momento não será necessária a documentação para fazer a Pós
                    <h3>Seu acesso ao sistema só será permitido após o envio da documentação para a Homeobrás
                        para avaliação e aprovação!
                    </h3>
                    -->
                    <br>
                    <div class="form-group">
                        <label>
                            Deseja receber e-mail com atualizações e promoções dos cursos?
                        </label>
                        <br>
                        <input type="checkbox" id="deseja-email" name="deseja-email">
                        <label for="deseja-email">Caso deseje, marque o botão ao lado</label>
                    </div>
                    <br>
                    <button type="submit" name="submit" value="submit" id="cadastro"
                            class="btn btn-primary pull-right">
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
                        Curso de Pós Graduação Lato Sensu - Especialização em Ciência da Homeopatia.
                        1) Aulas no sábado e no domingo por mês: das 8:00 às 18:00.
                        <b>2) Investimento 2015: 1 inscrição e 11 parcelas.</b>
                        3) Duração do Curso: 2 anos - março/2015 a dezembro /2016.​
                        4) Carga Horária  = Certificado de Especialização - 500H. 
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
