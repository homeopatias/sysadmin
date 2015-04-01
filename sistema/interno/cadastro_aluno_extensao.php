<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Cadastro no curso de extensão - Homeopatias.com</title>
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
                                tipo_curso = 'extensao'
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

                if(!($query->rowCount())) {
                    // caso não hajam cidades abertas para inscrição, avisa o aluno
                    session_destroy();
        ?>
            window.location.href = "index.php?mensagem=No momento não há nenhuma cidade aberta para matrícula." +
                              " Desculpe-nos o transtorno, em breve as inscrições serão abertas novamente." +
                              " Agradecemos o interesse!";
        <?php
                }

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
                $cpf            = $_POST["cpf"];
                $idIndicador    = $_POST["indicador"];
                $telefone       = $_POST["telefone"];
                $telefone2      = $_POST["telefone2"];
                $telefone3      = $_POST["telefone3"];
                $cep            = $_POST["cep"];
                $rua            = $_POST["rua"];
                $numero         = $_POST["numero"];
                $complemento    = $_POST["complemento"];
                $bairro         = $_POST["bairro"];
                $cidade         = $_POST["cidade"];
                $estado         = $_POST["estado"];
                $idCidadeMat    = $_POST["cidadeMat"];
                $modalidade     = $_POST["modalidade_curso"];                
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


                $idIndicadorValido = (isset($idIndicador) && !is_nan($idIndicador))
                                        || !isset($idIndicador) || $idIndicador === "";

                if($idIndicadorValido && isset($idIndicador) && $idIndicador !== ""){
                    // conferimos se o $idIndicador representa um aluno no sistema
                    
                    $textoQuery  = "SELECT A.numeroInscricao FROM Aluno A WHERE                 
                                    A.numeroInscricao = ?";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $idIndicador, PDO::PARAM_INT);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if(!($linha = $query->fetch())){
                        $idIndicadorValido = false;
                        $mensagem = "Não foi encontrado nos registros um aluno indicador com esse
                                     número de matrícula";
                    }
                }

                $telefoneValido = isset($telefone) &&
                                  preg_match("/^\(?\d{2}\)?\d{4}-?\d{4,7}$/", $telefone);
                $telefonesOpcValidos = (!isset($telefone2) ||
                                  preg_match("/^\(?\d*\)?\d*-?\d*$/", $telefone2)) &&
                                       (!isset($telefone3) ||
                                  preg_match("/^\(?\d*\)?\d*-?\d*$/", $telefone3));

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

                $modalidadeValida = ( isset($modalidade) ) &&
                                    $modalidade == "regular" || $modalidade == "intensivo" ;

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
                $superior = $escolaridade === "superior incompleto"    ||
                             $escolaridade === "superior completo"     ||
                             $escolaridade === "mestrado"              ||
                             $escolaridade === "doutorado";
                $cursoValido = ((!isset($curso) || $curso === "") && !$superior) ||

                               (isset($curso) && mb_strlen($curso) > 0 && mb_strlen($curso) <= 200);

                // verificamos se a cidade na qual o aluno quer se matricular
                // é válida
                $cidadeMatValida = isset($idCidadeMat) && preg_match("/^\d*$/", $idCidadeMat);

                if($cidadeMatValida) {
                    $textoQuery  = "SELECT idCidade FROM Cidade
                                    WHERE idCidade = ? AND ano = YEAR(CURDATE())
                                    AND modalidadeCidade = ? OR modalidadeCidade = 'ambos'";

                    $query = $conexao->prepare($textoQuery);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute(array($idCidadeMat, $modalidade));
                    if($linha = $query->fetch()){
                        // cidade existente no ano atual
                        // (trecho mantido apenas por clareza)
                        $cidadeMatValida = true;
                    } else {
                        // o id de cidade passado não existe, ou
                        // não é do ano atual
                        $cidadeMatValida = false;
                    }
                }

                $sucesso = true;

                // se todos os dados estão válidos, o aluno é cadastrado
                if($nomeValido && $emailValido && $loginValido && $senhaValida && $cpfValido &&
                   $idIndicadorValido && $telefoneValido && $enderecoValido && $escolaridadeValida &&
                   $cursoValido && $cidadeMatValida && $telefonesOpcValidos){

                    require_once("entidades/Aluno.php");

                    $query = $conexao->prepare("SELECT login FROM Usuario WHERE login=?");
                    $query->bindParam(1, $login, PDO::PARAM_STR);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if ($linha = $query->fetch()){
                        // já existe alguém com esse nome de usuário no sistema
                        $conexao = null;
                        $sucesso = false;
                        echo "<script> alert(\"Usuário existente, faça o cadastro novamente! \"); window.location.href = \"cadastro_aluno_extensao.php\";</script>";
                    }

                    $aluno = new Aluno($login);
                    $aluno->setEmail($email);
                    $aluno->setNome($nome);
                    $aluno->setStatus("preinscrito");
                    $aluno->setPais("BRL");
                    $aluno->setTipoCurso("extensao");
                    $aluno->setTipoCadastro("faculdade inspirar");
                    $aluno->setRecebeEmail($recebeEmail);
                    $aluno->setCpf($cpf);
                    $aluno->setTelefone($telefone);
                    if(isset($telefone2))
                        $aluno->setTelefone2($telefone2);
                    if(isset($telefone3))
                        $aluno->setTelefone3($telefone3);
                    $aluno->setCep($cep);
                    $aluno->setRua($rua);
                    $aluno->setNumero($numero);
                    $aluno->setComplemento($complemento);
                    $aluno->setBairro($bairro);
                    $aluno->setCidade($cidade);
                    $aluno->setEstado($estado);
                    $aluno->setIdIndicador($idIndicador);
                    $aluno->setModalidadeCurso($modalidade);
                    $aluno->setAtivo(true);
                    $aluno->setEscolaridade($escolaridade);

                    if($escolaridade === "superior incompleto" || $escolaridade === "superior completo"   ||
                           $escolaridade === "mestrado"            || $escolaridade === "doutorado" ){
                        $aluno->setCurso(isset($curso) ? $curso : null);
                    }else{
                        $aluno->setCurso(null);
                    }

                    $sucesso = $aluno->cadastrar($host, $db, $usuario, $senhaBD, $senha);

                    if(!$sucesso){
                        $mensagem = "Já existe um usuário com esse nome 
                                     de usuário no sistema";
                    } else {

                        // agora fazemos a matrícula do aluno

                        $idAluno = $aluno->getNumeroInscricao();

                        $dadosMatricula  = array($idAluno, $idCidadeMat);
                        $queryMatricula  = "INSERT INTO Matricula (chaveAluno, etapa, chaveCidade) 
                                            VALUES (?,1,?)";
                        $query  = $conexao->prepare($queryMatricula);
                        $sucessoMatricula = $query->execute($dadosMatricula);
                        $idUltimaMatricula = $conexao->lastInsertId();

                        // agora tentamos criar os pagamentos

                        // pega os valores de inscrição e parcelas da cidade
                        $textoQuery = "SELECT C.nome, C.idCidade,C.ano,";

                        //pega as parcelas da extensao
                        if($modalidade == "regular"){
                            $textoQuery .= "C.inscricao_extensao_regular
                                            as inscricao,
                                            C.parcela_extensao_regular
                                            as parcela";
                        }
                        if($modalidade == "intensivo"){
                            $textoQuery .= "C.inscricao_extensao_intensivo
                                            as inscricao,
                                            C.parcela_extensao_intensivo
                                            as parcela";
                        }

                        $textoQuery.= " FROM Cidade C, Matricula M
                                       WHERE C.idCidade = M.chaveCidade AND
                                       M.idMatricula = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1,$idUltimaMatricula);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $sucesso = $query->execute();

                        if($sucesso) {
                            $aluno->setStatus("inscrito");
                            $aluno->atualizar($host, $db, $usuario, $senhaBD);
                        }
                        
                        $queryInsert = "";
                        $insertArray = [];

                        $sucessoPgto = false;

                        if($linha = $query->fetch()){
                            $precoInscricao = $linha["inscricao"];
                            $precoParcela = $linha["parcela"];
                            
                            for($i = 0; $i < 12; $i++){

                                if($i == 0){ // parcela numero 0 será considerada valor da
                                             // inscrição
                                    $queryInsert    = "INSERT INTO `homeopatias`.`PgtoMensalidade` 
                                                    (`chaveMatricula`, `numParcela`, `ValorTotal`, `ValorPago`, 
                                                        `desconto`, `fechado`,`ano`) 
                                                    VALUES (?, '0', ?, '0', '0', '0', ?) ";
                                    $insertArray  = array($idUltimaMatricula, $precoInscricao, $linha["ano"]);

                                } 
                                else{
                                    $queryInsert    .= " , (?, ?, ?, '0', '0', '0', ?) ";
                                    $insertArray[]  = $idUltimaMatricula;
                                    $insertArray[]  = $i;
                                    $insertArray[]  = $precoParcela;
                                    $insertArray[]  = $linha["ano"];
                                }
                            }
                            $query = $conexao->prepare($queryInsert);
                            $sucessoPgto = $query->execute($insertArray);
                        } else {
                            // a cidade não foi encontrada
                            $mensagem = "Cidade não encontrada";
                        }

                        if(!$sucessoMatricula) {
                            // erro na matrícula
                            $mensagem = "Erro na matrícula";
                        } else if(!$sucessoInscrito) {
                            // erro na mudança para inscrito
                            $mensagem = "Erro na atualização de status de aluno após matrícula";
                        } else if(!$sucessoPgto) {
                            // erro na criação dos pagamentos
                            $mensagem = "Erro na criação dos pagamentos do ano";
                        }

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
                        } else {
                            $queryMoodle = "SELECT id FROM mdl_user WHERE username = ?";

                            $query = $conMoodle->prepare($queryMoodle);
                            $query->bindParam(1, $aluno->getLogin());
                            $query->setFetchMode(PDO::FETCH_ASSOC);
                            $query->execute();

                            $idUsuarioMoodle = false;
                            if($linha = $query->fetch()) {
                                $idUsuarioMoodle = $linha["id"];

                                $queryMoodle = "INSERT INTO mdl_user_enrolments
                                                (status,enrolid,userid,timecreated,
                                                 timemodified) VALUES (0,1,?,NOW(),NOW())";


                                $query = $conMoodle->prepare($queryMoodle);
                                $query->bindParam(1, $idUsuarioMoodle);
                                $sucessoMoodle = $query->execute();

                                if($sucessoMoodle) {
                                    $queryMoodle = "INSERT INTO mdl_role_assignments
                                                    (roleid,contextid,userid,timemodified)
                                                    VALUES (5,18,?,NOW())";


                                    $query = $conMoodle->prepare($queryMoodle);
                                    $query->bindParam(1, $idUsuarioMoodle);
                                    $sucessoMoodle = $query->execute();
                                }
                            } else {
                                $sucessoMoodle = false;
                            }
                        }
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content=<?= '"index.php?sucessoAval=true&mensagem='.$mensagem.'"'?>>
        <script type="text/javascript">
            window.location.href = <?= '"index.php?sucessoAval=true&mensagem='.$mensagem.'"'?>;
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
                }else if(!$cpfValido && !$cpfExistente){
                    $mensagem = "CPF inválido!";
                }else if($cpfExistente){
                    $mensagem = "CPF ja cadastrado!";
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
                } else if (!$cidadeMatValida) {
                    $mensagem = "Cidade de curso inválida!";
                } else if(!$telefonesOpcValidos) {
                    $mensagem = "Telefones opcionais inválidos!";
                }
            }

        ?>

        <div class="col-xs-12 vertical-center">
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
                    <p class="warning">
                        Selecione uma modalidade para visualizar as cidades disponíveis
                    </p>
                    <label for="modalidade-novo" >
                        Modalidade desejada :</label>
                    <select id="modalidade_curso" name="modalidade_curso" class="form-control">
                        <option value="">Selectione uma modalidade</option>
                        <option value="regular">Regular</option>
                        <option value="intensivo">Intensivo</option>
                    </select>
                    <br>

                    <label for="cidadeMat">Escolha a cidade onde deseja fazer o curso:</label>
                    <select name="cidadeMat" id="cidadeMat"
                            class="form-control" required>
                        <?php
                            foreach($cidades as $cidade){
                                echo '<option value="' . $cidade["id"] . '">';
                                echo $cidade["nome"] . '</option>';
                            }
                        ?>
                    </select>
                    <br>
                    <div class="form-group">
                        <label for="cpf-novo">CPF:</label>
                        <input type="text" name="cpf" id="cpf-novo" required
                               pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                               placeholder="xxx.xxx.xxx-xx" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="telefone-novo">Telefone 1:</label>
                        <input type="tel" name="telefone" id="telefone-novo" required
                               placeholder="(xx)xxxx-xxxx" pattern="^\(?\d*\)?\d*-?\d*$"
                               title="Insira um telefone válido"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="telefone2-novo">Telefone 2 (opcional):</label>
                        <input type="tel" name="telefone2" id="telefone2-novo"
                               placeholder="(xx)xxxx-xxxx" pattern="^\(?\d*\)?\d*-?\d*$"
                               title="Insira um telefone válido, ou deixe o campo vazio"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="telefone3-novo">Telefone 3 (opcional):</label>
                        <input type="tel" name="telefone3" id="telefone3-novo"
                               placeholder="(xx)xxxx-xxxx" pattern="^\(?\d{2}\)?\d{4}-?\d{4,7}$"
                               title="Insira um telefone válido, ou deixe o campo vazio"
                               class="form-control">
                    </div>
                    <div class="form-group col-sm-12" >

                        <label for="">Endereço do aluno:</label>
                        <div style="display:block">

                            <div  class="col-sm-6 col-md-4 " 
                                style="padding-top:10px;padding-bot:10px">
                                <label for="cep-novo" style="display:inline">CEP :</label>
                                <input type="text" name="cep" id="cep-novo"
                                    pattern="(^[0-9]{2}.?[0-9]{3}-?[0-9]{3}$|^$)" 
                                    placeholder="xxxxx-xxx"
                                    title="Insira um CEP válido"
                                    class="form-control"
                                    style="width:90px">
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
                                <label for="cidade-novo" >
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
                        <label for="indicador-novo">
                            Foi indicado por alguém?
                            Em caso afirmativo, insira o número de matrícula do
                            indicador:
                        </label>
                        <input type="text" name="indicador" id="indicador-novo"
                               pattern="^\d*$"
                               placeholder="Número de matrícula do indicador, se existir"
                               title="Esse campo deve ter número inteiro ou ficar vazio"
                               class="form-control" autocomplete="off">
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
                    <br>
                    <div class="form-group">
                        <label>
                            Deseja receber e-mail com atualizações e promoções dos cursos?
                        </label>
                        <br>
                        <input type="checkbox" id="deseja-email" name="deseja-email">
                        <label for="deseja-email">Caso deseje, marque o botão ao lado</label>
                    </div>
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
                        CURSO REGULAR FORMAÇÃO EM CIÊNCIA DA HOMEOPATIA – duração 2 anos
                        1)  Aulas no sábado das 8:00 às 18:00.   
                        <b>2)  Investimento em 2015: 1 inscrição e 11 parcelas.</b>
                        3)  Investimento em 2016: 1 inscrição e 11 parcelas anuais, com correção, aulas no Domingo.
                        4)  Duração do Curso: 2 anos - março /2015 a dezembro /2016.
                        5)  Carga Horária  ano 2015/16 = Certificado de Formação - 400H.
                        6)  Curso de Aprofundamento 2017/2018.
                        7) Certificado de Extensão de Formação em Ciência da Homeopatia - 400h Faculdade Inspirar

                        CURSO INTENSIVO  FORMAÇÃO EM CIÊNCIA DA HOMEOPATIA – duração 1 ano
                        1) Aulas no sábado e no domingo por mês - das 8:00 às 18:00.
                        <b>2) Investimento em 2015: 1 e 11 parcelas.</b>
                        3) Duração do Curso: 1 ano - março / 2015 a dezembro /2015.​
                        4) Carga Horária ano de 2015 = Certificado de Formação - 400H.
                        5)  Curso de Aprofundamento em 2016.
                        6) Certificado de Extensão de Formação em Ciência da Homeopatia - 400h Faculdade Inspirar
                        <b>OBS.: o desconto de 10% para o indicado e indicado, é válido para os Cursos Extensão e pelo Instituto.</b>
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
