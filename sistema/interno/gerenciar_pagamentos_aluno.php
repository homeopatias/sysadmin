<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <script src="./webshim-1.14.5/polyfiller.js"></script>

        <script type="text/javascript">
            // usamos um polyfill para que os campos de data e hora funcionem mesmo
            // em navegadores que não implementem essas funcionalidades

            webshims.activeLang("pt-BR");
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date', replaceUI: true});
            webshims.polyfill('forms forms-ext');
        </script>

        <title>Lista de chamada - Homeopatias.com</title>
        <script>
            $(document).ready(function(){
                // pequeno script para que o envio do formulário de ano seja feito assim
                // que a cidade ou a etapa for mudada
                $("#cidade").change( function(){ $(this).parent().submit() });
                $("#etapa").change( function(){ $(this).parent().submit() });

                $("#ano").change(function(){
                    $("#busca-ano").submit();
                });
            });
        </script>

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

            ?>
    </head>
    <body>
        <?php
            include("modulos/navegacao.php");

            // Checa se foi passado um aluno corretamente, caso contrário, retorna a lista de alunos
            require_once("entidades/Aluno.php");

            $idAluno = $_GET["id"];
            if(!isset($idAluno) || !preg_match("/^[0-9]*$/", $idAluno)){
                // o id passado foi inválido
                // redirecionamos o usuário para a página de gerenciamento de alunos
                // com uma mensagem de erro
            ?>
                <!-- redireciona o usuário -->
                <meta http-equiv="refresh" content="0; url=busca_alunos.php?erro=Dados inválidos!">
                <script type="text/javascript">
                    window.location = "busca_alunos.php?erro=Dados inválidos!";
                </script>
            <?php
                die();
            }

            // procuramos o aluno desejado no banco de dados
            $aluno = new Aluno("");
            $aluno->setNumeroInscricao($idAluno);
            $sucesso = $aluno->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);
            if(!$sucesso){
                // o id passado não representa um aluno no sistema
                // redirecionamos o usuário para a página de gerenciamento de alunos
                // com uma mensagem de erro
            ?>
    
            <!-- redireciona o usuário -->
            <meta http-equiv="refresh" content="0; url=busca_alunos.php?erro=Dados inválidos!">
            <script type="text/javascript">
                window.location = "busca_alunos.php?erro=Dados inválidos!";
            </script>
    
            <?php
                die();
            }

            $mensagem = "";

            // exibe pagamentos e lançamento de pagamentos de alunos apenas para Coordenadores logados 
            if(isset($_SESSION["usuario"]) &&
               unserialize($_SESSION["usuario"]) instanceof Administrador &&
               unserialize($_SESSION["usuario"])->getNivelAdmin() === "coordenador"){

                ?>

        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    
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
                    </div>

                    <?php
                        //Lê os anos das mariculas que o usuário possui para permitir selecionar o ano a ser
                            //exibido
    
                            $textoQuery = "SELECT C.ano 
                                           FROM Cidade C, Aluno A, Matricula M
                                           WHERE C.idCidade = M.chaveCidade 
                                           AND M.chaveAluno =     :chaveAluno AND
                                              C.ano <= YEAR(CURDATE()) 
                                           ORDER BY C.ano desc";
    
                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(":chaveAluno",
                                              $idAluno);
                            $query->setFetchMode(PDO::FETCH_ASSOC);
                            $query->execute();
    
                            $anos = [];
    
                            $matriculas = $query->rowCount();
                            $select = "";

                            $ano = date("Y");
                            if( isset($_GET["ano"]) ){
                                $ano = $_GET["ano"];
                            }
                            if($matriculas){
                                $select = "<select id='ano' name='ano'
                                            class='form-control input-sm'
                                            style='display:inline;
                                                   width: 100px !important;
                                                   margin-left: 10px;'>";
            
                                while($linha = $query->fetch()){
                                    if(!in_array($linha["ano"], $anos)){
                                        $select .= "<option value=\"".$linha["ano"]."\"";
                                        $select .= $ano == $linha["ano"] ? "selected = selected" : "";
                                        $select .= "> ".$linha["ano"]."</option>";
                                       
                                       $anos[] = $linha["ano"];
                                   }
                               }
    
                                $select .= "</select>";
    
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
                        // procuramos os pagamentos desse ano, tanto pendentes
                        // como efetuados

                        $anoPagamento = date("Y");
                        if( isset($_GET["ano"]) ){
                            $anoPagamento = $_GET["ano"];
                        }

                        $textoQuery  = "SELECT P.idPagMensalidade, P.valorPago, P.valorTotal, P.data, P.desconto, P.metodo, P.ano, P.numParcela ,P.fechado 
                                        FROM Matricula M, PgtoMensalidade P
                                        WHERE M.chaveAluno = ?
                                            AND P.chaveMatricula = M.idMatricula
                                            AND P.ano = ?
                                        ORDER BY P.data DESC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, $anoPagamento, PDO::PARAM_STR);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $anos = array();
                        $pagamentos = array();
                        while($linha = $query->fetch()){
                            $anoPag = $linha['ano'];

                            //Inicia a divida para o ano atual caso não tenha sido inicia ainda
                            if(!in_array($anoPag, $anos)){
                                $anos[] = $anoPag;
                                $pagamentos[$anoPag]['divida'] = 0;
                            }
                            $numParcela = $linha['numParcela'];
                            $pagamentos[$anoPag][$numParcela]['id'] = $linha['idPagMensalidade'];
                            $pagamentos[$anoPag][$numParcela]['valor'] = $linha['valorTotal'];
                            $pagamentos[$anoPag][$numParcela]['pago']   = $linha['valorPago'];
                            $pagamentos[$anoPag][$numParcela]['data']  = $linha['data'];
                            $pagamentos[$anoPag][$numParcela]['fechado']  = $linha['fechado'];
                            $pagamentos[$anoPag][$numParcela]['desconto']  = $linha['desconto'];
                            $pagamentos[$anoPag][$numParcela]['metodo'] = $linha['metodo'];
                            $pagamentos[$anoPag][$numParcela]['editado'] = 0;

                            
                            if(!$pagamentos[$anoPag][$numParcela]['fechado']){
                                $pagamentos[$anoPag]['divida'] += $linha['valorTotal'] -
                                ( ($linha['valorTotal'] * ($linha['desconto']/100) )
                                    - $linha['valorPago']);
                            }
                        }

                        //se recebeu um pagamento via POST, efetua o pagamento
                        if( isset($_POST["submit"]) ){
                            $valor = (int) $_POST["valor-pagamento"];
                            $valorValido = ($valor <= $pagamentos[$anoPagamento]['divida'] && $valor > 0) ? 1 : 0;

                            $metodo = $_POST["metodo-pagamento"];
                            $metodoValido =  ( isset($metodo) && 
                                                mb_strlen($metodo, 'UTF-8') >= 3 && 
                                                mb_strlen($metodo, 'UTF-8') <= 200
                                             );
                            
                            if($valorValido && $metodoValido){
                                $pagamentos[$anoPagamento]['divida'] = 0;
                                for ($i = 0 ; $i < 12 && $valor > 0; $i++) {
                                    if(!$pagamentos[$anoPagamento][$i]['fechado']){
                                        // Valor é o que sobrar do pagamento, ja que ele pode terminar de pagar, e caso não feche o pagamento, retornará um valor negativo

                                        $pagamentos[$anoPagamento][$i]['pago'] += $valor;

                                        $valor = $pagamentos[$anoPagamento][$i]['pago']  - $pagamentos[$anoPagamento][$i]['valor'] ;
                                        $pagamentos[$anoPagamento][$i]['editado'] = 1;
                                        //Se o valor retornar > 0, o pagamento foi suficiente para fechar a parcela
                                        if($valor > 0 || 
                                            $pagamentos[$anoPagamento][$i]['pago'] == 
                                            $pagamentos[$anoPagamento][$i]['valor']){

                                            $pagamentos[$anoPagamento][$i]['pago']  =    $pagamentos[$anoPagamento][$i]['valor'];

                                            //se o pagamento foi suficiente para pagar o restante da parcela, fecha a parcela
                                            $pagamentos[$anoPagamento][$i]['fechado'] = "1";
                                        }
                                    }
                                    if(!$pagamentos[$anoPagamento][$i]['fechado']){
                                    $pagamentos[$anoPagamento]['divida'] +=
                                        ($pagamentos[$anoPagamento][$i]['valor'] -
                                         (
                                            //desconto
                                            $pagamentos[$anoPagamento][$i]['valor'] *
                                            $pagamentos[$anoPag][$numParcela]['desconto'] /100
                                         )
                                        )
                                        - $pagamentos[$anoPagamento][$i]['pago'];
                            }
                                }   
                                $conexao->beginTransaction();
                                $sucesso = 1;
                                for ($i = 0 ; $i < 12 && $sucesso ; $i++) {

                                    if( $pagamentos[$anoPagamento][$i]['editado'] ){
                                        $textoQuery = "UPDATE PgtoMensalidade 
                                                    SET valorTotal = ?, valorPago = ?,
                                                    fechado = ? , data = CURDATE(), metodo = ?
                                                    WHERE idPagMensalidade = ?";

                                        $metodosList= array();
                                        if(strrpos($pagamentos[$anoPagamento][$i]['metodo'], "|") ){

                                            $metodosList = explode("|", 
                                            strtolower($pagamentos[$anoPagamento][$i]['metodo']));
                                        }else{
                                            $metodosList = array( 
                                                strtolower( 
                                                    $pagamentos[$anoPagamento][$i]['metodo'])
                                                    );
                                        }

                                        
                                        //Se o método passado não está na lista de métodos , adiciona ele
                                        if(!in_array(strtolower($metodo), $metodosList ) ){
                                            $metodosList[] = $metodo;
                                        }

                                        $metodoUpdate = "";
                                        //Separa os métodos por '|' no bd
                                        foreach ($metodosList as $metodo) {
                                            $metodo = ucfirst($metodo);
                                            if(strlen($metodoUpdate) == 0){
                                                $metodoUpdate = $metodo;
                                            }
                                            else{
                                                $append = "|".$metodo;
                                                $metodoUpdate = $metodoUpdate.$append; 
                                            }

                                        }
                                        $pagamentos[$anoPagamento][$i]['metodo'] = $metodoUpdate;

                                        $queryArray = array(
                                            $pagamentos[$anoPagamento][$i]['valor'],
                                            $pagamentos[$anoPagamento][$i]['pago'],
                                            $pagamentos[$anoPagamento][$i]['fechado'],
                                            $pagamentos[$anoPagamento][$i]['metodo'],
                                            $pagamentos[$anoPagamento][$i]['id'],
                                            );
                                        $query = $conexao->prepare($textoQuery);
                                        $sucesso = $query->execute($queryArray);

                                    }

                                }
                                // se conseguiu lançar o pagamento da inscrição do ano 
                                // atual e
                                // ela fechou, muda o status do aluno para inscrito
                                if($sucesso && $pagamentos[date("Y")][0]['editado']){
                                    
                                    if($pagamentos[date("Y")][0]['fechado']){
                                        
                                        $textoQueryUpdate = "UPDATE Aluno 
                                                             SET status = 'inscrito'
                                                             WHERE numeroInscricao = ?";
                                        
                                        $query = $conexao->prepare($textoQueryUpdate);
                                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                                        $sucesso = $query->execute();
                                    }

                                }
                                //se todos os pagamentos foram atualizados confirma a 
                                // atualização , se não, da rollback
                                if($sucesso){
                                    // enviamos um email confirmando o envio do pagamento
                                    $quantiaPaga = htmlspecialchars($_POST["valor-pagamento"]);
                                    $quantiaPaga = number_format($quantiaPaga, 2);
                                    $assunto = "Homeopatias.com - Pagamento recebido - " . date("d/m/Y");
                                    $msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la.</b>";
                                    $msg .= "<br><br><b>Pagamento recebido:</b><br><b>Valor:</b> R$" . $quantiaPaga;
                                    $msg .= "<br><b>Data:</b> " . date("d/m/Y") . "<br><b>Horário:</b> " . date("H:i");
                                    $msg .= "<br><b>Método:</b> " . $metodo;
                                    $msg .= "<br><br>Obrigado,<br>Equipe Homeobrás.";
                                    $headers = "Content-type: text/html; charset=utf-8 " .
                                        "From: Sistema Financeiro Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
                                        "Reply-To: noreply@homeopatias.com" . "\r\n" .
                                        "X-Mailer: PHP/" . phpversion();

                                    mail($aluno->getEmail(), $assunto, $msg, $headers);

                                    // agora registramos no sistema uma notificação para o aluno
                                    $texto .= "Pagamento recebido:\nValor: R$" . $quantiaPaga;
                                    $texto .= "\nData: " . date("d/m/Y") . "\nHorário: " . date("H:i");
                                    $texto .= "\nMétodo: " . $metodo;
                                    $queryNotificacao = $conexao->prepare("INSERT INTO Notificacao 
                                                        (titulo, texto, chaveAluno, lida) VALUES (?, ?, ?, 0)");
                                    $dados = array("Pagamento recebido", $texto, $idAluno);
                                    $queryNotificacao->execute($dados);

                                    $conexao->commit(); 
                                }
                                else{
                                    $conexao->rollback();
                                }
                            }

                            if(!$valorValido){
                                $mensagem = "Valor de pagamento inválido";
                            }else if(!$metodoValido){
                                $mensagem = "Método de pagamento inválido";
                            }else if(!$sucesso){
                                $mensagem = "Erro: falha ao atualizar pagamentos";
                            }
                            
                        }

                        echo "<form id='busca-ano' name='busca-ano' 
                                    action='gerenciar_pagamentos_aluno.php' method='GET'>
                                    Ano letivo:".$select."
                                    <input type='hidden' name='id' id='id' value='".$idAluno."'>
                                    </form>
                                    <br>";
                        if($query->rowCount() != 0) {
                    ?>

                    <?php if($anoPagamento == date("Y")){ ?>

                        <h3>Pagamentos do ano atual</h3>

                    <?php }else{ ?>

                        <h3>Pagamentos do ano de <?= $anoPagamento ?></h3>

                    <?php } 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                     ?>

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
                        echo "<td style='background-color: #AAA'><b>Data Pagamento</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            $data = $pagamentos[$anoPagamento][$i]['data'];
                            $data =  $data ? date("d/m/Y", strtotime($data)) : 'N/A';
                            echo "<td>" . $data . "</td>";
                        }
                        echo "</tr><tr>";

                        echo "<td style='background-color: #AAA'><b>Finalizada?</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            if($pagamentos[$anoPag][$i]['fechado'] === "1"){
                                echo "<td><i class=\"fa fa-check-square-o sucesso\"></i></td>";
                            }
                            else{
                                echo "<td><i class=\"fa fa-minus-square-o warning\"></i></td>";
                            }
                            
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Desconto</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            echo "<td>" .
                                 number_format($pagamentos[$anoPagamento][$i]['desconto'], 2)
                                 . "%</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Metodo(s)</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            $metodo = $pagamentos[$anoPagamento][$i]['metodo'];
                            echo "<td> ";

                            echo $metodo
                                 ? htmlspecialchars(str_replace("|", " , ", $metodo))
                                 : "N/A";
                            echo "</td>";
                        }
                    ?>
                            </tr>
                        </tbody>
                    </table>
                    <a href="#" class="btn btn-info pull-right" data-toggle="modal"
                       data-target="#modal-lanca-pagamento">
                        Lançar Pagamento
                    </a>
                    <br>
                    <?php
                        }

                        // fechamos a conexão
                        $conexao = null;
                    ?>
                </section>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para lançamento de pagamento -->
        <div class="modal fade" id="modal-lanca-pagamento" tabindex="-1" role="dialog"
             aria-labelledby="modal-lanca-pagamento" aria-hidden="true">
            <form method="POST" id="form-pagamento" 
            action=<?= "gerenciar_pagamentos_aluno.php?id=".$idAluno."&ano=".$anoPagamento ?>

            >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Lançamento de pagamento</h4>
                    </div>
                    <div class="modal-body">
                        <div class="btn col-sm-12">
                            <div class="col-sm-12">
                                <p> 
                                Lançamento de pagamento para o aluno  <?= $aluno->getNome() ?> para o ano : <?= isset($_GET["ano"]) 
                                                ? htmlspecialchars($_GET["ano"]) 
                                                : date("Y")  ?>
                                </p>
                            </div>
                            <div class="col-sm-12">
                                <p>
                                Divida total : <?= "R$".
                                    number_format( $pagamentos[$anoPagamento]["divida"], 2)
                                     ?>
                                </p>
                            </div>

                                <div class="col-sm-12">
                                <label for="valor-pagamento" class="col-sm-6">
                                    Valor do Pagamento:
                                </label>
                                <input type="text" class="col-sm-6" id="valor-pagamento" name=" valor-pagamento" >

                            </div>
                            <div class="col-sm-12">
                                <label for="metodo-pagamento" class="col-sm-6">
                                    Método de Pagamento:
                                </label>
                                <input type="text" class="col-sm-6" id="metodo-pagamento" name="metodo-pagamento" >
                            </div>
                            <div class="col-sm-12">
                                <h5 class="warning">Não é permitido lançar um pagamento maior do que a divida total!</h5>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="submit" value="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </div>
            </div>
            </form>
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