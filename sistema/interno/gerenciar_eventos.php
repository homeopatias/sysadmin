<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Eventos - Homeopatias.com</title>
        <!-- plugin de JQuery para tornar tabelas ordenaveis -->
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <!-- polyfill para funcionalidades do HTML5 -->
        <script src="./webshim-1.14.5/polyfiller.js"></script>
        <script>
            // usamos um polyfill para que os campos de data e hora funcionem mesmo
            // em navegadores que nao implementem essas funcionalidades (voce sabe quais)

            webshims.activeLang("pt-BR");
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date', replaceUI: true});
            webshims.polyfill('forms forms-ext');

            var podeMudarPagina = true;
            $(document).ready(function(){

                // permite redimensionar as colunas da tabela
                $("#eventos").colResizable({
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

                $("#eventos").tablesorter({ headers: {
                    2 : { sorter: "datetime" },
                    3 : { sorter: false },
                    4 : { sorter: false }
                }});
                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-evento').text(
                        $(e.relatedTarget).parent().siblings('.titulo').text()
                    );
                });
                // passa os dados do evento para o modal para a edição
                $("#modal-edita-evento").on('show.bs.modal', function(e) {
                    $(this).find('#id').val(
                        $(e.relatedTarget).data('id')
                    );
                    $(this).find('#titulo').val(
                        $(e.relatedTarget).parent().siblings('.titulo').text()
                    );
                    $(this).find('#local').val(
                        $(e.relatedTarget).parent().siblings('.local').text()
                    );

                    // formatamos a data para insercao no formulário de edição
                    var data = new Date($(e.relatedTarget).parent().siblings('.dataEvento').data("data-html"));
                    var ano = data.getFullYear();
                    var mes = data.getMonth() < 9 ? "0": "";
                    var dia = data.getDate() < 10 ? "0": "";
                    var hora = data.getHours() < 10 ? "0": "";
                    var minuto = data.getMinutes() < 10 ? "0": "";
                    mes = mes + "" + (data.getMonth() + 1);
                    dia = dia + "" + data.getDate();
                    hora = hora + "" + data.getHours();
                    minuto = minuto + "" + data.getMinutes();

                    $(this).find('#data').val(data.getFullYear() + "-" + mes + "-" + dia);
                    var inputPolyfill = $(this).find('#data').siblings("input");
                    if(inputPolyfill.length > 0)
                        inputPolyfill[0].value = dia + "/" + mes + "/" + data.getFullYear();

                    $(this).find('#horario').val(hora + ":" + minuto);
                    inputPolyfill = $(this).find('#horario').siblings("input");
                    if(inputPolyfill.length > 0)
                        inputPolyfill[0].value = hora + ":" + minuto;

                    $(this).find('#descricao').val(
                        $(e.relatedTarget).data('descricao')
                    );
                });

                // esconde inputs de busca

                $("#filtro-titulo").hide();
                $("#filtro-local").hide();
                $("#div-data-min").hide();
                $("#div-data-max").hide();
                $("#ipp").hide();  

                // alterna campos de texto com campos de input
                $("#label-titulo").click(function(){
                    $(this).hide();
                    $("#filtro-titulo").show(300);
                    $("#filtro-titulo").focus();
                });

                $("#filtro-titulo").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-titulo").show(300);   
                    } 
                });

                $("#label-local").click(function(){
                    $(this).hide();
                    $("#filtro-local").show(300);
                    $("#filtro-local").focus();
                });

                $("#filtro-local").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-local").show(300);   
                    }
                });

                $("#label-data-min").click(function(){
                    $(this).hide();
                    $("#div-data-min").show(300);
                    $("#filtro-data-min").focus();
                });

                $("#filtro-data-min").focusout(function(){
                    if($("this").val() != ""){
                        atualizaPagina();
                    }
                    $("#div-data-min").hide(300);
                    $("#label-data-min").show(300);   
                });

                

                $("#label-data-max").click(function(){
                    $(this).hide();
                    $("#div-data-max").show(300);
                    $("#filtro-data-max").focus();
                });

                $("#filtro-data-max").blur(function(){
                    if($(this).val() != ""){
                        atualizaPagina();
                    }
                    else{
                        $("#div-data-max").hide(300);
                        $("#label-data-max").show(300);   
                    }
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

                // processa envio do formulário se enter for pressionado dentro de algum campo
                // do formulário de filtro

                // filtro-data-max e filtro-data-min envia o formulário usando .onblur()
                $("#filtro-titulo").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-local").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-titulo").val("");
                    $("#filtro-local").val("");
                    $("#filtro-data-min").val("");
                    $("#filtro-data-max").val("");
                    $("#form-filtro").submit();
                });

                //se mudou a quantidade de pessoas por página, atualiza
                $("#ipp").change(function(){
                    $("#pagina-ipp").val( $(this).val() );
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

                checaTamanhoTela();
            }); 

            //atualiza formulário com a busca
            function atualizaPagina(){
                $("#pagina").val(0);
                $("#form-filtro").submit();
            }

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

            // mensagem a ser exibida acima da listagem de eventos, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe Eventos apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){
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

                // se o usuário chegou até aqui através de um formulário, registra o novo
                // evento no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $data      = $_POST["data"];
                    $horario   = $_POST["horario"];
                    $local     = $_POST["local"];
                    $descricao = $_POST["descricao"];
                    $titulo    = $_POST["titulo"];

                    $idValido        = isset($id) && preg_match("/^[0-9]+$/", $id);
                    $dataValida      = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
                    $horarioValido   = isset($horario) && preg_match("/^\d{2}:\d{2}$/", $horario);
                    $localValido     = isset($local) && mb_strlen($local, 'UTF-8') >= 3 &&
                                       mb_strlen($local, 'UTF-8') <= 500;
                    $descricaoValida = !isset($descricao) || mb_strlen($descricao, 'UTF-8') <= 3000;
                    $tituloValido    = isset($titulo) && mb_strlen($titulo, 'UTF-8') >= 3 &&
                                       mb_strlen($titulo, 'UTF-8') <= 100;

                    // se todos os dados estão válidos, o evento é criado
                    if($dataValida && $horarioValido && $localValido && $descricaoValida && $tituloValido){

                        if(!isset($descricao) || $descricao === ""){
                            $descricao = "";
                        }

                        $dataPublicacao = date("Y-m-d H:i:s");
                        
                        $dataEvento = date("Y-m-d H:i:s", strtotime($data . " " . $horario . ":00"));
                        $comando  = "INSERT INTO Evento (dataPublic, dataEvento, titulo, local, descricao) ";
                        $comando .= "VALUES (?, ?, ?, ?, ?)";
                        $query = $conexao->prepare($comando);
                        $dados  = array($dataPublicacao, $dataEvento, $titulo, $local, $descricao);
                        $sucesso = $query->execute($dados);

                        if($sucesso){
                            $mensagem = "";
                        }else{
                            $mensagem = "Erro na criação de evento";
                        }
                    }else if(!$dataValida){
                        $mensagem = "Data inválida!";
                    }else if(!$horarioValido){
                        $mensagem = "Horário inválido!";
                    }else if(!$localValido){
                        $mensagem = "Local inválido!";
                    }else if(!$descricaoValida){
                        $mensagem = "Descrição inválida!";
                    }else if(!$tituloValido){
                        $mensagem = "Título inválido!";
                    }
                }

                $textoQuery  = "SELECT idEvento, dataPublic, dataEvento, titulo, local, descricao 
                                FROM Evento";

                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroTitulo = $filtroLocal = $filtroDataMin = $filtroDataMax = false;
                if(isset($_GET["filtro-titulo"]) || isset($_GET["filtro-local"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"])){
                    
                    $queryWhere = "WHERE"; // armazena os filtros nesta variável para impedir filtros
                                           // vazios, pois a query padrão não usa WHERE

                    $filtroLocal   =  htmlspecialchars($_GET["filtro-local"]);
                    $filtroTitulo  =  htmlspecialchars($_GET["filtro-titulo"]);
                    $filtroDataMin =  htmlspecialchars($_GET["filtro-data-min"]);
                    $filtroDataMax =  htmlspecialchars($_GET["filtro-data-max"]);

                    if(isset($filtroTitulo) && mb_strlen($filtroTitulo) > 0){
                        $filtroTitulo    =  "%".mb_strtoupper($filtroTitulo)."%";
                        $queryWhere .= " UPPER(titulo) LIKE :filtrotitulo ";
                    }            
                    if(isset($filtroLocal) && mb_strlen($filtroLocal) > 0){
                        $filtroLocal    =  "%".mb_strtoupper($filtroLocal)."%";
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " UPPER(local) LIKE :filtrolocal ";
                        }
                        else{
                            $queryWhere .= " AND UPPER(local) LIKE :filtrolocal ";
                        }
                        
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " CAST(dataEvento AS Date) >= ";
                        }
                        else{
                            $queryWhere .= " AND CAST(dataEvento AS Date) >= ";
                        }
                        $queryWhere .= "CAST(:dataMin as Date)";

                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " CAST(dataEvento AS Date) <= ";
                        }
                        else{
                            $queryWhere .= " AND CAST(dataEvento AS Date) <= ";
                        }
                        $queryWhere .= "CAST(:dataMax as Date)";
                    }
                    if($queryWhere != "WHERE"){ // So sera colocada se for adicionado algo, para não
                                                // adicionar WHERE vazio

                        $textoQuery .= " ".$queryWhere;


                    }
                }

                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY dataPublic DESC" ;
                $indexHeader = -1;
                $direcao = 2;
                //------------------

                if( isset($_GET["numeroTableHeader"]) && isset($_GET["cimaOuBaixo"]) ){
                    $indexHeader = htmlspecialchars( $_GET["numeroTableHeader"] );
                    if( !is_nan($indexHeader) ){
                        
                        switch ($indexHeader) {
                            case '0':
                                $orderBy = " ORDER BY titulo " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY local " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY dataPublic " ;
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
                                $orderBy .= " ASC " ;
                                break;
                            case '2':
                                $orderBy .= " DESC " ;
                                break;
                            
                            
                            default:
                                $orderBy .= " DESC " ;
                                break;
                        }
                    }

                }

                //--------------------------------------------------------------------

                // Prepara as variáveis necessárias para controlar a paginação
                $pagina = isset($_GET["pagina"]) ? htmlspecialchars($_GET["pagina"]) : 0;
                $pagina = (int)$pagina;

                $itemsPorPagina = isset($_GET["pagina-ipp"]) ? 
                                  htmlspecialchars($_GET["pagina-ipp"]) : 10;
                $itemsPorPagina = (int)$itemsPorPagina;

                $textoQuery    .= $orderBy." LIMIT ".($itemsPorPagina+1).
                                " OFFSET ".(($pagina)*$itemsPorPagina);

                $query = $conexao->prepare($textoQuery);

                if(isset($_GET["filtro-titulo"]) || isset($_GET["filtro-local"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"])){
                    if(isset($filtroTitulo) && mb_strlen($filtroTitulo) > 0){
                        $query->bindParam(":filtrotitulo",$filtroTitulo);
                    }
                    if(isset($filtroLocal) && mb_strlen($filtroLocal) > 0){
                        $query->bindParam(":filtrolocal",$filtroLocal);
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

                while ($linha = $query->fetch()){
                    if($contador != $itemsPorPagina){
    
                        // listamos os dados de cada evento
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"titulo\">";
                        $tabela .= htmlspecialchars($linha["titulo"])                         ."</td>";
                        $tabela .= "    <td class=\"local\">";
                        $tabela .= htmlspecialchars($linha["local"])                          ."</td>";
                        $tabela .= "    <td class=\"dataEvento\" data-data-html=\"";
                        $tabela .= str_replace("-", "/", $linha["dataEvento"]) . "\">";
                        $tabela .= date("d/m/Y H:i",
                                        strtotime(htmlspecialchars($linha["dataEvento"])))    ."</td>";
                        $tabela .= "    <td><a data-id=\"";
                        $tabela .= $linha["idEvento"]."\" data-descricao=\"";
                        $tabela .= htmlspecialchars($linha["descricao"]);
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-edita-evento\">";
                        $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                        $tabela .= "    <td><a data-href=\"rotinas/evento/remover_evento.php?id=";
                        $tabela .= $linha["idEvento"];
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-confirma-deleta\">";
                        $tabela .= "<i class=\"fa fa-trash-o\"></i></a></td>";
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
                    <h1>Eventos</h1>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-evento">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Novo evento</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_eventos.php" id="form-filtro">
                        <div class="form-group">
                            <br>
                            <p>
                                <b>Buscar por:</b>
                            </p>
                            <a id="label-titulo" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-titulo"]) && 
                                        mb_strlen(($_GET["filtro-titulo"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Título
                            </a>
                            <input  type="text" name="filtro-titulo" id="filtro-titulo"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-titulo"]) ? 
                                        htmlspecialchars($_GET["filtro-titulo"]) : "" ?> >

                            <a id="label-local" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-local"]) && 
                                        mb_strlen(($_GET["filtro-local"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Local
                            </a>
                            <input  type="text" name="filtro-local" id="filtro-local"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-local"]) ? 
                                        htmlspecialchars($_GET["filtro-local"]) : "" ?> >

                            <a id="label-data-min" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-data-min"]) && 
                                        mb_strlen(($_GET["filtro-data-min"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >Data minima programada
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
                                >Data máxima programada
                            </a>
                            <div id="div-data-max" style="display: inline">
                            <input type="date" name="filtro-data-max" id="filtro-data-max"
                                       placeholder="dd/mm/aaaa" class="form-control"
                                       style="display:inline;width:150px"
                                       value =<?= isset($_GET["filtro-data-max"]) ?
                                                htmlspecialchars($_GET["filtro-data-max"]) : "" ?> >
                            </div>
                            <a href="#" id="busca" class="btn btn-info" style="margin-left: 50px">
                                Buscar
                                <i href="#" class="fa fa-search"></i>
                            </a>
                            <a href="#" id="limpar" class="btn btn-info" style="margin-left: 10px">
                                Limpar
                                <i href="#" class="fa fa-eraser"></i>
                            </a>

                            <!-- controle de pagina da paginação -->
                            <input type="hidden" id="pagina" name="pagina" 
                                value=<?= isset($_GET["pagina"]) ? 
                                    htmlspecialchars($_GET["pagina"]) : 0 ?> />
                            
                            <input type="hidden" id="pagina-ipp" name="pagina-ipp" 
                                value=<?= isset($_GET["pagina-ipp"]) ? 
                                    htmlspecialchars($_GET["pagina-ipp"]) : 10 ?> />

                            <!-- controle de ordenação da tabela -->
                            <input type="hidden" name ="numeroTableHeader" 
                                id="numeroTableHeader" 
                                value =<?= isset($_GET["numeroTableHeader"])? 
                                    htmlspecialchars($_GET["numeroTableHeader"]) :
                                    "0" ?> >

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
                            <table class="table table-bordered table-striped" id="eventos">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="200px" <?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Título</th>
                                        <th width="200px" <?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Local</th>
                                        <th width="100px" <?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data e horário</th>
                                        <th width="60px">Editar</th>
                                        <th width="60px">Excluir</th>
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
                    <?php } ?>
                    
                </section>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para inserção de novo evento -->
        <div class="modal fade" id="modal-novo-evento" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-evento" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_eventos.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Novo evento</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="titulo-novo">Título do evento:</label>
                                <input type="text" name="titulo" id="titulo-novo" required
                                       pattern="^.{3,100}$" title="O título deve ter de 3 a 100 caracteres"
                                       placeholder="Titulo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="data-novo">Data:</label>
                                <input type="date" name="data" id="data-novo"
                                       placeholder="dd/mm/aaaa" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="horario-novo">Horário:</label>
                                <input type="time" name="horario" id="horario-novo" required
                                       placeholder="--:--" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="local-novo">Local do evento:</label>
                                <input type="text" name="local" id="local-novo" required
                                       pattern="^.{3,500}$" placeholder="Local do evento"
                                       title="O local deve ter de 3 a 500 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="descricao-novo">Descrição:</label>
                                <textarea name="descricao" id="descricao-novo" rows="5" cols="50"
                                    maxlength="3000"
                                    title="A descrição do evento deve ter até 3000 caracteres"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir evento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de evento -->
        <div class="modal fade" id="modal-edita-evento" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-evento" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/evento/editar_evento.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar evento</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <label for="titulo">Título do evento:</label>
                                <input type="text" name="titulo" id="titulo" required
                                       pattern="^.{3,100}$" title="O título deve ter de 3 a 100 caracteres"
                                       placeholder="Titulo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="data">Data:</label>
                                <input type="date" name="data" id="data"
                                       required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="horario">Horário:</label>
                                <input type="time" name="horario" id="horario" required
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="local">Local do evento:</label>
                                <input type="text" name="local" id="local" required
                                       pattern="^.{3,500}$" placeholder="Local do evento"
                                       title="O local deve ter de 3 a 500 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="descricao">Descrição:</label>
                                <textarea name="descricao" id="descricao" rows="5" cols="50"
                                    maxlength="3000"
                                    title="A descrição do evento deve ter até 3000 caracteres"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar evento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de evento -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de evento</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover "<span id="nome-evento"></span>"?</h3>
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