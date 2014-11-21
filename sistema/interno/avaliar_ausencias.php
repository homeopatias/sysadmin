<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Justificativas de ausência - Homeopatias.com</title>
        <script>
            var podeMudarPagina = true;
            $(document).ready(function(){

                // se mudou a quantidade de pessoas por página, atualiza
                $("#ipp").change(function(){
                    $("#pagina").val(0);
                    $("#pagina-ipp").val( $(this).val() );
                    atualizaPagina();
                });

                $("#ipp").hide();

                $("#label-ipp").click(function(){
                    $(this).hide();
                    $("#ipp").show(300);
                    $("#ipp").focus();
                });

                $("#ipp").blur(function(){
                    $(this).hide(300);
                    $("#label-ipp").show(300);   
                });

                // se clicou em anterior ou próxima muda a página da tabela
                $("#anterior").click(function(e){
                    if(!podeMudarPagina){
                        atualizaPagina();
                    }
                    var paginaAnterior = $("#pagina").val()-1;
                    if(paginaAnterior <0)
                        paginaAnterior = 0;
                    $("#pagina").val(paginaAnterior);
                    $("#form-filtro").submit();
                });

                $("#proxima").click(function(e){
                    if(!podeMudarPagina){
                        atualizaPagina();
                    }
                    var proximaPagina = $("#pagina").val();
                    proximaPagina = parseInt(proximaPagina) + 1
                    $("#pagina").val(proximaPagina);
                    $("#form-filtro").submit();
                });

                // ------------ Muda de página usando as setas do teclado
                $(window).keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == "37" && possuiPaginaAnterior && 
                    document.activeElement.tagName == "BODY" ){
                        
                        $("#anterior").trigger("click");
                    }
                    
                    else if(keycode == "39" && possuiProximaPagina && 
                         document.activeElement.tagName == "BODY" ){
                       
                        $("#proxima").trigger("click");
                    }
                });

                // passa os dados para o modal de avaliação de justificativa
                // quando necessário
                $("#modal-justificativa").on('show.bs.modal', function(e) {
                    $(this).find('#idAula').val($(e.relatedTarget).data('chaveaula'));
                    $(this).find('#idAluno').val($(e.relatedTarget).data('chavealuno'));
                    $(this).find('#justificativa').text(
                        $(e.relatedTarget).data('justificativa')
                    );
                });

                checaTamanhoTela();
            }); 

            // atualiza formulário com a busca
            function atualizaPagina(){
                $("#pagina").val(0);
                $("#form-filtro").submit();
            }

            // ---- Checa se tamanho minimo da tela é o tamanho minimo do css
            function checaTamanhoTela(){
                tamanhoTela = $(window).width();

                if (tamanhoTela < 700) {
                    $(".flip-scroll th").css("width","150px");
                }
            }

            // ---- Checa se ao redimencionar a tela atingiu o tamanho minimo da tela
            $(window).resize(function() {
                checaTamanhoTela();
            });
        </script>
    </head>
    <body>
        <?php

            include("modulos/navegacao.php");

            // mensagem a ser exibida acima da listagem de justificativas, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe notícias apenas para administradores logados
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

                // Cria conexão com o banco para uso ao longo da página
                $conexao = null;
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                // se recebemos dados de um formulário, aceitamos os recusamos uma
                // justificativa de ausência
                if (isset($_POST['submit'])) {
                    $aceito  = $_POST['submit'];
                    $idAula  = $_POST['idAula'];
                    $idAluno = $_POST['idAluno'];

                    if($aceito == 0 || $aceito == 1) {
                        $textoQuery  = "UPDATE Frequencia SET aprovacaoPendente = 0, presenca = ?
                                        WHERE chaveAula = ? AND chaveAluno = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $aceito);
                        $query->bindParam(2, $idAula);
                        $query->bindParam(3, $idAluno);                 
                        $sucesso = $query->execute();

                        if (!$sucesso) {
                            $mensagem = "Não foi possível avaliar essa justificativa";
                        } else {

                            $aluno = new Aluno("");
                            $aluno->setId($idAluno);
                            $aluno->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);

                            // enviamos um email avisando o aluno do status de sua justificativa
                            $emailAluno = $aluno->getEmail();
                            $nomeAluno = $aluno->getNome();
                            $numInscricao = $idAluno;
                            $assunto = "Homeopatias.com - Justificativa de ausência " . ($aceito ? "aceita" : "negada");
                            $msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la</b>";
                            $msg .= "<br><br>A justificativa de ausência do(a) aluno(a) " . $nomeAluno . " foi " . ($aceito ? "aceita" : "negada") . ".";
                            if (!$aceito) {
                                $msg .= "<br>Caso você acredite que houve algum erro de julgamento";
                                $msg .= " por parte do avaliador da sua justificativa, favor";
                                $msg .= " entrar em contato conosco.";
                            }
                            $msg .= "<br><br>Obrigado,<br>Equipe Homeobrás.";
                            $headers = "Content-type: text/html; charset=utf-8 " .
                                "From: Sistema Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
                                "Reply-To: noreply@homeopatias.com" . "\r\n" .
                                "X-Mailer: PHP/" . phpversion();
                            mail($emailAluno, $assunto, $msg, $headers);

                            // agora registramos no sistema uma notificação para o aluno
                            $textoNotificacao = "Sua justificativa de ausência foi " . ($aceito ? "aceita" : "negada") . ".";
                            if (!$aceito) {
                                $textoNotificacao .= "\nCaso você acredite que houve algum erro de julgamento";
                                $textoNotificacao .= " por parte do avaliador da sua justificativa, favor";
                                $textoNotificacao .= " entrar em contato conosco.";
                            }
                            $queryNotificacao = $conexao->prepare("INSERT INTO Notificacao 
                                                (titulo, texto, chaveAluno, lida) VALUES (?, ?, ?, 0)");
                            $dados = array("Justificativa de ausência " . ($aceito ? "aceita" : "negada"),
                                           $textoNotificacao, $numInscricao);
                            $queryNotificacao->execute($dados);
                        }
                    } else {
                        $mensagem = "Dados inválidos";
                    }
                }

                //--------------------------------------------------------------------

                // Prepara as variáveis necessárias para controlar a paginação
                $pagina = isset($_GET["pagina"]) ? htmlspecialchars($_GET["pagina"]) : 0;
                $pagina = (int)$pagina;

                $itemsPorPagina = isset($_GET["pagina-ipp"]) ? 
                                  htmlspecialchars($_GET["pagina-ipp"]) : 10;
                $itemsPorPagina = (int)$itemsPorPagina;

                $textoQuery  = "SELECT U.nome, UNIX_TIMESTAMP(Au.data) as data, F.justificativaAusencia,
                                F.chaveAula, F.chaveAluno FROM Frequencia F
                                INNER JOIN Aluno A ON A.numeroInscricao = F.chaveAluno
                                INNER JOIN Usuario U ON A.idUsuario = U.id
                                INNER JOIN Aula Au ON Au.idAula = F.chaveAula
                                WHERE F.aprovacaoPendente = 1 AND F.presenca = 0";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $numeroRegistros = $query->rowCount();

                $possuiProximaPagina = false;
                $contador = 0;
                $tabela = "";

                while ($linha = $query->fetch()){
                    if($contador != $itemsPorPagina){

                        // listamos os dados de cada noticia
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"nome-aluno\">";
                        $tabela .= htmlspecialchars($linha["nome"])          ."</td>";
                        $tabela .= "    <td class=\"data\">";
                        $tabela .= date("d/m/Y H:i", htmlspecialchars($linha["data"]))          ."</td>";
                        $tabela .= "    <td><a href='#' data-chaveaula=\"";
                        $tabela .= htmlspecialchars($linha['chaveAula']) . "\"";
                        $tabela .= "data-chavealuno=\"";
                        $tabela .= htmlspecialchars($linha['chaveAluno']);
                        $tabela .= "\" data-justificativa=\"";
                        $tabela .= htmlspecialchars($linha['justificativaAusencia']);
                        $tabela .= "\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-justificativa\"><i class='fa fa-check'></i></a></td>";
                        $tabela .= "</tr>";
    
                    }
                    else{
                        $possuiProximaPagina = true;
                    }
                    $contador++;
                }
                // Encerramos a conexão com o BD
                $conexao = null;
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>Justificativa de ausências</h1>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <br>
                    <?php if($numeroRegistros !== 0) { ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="noticias">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="200px">Nome do aluno</th>
                                        <th width="200px">Data da aula</th>
                                        <th width="200px">Avaliar justificativa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= $tabela ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <script type="text/javascript">
                        //pequeno script somente para passar se existe proxima pagina e página
                        //anterior

                        possuiProximaPagina = <?= $possuiProximaPagina == 1 ? 1 : 0 ?>;
                        possuiPaginaAnterior = <?= (!(isset($_GET["pagina"])) ||
                                                       ($_GET["pagina"] == 0)) ? 0 : 1  ?>;

                    </script>
                    <div align="center">
                        <a href="#" id="anterior" class="btn btn-info"
                            <?php if(!isset($_GET["pagina"]) || $_GET["pagina"] == 0){
                                    echo "disabled name=\"desativado\"";
                                } ?>
                            >
                            <i href="#" class="fa fa-arrow-circle-o-left"></i>
                         Anterior
                        </a>
                        <a id="label-ipp" href="#" class="btn" 
                                style= "display:inline;color:#215F89"
                                >Linhas por página
                            </a>

                                <select name="ipp" id="ipp" class="form-control"
                                        style="display:inline;width:120px">

                                        <option value="10"
                                            <?=isset($_GET["pagina-ipp"]) &&
                                                htmlspecialchars($_GET["pagina-ipp"]) == "10"?
                                            "selected='selected'":'';?> >
                                        10</option>
                                        <option value="25"
                                            <?=isset($_GET["pagina-ipp"]) &&
                                                htmlspecialchars($_GET["pagina-ipp"]) == "25"?
                                            "selected='selected'":'';?> >
                                        25</option>
                                        <option value="50"
                                           <?=isset($_GET["pagina-ipp"]) &&
                                                htmlspecialchars($_GET["pagina-ipp"]) == "50"?
                                           "selected='selected'":'';?> >
                                        50</option>
                                        <option value="100"
                                            <?=isset($_GET["pagina-ipp"]) &&
                                                htmlspecialchars($_GET["pagina-ipp"]) == "100"?
                                           "selected='selected'":'';?> >
                                        100</option>
                                    </select>
                        <a href="#" id="proxima" class="btn btn-info" 
                            <?php if(!$possuiProximaPagina){
                                    echo 'disabled';
                                } ?>>
                            Próxima
                            <i href="#" class="fa fa-arrow-circle-o-right"></i>
                        </a>
                    </div>
                    <div align="center">
                        <p>Página <?= isset($_GET["pagina"]) ?
                                 (int)(htmlspecialchars($_GET["pagina"])) +1 :
                                 1 ?> </p>
                    </div>
                    <?php } else { ?>
                    <p>Nenhuma justificativa pendente!</p>
                    <?php } ?>
                    
                </section>
            </div>
        </div>
        </div>
        <!-- popup "modal" do bootstrap para avaliação de justificativa de ausência -->
        <div class="modal fade" id="modal-justificativa" tabindex="-1" role="dialog"
             aria-labelledby="modal-justificativa" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Avaliar justificativa</h4>
                    </div>
                    <div class="modal-body">
                        <b>Justificativa do aluno:</b><br><br>
                        <input type="hidden" id="idAula" name="idAula">
                        <input type="hidden" id="idAluno" name="idAluno">
                        <p id="justificativa"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger"
                                name="submit" value="0">Recusar</button>
                        <button type="submit" class="btn btn-success"
                                name="submit" value="1">Aceitar</button>
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