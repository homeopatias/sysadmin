<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Visualização de aluno - Homeopatias.com</title>
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

                $textoQuery  = "SELECT idCidade, UF, nome, ano FROM Cidade WHERE
                                CURDATE() < limiteInscricao ORDER BY ano DESC, nome ASC";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                // variável para garantir que inicializaremos o vetor para cada
                // ano sempre que estivermos utilizando-o pela primeira vez
                $anos = [];

                while ($linha = $query->fetch()){
                    // para cada cidade encontrada criamos um objeto no
                    // código javascript para representá-la
                    $id     = "\"".htmlspecialchars($linha["idCidade"])."\"";
                    $uf     = "\"".htmlspecialchars($linha["UF"])."\"";
                    $nome   = "\"".htmlspecialchars($linha["nome"])."\"";
                    $ano    = "\"".htmlspecialchars($linha["ano"])."\"";
                    if(!in_array($linha["ano"], $anos)){
                        $anos[] = $linha["ano"];
            ?>
            
            cidades[ <?= $ano ?> ] = new Array();
            <?php } ?>

            cidades[ <?= $ano ?> ].push({
                id:     <?= $id ?>,
                uf:     <?= $uf ?>,
                nome:   <?= $nome ?>,
                ano:    <?= $ano ?>
            });
            
            <?php
                }
            ?>

            $(document).ready(function(){
                // atualizamos o dropdown de cidades quando o usuário seleciona um ano durante
                // a criação de matrícula
                $("form #ano").change(function(){
                    $("form #cidade").find('option').remove().end();
                    var ano  = $(this).val();

                    var date = new Date();
                    if(cidades[ano]){
                        cidades[ano].forEach(function(cidade){
                            $("form #cidade")
                                .append('<option value="' + cidade.id + '">' + cidade.nome + "/"
                                        + cidade.uf + '</option>')
                        });
                    }
                });

                // abrimos o menu de nova matrícula quando o botão de nova matrícula é pressionado
                $("#renovar-mat").click(function(){
                    $("#form-mat").toggle(500);
                });

                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                });

                $("form #ano").change();                
            });
        </script>
    </head>
    <body>
        <?php
            require_once("entidades/Aluno.php");

            $aluno = unserialize($_SESSION["usuario"]);
            $idAluno = $aluno->getNumeroInscricao();
            include("modulos/navegacao.php");

            $mensagem = "";

            // exibe dados do aluno apenas para alunos logados e que seu id seja o id que está buscando
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Aluno ){

                // caso o usuário tenha chegado aqui através de um formulário, cria a nova
                // matrícula
                if(isset($_POST["submit"])){
                    // inscrição do aluno logado atualmente
                    $id = unserialize($_SESSION["usuario"])->getNumeroInscricao();

                    // validamos os dados recebidos
                    $idCidade = $_POST["cidade"];

                    $idCidadeValido = isset($idCidade) && preg_match("/^\d*$/", $idCidade);

                    $anoValido = true;
                    // checamos se o ano no qual o aluno quer se matricular é permitido

                    // primeiro descobrimos o ano dessa cidade
                    $ano = 0;
                    $textoQuery  = "SELECT ano FROM Cidade WHERE idCidade = ?";

                    $query = $conexao->prepare($textoQuery);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute(array($idCidade));
                    if($linha = $query->fetch()){
                        $ano = intval($linha["ano"]);
                        $anoValido = $ano >= date('Y');
                    }else{
                        // essa cidade não existe
                        $idCidadeValido = false;
                    }

                    // checamos se o aluno já está matrículado no ano recebido
                    $textoQuery  = "SELECT C.idCidade, C.nome FROM Cidade C, Matricula M 
                                    WHERE M.chaveCidade = C.idCidade AND 
                                    M.chaveAluno = ? AND C.ano = ?";

                    $query = $conexao->prepare($textoQuery);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute(array($id, $ano));
                    if($linha = $query->fetch()){
                        $anoValido = false;
                    }

                    // por fim, verificamos se o aluno terminou o período referente à
                    // sua última matrícula, e caso tenha, avalia qual a próxima etapa
                    // na qual ele deve se matricular
                    $textoQuery  = "SELECT M.aprovado, M.etapa
                                    FROM Matricula M, Cidade C 
                                    WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                    ORDER BY M.etapa, C.ano DESC LIMIT 1";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $id, PDO::PARAM_INT);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    $fechado = true;
                    $proximaEtapa = 1;
                    if ($linha = $query->fetch()) {
                        $fechado = !is_null($linha['aprovado']);
                        $proximaEtapa = $linha['aprovado'] ? $linha['etapa'] + 1 : $linha['etapa'];
                    }

                    if($idCidadeValido && $anoValido && $fechado){

                        // Usamos as TRANSACTIONs do MySql para garantir que caso haja
                        // algum erro, as tabelas continuem consistentes
                        $conexao->beginTransaction();

                        $dadosMatricula  = array($id, $proximaEtapa, $idCidade);
                        $queryMatricula  = "INSERT INTO Matricula (chaveAluno, etapa, chaveCidade) 
                                            VALUES (?,?,?)";
                        $query  = $conexao->prepare($queryMatricula);
                        $sucessoMatricula = $query->execute($dadosMatricula);
                        $idUltimaMatricula = $conexao->lastInsertId();

                        // agora fazemos com que o aluno passe a constar como pré-inscrito
                        $queryInscrito  = "UPDATE Aluno SET status = 'preinscrito' 
                                           WHERE numeroInscricao = ?";
                        $query           = $conexao->prepare($queryInscrito);
                        $query->bindParam(1, $id);
                        $sucessoInscrito = $query->execute();
                        $aluno->setStatus('preinscrito');

                        // agora tentamos criar os pagamentos

                        // pega os valores de inscrição e parcelas da cidade
                        $textoQuery = "SELECT C.precoInscricao, C.precoParcela, C.ano
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
                            for($i = 0; $i < 12; $i++){

                                if($i == 0){ // parcela numero 0 será considerada valor da
                                             // inscrição
                                    $queryInsert    = "INSERT INTO `homeopatias`.`PgtoMensalidade` 
                                                    (`chaveMatricula`, `numParcela`, `ValorTotal`, `ValorPago`, 
                                                        `desconto`, `fechado`,`ano`) 
                                                    VALUES (?, '0', ?, '0', '0', '0', ?) ";
                                    $insertArray  = array($idUltimaMatricula, $linha["precoInscricao"], $linha["ano"]);

                                } 
                                else{
                                    $queryInsert    .= " , (?, ?, ?, '0', '0', '0', ?) ";
                                    $insertArray[]  = $idUltimaMatricula;
                                    $insertArray[]  = $i;
                                    $insertArray[]  = $linha["precoParcela"];
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
                            // tudo certo, confirmamos as mudanças
                            $conexao->commit();                            
                        }

                    } else if (!$idCidadeValido) {
                        $mensagem = "Cidade inválida!";
                    } else if (!$anoValido && $ano >= date('Y')) {
                        $mensagem = "Esse aluno já está matriculado nesse ano!";
                    } else if (!$anoValido) {
                        $mensagem = "Você não pode matricular o aluno em um ano anterior!";
                    }
                }
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <?php
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <br>
                    <!-- //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////// SUMÁRIO DO ALUNO ////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         Dados principais do aluno -->
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Aluno:</b>
                            <?= htmlspecialchars($aluno->getNome()); ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Nome de usuário:</b>
                            <?= htmlspecialchars($aluno->getLogin()); ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Número de inscrição:</b>
                            <?= htmlspecialchars($aluno->getNumeroInscricao()) ?>
                        </p>
                        <?php
                            $status = "";
                            if($aluno->getStatus() === "inscrito"){
                                $status = "Inscrito";
                            }else if($aluno->getStatus() === "preinscrito"){
                                $status = "Pré-inscrito";
                            }else if($aluno->getStatus() === "desistente"){
                                $status = "Desistente";
                            }else if($aluno->getStatus() === "formado"){
                                $status = "Formado";
                            }
                        ?>
                        <p style="display:inline" class="col-sm-3">
                            <b>Status:</b>
                            <?= htmlspecialchars($status); ?>
                        </p>
                    </div>
                    <div class="row">

                        <?php
                            $indicador = 
                                $aluno->getIndicador($host, "homeopatias", $usuario, $senhaBD);
                        ?>
                        <p style="display:inline" class="col-sm-3">
                            <b>Indicador:</b>
                            <?= $indicador != null ? htmlspecialchars($indicador->getNome()) : "Nenhum" ?>
                        </p>
                        <?php  

                            //Agora checamos a quantidade de alunos indicados por ele matriculados
                            // neste ano

                            $textoQuery = "SELECT A.numeroInscricao
                                            FROM Aluno A, Matricula M, Cidade C
                                            WHERE A.idIndicador = ? AND 
                                            M.chaveAluno = A.numeroInscricao AND
                                            M.chaveCidade = C.idCidade AND C.ano = YEAR(CURDATE())";

                            $query = $conexao->prepare($textoQuery);

                            $query->setFetchMode(PDO::FETCH_ASSOC);
                            $query->bindParam(1, 
                                        unserialize( $_SESSION["usuario"] )->getNumeroInscricao() );

                            $query->execute();
                            $indicados = $query->rowCount();


                        ?>
                        <p style="display:inline" class="col-sm-4">
                            <b>Alunos indicados e matriculados atualmente:</b>
                            <?= $indicados ?>
                        </p>
                        
                    </div>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Endereço:</b>
                            <?= htmlspecialchars( $aluno->retornaEndereco() ); ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Telefone:</b>
                            <?= "(" . substr(htmlspecialchars($aluno->getTelefone()), 0, 2) . ")" . 
                                      substr(htmlspecialchars($aluno->getTelefone()), 2, 4) . "-" .
                                      substr(htmlspecialchars($aluno->getTelefone()), 6) ?>
                        </p>
                        <?php
                            $escolaridade = $aluno->getEscolaridade();
                            $escolaridade = mb_strpos($escolaridade, "completo", 0, "UTF-8") ?
                                            "Ensino " . $escolaridade :
                                            mb_convert_case($escolaridade, MB_CASE_TITLE, "UTF-8");

                        ?>
                    </div>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Escolaridade:</b>
                            <?= htmlspecialchars($escolaridade); ?>
                        </p>
                        <?php if($aluno->getCurso() != null) { ?>
                        <p style="display:inline" class="col-sm-4">
                            <b>Curso superior efetuado:</b>
                            <?= htmlspecialchars($aluno->getCurso()); ?>
                        </p>
                        <?php } ?>
                    </div>

                    <!-- //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         ////////////////////////// MATRÍCULAS ////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         ////////////////////////////////////////////////////////////////////// -->
                    <?php
                        // descobrimos se o aluno está matriculado atualmente
                        $matriculado = false;
                        $etapa = -1;
                        $idCidade = -1;
                        $idMatricula = -1;
                        $aprovado = null;
                        $numeroInscricao = unserialize( $_SESSION['usuario'] )->getNumeroInscricao();

                        $textoQuery  = "SELECT M.idMatricula, M.etapa, M.chaveCidade, M.aprovado 
                                        FROM Matricula M, Cidade C 
                                        WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                        AND C.ano = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, date('Y'), PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        if($linha = $query->fetch()){
                            // foi encontrada uma matrícula desse aluno no
                            // período atual

                            $etapa = $linha['etapa'];
                            $idCidade = $linha['chaveCidade'];
                            $idMatricula = $linha['idMatricula'];
                            $aprovado = $linha['aprovado'];
                            $matriculado = true;


                            // Agora checamos o desconto do aluno neste ano, para isso veremos os
                            // alunos matriculados no ano atual e que tenham sido indicados por ele

                            $textoQuery = "SELECT A.numeroInscricao
                                           FROM Aluno A, Matricula M, Cidade C
                                           WHERE A.idIndicador = ? AND 
                                           M.chaveAluno = A.numeroInscricao AND
                                           M.chaveCidade = C.idCidade AND C.ano = YEAR(CURDATE())";

                            $query = $conexao->prepare($textoQuery);

                            $query->setFetchMode(PDO::FETCH_ASSOC);
                            $query->bindParam(1, $numeroInscricao );

                            $query->execute();
                            $desconto = $query->rowCount() * 10;

                            if($desconto > 100){
                                $desconto = 100;
                            }
                        }
                    
                    if($matriculado){ ?>

                    <!-- mostramos todos os dados da matricula atual do aluno -->
                    <br>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Matriculado no período atual</b>
                        </p>
                        <p class=<?= "\"" . ($aprovado ? "sucesso" : "warning") . "\"" ?>><b>
                            Aluno <?= $aprovado ? "aprovado" : "reprovado" ?> no ano atual
                        </b></p> 
                        <p style="display:inline" class="col-sm-3">
                            <?php 
                                if( isset($_GET["ano"]) && $_GET["ano"] != date("Y") ){ ?>
    
                                    <a href=  
                                        <?= "visualizar_informacoes_curso.php?id=". $idAluno ?>>
                                        Visualizar pagamentos do ano atual
                                    </a>
                            <?php
                                }
                            ?>
                    </div>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Cidade:</b>
                            <?php
                                require_once("entidades/Cidade.php");
                                $cidade = new Cidade();
                                $cidade->setIdCidade($idCidade);
                                $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);
                                echo $cidade->getNome() . "/" . $cidade->getUF();
                            ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Etapa:</b>
                            <?= $etapa ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Desconto no período atual:</b>
                            <?= $desconto."%" ?>
                        </p>
                    </div>

                    <?php }else{ ?>

                    <br>
                    <div class="row">
                        <b class="col-sm-3">Não-matriculado no período atual</b>
                    </div>
                    <?php } 

                        // descobrimos se o aluno já terminou a parte do
                        // curso referente à sua última matrícula (passando ou não)
                        $textoQuery  = "SELECT M.aprovado, M.etapa
                                        FROM Matricula M, Cidade C 
                                        WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                        ORDER BY C.ano DESC, M.etapa DESC LIMIT 1";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $fechado = true;
                        $proximaEtapa = 1;
                        if ($linha = $query->fetch()) {
                            $fechado = !is_null($linha['aprovado']);
                            $proximaEtapa = $linha['aprovado'] ? $linha['etapa'] + 1 : $linha['etapa'];
                        }

                        // damos a opção de rematrícula caso o aluno
                        // não esteja matriculado no ano seguinte e já tenha concluído
                        // a parte do curso referente à sua úiltima matrícula

                        $anoAtual = date("Y"); // ano atual para uso na nova matrícula 
                        if($fechado &&
                            $aluno->getStatus() !== "desistente" &&
                            $aluno->getStatus() !== "formado"){
                    ?>
                    <div class="row">
                        <a style="cursor: pointer" class="col-sm-2" id="renovar-mat">
                            Renovar matrícula
                        </a>
                    </div>
                    <div class="row">
                        <!-- formulário de nova matrícula -->
                        <form class="form-inline col-sm-12" style="display: none"
                              id="form-mat" action method="POST">
                            <br>
                            <div class="form-group" style="margin-left: 20px">
                                <label for="ano">Ano:</label>
                                <select name="ano" id="ano" class="form-control" required>
                                    <?php if(in_array($anoAtual + 1, $anos)) { ?>
                                    <option value=<?= $anoAtual + 1 ?> >
                                        <?= $anoAtual + 1 ?>
                                    </option>
                                    <?php
                                        }
                                        if(in_array($anoAtual, $anos)) {
                                    ?>
                                    <option value=<?= $anoAtual ?> >
                                        <?= $anoAtual ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin-left: 20px">
                                <label for="cidade">Cidade:</label>
                                <select name="cidade" id="cidade"
                                        class="form-control" required>
                                    <option value="">Escolha um ano ao lado...</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-left: 20px">
                                <label><?= $proximaEtapa ?>ª etapa</label>
                            </div>
                            <button type="submit" name="submit" value="submit"
                                    class="btn btn-primary pull-right">
                                Efetuar Matrícula
                            </button>
                        </form>
                    </div>

                    <?php
                        }

                        // agora checamos se esse aluno possui matrículas futuras, e se possuir,
                        // as listamos

                        // essa variável determina se foram encontradas matrículas futuras ou não
                        $futuras = false;

                        $textoQuery  = "SELECT M.etapa, M.idMatricula, C.ano, C.nome 
                                        FROM Matricula M, Cidade C 
                                        WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                        AND C.ano > ? ORDER BY C.ano DESC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, date("Y"), PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $tabela = "";

                        while($linha = $query->fetch()){
                            if(!$futuras) $futuras = true;
                            // listamos as matrículas futuras

                            $tabela .= "<tr>";
                            $tabela .= "    <td>". htmlspecialchars($linha["ano"])."</td>";
                            $tabela .= "    <td>". htmlspecialchars($linha["etapa"])."</td>";
                            $tabela .= "    <td>". htmlspecialchars($linha["nome"])."</td>";
                            $tabela .= "<td><a data-href=\"rotinas/matricula/";
                            $tabela .= "remover_matricula.php?id=";
                            $tabela .= $linha["idMatricula"] . "&aluno=" . $idAluno;
                            $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                            $tabela .= " data-target=\"#modal-confirma-deleta\">";
                            $tabela .= "<i class=\"fa fa-trash-o\"></i></a></td>";
                            $tabela .= "</tr>";
                        }

                        if($futuras){
                    ?>
                    <h3>Pré-matrículas</h3>
                    <table class="table table-bordered table-striped" id="alunos">
                        <thead style="background-color: #AAA">
                            <tr>
                                <th>Ano</th>
                                <th>Etapa</th>
                                <th>Cidade</th>
                                <th>Cancelar matrícula</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $tabela ?>
                        </tbody>
                    </table>
                    <?php
                        }

                        // agora checamos se esse aluno possui matrículas anteriores, e se possuir,
                        // as listamos

                        // essa variável determina se foram encontradas matrículas anteriores ou não
                        $anteriores = false;

                        $textoQuery  = "SELECT M.aprovado, M.etapa, C.ano, C.nome 
                                        FROM Matricula M, Cidade C 
                                        WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                        AND C.ano < ? ORDER BY C.ano DESC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, date("Y"), PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $tabela = "";

                        $anosMatriculados = [];

                        while($linha = $query->fetch()){
                            if(!$anteriores) $anteriores = true;
                            // listamos as matrículas anteriores

                            $tabela .= "<tr>";
                            $tabela .= "    <td>". htmlspecialchars($linha["ano"])."</td>";
                            $tabela .= "    <td>". htmlspecialchars($linha["etapa"])."</td>";
                            $tabela .= "    <td>". htmlspecialchars($linha["nome"])."</td>";
                            $tabela .= "    <td>";
                            if(is_null($linha["aprovado"])){
                                $tabela .= "<i class=\"fa fa-ellipsis-h\"></i>";
                            }else{
                                $tabela .= htmlspecialchars($linha["aprovado"]) ?
                                           "<i class=\"fa fa-check sucesso\"></i>"  :
                                           "<i class=\"fa fa-times warning\"></i>";
                            }
                            $tabela .= "</td>";
                            $tabela .= "    <td><a href=\"visualizar_informacoes_curso.php?ano=".
                                $linha["ano"]."\" >
                                        <i class=\"fa fa-money\"></i>
                                        </a></td>";
                            $tabela .= "</tr>";

                        }

                        if($anteriores){
                    ?>
                    <h3>Matrículas em períodos anteriores</h3>
                    <table class="table table-bordered table-striped" id="alunos">
                        <thead style="background-color: #AAA">
                            <tr>
                                <th>Ano</th>
                                <th>Etapa</th>
                                <th>Cidade</th>
                                <th>Aprovado?</th>
                                <th style="width:50px">Visualizar Pagamentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $tabela ?>
                        </tbody>
                    </table>
                    <?php
                        }
                    ?>

                    <!-- //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         ////////////////////////// PAGAMENTOS ////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         Pagamentos efetuados e pendentes desse aluno -->
                    <?php

                        // procuramos os pagamentos do ano enviado, tanto pendentes
                        // como efetuados

                        $anoPagamento = date("Y");
                        if( isset($_GET["ano"]) ){
                            $anoPagamento = $_GET["ano"];
                        }

                        $textoQuery  = "SELECT P.valorPago, P.valorTotal, P.data, P.desconto,
                                        P.ano, P.numParcela FROM Matricula M, PgtoMensalidade P
                                        WHERE M.chaveAluno = ?
                                        AND P.chaveMatricula = M.idMatricula
                                        AND P.ano = ?
                                        ORDER BY P.data DESC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, $anoPagamento, PDO::PARAM_STR);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $pagamentos = array();
                        while($linha = $query->fetch()){
                            $anoPag = $linha['ano'];
                            $numParcela = $linha['numParcela'];
                            $pagamentos[$anoPag][$numParcela]['valor'] = $linha['valorTotal'];
                            $pagamentos[$anoPag][$numParcela]['pago']  = $linha['valorPago'];
                            $pagamentos[$anoPag][$numParcela]['data']  = $linha['data'];
                            $pagamentos[$anoPag][$numParcela]['desconto']  = $linha['desconto'];
                        }

                        if($query->rowCount() != 0) {
                    ?>

                    <?php if($anoPagamento == date("Y")){ ?>

                        <h3>Pagamentos do ano atual</h3>

                    <?php }else{ ?>

                        <h3>Pagamentos do ano de <?= $anoPagamento ?></h3>
                        
                    <?php } ?>

                    <table class="table table-bordered table-striped" id="alunos">
                        <thead style="background-color: #AAA">
                            <tr>
                                <th width="20px" style="background-color: #777"></th>
                                <th>Inscrição</th>
                    <?php
                        for($i = 1; $i < 12; $i++) {
                            echo "<th>$i</th>";
                        }
                    ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style='background-color: #AAA'><b>Valor a pagar</b></td>
                    <?php
                        for($i = 0; $i < 12; $i ++) {
                            echo "<td>R$ " . 
                                 number_format($pagamentos[$anoPagamento][$i]['valor'], 2)
                                 . "</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Valor pago</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            echo "<td>R$ " .
                                 number_format($pagamentos[$anoPagamento][$i]['pago'], 2)
                                 . "</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Data do pagamento</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            $data = $pagamentos[$anoPagamento][$i]['data'];
                            $data =  $data ? date("d/m/Y", strtotime($data)) : 'N/A';
                            echo "<td>" . $data . "</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Desconto</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            echo "<td>" .
                                 number_format($pagamentos[$anoPagamento][$i]['desconto'], 2)
                                 . "%</td>";
                        }
                    ?>
                            </tr>
                        </tbody>
                    </table>

                    <?php
                        }

                        // fechamos a conexão
                        $conexao = null;
                    ?>

                </section>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para confirmação de cancelamento de matrícula -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Cancelamento de matrícula</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja cancelar essa matrícula?</h3>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                        <a href="#" class="btn btn-danger danger">Sim</a>
                    </div>
                </div>
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