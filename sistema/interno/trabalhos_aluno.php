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
        <script>
            $(document).ready(function(){
                // passa os dados para o modal de visualização de trabalho
                // quando necessário
                $("#modal-descricao").on('show.bs.modal', function(e) {
                    $(this).find('#nome-def-trabalho').text(
                        $(e.relatedTarget).parent().siblings('.titulo').text()
                    );
                    $(this).find('#desc-def-trabalho').html(
                        $(e.relatedTarget).data('descricao')
                    );
                });

                // passa os dados para o modal de visualização de trabalho enviado
                // quando necessário
                $("#modal-envio").on('show.bs.modal', function(e) {
                    $(this).find('#nome-trabalho').text(
                        $(e.relatedTarget).parent().siblings('.titulo').text()
                    );
                    $(this).find('#data-entrega-enviado').text(
                        $(e.relatedTarget).data('entrega')
                    );
                    $(this).find('#trabalho-enviado').attr('href',
                        "trabalhos/" + <?= "\"" . date("Y") . "\"" ?> + "/" + 
                        $(e.relatedTarget).data('inscricao-aluno') + "/" +
                        $(e.relatedTarget).data('arquivo')
                    );
                    var nota        = $(e.relatedTarget).data('nota'),
                        comentario  = $(e.relatedTarget).data('comentario');

                    //passa a dados para o modal de reenvio
                    $("#modal-reenviar-trabalho").find("#chaveDefTrabalho").val(
                        $(e.relatedTarget).data("def-trabalho")
                    );

                    var dataLimite  = $(e.relatedTarget).data('limite-entrega');
                    
                    //Transforma a data lida em um elemento date
                    var reg = new RegExp("/", 'g');
                    dataLimite      = dataLimite.replace(reg,"-");
                    dataLimite = new Date(dataLimite);


                    if(nota !== "") {
                        $(this).find('#nao-corrigido').hide();
                        $(this).find('#div-reenvio-trabalho').hide();
                        $(this).find('#informacoes-nota').show();
                        $(this).find('#informacoes-nota #trabalho-nota').text(
                            nota
                        );
                        if(comentario !== "") {
                            $(this).find('#informacoes-nota #trabalho-comentario').html(
                                comentario
                            );
                        } else {
                            $(this).find('#informacoes-nota #trabalho-comentario').text(
                                "O professor não fez nenhum comentário em relação ao trabalho enviado."
                            );
                        }
                    } else {
                        $(this).find('#informacoes-nota').hide();

                        var dataAtual = new Date();
                        if(dataAtual.getTime() < dataLimite.getTime()){
                            $(this).find('#div-reenvio-trabalho').show();
                        }
                        else{
                            $(this).find('#div-reenvio-trabalho').hide();
                        }
                    }
                });

                // passa os dados para o modal de envio de trabalho quando necessário
                $("#modal-enviar-trabalho").on('show.bs.modal', function(e) {
                    $(this).find('#chaveDefTrabalho').val(
                        $(e.relatedTarget).data('def-trabalho')
                    );
                });

                //----- muda o ano sendo visualizado se o input de ano mudar
                $("#ano").change(function(){
                    $("#muda-ano").submit();
                });

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

            include('modulos/navegacao.php');

            // mensagem a ser exibida acima da listagem de trabalhos, caso seja necessário
            $mensagem = '';

            // determina se houve um envio de trabalho bem sucedido
            $enviado = isset($_GET['sucesso']);

            if(isset($_GET["erro"])){
                $mensagem = $_GET['erro'];
            }

            // exibe trabalhos apenas para alunos logados e inscritos
            if(isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Aluno &&
                unserialize($_SESSION['usuario'])->getStatus() === 'inscrito'){

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

                // se o usuário chegou até aqui através de um formulário, envia o trabalho pronto
                if(isset($_POST['submit'])){

                    if ($_FILES['trabalho']['error'] != 0) {
                        // ocorreu algum erro no processo de upload do arquivo
                        die('Upload mal-sucedido. Erro ' . $_FILES['trabalho']['error']);
                    }

                    // tamanho máximo do arquivo em bytes
                    $tamanhoMaximo = 1024 * 1024 * 2; // 2MB

                    // vetor de extensões permitidas para os arquivos enviados
                    $extensoesPermitidas = array('pdf', 'doc', 'docx', 'rtf', 'ppt', 'pptx', 'odt',
                                                 'txt', 'zip', 'rar');

                    $trabalhoEnviado = $_FILES['trabalho'];

                    $extensao = mb_convert_case(
                                    pathinfo($trabalhoEnviado['name'], PATHINFO_EXTENSION),
                                    MB_CASE_LOWER, "UTF-8"
                                );

                    $tamanhoValido  = $trabalhoEnviado['size'] <= $tamanhoMaximo &&
                                      $trabalhoEnviado['size'] != 0;
                                      
                    $extensaoValida = in_array($extensao, $extensoesPermitidas);

                    $numeroInscricao  = $_POST["numeroInscricao"];
                    $chaveDefTrabalho = $_POST["chaveDefTrabalho"];

                    $inscricaoValida   = isset($numeroInscricao) &&
                                        preg_match("/^[0-9]+$/", $numeroInscricao);
                    $defTrabalhoValido = isset($chaveDefTrabalho) &&
                                        preg_match("/^[0-9]+$/", $chaveDefTrabalho);
                    $trabalhoEnviado = false;

                    $textoQuery  = "SELECT idTrabalho,nota FROM Trabalho WHERE chaveAluno = ? AND 
                                    chaveDefinicao = ?";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $numeroInscricao);
                    $query->bindParam(2, $chaveDefTrabalho);
                    $sucesso = $query->execute();

                    $trabalhoCorrigido = false;
                    if($linha =$query->fetch()){
                        // esse aluno já enviou esse trabalho
                        $trabalhoEnviado = true;
                        if($linha["nota"] != ""){
                            $trabalhoCorrigido = true;
                        }
                    }

                    // verificamos se o aluno ainda está dentro do prazo de envio do trabalho
                    $dentroDoPrazo = false;

                    $textoQuery  = "SELECT dataLimite FROM TrabalhoDefinicao 
                                    WHERE idDefTrabalho = ?";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $chaveDefTrabalho);
                    $sucesso = $query->execute();

                    // adicionamos um dia na data limite, pois a princípio o aluno
                    // pode enviar o trabalho no dia final, até as 23:59
                    $dataMaxima = strtotime($query->fetch()["dataLimite"] . " + 1 day");
                    if($dataMaxima > strtotime(date("Y-m-d H:i:s"))){
                        $dentroDoPrazo = true;
                    }

                    //Testa se o aluno que deseja reenviar o trabalho é o dono do trabalho
                    $usuarioValido = false;
                    if(unserialize($_SESSION["usuario"])->getNumeroInscricao() == $numeroInscricao){
                        $usuarioValido = true;
                    }

                    $reenvioTrabalho = isset($_POST["reenvioDeTrabalho"])? true: false;
                    if($reenvioTrabalho && $dentroDoPrazo &&
                        $extensaoValida && $tamanhoValido && $inscricaoValida && 
                        $defTrabalhoValido && $usuarioValido && !$trabalhoCorrigido){

                        //pegamos a extensão do arquivo salvo
                        $textoQuery  = "SELECT extensao
                                        FROM Trabalho 
                                        WHERE chaveAluno = ?";
                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $numeroInscricao);
                        $query->execute();

                        if($linha = $query->fetch()){
                            $extensaoTrabalhoSalvo = $linha["extensao"];
                        }

                        // Usamos as TRANSACTIONs do MySql para garantir que caso haja
                        // algum erro, as tabelas continuem consistentes
                        $conexao->beginTransaction();

                        $textoQuery  = "UPDATE Trabalho 
                                        SET dataEntrega = NOW(),extensao = :extensao
                                        WHERE chaveAluno = :inscricao";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(":extensao"  , $extensao);
                        $query->bindParam(":inscricao" , $numeroInscricao);
                        $sucesso = $query->execute();


                        if($sucesso){
                            //remove o trabalho anterior do banco e coloca o novo
                            if(unlink("trabalhos/".date("Y",$dataMaxima) . "/" . $numeroInscricao . "/" .
                                                   $chaveDefTrabalho . "." . $extensaoTrabalhoSalvo)){

                                


                                if (move_uploaded_file($_FILES['trabalho']['tmp_name'], "trabalhos/" .
                                                   date("Y") . "/" . $numeroInscricao . "/" .
                                                   $chaveDefTrabalho . "." . $extensao)) {

                                //conseguimos salvar o trabalho, confirmamos o envio
                                $conexao->commit();

                                // redireciona o usuário para essa mesma página, avisando através
                                // do GET que o envio foi bem sucedido
                                // OBS: O redirecionamento é feito para que não ocorram problemas
                                //      de reenvio de formulário
                                ?>
                                <meta http-equiv="refresh"
                                  content=<?= "\"0; url=trabalhos_aluno.php?reenvio=true" . 
                                              (mb_strlen($mensagem) > 0 ? "&erro=$mensagem" : "") .
                                              "\"" ?>>
                                <script type="text/javascript">
                                window.location = <?= "\"trabalhos_aluno.php?reenvio=true" . 
                                              (mb_strlen($mensagem) > 0 ? "&erro=$mensagem" : "") .
                                              "\"" ?>;

                                <?php
                                    die();

                                }
                            }  
                        }
                        else {
                                // houve uma falha ao salvar o arquivo, desfazemos
                                // as mudanças no banco de dados
                                $conexao->rollBack();

                                $mensagem = "Erro no armazenamento de arquivo";
                            }
                        
                    }

                    if($extensaoValida && $tamanhoValido && $inscricaoValida && $defTrabalhoValido &&
                      !$trabalhoEnviado && !$reenvioTrabalho) {

                        // Usamos as TRANSACTIONs do MySql para garantir que caso haja
                        // algum erro, as tabelas continuem consistentes
                        $conexao->beginTransaction();

                        $textoQuery  = "INSERT INTO Trabalho (chaveAluno, dataEntrega, chaveDefinicao, 
                                        extensao) VALUES (?, NOW(), ?, ?)";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $numeroInscricao);
                        $query->bindParam(2, $chaveDefTrabalho);
                        $query->bindParam(3, $extensao);
                        $sucesso = $query->execute();

                        if($sucesso) {
                            // descobrimos o id do trabalho que acabamos de inserir
                            // para poder criar um arquivo com mesmo nome
                            $idTrabalho = $conexao->lastInsertId();

                            // checamos se os diretórios para esse ano e esse aluno já existem
                            // se não existirem, criamos esses diretórios
                            if (!is_dir("trabalhos/". date("Y"))) {
                                  mkdir("trabalhos/". date("Y"));
                            }
                            if (!is_dir("trabalhos/". date("Y") . "/" . $numeroInscricao)) {
                                  mkdir("trabalhos/". date("Y") . "/" . $numeroInscricao);
                            }

                            // criamos o arquivo do trabalho no sistema
                            // o trabalho será salvo dentro da pasta com o número de inscrição
                            // do aluno, que estará dentro de uma pasta com o ano atual
                            if (move_uploaded_file($_FILES['trabalho']['tmp_name'], "trabalhos/" .
                                                   date("Y") . "/" . $numeroInscricao . "/" .
                                                   $idTrabalho . "." . $extensao)) {
                                // conseguimos salvar o trabalho, confirmamos o envio
                                $conexao->commit();

                                // redireciona o usuário para essa mesma página, avisando através
                                // do GET que o envio foi bem sucedido
                                // OBS: O redirecionamento é feito para que não ocorram problemas
                                //      de reenvio de formulário

                                // caso esteja fora do prazo, avisamos o aluno que o trabalho valerá
                                // apenas 80% da nota
                                if (!$dentroDoPrazo) {
                                    $mensagem = "O trabalho foi enviado após o prazo, portanto valerá
                                                 apenas 80% da nota";
                                }
                            ?>

                            <meta http-equiv="refresh"
                                  content=<?= "\"0; url=trabalhos_aluno.php?sucesso=true" . 
                                              (mb_strlen($mensagem) > 0 ? "&erro=$mensagem" : "") .
                                              "\"" ?>>
                            <script type="text/javascript">
                                window.location = <?= "\"trabalhos_aluno.php?sucesso=true" . 
                                              (mb_strlen($mensagem) > 0 ? "&erro=$mensagem" : "") .
                                              "\"" ?>;
                            </script>

                            <?php
                                die();
                            } else {
                                // houve uma falha ao salvar o arquivo, desfazemos
                                // as mudanças no banco de dados
                                $conexao->rollBack();

                                $mensagem = "Erro no armazenamento de arquivo";
                            }
                        } else {
                            $mensagem = "Erro de banco de dados";
                        }

                    } else if(!$extensaoValida) {
                        $mensagem = mb_strlen($extensao, 'UTF-8') > 0 ? 
                                    "Não é permitido o envio de arquivos " . $extensao :
                                    "Não é permitido o envio de arquivos sem tipo";
                    } else if(!$tamanhoValido) {
                        $mensagem = "O tamanho do arquivo deve ser menor que 2MB";
                    } else if(!$inscricaoValida) {
                        $mensagem = "Aluno inválido";
                    } else if(!$defTrabalhoValido) {
                        $mensagem = "Trabalho inválido";
                    } else if($trabalhoEnviado && !$reenvioTrabalho) {
                        $mensagem = "Você já fez o envio desse trabalho, não pode enviá-lo novamente";
                    } else if($reenvioTrabalho && !$usuarioValido){
                        $mensagem = "Identificação de usuário inválida, tente novamente";
                    } else if($reenvioTrabalho && $trabalhoCorrigido){
                        $mensagem = "ERRO: trabalho escolhido ja foi corrigido!";
                    }

                }


                // descobrimos se o aluno está matriculado atualmente
                // se não estiver, redirecionamos para o index
                $matriculado = false;
                $etapa = -1;

                $textoQuery  = "SELECT M.etapa FROM Matricula M, Cidade C 
                                WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                AND C.ano = YEAR(CURDATE())";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1, unserialize($_SESSION["usuario"])->getNumeroInscricao(),
                                  PDO::PARAM_INT);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                if($linha = $query->fetch()){
                    $matriculado = true;
                    $etapa = htmlspecialchars($linha["etapa"]);
                }else{
                    $erro = "Aluno não matriculado no ano atual";
                ?>

                <!-- redireciona o usuário para o index.php -->
                <meta http-equiv="refresh" content=<?= "\"0; url=index.php?mensagem=$erro\"" ?>>
                <script type="text/javascript">
                    window.location = <?= "\"index.php?mensagem=$erro\"" ?>;
                </script>

                <?php
                    die();
                }

                // Verifica se ele qual ano ele quer visualizar
                $ano = isset($_GET["ano"]) ? $_GET["ano"] : date("Y");

                // Armazena o ano que será mostrado na tela
                $anoDesejado = date("Y");

                //Busca os anos anteriores do aluno
                $anos = [];
                $textoQuery  = "SELECT C.ano, M.etapa FROM Matricula M, Cidade C 
                                WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1, unserialize($_SESSION["usuario"])->getNumeroInscricao(),
                                  PDO::PARAM_INT);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                while($linha = $query->fetch()){
                    if($ano == $linha["ano"]){
                        $etapa       = $linha["etapa"];
                        $anoDesejado = $linha["ano"];
                    }
                    if(!in_array($linha["ano"], $anos) && $linha["ano"] <= date("Y")){
                        $anos[] = $linha["ano"];
                    }
                }

                $textoQuery  = "SELECT TD.idDefTrabalho, TD.titulo, TD.descricao, TD.dataLimite, 
                                UNIX_TIMESTAMP(TD.dataLimite) as data, UNIX_TIMESTAMP(T.dataEntrega) 
                                as entrega, T.nota, T.idTrabalho, T.extensao, T.comentarioProfessor 
                                FROM TrabalhoDefinicao TD LEFT JOIN Trabalho T ON T.chaveDefinicao = 
                                TD.idDefTrabalho AND T.chaveAluno = ? WHERE YEAR(TD.dataLimite) = 
                                ? AND TD.etapa = ? ORDER BY TD.idDefTrabalho DESC";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1, unserialize($_SESSION["usuario"])->getNumeroInscricao(),
                                  PDO::PARAM_INT);
                $query->bindParam(2, $ano);
                $query->bindParam(3, $etapa, PDO::PARAM_INT);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $numeroRegistros = 0;
                $tabela = "";

                while ($linha = $query->fetch()){
                    // listamos os dados de cada trabalho
                    $tabela .= "<tr>";
                    $tabela .= "    <td class=\"titulo\">";
                    $tabela .= htmlspecialchars($linha["titulo"])   ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= date("d/m/Y", $linha["data"])    ."</td>";

                    $tabela .= "    <td>";
                    if(is_null($linha["entrega"])) {
                        $tabela .= "<i class=\"fa fa-times warning\">";
                    } else {
                        $tabela .= "<i class=\"fa fa-check sucesso\">";
                    }
                    $tabela .= "</td>";

                    $tabela .= "    <td>";
                    if(is_null($linha["nota"])) {
                        $tabela .= "<i class=\"fa fa-times warning\">";
                    } else {
                        $tabela .= "<i class=\"fa fa-check sucesso\">";
                    }
                    $tabela .= "</td>";

                    if(!is_null($linha["nota"])) {
                        $tabela .= "    <td>";
                        $tabela .= htmlspecialchars($linha["nota"])   ."</td>";
                    } else {
                        $tabela .= "    <td>Não corrigido</td>";
                    }

                    if(!is_null($linha["idTrabalho"])){
                        $tabela .= "    <td><a data-entrega=\"" . date("d/m/Y à\s H:i", $linha["entrega"]) . "\"";
                        $tabela .= " data-arquivo=\"" . htmlspecialchars($linha["idTrabalho"]) . ".";
                        $tabela .= htmlspecialchars($linha["extensao"]) . "\"";
                        $tabela .= " data-nota=\"" . htmlspecialchars($linha["nota"]) . "\"";
                        $tabela .= " data-comentario=\"" . nl2br(htmlspecialchars($linha["comentarioProfessor"]))."\"";
                        $tabela .= " data-inscricao-aluno=\"" . unserialize($_SESSION["usuario"])->getNumeroInscricao()."\"";
                        $tabela .= " href=\"#\" data-toggle=\"modal\"";
                        $tabela .= "data-limite-entrega=\"";
                            $dataMaxima = strtotime($linha["dataLimite"] . " + 1 day");//prepara data max
                        $tabela .= date("m/d/Y",$dataMaxima)."\"";
                        $tabela .= " data-def-trabalho=\"" . htmlspecialchars($linha["idDefTrabalho"]) . "\"";
                        $tabela .= " data-target=\"#modal-envio\">";
                        $tabela .= "<i class=\"fa fa-mail-forward\"></i></a></td>";
                    }else{
                        // checamos se o aluno ainda está dentro do prazo para enviar o trabalho

                        // adicionamos um dia na data limite, pois a princípio o aluno
                        // pode enviar o trabalho no dia final, até as 23:59
                        $dataMaxima = strtotime($linha["dataLimite"] . " + 1 day");

                        $tabela .= "    <td><a";
                        if($dataMaxima < strtotime(date("Y-m-d H:i:s"))) {
                            $tabela .= " style=\"font-weight: bold\"";
                        }
                        $tabela .= " data-def-trabalho=\"" . htmlspecialchars($linha["idDefTrabalho"]) . "\"";
                        $tabela .= " href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-enviar-trabalho\">";
                        $tabela .= "<i class=\"fa fa-upload\"></i> Enviar";
                        if($dataMaxima < strtotime(date("Y-m-d H:i:s"))) {
                            $tabela .= " com atraso";
                        }
                        $tabela .= "</a></td>";

                    }

                    $tabela .= "    <td><a data-descricao=\"";
                    $tabela .= nl2br(htmlspecialchars($linha["descricao"]));
                    $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                    $tabela .= " data-target=\"#modal-descricao\">";
                    $tabela .= "<i class=\"fa fa-file-text\"></i></a></td>";

                    $tabela .= "</tr>";

                    $numeroRegistros++;
                }          

                //prepara select com os anos que o aluno estudou até o momento
                $selectAnos = "";
                if( sizeof( $anos ) > 0){
                    $selectAnos .= "<select id='ano' name='ano'>";
                    foreach($anos as $option){
                        $selectAnos .= "<option value='".$option."'";
                        $selectAnos .= " ". $ano == $option ? "selected = selected" : "";
                        $selectAnos .= ">".$option."</option>";
                    }
                    $selectAnos .= "</select>";
                }
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>
                        Trabalhos <?= isset($etapa) ? " - " . $etapa . "ª etapa" : "" ?>
                                  <?= " - " . $anoDesejado ?>
                    </h1><br>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                        if($enviado){
                            echo "<p class=\"sucesso\">Trabalho enviado com sucesso!</p>";
                        }
                        $reenvio = $_GET["reenvio"];
                        if($reenvio){
                            echo "<p class=\"sucesso\">Trabalho atualizado com sucesso!</p>";
                        }
                    ?>
                    <br>
                    <br>
                    <?php 
                        if(sizeof($selectAnos) >0){
                            echo ("<form method=\"GET\" 
                                action=\"trabalhos_aluno.php\" 
                                id=\"muda-ano\">
                                        <label for='ano'>Visualizar ano: </label>".
                                        $selectAnos.
                                    
                                  "</form>");
                        }

                    if($numeroRegistros !== 0){ ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="trabalhos">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="350px">Nome do trabalho</th>
                                        <th width="200px">Data limite para envio</th>
                                        <th>Enviado?</th>
                                        <th>Corrigido?</th>
                                        <th width="100px">Nota</th>
                                        <th width="150px">Detalhes do envio</th>
                                        <th width="150px">Descrição do trabalho</th>
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
                    <?php   } ?>
                    <br>
                    <b class="warning">Envie sempre seus trabalhos antes da data de entrega!
                        Trabalhos enviados após a data limite valerão apenas 80% da nota! </b>
                    <?php
                        } // $numeroRegistros !== 0
                        else {
                    ?>
                    <b>Nenhum trabalho até o momento</b><br>
                    <?php
                        }
                    ?>
                    
                </section>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para visualização de descrição de trabalho -->
        <div class="modal fade" id="modal-descricao" tabindex="-1" role="dialog"
             aria-labelledby="modal-descricao" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title" id="nome-def-trabalho" style="font-weight:bold"></h4>
                    </div>
                    <div class="modal-body">
                        <p id="desc-def-trabalho"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para visualização de detalhes do trabalho enviado -->
        <div class="modal fade" id="modal-envio" tabindex="-1" role="dialog"
             aria-labelledby="modal-envio" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title" style="font-weight:bold" id="nome-trabalho"></h4>
                    </div>
                    <div class="modal-body">
                        <b>Entregue no dia <span id="data-entrega-enviado"></span></b>
                        <br><br>
                        <a href="#" id="trabalho-enviado" style="text-decoration: none"
                           target="_blank">
                            <i class="fa fa-save"></i> Fazer download do trabalho
                        </a>
                        <br><br>
                        <div id="informacoes-nota">
                            <b>Nota do trabalho: </b><span id="trabalho-nota"></span>
                            <br><br>
                            <b>Comentário do professor: </b><br><br>
                            <p id="trabalho-comentario"></p>
                        </div>
                        <b id="nao-corrigido">Esse trabalho não foi corrigido ainda.</b>
                        <div id="div-reenvio-trabalho">
                            <br><br>
                            <a id="reenviarTrabalho" href="#" data-toggle="modal" data-target="#modal-reenviar-trabalho">
                                <i class="fa fa-mail-forward"></i> Reenviar Trabalho
                            </a>
                            <br><br>
                            <p style="padding='15px'">
                                <b class="warning">Trabalhos não corrigidos podem ser 
                                reenviados até a data limite  da definição de trabalho!</b>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para envio de trabalho -->
        <div class="modal fade" id="modal-enviar-trabalho" tabindex="-1" role="dialog"
             aria-labelledby="modal-enviar-trabalho" aria-hidden="true">
            <div class="modal-dialog">
                <form id="enviar-trabalho" action="trabalhos_aluno.php " method="POST" 
                      enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Enviar trabalho</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="numeroInscricao" id="numeroInscricao"
                                   value=<?= unserialize($_SESSION["usuario"])->getNumeroInscricao() ?>>
                            <input type="hidden" name="chaveDefTrabalho" id="chaveDefTrabalho">
                            <b>Tamanho máximo de arquivo permitido: 2MB</b><br>
                            <b>Formatos de arquivo permitidos: pdf, doc, docx, rtf, ppt,
                               pptx, odt, txt, zip, rar</b><br><br>
                            <input type="file" name="trabalho" style="width:100%"><br>
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
        <!-- popup "modal" do bootstrap para reenvio de trabalho -->
        <div class="modal fade" id="modal-reenviar-trabalho" tabindex="-1" role="dialog"
             aria-labelledby="modal-reenviar-trabalho" aria-hidden="true">
            <div class="modal-dialog">
                <form id="reenviar-trabalho" action="trabalhos_aluno.php " method="POST" 
                      enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Reenviar trabalho</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="numeroInscricao" id="numeroInscricao"
                                   value=<?= unserialize($_SESSION["usuario"])->getNumeroInscricao() ?>>
                            <input type="hidden" name="chaveDefTrabalho" id="chaveDefTrabalho">
                            <input type="hidden" name="reenvioDeTrabalho" id="reenvioDeTrabalho">
                            <b>Tamanho máximo de arquivo permitido: 2MB</b><br>
                            <b>Formatos de arquivo permitidos: pdf, doc, docx, rtf, ppt,
                               pptx, odt, txt, zip, rar</b><br><br>
                            <input type="file" name="trabalho" style="width:100%">
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
            } else if(isset($_SESSION['usuario']) &&
                      unserialize($_SESSION['usuario'])->getStatus() !== 'inscrito') {
                // redirecionamento para alunos que não estão inscritos
        ?>
        <!-- redireciona o usuário para o index.php com uma mensagem específica -->
        <meta http-equiv="refresh" content="0; url=index.php">
        <script type="text/javascript">
            window.location = "index.php?mensagem=Apenas alunos inscritos podem ver os trabalhos";
        </script>
        <?php
                die();
            }

            // redirecionamento para os outros casos
            else {
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