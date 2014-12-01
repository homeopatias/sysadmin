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


                // esconde inputs de busca

                $("#filtro-nome").hide();
                $("#filtro-registro").hide();

                // alterna campos de texto com campos de input
                $("#label-nome").click(function(){
                    $(this).hide();
                    $("#filtro-nome").show(300);
                    $("#filtro-nome").focus();
                });

                $("#label-registro").click(function(){
                    $(this).hide();
                    $("#filtro-registro").show(300);
                    $("#filtro-registro").focus();
                });

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-nome").val("");
                    $("#filtro-registro").val("");
                    atualizaPagina();
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
                        $mensagem = isset($_GET['mensagem']) ? $_GET['mensagem'] : false;
                        $sucesso  = isset($_GET['sucesso']) ? $_GET['sucesso'] : false;
                        if(mb_strlen($mensagem, 'UTF-8') !== 0 && !$sucesso){
                            echo "<br><br><p class=\"warning\">$mensagem</p>";
                        }
                        if($sucesso){
                            echo "<br><br><p class=\"sucesso\">$mensagem</p>";
                        }
                    ?>
                    <a href=<?= "\"impressao_chamada.php?etapa=" . $etapa ."\"" ?>
                       target="_blank" class="pull-right" style="text-decoration:none" id="btn-imprimir">
                        <b>Lista de chamada para impressão &nbsp;</b>
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

                        $textoQuery  = "SELECT U.nome, U.cpf, A.numeroInscricao,
                                        M.aprovado FROM Matricula M INNER JOIN Cidade C 
                                        ON C.idCidade = M.chaveCidade INNER JOIN Aluno A ON 
                                        M.chaveAluno = A.numeroInscricao INNER JOIN Usuario U ON 
                                        U.id = A.idUsuario WHERE 
                                        C.idCidade = :idcidade AND M.etapa = :etapa";

                        // Se algum filtro foi repassado, altera o query para filtrar
                        $filtroRegistro = $filtroNome = false;
                        if(isset($_GET["filtro-nome"]) || isset($_GET["filtro-registro"])){

                            $filtroNome     =  htmlspecialchars($_GET["filtro-nome"]);
                            $filtroRegistro =  htmlspecialchars($_GET["filtro-registro"]);

                            if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                                $filtroNome  =  "%".mb_strtoupper($filtroNome)."%";
                                $textoQuery .= " AND UPPER(U.nome) LIKE :filtronome ";
                            }            
                            if(isset($filtroRegistro) && mb_strlen($filtroRegistro) > 0){
                                $filtroRegistro =  "%".mb_strtoupper($filtroRegistro)."%";
                                $textoQuery    .= " AND UPPER(A.numeroInscricao) LIKE :filtroinsc ";
                            }
                        }

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam("idcidade", $idCidade, PDO::PARAM_INT);
                        $query->bindParam("etapa", $etapa, PDO::PARAM_INT);

                        // seta os parâmetro necessários para exacutar a filtragem de dados
                        if(isset($_GET["filtro-nome"]) || isset($_GET["filtro-registro"])){
                            if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                                $query->bindParam(":filtronome", $filtroNome);
                            }
                            if(isset($filtroRegistro) && mb_strlen($filtroRegistro) > 0){
                                $query->bindParam(":filtroinsc", $filtroRegistro);
                            }
                        }

                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $resultado = '<div class="flip-table"> <table class="table">
                            <th style="font-weight: bold">Nº de inscrição</th>
                            <th style="font-weight: bold">Nome do aluno</th>
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
                            <td>' . $linha['numeroInscricao'] . '</td>
                            <td>' . $linha['nome'] .'</td>
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
                    ?>

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