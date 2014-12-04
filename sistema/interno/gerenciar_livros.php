<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Livros - Homeopatias.com</title>
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
                $("#livros").colResizable({
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

                $("#livros").tablesorter({ headers: {
                    1 : { sorter: false },
                    7 : { sorter: false },
                    8 : { sorter: false }
                }});
                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-livro').text(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                });
                // passa os dados do livro para o modal para a edição
                $("#modal-edita-livro").on('show.bs.modal', function(e) {

                    $(this).find('#id').val(
                        $(e.relatedTarget).data('id')
                    );
                    $(this).find('#nome').val(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                    $(this).find('#edicao').val(
                        $(e.relatedTarget).parent().siblings('.edicao').text()
                    );
                    $(this).find('#autor').val(
                        $(e.relatedTarget).parent().siblings('.autor').text()
                    );
                    $(this).find('#editora').val(
                        $(e.relatedTarget).parent().siblings('.editora').text()
                    );
                    $(this).find('#preco').val(
                        $(e.relatedTarget).parent().siblings('.preco').text()
                                          .substring(3).replace(/\s+/g, '')
                    );
                    $(this).find('#quantidade').val(
                        $(e.relatedTarget).parent().siblings('.quantidade').text()
                    );

                    // formatamos a data para insercao no formulário de edição
                    var data = new Date($(e.relatedTarget).data("data-html"));
                    var ano = data.getFullYear();
                    var mes = data.getMonth() < 9 ? "0": "";
                    var dia = data.getDate() < 10 ? "0": "";
                    mes = mes + "" + (data.getMonth() + 1);
                    dia = dia + "" + (data.getDate()  + 1);

                    $(this).find('#dataPublic').val(data.getFullYear() + "-" + mes + "-" + dia);
                    var inputPolyfill = $(this).find('#dataPublic').siblings("input");
                    if(inputPolyfill.length > 0)
                        inputPolyfill[0].value = dia + "/" + mes + "/" + data.getFullYear();

                    $(this).find('#fornecedor').val(
                        $(e.relatedTarget).parent().siblings('.fornecedor').text()
                    );
                });
                
                // esconde inputs de busca

                $("#filtro-titulo").hide();
                $("#filtro-edicao").hide();
                $("#filtro-autor").hide();
                $("#filtro-editora").hide();
                $("#filtro-qtd-min").hide();
                $("#filtro-qtd-max").hide();
                $("#filtro-fornecedor").hide();
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

                $("#label-edicao").click(function(){
                    $(this).hide();
                    $("#filtro-edicao").show(300);
                    $("#filtro-edicao").focus();
                });

                $("#filtro-edicao").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-edicao").show(300);   
                    } 
                });

                $("#label-autor").click(function(){
                    $(this).hide();
                    $("#filtro-autor").show(300);
                    $("#filtro-autor").focus();
                });

                $("#filtro-autor").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-autor").show(300);   
                    }
                });

                $("#label-editora").click(function(){
                    $(this).hide();
                    $("#filtro-editora").show(300);
                    $("#filtro-editora").focus();
                });

                $("#filtro-editora").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-editora").show(300);   
                    } 
                });

                $("#label-qtd-min").click(function(){
                    $(this).hide();
                    $("#filtro-qtd-min").show(300);
                    $("#filtro-qtd-min").focus();
                });

                $("#filtro-qtd-min").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-qtd-min").show(300);   
                    } 
                });

                $("#label-qtd-max").click(function(){
                    $(this).hide();
                    $("#filtro-qtd-max").show(300);
                    $("#filtro-qtd-max").focus();
                });

                $("#filtro-qtd-max").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-qtd-max").show(300);   
                    }
                });

                $("#label-fornecedor").click(function(){
                    $(this).hide();
                    $("#filtro-fornecedor").show(300);
                    $("#filtro-fornecedor").focus();
                });

                $("#filtro-fornecedor").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-fornecedor").show(300);   
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

                //se mudou a quantidade de pessoas por página, atualiza
                $("#ipp").change(function(){
                    $("#pagina").val(0);
                    $("#pagina-ipp").val( $(this).val() );
                    atualizaPagina();
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

                $("#filtro-edicao").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-autor").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-editora").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-qtd-min").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-qtd-max").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-fornecedor").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
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

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-titulo").val("");
                    $("#filtro-autor").val("");
                    $("#filtro-edicao").val("");
                    $("#filtro-editora").val("");
                    $("#filtro-qtd-min").val("");
                    $("#filtro-qtd-max").val("");
                    $("#filtro-fornecedor").val("");
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

            // mensagem a ser exibida acima da listagem de livros, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe livros apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
               8 & unserialize($_SESSION["usuario"])->getPermissoes()){

                // se o usuário chegou até aqui através de um formulário, registra o novo
                // livro no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $nome       = $_POST["nome"];
                    $edicao     = $_POST["edicao"];
                    $autor      = $_POST["autor"];
                    $editora    = $_POST["editora"];
                    $preco      = $_POST["preco"];
                    $quantidade = $_POST["quantidade"];
                    $data       = $_POST["dataPublic"];
                    $fornecedor = $_POST["fornecedor"];

                    $nomeValido    = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                                     mb_strlen($nome, 'UTF-8') <= 500;
                    $edicaoValido  = isset($edicao) && preg_match("/^\d+$/", $edicao);
                    $autorValido   = isset($autor) && mb_strlen($autor, 'UTF-8') >= 3 &&
                                     mb_strlen($autor, 'UTF-8') <= 100;
                    $editoraValida = isset($editora) && mb_strlen($editora, 'UTF-8') >= 3 &&
                                     mb_strlen($editora, 'UTF-8') <= 100;
                    $precoValido   = isset($preco) && preg_match("/^[0-9]*\.?[0-9]+$/", $preco);
                    $quantValida   = isset($edicao) && preg_match("/^\d+$/", $edicao);
                    $dataValida    = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
                    $fornecValido  = isset($fornecedor) && mb_strlen($fornecedor, 'UTF-8') >= 3 &&
                                     mb_strlen($fornecedor, 'UTF-8') <= 200;

                    // se todos os dados estão válidos, o livro é cadastrado
                    if($nomeValido && $edicaoValido && $autorValido && $editoraValida &&
                       $precoValido && $quantValida && $dataValida && $fornecValido){

                        // lemos as credenciais do banco de dados
                        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                        $dados = json_decode($dados, true);

                        foreach($dados as $chave => $valor) {
                            $dados[$chave] = str_rot13($valor);
                        }

                        $host    = $dados["host"];
                        $usuario = $dados["nome_usuario"];
                        $senhaBD = $dados["senha"];

                        require_once("entidades/Livro.php");

                        $novo = new Livro();
                        $novo->setNome($nome);
                        $novo->setEdicao($edicao);
                        $novo->setAutor($autor);
                        $novo->setEditora($editora);
                        $novo->setPreco($preco);
                        $novo->setQuantidade($quantidade);
                        $novo->setDataPublicacao($data);
                        $novo->setFornecedor($fornecedor);

                        $sucesso = $novo->cadastrar($host, "homeopatias", $usuario, $senhaBD);

                        if(!$sucesso){
                            $mensagem = "Erro";
                        }
                    }else if(!$nomeValido){
                        $mensagem = "Nome inválido!";
                    }else if(!$edicaoValido){
                        $mensagem = "Edição inválida!";
                    }else if(!$autorValido){
                        $mensagem = "Nome de autor inválido!";
                    }else if(!$editoraValida){
                        $mensagem = "Nome da editora inválido!";
                    }else if(!$precoValido){
                        $mensagem = "Preço inválido!";
                    }else if(!$quantValida){
                        $mensagem = "Quantidade inválida!";
                    }else if(!$dataValida){
                        $mensagem = "Data inválida!";
                    }else if(!$fornecValido){
                        $mensagem = "Nome de fornecedor inválido!";
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

                $textoQuery  = "SELECT idLivro, valor, quantidade, nome, autor, editora, 
                                dataPublic, edicao, fornecedor 
                                FROM Livro ";

                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroTitulo = $filtroEdicao = $filtroAutor      =  $filtroEditora = false;
                $filtroQtdMax = $filtroQtdMin = $filtroFornecedor = false;

                if(isset($_GET["filtro-titulo"]) || isset($_GET["filtro-autor"])){
                    $queryWhere = "WHERE"; // armazena os filtros nesta variável para impedir filtros
                                           // vazios, pois a query padrão não usa WHERE

                    $filtroTitulo     =  htmlspecialchars($_GET["filtro-titulo"]);
                    $filtroEdicao     =  htmlspecialchars($_GET["filtro-edicao"]);
                    $filtroAutor      =  htmlspecialchars($_GET["filtro-autor"]);
                    $filtroEditora    =  htmlspecialchars($_GET["filtro-editora"]);
                    $filtroQtdMin     =  htmlspecialchars($_GET["filtro-qtd-min"]);
                    $filtroQtdMax     =  htmlspecialchars($_GET["filtro-qtd-max"]);
                    $filtroFornecedor =  htmlspecialchars($_GET["filtro-fornecedor"]);

                    if(isset($filtroTitulo) && mb_strlen($filtroTitulo) > 0){
                        $filtroTitulo   =  "%".$filtroTitulo."%";
                        $queryWhere    .= " nome LIKE :filtrotitulo ";
                    }  
                    if(isset($filtroAutor) && mb_strlen($filtroAutor) > 0){
                        $filtroAutor   =  "%".$filtroAutor."%";
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " autor LIKE :filtroautor ";
                        }
                        else{
                            $queryWhere .= " AND autor LIKE :filtroautor ";
                        }
                    }      
                    if(isset($filtroEdicao) && mb_strlen($filtroEdicao) > 0 &&
                        !is_nan($filtroEdicao)){
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " edicao = :filtroedicao ";
                        }
                        else{
                            $queryWhere .= " AND edicao = :filtroedicao ";
                        }
                    }    
                    if(isset($filtroAutor) && mb_strlen($filtroAutor) > 0){
                        $filtroAutor   =  "%".$filtroAutor."%";
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " autor LIKE :filtroautor ";
                        }
                        else{
                            $queryWhere .= " AND autor LIKE :filtroautor ";
                        }
                    }
                    if(isset($filtroEditora) && mb_strlen($filtroEditora) > 0){
                        $filtroEditora   =  "%".$filtroEditora."%";
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " editora LIKE :filtroeditora ";
                        }
                        else{
                            $queryWhere .= " AND editora LIKE :filtroeditora ";
                        }
                    }


                    if(isset($filtroQtdMin) && mb_strlen($filtroQtdMin) > 0 &&
                        !is_nan($filtroQtdMin) && $filtroQtdMin > 0){
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " quantidade >= :filtroqtdmin ";
                        }
                        else{
                            $queryWhere .= " AND quantidade >= :filtroqtdmin ";
                        }
                    }
                    if(isset($filtroQtdMax) && mb_strlen($filtroQtdMax) > 0 &&
                        !is_nan($filtroQtdMax) && $filtroQtdMax > 0){
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " quantidade <= :filtroqtdmax ";
                        }
                        else{
                            $queryWhere .= " AND quantidade <= :filtroqtdmax ";
                        }
                    }


                    if(isset($filtroFornecedor) && mb_strlen($filtroFornecedor) > 0){
                        $filtroFornecedor   =  "%".$filtroFornecedor."%";
                        if(mb_strlen($queryWhere) == 5){ // Se não há filtro anterior, adiciona como primeiro

                            $queryWhere .= " fornecedor LIKE :filtrofornecedor ";
                        }
                        else{
                            $queryWhere .= " AND fornecedor LIKE :filtrofornecedor ";
                        }
                    }
                    if($queryWhere != "WHERE"){ // So sera colocada se for adicionado algo, para não
                                                // adicionar WHERE vazio

                        $textoQuery .= " ".$queryWhere;
                        


                    }
                    
                }

                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY idLivro DESC " ;
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
                                $orderBy = " ORDER BY nome " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY autor " ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY editora " ;
                                break;
                            case '4':
                                $orderBy = " ORDER BY valor " ;
                                break;
                            case '5':
                                $orderBy = " ORDER BY quantidade " ;
                                break;
                            case '6':
                                $orderBy = " ORDER BY fornecedor " ;
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
                if(isset($_GET["filtro-titulo"]) || isset($_GET["filtro-etapa"]) ){
                    if(isset($filtroTitulo) && mb_strlen($filtroTitulo) > 0){
                        $query->bindParam(":filtrotitulo",$filtroTitulo);
                    }
                    if(isset($filtroEdicao) && mb_strlen($filtroEdicao) > 0 &&
                        !is_nan($filtroEdicao)){
                        $query->bindParam(":filtroedicao",$filtroEdicao);
                    }
                    if(isset($filtroAutor) && mb_strlen($filtroAutor) > 0){
                        $query->bindParam(":filtroautor",$filtroAutor);
                    }
                    if(isset($filtroEditora) && mb_strlen($filtroEditora) > 0){
                        $query->bindParam(":filtroeditora",$filtroEditora);
                    }
                    if(isset($filtroQtdMin) && mb_strlen($filtroQtdMin) > 0 &&
                        !is_nan($filtroQtdMin) && $filtroQtdMin > 0){
                        $query->bindParam(":filtroqtdmin",$filtroQtdMin);
                    }
                    if(isset($filtroQtdMax) && mb_strlen($filtroQtdMax) > 0 &&
                        !is_nan($filtroQtdMax) && $filtroQtdMax > 0){
                        $query->bindParam(":filtroqtdmax",$filtroQtdMax);
                    }
                    if(isset($filtroFornecedor) && mb_strlen($filtroFornecedor) > 0){
                        $query->bindParam(":filtrofornecedor",$filtroFornecedor);
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

                        // listamos os dados de cada usuário
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"nome\">";
                        $tabela .= htmlspecialchars($linha["nome"])                ."</td>";
                        $tabela .= "    <td class=\"edicao\">";
                        $tabela .= htmlspecialchars($linha["edicao"])              ."</td>";
                        $tabela .= "    <td class=\"autor\">";
                        $tabela .= htmlspecialchars($linha["autor"])               ."</td>";
                        $tabela .= "    <td class=\"editora\">";
                        $tabela .= htmlspecialchars($linha["editora"])             ."</td>";
                        $tabela .= "    <td class=\"preco\">R$ ";
                        $tabela .= number_format(htmlspecialchars($linha["valor"]), 2, ".", " ")."</td>";
                        $tabela .= "    <td class=\"quantidade\">";
                        $tabela .= htmlspecialchars($linha["quantidade"])          ."</td>";
                        $tabela .= "    <td class=\"fornecedor\">";
                        $tabela .= htmlspecialchars($linha["fornecedor"])          ."</td>";
                        $tabela .= "    <td><a data-id=\"";
                        $tabela .= $linha["idLivro"];
                        $tabela .= "\" data-data-html=\"";
                        $tabela .= date("Y-m-d", strtotime($linha["dataPublic"]));
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-edita-livro\">";
                        $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                        $tabela .= "    <td><a data-href=\"rotinas/livro/remover_livro.php?id=";
                        $tabela .= $linha["idLivro"];
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
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>Livros</h1>
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-livro">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Novo livro</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_livros.php" id="form-filtro">
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
                            <a id="label-edicao" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-edicao"]) && 
                                        mb_strlen(($_GET["filtro-edicao"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Edição
                            </a>
                            <input  type="text" name="filtro-edicao" id="filtro-edicao"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-edicao"]) ? 
                                        htmlspecialchars($_GET["filtro-edicao"]) : "" ?> >
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

                            <a id="label-editora" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-editora"]) && 
                                        mb_strlen(($_GET["filtro-editora"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Editora
                            </a>
                            <input  type="text" name="filtro-editora" id="filtro-editora"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-editora"]) ? 
                                        htmlspecialchars($_GET["filtro-editora"]) : "" ?> >

                            <a id="label-qtd-min" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-qtd-min"]) && 
                                        mb_strlen(($_GET["filtro-qtd-min"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Quantidade mínima
                            </a>
                            <input  type="text" name="filtro-qtd-min" id="filtro-qtd-min"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-qtd-min"]) ? 
                                        htmlspecialchars($_GET["filtro-qtd-min"]) : "" ?> >

                            <a id="label-qtd-max" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-qtd-max"]) && 
                                        mb_strlen(($_GET["filtro-qtd-max"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Quantidade máxima
                            </a>
                            <input  type="text" name="filtro-qtd-max" id="filtro-qtd-max"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-qtd-max"]) ? 
                                        htmlspecialchars($_GET["filtro-qtd-max"]) : "" ?> >

                            <a id="label-fornecedor" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-fornecedor"]) && 
                                        mb_strlen(($_GET["filtro-fornecedor"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Fornecedor
                            </a>
                            <input  type="text" name="filtro-fornecedor" id="filtro-fornecedor"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-fornecedor"]) ? 
                                        htmlspecialchars($_GET["filtro-fornecedor"]) : "" ?> >

                            
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
                    <?php  if($numeroRegistros !== 0){ ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="livros">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="80px" <?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Título</th>
                                        <th width="40px">Edição</th>
                                        <th width="80px" <?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Autor</th>
                                        <th width="80px" <?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Editora</th>
                                        <th width="60px" <?= $indexHeader == 4 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Preço</th>
                                        <th width="80px" <?= $indexHeader == 5 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Quantidade</th>
                                        <th width="90px" <?= $indexHeader == 6 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Fornecedor</th>
                                        <th width="45px">Editar</th>
                                        <th width="45px">Excluir</th>
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
        <!-- popup "modal" do bootstrap para inserção de novo livro -->
        <div class="modal fade" id="modal-novo-livro" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-livro" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_livros.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Novo livro</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="nome-novo">Nome do livro:</label>
                                <input type="text" name="nome" id="nome-novo" required
                                       pattern="^.{3,500}$" title="O nome deve ter de 3 a 500 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edicao-novo">Edição:</label>
                                <input type="text" name="edicao" id="edicao-novo" required
                                       pattern="^\d+$" title="A edição do livro deve ser um número inteiro"
                                       placeholder="Edição" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="autor-novo">Autor do livro:</label>
                                <input type="text" name="autor" id="autor-novo" required
                                       pattern="^.{3,100}$" title="O nome do autor deve ter de 3 a 100 caracteres"
                                       placeholder="Autor" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="editora-novo">Editora:</label>
                                <input type="text" name="editora" id="editora-novo" required
                                       pattern="^.{3,100}$" placeholder="Editora"
                                       title="O nome da editora do livro deve ter de 3 a 100 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="preco-novo">Preço:</label>
                                <input type="text" name="preco" id="preco-novo" required
                                       pattern="^[0-9]*\.?[0-9]+$" placeholder="Preço do livro"
                                       title="O preco deve ser um número real"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="quantidade-novo">Quantidade:</label>
                                <input type="text" name="quantidade" id="quantidade-novo" required
                                       pattern="^\d+$" placeholder="Quantidade"
                                       title="A quantidade deve ser um número inteiro"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="dataPublic-novo">Data de publicação:</label>
                                <input type="date" name="dataPublic" id="dataPublic-novo" required
                                       placeholder="dd/mm/aaaa" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="fornecedor-novo">Fornecedor:</label>
                                <input type="text" name="fornecedor" id="fornecedor-novo" required
                                       pattern="^{3,200}$" placeholder="Fornecedor"
                                       title="O nome do fornecedor deve ter de 3 a 200 caracteres"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir livro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de livro -->
        <div class="modal fade" id="modal-edita-livro" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-livro" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/livro/editar_livro.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar livro</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="id" id="id" value="">
                            <div class="form-group">
                                <label for="nome">Nome do livro:</label>
                                <input type="text" name="nome" id="nome" required
                                       pattern="^.{3,500}$" title="O nome deve ter de 3 a 500 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edicao">Edição:</label>
                                <input type="text" name="edicao" id="edicao" required
                                       pattern="^\d+$" title="A edição do livro deve ser um número inteiro"
                                       placeholder="Edição" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="autor">Autor do livro:</label>
                                <input type="text" name="autor" id="autor" required
                                       pattern="^.{3,100}$" title="O nome do autor deve ter de 3 a 100 caracteres"
                                       placeholder="Autor" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="editora">Editora:</label>
                                <input type="text" name="editora" id="editora" required
                                       pattern="^.{3,100}$" placeholder="Editora"
                                       title="O nome da editora do livro deve ter de 3 a 100 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="preco">Preço:</label>
                                <input type="text" name="preco" id="preco" required
                                       pattern="^[0-9]*\.?[0-9]+$" placeholder="Preço do livro"
                                       title="O preco deve ser um número real"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="quantidade">Quantidade:</label>
                                <input type="text" name="quantidade" id="quantidade" required
                                       pattern="^\d+$" placeholder="Quantidade"
                                       title="A quantidade deve ser um número inteiro"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="dataPublic">Data de publicação:</label>
                                <input type="date" name="dataPublic" id="dataPublic" required
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="fornecedor">Fornecedor:</label>
                                <input type="text" name="fornecedor" id="fornecedor" required
                                       pattern="^{3,200}$" placeholder="Fornecedor"
                                       title="O nome do fornecedor deve ter de 3 a 200 caracteres"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar livro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de livro -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de livro</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover "<span id="nome-livro"></span>"?</h3>
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