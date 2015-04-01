<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
	session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Reuniões - Homeopatias.com</title>
        <script>
            $(document).ready(function(){
                // pequeno script para que o envio do formulário de ano seja feito assim
                // que o ano for mudado
                $("#ano").change( function(){ $(this).parent().submit() });
            });
        </script>
    </head>
    <body>
        <?php
            include("modulos/navegacao.php");

            $mensagem = "";


            // conferimos se o associado logado está com os pagamentos em dia
            // caso o usuário logado não seja um associado, retorna false
            require_once('rotinas/associado/checa_situacao_pagamentos.php');

            $pagamentosEmDia = checa_situacao_pagamentos();

            // exibe reuniões apenas para associados logados cujos documentos tenham
            // sido aprovados
            if(isset($_SESSION["usuario"]) && $pagamentosEmDia &&
               unserialize($_SESSION["usuario"])->getEnviouDocumentos()){
                $ano = date("Y");
                if(isset($_GET["ano"]) && preg_match("/^[0-9]{4,}$/", $_GET["ano"]) ){
                    $ano = $_GET["ano"];
                }else if(isset($_GET["ano"])){
                    $mensagem = "Ano inválido!";
                }

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

                // primeiro descobrimos todos os anos que possuem reuniões, para mostrá-los
                // no dropdown
                $textoQuery  = "SELECT DISTINCT YEAR(data) as ano FROM Reuniao";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $anosRelevantes = array();
                while ($linha = $query->fetch()){
                    $anosRelevantes[] = $linha["ano"];
                }

                $textoQuery  = "SELECT idReuniao, tema, data, descricao, local 
                                FROM Reuniao WHERE YEAR(data) = ? ORDER BY idReuniao DESC";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1, $ano);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $resultado = "";

                while ($linha = $query->fetch()){
                    // detalhe simples, para que o código fonte fique indentado ao ser visualizado
                    // pelo navegador
                    $resultado .= '
                        <div style="border-top: 1px dotted black">
                            <br>
                            <div class="row">
                                <h3 style="display:inline; font-weight:bold" class="col-sm-6"> '
                                . htmlspecialchars($linha["tema"]) .
                                ' </h3>
                            </div>
                            <br>
                            <div class="row">
                                <p style="display:inline" class="col-sm-3">
                                    <b>Data e horário:</b>
                                    ' . date("d/m/Y H:i", 
                                        strtotime($linha["data"])) . '
                                </p>
                            </div>
                            <div class="row">
                                <p style="display:inline" class="col-sm-6">
                                    <b>Local:</b>
                                    ' . htmlspecialchars($linha["local"]) . '
                                </p>
                            </div>
                            <div class="row">
                                <p style="display:inline" class="col-sm-10">
                                    <b>Descrição:</b><br>
                                    ' . htmlspecialchars($linha["descricao"]) . '
                                </p>
                            </div>
                        </div><br>
                    ';
                }
        ?>

        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h2 style="font-weight:bold">Visualizar reuniões por período</h2>
                    <label for="ano">
                        Reuniões do ano de:
                    </label><br>
                    <form style="width: 100px" method="GET" action="visualizar_reunioes.php ">
                        <select class="form-control input-sm" id="ano" name="ano">
                            <?php foreach ($anosRelevantes as $anoReunioes) {
                                $selecionado = $anoReunioes == $ano ? "selected" : "";
                                echo "<option value=" . $anoReunioes . " " . $selecionado
                                   . ">" . $anoReunioes . "</option>";
                            } ?>
                        </select>
                    </form>
                    <br><br>
                    <?php
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "            <p class=\"warning\">$mensagem</p>";
                        }
                        echo $resultado;
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
            window.location.href = "index.php";
        </script>
        <?php
                die();
            }

            include("modulos/rodape.php");
        ?>
    </body>
</html>