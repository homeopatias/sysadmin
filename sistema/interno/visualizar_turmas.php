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
                // que a cidade ou a etapa for mudada
                $("#cidade").change( function(){ $(this).parent().submit() });
                $("#etapa").change( function(){ $(this).parent().submit() });

                // passa os dados do href para o modal de confirmação de fechamento
                // de turma quando necessário
                $("#modal-fecha-turma").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                });
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

                $idCidade = false;
                if(isset($_GET["cidade"])){
                    if(preg_match("/^[0-9]*$/", $_GET["cidade"])){
                        $idCidade = htmlspecialchars($_GET["cidade"]);
                    }else{
                        $mensagem = "Cidade inválida!";
                        $idCidade = -1;
                    }
                }

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
                                Administrador A WHERE C.ano = YEAR(NOW()) AND U.id = ? AND
                                A.nivel = 'coordenador' AND C.idCoordenador = A.idAdmin AND A.idAdmin = ?
                                ORDER BY nome DESC";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1,$coordenadorId);
                $query->bindParam(2,$coordenadorIdAdmin);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $cidades = array();

                while ($linha = $query->fetch()){
                    // caso nenhuma cidade tenha sido recebida ou seja recebido um id de cidade
                    // inválido, listamos os alunos da primeira cidade (ordem alfabética)
                    if($idCidade == -1 || !$idCidade){
                        $idCidade = htmlspecialchars($linha["idCidade"]);
                    }

                    // caso nenhuma etapa tenha sido recebida, listamos os alunos da
                    // primeira etapa
                    if(!$etapa){
                        $etapa = 1;
                    }

                    $cidades[] = array("nome" => htmlspecialchars($linha["nome"]),
                                       "UF"   => htmlspecialchars($linha["UF"]),
                                       "id"   => htmlspecialchars($linha["idCidade"]));
                }
        ?>

        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h2 style="font-weight:bold; display:inline">Turmas de <?= date("Y") ?></h2>
                    <?php
                        $mensagem = $_GET['mensagem'];
                        $sucesso  = $_GET['sucesso'];
                        if(mb_strlen($mensagem, 'UTF-8') !== 0 && !$sucesso){
                            echo '<p class="warning">$mensagem</p>';
                        }
                        if($sucesso){
                            echo '<p class="sucesso">$mensagem</p>';
                        }
                    ?>
                    <a href=<?= "\"impressao_chamada.php?cidade=". $idCidade . "&etapa=" . $etapa ."\"" ?>
                       target="_blank" class="pull-right" style="text-decoration:none" id="btn-imprimir">
                        <b>Lista de chamada para impressão &nbsp;</b>
                        <i class="fa fa-lg fa-print"></i>
                    </a>
                    <br><br>
                    <a class="pull-right btn btn-primary" id="btn-fechar" data-toggle="modal"
                       data-target="#modal-fecha-turma"
                       data-href=<?= "\"rotinas/fechar_turma.php?idCidade=" .
                                     $idCidade . "&etapa=" . $etapa . "\""?>>
                        Fechar turma
                    </a>
                    <label for="ano">
                        Selecione a cidade e a etapa:
                    </label><br>
                    <form style="width: 300px" method="GET" action="visualizar_turmas.php ">
                        <select style="display:inline; width: 200px !important"
                                class="form-control input-sm" id="cidade" name="cidade">
                            <?php foreach ($cidades as $cidade) {
                                if($idCidade == $cidade["id"]){
                                    echo "<option value=" . $cidade["id"] .
                                         " selected>" . $cidade["nome"] . "/" . $cidade["UF"] . "</option>";
                                }else{
                                    echo "<option value=" . $cidade["id"] .
                                         ">" . $cidade["nome"] . "/" . $cidade["UF"] . "</option>";
                                }
                            } ?>
                        </select>
                        <select style="display:inline; width: 50px !important"
                                class="form-control input-sm" id="etapa" name="etapa">
                            <option value="1" <?php if($etapa == 1) echo "selected" ?>>1</option>
                            <option value="2" <?php if($etapa == 2) echo "selected" ?>>2</option>
                            <option value="3" <?php if($etapa == 3) echo "selected" ?>>3</option>
                            <option value="4" <?php if($etapa == 4) echo "selected" ?>>4</option>
                        </select>
                    </form>
                    <br><br>
                    <?php

                        $textoQuery  = "SELECT U.nome, U.cpf, A.numeroInscricao, M.idMatricula,
                                        M.aprovado FROM Matricula M INNER JOIN Cidade C 
                                        ON C.idCidade = M.chaveCidade INNER JOIN Aluno A ON 
                                        M.chaveAluno = A.numeroInscricao INNER JOIN Usuario U ON 
                                        U.id = A.idUsuario WHERE 
                                        C.idCidade = ? AND M.etapa = ? AND C.idCoordenador = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idCidade, PDO::PARAM_INT);
                        $query->bindParam(2, $etapa, PDO::PARAM_INT);
                        $query->bindParam(3, $coordenadorId, PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $resultado = '<div class="flip-table"> <table class="table">
                            <th style="font-weight: bold">Registro do aluno</th>
                            <th style="font-weight: bold">Número de matrícula</th>
                            <th style="font-weight: bold">Nome do aluno</th>
                            <th style="font-weight: bold">CPF do aluno</th>
                            <th style="font-weight: bold">Visualizar pagamentos</th>';

                        $numAlunos = 0;

                        while ($linha = $query->fetch()){
                            if(!is_null($linha['aprovado'])) {
                    ?>
                    <script> $("#btn-fechar").prop("disabled",true); </script>
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
                            <td>' . $linha['idMatricula'] . '</td>
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

                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "            <p class=\"warning\">$mensagem</p>";
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