<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Aulas - Homeopatias.com</title>
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

            // aqui recebemos os dados das cidades existentes para cada ano
            // assim podemos atualizar a lista de cidades dinamicamente durante a inserção
            
            var cidades      = new Array();
            var professores  = new Array();
            var nomesCidades = new Array();
            var anos         = new Array();
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

                $textoQuery  = "SELECT idCidade, UF, nome, ano 
                                FROM Cidade ORDER BY ano DESC, nome ASC";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                // variável para garantir que inicializaremos o vetor para cada
                // ano sempre que estivermos utilizando-o pela primeira vez
                $anos = [];
                $nomesCidades = [];

                while ($linha = $query->fetch()){
                    $id   = "\"".htmlspecialchars($linha["idCidade"])."\"";
                    $uf   = "\"".htmlspecialchars($linha["UF"])."\"";
                    $nome = "\"".htmlspecialchars($linha["nome"])."\"";
                    $ano  = "\"".htmlspecialchars($linha["ano"])."\"";
                    if(!in_array($linha["ano"], $anos)){
                        $anos[] = $linha["ano"];
            ?>
            
            cidades[ <?= $ano ?> ] = new Array();
            anos.push( <?= $ano ?> );
            <?php } ?>

            cidades[ <?= $ano ?> ].push({
                id:   <?= $id ?>,
                uf:   <?= $uf ?>,
                nome: <?= $nome ?>,
                ano:  <?= $ano ?>
            });

            // preenche o vetor de nomes de cidades
            <?php 
                if(!in_array($linha["nome"], $nomesCidades)){
                    $nomesCidades[] = $linha["nome"];
            ?>
                nomesCidades.push({
                id:   <?= $id ?>,
                nome: <?= $nome . " + \"/\" + " . $uf ?>
            });
            <?php
                }
            ?>
            <?php
                }
                // Realizaremos uma busca para encontrar os professores no sistema
                $textoQuery  = "SELECT A.idAdmin,U.nome
                                FROM Usuario U, Administrador A WHERE A.idUsuario = U.id AND 
                                A.nivel = \"professor\" ";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                while ($linha = $query->fetch()){
                    $id   = "\"".htmlspecialchars($linha["idAdmin"])."\"";
                    $nome = "\"".htmlspecialchars($linha["nome"])."\"";
            ?>

            professores.push({
                id:   <?= $id ?>,
                nome: <?= $nome ?>
            });

            <?php 
                }
            ?>

            var podeMudarPagina = true;
            $(document).ready(function(){

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
                    6 : { sorter: false },
                    7 : { sorter: false }
                }});

                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                });
                // passa os dados da aula para o modal para a edição
                $("#modal-edita-aula").on('show.bs.modal', function(e) {
                    $(this).find('#idAula').val(
                        $(e.relatedTarget).data('id')
                    );

                    // formatamos a data para inserção no formulário de edição
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

                    $(this).find('#data-edita-aula').val(data.getFullYear() + "-" + mes + "-" + dia);
                    $(this).find('#horario').val(hora + ":" + minuto);

                    document.getElementById("data-edita-aula").oninput();
                    $(this).find('#cidade-edita-aula').val(
                        $(e.relatedTarget).parent().siblings('.cidade').data('id-cidade')
                    );
                    $(this).find('#etapa').val(
                        $(e.relatedTarget).parent().siblings('.etapa').text()
                    );

                    if(cidades[ano]){
                        $(this).find('#prof').val(
                            $(e.relatedTarget).parent().siblings('.professor').data('id-professor')
                        );
                    }else{
                        $("#cidade-edita-aula")
                            .append('<option selected>Não existe cidade cadastrada no ano dado</option>');
                    }

                    $(this).find("#descricao").val(
                        $(e.relatedTarget).data("descricao")
                    );
                });

                // quando a data é mudada no formulário de nova aula, listamos
                // as cidades referentes ao ano correto
                document.getElementById("data-nova-aula").oninput = function(){
                    // descobrimos qual cidade está selecionada para que não
                    // seja mudada a cidade sem necessidade
                    var cidadeSelecionada = $("#cidade-nova-aula").val();

                    $("#cidade-nova-aula").find('option').remove().end();
                    var data = new Date($("#data-nova-aula").val());
                    // a data recebida estará um dia atrasada, adicionamos um dia
                    data = new Date(data.getTime() + (24 * 60 * 60 * 1000));

                    var ano  = data.getFullYear();
                    if(cidades[ano]){
                        cidades[ano].forEach(function(cidade){
                            var opcao = '<option value="' + cidade.id + '">'
                                       + cidade.nome + "/" + cidade.uf + '</option>';
                            $("#cidade-nova-aula").append(opcao);
                            if(cidade.id == cidadeSelecionada){
                                $("#cidade-nova-aula option:last-child").prop("selected", true);
                            }
                        })
                    }
                };
                // o mesmo ocorre no formulário de edição de data
                document.getElementById("data-edita-aula").oninput = function(){
                    // descobrimos qual cidade está selecionada para que não
                    // seja mudada a cidade sem necessidade
                    var cidadeSelecionada = $("#cidade-edita-aula").val();

                    $("#cidade-edita-aula").find('option').remove().end();
                    var data = new Date($("#data-edita-aula").val());
                    // a data recebida estará um dia atrasada, adicionamos um dia
                    data = new Date(data.getTime() + (24 * 60 * 60 * 1000));

                    var ano = data.getFullYear();
                    if(cidades[ano]){
                        cidades[ano].forEach(function(cidade){
                            var opcao = '<option value="' + cidade.id + '">'
                                       + cidade.nome + "/" + cidade.uf + '</option>';
                            $("#cidade-edita-aula").append(opcao);
                            if(cidade.id == cidadeSelecionada){
                                $("#cidade-edita-aula option:last-child").prop("selected", true);
                            }
                        })
                    }
                };

                // esconde inputs de busca
                $("#filtro-professor").hide();
                $("#filtro-etapa").hide();
                $("#filtro-cidade").hide();
                $("#filtro-ano").hide();
                $("#div-data-min").hide();
                $("#div-data-max").hide();
                $("#ipp").hide();  

                $("#label-professor").click(function(){
                    $(this).hide();
                    $("#filtro-professor").show(300);
                    $("#filtro-professor").focus();
                });

                $("#filtro-professor").blur(function(){
                    if($(this).val() == "0"){
                        $(this).hide(300);
                        $("#label-professor").show(300);   
                    }
                });
                
                $("#label-cidade").click(function(){
                    $(this).hide();
                    $("#filtro-cidade").show(300);
                    $("#filtro-cidade").focus();
                });

                $("#filtro-cidade").blur(function(){
                    if($(this).val() == null || $(this).val() == "0"){
                        $(this).hide(300);
                        $("#label-cidade").show(300);   
                    }
                });

                $("#label-ano").click(function(){
                    $(this).hide();
                    $("#filtro-ano").show(300);
                    $("#filtro-ano").focus();
                });

                $("#filtro-ano").blur(function(){
                    if($(this).val() == null || $(this).val() == "0"){
                        $(this).hide(300);
                        $("#label-ano").show(300);   
                    }
                });

                $("#label-etapa").click(function(){
                    $(this).hide();
                    $("#filtro-etapa").show(300);
                    $("#filtro-etapa").focus();
                });

                $("#filtro-etapa").blur(function(){
                    if($(this).val() == ""){
                        $(this).hide(300);
                        $("#label-etapa").show(300);   
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

                $("#label-etapa").click(function(){
                    $(this).hide();
                    $("#filtro-etapa").show(300);
                    $("#filtro-etapa").focus();
                });

                $("#filtro-etapa").blur(function(){
                    if($(this).val() == "0"){
                        $(this).hide(300);
                        $("#label-etapa").show(300);   
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
                $("#filtro-cidade").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-cidade").change(function(){
                    if($("#filtro-cidade").val != "0" && $("#filtro-ano").val() == "0"){
                        $("#label-ano").click();
                    }
                    else{
                        $(this).blur();
                    }
                });

                $("#filtro-ano").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-professor").keypress(function(e){
                    var keycode = (e.keyCode ? e.keyCode : e.which);
                    if(keycode == '13'){ // enter foi pressionado
                       atualizaPagina();
                    }
                });

                $("#filtro-etapa").keypress(function(e){
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
                    $("#filtro-professor").val("");
                    $("#filtro-cidade").val("");
                    $("#filtro-ano").val("");
                    $("#filtro-etapa").val("");
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

                $("#form-filtro input").change(function(){
                    podeMudarPagina = false;
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

                // Preenche o select de professores

                // se há professor filtrado, seleciona ele
                // remove os sinais de + que são passados e transforma em uma entidade html
                var selecionado = <?= isset($_GET["filtro-professor"])?
                             htmlspecialchars( str_replace("+","",$_GET["filtro-professor"]) ) : "0"?>;


                // A primeira opção indica nenhum professor
                var opcao = '<option value= 0>Todos</option>';
                            $("#filtro-professor").append(opcao);
                professores.forEach(function(prof){
                    if(selecionado != "0" && selecionado == prof.id){
                        var opcao = '<option value=" '+ prof.id +' " selected = selected>'
                            + prof.nome + '</option>';
                        $("#filtro-professor").append(opcao);

                    }
                    else{
                        var opcao = '<option value=" '+ prof.id +' ">'
                            + prof.nome + '</option>';
                        $("#filtro-professor").append(opcao);
                    }
                });

                // preenche o select de cidades

                // se há cidade filtrada, seleciona ela
                // remove os sinais de + que são passados e transforma em uma entidade html
                selecionado = <?= isset($_GET["filtro-cidade"]) ?
                             "\"" . str_replace("+","",$_GET["filtro-cidade"]) . "\"" 
                             : "0"?>;


                // A primeira opção indica nenhuma cidade
                var opcao = '<option value= 0>Todas</option>';
                            $("#filtro-cidade").append(opcao);
                nomesCidades.forEach(function(cidade){
                    if(selecionado != "0" && selecionado == cidade.nome){
                        var opcao = '<option value=" '+ cidade.nome +' " selected = selected>'
                            + cidade.nome + '</option>';
                        $("#filtro-cidade").append(opcao);

                    }
                    else{
                        var opcao = '<option value=" '+ cidade.nome +' ">'
                            + cidade.nome + '</option>';
                        $("#filtro-cidade").append(opcao);
                    }
                });

                // se há ano filtrado, seleciona ele
                // remove os sinais de + que são passados e transforma em uma entidade html
                selecionado = <?= isset($_GET["filtro-ano"]) ?
                             htmlspecialchars(str_replace("+","",$_GET["filtro-ano"]) ) : "0"?>;


                // A primeira opção indica nenhum ano
                var opcao = '<option value= 0>Todos</option>';
                            $("#filtro-ano").append(opcao);
                anos.forEach(function(ano){
                    if(selecionado != "0" && selecionado == ano){
                        var opcao = '<option value=" '+ ano +' " selected = selected>'
                            + ano + '</option>';
                        $("#filtro-ano").append(opcao);

                    }
                    else{
                        var opcao = '<option value=" '+ ano +' ">'
                            + ano + '</option>';
                        $("#filtro-ano").append(opcao);
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

            function atualizaPagina(){
                $("#pagina").val("0");
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

            // mensagem a ser exibida acima da listagem de aulas, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe aulas apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){
                // se o usuário chegou até aqui através de um formulário, registra a nova
                // aula no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $data        = $_POST["data-nova-aula"];
                    $horario     = $_POST["horario"];
                    $idCidade    = $_POST["cidade-nova-aula"];
                    $etapa       = $_POST["etapa"];
                    $idProfessor = $_POST["prof"];
                    $descricao   = $_POST["descricao"];

                    $dataValida        = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
                    $horarioValido     = isset($horario) && preg_match("/^\d{2}:\d{2}$/", $horario);
                    $idCidadeValido    = isset($idCidade) && preg_match("/^[0-9]+$/", $idCidade);
                    $etapaValida       = isset($etapa) && preg_match("/^[1-4]$/", $etapa);
                    $idProfessorValido = isset($idProfessor) && 
                                         (preg_match("/^[0-9]+$/", $idProfessor) || $idProfessor == -1);
                    $descricaoValida = isset($descricao) && mb_strlen($descricao, 'UTF-8') <= 10000;

                    // checamos se a cidade recebida pertence ao ano recebido e se existe
                    if($dataValida && $idCidadeValido){
                        require_once("entidades/Cidade.php");

                        $ano = date("Y", strtotime($data));
                        $cidade = new Cidade();
                        $cidade->setIdCidade($idCidade);
                        $encontrada = $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);
                        if(!$encontrada){
                            $idCidadeValido = false;
                            $mensagem = "Essa cidade não foi encontrada no sistema";
                        }else if($cidade->getAno() != $ano){
                            $idCidadeValido = false;
                            $mensagem = "Essa cidade não pertence a esse ano";
                        }
                    }

                    // agora checamos se o professor recebido existe
                    if($idProfessorValido && $idProfessor != -1){
                        require_once("entidades/Administrador.php");

                        $admin = new Administrador("");
                        $admin->setIdAdmin($idProfessor);
                        $encontrado = $admin->recebeAdminId($host, "homeopatias", $usuario,
                                                            $senhaBD, "professor");
                        if(!$encontrado){
                            $idProfessorValido = false;
                            $mensagem = "Esse professor não foi encontrado no sistema";
                        }
                    }

                    // se todos os dados estão válidos, a aula é inserida
                    if($dataValida && $horarioValido && $idCidadeValido && $etapaValida &&
                       $idProfessorValido && $descricaoValida){

                        require_once("entidades/Aula.php");

                        $nova = new Aula();
                        $nova->setCidadeId($idCidade);
                        $nova->setEtapa($etapa);
                        $nova->setData(strtotime($data . " " . $horario . ":00"));
                        $nova->setProfessorId($idProfessor);
                        $nova->setNota(null);
                        $nova->setDescricao($descricao);

                        $sucesso = $nova->cadastrar($host, "homeopatias", $usuario, $senhaBD);

                        if($sucesso){
                            $mensagem = "";
                        }else{
                            $mensagem = "Erro na inserção de aula";
                        }
                    } else if(!$dataValida) {
                        $mensagem = "Data inválida!";
                    } else if(!$horarioValido) {
                        $mensagem = "Horário inválido!";
                    } else if(!$etapaValida) {
                        $mensagem = "Etapa inválida!";
                    } else if(!$descricaoValida){
                        $mensagem = "Descrição inválida!";
                    }
                }

                $textoQuery  = "SELECT A.idAula, A.chaveCidade, A.etapa, A.data, 
                                A.idProfessor, A.nota, A.descricao FROM Aula A, 
                                Cidade C WHERE C.idCidade = A.chaveCidade";
                
                // Se algum filtro foi repassado, altera o query para filtrar
                $filtroProfessor = $filtroEtapa = $filtroDataMin = $filtroDataMax = false;
                $filtroCidade    = $filtroAno   = false;
                if(isset($_GET["filtro-professor"]) || isset($_GET["filtro-etapa"]) ||
                   isset($_GET["filtro-data-min"])  || isset($_GET["filtro-data-max"]) ||
                   isset($_GET["filtro-cidade"])    || isset($_GET["filtro-ano"])){

                    $filtroCidade     =  $_GET["filtro-cidade"];
                    $filtroEtapa      =  htmlspecialchars($_GET["filtro-etapa"]);
                    $filtroProfessor  =  htmlspecialchars($_GET["filtro-professor"]);
                    $filtroDataMin    =  htmlspecialchars($_GET["filtro-data-min"]);
                    $filtroDataMax    =  htmlspecialchars($_GET["filtro-data-max"]);
                    $filtroAno        =  htmlspecialchars($_GET["filtro-ano"]);

                    if(isset($filtroProfessor) && mb_strlen($filtroProfessor) > 0 && 
                        !is_nan($filtroProfessor) && $filtroProfessor != "0"){
                        $textoQuery .= " AND A.idProfessor = :filtroprofessor ";
                        
                    }  
                    if(isset($filtroCidade) && mb_strlen($filtroCidade) > 0 && 
                        $filtroCidade != "0"){
                        $textoQuery .= " AND C.nome = :filtrocidade ";
                        $textoQuery .= " AND C.UF   = :filtrouf";
                        
                    }
                    if(isset($filtroAno) && mb_strlen($filtroAno) > 0 && 
                        !is_nan($filtroAno) && $filtroAno != "0"){
                        $textoQuery .= " AND YEAR(A.data) = :filtroano ";
                        
                    }    
                    if(isset($filtroEtapa) && mb_strlen($filtroEtapa) > 0 && 
                        !is_nan($filtroEtapa) && $filtroEtapa != "0"){
                        $textoQuery .= " AND A.etapa LIKE :filtroetapa ";
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        $textoQuery .= " AND CAST(A.data AS Date) >= ";
                        $textoQuery .= "CAST(:dataMin as Date)";

                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        $textoQuery .= " AND CAST(A.data AS Date) <= ";
                        $textoQuery .= "CAST(:dataMax as Date)";
                    }
                }

                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY A.data DESC" ;
                $indexHeader = -1;
                $direcao = 2;
                //------------------

                if( isset($_GET["numeroTableHeader"]) && isset($_GET["cimaOuBaixo"]) ){
                    $indexHeader = htmlspecialchars( $_GET["numeroTableHeader"] );
                    if( !is_nan($indexHeader) ){
                        
                        switch ($indexHeader) {
                            case '0':
                                $orderBy = " ORDER BY A.idAula " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY A.chaveCidade " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY A.etapa " ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY A.data " ;
                                break;
                            case '4':
                                $orderBy = " ORDER BY A.idProfessor " ;
                                break;
                            case '5':
                                $orderBy = " ORDER BY A.nota " ;
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

                if(isset($_GET["filtro-professor"]) || isset($_GET["filtro-etapa"]) ||
                   isset($_GET["filtro-data-min"])  || isset($_GET["filtro-data-max"]) ||
                   isset($_GET["filtro-cidade"])    || isset($_GET["filtro-ano"])){
                    if(isset($filtroProfessor) && mb_strlen($filtroProfessor) > 0 && 
                        !is_nan($filtroProfessor) && $filtroProfessor != "0"){
                        $query->bindParam(":filtroprofessor",$filtroProfessor);
                    }
                    if(isset($filtroCidade) && mb_strlen($filtroCidade) > 0 && 
                        $filtroCidade != "0"){
                        $vetorCidade = explode("/", $filtroCidade);
                        $nomeCidade = trim($vetorCidade[0]);
                        $ufCidade   = trim($vetorCidade[1]);
                        $query->bindParam(":filtrocidade", $nomeCidade);
                        $query->bindParam(":filtrouf"    , $ufCidade);
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
                    if(isset($filtroAno) && mb_strlen($filtroAno) > 0 && 
                        !is_nan($filtroAno) && $filtroAno != "0"){
                        $query->bindParam(":filtroano" , $filtroAno);
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
                        // listamos os dados de cada aula
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"id\">";
                        $tabela .= htmlspecialchars($linha["idAula"])               ."</td>";
    
                        require_once("entidades/Cidade.php");
                        $cidade = new Cidade();
                        $cidade->setIdCidade($linha["chaveCidade"]);
                        $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);
    
                        $tabela .= "    <td class=\"cidade\" data-id-cidade=\"";
                        $tabela .= $linha["chaveCidade"]."\">";
                        $tabela .= htmlspecialchars($cidade->getNome())             ."</td>";
                        $tabela .= "    <td class=\"etapa\">";
                        $tabela .= htmlspecialchars($linha["etapa"])                ."</td>";
                        $tabela .= "    <td class=\"data\" data-data-html=\"";
                        $tabela .= str_replace("-", "/", $linha["data"])."\">";
                        $tabela .= date("d/m/Y H:i", strtotime($linha["data"])) ."</td>";
    
                        require_once("entidades/Administrador.php");
                        $prof = new Administrador("");
                        $prof->setIdAdmin($linha["idProfessor"]);
                        $sucesso = $prof->recebeAdminId($host, "homeopatias", $usuario, $senhaBD,
                                             "professor");
                        $idProfessor = $sucesso ? $linha["idProfessor"] : -1;
                        $nomeProfessor = $sucesso ? $prof->getNome() : "Indeterminado";
    
                        $tabela .= "    <td class=\"professor\" data-id-professor=\"";
                        $tabela .= htmlspecialchars($idProfessor);
                        $tabela .= "\">";
                        $tabela .= htmlspecialchars($nomeProfessor)           ."</td>";
                        $tabela .= "    <td class=\"nota\">";
                        if(!isset($linha["nota"]) || $linha["nota"] === ""){
                            $tabela .= "N/A";
                        }else{
                            $tabela .= number_format(htmlspecialchars($linha["nota"]), 2)."%";
                        }
                        $tabela .= "</td>";
    
                        $tabela .= "    <td><a data-id=\"";
                        $tabela .= $linha["idAula"];
                        $tabela .= "\" data-descricao=\"";
                        $tabela .= htmlspecialchars($linha["descricao"]);
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-edita-aula\">";
                        $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                        $tabela .= "    <td><a data-href=\"rotinas/aula/";
                        $tabela .= "remover_aula.php?id=";
                        $tabela .= $linha["idAula"];
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
                    <h1>Aulas</h1>
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-aula">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Nova aula</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_aulas.php" id="form-filtro">
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
                            <a id="label-ano" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-ano"]) && 
                                        mb_strlen(($_GET["filtro-ano"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-ano"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Ano
                            </a>
                            <select name="filtro-ano" id="filtro-ano" 
                                        class="form-control"
                                        style="display:inline;width:95px">
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
                            <a id="label-professor" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-professor"]) && 
                                        mb_strlen(($_GET["filtro-professor"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-professor"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Professor
                            </a>
                            <select name="filtro-professor" id="filtro-professor" 
                                        class="form-control"
                                        style="display:inline;width:175px">
                            </select>
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
                            <table class="table table-bordered table-striped" id="aulas">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="50px"<?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>ID</th>
                                        <th width="180px"<?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Cidade</th>
                                        <th width="80px"<?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Etapa</th>
                                        <th width="130px"<?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data e horário</th>
                                        <th width="200px"<?= $indexHeader == 4 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Professor</th>
                                        <th width="60px"<?= $indexHeader == 5 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Nota</th>
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
        <!-- popup "modal" do bootstrap para inserção de nova aula -->
        <div class="modal fade" id="modal-novo-aula" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-aula" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_aulas.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Nova aula</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="data-nova-aula">Data:</label>
                                <input type="date" name="data-nova-aula" id="data-nova-aula"
                                       placeholder="dd/mm/aaaa" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="horario-nova-aula">Horário:</label>
                                <input type="time" name="horario" id="horario-nova-aula" required
                                       placeholder="--:--" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cidade-nova-aula">Cidade:</label>
                                <select name="cidade-nova-aula" id="cidade-nova-aula"
                                        class="form-control" required>
                                    <option value="">Escolha uma data acima...</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="etapa-nova-aula">Etapa:</label>
                                <select name="etapa" id="etapa-nova-aula" required
                                       title="A etapa deve ser um número de 1 a 4"
                                       class="form-control">
                                       <option value="1">1</option>
                                       <option value="2">2</option>
                                       <option value="3">3</option>
                                       <option value="4">4</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="prof-nova-aula">Professor da aula:</label>
                                <select name="prof" id="prof-nova-aula" class="form-control">
                                    <option value="-1">Indeterminado</option>
                                    <?php
                                        require_once("rotinas/professor/lista_professores.php");
                                        $lista = listaProfessores();
                                        for($i = 0; $i < count($lista); $i++){
                                            echo "<option value=\"";
                                            echo $lista[$i]->getIdAdmin()."\">";
                                            echo $lista[$i]->getNome()."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="descricao-nova-aula">Descrição:</label>
                                <textarea name="descricao" id="descricao-nova-aula" rows="8" cols="50"
                                    maxlength="10000" required
                                    title="A descrição da aula deve ser preenchida e ter até
                                           10000 caracteres"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir aula
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de aula -->
        <div class="modal fade" id="modal-edita-aula" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-aula" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/aula/editar_aula.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar aula</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="idAula" id="idAula" value="">
                            <div class="form-group">
                                <label for="data-edita-aula">Data:</label>
                                <input type="date" name="data-edita-aula" id="data-edita-aula"
                                       required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="horario">Horário:</label>
                                <input type="time" name="horario" id="horario" required
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cidade-edita-aula">Cidade:</label>
                                <select name="cidade-edita-aula" id="cidade-edita-aula"
                                        class="form-control" required>
                                    <option value="">Escolha uma data acima...</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="etapa">Etapa:</label>
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
                                <label for="prof">Professor da aula:</label>
                                <select name="prof" id="prof" class="form-control">
                                    <option value="-1">Indeterminado</option>
                                    <?php
                                        require_once("rotinas/professor/lista_professores.php");
                                        $lista = listaProfessores();
                                        for($i = 0; $i < count($lista); $i++){
                                            echo "<option value=\"";
                                            echo $lista[$i]->getIdAdmin()."\">";
                                            echo $lista[$i]->getNome()."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="descricao">Descrição da aula:</label>
                                <textarea name="descricao" id="descricao" rows="8" cols="50"
                                    maxlength="10000" required
                                    title="A descrição da aula deve ser preenchida e ter até
                                           10000 caracteres"
                                    class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar aula
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de aula -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de aula</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover essa aula?</h3>
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