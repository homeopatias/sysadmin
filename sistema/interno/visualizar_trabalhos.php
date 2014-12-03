<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Trabalhos - Homeopatias.com</title>
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <!-- polyfill para funcionalidades do HTML5 -->
        <script src="./webshim-1.14.5/polyfiller.js"></script>
        <script>
            $(document).ready(function(){
                // usamos um polyfill para que os campos de data e hora funcionem mesmo
                // em navegadores que não implementem essas funcionalidades (você sabe quais)

                webshims.activeLang("pt-BR");
                webshims.setOptions('waitReady', false);
                webshims.setOptions('forms-ext', {types: 'number'});
                webshims.polyfill('forms forms-ext');

                // permite redimensionar as colunas da tabela
                $("#trabalhos").colResizable({
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
                        s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4}) às (\d{1,2}):(\d{2})/, "$3/$2/$1 $4:$5");
                        return $.tablesorter.formatFloat(new Date(s).getTime());
                    },
                    type: "numeric"
                });

                $("#trabalhos").tablesorter({ headers: {
                    2 : { sorter: 'datetime' },
                    3 : { sorter: false },
                    4 : { sorter: false },
                    5 : { sorter: false }
                }});

                // passa os dados para o modal de avaliação quando necessário
                $("#modal-avaliacao").on('show.bs.modal', function(e) {
                    $(this).find('#idTrabalho').val(
                        $(e.relatedTarget).data('id-trabalho')
                    );
                });

                // passa os dados para o modal de visualização de avaliação quando necessário
                $("#modal-visualizacao").on('show.bs.modal', function(e) {
                    var nota       = $(e.relatedTarget).data('nota'),
                        comentario = $(e.relatedTarget).data('comentario');

                    $(this).find('#trabalho-nota').text(
                        nota
                    );
                    if(comentario !== "") {
                        $(this).find('#trabalho-comentario').html(
                            comentario
                        );
                    } else {
                        $(this).find('#trabalho-comentario').text(
                            "O professor não fez nenhum comentário em relação ao trabalho enviado."
                        );
                    }
                });

                $("#filtro-nome").hide();
                $("#filtro-numero").hide();
                $("#filtro-corrigido").hide();

                // alterna campos de texto com campos de input
                $("#label-nome").click(function(){
                    $(this).hide();
                    $("#filtro-nome").show(300);
                    $("#filtro-nome").focus();
                });
                $("#label-numero").click(function(){
                    $(this).hide();
                    $("#filtro-numero").show(300);
                    $("#filtro-numero").focus();
                });
                $("#label-corrigido").click(function(){
                    $(this).hide();
                    $("#filtro-corrigido").show(300);
                    $("#filtro-corrigido").focus();
                });

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-nome").val("");
                    $("#filtro-numero").val("");
                    $("#filtro-corrigido").val("");
                    atualizaPagina();
                });

                //atualiza formulário com a busca
                function atualizaPagina(){
                    $("#pagina").val(0);
                    $("#form-filtro").submit();
                }
            });
        </script>
    </head>
    <body>
        <?php

            include('modulos/navegacao.php');

            // mensagem a ser exibida acima da listagem de trabalhos, caso seja necessário
            $mensagem = '';

            if(isset($_GET["erro"])){
                $mensagem = $_GET['erro'];
            }

            // exibe trabalhos apenas para professores logados que tenham permissão para avaliar
            if(isset($_SESSION['usuario']) &&
               unserialize($_SESSION['usuario']) instanceof Administrador &&
               unserialize($_SESSION['usuario'])->getNivelAdmin() == 'professor' &&
               unserialize($_SESSION['usuario'])->getCorrigeTrabalho()){

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
                try {
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }

                // se o usuário chegou até aqui através de um formulário, envia a avaliação de
                // trabalho
                if(isset($_POST['submit'])){

                    $idTrabalho = htmlspecialchars($_POST['idTrabalho']);
                    $nota       = htmlspecialchars($_POST['nota']);
                    $comentario = htmlspecialchars($_POST['comentario']);

                    $idTrabalhoValido = isset($idTrabalho) && preg_match("/^[0-9]*$/", $idTrabalho);
                    $notaValida       = isset($nota) && preg_match("/^[0-9]*$/", $nota);
                    $comentarioValido = !isset($comentario) || (mb_strlen($comentario) <= 10000);

                    // se todos os dados estiverem válidos, enviamos a avaliação
                    if($idTrabalhoValido && $notaValida && $comentarioValido) {

                        $textoQuery  = "UPDATE Trabalho SET nota = ?, comentarioProfessor = ? 
                                        WHERE idTrabalho = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $nota);
                        $query->bindParam(2, $comentario);
                        $query->bindParam(3, $idTrabalho);
                        $sucesso = $query->execute();

                        $textoQuery = "SELECT U.email, A.numeroInscricao, TD.titulo FROM Aluno A INNER JOIN
                                       Usuario U ON A.idUsuario = U.id INNER JOIN Trabalho T ON T.chaveAluno =
                                       A.numeroInscricao INNER JOIN TrabalhoDefinicao TD ON
                                       T.chaveDefinicao = TD.idDefTrabalho WHERE T.idTrabalho = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idTrabalho);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $resultado = $query->fetch();
                        $emailAluno = $resultado['email'];
                        $inscAluno = $resultado['numeroInscricao'];
                        $nomeTrabalho = $resultado['titulo'];

                        if(!$sucesso) {
                            $mensagem = "Falha na avaliação de trabalho";
                        } else {
                            // enviamos um email avisando o aluno do trabalho corrigido
                            $assunto = "Homeopatias.com - Trabalho corrigido: " . $nomeTrabalho;
                            $msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la.</b>";
                            $msg .= "<br><br><b>Trabalho \"" . $nomeTrabalho . "\"</b>";
                            $msg .= "<br>Corrigido dia " . date("d/m/Y, à\s H:i");
                            $msg .= "<br>Nota: " . $nota;
                            $msg .= "<br>" . (mb_strlen($comentario, 'UTF-8') != 0 ? "\"" . $comentario . "\"" :
                                              "O professor não fez nenhum comentário em relação ao trabalho.");
                            $msg .= "<br><br>Obrigado,<br>Equipe Homeobrás.";
                            $headers = "Content-type: text/html; charset=utf-8 " .
                                "From: Sistema Financeiro Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
                                "Reply-To: noreply@homeopatias.com" . "\r\n" .
                                "X-Mailer: PHP/" . phpversion();

                            mail($emailAluno, $assunto, $msg, $headers);

                            // agora registramos no sistema uma notificação para o aluno
                            $texto .= "Trabalho \"" . $nomeTrabalho . "\"";
                            $texto .= "\nCorrigido dia " . date("d/m/Y, à\s H:i");
                            $texto .= "\nNota: " . $nota;
                            $texto .= "\n" . (mb_strlen($comentario, 'UTF-8') != 0 ? "\"" . $comentario . "\"" :
                                              "O professor não fez nenhum comentário em relação ao trabalho.");
                            $queryNotificacao = $conexao->prepare("INSERT INTO Notificacao 
                                                (titulo, texto, chaveAluno, lida) VALUES (?, ?, ?, 0)");
                            $dados = array("Trabalho \"" . $nomeTrabalho . "\" corrigido", $texto, $inscAluno);
                            $queryNotificacao->execute($dados);
                        }

                    } else if(!$idTrabalhoValido) {
                        $mensagem = "Id de trabalho inválido!";
                    } else if(!$notaValida) {
                        $mensagem = "Nota inválida!";
                    } else if(!$comentarioValido) {
                        $mensagem = "Comentário do professor inválido!";
                    }

                }

                // recebemos o id da definição de trabalho selecionada
                $idDefTrabalho = $_GET["id"];
                if(!isset($idDefTrabalho) || !preg_match("/^[0-9]+$/", $idDefTrabalho)) {
                    die("Id de trabalho inválido");
                }

                $textoQuery  = "SELECT U.nome, A.numeroInscricao, UNIX_TIMESTAMP(T.dataEntrega) as 
                                entrega, T.idTrabalho, T.extensao, (NOT nota IS NULL) as avaliado, 
                                T.nota, T.comentarioProfessor, 
                                YEAR(T.dataEntrega) as ano FROM Trabalho T INNER JOIN Aluno A 
                                ON A.numeroInscricao = T.chaveAluno INNER JOIN Usuario U ON 
                                A.idUsuario = U.id WHERE T.chaveDefinicao = :idTDef";

                // se algum filtro foi enviado, filtra os resultados da consulta
                $filtroNome = $filtroNumero = $filtroCorrigido = false;

                // como não há botão para submit, temos que checar se todas as variáveis
                // existem
                if(isset($_GET["filtro-nome"])     || isset($_GET["filtro-numero"]) ||
                   isset($_GET["filtro-corrigido"])){
                    $filtroNome    =  htmlspecialchars($_GET["filtro-nome"]);
                    $filtroNumero  =  htmlspecialchars($_GET["filtro-numero"]);
                    $filtroCorrigido = htmlspecialchars($_GET["filtro-corrigido"]);

                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        // prepara o nome para ser colocado na query
                        $filtroNome    =  "%".$filtroNome."%";
                        $textoQuery .= "  AND U.nome LIKE :nome";
                    }
                    if(isset($filtroNumero) && mb_strlen($filtroNumero) > 0) {
                        if(!is_nan($filtroNumero)){
                            $textoQuery .= " AND A.numeroInscricao = :numInsc";
                        }
                    }
                    if(isset($filtroCorrigido) && mb_strlen($filtroCorrigido) > 0){
                        // prepara o status para ser colocado na query
                        $textoQuery .= "  AND ";
                        $textoQuery .= ($filtroCorrigido == 'true') ? "NOT" : "";
                        $textoQuery .= " nota IS NULL";
                    }
                }  

                $textoQuery .= " ORDER BY T.dataEntrega ASC";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(':idTDef', $idDefTrabalho, PDO::PARAM_INT);

                // passamos os parâmetros corretamente de acordo com os filtros passados
                if(isset($_GET["filtro-nome"]) || isset($_GET["filtro-numero"])){
                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $query->bindParam(":nome", $filtroNome);
                    }
                    if(isset($filtroNumero) && mb_strlen($filtroNumero) > 0) {
                        if(!is_nan($filtroNumero)){
                            $query->bindParam(":numInsc", $filtroNumero);
                        }
                    }
                }

                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $numeroRegistros = 0;
                $tabela = "";

                while ($linha = $query->fetch()){

                    // listamos os dados de cada trabalho
                    $tabela .= "<tr>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars($linha["numeroInscricao"])    ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars($linha["nome"])   ."</td>";

                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars(date("d/m/Y à\s H:i", $linha["entrega"]))   ."</td>";

                    $tabela .= "    <td><a target=\"_blank\" href=\"trabalhos/" . $linha['ano'];
                    $tabela .= "/" . $linha['numeroInscricao'] . "/" . $linha['idTrabalho'] . ".";
                    $tabela .= $linha['extensao'] . "\"><i class=\"fa fa-floppy-o\"></i></a></td>";

                    if($linha["avaliado"]){
                        $tabela .= "    <td><i class=\"fa fa-check sucesso\"></i></td>";
                        $tabela .= "    <td><a href=\"#\" data-toggle=\"modal\" data-nota=\"";
                        $tabela .= htmlspecialchars($linha['nota']) . "\" data-comentario=\"";
                        $tabela .= nl2br(htmlspecialchars($linha['comentarioProfessor'])) . "\"";
                        $tabela .= " data-target=\"#modal-visualizacao\" ";
                        $tabela .= "style=\"text-decoration: none\">Visualizar avaliação</td>";
                    }else{
                        $tabela .= "    <td><i class=\"fa fa-times warning\"></i></td>";
                        $tabela .= "    <td><a href=\"#\" data-id-trabalho=\"";
                        $tabela .= $linha['idTrabalho'] . "\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-avaliacao\"><i class=\"fa fa-pencil\">";
                        $tabela .= "</i></a></td>";
                    }

                    $tabela .= "</tr>";

                    $numeroRegistros++;
                }          
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>
                        Trabalhos enviados
                    </h1><br>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <b class="warning">Lembre-se, os trabalhos que os alunos enviaram 
                        após a data limite valem apenas 80% da nota! </b>
                    <br><br>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="visualizar_trabalhos.php" id="form-filtro">
                        <div class="form-group">
                            <br/>
                            <p>
                                <b>Buscar por:</b>
                            </p>
                            <input type="hidden" name="id" value=<?= "\"" . $idDefTrabalho . "\"" ?>>
                            <a id="label-nome" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-nome"]) && 
                                        mb_strlen(($_GET["filtro-nome"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Nome
                            </a>
                            <input  type="text" name="filtro-nome" id="filtro-nome"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-nome"]) ? 
                                        htmlspecialchars($_GET["filtro-nome"]) : "" ?> >

                            <a id="label-numero" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-numero"]) && 
                                        mb_strlen(($_GET["filtro-numero"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Inscrição
                            </a>

                            <input type="text" name="filtro-numero"
                                       id="filtro-numero"
                                       placeholder="Nº insc" class="form-control"
                                       style="display:inline;width:75px"
                                       value= <?= isset($_GET["filtro-numero"]) ? 
                                        htmlspecialchars($_GET["filtro-numero"]) : "" ?> >

                            <a id="label-corrigido" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-corrigido"]) && 
                                        mb_strlen(($_GET["filtro-corrigido"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Status
                            </a>

                            <select type="text" name="filtro-corrigido"
                                       id="filtro-corrigido" class="form-control"
                                       style="display:inline;width:150px">
                                <option value="" 
                                    <?=isset($_GET["filtro-corrigido"]) &&
                                        htmlspecialchars($_GET["filtro-corrigido"]) == "" ?
                                        'selected="selected"': '' ;?> >Nenhum
                                </option>
                                <option value="true"
                                    <?=isset($_GET["filtro-corrigido"]) &&
                                        htmlspecialchars($_GET["filtro-corrigido"]) == "true"?
                                    'selected="selected"':'';?> >
                                Corrigido</option>
                                <option value="false"
                                    <?=isset($_GET["filtro-corrigido"]) &&
                                        htmlspecialchars($_GET["filtro-corrigido"]) == "false"?
                                    'selected="selected"':'';?> >
                                Não-corrigido</option>
                            </select>
                            <br><br>
                            <a href="#" id="limpar" class="btn btn-info" >
                                Limpar
                                <i href="#" class="fa fa-eraser"></i>
                            </a>
                            <a href="#" id="busca" class="btn btn-info">
                                Buscar
                                <i href="#" class="fa fa-search"></i>
                            </a>
                        </div>
                    </form>
                    <!-- fim dos filtros -->
                    <br>
                    <?php if($numeroRegistros !== 0){ ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="trabalhos">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th>Nº de inscrição</th>
                                        <th width="350px">Nome do aluno</th>
                                        <th width="200px">Hora de envio</th>
                                        <th>Baixar trabalho</th>
                                        <th>Corrigido?</th>
                                        <th width="150px">Avaliar trabalho</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= $tabela ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php 
                            if($numeroRegistros != 1){
                    ?>

                    <b><?= $numeroRegistros ?> trabalhos encontrados</b><br>
                    <?php   }else{ ?>
                    
                    <b><?= $numeroRegistros ?> trabalho encontrado</b><br>
                    <?php   }
                        } // $numeroRegistros !== 0
                        else{
                    ?>
                    <b>Nenhum trabalho recebido até o momento</b><br>
                    <?php
                        }
                    ?>
                    
                </section>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para visualização de avaliação de trabalho -->
        <div class="modal fade" id="modal-visualizacao" tabindex="-1" role="dialog"
             aria-labelledby="modal-visualizacao" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title" style="font-weight:bold">Trabalho corrigido</h4>
                    </div>
                    <div class="modal-body">
                        <b>Nota do trabalho: </b><span id="trabalho-nota"></span>
                        <br><br>
                        <b>Comentário do professor: </b><br><br>
                        <p id="trabalho-comentario"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para avaliação de trabalho -->
        <div class="modal fade" id="modal-avaliacao" tabindex="-1" role="dialog"
             aria-labelledby="modal-avaliacao" aria-hidden="true">
            <div class="modal-dialog">
                <form id="avaliar-trabalho" action method="POST">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Avaliar trabalho</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="idTrabalho" id="idTrabalho">
                            <div class="form-group">
                                <label for="nota">Nota:</label>
                                <input type="number" name="nota" id="nota" min="0" max="100"
                                       style="margin-left: 20px; width: 60px">
                            </div>
                            <div class="form-group">
                                <label for="comentario">Comentário do professor:</label>
                                <textarea name="comentario" id="comentario" rows="8" cols="50"
                                    maxlength="10000" title="O comentário do professor deve ter
                                    no máximo 10000 caracteres" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
                            <button type="submit" name="submit" value="submit" class="btn btn-success">
                                Enviar
                            </button>
                        </div>
                    </div>
                </form>
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