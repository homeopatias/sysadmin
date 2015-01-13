<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Aulas desse ano - Homeopatias.com</title>
        <!-- plugin de JQuery para tornar tabelas ordenáveis -->
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <!-- polyfill para funcionalidades do HTML5 -->
        <script src="./webshim-1.14.5/polyfiller.js"></script>
        <script>
            // usamos um polyfill para que os campos de data e hora funcionem mesmo
            // em navegadores que não implementem essas funcionalidades (você sabe quais)

            webshims.activeLang("pt-BR");
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date', replaceUI: true});
            webshims.polyfill('forms forms-ext');

            var podeMudarPagina = true;
            $(document).ready(function(){

                var nomesCidades = new Array();

                <?php
                    // lemos as credenciais do banco de dados
                    $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                    $dados = json_decode($dados, true);

                    foreach($dados as $chave => $valor) {
                        $dados[$chave] = str_rot13($valor);
                    }

                    $host    = $dados["host"];
                    $usuario = $dados["nome_usuario"];
                    $senhaBD = $dados["senha"];

                    // cria conexão com o banco para ser usada ao longo da página
                    $conexao = null;
                    $host    = "localhost";
                    $db      = "homeopatias";
                    try{
                        $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                    }catch (PDOException $e){
                        echo $e->getMessage();
                    }

                    $textoQuery  = "SELECT idCidade, UF, nome
                                    FROM Cidade ORDER BY ano DESC, nome ASC";

                    $query = $conexao->prepare($textoQuery);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    while ($linha = $query->fetch()){
                        $id   = "\"".htmlspecialchars($linha["idCidade"])."\"";
                        $uf   = "\"".htmlspecialchars($linha["UF"])."\"";
                        $nome = "\"".htmlspecialchars($linha["nome"])."\"";

                ?>

                nomesCidades.push({
                    id:   <?= $id ?>,
                    nome: <?= $nome . " + \"/\" + " . $uf ?>
                });

                <?php
                    }
                ?>

                // permite redimensionar as colunas da tabela
                $("#aulas").colResizable({
                    liveDrag: true,
                    minWidth: 60
                });

                // torna a tabela ordenável pelas colunas

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

                $("#aulas").tablesorter({ headers: {
                    3 : { sorter: "datetime" },
                    4 : { sorter: false }
                }});

                // passa os dados da aula para o modal de visualização
                $("#modal-visualiza-aula").on('show.bs.modal', function(e) {

                    // formatamos a data para visualização
                    var data = new Date($(e.relatedTarget).parent().siblings('.data').data("data-html"));
                    var ano = data.getFullYear();
                    var mes = data.getMonth() < 9 ? "0": "";
                    var dia = data.getDate() < 10 ? "0": "";
                    var hora = data.getHours() < 10 ? "0": "";
                    var minuto = data.getMinutes() < 10 ? "0": "";
                    mes = mes + "" + (data.getMonth() + 1);
                    dia = dia + "" + data.getDate();
                    hora = hora + "" + data.getHours();
                    minuto = minuto + "" + data.getMinutes();

                    $(this).find('#data-hora').text(dia + "/" + mes + "/" + data.getFullYear() +
                                                   " às " + hora + ":" + minuto);
                    $(this).find('#cidade').text(
                        $(e.relatedTarget).parent().siblings('.cidade').text()
                    );
                    $(this).find('#etapa').text(
                        $(e.relatedTarget).parent().siblings('.etapa').text()
                    );
                    $(this).find("#descricao").text(
                        $(e.relatedTarget).data("descricao")
                    );
                    console.log($(e.relatedTarget));
                });

                // esconde inputs de busca
                $("#filtro-etapa").hide();
                $("#filtro-cidade").hide();
                $("#div-data-min").hide();
                $("#div-data-max").hide();
                $("#ipp").hide(); 

                $("#label-cidade").click(function(){
                    $(this).hide();
                    $("#filtro-cidade").show(300);
                    $("#filtro-cidade").focus();
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

                $("#label-etapa").click(function(){
                    $(this).hide();
                    $("#filtro-etapa").show(300);
                    $("#filtro-etapa").focus();
                });

                $("#label-ipp").click(function(){
                    $(this).hide();
                    $("#ipp").show(300);
                    $("#ipp").focus();
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

                $("#form-filtro input").change(function(){
                    podeMudarPagina = false;
                });

                //remove inputs em branco do form antes de enviar
                $("#form-filtro").submit(function(){

                    $(':input', this).each(function() {
                         this.disabled = !($(this).val());
                    });

                    if($('#filtro-cidade').val() == 0) {
                        $('#filtro-cidade')[0].disabled = true;
                    }
                    if($('#filtro-etapa').val() == 0) {
                        $('#filtro-etapa')[0].disabled = true;
                    }

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

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-cidade").val("");
                    $("#filtro-etapa").val("");
                    $("#filtro-data-min").val("");
                    $("#filtro-data-max").val("");
                    atualizaPagina();
                });



                // preenche o select de cidades

                // se há cidade filtrada, seleciona ela
                // remove os sinais de + que são passados e transforma em uma entidade html
                var selecionado = <?= isset($_GET["filtro-cidade"]) ?
                             htmlspecialchars(str_replace("+","",$_GET["filtro-cidade"]) ) : "0"?>;


                // A primeira opção indica nenhuma cidade
                var opcao = '<option value= 0>Nenhuma</option>';
                            $("#filtro-cidade").append(opcao);
                nomesCidades.forEach(function(cidade){
                    if(selecionado != "0" && selecionado == cidade.id){
                        var opcao = '<option value=" '+ cidade.id +' " selected = selected>'
                            + cidade.nome + '</option>';
                        $("#filtro-cidade").append(opcao);

                    }
                    else{
                        var opcao = '<option value=" '+ cidade.id +' ">'
                            + cidade.nome + '</option>';
                        $("#filtro-cidade").append(opcao);
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
                $("#Spagina").val(0);
                $("#form-filtro").submit();
            }

            //------------Checa se tamanho minimo da tela é o tamanho minimo do css
            function checaTamanhoTela(){
                tamanhoTela = $(window).width();

                if (tamanhoTela < 700) {
                    $("table").colResizable({
                        disable:true
                    }); 
                    $(".flip-scroll th").css("width","100px");
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

            // mensagem a ser exibida acima da listagem de aulas, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe aulas apenas para alunos logados
            if(isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Aluno &&
                unserialize($_SESSION['usuario'])->getStatus() === 'inscrito'){

                $textoQuery  = "SELECT A.chaveCidade, A.etapa, A.data, A.descricao,
                                P.nome FROM Aula A INNER JOIN Administrador Ad
                                ON Ad.idAdmin = A.idProfessor INNER JOIN Usuario P ON 
                                Ad.idUsuario = P.id INNER JOIN Cidade C ON
                                A.chaveCidade = C.idCidade INNER JOIN Matricula M ON
                                M.chaveCidade = C.idCidade WHERE M.chaveAluno = :chaveAluno
                                AND C.ano = YEAR(CURDATE()) AND A.etapa = M.etapa";
                
                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroProfessor = $filtroEtapa = $filtroDataMin = $filtroDataMax = false;
                $filtroCidade    = false;
                if(isset($_GET["filtro-professor"]) || isset($_GET["filtro-etapa"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"]) ||
                   isset($_GET["filtro-cidade"])){
                    
                    $queryWhere = "WHERE"; // armazena os filtros nesta variável para impedir filtros
                                           // vazios, pois a query padrão não usa WHERE

                    $filtroCidade     =  htmlspecialchars($_GET["filtro-cidade"]);
                    $filtroEtapa      =  htmlspecialchars($_GET["filtro-etapa"]);
                    $filtroProfessor  =  htmlspecialchars($_GET["filtro-professor"]);
                    $filtroDataMin    =  htmlspecialchars($_GET["filtro-data-min"]);
                    $filtroDataMax    =  htmlspecialchars($_GET["filtro-data-max"]);

                    if(isset($filtroProfessor) && mb_strlen($filtroProfessor) > 0 && 
                        !is_nan($filtroProfessor) && $filtroProfessor != "0"){

                        if(mb_strlen($queryWhere) == 5){
                            // Se não há filtro anterior, adiciona como primeiro
                            $queryWhere .= " idProfessor = :filtroprofessor ";
                        }
                        else{
                            $queryWhere .= " AND idProfessor = :filtroprofessor ";
                        }
                        
                    }  
                    if(isset($filtroCidade) && mb_strlen($filtroCidade) > 0 && 
                        !is_nan($filtroCidade) && $filtroCidade != "0"){

                        if(mb_strlen($queryWhere) == 5){ 

                            $queryWhere .= " chaveCidade = :filtrocidade ";
                        }
                        else{
                            $queryWhere .= " AND chaveCidade = :filtrocidade ";
                        }
                        
                    }  
                    if(isset($filtroEtapa) && mb_strlen($filtroEtapa) > 0 && 
                        !is_nan($filtroEtapa) && $filtroEtapa != "0"){
                        
                        if(mb_strlen($queryWhere) == 5){

                            $queryWhere .= " etapa LIKE :filtroetapa ";
                        }
                        else{
                            $queryWhere .= " AND etapa LIKE :filtroetapa ";
                        }
                        
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        if(mb_strlen($queryWhere) == 5){ 

                            $queryWhere .= " CAST(data AS Date) >= ";
                        }
                        else{
                            $queryWhere .= " AND CAST(data AS Date) >= ";
                        }
                        $queryWhere .= "CAST(:dataMin as Date)";

                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        if(mb_strlen($queryWhere) == 5){ 

                            $queryWhere .= " CAST(data AS Date) <= ";
                        }
                        else{
                            $queryWhere .= " AND CAST(data AS Date) <= ";
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
                $orderBy = " ORDER BY data DESC " ;
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
                                $orderBy = " ORDER BY A.chaveCidade " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY A.idProfessor " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY A.etapa " ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY A.data " ;
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

                if(isset($_GET["filtro-professor"]) || isset($_GET["filtro-etapa"]) ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"]) ||
                   isset($_GET["filtro-cidade"])){
                    if(isset($filtroProfessor) && mb_strlen($filtroProfessor) > 0 && 
                        !is_nan($filtroProfessor) && $filtroProfessor != "0"){
                        $query->bindParam(":filtroprofessor",$filtroProfessor);
                    }
                    if(isset($filtroCidade) && mb_strlen($filtroCidade) > 0 && 
                        !is_nan($filtroCidade) && $filtroCidade != "0"){
                        $query->bindParam(":filtrocidade",$filtroCidade);
                    }
                    if(isset($filtroEtapa) && mb_strlen($filtroEtapa) > 0  &&
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

                $query->bindParam(":chaveAluno",
                                  unserialize($_SESSION["usuario"])->getNumeroInscricao());

                $query->setFetchMode(PDO::FETCH_ASSOC);

                $query->execute();

                $numeroRegistros = $query->rowCount();

                $possuiProximaPagina = false;
                $contador = 0;
                $tabela = "";

                while ($linha = $query->fetch()){
                    if($contador != $itemsPorPagina){

                        // listamos os dados de cada aula
                        $tabela .= "<tr>";
    
                        require_once("entidades/Cidade.php");
                        $cidade = new Cidade();
                        $cidade->setIdCidade($linha["chaveCidade"]);
                        $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);
    
                        $tabela .= "    <td class=\"cidade\" data-id-cidade=\"";
                        $tabela .= $linha["chaveCidade"]."\">";
                        $tabela .= htmlspecialchars($cidade->getNome())             ."</td>";
                        $tabela .= "    <td class=\"professor\">";
                        $tabela .= htmlspecialchars($linha["nome"])             ."</td>";
                        $tabela .= "    <td class=\"etapa\">";
                        $tabela .= htmlspecialchars($linha["etapa"])                ."</td>";
                        $tabela .= "    <td class=\"data\" data-data-html=\"";
                        $tabela .= str_replace("-", "/", $linha["data"])."\">";
                        $tabela .= date("d/m/Y H:i", strtotime($linha["data"])) ."</td>";
    
                        $tabela .= "    <td><a data-descricao=\"";
                        $tabela .= htmlspecialchars($linha["descricao"]);
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-visualiza-aula\">";
                        $tabela .= "<i class=\"fa fa-eye\"></i></a></td>";
    
                        $tabela .= "</tr>";
    
                    }
                    else{
                        $possuiProximaPagina = true;
                    }
                    $contador++;
                }          
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>Aulas do ano atual</h1>
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="aulas_aluno.php" id="form-filtro">
                        <div class="form-group">
                            <br/>
                            <p>
                                <b>Buscar por:</b>
                            </p>
                            <a id="label-cidade" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-cidade"]) && 
                                        mb_strlen(($_GET["filtro-cidade"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-cidade"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Cidade
                            </a>
                            <select name="filtro-cidade" id="filtro-cidade" 
                                        class="form-control"
                                        style="display:inline;width:120px">
                            </select>
                            <a id="label-etapa" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-etapa"]) && 
                                        mb_strlen(($_GET["filtro-etapa"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-etapa"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Etapa
                            </a>

                                <select name="filtro-etapa" id="filtro-etapa" class="form-control"
                                        style="display:inline;width:120px">
                                        <option value="0" 
                                            <?=isset($_GET["filtro-etapa"]) &&
                                                htmlspecialchars($_GET["filtro-etapa"]) == "0" ?
                                                'selected="selected"': '' ;?> >Nenhuma
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
                                >Data desde
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
                                >Data até
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
                            <table class="table table-bordered table-striped" id="aulas">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="150px" <?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Cidade</th>
                                        <th width="150px" <?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Professor</th>
                                        <th width="40px" <?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Etapa</th>
                                        <th width="100px" <?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data e horário</th>
                                        <th width="100px">Visualizar detalhes</th>
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
        <!-- popup "modal" do bootstrap para visualização de aula -->
        <div class="modal fade" id="modal-visualiza-aula" tabindex="-1" role="dialog" 
             aria-labelledby="modal-visualiza-aula" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Detalhes da aula</h4>
                    </div>
                    <div class="modal-body">
                        <b>
                            Aula do dia <span id="data-hora"></span>, na cidade de 
                            <span id="cidade"></span>
                        </b><br>
                        <b>
                            <span id="etapa"></span>ª etapa
                        </b><br><br>
                        <b>
                            Detalhes da aula: <br><br>
                            <span id="descricao"></span>
                        </b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                            Fechar
                        </button>
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
            window.location = "index.php?mensagem=Apenas alunos inscritos podem ver as aulas";
        </script>
        <?php
                die();
            }
            include("modulos/rodape.php");
        ?>
    </body>
</html>
