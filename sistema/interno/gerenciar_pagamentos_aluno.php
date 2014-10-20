<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Lista de chamada - Homeopatias.com</title>
        <script>
            $(document).ready(function(){
                // pequeno script para que o envio do formulário de ano seja feito assim
                // que a cidade ou a etapa for mudada
                $("#cidade").change( function(){ $(this).parent().submit() });
                $("#etapa").change( function(){ $(this).parent().submit() });
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

            // exibe pagamentos e lançamento de pagamentos de alunos apenas para Coordenadores logados e administradores com permissão de acesso
            if(isset($_SESSION["usuario"]) &&
               unserialize($_SESSION["usuario"]) instanceof Administrador &&
               unserialize($_SESSION["usuario"])->getNivelAdmin() === "coordenador"){
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

                        // os pagamentos são criados na hora em que o aluno se matricula,
                        // e a medida que o aluno paga, eles vão sendo fechados.

                        // procuramos os pagamentos desse ano, tanto pendentes
                        // como efetuados

                        $anoPagamento = date("Y");
                        if( isset($_GET["ano"]) ){
                            $anoPagamento = $_GET["ano"];
                        }

                        $textoQuery  = "SELECT P.valorPago, P.valorTotal, P.data, P.desconto,
                                        P.ano, P.numParcela ,P.fechado FROM Matricula M, PgtoMensalidade P
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
                            $pagamentos[$anoPag][$numParcela]['pago']   = $linha['valorPago'];
                            $pagamentos[$anoPag][$numParcela]['data']  = $linha['data'];
                            $pagamentos[$anoPag][$numParcela]['fechado']  = $linha['fechado'];
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
                        echo "<td style='background-color: #AAA'><b>Valor pago</b></td>";
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
                    ?>
                            </tr>
                        </tbody>
                    </table>

                    <?php

                        
                    ?>

                    <?php
                        }

                        // fechamos a conexão
                        $conexao = null;
                    ?>
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