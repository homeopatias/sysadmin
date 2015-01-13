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
                $cpf            = $_POST["cpf"];
                $email          = $_POST["email"];
                $login          = $_POST["login"];
                $senha          = $_POST["senha"];
                $loginIndicador = $_POST["indicador"];
                $telefone       = $_POST["telefone"];
                $endereco       = $_POST["endereco"];
                $escolaridade   = $_POST["escolaridade"];
                $curso          = $_POST["curso"];
                $cep            = $_POST["cep"];
                $rua            = $_POST["rua"];
                $numero         = $_POST["numero"];
                $complemento    = $_POST["complemento"];
                $bairro         = $_POST["bairro"];
                $cidade         = $_POST["cidade"];
                $estado         = $_POST["estado"];

                $nomeValido     = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                                  mb_strlen($nome, 'UTF-8') <= 100;
                $cpfValido      = isset($cpf) &&
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
                            $digito = floor($somaChecagem/11);
                            $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
                            if($digito != $cpfChecar[10]){
                                $cpfValido = false;
                            }
                        }

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
                            $cpfValido = false;
                        }

                    }

                    $cpfExistente = false;
                    if($cpfValido){
                        //Checa se ja existe este cpf no sistema cadastrado como aluno
                        $cpfNumerico = str_replace(".","",$cpf);
                        $cpfNumerico = str_replace("-","",$cpfNumerico);
                        $textoQuery = "SELECT U.cpf
                                       FROM Usuario U , Aluno A
                                       WHERE U.id = A.idUsuario AND U.cpf = ?";
        
                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $cpfNumerico, PDO::PARAM_STR);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();
    
                        if($linha = $query->fetch()){
                            $cpfValido = false;
                            $cpfExistente = true;
                        }
                    }


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
                $loginIndicadorValido = (isset($loginIndicador) &&
                                        mb_strlen($loginIndicador, 'UTF-8') >= 3 &&
                                        mb_strlen($loginIndicador, 'UTF-8') <= 100)
                                        || !isset($loginIndicador) || $loginIndicador === "";

                $idIndicador = "";
                if($loginIndicadorValido && isset($loginIndicador) && $loginIndicador !== ""){
                    // conferimos se o $loginIndicador representa um aluno no sistema
                    
                    $textoQuery  = "SELECT A.numeroInscricao FROM Aluno A, Usuario U WHERE                 
                                    U.login = ? AND A.idUsuario = U.id";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $loginIndicador, PDO::PARAM_INT);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if(!($linha = $query->fetch())){
                        $loginIndicadorValido = false;
                        $mensagem = "Não foi encontrado nos registros um aluno indicador com esse
                                     nome de usuário";
                    }else{
                        $idIndicador = $linha["numeroInscricao"];
                    }
                }

                $telefoneValido = isset($telefone) &&
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

                // se todos os dados estão válidos, o aluno é cadastrado
                if($nomeValido && $cpfValido && $emailValido && $loginValido && $senhaValida &&
                   $loginIndicadorValido && $telefoneValido && $enderecoValido &&
                   $escolaridadeValida && $cursoValido){

                    require_once("entidades/Aluno.php");

                    $novo = new Aluno($login);
                    $novo->setNome($nome);
                    $novo->setCpf($cpf);
                    $novo->setEmail($email);
                    $novo->setTelefone($telefone);
                    $novo->setCep($cep);
                    $novo->setRua($rua);
                    $novo->setNumero($numero);
                    $novo->setComplemento($complemento);
                    $novo->setBairro($bairro);
                    $novo->setCidade($cidade);
                    $novo->setEstado($estado);
                    $novo->setEscolaridade($escolaridade);
                    if($escolaridade === "superior incompleto" || $escolaridade === "superior completo"   ||
                       $escolaridade === "mestrado"            || $escolaridade === "doutorado" ){
                        $novo->setCurso(isset($curso) ? $curso : null);
                    }else{
                        $novo->setCurso(null);
                    }
                    $novo->setStatus("preinscrito");

                    $novo->setIdIndicador($idIndicador);

                    $sucesso = $novo->cadastrar($host, "homeopatias", $usuario, $senhaBD, $senha);

                    if(!$sucesso){
                        $mensagem = "Já existe um usuário com esse nome 
                                     de usuário no sistema";
                    } else {
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="index.php?sucessoAval=true">
        <script type="text/javascript">
            window.location = "index.php?cadastroSucesso=true";
        </script>
        <?php
                    }
                }else if(!$nomeValido){
                    $mensagem = "Nome inválido!";
                }else if(!$cpfValido && !$cpfExistente){
                    $mensagem = "CPF inválido!";
                }else if($cpfExistente){
                    $mensagem = "CPF ja cadastrado!";
                }else if(!$emailValido && !$emailExistente){
                    $mensagem = "E-mail inválido!";
                }else if($emailExistente){
                    $mensagem = "E-mail ja cadastrado!";
                }else if(!$loginValido){
                    $mensagem = "Nome de usuário inválido!";
                }else if(!$senhaValida){
                    $mensagem = "Senha inválida!";
                }else if(!$telefoneValido){
                    $mensagem = "Telefone inválido!";
                }else if(!$enderecoValido){
                    $mensagem = "Endereço inválido!";
                }else if(!$escolaridadeValida){
                    $mensagem = "Escolaridade inválida!";
                }else if(!$cursoValido){
                    if((!isset($curso) || $curso === "") && $superior){
                        $mensagem = "Insira o curso superior!";
                    }else{
                        $mensagem = "Curso inválido!";
                    }
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
                               pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                               placeholder="Nome" class="form-control" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="cpf-novo">CPF:</label>
                        <input type="text" name="cpf" id="cpf-novo" required
                               pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                               placeholder="xxx.xxx.xxx-xx" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email-novo">E-mail:</label>
                        <input type="email" name="email" id="email-novo" required
                               placeholder="E-mail"
                               title="Insira um e-mail válido"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="telefone-novo">Telefone:</label>
                        <input type="tel" name="telefone" id="telefone-novo" required
                               placeholder="(xx)xxxx-xxxx" pattern="^\(?\d{2}\)?\d{4}-?\d{4,7}$"
                               title="Insira um telefone válido"
                               class="form-control">
                    </div>
                    <div class="form-group col-sm-12" >

                        <label for="">Endereço do aluno:</label>
                        <div style="display:block">

                            <div  class="col-sm-6 col-md-4 " 
                                style="padding-top:10px;padding-bot:10px">
                                <label for="cep-novo" style="display:inline">CEP :</label>
                                <input type="text" name="cep" id="cep-novo"
                                    pattern="^[0-9]{2}.?[0-9]{3}-?[0-9]{3}$" 
                                    placeholder="xxxxx-xxx"
                                    title="Insira um CEP válido"
                                    class="form-control"
                                    style="width:90px" required>
                            </div>
                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="rua-novo">Rua :</label>
                                <input type="text" name="rua" id="rua-novo"
                                    pattern="^.{0,200}$" placeholder="Rua"
                                    title="A rua deve ter no máximo 200 caracteres"
                                    class="form-control"
                                    style="width:150px " required>
                            </div>
                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="numero-novo">
                                    Numero :</label>
                                <input type="text" name="numero" id="numero-novo"
                                    placeholder="xx"
                                    title="Insira o numero da residência do aluno"
                                    class="form-control"
                                    style="width:80px ;" required>
                            </div>

                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="bairro-novo" >
                                    Bairro :</label>
                                <input type="text" name="bairro" id="bairro-novo"
                                    placeholder="Bairro"
                                    title="Insira o bairro da residência do aluno"
                                    class="form-control"
                                    style="width:120px ;" required>
                            </div>

                            
                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="numero-novo" >
                                    Cidade :</label>
                                <input type="text" name="cidade" id="cidade-novo"
                                    placeholder="Cidade"
                                    title="Insira o numero da residência do aluno"
                                    class="form-control"
                                    style="width:150px ;" required>
                            </div>
                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="estado-novo">
                                    Estado :</label>
                                <select name="estado" id="estado-novo" class="form-control"
                                style="width:120px">
                                    <option value="AC">Acre</option>
                                    <option value="AL">Alagoas</option>
                                    <option value="AM">Amazonas</option>
                                    <option value="AP">Amapá</option>
                                    <option value="BA">Bahia</option>
                                    <option value="CE">Ceará</option>
                                    <option value="DF">Distrito Federal</option>
                                    <option value="ES">Espírito Santo</option>
                                    <option value="GO">Goiás</option>
                                    <option value="MA">Maranhão</option>
                                    <option value="MT">Mato Grosso</option>
                                    <option value="MS">Mato Grosso do Sul</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="PA">Pará</option>
                                    <option value="PB">Paraíba</option>
                                    <option value="PR">Paraná</option>
                                    <option value="PE">Pernambuco</option>
                                    <option value="PI">Piauí</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="RN">Rio Grande do Norte</option>
                                    <option value="RO">Rondônia</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="RR">Roraima</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="SE">Sergipe</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="TO">Tocantins</option>
                                </select>
                            </div>


                            <div  class="col-sm-6 col-md-12"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="complemento-novo">
                                    Complemento :</label>
                                <input type="text" name="complemento" id="complemento-novo"
                                    placeholder="Complemento"
                                    title="Insira o complemento da residência do aluno"
                                    class="form-control"
                                    style="width:200px" >
                            </div>

                        </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="escolaridade-novo">Nível de escolaridade:</label>
                        <select name="escolaridade" id="escolaridade-novo" class="form-control">
                            <option value="fundamental incompleto" selected>
                                Ensino Fundamental Incompleto
                            </option>
                            <option value="fundamental completo">
                                Ensino Fundamental Completo
                            </option>
                            <option value="médio incompleto">
                                Ensino Médio Incompleto
                            </option>
                            <option value="médio completo">
                                Ensino Médio Completo
                            </option>
                            <option value="superior incompleto">
                                Ensino Superior Incompleto
                            </option>
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
                    <div class="form-group" style="display: none">
                        <label for="curso-novo">Curso superior cursado:</label>
                        <input type="text" name="curso" id="curso-novo"
                               pattern="^.{0,200}$" placeholder="Curso superior cursado"
                               title="O curso deve ter no máximo 200 caracteres"
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
                    <div class="form-group">
                        <label for="indicador-novo">
                            Foi indicado por alguém?
                            Em caso afirmativo, insira o nome de usuário do
                            indicador:
                        </label>
                        <input type="text" name="indicador" id="indicador-novo"
                               pattern="^.{3,100}$"
                               placeholder="Nome de usuário do indicador, se existir"
                               title="Esse campo deve ter um login de 3 a 100 caracteres ou ficar vazio"
                               class="form-control" autocomplete="off">
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
                            e todas as outras abrangidas.
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