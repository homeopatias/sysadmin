<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Cadastro de associado CONAHOM - Homeopatias.com</title>
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

            $sql = "SELECT nome FROM Instituicao WHERE inicioInsc <= CURDATE() AND
                    fimInsc >= CURDATE() AND nome = 'conahom'";
            $query = $conexao->prepare($sql);
            $query->setFetchMode(PDO::FETCH_ASSOC);
            $query->execute();

            if($query->rowCount() == 0) {
            ?>

            <!-- redireciona o usuário para o index.php -->
            <meta http-equiv="refresh" content="0; url=index.php">
            <script type="text/javascript">
                window.location = "index.php?mensagem=O cadastro na CONAHOM ainda não está aberto!";
            </script>

            <?php
                die();
            }

            // se o associado chegou até aqui através de um formulário, registra-o no sistema
            if(isset($_POST["submit"])){
                // validamos todos os dados recebidos
                $nome               = $_POST["nome"];
                $cpf                = $_POST["cpf"];
                $email              = $_POST["email"];
                $login              = $_POST["login"];
                $senha              = $_POST["senha"];
                $telefone           = $_POST["telefone"];
                $cep                = $_POST["cep"];
                $rua                = $_POST["rua"];
                $bairro             = $_POST["bairro"];
                $numero             = $_POST["numero"];
                $pais               = $_POST["pais"];
                $complemento        = $_POST["complemento"];
                $cidade             = $_POST["cidade"];
                $estado             = $_POST["estado"];
                $formTerapeutica    = $_POST["form-terapeutica"];

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
                        $digito = floor($somaChecagem/11);
                        $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
                        if($digito != $cpfChecar[10]){
                            $cpfValido = false;
                        }
                    }

                    $todosZero = true;
                    $todosNove = true;

                    for($i = 0; $i <12; $i++){
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
                if($cpfValido){
                    //Checa se ja existe este cpf no sistema cadastrado como associado
                    $textoQuery = "SELECT U.cpf
                                   FROM Usuario U , Associado A
                                   WHERE U.id = A.id AND U.cpf = ?";
    
                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1,$cpf, PDO::PARAM_STR);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if($linha = $query->fecth()){
                        $cpfValido = false;
                    }
                }

                $emailValido       = isset($email) && mb_strlen($email,'UTF-8') <= 100 &&
                                     preg_match("/^.+\@.+\..+$/", $email);
                                     
                if($emailvalido){
                    //Checa se ja existe este email no sistema cadastrado como associado
                    $textoQuery = "SELECT U.email
                                   FROM Usuario U , Associado A
                                   WHERE U.id = A.id AND U.email = ?";
    
                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1,$email, PDO::PARAM_STR);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if($linha = $query->fecth()){
                        $emailvalido = false;
                    }
                }
                $loginValido       = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                                     mb_strlen($login, 'UTF-8') <= 100;
                $senhaValida       = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 &&
                                     mb_strlen($senha, 'UTF-8') <= 72;

                $telefoneValido = isset($telefone) &&
                                  preg_match("/^\(?\d{2}\)?\d{4}-?\d{4,7}$/", $telefone);

                //checa se o endereço é válido em todas as suas partes-------
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

                //-------------------------------------------------------------------

                $formTerapeuticaValida = isset($formTerapeutica) &&
                                         mb_strlen($formTerapeutica, "UTF-8") >= 3 &&
                                         mb_strlen($formTerapeutica, "UTF-8") <= 200;

                // se todos os dados estão válidos, o associado é cadastrado
                if($nomeValido && $cpfValido && $emailValido && $loginValido && $senhaValida &&
                   $telefoneValido && $enderecoValido && $cidadeValida && $estadoValido &&
                   $formTerapeuticaValida){

                    // lemos as credenciais do banco de dados
                    $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                    $dados = json_decode($dados, true);

                    foreach($dados as $chave => $valor) {
                        $dados[$chave] = str_rot13($valor);
                    }

                    $host    = $dados["host"];
                    $usuario = $dados["nome_usuario"];
                    $senhaBD = $dados["senha"];

                    require_once("entidades/Associado.php");

                    $novo = new Associado($login);
                    $novo->setNome($nome);
                    $novo->setCpf($cpf);
                    $novo->setEmail($email);
                    $novo->setTelefone($telefone);
                    $novo->setCep($cep);
                    $novo->setRua($rua);
                    $novo->setNumero($numero);
                    $novo->setBairro($bairro);
                    $novo->setPais("BRL");
                    $novo->setComplemento($complemento);
                    $novo->setCidade($cidade);
                    $novo->setEstado($estado);
                    $novo->setInstituicao("conahom");
                    $novo->setFormacaoTerapeutica($formTerapeutica);
                    $novo->setEnviouDocumentos(false);

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
                } else if (!$nomeValido) {
                    $mensagem = "Nome inválido!";
                } else if (!$cpfValido) {
                    $mensagem = "CPF inválido!";
                } else if (!$emailValido) {
                    $mensagem = "E-mail inválido!";
                } else if (!$loginValido) {
                    $mensagem = "Nome de usuário inválido!";
                } else if (!$senhaValida) {
                    $mensagem = "Senha inválida!";
                } else if (!$telefoneValido) {
                    $mensagem = "Telefone inválido!";
                } else if (!$enderecoValido) {
                    $mensagem = "Endereço inválido!";
                } else if (!$formTerapeuticaValida) {
                    $mensagem = "Formação terapeutica inválida";
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
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" name="nome" id="nome" required
                               pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                               placeholder="Nome" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cpf">CPF:</label>
                        <input type="text" name="cpf" id="cpf" required
                               pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                               placeholder="xxx.xxx.xxx-xx" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" name="email" id="email" required
                               placeholder="E-mail"
                               title="Insira um e-mail válido"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="tel" name="telefone" id="telefone" required
                               placeholder="(xx)xxxx-xxxx" pattern="^\(?\d{2}\)?\d{4}-?\d{4,7}$"
                               title="Insira um telefone válido"
                               class="form-control">
                    </div>
                    <div class="form-group col-sm-12" >

                        <label for="">Endereço do aluno:</label>
                        <div style="display:block">

                            <div  class="col-sm-6 col-md-4 " 
                                style="padding-top:10px;padding-bot:10px">
                                <label for="cep" style="display:inline">CEP :</label>
                                <input type="text" name="cep" id="cep"
                                    pattern="^[0-9]{2}.?[0-9]{3}-?[0-9]{3}$" 
                                    placeholder="xxxxx-xxx"
                                    title="Insira um CEP válido"
                                    class="form-control"
                                    style="width:90px" required>
                            </div>
                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="rua">Rua :</label>
                                <input type="text" name="rua" id="rua"
                                    pattern="^.{0,200}$" placeholder="Rua"
                                    title="A rua deve ter no máximo 200 caracteres"
                                    class="form-control"
                                    style="width:150px " required>
                            </div>
                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="numero">
                                    Numero :</label>
                                <input type="text" name="numero" id="numero"
                                    placeholder="xx"
                                    title="Insira o numero da residência do aluno"
                                    class="form-control"
                                    style="width:80px ;" required>
                            </div>

                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="bairro" >
                                    Bairro :</label>
                                <input type="text" name="bairro" id="bairro"
                                    placeholder="Bairro"
                                    title="Insira o bairro da residência do aluno"
                                    class="form-control"
                                    style="width:120px ;" required>
                            </div>

                            
                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="numero" >
                                    Cidade :</label>
                                <input type="text" name="cidade" id="cidade"
                                    placeholder="Cidade"
                                    title="Insira o numero da residência do aluno"
                                    class="form-control"
                                    style="width:150px ;" required>
                            </div>
                            <div  class="col-sm-6 col-md-4"
                            style="padding-top:10px;padding-bot:10px">
                                <label for="estado">
                                    Estado :</label>
                                <select name="estado" id="estado" class="form-control"
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
                                <label for="complemento">
                                    Complemento :</label>
                                <input type="text" name="complemento" id="complemento"
                                    placeholder="Complemento"
                                    title="Insira o complemento da residência do aluno"
                                    class="form-control"
                                    style="width:200px" >
                            </div>

                        </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="form-terapeutica">Formação terapêutica:</label>
                        <input type="text" name="form-terapeutica" id="form-terapeutica" required
                               pattern="^.{3,200}$" placeholder="Formação terapêutica"
                               title="O campo de formação terapêutica deve ter de 3 a 200 caracteres"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="login">Nome de usuário:</label>
                        <input type="text" name="login" id="login" required
                               pattern="^.{3,100}$" placeholder="Nome de usuário"
                               title="O login deve ter de 3 a 100 caracteres"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha:</label>
                        <input type="password" name="senha" id="senha" required
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
                        </span><b> Informações para o associado - LEIA ANTES DE SE CADASTRAR</b>
                    </a>
                    <br><br><br>
                    <div class="form-group">
                        <label>
                            Marcando a opção abaixo, você confirma que leu e compreendeu
                            todas as informações referentes aos custos da associação, além
                            de todos os documentos necessários para a efetivação da mesma,
                            estando essa instituição isenta de qualquer responsabilidade caso
                            surjam problemas na associação devido à negligência para com os itens
                            supracitados.
                        </label>
                        <br>
                        <input type="checkbox" id="li-termos">
                        <label for="li-termos">Confirmo</label>
                    </div>
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
                        <h4 class="modal-title">Associação - Informações principais</h4>
                    </div>
                    <div class="modal-body"
                         style="font-size: 0.9em; white-space: pre-line; overflow: none">
                        Inscrição: R$120,00 

                        Anuidade: R$120,00 

                        Faça sua pré-inscrição e tenha acesso às formas de pagamento pelo PagSeguro.

                        Sua inscrição será efetivada após confirmação do pagamento e aprovação dos documentos abaixo. 

                        Documentos necessários:

                        <b>1 Foto 3x4

                        Curriculum vitae

                        Xerox Autenticado em cartório de:

                        1) Identidade;

                        2) CPF;

                        3) Comprovante de endereço;

                        4) Certificados de curso de Homeopatia - Mínimo de 400 horas.

                        5) Certificados de curso de Fitoterapia - Mínimo de 180 horas.</b>


                        Sua documentação será analisada e a resposta será enviada por e-mail ou carta.

                        Endereço para envio:

                        CONAHOM
                        Av. Antônio Abraão Caram, 430/701
                        31275-000 Belo Horizonte/MG
                        Telefone: (31) 3439-2500
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