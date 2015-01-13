<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Notícias - Homeopatias.com</title>
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
                $("#noticias").colResizable({
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

                $("#noticias").tablesorter({ headers: {
                    2 : { sorter: "datetime" },
                    3 : { sorter: false },
                    4 : { sorter: false }
                }});
                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-noticia').text(
                        $(e.relatedTarget).parent().siblings('.titulo').text()
                    );
                });
                // passa os dados da notícia para o modal para a edição
                $("#modal-edita-noticia").on('show.bs.modal', function(e) {
                    $(this).find('#id').val(
                        $(e.relatedTarget).data('id')
                    );
                    $(this).find('#titulo').val(
                        $(e.relatedTarget).parent().siblings('.titulo').text()
                    );
                    $(this).find('#autor').val(
                        $(e.relatedTarget).parent().siblings('.autor').text()
                    );
                    $(this).find('#conteudo').val(
                        $(e.relatedTarget).data('conteudo')
                    );
                });
                // esconde inputs de busca

                $("#filtro-titulo").hide();
                $("#filtro-autor").hide();
                $("#div-data-min").hide();
                $("#div-data-max").hide();
                $("#ipp").hide();   

                // alterna campos de texto com campos de input
                $("#label-titulo").click(function(){
                    $(this).hide();
                    $("#filtro-titulo").show(300);
                    $("#filtro-titulo").focus();
                });

                $("#label-autor").click(function(){
                    $(this).hide();
                    $("#filtro-autor").show(300);
                    $("#filtro-autor").focus();
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
                    $("#filtro-autor").val("");
                    $("#filtro-data-min").val("");
                    $("#filtro-data-max").val("");
                    atualizaPagina();
                });

                //se mudou a quantidade de pessoas por página, atualiza
                $("#ipp").change(function(){
                    $("#pagina").val(0);
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

                //remove inputs em branco do form antes de enviar
                $("#form-filtro").submit(function(){

                    $(':input', this).each(function() {
                         this.disabled = !($(this).val());
                    });

                    if($('#pagina').val() == 0) {
                        $('#pagina')[0].disabled = true;
                    }
                    if($('#pagina-ipp').val() == 10) {
                        $('#pagina-ipp')[0].disabled = true;
                    }
                    if($('#numeroTableHeader').val() == -1) {
                        $('#numeroTableHeader')[0].disabled = true;
                    }
                    if($('#cimaOuBaixo').val() == 2) {
                        $('#cimaOuBaixo')[0].disabled = true;
                    }

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

            // mensagem a ser exibida acima da listagem de notícias, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe notícias apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
               4 & unserialize($_SESSION["usuario"])->getPermissoes() ){

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

                // se o usuário chegou até aqui através de um formulário, registra a nova
                // noticia no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $titulo   = $_POST["titulo"];
                    $autor    = $_POST["autor"];
                    $conteudo = $_POST["conteudo"];

                    $tituloValido   = isset($titulo) && mb_strlen($titulo, 'UTF-8') >= 3 &&
                                      mb_strlen($titulo, 'UTF-8') <= 100;
                    $autorValido    = isset($autor) && mb_strlen($autor, 'UTF-8') >= 3 &&
                                      mb_strlen($autor, 'UTF-8') <= 100;
                    $conteudoValido = isset($conteudo) && mb_strlen($conteudo, 'UTF-8') <= 65535;

                    // se todos os dados estão válidos, a notícia é criada
                    if($tituloValido && $autorValido && $conteudoValido){

                        $comando  = "INSERT INTO Artigo (autor, titulo, conteudo, dataPublic, tipo) 
                                     VALUES (?, ?, ?, ?, 'noticia')";
                        $query = $conexao->prepare($comando);
                        $dados  = array($autor, $titulo, $conteudo, date("Y-m-d H:i:s"));
                        $sucesso = $query->execute($dados);

                        if($sucesso){
                            $mensagem = "";
                        }else{
                            $mensagem = "Erro na inserção de notícia";
                        }
                    }else if(!$tituloValido){
                        $mensagem = "Título inválido!";
                    }else if(!$autorValido){
                        $mensagem = "Autor inválido!";
                    }else if(!$conteudoValido){
                        $mensagem = "Conteúdo inválido!";
                    }
                }

                $textoQuery  = "SELECT idArtigo, autor, titulo, conteudo, dataPublic 
                                FROM Artigo WHERE tipo = 'noticia' ";
                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroTitulo = $filtroAutor = $filtroDataMin = $filtroDataMax = false;
                if(isset($_GET["filtro-titulo"]) || isset($_GET["filtro-autor"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"])){

                    $filtroAutor     =  htmlspecialchars($_GET["filtro-autor"]);
                    $filtroTitulo    =  htmlspecialchars($_GET["filtro-titulo"]);
                    $filtroDataMin   =  htmlspecialchars($_GET["filtro-data-min"]);
                    $filtroDataMax   =  htmlspecialchars($_GET["filtro-data-max"]);

                    if(isset($filtroTitulo) && mb_strlen($filtroTitulo) > 0){
                        $filtroTitulo    =  "%".mb_strtoupper($filtroTitulo)."%";
                        $textoQuery .= " AND UPPER(titulo) LIKE :filtrotitulo ";
                    }            
                    if(isset($filtroAutor) && mb_strlen($filtroAutor) > 0){
                        $filtroAutor    =  "%".mb_strtoupper($filtroAutor)."%";
                        $textoQuery .= " AND UPPER(autor) LIKE :filtroautor ";
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        $textoQuery .= " AND CAST(dataPublic AS Date) >= ";
                        $textoQuery .= "CAST(:dataMin as Date)";

                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        $textoQuery .= " AND CAST(dataPublic AS Date) <= ";
                        $textoQuery .= "CAST(:dataMax as Date)";
                    }
                }

                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY dataPublic DESC" ;
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
                                $orderBy = " ORDER BY autor " ;
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

                //---------SE algum index foi excolhido para ordenação, ordena---------
                
                if($indexHeader != -1){
                    $textoQuery .= $orderBy;
                }

                $textoQuery .= " LIMIT ".($itemsPorPagina+1).
                                " OFFSET ".(($pagina)*$itemsPorPagina);

                //---------------------------------------------------------------------

                $query = $conexao->prepare($textoQuery);

                if(isset($_GET["filtro-titulo"]) || isset($_GET["filtro-autor"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"])){
                    if(isset($filtroTitulo) && mb_strlen($filtroTitulo) > 0){
                        $query->bindParam(":filtrotitulo",$filtroTitulo);
                    }
                    if(isset($filtroAutor) && mb_strlen($filtroAutor) > 0){
                        $query->bindParam(":filtroautor",$filtroAutor);
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

                        // listamos os dados de cada noticia
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"titulo\">";
                        $tabela .= htmlspecialchars($linha["titulo"])                         ."</td>";
                        $tabela .= "    <td class=\"autor\">";
                        $tabela .= htmlspecialchars($linha["autor"])                          ."</td>";
                        $tabela .= "    <td class=\"dataPublic\" data-data-html=\"";
                        $tabela .= $linha["dataPublic"] . "\">";
                        $tabela .= date("d/m/Y H:i",
                                        strtotime(htmlspecialchars($linha["dataPublic"])))    ."</td>";
                        $tabela .= "    <td><a data-id=\"";
                        $tabela .= $linha["idArtigo"]."\" data-conteudo=\"";
                        $tabela .= htmlspecialchars($linha["conteudo"]);
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-edita-noticia\">";
                        $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                        $tabela .= "    <td><a data-href=\"rotinas/noticia/remover_noticia.php?id=";
                        $tabela .= $linha["idArtigo"];
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
                    <h1>Notícias</h1>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-noticia">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Nova notícia</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_noticias.php" id="form-filtro">
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

                            <a id="label-autor" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-autor"]) && 
                                        mb_strlen(($_GET["filtro-autor"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Autor
                            </a>
                            <input  type="text" name="filtro-autor" id="filtro-autor"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-autor"]) ? 
                                        htmlspecialchars($_GET["filtro-autor"]) : "" ?> >

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
                            <table class="table table-bordered table-striped" id="noticias">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="200px" <?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Título</th>
                                        <th width="200px" <?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Autor</th>
                                        <th width="150px" <?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data e hora de publicação</th>
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
        <!-- popup "modal" do bootstrap para inserção de nova notícia -->
        <div class="modal fade" id="modal-novo-noticia" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-noticia" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_noticias.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Nova notícia</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="titulo-nova">Título da notícia:</label>
                                <input type="text" name="titulo" id="titulo-nova" required
                                       pattern="^.{3,100}$" title="O título deve ter de 3 a 100 caracteres"
                                       placeholder="Titulo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="autor-nova">Nome do autor da notícia:</label>
                                <input type="text" name="autor" id="autor-nova" required
                                       pattern="^.{3,100}$" title="O nome do autor deve ter de 3 a 100 caracteres"
                                       placeholder="Autor" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="conteudo-nova">Conteúdo:</label>
                                <textarea name="conteudo" id="conteudo-nova" rows="8" cols="50"
                                    maxlength="65535" required
                                    title="O conteúdo da notícia deve ser preenchido e ter até
                                           65535 caracteres"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir noticia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de notícia -->
        <div class="modal fade" id="modal-edita-noticia" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-noticia" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/noticia/editar_noticia.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar notícia</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="id" id="id">
                            <div class="form-group">
                                <label for="titulo">Título da notícia:</label>
                                <input type="text" name="titulo" id="titulo" required
                                       pattern="^.{3,100}$" title="O título deve ter de 3 a 100 caracteres"
                                       placeholder="Titulo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="autor">Nome do autor da notícia:</label>
                                <input type="text" name="autor" id="autor" required
                                       pattern="^.{3,100}$" title="O nome do autor deve ter de 3 a 100 caracteres"
                                       placeholder="Autor" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="conteudo">Conteúdo:</label>
                                <textarea name="conteudo" id="conteudo" rows="8" cols="50"
                                    maxlength="65535" required
                                    title="O conteúdo da notícia deve ser preenchido e ter até
                                           65535 caracteres"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar notícia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de noticia -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de notícia</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover "<span id="nome-noticia"></span>"?</h3>
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
