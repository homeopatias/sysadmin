<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
	session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Bem-vindo - Homeopatias.com</title>
        <script>
            $(document).ready(function() {
                $("#escolaridade-novo").change(function(){
                    if($(this).val() === "superior incompleto" || $(this).val() === "superior completo"   ||
                       $(this).val() === "mestrado"            || $(this).val() === "doutorado" ){
                        $("#curso-novo").parent().show(500);
                    }else{
                        $("#curso-novo").parent().hide(500);
                    }
                });
            });
        </script>
    </head>
    <body>
        <?php
            require_once("entidades/Aluno.php");

        	// mensagem a ser exibida acima do formulário, caso seja necessário
        	$mensagem = "";

            if(isset($_GET["erro"])) {
                $mensagem = $_GET["erro"];
            }

            // mostra essa tela apenas para alunos logados
            if(isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Aluno) {

                // lemos as credenciais do banco de dados
                $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                $dados = json_decode($dados, true);

                foreach($dados as $chave => $valor) {
                    $dados[$chave] = str_rot13($valor);
                }

                // cria conexão com o banco para uso ao longo da página
                $conexao   = null;
                $host      = $dados["host"];
                $usuario   = $dados["nome_usuario"];
                $senhaBD   = $dados["senha"];
                $db      = "homeopatias";
                try {
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }

                // se o usuario chegou aqui atraves de um formulário, tentamos registrar os dados
                if (isset($_POST["submit"])){

                    $cpf            = $_POST["cpf"];
                    $loginIndicador = $_POST["indicador"];
                    $telefone       = $_POST["telefone"];
                    $cep            = $_POST["cep"];
                    $rua            = $_POST["rua"];
                    $numero         = $_POST["numero"];
                    $complemento    = $_POST["complemento"];
                    $bairro         = $_POST["bairro"];
                    $cidade         = $_POST["cidade"];
                    $estado         = $_POST["estado"];
                    $idCidadeMat    = $_POST["cidadeMat"];
                    // Até o momento, não será necessaria a documentação para pós-graduação
                    // if(unserialize($_SESSION['usuario'])->getTipoCurso() === "extensao") {                  
                        $escolaridade   = $_POST["escolaridade"];
                        $curso          = $_POST["curso"];
                    // }

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

                    // Até o momento, não será necessaria a documentação para pós-graduação
                    $escolaridadeValida = //unserialize($_SESSION['usuario'])->getTipoCurso() === "pos" ||
                                (isset($escolaridade) &&
                                   ($escolaridade === "fundamental incompleto" ||
                                    $escolaridade === "fundamental completo"   ||
                                    $escolaridade === "médio incompleto"       ||
                                    $escolaridade === "médio completo"         ||
                                    $escolaridade === "superior incompleto"    ||
                                    $escolaridade === "superior completo"      ||
                                    $escolaridade === "mestrado"               ||
                                    $escolaridade === "doutorado")
                                );

                    // para permitir a validação do curso, conferimos se possui curso superior
                    $superior = //unserialize($_SESSION['usuario'])->getTipoCurso() === "pos" ||
                                ($escolaridade === "superior incompleto"    ||
                                 $escolaridade === "superior completo"      ||
                                 $escolaridade === "mestrado"               ||
                                 $escolaridade === "doutorado");
                    $cursoValido = //unserialize($_SESSION['usuario'])->getTipoCurso() === "pos" ||
                                   ((!isset($curso) || $curso === "") && !$superior) ||

                                   (isset($curso) && mb_strlen($curso) > 0 && mb_strlen($curso) <= 200);

                    // verificamos se a cidade na qual o aluno quer se matricular
                    // é válida
                    $cidadeMatValida = isset($idCidadeMat) && preg_match("/^\d*$/", $idCidadeMat);

                    if($cidadeMatValida) {
                        $textoQuery  = "SELECT idCidade FROM Cidade
                                        WHERE idCidade = ? AND ano = YEAR(CURDATE())";

                        $query = $conexao->prepare($textoQuery);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute(array($idCidadeMat));
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

                    if($cpfValido && $loginIndicadorValido && $telefoneValido && $enderecoValido &&
                       $escolaridadeValida && $cursoValido && $cidadeMatValida){

                        $aluno = unserialize($_SESSION['usuario']);

                        $aluno->setCpf($cpf);
                        $aluno->setTelefone($telefone);
                        $aluno->setCep($cep);
                        $aluno->setRua($rua);
                        $aluno->setNumero($numero);
                        $aluno->setComplemento($complemento);
                        $aluno->setBairro($bairro);
                        $aluno->setCidade($cidade);
                        $aluno->setEstado($estado);
                        $aluno->setIdIndicador($idIndicador);

                        // if(unserialize($_SESSION['usuario'])->getTipoCurso() === "extensao") {
                            $aluno->setEscolaridade($escolaridade);
                            if($escolaridade === "superior incompleto" || $escolaridade === "superior completo"   ||
                               $escolaridade === "mestrado"            || $escolaridade === "doutorado" ){
                                $aluno->setCurso(isset($curso) ? $curso : null);
                            }else{
                                $aluno->setCurso(null);
                            }

                        // }

                        $sucesso = $aluno->atualizar($host, $db, $usuario, $senhaBD);

                        if(!$sucesso) {
                            $mensagem = "Erro na inserção de dados";
                        } else {
                            // agora fazemos a matrícula do aluno

                            // Usamos as TRANSACTIONs do MySql para garantir que caso haja
                            // algum erro, as tabelas continuem consistentes
                            $conexao->beginTransaction();

                            $idAluno = $aluno->getNumeroInscricao();

                            $dadosMatricula  = array($idAluno, $idCidadeMat);
                            $queryMatricula  = "INSERT INTO Matricula (chaveAluno, etapa, chaveCidade) 
                                                VALUES (?,1,?)";
                            $query  = $conexao->prepare($queryMatricula);
                            $sucessoMatricula = $query->execute($dadosMatricula);
                            $idUltimaMatricula = $conexao->lastInsertId();

                            // agora fazemos com que o aluno passe a constar como pré-inscrito
                            $queryInscrito  = "UPDATE Aluno SET status = 'preinscrito' 
                                               WHERE numeroInscricao = ?";
                            $query           = $conexao->prepare($queryInscrito);
                            $query->bindParam(1, $idAluno);
                            $sucessoInscrito = $query->execute();
                            $aluno->setStatus('preinscrito');

                            // agora tentamos criar os pagamentos

                            // pega os valores de inscrição e parcelas da cidade
                            $textoQuery = "SELECT C.nome, C.idCidade,C.ano, C.v_inscricao_extensao, C.v_parcela_extensao,
                                                  C.v_inscricao_pos, C.v_parcela_pos
                                           FROM Cidade C, Matricula M
                                           WHERE C.idCidade = M.chaveCidade AND
                                           M.idMatricula = ?";

                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(1,$idUltimaMatricula);
                            $query->setFetchMode(PDO::FETCH_ASSOC);
                            $query->execute();

                            
                            $queryInsert = "";
                            $insertArray = [];

                            $sucessoPgto = false;

                            if($linha = $query->fetch()){

                                if($aluno->getTipoCurso() == "extensao"){
                                    $precoInscricao = $linha["v_inscricao_extensao"];
                                    $precoParcela = $linha["v_parcela_extensao"];
                                }else{
                                    $precoInscricao = $linha["v_inscricao_pos"];
                                    $precoParcela = $linha["v_parcela_pos"];
                                }
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
                                // a cidade não foi encontrada, cancela
                                $conexao->rollBack();
                                $mensagem = "Cidade não encontrada";
                            }

                            if(!$sucessoMatricula) {
                                // erro na matrícula, desfazemos as mudanças
                                $conexao->rollBack();
                                $mensagem = "Erro na matrícula";
                            } else if(!$sucessoInscrito) {
                                // erro na mudança para inscrito, desfazemos as mudanças
                                $conexao->rollBack();
                                $mensagem = "Erro na atualização de status de aluno após matrícula";
                            } else if(!$sucessoPgto) {
                                // erro na criação dos pagamentos, desfazemos as mudanças
                                $conexao->rollBack();
                                $mensagem = "Erro na criação dos pagamentos do ano";
                            } else {
                                // tudo certo, inscrevemos o aluno no Moodle e confirmamos as mudanças

                                $usuarioMoodle = $dados["usuario_moodle"];
                                $senhaMoodle   = $dados["senha_moodle"];

                                $sucessoMoodle = false;

                                $conMoodle = null;
                                try{
                                    $conMoodle = new PDO("mysql:host=$host;dbname=moodle;charset=utf8",
                                                         $usuarioMoodle, $senhaMoodle);

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
                                                         timemodified) VALUES (0,";
                                        $queryMoodle .= ($aluno->getTipoCurso() === "pos" ? "4" : "1");
                                        $queryMoodle .= ",?,NOW(),NOW())";


                                        $query = $conMoodle->prepare($queryMoodle);
                                        $query->bindParam(1, $idUsuarioMoodle);
                                        $sucessoMoodle = $query->execute();

                                        if($sucessoMoodle) {
                                            $queryMoodle = "INSERT INTO mdl_role_assignments
                                                            (roleid,contextid,userid,timemodified)
                                                            VALUES (5,";
                                            $queryMoodle .= ($aluno->getTipoCurso() === "pos" ? "26" : "18");
                                            $queryMoodle .= ",?,NOW())";


                                            $query = $conMoodle->prepare($queryMoodle);
                                            $query->bindParam(1, $idUsuarioMoodle);
                                            $sucessoMoodle = $query->execute();
                                        }
                                    } else {
                                        $sucessoMoodle = false;
                                    }

                                }catch (PDOException $e){
                                    // echo $e->getMessage();
                                }

                                $mensagem = "";
                                if(!$sucessoMoodle){
                                    $mensagem = "O registro foi efetuado, porém não foi possível registrar no Moodle";
                                }

                                $_SESSION["usuario"] = serialize($aluno);
                                $conexao->commit();
                ?>
                <!-- redireciona o usuário para o index -->
                <meta http-equiv="refresh" content=<?= '"0; url=index.php?mensagem='.$mensagem.'"' ?>>
                <script type="text/javascript">
                    window.location = <?= '"index.php?mensagem='.$mensagem.'"'?>;
                </script>
                <?php
                            }
                        }
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
                    } 
                }

                $aluno = unserialize($_SESSION['usuario']);
                // listamos as cidades em que o aluno pode se matricular
                // o aluno só pode entrar em cidades do ano atual
                $textoQuery  = "SELECT idCidade, UF, nome FROM Cidade WHERE
                CURDATE() < limiteInscricao AND ano = YEAR(CURDATE()) AND cadastro_ativo = 1 AND 
                        (tipo_curso = '" .$aluno->getTipoCurso(). "' OR tipo_curso = 'ambos')
                        ORDER BY nome ASC";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $cidades = array();

                if(!($query->rowCount())) {
                    // caso não hajam cidades abertas para inscrição, avisa o aluno
                    session_destroy();
        ?>
        <meta http-equiv="refresh" content=<?= '"0; url=finalizar_cadastro.php"' ?>>
        <script type="text/javascript">
            window.location = "index.php?mensagem=No momento não há nenhuma cidade aberta para matrícula." +
                              " Desculpe-nos o transtorno, em breve as inscrições serão abertas novamente." +
                              " Agradecemos o interesse!";
        </script>
        <?php
                    die();
                }

                while ($linha = $query->fetch()){
                    $id     = htmlspecialchars($linha["idCidade"]);
                    $uf     = htmlspecialchars($linha["UF"]);
                    $nome   = htmlspecialchars($linha["nome"]);
                    $cidades[] = array("nome" => $nome . "/" . $uf, "id" => $id);
                }

        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0) {
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a class="pull-right btn btn-danger" href="rotinas/logout.php" style="position:relative; top: 10px">
                        Sair do sistema
                    </a>
                    <h4>Para acessar o sistema, você primeiro deve preencher os dados restantes do
                    seu registro:</h4>
                    <br>
                    <form action method="POST">
                        <div class="form-group">
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
                        </div>
                        <br><br>
                        <div class="form-group">
                            <label for="cpf-novo">CPF:</label>
                            <input type="text" name="cpf" id="cpf-novo" required
                                   pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                   placeholder="xxx.xxx.xxx-xx" class="form-control">
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
                        <?php // if(unserialize($_SESSION['usuario'])->getTipoCurso() === "extensao") { ?>

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
                        <?php // } ?>

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
                        <button type="submit" name="submit" value="submit"
                                class="btn btn-success">Terminar cadastro</button>
                    </form>
                    <br>
                </section>
            </div>
        </div>

        <?php
            }else{
        ?>

        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="0; url=index.php">
        <script type="text/javascript">
            window.location = "index.php";
        </script>

        <?php
                die();
            }

            include("modulos/rodape.php");
        ?>
    </body>
</html>
