<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Visualização de turmas - Homeopatias.com</title>
        <script>
            $(document).ready(function(){
                // pequeno script para que o envio do formulário de ano seja feito assim
                // que a etapa for mudada
                $("#etapa").change( function(){ $(this).parent().submit() });

                // passa os dados do href para o modal de confirmação de fechamento
                // de turma quando necessário
                $("#modal-fecha-turma").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                });

                //seta o tipo do e-mail a ser enviado
                $("#sendTodos").click(function(e){
                    $("#modal-email").find("#sendType").val("todos");
                });

                $("#sendSelecionados").click(function(e){
                    $("#modal-email").find("#sendType").val("selecionados");
                });

                //prepara uma string com os ids selecionados para enviar o email
                $("#email").submit(function(e){

                    $(this).find("#url-send").val(window.location.href);

                    var selected = "";
                    $("table input:checked").each(function(){
                        selected += $(this).val() + ",";
                    });
                    var element = $("<input type='hidden' id='selecionados' name='selecionados'>");
                    element.val(selected);
                    $(this).append(element);

                });


                // esconde inputs de busca

                $("#filtro-nome").hide();
                $("#filtro-email").hide();
                $("#filtro-registro").hide();
                $("#filtro-status").hide();

                // alterna campos de texto com campos de input
                $("#label-nome").click(function(){
                    $(this).hide();
                    $("#filtro-nome").show(300);
                    $("#filtro-nome").focus();
                });

                $("#label-email").click(function(){
                    $(this).hide();
                    $("#filtro-email").show(300);
                    $("#filtro-email").focus();
                });

                $("#label-registro").click(function(){
                    $(this).hide();
                    $("#filtro-registro").show(300);
                    $("#filtro-registro").focus();
                });

                $("#label-status").click(function(){
                    $(this).hide();
                    $("#filtro-status").show(300);
                    $("#filtro-status").focus();
                });

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-nome").val("");
                    $("#filtro-email").val("");
                    $("#filtro-registro").val("");
                    $("#filtro-status").val("");
                    atualizaPagina();
                });


                $('#alunos input[type="checkbox"]').click(function() {
                    var numSelecionados = $("#alunos").find('input[type="checkbox"]:checked').length;
                    if(numSelecionados)
                        $("#sendSelecionados").fadeIn();
                    else
                        $("#sendSelecionados").fadeOut();
                });


                // atualiza formulário com a busca
                function atualizaPagina(){
                    $("#form-filtro").submit();
                }
            });
        </script>
    </head>
    <body>
        <?php
            include("modulos/navegacao.php");

            $mensagem = "";
            $sucesso  = false;

            // exibe listas de chamada apenas para coordenadores logados
            if(isset($_SESSION["usuario"]) &&
               unserialize($_SESSION["usuario"]) instanceof Administrador &&
               unserialize($_SESSION["usuario"])->getNivelAdmin() === "coordenador"){

                $etapa = false;
                if(isset($_GET["etapa"])){
                    if(preg_match("/^[1-4]*$/", $_GET["etapa"])){
                        $etapa = htmlspecialchars($_GET["etapa"]);
                    }else{
                        $mensagem = "Etapa inválida!";
                        $etapa = 1;
                    }
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
                require_once("entidades/Administrador.php");
                $coordenador = unserialize($_SESSION["usuario"]);

                $coordenadorId = $coordenador->getId();
                $coordenadorIdAdmin = $coordenador->getIdAdmin();

                $textoQuery  = "SELECT C.idCidade, C.nome, C.UF FROM Cidade C , Usuario U, 
                                Administrador A WHERE U.id = ? AND A.nivel = 'coordenador'
                                AND C.idCoordenador = A.idAdmin AND A.idAdmin = ?
                                AND ano = YEAR(NOW()) LIMIT 1";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1,$coordenadorId);
                $query->bindParam(2,$coordenadorIdAdmin);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $cidade = null;

                if ($linha = $query->fetch()){
                    $idCidade = htmlspecialchars($linha["idCidade"]);

                    // caso nenhuma etapa tenha sido recebida, listamos os alunos da
                    // primeira etapa
                    if(!$etapa){
                        $etapa = 1;
                    }

                    $cidade = array("nome" => htmlspecialchars($linha["nome"]),
                                    "UF"   => htmlspecialchars($linha["UF"]));
                }
        ?>

        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h2 style="font-weight:bold; display:inline">
                        Suas turmas atuais - <?= $cidade['nome'] . '/' . $cidade['UF'] ?>
                    </h2>
                    <?php
                        $mensagem = isset($_GET['mensagem']) ? $_GET['mensagem'] : $mensagem;
                        $sucesso  = isset($_GET['sucesso']) ? $_GET['sucesso'] : $sucesso;
                        if(mb_strlen($mensagem, 'UTF-8') !== 0 && !$sucesso){
                            echo "<br><br><p class=\"warning\">$mensagem</p>";
                        }
                        if($sucesso){
                            echo "<br><br><p class=\"sucesso\">$mensagem</p>";
                        }
                    ?>
                    <a href=<?= "\"impressao_chamada.php?etapa=" . $etapa ."\"" ?>
                       target="_blank" class="pull-right" style="text-decoration:none" id="btn-imprimir">
                        <b>Diario da próxima aula para impressão &nbsp;</b>
                        <i class="fa fa-lg fa-print"></i>
                    </a>
                    <br><br>
                    <a class="pull-right btn btn-primary" id="btn-fechar" data-toggle="modal"
                       data-target="#modal-fecha-turma"
                       data-href=<?= "\"rotinas/fechar_turma.php?etapa=" . $etapa . "\""?>>
                        Fechar turma
                    </a>
                    <form style="width: 500px" method="GET" action="visualizar_turmas.php ">                
                        <label for="etapa">
                            Selecione a etapa:
                        </label><br>
                        <select style="display:inline; width: 50px !important"
                                class="form-control input-sm" id="etapa" name="etapa">
                            <option value="1" <?php if($etapa == 1) echo "selected" ?>>1</option>
                            <option value="2" <?php if($etapa == 2) echo "selected" ?>>2</option>
                            <option value="3" <?php if($etapa == 3) echo "selected" ?>>3</option>
                            <option value="4" <?php if($etapa == 4) echo "selected" ?>>4</option>
                        </select>
                    </form>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action id="form-filtro">
                        <div class="form-group">
                            <br/>
                            <p>
                                <b>Buscar por:</b>
                            </p>
                            <input type="hidden" name="etapa" value=<?= "\"" . $etapa . "\""?>>
                            <a id="label-nome" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-nome"]) && 
                                        mb_strlen(($_GET["filtro-nome"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Nome
                            </a>
                            <input  type="text" name="filtro-nome" id="filtro-nome"
                                    placeholder="Nome do aluno" class="form-control"
                                    autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-nome"]) ? 
                                        htmlspecialchars($_GET["filtro-nome"]) : "" ?> >

                            <a id="label-email" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-email"]) && 
                                        mb_strlen(($_GET["filtro-email"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Email
                            </a>
                            <input  type="text" name="filtro-email" id="filtro-email"
                                    placeholder="Email do aluno" class="form-control"
                                    autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-email"]) ? 
                                        htmlspecialchars($_GET["filtro-email"]) : "" ?> >

                            <a id="label-registro" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-registro"]) && 
                                        mb_strlen(($_GET["filtro-registro"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Registro
                            </a>
                            <input  type="text" name="filtro-registro" id="filtro-registro"
                                    placeholder="Registro do aluno" class="form-control"
                                    autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-registro"]) ? 
                                        htmlspecialchars($_GET["filtro-registro"]) : "" ?> >

                            <a id="label-status" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-status"]) && 
                                        mb_strlen(($_GET["filtro-status"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Status
                            </a>

                            <select name="filtro-status" id="filtro-status" class="form-control"
                                    style="display:inline;width:120px">
                                <option value="" 
                                    <?=isset($_GET["filtro-status"]) &&
                                        htmlspecialchars($_GET["filtro-status"]) == "" ?
                                        'selected="selected"': '' ;?> >Nenhum
                                </option>
                                <option value="preinscrito"
                                    <?=isset($_GET["filtro-status"]) &&
                                        htmlspecialchars($_GET["filtro-status"]) == "preinscrito"?
                                    'selected="selected"':'';?> >
                                Pré-inscrito</option>
                                <option value="inscrito"
                                    <?=isset($_GET["filtro-status"]) &&
                                        htmlspecialchars($_GET["filtro-status"]) == "inscrito"?
                                    'selected="selected"':'';?> >
                                Inscrito</option>
                                <option value="desistente"
                                   <?=isset($_GET["filtro-status"]) &&
                                        htmlspecialchars($_GET["filtro-status"]) == "desistente"?
                                   'selected="selected"':'';?> >
                                Desistente</option>
                                <option value="formado"
                                    <?=isset($_GET["filtro-status"]) &&
                                        htmlspecialchars($_GET["filtro-status"]) == "formado"?
                                   'selected="selected"':'';?> >
                                Formado</option>
                                <option value="inativo"
                                    <?=isset($_GET["filtro-status"]) &&
                                        htmlspecialchars($_GET["filtro-status"]) == "inativo"?
                                   'selected="selected"':'';?> >
                                Inativo</option>
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
                    <!--  Fim form filtros   -->
                    <br><br>
                    <?php

                        $textoQuery  = "SELECT U.nome, U.cpf, U.email, A.numeroInscricao, A.status,
                                        M.aprovado FROM Matricula M INNER JOIN Cidade C 
                                        ON C.idCidade = M.chaveCidade INNER JOIN Aluno A ON 
                                        M.chaveAluno = A.numeroInscricao INNER JOIN Usuario U ON 
                                        U.id = A.idUsuario WHERE 
                                        C.idCidade = :idcidade AND M.etapa = :etapa";

                        // Se algum filtro foi repassado, altera o query para filtrar
                        $filtroRegistro = $filtroEmail = $filtroNome = $filtroStatus = false;
                        if(isset($_GET["filtro-nome"]) || isset($_GET["filtro-registro"]) ||
                           isset($_GET["filtro-email"])|| isset($_GET["filtro-status"])){

                            $filtroNome     =  htmlspecialchars($_GET["filtro-nome"]);
                            $filtroEmail    =  htmlspecialchars($_GET["filtro-email"]);
                            $filtroRegistro =  htmlspecialchars($_GET["filtro-registro"]);
                            $filtroStatus   =  htmlspecialchars($_GET["filtro-status"]);

                            if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                                $filtroNome  =  "%".mb_strtoupper($filtroNome)."%";
                                $textoQuery .= " AND UPPER(U.nome) LIKE :filtronome ";
                            }
                            if(isset($filtroEmail) && mb_strlen($filtroEmail) > 0){
                                $filtroEmail  =  "%".mb_strtoupper($filtroEmail)."%";
                                $textoQuery .= " AND UPPER(U.email) LIKE :filtroemail ";
                            }
                            if(isset($filtroRegistro) && mb_strlen($filtroRegistro) > 0){
                                $filtroRegistro =  "%".mb_strtoupper($filtroRegistro)."%";
                                $textoQuery    .= " AND UPPER(A.numeroInscricao) LIKE :filtroinsc ";
                            }
                            if(isset($filtroStatus) && mb_strlen($filtroStatus) > 0){
                                $textoQuery .= " AND A.status = :filtrostatus ";
                            }
                        }

						$textoQuery    .= " ORDER BY U.nome ASC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam("idcidade", $idCidade, PDO::PARAM_INT);
                        $query->bindParam("etapa", $etapa, PDO::PARAM_INT);

                        // seta os parâmetro necessários para exacutar a filtragem de dados
                        if(isset($_GET["filtro-nome"]) || isset($_GET["filtro-registro"]) ||
                           isset($_GET["filtro-email"])|| isset($_GET["filtro-status"])){
                            if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                                $query->bindParam(":filtronome", $filtroNome);
                            }
                            if(isset($filtroEmail) && mb_strlen($filtroEmail) > 0){
                                $query->bindParam(":filtroemail", $filtroEmail);
                            }
                            if(isset($filtroRegistro) && mb_strlen($filtroRegistro) > 0){
                                $query->bindParam(":filtroinsc", $filtroRegistro);
                            }
                            if(isset($filtroStatus) && mb_strlen($filtroStatus) > 0){
                                $query->bindParam(":filtrostatus", $filtroStatus);
                            }
                        }

                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $resultado = '<div class="flip-table"> <table class="table" id="alunos">
                            <th></th>
                            <th style="font-weight: bold">Registro do aluno</th>
                            <th style="font-weight: bold">Nome do aluno</th>
                            <th style="font-weight: bold">Status</th>
                            <th style="font-weight: bold">Email</th>
                            <th style="font-weight: bold">CPF do aluno</th>
                            <th style="font-weight: bold">Visualizar pagamentos</th>';

                        $numAlunos = 0;

                        while ($linha = $query->fetch()){
                            if(!is_null($linha['aprovado'])) {
                    ?>
                    <script> $("#btn-fechar").prop("disabled",true).toggleClass('disabled'); </script>
                    <?php
                            }

                            // formatamos o CPF para exibição
                            $cpfOriginal = str_split($linha["cpf"]);
        
                            $cpf  = implode("", array_slice($cpfOriginal, 0, 3)) . ".";
                            $cpf .= implode("", array_slice($cpfOriginal, 3, 3)) . ".";
                            $cpf .= implode("", array_slice($cpfOriginal, 6, 3)) . "-";
                            $cpf .= implode("", array_slice($cpfOriginal, 9, 2));
                            $cpf  = htmlspecialchars($cpf);

                            $resultado .= '
                        <tr>

                            <td class=\"selc\">
                                <input type="checkbox" name="inscricoes[]"
                                value="'.$linha['numeroInscricao'].'"> </td>
                            <td>' . htmlspecialchars($linha['numeroInscricao']). '</td>
                            <td>' . htmlspecialchars($linha['nome']) .'</td>
                            <td>' . ($linha['status'] == "preinscrito" ?
                                        "Pré-inscrito" : ucfirst(htmlspecialchars($linha['status']))) .'</td>
                            <td>' . htmlspecialchars($linha['email']) .'</td>
                            <td>' . $cpf .'</td>
                            <td>
                                <a href="gerenciar_pagamentos_aluno.php?id='.$linha["numeroInscricao"].'" >
                                    <i class="fa fa-money sucesso"></i>
                                </a>
                            </td>
                        </tr>
                            ';
                            $numAlunos++;
                        }

                        $resultado .= "</table> </div>";

                        if($numAlunos == 0){
                    ?>

                    <!-- removemos a opção de imprimir a lista de chamada e fechar turma -->
                    <script> $("#btn-imprimir").remove(); $("#btn-fechar").remove(); </script>

                    <?php
                            $resultado = "<b>Nenhum aluno matrículado nessa cidade nessa etapa.</b>";
                        }
                        echo $resultado;

                        if($numAlunos) {
                            echo "<b>" . $numAlunos . " aluno";
                            if($numAlunos != 1) echo "s";
                            echo " matriculado";
                            if($numAlunos != 1) echo "s";
                            echo " nessa turma</b>";
                        }
                    ?>

                    <a href="#" class="btn btn-primary pull-right" data-toggle="modal" data-target="#modal-email"
                        id="sendTodos">
                        <p>Enviar e-mail para todos</p>
                    </a>
                    <a href="#" class="btn btn-primary pull-right" data-toggle="modal" data-target="#modal-email"
                        id="sendSelecionados" style="margin-right:2em; display:none">
                        <p>Enviar e-mail para os selecionados</p>
                    </a>

                    <br>

                </section>
            </div>
        </div>
        <div class="modal fade" id="modal-fecha-turma" tabindex="-1" role="dialog"
             aria-labelledby="modal-fecha-turma" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Fechar turma</h4>
                    </div>
                    <div class="modal-body">
                        <h4>Tem certeza que deseja fechar essa turma?
                            <br><span style="color: #F00; font-weight: bold">Não será
                                possível modificar os dados dessa turma após esse
                                processo!</span>
                    </h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                        <a href="#" class="btn btn-danger danger">Sim</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal-email" tabindex="-1" role="dialog"
             aria-labelledby="modal-email" aria-hidden="true">
             <form method="POST" action="rotinas/gerenciar_email.php" id="email" name="email">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Email para alunos</h4>
                        </div>
                        <div class="modal-body">
                            <label for="title">Título do e-mail :</label>
                            <input type='text' class="form-control" 
                                name="title" id="title" placeholder="Título" required>

                            <br>
                            <label for="conteudo">Conteúdo do e-mail :</label>
                            <br>
                            <textarea name="conteudo" id="conteudo" 
                            class="form-control" 
                            cols="100"
                            rows="10"
                            placeholder="Mensagem"
                            required></textarea>
                        </div>
                        <input type="hidden" id="sendType" name="sendType" value="todos">
                        <input type="hidden" id="url-send" name="url-send">
                        <input type="hidden" id="vetGet" name="vetGet" value=<?= json_encode($_GET) ?>>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" >Enviar</button>
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
            window.location.href = "index.php";
        </script>
        <?php
                die();
            }

            include("modulos/rodape.php");
        ?>
    </body>
</html>
