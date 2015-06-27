<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Pagamentos - Homeopatias.com</title>
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <!-- polyfill para funcionalidades do HTML5 -->
        <script src="./webshim-1.14.5/polyfiller.js"></script>

        <script type="text/javascript">
            // usamos um polyfill para que os campos de data e hora funcionem mesmo
            // em navegadores que não implementem essas funcionalidades

            webshims.activeLang("pt-BR");
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date', replaceUI: true});
            webshims.polyfill('forms forms-ext');
        </script>
        <script>
            var podeMudarPagina = true;
            $(document).ready(function(){

                // permite redimensionar as colunas da tabela
                $("#pagamentos").colResizable({
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

                $("#pagamentos").tablesorter({ headers: {
                    0 : { sorter: false },
                    2 : { sorter: false },
                    3 : { sorter: false },
                    4 : { sorter: "datetime" },
                    5 : { sorter: false },
                    6 : { sorter: false }

                }});

                // esconde inputs de busca

                $("#filtro-valor").hide();
                $("#filtro-motivo").hide();
                $("#filtro-meio").hide();
                $("#ipp").hide();   
                $("#div-data-min").hide();
                $("#div-data-max").hide();

                // alterna campos de texto com campos de input
                $("#label-valor").click(function(){
                    $(this).hide();
                    $("#filtro-valor").show(300);
                    $("#filtro-valor").focus();
                });

                $("#label-motivo").click(function(){
                    $(this).hide();
                    $("#filtro-motivo").show(300);
                    $("#filtro-motivo").focus();
                });

                $("#label-meio").click(function(){
                    $(this).hide();
                    $("#filtro-meio").show(300);
                    $("#filtro-meio").focus();
                });

                $("#label-data-min").click(function(){
                    $(this).hide();
                    $("#div-data-min").show(300);
                    $("#filtro-data-min").focus();
                });                

                $("#label-data-max").click(function(){
                    $(this).hide();
                    $("#div-data-max").show(300);
                    $("#filtro-data-max").focus();
                });

                $("#label-ipp").click(function(){
                    $(this).hide();
                    $("#ipp").show(300);
                    $("#ipp").focus();
                });

                $("#ipp").blur(function(){
                    $(this).hide(300);
                    $("#label-ipp").show(300);   
                });

                //se mudou a quantidade de pessoas por página, atualiza
                $("#ipp").change(function(){
                    $("#pagina-ipp").val( $(this).val() );
                    atualizaPagina();
                });

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-valor").val("");
                    $("#filtro-motivo").val("");
                    $("#filtro-meio").val("");
                    $("#filtro-data-min").val("");
                    $("#filtro-data-max").val("");
                    atualizaPagina();
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

                $("#form-filtro input").change(function(){
                    podeMudarPagina = false;
                });

                //remove inputs em branco do form antes de enviar
                $("#form-filtro").submit(function(){

                    $(':input', this).each(function() {
                         this.disabled = !($(this).val());
                    });

                    if($('#pagina').val() == 0) {
                        $('#pagina')[0].disabled = true;
                    }
                    if($('#pagina-ipp').val() == 100) {
                        $('#pagina-ipp')[0].disabled = true;
                    }
                    if($('#numeroTableHeader').val() == -1) {
                        $('#numeroTableHeader')[0].disabled = true;
                    }
                    if($('#cimaOuBaixo').val() == 2) {
                        $('#cimaOuBaixo')[0].disabled = true;
                    }

                });


                //---- Passa o th que foi clicado para o form e o envia, para reformatar
                //----  a tabela
                $("table th.header").click(function(){
                    var position = $("table th").index( $(this) );
                    if( $(this).hasClass("headerSortDown") ){
                        direcao = 1; // muda para virada para cima
                    }
                    else{
                        direcao = 2;
                    }
                    $("#numeroTableHeader").val(position);
                    $("#cimaOuBaixo").val(direcao);

                    // Envia o formulário para atualizar a tabela com os filtros desejados
                    atualizaPagina();
                });
            });

            // atualiza formulário com a busca
            function atualizaPagina(){
                $("#form-filtro").submit();
            }
        </script>
    </head>
    <body>
        <?php

            include("modulos/navegacao.php");

            // mensagem a ser exibida acima da listagem de pagamentos, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])) {
                $mensagem = $_GET["erro"];
            }

            // exibe pagamentos apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" &&
               32 & unserialize($_SESSION["usuario"])->getPermissoes() ) {
                // lemos as credenciais do banco de dados
                $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                $dados = json_decode($dados, true);

                foreach($dados as $chave => $valor) {
                    $dados[$chave] = str_rot13($valor);
                }

                $host    = $dados["host"];
                $usuario = $dados["nome_usuario"];
                $senhaBD = $dados["senha"];

                // Cria conexão com o banco para ser usada ao longo da página
                $conexao = null;
                try {
                    $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }

                $textoQuery  = "SELECT P.valor, P.data, P.metodo, P.objetivo, U.nome 
                                FROM Pagamento P INNER JOIN Usuario U ON U.id = P.chaveUsuario ";

                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroValor = $filtroMotivo = $filtroMeio = $filtroDataMin = $filtroDataMax = false;
                if(isset($_GET["filtro-valor"]) || isset($_GET["filtro-motivo"]) || isset($_GET["filtro-meio"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"])){

                    $filtroMotivo     =  htmlspecialchars($_GET["filtro-motivo"]);
                    $filtroValor    =  htmlspecialchars($_GET["filtro-valor"]);
                    $filtroMeio     =  htmlspecialchars($_GET["filtro-meio"]);
                    $filtroDataMin   =  htmlspecialchars($_GET["filtro-data-min"]);
                    $filtroDataMax   =  htmlspecialchars($_GET["filtro-data-max"]);

                    if(isset($filtroValor) && mb_strlen($filtroValor) > 0){
                        $textoQuery     .= " AND P.valor = :filtroValor ";
                    }            
                    if(isset($filtroMotivo) && mb_strlen($filtroMotivo) > 0){
                        $filtroMotivo    =  "%".mb_strtoupper($filtroMotivo)."%";
                        $textoQuery    .= " AND UPPER(P.objetivo) LIKE :filtroMotivo ";
                    }            
                    if(isset($filtroMeio) && mb_strlen($filtroMeio) > 0){
                        $filtroMeio    =  "%".mb_strtoupper($filtroMeio)."%";
                        $textoQuery    .= " AND UPPER(P.metodo) LIKE :filtroMeio ";
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        $textoQuery    .= " AND CAST(P.data AS Date) >= ";
                        $textoQuery    .= "CAST(:dataMin as Date)";

                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        $textoQuery    .= " AND CAST(P.data AS Date) <= ";
                        $textoQuery    .= "CAST(:dataMax as Date)";
                    }
                }

                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY P.data DESC" ;
                $indexHeader = isset($_GET["numeroTableHeader"] ) 
                                ? htmlspecialchars( $_GET["numeroTableHeader"] ) 
                                : -1 ;
                $direcao = 1;
                //------------------

                if( isset($_GET["numeroTableHeader"]) ){
                    $indexHeader = htmlspecialchars( $_GET["numeroTableHeader"] );
                    if( !is_nan($indexHeader) ){
                        
                        switch ($indexHeader) {
                            case '1':
                                $orderBy = " ORDER BY P.valor " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY P.motivo" ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY P.metodo " ;
                                break;
                            case '4':
                                $orderBy = " ORDER BY P.data " ;
                                break;
                            
                            default:
                                $indexHeader = -1;
                                break;
                        }
                    }

                    $direcao = htmlspecialchars( $_GET["cimaOuBaixo"] );
                    if( !is_nan($direcao) ){

                        switch ($direcao) {
                            case '1':
                                $orderBy .= " DESC " ;
                                break;
                            case '2':
                                $orderBy .= " ASC " ;
                                break;
                            
                            
                            default:
                                $orderBy .= " ASC " ;
                                break;
                        }
                    }

                }

                //--------------------------------------------------------------------

                // Prepara as variáveis necessárias para controlar a paginação
                $pagina = isset($_GET["pagina"]) ? htmlspecialchars($_GET["pagina"]) : 0;
                $pagina = (int)$pagina;

                $itemsPorPagina = isset($_GET["pagina-ipp"]) ? 
                                  htmlspecialchars($_GET["pagina-ipp"]) : 100;
                $itemsPorPagina = (int)$itemsPorPagina;

                //---------SE algum index foi excolhido para ordenação, ordena---------
                
                if($indexHeader != -1){
                    $textoQuery .= $orderBy;
                }

                $textoQuery .= " LIMIT ".($itemsPorPagina+1).
                                " OFFSET ".(($pagina)*$itemsPorPagina);

                //---------------------------------------------------------------------


                $query = $conexao->prepare($textoQuery);

                // seta os parâmetro necessários para exacutar a filtragem de dados
                if(isset($_GET["filtro-valor"]) || isset($_GET["filtro-motivo"]) || isset($_GET["filtro-meio"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"])){
                    if(isset($filtroValor) && mb_strlen($filtroValor) > 0){
                        $query->bindParam(":filtroValor",$filtroValor);
                    }
                    if(isset($filtroMotivo) && mb_strlen($filtroMotivo) > 0){
                        $query->bindParam(":filtroMotivo",$filtroMotivo);
                    }
                    if(isset($filtroMeio) && mb_strlen($filtroMeio) > 0){
                        $query->bindParam(":filtroMeio",$filtroMeio);
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin)){
                        $query->bindParam(":dataMin" , $filtroDataMin);
                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax)){
                        $query->bindParam(":dataMax" , $filtroDataMax);
                    }
                }

                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $numeroRegistros = $query->rowCount();

                $possuiProximaPagina = false;
                $contador = 0;

                $tabela = "";

                while ($linha = $query->fetch()) {
                    if($contador != $itemsPorPagina){
                    // listamos os dados de cada artigo
                    $tabela .= "<tr>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars($linha['nome'])                         ."</td>";
                    $tabela .= "    <td>R$ ";
                    $tabela .= number_format(htmlspecialchars($linha["valor"]), 2)                         ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= ucfirst(htmlspecialchars($linha["objetivo"]))             ."</td>";
                    $tabela .= "    <td>";
                    $tabela .= htmlspecialchars($linha["metodo"]) . "</td>";
                    $tabela .= "    <td>";
                    $tabela .= date("d/m/Y",
                                    strtotime(htmlspecialchars($linha["data"])))    ."</td>";

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
                    <h1>Pagamentos</h1> 
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="lista_pagamentos.php" id="form-filtro">
                        <div class="form-group">
                            <br/>
                            <p>
                                <b>Buscar por:</b>
                            </p>
                            <a id="label-valor" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-valor"]) && 
                                        mb_strlen(($_GET["filtro-valor"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Valor
                            </a>
                            <input  type="text" name="filtro-valor" id="filtro-valor"
                                    placeholder="Valor" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-valor"]) ? 
                                        htmlspecialchars($_GET["filtro-valor"]) : "" ?> >

                            <a id="label-motivo" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-motivo"]) && 
                                        mb_strlen(($_GET["filtro-motivo"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Motivo
                            </a>
                            <select name="filtro-motivo" id="filtro-motivo" class="form-control"
                                    style="display:inline;width:120px">
                                <option value="" 
                                    <?=isset($_GET["filtro-motivo"]) &&
                                        htmlspecialchars($_GET["filtro-motivo"]) == "" ?
                                        'selected="selected"': '' ;?> >Nenhum
                                </option>
                                <option value="mensalidade"
                                    <?=isset($_GET["filtro-motivo"]) &&
                                        htmlspecialchars($_GET["filtro-motivo"]) == "mensalidade"?
                                    'selected="selected"':'';?> >
                                Mensalidade</option>
                                <option value="anuidade"
                                    <?=isset($_GET["filtro-motivo"]) &&
                                        htmlspecialchars($_GET["filtro-motivo"]) == "anuidade"?
                                    'selected="selected"':'';?> >
                                Anuidade</option>
                                <option value="livro"
                                   <?=isset($_GET["filtro-motivo"]) &&
                                        htmlspecialchars($_GET["filtro-motivo"]) == "livro"?
                                   'selected="selected"':'';?> >
                                Livro</option>
                            </select>

                            <a id="label-meio" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-meio"]) && 
                                        mb_strlen(($_GET["filtro-meio"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Método
                            </a>
                            <input  type="text" name="filtro-meio" id="filtro-meio"
                                    placeholder="Método" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-meio"]) ? 
                                        htmlspecialchars($_GET["filtro-meio"]) : "" ?> >

                            <a id="label-data-min" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-data-min"]) && 
                                        mb_strlen(($_GET["filtro-data-min"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >Datas desde
                            </a>
                            <div id="div-data-min" style="display: inline">
                                <input type="date" name="filtro-data-min" id="filtro-data-min"
                                       placeholder="dd/mm/aaaa" class="form-control"
                                       style="display:inline;width:150px"
                                       value =<?= isset($_GET["filtro-data-min"]) ?
                                                htmlspecialchars($_GET["filtro-data-min"]) : "" ?> >
                            </div>
                            <a id="label-data-max" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-data-max"]) && 
                                        mb_strlen(($_GET["filtro-data-max"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >Datas até
                            </a>
                            <div id="div-data-max" style="display: inline">
                            <input type="date" name="filtro-data-max" id="filtro-data-max"
                                       placeholder="dd/mm/aaaa" class="form-control"
                                       style="display:inline;width:150px"
                                       value =<?= isset($_GET["filtro-data-max"]) ?
                                                htmlspecialchars($_GET["filtro-data-max"]) : "" ?> >
                            </div>
                            <br><br>
                            <a href="#" id="limpar" class="btn btn-info" >
                                Limpar
                                <i href="#" class="fa fa-eraser"></i>
                            </a>
                            <a href="#" id="busca" class="btn btn-info">
                                Buscar
                                <i href="#" class="fa fa-search"></i>
                            </a>
                            <!-- controle de pagina da paginação -->
                            <input type="hidden" id="pagina" name="pagina" 
                                value=<?= isset($_GET["pagina"]) ? 
                                    htmlspecialchars($_GET["pagina"]) : 0 ?> />
                            <input type="hidden" id="pagina-ipp" name="pagina-ipp" 
                                value=<?= isset($_GET["pagina-ipp"]) ? 
                                    htmlspecialchars($_GET["pagina-ipp"]) : 100 ?> />

                            <!-- controle de ordenação da tabela -->
                            <input type="hidden" name ="numeroTableHeader" 
                                id="numeroTableHeader" 
                                value =<?= isset($_GET["numeroTableHeader"])? 
                                    htmlspecialchars($_GET["numeroTableHeader"]) :
                                    "-1" ?> >

                            <!-- passar 1 para ser crescente ou 2 para decrescente -->
                            <input type="hidden" name="cimaOuBaixo" 
                                id="cimaOuBaixo" 
                                value =<?= isset($_GET["cimaOuBaixo"])? 
                                    htmlspecialchars($_GET["cimaOuBaixo"]) :
                                    "2" ?>>
                        </div>
                    </form>
                    <!--  Fim form filtros   -->
                    <br>
                    <?php if($numeroRegistros !== 0){ ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="pagamentos">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="200px">Usuário</th>
                                        <th width="200px"<?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?> >Valor</th>
                                        <th width="160px"<?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?> >Motivo</th>
                                        <th width="120px"<?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?> >Meio de pagamento</th>
                                        <th width="120px"<?= $indexHeader == 4 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?> >Data</th>
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

                                        <option value="100"
                                            <?=isset($_GET["pagina-ipp"]) &&
                                                htmlspecialchars($_GET["pagina-ipp"]) == "100"?
                                            "selected='selected'":'';?> >
                                        100</option>
                                        <option value="150"
                                            <?=isset($_GET["pagina-ipp"]) &&
                                                htmlspecialchars($_GET["pagina-ipp"]) == "150"?
                                            "selected='selected'":'';?> >
                                        150</option>
                                        <option value="200"
                                           <?=isset($_GET["pagina-ipp"]) &&
                                                htmlspecialchars($_GET["pagina-ipp"]) == "200"?
                                           "selected='selected'":'';?> >
                                        200</option>
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
            window.location.href = "index.php";
        </script>
        <?php
                die();
            }
            include("modulos/rodape.php");
        ?>
    </body>
</html>
