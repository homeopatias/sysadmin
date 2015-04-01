<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Selecionar aula - Homeopatias.com</title>
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

                $textoQuery  = "SELECT C.UF, C.nome, A.idAula, A.etapa,
                                UNIX_TIMESTAMP(A.data) as dataAula FROM Cidade C
                                INNER JOIN Aula A ON A.chaveCidade = C.idCidade
                                WHERE A.data < NOW() AND C.idCoordenador = ?
                                AND C.ano = YEAR(NOW())";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1, $idCoordenador);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                if ($query->rowCount() == 0) {
                    // esse coordenador não coordena nenhuma cidade, redirecionamos
                    // ele para o index
            ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="index.php?sucessoAval=true">
        <script type="text/javascript">
            window.location.href = "index.php?mensagem=Sua cidade ainda não teve aulas esse ano!";
        </script>
            <?php
                }
            ?>
        <script>
            var aulas = [];
            <?php
                $nomeCidade = false;
                while ($linha = $query->fetch()){
                    if(!$nomeCidade) {
                        $nomeCidade = htmlspecialchars($linha["nome"]) . "/" .
                                      htmlspecialchars($linha["UF"]);
                    }
                    $idAula = "\"".htmlspecialchars($linha["idAula"])."\"";
                    $etapa  = "\"".htmlspecialchars($linha["etapa"])."\"";
                    $data   = "\"".date("d/m/Y", htmlspecialchars($linha["dataAula"]))."\"";
            ?>
            aulas.push({
                id:    <?= $idAula ?>,
                etapa: <?= $etapa  ?>,
                data:  <?= $data   ?>
            });
            <?php
                }
            ?>

            // agora implementamos o funcionamento dinâmico da página
            $(document).ready(function(){
                $("#form-turma #etapa").on('change', function(){
                    var etapaSelecionada  = $("#form-turma #etapa").val();

                    $("#form-turma #aula").find('option').remove().end();
                    aulas.forEach(function(aula){
                        if(aula.etapa == etapaSelecionada) {
                            $("#form-turma #aula").append('<option value="' +aula.id + '">' +
                                aula.data + '</option>');
                        }
                    });
                });

                // descobrimos em quais etapas dessa cidade já houveram aulas
                var etapas = new Array();

                aulas.forEach(function(aula){
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

                // ativamos o evento change da etapa para popular os inputs
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
            // COORDENADORES NÃO PODEM TER ACESSO, PORTANTO, POR ENQUANTO, PERMITIMOS APENAS PARA
            // ADMINISTRADORES
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>Aulas em <?= $nomeCidade ?></h1><br>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <p>Na lista de etapas constam apenas as etapas em que já ocorreram aulas</p>
                    <form class="form-inline" id="form-turma"
                          action="lancar_frequencias.php" method="GET">
                        <br>
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
            window.location.href = "index.php";
        </script>

        <?php
                die();
            }

            include("modulos/rodape.php");
        ?>
    </body>
</html>