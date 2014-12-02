<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Definições de trabalho - Homeopatias.com</title>
        <!-- plugin de JQuery para tornar tabelas ordenaveis -->
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <!-- polyfill para funcionalidades do HTML5 -->
        <script src="./webshim-1.14.5/polyfiller.js"></script>
        <script>
            // usamos um polyfill para que os campos de data funcionem mesmo
            // em navegadores que nao implementem essas funcionalidades (voce sabe quais)

            webshims.activeLang("pt-BR");
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date', replaceUI: true});
            webshims.polyfill('forms forms-ext');

            var podeMudarPagina = true;
            $(document).ready(function(){
                
                // permite redimensionar as colunas da tabela
                $("#defs-trabalho").colResizable({
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

                $("#defs-trabalho").tablesorter({ headers: {
                    2 : { sorter: "datetime" },
                    3 : { sorter: false },
                    4 : { sorter: false },
                    5 : { sorter: false }
                }});
                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-def-trabalho').text(
                        $(e.relatedTarget).parent().siblings('.titulo').text()
                    );
                });
                // passa os dados da definição de trabalho para o modal para a edição
                $("#modal-edita-def-trabalho").on('show.bs.modal', function(e) {
                    $(this).find('#id').val(
                        $(e.relatedTarget).data('id')
                    );
                    $(this).find('#titulo').val(
                        $(e.relatedTarget).parent().siblings('.titulo').text()
                    );
                    $(this).find('#etapa').val(
                        $(e.relatedTarget).parent().siblings('.etapa').text()
                    );

                    // formatamos a data para insercao no formulário de edição
                    var data = new Date($(e.relatedTarget).parent().siblings('.data').data("data-html"));
                    var ano = data.getFullYear();
                    var mes = data.getMonth() < 9 ? "0": "";
                    var dia = data.getDate() < 10 ? "0": "";
                    mes = mes + "" + (data.getMonth() + 1);
                    dia = dia + "" + (data.getDate()  + 1);

                    $(this).find('#data').val(data.getFullYear() + "-" + mes + "-" + dia);
                    var inputPolyfill = $(this).find('#data').siblings("input");
                    if(inputPolyfill.length > 0)
                        inputPolyfill[0].value = dia + "/" + mes + "/" + data.getFullYear();

                    $(this).find('#descricao').val(
                        $(e.relatedTarget).data('descricao')
                    );
                });
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

                // esconde inputs de busca

                $("#filtro-titulo").hide();
                $("#filtro-etapa").hide();
                $("#div-data-min").hide();
                $("#div-data-max").hide();
                $("#ipp").hide();   

                // alterna campos de texto com campos de input
                $("#label-titulo").click(function(){
                    $(this).hide();
                    $("#filtro-titulo").show(300);
                    $("#filtro-titulo").focus();
                });

                $("#label-etapa").click(function(){
                    $(this).hide();
                    $("#filtro-etapa").show(300);
                    $("#filtro-etapa").focus();
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

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-titulo").val("");
                    $("#filtro-etapa").val("");
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

            // mensagem a ser exibida acima da listagem de definições de trabalho, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe definições de trabalho apenas para administradores ou professores logados
            // porém apenas administradores podem editar e excluir
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && (unserialize($_SESSION["usuario"])->getNivelAdmin() === "professor"
                   || (unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador") && 
                        2 & unserialize($_SESSION["usuario"])->getPermissoes()) ){

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
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                // se o usuário chegou até aqui através de um formulário, registra a nova
                // definição de trabalho no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $titulo    = $_POST["titulo"];
                    $etapa     = $_POST["etapa"];
                    $data      = $_POST["data"];
                    $descricao = $_POST["descricao"];

                    $tituloValido    = isset($titulo) && mb_strlen($titulo, 'UTF-8') >= 3 &&
                                       mb_strlen($titulo, 'UTF-8') <= 300;
                    $etapaValida     = isset($etapa) && preg_match("/^[1-4]$/", $etapa);
                    $dataValida      = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
                    $descricaoValida = isset($descricao) && mb_strlen($descricao, 'UTF-8') <= 10000;

                    // se todos os dados estão válidos, a definição de trabalho é criada
                    if($tituloValido && $etapaValida && $dataValida && $descricaoValida){

                        $comando  = "INSERT INTO TrabalhoDefinicao (titulo, etapa, descricao,";
                        $comando .= " dataLimite, ano) VALUES (?, ?, ?, ?, ?)";
                        $query = $conexao->prepare($comando);

                        $data = date("Y-m-d H:i:s", strtotime($data));
                        $dados  = array($titulo, $etapa, $descricao, $data, date("Y"));
                        $sucesso = $query->execute($dados);

                        if($sucesso){
                            $mensagem = "";
                        }else{
                            $mensagem = "Erro na inserção de definição de trabalho";
                        }
                    }else if(!$tituloValido){
                        $mensagem = "Título inválido!";
                    }else if(!$etapaValida){
                        $mensagem = "Etapa inválida!";
                    }else if(!$dataValida){
                        $mensagem = "Data inválida!";
                    }else if(!$descricaoValida){
                        $mensagem = "Descrição inválida!";
                    }
                }

                $textoQuery  = "SELECT idDefTrabalho, titulo, etapa, descricao, dataLimite,
                                UNIX_TIMESTAMP(dataLimite) as data, ano FROM TrabalhoDefinicao"; 
                
                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroTitulo = $filtroEtapa = $filtroDataMin = $filtroDataMax = false;
                if(isset($_GET["filtro-titulo"]) || isset($_GET["filtro-etapa"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"])){
                    $queryWhere = "WHERE"; // armazena os filtros nesta variável para impedir filtros
                                           // vazios, pois a query padrão não usa WHERE

                    $filtroEtapa     =  htmlspecialchars($_GET["filtro-etapa"]);
                    $filtroTitulo    =  htmlspecialchars($_GET["filtro-titulo"]);
                    $filtroDataMin   =  htmlspecialchars($_GET["filtro-data-min"]);
                    $filtroDataMax   =  htmlspecialchars($_GET["filtro-data-max"]);

                    if(isset($filtroTitulo) && mb_strlen($filtroTitulo) > 0){
                        $filtroTitulo   =  "%".$filtroTitulo."%";
                        $queryWhere    .= " titulo LIKE :filtrotitulo ";
                    }            
                    if(isset($filtroEtapa) && mb_strlen($filtroEtapa) > 0 &&
                        $filtroEtapa != "0"){
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " etapa = :filtroetapa ";
                        }
                        else{
                            $queryWhere .= " AND etapa = :filtroetapa ";
                        }
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " CAST(dataLimite AS Date) >= ";
                        }
                        else{
                            $queryWhere .= " AND CAST(dataLimite AS Date) >= ";
                        }
                        $queryWhere .= "CAST(:dataMin as Date)";

                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " CAST(dataLimite AS Date) <= ";
                        }
                        else{
                            $queryWhere .= " AND CAST(dataLimite AS Date) <= ";
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
                $orderBy = " ORDER BY dataLimite DESC" ;
                $indexHeader = isset($_GET["numeroTableHeader"] ) 
                                ? htmlspecialchars( $_GET["numeroTableHeader"] ) 
                                : -1 ;
                $direcao = 1;
                //------------------

                if( isset($_GET["numeroTableHeader"]) && isset($_GET["cimaOuBaixo"]) ){
                    $indexHeader = htmlspecialchars( $_GET["numeroTableHeader"] );
                    if( !is_nan($indexHeader) ){
                        
                        switch ($indexHeader) {
                            case '0':
                                $orderBy = " ORDER BY titulo " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY etapa " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY dataLimite " ;
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

                //---------SE algum index foi excolhido para ordenação, ordena---------
                
                if($indexHeader != -1){
                    $textoQuery .= $orderBy;
                }

                $textoQuery .= " LIMIT ".($itemsPorPagina+1).
                                " OFFSET ".(($pagina)*$itemsPorPagina);

                //---------------------------------------------------------------------

                $query = $conexao->prepare($textoQuery);

                // seta os parâmetro necessários para exacutar a filtragem de dados
                if(isset($_GET["filtro-titulo"]) || isset($_GET["filtro-etapa"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"])){
                    if(isset($filtroTitulo) && mb_strlen($filtroTitulo) > 0){
                        $query->bindParam(":filtrotitulo",$filtroTitulo);
                    }
                    if(isset($filtroEtapa) && mb_strlen($filtroEtapa) > 0 &&
                        $filtroEtapa != "0"){
                        $query->bindParam(":filtroetapa",$filtroEtapa);
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

                        // listamos os dados de cada definição de trabalho
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"titulo\">";
                        $tabela .= htmlspecialchars($linha["titulo"])                         ."</td>";
                        $tabela .= "    <td class=\"etapa\">";
                        $tabela .= htmlspecialchars($linha["etapa"])                          ."</td>";
                        $tabela .= "    <td class=\"data\" data-data-html=\"";
                        $tabela .= date("Y-m-d", $linha["data"]) . "\">";
                        $tabela .= date("d/m/Y", $linha["data"])    ."</td>";
                        $tabela .= "<td>" . htmlspecialchars($linha["ano"]) . "</td>";

                        if(unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){
                            // dá ao admin as opções de editar e excluir
                            $tabela .= "    <td><a data-id=\"";
                            $tabela .= $linha["idDefTrabalho"]."\" data-descricao=\"";
                            $tabela .= htmlspecialchars($linha["descricao"]);
                            $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                            $tabela .= " data-target=\"#modal-edita-def-trabalho\">";
                            $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                            $tabela .= "    <td><a data-href=\"rotinas/definicoes_trabalho/remover_definicao_trabalho.php?id=";
                            $tabela .= $linha["idDefTrabalho"];
                            $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                            $tabela .= " data-target=\"#modal-confirma-deleta\">";
                            $tabela .= "<i class=\"fa fa-trash-o\"></i></a></td>";
                        }else if(unserialize($_SESSION["usuario"])->getNivelAdmin() === "professor"){
                            // dá ao professor as opções de visualizar a descrição e, caso possa,
                            // avaliar os trabalhos enviados pelos alunos
                            $tabela .= "    <td><a data-descricao=\"";
                            $tabela .= nl2br(htmlspecialchars($linha["descricao"]));
                            $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                            $tabela .= " data-target=\"#modal-descricao\">";
                            $tabela .= "<i class=\"fa fa-eye\"></i></a></td>";
                            if(unserialize($_SESSION["usuario"])->getCorrigeTrabalho()) {
                                $tabela .= "    <td><a href=\"visualizar_trabalhos.php?id=";
                                $tabela .= $linha["idDefTrabalho"] . "\">";
                                $tabela .= "<i class=\"fa fa-lg fa-files-o\"></i></a></td>";
                            }
                        }
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
                    <h1>Definições de trabalho</h1>   
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-def-trabalho">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Nova definição de trabalho</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_definicoes_trabalho.php" id="form-filtro">
                        <div class="form-group">
                            <br/>
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

                            <a id="label-etapa" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-etapa"]) && 
                                        mb_strlen(($_GET["filtro-etapa"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-etapa"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Etapa
                            </a>
                            <select name="filtro-etapa" id="filtro-etapa" class="form-control"
                                style="display:inline;width:120px">
                                    <option value="0" 
                                        <?=isset($_GET["filtro-etapa"]) &&
                                           htmlspecialchars($_GET["filtro-etapa"]) == "0" ?
                                            'selected="selected"': '' ;?> >Todas
                                    </option>
                                    <option value="1" 
                                        <?=isset($_GET["filtro-etapa"]) &&
                                            htmlspecialchars($_GET["filtro-etapa"]) == "1" ?
                                            'selected="selected"': '' ;?> >1
                                    </option>
                                    <option value="2"
                                        <?=isset($_GET["filtro-etapa"]) &&
                                            htmlspecialchars($_GET["filtro-etapa"]) == "2"?
                                            'selected="selected"':'';?> >  2
                                    </option>
                                    <option value="3"
                                        <?=isset($_GET["filtro-etapa"]) &&
                                            htmlspecialchars($_GET["filtro-etapa"]) == "3"?
                                            'selected="selected"':'';?> >      3
                                    </option>
                                    <option value="4"
                                        <?=isset($_GET["filtro-etapa"]) &&
                                            htmlspecialchars($_GET["filtro-etapa"]) == "4"?
                                           'selected="selected"':'';?> >       4
                                    </option>
                            </select>

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
                                    htmlspecialchars($_GET["pagina-ipp"]) : 10 ?> />

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
                            <table class="table table-bordered table-striped" id="defs-trabalho">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="420px" <?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Título</th>
                                        <th width="100px" <?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Etapa</th>
                                        <th width="120px" <?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data de entrega</th>
                                        <th width="60px">Ano</th>
                                        <?php
                                            // dá ao admin as opções de editar e excluir
                                            if(unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){
                                        ?>
                                        <th width="60px">Editar</th>
                                        <th width="60px">Excluir</th>
                                        <?php
                                            }else if(unserialize($_SESSION["usuario"])->getNivelAdmin() === "professor"){
                                        ?>
                                            <th width="130px">Visualizar descrição</th>
                                        <?php
                                                if(unserialize($_SESSION["usuario"])->getCorrigeTrabalho()) {
                                        ?>
                                            <th width="130px">Trabalhos recebidos</th> 
                                        <?php
                                                }
                                            }
                                        ?>
    
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
        <!-- popup "modal" do bootstrap para inserção de nova definição de trabalho -->
        <div class="modal fade" id="modal-novo-def-trabalho" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-def-trabalho" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_definicoes_trabalho.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Nova definição de trabalho</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="titulo-novo">Título da definição de trabalho:</label>
                                <input type="text" name="titulo" id="titulo-novo" required
                                       pattern="^.{3,300}$" title="O título deve ter de 3 a 300 caracteres"
                                       placeholder="Titulo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="etapa-novo">Etapa referente ao trabalho:</label>
                                <select name="etapa" id="etapa-novo" required
                                       title="A etapa deve ser um número de 1 a 4"
                                       class="form-control">
                                       <option value="1">1</option>
                                       <option value="2">2</option>
                                       <option value="3">3</option>
                                       <option value="4">4</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data-novo">Data de entrega:</label>
                                <input type="date" name="data" id="data-novo" required
                                       title="A data deve ser preenchida"
                                       placeholder="dd/mm/aaaa" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="descricao-novo">Descrição do trabalho:</label>
                                <textarea name="descricao" id="descricao-novo" rows="8" cols="50"
                                    maxlength="10000" required
                                    title="O conteúdo da definição de trabalho deve ser preenchido e ter até
                                           10000 caracteres"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir definição de trabalho
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de definição de trabalho -->
        <div class="modal fade" id="modal-edita-def-trabalho" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-def-trabalho" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/definicoes_trabalho/editar_definicao_trabalho.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar definição de trabalho</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <label for="titulo">Título da definição de trabalho:</label>
                                <input type="text" name="titulo" id="titulo" required
                                       pattern="^.{3,300}$" title="O título deve ter de 3 a 300 caracteres"
                                       placeholder="Titulo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="etapa">Etapa referente ao trabalho:</label>
                                <select name="etapa" id="etapa" required
                                       title="A etapa deve ser um número de 1 a 4"
                                       class="form-control">
                                       <option value="1">1</option>
                                       <option value="2">2</option>
                                       <option value="3">3</option>
                                       <option value="4">4</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="data">Data de entrega:</label>
                                <input type="date" name="data" id="data" required
                                       title="A data deve ser preenchida"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="descricao">Descrição do trabalho:</label>
                                <textarea name="descricao" id="descricao" rows="8" cols="50"
                                    maxlength="10000" required
                                    title="O conteúdo da definição de trabalho deve ser preenchido e ter até
                                           10000 caracteres"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar definição de trabalho
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de definição de trabalho -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de definição de trabalho</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover "<span id="nome-def-trabalho"></span>"?</h3>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                        <a href="#" class="btn btn-danger danger">Sim</a>
                    </div>
                </div>
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