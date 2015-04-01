<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Administradores - Homeopatias.com</title>
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <script>
            var podeMudarPagina = true;
            $(document).ready(function(){

                // permite redimensionar as colunas da tabela
                $("#administradores").colResizable({
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

                $("#administradores").tablesorter({ headers: {
                    3 : { sorter: "datetime" },
                    4 : { sorter: false },
                    5 : { sorter: false }
                }});
                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-administrador').text(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                });
                // passa os dados do administrador para o modal para a edição
                $("#modal-edita-admin").on('show.bs.modal', function(e) {
                    $(this).find('#id').val(
                        $(e.relatedTarget).data('id')
                    );
                    $(this).find('#idAdmin').val(
                        $(e.relatedTarget).data('id-admin')
                    );
                    $(this).find('#nome').val(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                    $(this).find('#email').val(
                        $(e.relatedTarget).parent().siblings('.email').text()
                    );
                    $(this).find('#login').val(
                        $(e.relatedTarget).parent().siblings('.login').text()
                    );

                    //Seta as permissões no menu de edição
                    var permissoes = $(e.relatedTarget).data('permissoes');
                    permissoes = parseInt(permissoes);
                    if( 1 & permissoes){
                        $(this).find("#pessoas").prop('checked', true);
                    }else{
                        $(this).find("#pessoas").prop('checked', false);
                    }
                    if( 2 & permissoes){
                        $(this).find("#curso").prop('checked', true);
                    }else{
                        $(this).find("#curso").prop('checked', false);
                    }
                    if( 4 & permissoes){
                        $(this).find("#site").prop('checked', true);
                    }else{
                        $(this).find("#site").prop('checked', false);
                    }
                    if( 8 & permissoes){
                        $(this).find("#miscelanea").prop('checked', true);
                    }else{
                        $(this).find("#miscelanea").prop('checked', false);
                    }
                    if( 32 & permissoes){
                        $(this).find("#financeiro").prop('checked', true);
                    }else{
                        $(this).find("#financeiro").prop('checked', false);
                    }
                });

                // esconde inputs de busca

                $("#filtro-nome").hide();
                $("#ipp").hide();  

                // alterna campos de texto com campos de input
                $("#label-nome").click(function(){
                    $(this).hide();
                    $("#filtro-nome").show(300);
                    $("#filtro-nome").focus();
                });

                $("#filtro-nome").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-nome").show(300);   
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

                $("#btn-muda-senha").click(function(){
                    $(this).hide();
                    $("#senha").parent().show(300);
                    $("#senha").focus();
                });

                // processa envio do formulário se enter for pressionado dentro de algum campo
                // do formulário de filtro

                // filtro-data-max e filtro-data-min envia o formulário usando .onblur()
                $("#filtro-nome").keypress(function(e){
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
                    $("#filtro-nome").val("");
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

                //Prepara as permissoes no formulário de cadastro-------
                $("form").submit(function(){
                    var permissoes = 0;
                    if( $(this).find("#permissoes") ){

                        if( $(this).find("#pessoas").is(':checked') ){
                            permissoes += 1;
                        }
                        if( $(this).find("#curso").is(':checked') ){
                            permissoes += 2;
                        }
                        if( $(this).find("#site").is(':checked') ){
                            permissoes += 4;
                        }
                        if( $(this).find("#miscelanea").is(':checked') ){
                            permissoes += 8;
                        }
                        if( $(this).find("#financeiro").is(':checked') ){
                            permissoes += 32;
                        }

                        $(this).find("#permissoes").val( permissoes );

                    }
                });


                //------------------------------------------------------

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

            // mensagem a ser exibida acima da listagem de administradores, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe administradores apenas para administradores logados que tenham permissão
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" &&
               16 & unserialize($_SESSION["usuario"])->getPermissoes() ){

                $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                $dados = json_decode($dados, true);

                foreach($dados as $chave => $valor) {
                    $dados[$chave] = str_rot13($valor);
                }

                $host      = $dados["host"];
                $usuario   = $dados["nome_usuario"];
                $senhaBD   = $dados["senha"];
            
                // cria conexão com o banco
                $conexao = null;
                $db      = "homeopatias";
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }                

                // se o usuário chegou até aqui através de um formulário, registra o novo
                // administrador no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $nome        = $_POST["nome"];
                    $email       = $_POST["email"];
                    $login       = $_POST["login"];
                    $senha       = $_POST["senha"];
                    $permissoes  = $_POST["permissoes"];

                    $nomeValido   = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                                    mb_strlen($nome,'UTF-8') <= 100;

                    $emailValido  = isset($email) && mb_strlen($email, 'UTF-8') <= 100 &&
                                    preg_match("/^.+\@.+\..+$/", $email);
    
                    $emailExistente = false;
                    if($emailValido){
                        //Checa se ja existe este email no sistema cadastrado como administrador
                        $textoQuery = "SELECT U.email
                                       FROM Usuario U , Administrador A
                                       WHERE U.id = A.idUsuario AND U.email = ? 
                                       AND A.nivel LIKE 'administrador'";
        
                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1,$email, PDO::PARAM_STR);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();
    
                        if($linha = $query->fetch()){
                            $emailValido = false;
                            $emailExistente = true;
                        }
                    }

                    $loginValido  = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                                    mb_strlen($login, 'UTF-8') <= 100;
                    $senhaValida  = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 &&
                                    mb_strlen($senha, 'UTF-8') <= 72;



                    // se todos os dados estão válidos, o administrador é cadastrado
                    if($nomeValido && $emailValido && $loginValido && $senhaValida){

                        // lemos as credenciais do banco de dados
                        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                        $dados = json_decode($dados, true);

                        foreach($dados as $chave => $valor) {
                            $dados[$chave] = str_rot13($valor);
                        }

                        $host    = $dados["host"];
                        $usuario = $dados["nome_usuario"];
                        $senhaBD = $dados["senha"];

                        require_once("entidades/Administrador.php");

                        $novo = new Administrador($login);
                        $novo->setNome($nome);
                        $novo->setEmail($email);
                        $novo->setPermissoes($permissoes);
                        $novo->setNivelAdmin("administrador");

                        $sucesso = $novo->cadastrar($host, "homeopatias", $usuario, $senhaBD, $senha);

                        if(!$sucesso){
                            $mensagem = "Já existe um usuário com esse nome 
                                         de usuário no sistema";
                        }
                    }else if(!$nomeValido){
                        $mensagem = "Nome inválido!";
                    }else if(!$emailValido && !$emailExistente){
                        $mensagem = "E-mail inválido!";
                    }else if($emailExistente){
                        $mensagem = "E-mail ja cadastrado!";
                    }else if(!$loginValido){
                        $mensagem = "Nome de usuário inválido!";
                    }else if(!$senhaValida){
                        $mensagem = "Senha inválida!";
                    }
                }

                $textoQuery  = "SELECT U.id, U.dataInscricao, U.email, 
                                U.nome, U.login, A.idAdmin , A.permissoes
                                FROM Usuario U, Administrador A WHERE A.idUsuario = U.id AND 
                                A.nivel = \"administrador\" ";

                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroNome = false;
                if(isset($_GET["filtro-nome"])){

                    $filtroNome    =  htmlspecialchars($_GET["filtro-nome"], ENT_QUOTES, "UTF-8");
                    // Os dois argumentos acima são para o UTF-8 funcionar corretamente
                    // após a conversão
                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $filtroNome    =  "%".$filtroNome."%";
                        $textoQuery .= " AND U.nome LIKE :filtronomeadmin ";
                    }
                }
                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY U.dataInscricao DESC" ;
                $indexHeader = isset($_GET["numeroTableHeader"] ) 
                                ? htmlspecialchars( $_GET["numeroTableHeader"] ) 
                                : -1 ;
                $direcao = 1;
                //------------------

                
                if( isset($_GET["numeroTableHeader"]) ){
                    $indexHeader = htmlspecialchars( $_GET["numeroTableHeader"] );
                    if( !is_nan($indexHeader) ){
                        
                        switch ($indexHeader) {
                            case '0':
                                $orderBy = " ORDER BY U.nome " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY U.login " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY U.email " ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY U.dataInscricao " ;
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
                $query->setFetchMode(PDO::FETCH_ASSOC);

                if(isset($_GET["filtro-nome"])){
                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $query->bindParam(":filtronomeadmin",$filtroNome);
                    }
                }

                $query->execute();

                $numeroRegistros = $query->rowCount();

                $possuiProximaPagina = false;
                $contador = 0;

                $tabela = "";

                while ($linha = $query->fetch()){
                    if($contador != $itemsPorPagina && $linha['idAdmin'] != 1){

                        // listamos os dados de cada usuário
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"nome\">";
                        $tabela .= htmlspecialchars($linha["nome"])             ."</td>";
                        $tabela .= "    <td class=\"login\">";
                        $tabela .= htmlspecialchars($linha["login"])            ."</td>";
                        $tabela .= "    <td class=\"email\">";
                        $tabela .= htmlspecialchars($linha["email"])            ."</td>";
                        $tabela .= "    <td class=\"datainsc\">";
                        $tabela .= date("d/m/Y H:i:s",
                                       strtotime(htmlspecialchars($linha["dataInscricao"])))    ."</td>";
                        $tabela .= "    <td><a data-id=\"" . $linha["id"];
                        $tabela .= "\" data-id-admin=\"";
                        $tabela .= $linha['idAdmin']."\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-edita-admin\"";
                        $tabela .= " data-permissoes=\"".htmlspecialchars($linha["permissoes"])."\">";
                        $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                        $tabela .= "    <td><a data-href=\"rotinas/administrador/remover_administrador.php?id=";
                        $tabela .= $linha["id"];
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-confirma-deleta\">";
                        $tabela .= "<i class=\"fa fa-trash-o\"></i></a></td>";
                        $tabela .= "</tr>";
                    }
                    else if($linha['idAdmin'] != 1){
                        $possuiProximaPagina = true;
                    }
                    $contador++;
                }

                // agora contamos quantos administradores essa pesquisa conseguiria, sem o LIMIT
                $textoQueryCount = explode("LIMIT", $textoQuery);
                // lembrando de desconsiderar o administrador principal
                $query = $conexao->prepare($textoQueryCount[0] . " AND idAdmin <> 1");

                if(isset($_GET["filtro-nome"])){
                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $query->bindParam(":filtronomeadmin",$filtroNome);
                    }
                }

                $query->execute();
                $numAdmins = $query->rowCount();
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>Administradores</h1>
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-administrador">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Novo administrador</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_administradores.php" id="form-filtro">
                        <div class="form-group">
                            <br/>
                            <p>
                                <b>Buscar por:</b>
                            </p>
                            <a id="label-nome" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-nome"]) && 
                                        mb_strlen(($_GET["filtro-nome"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >
                                Nome
                            </a>
                            <input  type="text" name="filtro-nome" id="filtro-nome"
                                    placeholder="Nome" class="form-control" autocomplete="off"
                                    style="display:inline;width:205px"
                                    value= <?= isset($_GET["filtro-nome"]) ? 
                                        htmlspecialchars($_GET["filtro-nome"]) : "" ?> >

                            
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
                    <div class="flip-scroll" >
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="administradores">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="200px" <?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Nome</th>
                                        <th width="160px" <?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Nome de usuário</th>
                                        <th width="200px" <?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>E-mail</th>
                                        <th width="180px" <?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data e hora de inscrição</th>
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
                    <br>
                    <b><?= $numAdmins ?> administrador<?= $numAdmins != '1' ? 'es' : ''?> 
                       encontrado<?= $numAdmins != '1' ? 's' : ''?> para os critérios passados</b>
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
                    <?php } ?>
                    
                </section>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para inserção de novo administrador -->
        <div class="modal fade" id="modal-novo-administrador" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-administrador" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_administradores.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Novo administrador</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="nome-novo">Nome do administrador:</label>
                                <input type="text" name="nome" id="nome-novo" required
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email-novo">E-mail do administrador:</label>
                                <input type="email" name="email" id="email-novo" required
                                       placeholder="E-mail"
                                       title="Insira um e-mail válido"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="login-novo">Nome de usuário:</label>
                                <input type="text" name="login" id="login-novo" required
                                       pattern="^.{3,100}$" placeholder="Nome de usuário"
                                       title="O login deve ter de 3 a 100 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="senha-novo">Senha:</label>
                                <input type="password" name="senha" id="senha-novo" required
                                       pattern="^.{6,72}$" placeholder="Senha"
                                       title="A senha deve ter de 6 a 72 caracteres"
                                       class="form-control">
                            </div>
                            <div style="display:block col-sm-12">
                                <p> Permissões de administração:</p>
                                <div  class="col-sm-2" 
                                    style="padding-top:10px;padding-bot:10px">
                                    <label for="pessoas">Usuários :</label>
                                    <input type="checkbox" name="pessoas" id="pessoas"
                                        title="Permite acesso a edição de usuários"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                                <div  class="col-sm-2" 
                                    style="padding-top:10px;padding-bot:10px">
                                    <label for="curso">Curso :</label>
                                    <input type="checkbox" name="curso" id="curso"
                                        title="Permite acesso a edição de informações do curso"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                                <div  class="col-sm-2" 
                                    style="padding-top:10px;padding-bot:10px">
                                    <label for="site">Info :</label>
                                    <input type="checkbox" name="site" id="site"
                                        title="Permite acesso a edição de informações do site"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                                <div  class="col-sm-3" 
                                    style="padding-top:10px;padding-bot:10px">
                                    <label for="financeiro">Financeiro :</label>
                                    <input type="checkbox" name="financeiro" id="financeiro"
                                        title="Permite acesso a edição de informações financeiras"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                                <div  class="col-sm-2" 
                                    style="padding-top:10px;padding-bot:10px;display:none">
                                    <label for="miscelanea">Outros :</label>
                                    <input type="hidden" name="miscelanea" id="miscelanea"
                                        title="Permite acesso a edição de informações adicionais"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                            </div>
                            <br><br>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir administrador
                            </button>
                        </div>
                        <input type="hidden" value="0" id="permissoes" name="permissoes">
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de administrador -->
        <div class="modal fade" id="modal-edita-admin" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-admin" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/administrador/editar_administrador.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar administrador</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="idAdmin" id="idAdmin" value="">
                            <input type="hidden" name="id" id="id" value="">
                            <div class="form-group">
                                <label for="nome">Nome do administrador:</label>
                                <input type="text" name="nome" id="nome" required
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail do administrador:</label>
                                <input type="email" name="email" id="email" required
                                       placeholder="E-mail"
                                       title="Insira um e-mail válido"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="senha">Nome de usuário:</label>
                                <input type="text" name="login" id="login" required
                                       pattern="^.{3,72}$" placeholder="Nome de usuário"
                                       title="O login deve ter de 3 a 100 caracteres"
                                       class="form-control">
                            </div>

                            <div class="btn btn-primary" id="btn-muda-senha">Mudar senha</div>
                            <div class="form-group" style="display:none">
                                <label for="login">Nova senha:</label>
                                <input type="password" name="senha" id="senha" pattern="^.{6,100}$|^$"
                                       placeholder="Senha (deixe em branco caso não deseje mudar)"
                                       title="A senha deve ter de 6 a 72 caracteres"
                                       class="form-control">
                            </div>
                            <br><br>

                            <div style="display:block col-sm-12">
                                <p> Permissões de administração:</p>
                                <div class="warning">
                                    As permissões só serão válidas a partir do próximo login
                                     do administrador
                                </div>
                                <div  class="col-sm-2" 
                                    style="padding-top:10px;padding-bot:10px">
                                    <label for="pessoas">Usuários :</label>
                                    <input type="checkbox" name="pessoas" id="pessoas"
                                        title="Permite acesso a edição de usuários"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                                <div  class="col-sm-2" 
                                    style="padding-top:10px;padding-bot:10px">
                                    <label for="curso">Curso :</label>
                                    <input type="checkbox" name="curso" id="curso"
                                        title="Permite acesso a edição de informações do curso"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                                <div  class="col-sm-2" 
                                    style="padding-top:10px;padding-bot:10px">
                                    <label for="site">Info :</label>
                                    <input type="checkbox" name="site" id="site"
                                        title="Permite acesso a edição de informações do site"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                                <div  class="col-sm-3" 
                                    style="padding-top:10px;padding-bot:10px">
                                    <label for="financeiro">Financeiro :</label>
                                    <input type="checkbox" name="financeiro" id="financeiro"
                                        title="Permite acesso a edição de informações financeiras"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                                <div  class="col-sm-2" 
                                    style="padding-top:10px;padding-bot:10px;display:none">
                                    <label for="miscelanea">Outros :</label>
                                    <input type="hidden" name="miscelanea" id="miscelanea"
                                        title="Permite acesso a edição de informações adicionais"
                                        class="form-control"
                                        style="display:inline">
                                </div>
                            </div>
                            <br><br>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar administrador
                            </button>
                        </div>
                        <input type="hidden" value="0" id="permissoes" name="permissoes">
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de administrador -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de administrador</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover <span id="nome-administrador"></span>?</h3>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                        <a href="#" class="btn btn-danger danger">Sim</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para filtro de resultados -->
        <div class="modal fade" id="modal-filtra-resultados" tabindex="-1" role="dialog"
             aria-labelledby="modal-filtra-resultados" aria-hidden="true">
            <div class="modal-dialog">
                <form method="GET" action="gerenciar_administradores.php ">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Buscar alunos</h4>
                        <p class="warning">Preencha apenas os campos que quiser restringir</p>
                        </div>
                        <div class="modal-body">
                            <?php 
                            // se o usuario filtrou algo recentemente, mantem o filtro
                            if(isset($_GET["submit"])) { ?>
                                
                                <label for="filtro-nome">Nome:</label>
                                <input type="text" name="filtro-nome" 
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control" autocomplete="off"
                                       value= <?= $_GET["filtro-nome"] ?> >

                            <?php

                                } else { // caso contrario, mostra um novo formulario
                            ?>

                            <label for="filtro-nome">Nome:</label>
                            <input type="text" name="filtro-nome" 
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control" autocomplete="off">
                            <?php } ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Filtrar Resultados
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
            } else {
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
