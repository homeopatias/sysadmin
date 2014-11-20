<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Visualização de pagamentos</title>
        <script type="text/javascript">
            $(document).ready(function(){
            // entrada de valor em reais
                $("#label-valor").click(function(){
                    $(this).hide();
                    $("#pgto-valor").show(300);
                    $("#pgto-valor").focus();
                });
                $("#pgto-valor").blur(function(){
                    if ($(this).val() && $(this).parent()[0].checkValidity()) {
                        $(this).parent().submit();
                        return;
                    } else {
                        $("#msg-erro").text("Insira um valor numérico real menor ou igual " +
                                            "ao saldo devedor");
                    }
                    $(this).hide();
                    $(this).val("");
                    $("#label-valor").show(300);
                });

                $("#pagar-inscricao").click(function(){
                    $(this).parent().find("#target").val("inscricao");
                    $(this).parent().submit();
                });
                $("#pagar-anuidade").click(function(){
                    $(this).parent().find("#target").val("anuidade");
                    $(this).parent().submit();
                });
            });

        </script>

    </head>
    <body>
        <?php 
            require_once("entidades/Associado.php");

            //exibe página apenas para associados logados no sistema
             if(isset($_SESSION["usuario"]) &&
               unserialize($_SESSION["usuario"])->getEnviouDocumentos()){

                include("modulos/navegacao.php");

                $mensagem = "";

                // lemos as credenciais do banco de dados
                $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                $dados = json_decode($dados, true);

                foreach($dados as $chave => $valor) {
                    $dados[$chave] = str_rot13($valor);
                }

                $host    = $dados["host"];
                $usuario = $dados["nome_usuario"];
                $senhaBD = $dados["senha"];

                // cria conexão com o banco
                $conexao = null;
                $db      = "homeopatias";
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                if( isset($_GET["mensagem"]) ){
                    $mensagem = $_GET["mensagem"];
                }
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">

                    <p id="msg-erro" class = "warning"> <?= $mensagem ?> </p>
                    <h3>Pagamentos do ano atual</h3>

                    <?php 
                        // buscamos os pagamentos do ano atual do usuário
                        $textoQuery = "SELECT inscricao, valorTotal, valorPago, data, fechado
                                       FROM PgtoAnuidade
                                       WHERE chaveAssoc = ? AND ano = YEAR(CURDATE())";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, unserialize($_SESSION["usuario"])->getIdAssoc(), 
                            PDO::PARAM_INT );
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        // checamos se a inscrição foi paga e o valor da divida
                        $inscricaoPaga = false;
                        $divida = 0;

                        $tabela = "";
                        while($linha = $query->fetch()){
                            $tabela .= "<tr>";
                            $tabela .= "    <td>". ($linha["inscricao"] ? "Inscrição" : "Anuidade") . "</td>";
                            $tabela .= "    <td> R$". number_format($linha["valorTotal"],2).
                                    "</td>";
                            $tabela .= "    <td> R$". number_format($linha["valorPago"],2)."</td>";
                            $tabela .= "    <td>".($linha["data"] ? $linha["data"] : "N/A")."</td>";
                            $tabela .= "</tr>"; 

                            if($linha["inscricao"] && $linha["fechado"]){
                                $inscricaoPaga = true;
                            }
                            if(!$linha["fechado"]){
                                $divida += $linha["valorTotal"] - $linha["valorPago"];
                            }

                        }
                        if($divida > 0){
                            echo "<p class='warning'>Sua divida no ano atual é de R$".number_format($divida,2);
                        }

                    ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="pagamentos">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <td>Tipo</td>
                                        <td>Valor Total</td>
                                        <td>Valor Pago</td>
                                        <td>Data do Pagamento</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= $tabela ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php 
                        // buscamos os pagamentos dos anos anteriores do associado
                        $textoQuery = "SELECT inscricao, valorTotal, valorPago, data, ano, fechado
                                       FROM PgtoAnuidade
                                       WHERE chaveAssoc = ? AND ano < YEAR(CURDATE())";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, unserialize($_SESSION["usuario"])->getIdAssoc(), 
                            PDO::PARAM_INT );
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $dividaAnosAnteriores = 0;

                        $tabela = "";
                        $possui_anos_anteriores = false;

                        while($linha = $query->fetch()){
                            $possui_anos_anteriores = true;
                            $tabela .= "<tr>";
                            $tabela .= "    <td>". ($linha["inscricao"] ? "Inscrição" : "Anuidade") . "</td>";
                            $tabela .= "    <td> R$". number_format($linha["valorTotal"],2).
                                    "</td>";
                            $tabela .= "    <td> R$". number_format($linha["valorPago"],2)."</td>";
                            $tabela .= "    <td>".($linha["data"] ? $linha["data"] : "N/A")."</td>";
                            $tabela .= "    <td>".$linha["ano"]."</td>";
                            $tabela .= "</tr>"; 
                            if(!$linha["fechado"]){
                                $dividaAnosAnteriores += $linha["valorTotal"] - $linha["valorPago"];
                            }
                        }
                        if($dividaAnosAnteriores > 0){
                            echo "<h5 class = 'warning'>Você possui divida em anos anteriores equivalente há R$".number_format($dividaAnosAnteriores,2).", seus pagamentos serão utilizados para pagar as dividas anteriores antes das abertas no ano atual</h5>";
                            $divida += $dividaAnosAnteriores;
                        }
                        echo "<p>Divida total = R$".number_format($divida,2);


                        if($divida > 0){
                            echo "<form action = './rotinas/gerar_pagamento_anuidade.php' method='POST' >
                                    <input type='hidden' name='target' id= 'target' value=''> ";

                            if(!($dividaAnosAnteriores > 0) ){
                                if(!$inscricaoPaga){
                                    echo "<a  id='pagar-inscricao' href='#' class='btn btn-primary'
                                            name = 'pagar-inscricao'
                                            >Pagar Inscrição</a>";
                                }else{
                                    echo "<a  id='pagar-anuidade' href='#' class='btn btn-primary'
                                            name = 'pagar-anuidade'>Pagar Anuidade</a>";
                                }

                            }
                            echo '<a id="label-valor" href="#" class="btn btn-primary" 
                                        style="display:block; width:150px">
                                        Pagar valor
                                    </a>
                                    <input type="number" name="pgto-valor" id="pgto-valor"
                                           placeholder="Quantidade em R$" class="form-control"
                                           autocomplete="off" pattern="^[0-9]*\.?[0-9]+$"
                                           style="display:none;width:150px"
                                           step="0.01" min="1"
                                           max="' .
                                                   number_format($divida, 2, '.', '') .
                                                   '">';
                        }

                    ?>
                    <?php if($possui_anos_anteriores){ ?>

                    <h3>Pagamentos de anos anteriores</h3>

                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="pagamentos">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <td>Tipo</td>
                                        <td>Valor Total</td>
                                        <td>Valor Pago</td>
                                        <td>Data do Pagamento</td>
                                        <td>Referente ao ano</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= $tabela ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php } ?>

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