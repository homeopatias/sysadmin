<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Professores - Homeopatias.com</title>
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <script>
            var podeMudarPagina = true;
            $(document).ready(function(){

                // permite redimensionar as colunas da tabela
                $("#professores").colResizable({
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

                $("#professores").tablesorter({ headers: {
                    2 : { sorter: false },
                    4 : { sorter: "datetime" },
                    5 : { sorter: false },
                    6 : { sorter: false },
                    7 : { sorter: false }
                }});
                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-professor').text(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                });
                // passa os dados do professor para o modal para a edição
                $("#modal-edita-professor").on('show.bs.modal', function(e) {
                    $(this).find('#id').val(
                        $(e.relatedTarget).data('id')
                    );
                    $(this).find('#idAdmin').val(
                        $(e.relatedTarget).data('id-admin')
                    );
                    $(this).find('#nome').val(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                    $(this).find('#cpf').val(
                        $(e.relatedTarget).parent().siblings('.cpf').text()
                    );
                    $(this).find('#email').val(
                        $(e.relatedTarget).parent().siblings('.email').text()
                    );
                    $(this).find('#login').val(
                        $(e.relatedTarget).parent().siblings('.login').text()
                    );
                    $(this).find('#corrigeTrabalho').prop("checked",
                        $(e.relatedTarget).data('corrige') ? true : false
                    );
                });

                // esconde inputs de busca

                $("#filtro-nome").hide();
                $("#filtro-cpf").hide();
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

                $("#label-cpf").click(function(){
                    $(this).hide();
                    $("#filtro-cpf").show(300);
                    $("#filtro-cpf").focus();
                });

                $("#filtro-cpf").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-cpf").show(300);   
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
                $("#filtro-nome").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-cpf").keypress(function(e){
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
                    $("#filtro-cpf").val("");
                    atualizaPagina();
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

            // mensagem a ser exibida acima da listagem de professores, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe professores apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

                // se o usuário chegou até aqui através de um formulário, registra o novo
                // professor no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $nome            = $_POST["nome"];
                    $cpf             = $_POST["cpf"];
                    $email           = $_POST["email"];
                    $login           = $_POST["login"];
                    $senha           = $_POST["senha"];
                    $corrigeTrabalho = $_POST["corrigeTrabalho"] === "on";

                    $nomeValido   = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                                    mb_strlen($nome, 'UTF-8') <= 100;
                    $cpfValido    = isset($cpf) &&
                                    (preg_match("/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/", $cpf) || 
                                     preg_match("/^\d{11}$/", $cpf));
                    
                    if($cpfValido){
                        // checamos se os dígitos verificadores do cpf conferem
                        $cpfChecar = str_replace(".","",$cpf);
                        $cpfChecar = str_replace("-","",$cpfChecar);
                        $cpfChecar = str_split($cpfChecar);
                        $somaChecagem = 0;
                        for($i = 10; $i >= 2; $i = $i - 1){
                            $somaChecagem += (int)($cpfChecar[10 - $i]) * $i;
                        }
                        $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
                        if($digito != $cpfChecar[9]){
                            $cpfValido = false;
                        }else{
                            // agora checamos o segundo dígito
                            $somaChecagem = 0;
                            for($i = 11; $i >= 2; $i = $i - 1){
                                $somaChecagem += (int)($cpfChecar[11 - $i]) * $i;
                            }
                            $digito = floor($somaChecagem/11);
                            $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
                            if($digito != $cpfChecar[10]){
                                $cpfValido = false;
                            }
                        }
                    }

                    $emailValido  = isset($email) && mb_strlen($email, 'UTF-8') <= 100 &&
                                    preg_match("/^.+\@.+\..+$/", $email);
                    $loginValido  = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                                    mb_strlen($login, 'UTF-8') <= 100;
                    $senhaValida  = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 &&
                                    mb_strlen($senha, 'UTF-8') <= 72;

                    // se todos os dados estão válidos, o professor é cadastrado
                    if($nomeValido && $cpfValido && $emailValido && $loginValido &&
                       $senhaValida){

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
                        $novo->setCpf($cpf);
                        $novo->setEmail($email);
                        $novo->setNivelAdmin("professor");
                        $novo->setCorrigeTrabalho($corrigeTrabalho);

                        $sucesso = $novo->cadastrar($host, "homeopatias", $usuario, $senhaBD, $senha);

                        if(!$sucesso){
                            $mensagem = "Já existe um usuário com esse nome 
                                         de usuário no sistema";
                        }
                    }else if(!$nomeValido){
                        $mensagem = "Nome inválido!";
                    }else if(!$cpfValido){
                        $mensagem = "CPF inválido!";
                    }else if(!$emailValido){
                        $mensagem = "E-mail inválido!";
                    }else if(!$loginValido){
                        $mensagem = "Nome de usuário inválido!";
                    }else if(!$senhaValida){
                        $mensagem = "Senha inválida!";
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

                $textoQuery  = "SELECT U.id, U.cpf, U.dataInscricao, U.email, 
                                U.nome, U.login, A.idAdmin, A.corrigeTrabalho
                                FROM Usuario U, Administrador A WHERE A.idUsuario = U.id AND 
                                A.nivel = \"professor\" ";

                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroCpf = $filtroNome = false;
                if(isset($_GET["filtro-nome"]) || isset($_GET["filtro-cpf"])){

                    $filtroCpf     =  htmlspecialchars($_GET["filtro-cpf"]);
                    $filtroNome    =  htmlspecialchars($_GET["filtro-nome"]);

                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $filtroNome    =  "%".mb_strtoupper($filtroNome)."%";
                        $textoQuery .= " AND UPPER(U.nome) LIKE :filtronome ";
                    }            
                    if(isset($filtroCpf) && mb_strlen($filtroCpf) > 0){

                        // Remove os '.' e '-' para comparar com o cpf do bd
                        $filtroCpf = str_replace(".","",$filtroCpf);
                        $filtroCpf = str_replace("-","",$filtroCpf);

                        $textoQuery .= " AND U.cpf LIKE :filtrocpf ";
                    }
                }

                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY U.dataInscricao DESC" ;
                $indexHeader = -1;
                $direcao = 2;
                //------------------

                if( isset($_GET["numeroTableHeader"]) && isset($_GET["cimaOuBaixo"]) ){
                    $indexHeader = htmlspecialchars( $_GET["numeroTableHeader"] );
                    if( !is_nan($indexHeader) ){
                        
                        switch ($indexHeader) {
                            case '0':
                                $orderBy = " ORDER BY U.nome " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY U.login " ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY U.email " ;
                                break;
                            case '4':
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

                $textoQuery .= $orderBy." LIMIT ".($itemsPorPagina+1).
                                " OFFSET ".(($pagina)*$itemsPorPagina);

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);

                if(isset($_GET["filtro-nome"]) || isset($_GET["filtro-cpf"])){
                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $query->bindParam(":filtronome",$filtroNome);
                    }
                    if(isset($filtroCpf) && mb_strlen($filtroCpf) > 0){
                        $query->bindParam(":filtrocpf",$filtroCpf);
                    }
                }

                $query->execute();

                $numeroRegistros = $query->rowCount();

                $possuiProximaPagina = false;
                $contador = 0;
                $tabela = "";

                while ($linha = $query->fetch()){
                    if($contador != $itemsPorPagina){

                        // formatando o texto do cpf
                        $cpfOriginal = str_split($linha["cpf"]);
    
                        $cpf  = implode("", array_slice($cpfOriginal, 0, 3)) . ".";
                        $cpf .= implode("", array_slice($cpfOriginal, 3, 3)) . ".";
                        $cpf .= implode("", array_slice($cpfOriginal, 6, 3)) . "-";
                        $cpf .= implode("", array_slice($cpfOriginal, 9, 2));
                        $cpf  = htmlspecialchars($cpf);
    
                        // listamos os dados de cada usuário
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"nome\">";
                        $tabela .= htmlspecialchars($linha["nome"])             ."</td>";
                        $tabela .= "    <td class=\"login\">";
                        $tabela .= htmlspecialchars($linha["login"])            ."</td>";
                        $tabela .= "    <td class=\"cpf\">";
                        $tabela .= $cpf                                     ."</td>";
                        $tabela .= "    <td class=\"email\">";
                        $tabela .= htmlspecialchars($linha["email"])            ."</td>";
                        $tabela .= "    <td class=\"datainsc\">";
                        $tabela .= date("d/m/Y H:i:s", 
                                        strtotime(htmlspecialchars($linha["dataInscricao"]))) ."</td>";
                        $tabela .= "    <td class=\"corrigeTrabalho\">";
                        $tabela .= $linha["corrigeTrabalho"] ?
                                        "<i class=\"fa fa-check sucesso\"></i>" :
                                        "<i class=\"fa fa-times warning\"></i>";
                        $tabela .= "    </td>";
                        $tabela .= "    <td><a data-id=\"";
                        $tabela .= $linha["id"];
                        $tabela .= "\" data-id-admin=\"";
                        $tabela .= $linha["idAdmin"]."\" data-corrige=\"";
                        $tabela .= $linha["corrigeTrabalho"];
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-edita-professor\">";
                        $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                        $tabela .= "    <td><a data-href=\"rotinas/professor/remover_professor.php?id=";
                        $tabela .= $linha["id"];
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
                    <h1>Professores</h1>
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-professor">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Novo professor</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_professores.php" id="form-filtro">
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
                                    
                            <a id="label-cpf" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-cpf"]) && 
                                        mb_strlen(($_GET["filtro-cpf"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >CPF
                            </a>

                            <input type="text" name="filtro-cpf" id="filtro-cpf"
                                       pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                       placeholder="xxx.xxx.xxx-xx" class="form-control"
                                       style="display:inline;width:120px"
                                       value= <?= isset($_GET["filtro-cpf"]) ? 
                                        htmlspecialchars($_GET["filtro-cpf"]) : "" ?> >

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
                            <table class="table table-bordered table-striped" id="professores">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="180px"<?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Nome</th>
                                        <th width="130px"<?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Nome de usuário</th>
                                        <th width="100px">CPF</th>
                                        <th width="160px"<?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>E-mail</th>
                                        <th width="170px"<?= $indexHeader == 4 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data e hora de inscrição</th>
                                        <th width="70px">Avaliador?</th>
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
        <!-- popup "modal" do bootstrap para inserção de novo professor -->
        <div class="modal fade" id="modal-novo-professor" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-professor" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_professores.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Novo professor</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="nome-novo">Nome do professor:</label>
                                <input type="text" name="nome" id="nome-novo" required
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cpf-novo">CPF do professor:</label>
                                <input type="text" name="cpf" id="cpf-novo" required
                                       pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                       placeholder="xxx.xxx.xxx-xx" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email-novo">E-mail do professor:</label>
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
                            <div class="form-group">
                                <br>
                                <input type="checkbox" name="corrigeTrabalho" id="corrigeTrabalho-novo">
                                <label for="corrigeTrabalho-novo">Faz correção de trabalhos</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir professor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de professor -->
        <div class="modal fade" id="modal-edita-professor" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-professor" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/professor/editar_professor.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar professor</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="idAdmin" id="idAdmin" value="">
                            <input type="hidden" name="id" id="id" value="">
                            <div class="form-group">
                                <label for="nome">Nome do professor:</label>
                                <input type="text" name="nome" id="nome" required
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cpf">CPF do professor:</label>
                                <input type="text" name="cpf" id="cpf" required
                                       pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                       placeholder="xxx.xxx.xxx-xx" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail do professor:</label>
                                <input type="email" name="email" id="email" required
                                       placeholder="E-mail"
                                       title="Insira um e-mail válido"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="login">Nome de usuário:</label>
                                <input type="text" name="login" id="login" required
                                       pattern="^.{3,100}$" placeholder="Nome de usuário"
                                       title="O login deve ter de 3 a 100 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <br>
                                <input type="checkbox" name="corrigeTrabalho" id="corrigeTrabalho">
                                <label for="corrigeTrabalho">Faz correção de trabalhos</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar professor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de professor -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de professor</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover <span id="nome-professor"></span>?</h3>
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