<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Selecionar turma - Homeopatias.com</title>
        <script>
            // aqui recebemos os dados das cidades do ano atual em que já ocorreram
            // aulas, assim podemos atualizar a lista de cidades dinamicamente

            // também recebemos os dados das aulas de cada cidade
            
            var cidades = new Array(); // o vetor será indexado pelo id de cada cidade
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

                // cria conexão com o banco
                $conexao = null;
                $db      = "homeopatias";
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                require_once("entidades/Administrador.php");

                $coordenador = unserialize($_SESSION["usuario"]);
                $idCoordenador = $coordenador->getIdAdmin();

                $textoQuery  = "SELECT C.idCidade, C.UF, C.nome, A.idAula, A.etapa,
                                UNIX_TIMESTAMP(A.data) as dataAula FROM Cidade C
                                INNER JOIN Aula A ON A.chaveCidade = C.idCidade
                                WHERE C.ano = YEAR(CURDATE()) AND A.data < NOW() AND
                                C.idCoordenador = :idcoordenador ORDER BY A.data ASC, C.nome ASC";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(":idcoordenador",$idCoordenador);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();


                // usamos um vetor com os ids das cidades já inseridas
                // para determinar quando criar uma nova cidade e quando inserir
                // a aula na cidade já existente
                $cidadesListadas = array();

                while ($linha = $query->fetch()){
                    $id   = "\"".htmlspecialchars($linha["idCidade"])."\"";
                    $uf   = "\"".htmlspecialchars($linha["UF"])."\"";
                    $nome = "\"".htmlspecialchars($linha["nome"])."\"";
                    if(!in_array($linha["idCidade"], $cidadesListadas)){
                        $cidadesListadas[] = $linha["idCidade"];
            ?>

            cidades[ <?= $id ?> ] = {
                uf:   <?= $uf ?>,
                nome: <?= $nome ?>,
                aulas: new Array()
            };
            <?php
                    }
                    $idAula = "\"".htmlspecialchars($linha["idAula"])."\"";
                    $etapa  = "\"".htmlspecialchars($linha["etapa"])."\"";
                    $data   = "\"".date("d/m/Y", htmlspecialchars($linha["dataAula"]))."\"";
            ?>
            cidades[<?= $id ?>].aulas.push({
                id:    <?= $idAula ?>,
                etapa: <?= $etapa  ?>,
                data:  <?= $data   ?>
            });
            <?php
                }
            ?>

            // agora implementamos o funcionamento dinâmico da página
            $(document).ready(function(){
                cidades.forEach(function(cidade, idCidade){
                    $("#form-turma #cidade").append('<option value="' + idCidade + '">' +
                        cidade.nome + "/" + cidade.uf + '</option>');
                });

                // atualizamos as etapas disponíveis quando a cidade é mudada
                // para que o coordenador não veja etapas em que não houveram aulas ainda
                $("#form-turma #cidade").on('change', function(){
                    var cidadeSelecionada = $(this).val();

                    // descobrimos em quais etapas dessa cidade já houveram aulas
                    var etapas = new Array();

                    cidades[cidadeSelecionada].aulas.forEach(function(aula){
                        if(etapas.indexOf(aula.etapa) == -1){
                            etapas.push(aula.etapa);
                        }
                    });
                    etapas.sort();

                    $("#form-turma #etapa").find('option').remove().end();
                    etapas.forEach(function(etapa){
                        $("#form-turma #etapa").append('<option value="' + etapa + '">' +
                            etapa + '</option>');
                    });

                    // ativamos o evento change da etapa para atualizar os valores
                    $("#form-turma #etapa").change();
                });

                $("#form-turma #etapa").on('change', function(){
                    var cidadeSelecionada = $("#form-turma #cidade").val();
                    var etapaSelecionada  = $("#form-turma #etapa").val();

                    $("#form-turma #aula").find('option').remove().end();
                    cidades[cidadeSelecionada].aulas.forEach(function(aula){
                        if(aula.etapa == etapaSelecionada) {
                            $("#form-turma #aula").append('<option value="' +aula.id + '">' +
                                aula.data + '</option>');
                        }
                    });
                });

                // ativamos o evento change da cidade e da etapa para popular os inputs
                $("#form-turma #cidade").change();
                $("#form-turma #etapa").change();
            });

        </script>
    </head>
    <body>
        <?php

            include("modulos/navegacao.php");

            // mensagem a ser exibida acima da seleção de cidade, caso necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // permite a seleção de aula apenas se o usuário for um coordenador
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "coordenador"){
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>Selecione uma turma</h1><br>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <p>Na lista de cidades constam apenas as cidades em que já ocorreram aulas esse ano</p>
                    <form class="form-inline" id="form-turma"
                          action="lancar_frequencias.php" method="GET">
                        <br>
                        <div class="form-group" style="margin-left: 20px">
                            <label for="cidade">Cidade:</label>
                            <select name="cidade" id="cidade" class="form-control" required>
                            </select>
                        </div>
                        <div class="form-group" style="margin-left: 20px">
                            <label for="etapa">Etapa:</label>
                            <select name="etapa" id="etapa" class="form-control" required>
                            </select>
                        </div>
                        <div class="form-group" style="margin-left: 20px">
                            <label for="aula">Aula do dia:</label>
                            <select name="aula" id="aula" class="form-control" required>
                            </select>
                        </div>
                        <br><br>
                        <button type="submit" name="submit" value="submit"
                                class="btn btn-primary">
                            Buscar lista de alunos
                        </button>
                    </form>
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