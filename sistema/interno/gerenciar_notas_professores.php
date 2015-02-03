<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();

    // exibe as avaliações que os alunos fizeram dos professores, mostrando
    // que nota cada aula do professor recebeu
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Avaliações dos professores - Homeopatias.com</title>
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <script>
            $(document).ready(function(){

                // permite redimensionar as colunas da tabela
                $("#notas").colResizable({
                    liveDrag: true,
                    minWidth: 60
                });

                // torna a tabela ordenavel pelas colunas

                // parser para ordenar datas
                $.tablesorter.addParser({
                    id: "datetime",
                    is: function(s) {
                        return false; 
                    },
                    format: function(s,table) {
                        s = s.replace(/\-/g,"/");
                        s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
                        return $.tablesorter.formatFloat(new Date(s).getTime());
                    },
                    type: "numeric"
                });

                $("#notas").tablesorter({ headers: {
                    3 : { sorter: "datetime" },
                }});

                checaTamanhoTela();
            }); 

            //------------Checa se tamanho minimo da tela é o tamanho minimo do css
            function checaTamanhoTela(){
                tamanhoTela = $(window).width();

                if (tamanhoTela < 700) {
                    $("table").colResizable({
                        disable:true
                    }); 
                    $(".flip-scroll th").css("width","150px");
                }
                else {
                    $("table").colResizable({
                        disable:false
                    }); 
                }
            }

            //----Checa se ao redimencionar a tela atingiu o tamanho minimo da tela
            $(window).resize(function() {
                checaTamanhoTela();
            });
        </script>
    </head>
    <body>
        <?php

            include("modulos/navegacao.php");

            // mensagem a ser exibida acima da listagem de notas, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe notas apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
               2 & unserialize($_SESSION["usuario"])->getPermissoes() ){
            
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

                $idProfessor = isset($_GET["idProfessor"]) ? $_GET["idProfessor"] : '';
                $idProfessor = intval($idProfessor);

                $textoQuery  = "SELECT P.nome as nProf, C.nome, C.ano, C.UF, A.etapa, 
                                UNIX_TIMESTAMP(A.data) as data, A.nota, COUNT(F.chaveAula) as numAval 
                                FROM Frequencia F INNER JOIN Aula A ON A.idAula = F.chaveAula 
                                INNER JOIN Administrador Ad ON Ad.idAdmin = A.idProfessor 
                                INNER JOIN Usuario P ON P.id = Ad.idUsuario 
                                INNER JOIN Cidade C ON C.idCidade = A.chaveCidade 
                                WHERE A.nota IS NOT NULL AND jaAvaliou = 1 AND A.idProfessor = ?
                                GROUP BY F.chaveAula";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->bindParam(1, $idProfessor);
                $query->execute();

                $numeroRegistros = 0;
                $tabela = "";
                $nomeProf = '';

                while ($linha = $query->fetch()){

                    // listamos os dados de cada nota
                    if(!$nomeProf){
                        $nomeProf = htmlspecialchars($linha["nProf"]);
                    }

                    $tabela .= "<tr>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars($linha["nome"] . "/" . $linha["UF"])         ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars($linha["ano"])                               ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars($linha["etapa"])                             ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars(date("d/m/Y H:i", $linha["data"]))           ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars($linha["numAval"])                           ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= number_format(htmlspecialchars($linha["nota"]), 2, ".", " ") ."%</td>";
                    $tabela .= "</tr>";

                    $numeroRegistros++;
                }          
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>Notas recebidas por <?= $nomeProf ?></h1><br>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <!-- opção para organizar as notas por etapa de cada ano, ao invés de por aula -->
                    <a href=<?= "\"gerenciar_notas_professores_etapa.php?idProfessor=" . $idProfessor . "\"" ?> class="btn">
                        <i href="#" class="fa fa-calendar"></i>
                        <p style="display:inline">Visualizar notas pelas etapas de cada ano</p>
                    </a>
                    <br><br>
                    <?php if($numeroRegistros !== 0){ ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="notas">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="200px">Cidade</th>
                                        <th width="70px">Ano</th>
                                        <th width="70px">Etapa</th>
                                        <th width="160px">Data e horário da aula</th>
                                        <th width="160px">Número de avaliações</th>
                                        <th width="100px">Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= $tabela ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php }
                        if($numeroRegistros != 1){
                    ?>

                    <b><?= $numeroRegistros ?> registros encontrados</b><br>
                    <?php }else{ ?>
                    
                    <b><?= $numeroRegistros ?> registro encontrado</b><br>
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