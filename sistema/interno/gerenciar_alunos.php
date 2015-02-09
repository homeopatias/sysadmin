<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Alunos - Homeopatias.com</title>
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

                $("#modal-novo-aluno #curso-novo").parent().hide(500);

                // permite redimensionar as colunas da tabela
                $("#alunos").colResizable({
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

                $("#alunos").tablesorter({ headers: {
                    0 : { sorter: false },
                    4 : { sorter: false },
                    5 : { sorter: false },
                    6 : { sorter: false },
                    7 : { sorter: false },
                    9 : { sorter: false },
                    10 : { sorter: false },
                    11 : { sorter: false },
                    12 : { sorter: false }
                }});

                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-aluno').text(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                });

                // passa os dados do aluno para o modal para a edição
                $("#modal-edita-aluno").on('show.bs.modal', function(e) {
                    $(this).find('#insc').val(
                        $(e.relatedTarget).parent().siblings('.insc').text()
                    );
                    $(this).find('#id').val(
                        $(e.relatedTarget).data('id')
                    );
                    var telefone = $(e.relatedTarget).data('telefone')+"";
                    $(this).find('#telefone').val(
                        ["(", telefone.slice(0, 2), ")", telefone.slice(2, 6), "-",
                         telefone.slice(6)].join('')
                    );

                    //Preenche endereço----------------------
                    $(this).find('#cep').val(
                        $(e.relatedTarget).data('cep')
                    );
                    $(this).find('#rua').val(
                        $(e.relatedTarget).data('rua')
                    );
                    $(this).find('#numero').val(
                        $(e.relatedTarget).data('numero')
                    );
                    $(this).find('#bairro').val(
                        $(e.relatedTarget).data('bairro')
                    );
                    $(this).find('#cidade').val(
                        $(e.relatedTarget).data('cidade')
                    );
                    $(this).find('#estado').val(
                        $(e.relatedTarget).data("estado")
                    );
                    $(this).find('#complemento').val(
                        $(e.relatedTarget).data('complemento')
                    );
                    $(this).find('#tipo_curso').val(
                        $(e.relatedTarget).data('tipo_curso')
                    );
                    $(this).find('#tipo_cadastro').val(
                        $(e.relatedTarget).data('tipo_cadastro')
                    );
                    $(this).find('#cpf').val(
                        $(e.relatedTarget).data('cpf')
                    );
                    $(this).find('#login').val(
                        $(e.relatedTarget).data('login')
                    );

                    //------------------------
                    $(this).find('#escolaridade').val(
                        $(e.relatedTarget).data('escolaridade')
                    );
                    $(this).find('#curso').val(
                        $(e.relatedTarget).data('curso')
                    );
                    if($(e.relatedTarget).data('escolaridade') === "superior incompleto" ||
                       $(e.relatedTarget).data('escolaridade') === "superior completo"   ||
                       $(e.relatedTarget).data('escolaridade') === "mestrado"            ||
                       $(e.relatedTarget).data('escolaridade') === "doutorado"){
                        $(this).find('#curso').parent().show();
                    }else{
                        $(this).find('#curso').parent().hide();
                    }
                    $(this).find('#nome').val(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                    $(this).find('#email').val(
                        $(e.relatedTarget).parent().siblings('.email').text()
                    );
                    $(this).find('#status').val(
                        $(e.relatedTarget).parent().siblings('.status').data("status")
                    );
                    $(this).find('#indicador').val(
                        $(e.relatedTarget).data('indicador')
                    );
                });

                $("#modal-novo-aluno #escolaridade-novo").change(function(){
                    if($(this).val() === "superior incompleto" || $(this).val() === "superior completo"   ||
                       $(this).val() === "mestrado"            || $(this).val() === "doutorado" ){
                        $("#modal-novo-aluno #curso-novo").parent().show(500);
                    }else{
                        $("#modal-novo-aluno #curso-novo").parent().hide(500);
                    }
                });

                $("#modal-edita-aluno #escolaridade").change(function(){
                    if($(this).val() === "superior incompleto" || $(this).val() === "superior completo"   ||
                       $(this).val() === "mestrado"            || $(this).val() === "doutorado" ){
                        $("#modal-edita-aluno #curso").parent().show(500);
                    }else{
                        $("#modal-edita-aluno #curso").parent().hide(500);
                    }
                });

                // esconde inputs de busca

                $("#filtro-nome").hide();
                $("#filtro-cpf").hide();
                $("#ipp").hide();
                $("#filtro-status").hide();
                $("#filtro-numero").hide();
                $("#div-data-min").hide();
                $("#div-data-max").hide();
                $("#filtro-cidade").hide();
                $("#filtro-ano").hide();
                $("#filtro-etapa").hide();


                // alterna campos de texto com campos de input
                $("#label-nome").click(function(){
                    $(this).hide();
                    $("#filtro-nome").show(300);
                    $("#filtro-nome").focus();
                });

                $("#label-cpf").click(function(){
                    $(this).hide();
                    $("#filtro-cpf").show(300);
                    $("#filtro-cpf").focus();
                });

                

                $("#label-status").click(function(){
                    $(this).hide();
                    $("#filtro-status").show(300);
                    $("#filtro-status").focus();
                });                

                $("#label-numero").click(function(){
                    $(this).hide();
                    $("#filtro-numero").show(300);
                    $("#filtro-numero").focus();
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

                $("#label-cidade").click(function(){
                    $(this).hide();
                    $("#filtro-cidade").show(300);
                    $("#filtro-cidade").focus();
                });

                $("#label-ano").click(function(){
                    $(this).hide();
                    $("#filtro-ano").show(300);
                    $("#filtro-ano").focus();
                });

                $("#label-etapa").click(function(){
                    $(this).hide();
                    $("#filtro-etapa").show(300);
                    $("#filtro-etapa").focus();
                });

                // se clicou na lupa, envia o formulário
                $("#busca").click(function(e){
                    atualizaPagina();
                });

                //se mudou a quantidade de pessoas por página, atualiza
                $("#ipp").change(function(){
                    $("#pagina-ipp").val( $(this).val() );
                    atualizaPagina();
                });

                $("#btn-muda-senha").click(function(){
                    $(this).hide();
                    $("#senha").parent().show(300);
                    $("#senha").focus();
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

                // remove inputs em branco do form antes de enviar
                $("#form-filtro").submit(function(){

                    $(':input', this).each(function() {
                         this.disabled = !($(this).val()) || $(this).val() == 0;
                    });

                    if($('#filtro-ano').val() == 0) {
                        $('#filtro-ano')[0].disabled = true;
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

                // se clicou na borracha, apaga todos os campos e envia o formulário limpo
                $("#limpar").click(function(e){
                    $("#filtro-nome").val("");
                    $("#filtro-cpf").val("");
                    $("#filtro-status").val("");
                    $("#filtro-numero").val("");
                    $("#filtro-data-min").val("");
                    $("#filtro-data-max").val("");
                    $("#filtro-cidade").val("");
                    $("#filtro-ano").val("");
                    $("#filtro-etapa").val("");
                    atualizaPagina();
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

                // preenche o select de cidades

                // se há cidade filtrada, seleciona ela
                // remove os sinais de + que são passados e transforma em uma entidade html
                selecionado = <?= isset($_GET["filtro-cidade"]) ?
                             "\"" . str_replace("+","",$_GET["filtro-cidade"]) . "\"" 
                             : "0"?>;


                // A primeira opção indica nenhuma cidade
                var opcao = '<option value="" >Todas</option>';
                            $("#filtro-cidade").append(opcao);

                nomesCidades.forEach(function(cidade){
                    if(selecionado != "" && selecionado == cidade.nome){
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

                //prepara o form do e-mail para ser enviado
                $("#form-email").submit(function(e){

                    $(this).find("#url-send").val(window.location.href);

                    var selected = "";
                    $("table td input:checked").each(function(){
                        selected += $(this).val() + ",";
                    });
                    var element = $("<input type='hidden' id='selecionados' name='selecionados'>");
                    element.val(selected);
                    $(this).append(element);
                });

                //seta o tipo do e-mail a ser enviado
                $("#sendTodos").click(function(e){
                    $("#modal-email").find("#sendType").val("todos");
                });

                $("#sendSelecionados").click(function(e){
                    $("#modal-email").find("#sendType").val("selecionados");
                });

                $('.selc > input[type="checkbox"]').click(function() {
                    var numSelecionados = $("#alunos").find('input[type="checkbox"]:checked').length;
                    if(numSelecionados)
                        $("#send").fadeIn();
                    else
                        $("#send").fadeOut();
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

            //preparamos os dados necessários para fazer os inputs de busca por cidade
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
            
                        anos.push( <?= $ano ?> );
                    <?php } 

                // preenche o vetor de nomes de cidades

                    if(!in_array($linha["nome"], $nomesCidades)){
                        $nomesCidades[] = $linha["nome"];
                ?>
                    nomesCidades.push({
                    id:   <?= $id ?>,
                    nome: <?= $nome . " + \"/\" + " . $uf ?>
                });
                <?php
                    }
                }
                ?>

        </script>
    </head>
    <body>
        <?php

            include("modulos/navegacao.php");

            // mensagem a ser exibida acima da listagem de alunos, caso seja necessário
            $mensagem = "";
            $sucesso = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe alunos apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
               1 & unserialize($_SESSION["usuario"])->getPermissoes() ){

                // lemos as credenciais do banco de dados
                $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                $dados = json_decode($dados, true);

                foreach($dados as $chave => $valor) {
                    $dados[$chave] = str_rot13($valor);
                }

                $host    = $dados["host"];
                $usuario = $dados["nome_usuario"];
                $senhaBD = $dados["senha"];

                // cria conexão com o banco para uso ao longo da página
                $conexao = null;
                $db      = "homeopatias";
                try {
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }

                // se o usuário chegou até aqui através de um formulário, registra o novo
                // aluno no sistema
                if(isset($_POST["submit"])){

                    // validamos todos os dados recebidos
                    $nome           = $_POST["nome"];
                    $cpf            = $_POST["cpf"];
                    $email          = $_POST["email"];
                    $login          = $_POST["login"];
                    $senha          = $_POST["senha"];
                    $loginIndicador = $_POST["indicador"];
                    $telefone       = $_POST["telefone"];
                    $endereco       = $_POST["endereco"];
                    $escolaridade   = $_POST["escolaridade"];
                    $curso          = $_POST["curso-novo"];
                    $cep            = $_POST["cep"];
                    $rua            = $_POST["rua"];
                    $numero         = $_POST["numero"];
                    $complemento    = $_POST["complemento"];
                    $bairro         = $_POST["bairro"];
                    $cidade         = $_POST["cidade"];
                    $estado         = $_POST["estado"];
                    $tipoCurso      = $_POST["tipo_curso"];
                    $tipoCadastro   = $_POST["tipo_cadastro"];

                    $nomeValido     = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                                      mb_strlen($nome, 'UTF-8') <= 100 &&
                                      preg_match("/^.{3,50} .{1,50}$/", $nome);
                                      
                    $cpfValido      = isset($cpf) &&
                                      (preg_match("/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/", $cpf) || 
                                       preg_match("/^\d{11}$/", $cpf));

                    $sucesso = false;
                    
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

                        $todosZero = true;
                        $todosNove = true;
                        for($i = 0; $i <11; $i++){
                            if($cpfChecar[$i] != '0'){
                                $todosZero = false;
                            }
                            if($cpfChecar[$i] != '9'){
                                $todosNove = false;
                            }
                        }

                        if($todosZero || $todosNove){
                            $cpfValido = false;
                        }

                    }

                    $cpfExistente = false;
                    if($cpfValido){
                        //Checa se ja existe este cpf no sistema cadastrado como aluno
                        $cpfNumerico = str_replace(".","",$cpf);
                        $cpfNumerico = str_replace("-","",$cpfNumerico);
                        $textoQuery = "SELECT U.cpf
                                       FROM Usuario U , Administrador A
                                       WHERE U.id = A.idUsuario AND U.cpf = ?
                                       AND A.nivel LIKE 'administrador'";
        
                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $cpfNumerico, PDO::PARAM_STR);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();
    
                        if($linha = $query->fetch()){
                            $cpfValido = false;
                            $cpfExistente = true;
                        }
                    }

                    $emailValido  = isset($email) && mb_strlen($email, 'UTF-8') <= 100 &&
                                    preg_match("/^.+\@.+\..+$/", $email);
    
                    $emailExistente = false;
                    if($emailValido){
                        //Checa se ja existe este email no sistema cadastrado como aluno
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
                    $loginIndicadorValido = (isset($loginIndicador) &&
                                            mb_strlen($loginIndicador, 'UTF-8') >= 3 &&
                                            mb_strlen($loginIndicador, 'UTF-8') <= 100)
                                            || !isset($loginIndicador) || $loginIndicador === "";

                    $idIndicador = "";
                    if($loginIndicadorValido && isset($loginIndicador) && $loginIndicador !== ""){
                        // conferimos se o $loginIndicador representa um aluno no sistema
                        
                        $textoQuery  = "SELECT A.numeroInscricao FROM Aluno A, Usuario U WHERE                 
                                        U.login = ? AND A.idUsuario = U.id";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $loginIndicador, PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        if(!($linha = $query->fetch())){
                            $loginIndicadorValido = false;
                            $mensagem = "Não foi encontrado nos registros um aluno indicador com esse
                                         nome de usuário";
                        }else{
                            $idIndicador = $linha["numeroInscricao"];
                        }
                    }

                    $telefoneValido = isset($telefone) &&
                                      preg_match("/^\(?\d{2}\)?\d{4}-?\d{4,7}$/", $telefone);
                    
                    $enderecoValido = false;

                    // formata CEP
                    $cep = str_replace(".","",$cep);
                    $cep = str_replace("-","",$cep);
                    

                    $cepValido = (isset($cep) && mb_strlen($cep, 'UTF-8') == 8 );
                    
                    

                    $ruaValida = (isset($rua) && mb_strlen($rua, 'UTF-8') >= 3 &&
                                      mb_strlen($rua, 'UTF-8') <= 200);

                    $numeroValido = (isset($numero) && mb_strlen($numero, 'UTF-8') >= 0 &&
                                      mb_strlen($numero, 'UTF-8') <= 200);


                    $bairroValido = (isset($bairro) && mb_strlen($bairro, 'UTF-8') >= 3 &&
                                      mb_strlen($bairro, 'UTF-8') <= 200);

                    $cidadeValida = (isset($cidade) && mb_strlen($cidade, 'UTF-8') >= 3 &&
                                      mb_strlen($cidade, 'UTF-8') <= 200);

                    $estadoValido = (isset($estado) && mb_strlen($estado, 'UTF-8') ==2);

                    $enderecoValido = ($cepValido && $ruaValida && $numeroValido &&
                                        $bairroValido && $cidadeValida
                                       && $estadoValido);


                    $escolaridadeValida = isset($escolaridade) &&
                               ($escolaridade === "fundamental incompleto" ||
                                $escolaridade === "fundamental completo"   ||
                                $escolaridade === "médio incompleto"       ||
                                $escolaridade === "médio completo"         ||
                                $escolaridade === "superior incompleto"    ||
                                $escolaridade === "superior completo"      ||
                                $escolaridade === "mestrado"               ||
                                $escolaridade === "doutorado");

                    // para permitir a validação do curso, conferimos se possui curso superior
                    $superior = ($escolaridade === "superior incompleto"    ||
                                 $escolaridade === "superior completo"      ||
                                 $escolaridade === "mestrado"               ||
                                 $escolaridade === "doutorado");
                    $cursoValido = ((!isset($curso) || $curso === "") && !$superior) ||
                                   (isset($curso) && mb_strlen($curso) > 0 && mb_strlen($curso) <= 200);

                    $tipoCursoValido = $tipoCurso == "extensao" || $tipoCurso == "pos" ;

                    $tipoCadastroValido = $tipoCadastro == "instituto" || 
                                          $tipoCadastro == "faculdade inspirar";

                    // se todos os dados estão válidos, o aluno é cadastrado
                    if($nomeValido && $cpfValido && $emailValido && $loginValido && $senhaValida &&
                       $loginIndicadorValido && $telefoneValido && $enderecoValido &&
                       $escolaridadeValida && $cursoValido && $tipoCursoValido && $tipoCadastroValido){

                        require_once("entidades/Aluno.php");

                        $novo = new Aluno($login);
                        $novo->setNome($nome);
                        $novo->setCpf($cpf);
                        $novo->setEmail($email);
                        $novo->setTelefone($telefone);
                        $novo->setEscolaridade($escolaridade);
                        $novo->setCep($cep);
                        $novo->setRua($rua);
                        $novo->setNumero($numero);
                        $novo->setComplemento($complemento);
                        $novo->setBairro($bairro);
                        $novo->setCidade($cidade);
                        $novo->setEstado($estado);
                        $novo->setPais("BRL");
                        $novo->setTipoCurso($tipoCurso);
                        $novo->setTipoCadastro($tipoCadastro);
                        if($escolaridade === "superior incompleto" || $escolaridade === "superior completo"   ||
                           $escolaridade === "mestrado"            || $escolaridade === "doutorado" ){
                            $novo->setCurso(isset($curso) ? $curso : null);
                        }else{
                            $novo->setCurso(null);
                        }
                        $novo->setStatus("preinscrito");

                        $novo->setIdIndicador($idIndicador);

                        $sucesso  = $novo->cadastrar($host, "homeopatias", $usuario, $senhaBD, $senha);
                        $mensagem = "Usuário cadastrado com sucesso";
                        if(!$sucesso){
                            $mensagem = "Já existe um usuário com esse nome 
                                         de usuário no sistema";
                        }

                    }else if(!$nomeValido){
                        $mensagem = "Nome inválido!";
                    }else if(!$cpfValido && !$cpfExistente){
                        $mensagem = "CPF inválido!";
                    }else if($cpfExistente){
                        $mensagem = "CPF ja cadastrado!";
                    }else if(!$emailValido && !$emailExistente){
                        $mensagem = "E-mail inválido!";
                    }else if($emailExistente){
                        $mensagem = "E-mail ja cadastrado!";
                    }else if(!$loginValido){
                        $mensagem = "Nome de usuário inválido!";
                    }else if(!$senhaValida){
                        $mensagem = "Senha inválida!";
                    }else if(!$telefoneValido){
                        $mensagem = "Telefone inválido!";
                    }else if(!$enderecoValido){
                        $mensagem = "Endereço inválido!";
                    }else if(!$escolaridadeValida){
                        $mensagem = "Escolaridade inválida!";
                    }else if(!$cursoValido){
                        if((!isset($curso) || $curso === "") && $superior){
                            $mensagem = "Insira o curso superior!";
                        }else{
                            $mensagem = "Curso inválido!";
                        }
                    }
                    else if(!$tipoCursoValido){
                        $mensagem = "Tipo de curso inválido";
                    }
                    else if(!$tipoCadastroValido){
                        $mensagem = "Tipo de cadastro inválido";
                    }
                }
                $filtroCidade     = null;
                $queryAnoCidade   = null;
                $filtroAnoCidade  = null;
                if( isset($_GET["filtro-cidade"] ) ){
                    $filtroCidade    = $_GET["filtro-cidade"] ;
                }
                if( isset($_GET["filtro-ano"]) ){
                    $filtroAnoCidade = htmlspecialchars( $_GET["anoCidade"] );
                }

                $textoQuery  =  "SELECT U.id, U.cpf, U.dataInscricao, U.email,
                                U.nome, U.login, A.numeroInscricao, A.status, A.idIndicador, 
                                A.telefone, A.cep, A.rua, A.numero, A.bairro, A.cidade, A.estado,
                                A.complemento, A.escolaridade, A.curso, A.tipo_curso, A.tipo_cadastro,
                                MAX(C.ano) as anoMatricula, MAX(M.etapa) as etapaMatricula, A.ativo
                                FROM Usuario U, Aluno A";

                $textoQuery .=  (mb_strlen($filtroCidade) > 0 || isset($_GET["filtro-etapa"]) 
                                 && $_GET["filtro-etapa"] != "0" || mb_strlen($filtroAnoCidade) >0 
                                 && $filtroAnoCidade != "0")
                                            ? ", Cidade C, Matricula M "
                                            : "";

                $textoQuery .=  " LEFT JOIN Matricula M ON M.chaveAluno = A.numeroInscricao
                                  LEFT JOIN Cidade C ON M.chaveCidade = C.idCidade
                                  WHERE A.idUsuario = U.id ";

                $textoQuery .= ( mb_strlen($filtroCidade) > 0 || isset($_GET["filtro-etapa"]) 
                                 && $_GET["filtro-etapa"] != "0" || mb_strlen($filtroAnoCidade) >0 
                                 && $filtroAnoCidade != "0" )
                                            ?"AND M.chaveAluno = A.numeroInscricao  
                                              AND M.chaveCidade = C.idCidade "
                                            : "";
                $textoQuery .= mb_strlen($filtroCidade)  > 0 && $filtroCidade != "0"
                                                    ? "
                                                        AND C.nome = :filtrocidade
                                                        AND C.uf = :filtrouf "
                                                    : "" ;

                $textoQuery .= mb_strlen($filtroAnoCidade) > 0 && $filtroAnoCidade != "0" 
                                                       ? " AND C.ano = :anoCidade"
                                                       : "" ;

                // se algum filtro foi enviado, filtra os resultados da consulta
                $filtroNome = $filtroCpf = $filtroStatus = $filtroNumero = 
                $filtroDataMin = $filtroDataMax = false;

                // como não há botão para submit, temos que checar se todas as variáveis
                // existem
                if(isset($_GET["filtro-nome"])     || isset($_GET["filtro-cpf"])      ||
                   isset($_GET["filtro-status"])   || isset($_GET["filtro-numero"])   ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"]) ||
                   isset($_GET["filtro-cidade"])   || isset($_GET["filtro-ano"])      ||
                   isset($_GET["filtro-etapa"])                                  ){
                    $filtroNome    =  htmlspecialchars($_GET["filtro-nome"]);
                    $filtroCpf     =  htmlspecialchars($_GET["filtro-cpf"]);
                    $filtroStatus  =  htmlspecialchars($_GET["filtro-status"]);
                    $filtroNumero  =  htmlspecialchars($_GET["filtro-numero"]);
                    $filtroDataMin =  htmlspecialchars($_GET["filtro-data-min"]);
                    $filtroDataMax =  htmlspecialchars($_GET["filtro-data-max"]);
                    $filtroEtapa      =  htmlspecialchars($_GET["filtro-etapa"]);

                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        // prepara o nome para ser colocado na query
                        $filtroNome    =  "%".$filtroNome."%";
                        $textoQuery .= "  AND U.nome LIKE :nome";
                    }
                    if(isset($filtroCpf) && mb_strlen($filtroCpf) > 0){
                        $textoQuery .= "  AND U.cpf LIKE :cpf";
                    }
                    if(isset($filtroStatus) && mb_strlen($filtroStatus) > 0){
                        $textoQuery .= " AND A.status LIKE :status";
                    }
                    if(isset($filtroNumero) && mb_strlen($filtroNumero) > 0) {
                        if(!is_nan($filtroNumero)){
                            $textoQuery .= " AND A.numeroInscricao = :numInsc";
                        }
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        $textoQuery .= " AND CAST(U.dataInscricao AS Date) >= ";
                        $textoQuery .= "CAST(:dataMin as Date)";
                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        $textoQuery .= " AND CAST(U.dataInscricao AS Date) <= ";
                        $textoQuery .= "CAST(:dataMax as Date)";
                    }
                    if(isset($filtroEtapa) && mb_strlen($filtroEtapa) > 0 && 
                        !is_nan($filtroEtapa) && $filtroEtapa != "0"){
                        $textoQuery .= " AND M.etapa LIKE :filtroetapa ";
                    }


                }

                $textoQuery .= " GROUP BY U.id";

                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY U.dataInscricao DESC" ;
                $indexHeader = isset($_GET["numeroTableHeader"] ) 
                                ? htmlspecialchars( $_GET["numeroTableHeader"] ) 
                                : -1 ;
                $direcao = 2;
                //------------------

                if( isset($_GET["numeroTableHeader"]) && isset($_GET["cimaOuBaixo"]) ){
                    $indexHeader = htmlspecialchars( $_GET["numeroTableHeader"] );
                    if( !is_nan($indexHeader) ){
                        
                        switch ($indexHeader) {
                            case '0':
                                $orderBy = " ORDER BY A.numeroInscricao " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY U.nome " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY U.email " ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY U.status " ;
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

                // passamos os parâmetros corretamente de acordo com os filtros passados
                if(isset($_GET["filtro-nome"])     || isset($_GET["filtro-cpf"])      ||
                   isset($_GET["filtro-status"])   || isset($_GET["filtro-numero"])   ||
                   isset($_GET["filtro-data-min"]) || isset($_GET["filtro-data-max"]) ||
                   isset($_GET["filtro-cidade"])   || isset($_GET["filtro-ano"])      ||
                   isset($_GET["filtro-etapa"])                                         ){
                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $query->bindParam(":nome", $filtroNome);
                    }
                    if(isset($filtroCpf) && mb_strlen($filtroCpf) > 0){
                        // remove os '.' e '-' para comparar com o cpf do bd
                        $filtroCpf = str_replace(".","",$filtroCpf);
                        $filtroCpf = str_replace("-","",$filtroCpf);

                        $query->bindParam(":cpf", $filtroCpf);
                    }
                    if(isset($filtroStatus) && mb_strlen($filtroStatus) > 0){
                        $query->bindParam(":status", $filtroStatus);
                    }
                    if(isset($filtroNumero) && mb_strlen($filtroNumero) > 0) {
                        if(!is_nan($filtroNumero)){
                            $query->bindParam(":numInsc", $filtroNumero);
                        }
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        $query->bindParam(":dataMin" , $filtroDataMin);
                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        $query->bindParam(":dataMax" , $filtroDataMax);
                    }
                    if(isset($filtroAnoCidade) && mb_strlen($filtroAnoCidade) > 0 ){
                        $query->bindParam(":anoCidade" , $filtroAnoCidade);

                    }
                    if(isset($filtroCidade) && mb_strlen($filtroCidade) > 0 ){
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
                }

                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $numeroRegistros = $query->rowCount();

                $possuiProximaPagina = false;
                $contador = 0;
                $tabela = "";

                while ($linha = $query->fetch()){
                    if($contador != $itemsPorPagina){
                    // formatando o texto do cpf
                    $cpfOriginal = str_split($linha["cpf"]);
                    $cepOriginal = str_split($linha["cep"]);

                    $cpf  = implode("", array_slice($cpfOriginal, 0, 3)) . ".";
                    $cpf .= implode("", array_slice($cpfOriginal, 3, 3)) . ".";
                    $cpf .= implode("", array_slice($cpfOriginal, 6, 3)) . "-";
                    $cpf .= implode("", array_slice($cpfOriginal, 9, 2));
                    $cpf  = htmlspecialchars($cpf);

                    $cep  = implode("", array_slice($cepOriginal, 0, 5)) . "-"; ;
                    $cep .= implode("", array_slice($cepOriginal, 5, 8));

                    // listamos os dados de cada usuário
                    $tabela .= "<tr>";
                    $tabela .= "    <td class=\"selc\">";
                    $tabela .= '<input type="checkbox" name="inscricoes[]"
                                value="'.$linha['numeroInscricao'].'"> </td>';
                    $tabela .= "    <td class=\"insc\">";
                    $tabela .= htmlspecialchars($linha["numeroInscricao"])  ."</td>";
                    $tabela .= "    <td class=\"nome\">";
                    $tabela .= htmlspecialchars($linha["nome"])             ."</td>";
                    $tabela .= "    <td class=\"email\">";
                    $tabela .= htmlspecialchars($linha["email"])            ."</td>";
                    $etapaMatricula = htmlspecialchars($linha["etapaMatricula"]);
                    if($etapaMatricula == "") {
                        $etapaMatricula = "N/A";
                    }
                    $tabela .= "    <td class=\"etapa\">";
                    $tabela .= $etapaMatricula ."</td>";
                    $anoMatricula = htmlspecialchars($linha["anoMatricula"]);
                    if($anoMatricula == "") {
                        $anoMatricula = "N/A";
                    }
                    $tabela .= "    <td class=\"ano\">";
                    $tabela .= $anoMatricula   ."</td>";
                    $tabela .= "    <td class=\"tipocurso\">";
                    
                    $tipocurso = htmlspecialchars($linha["tipo_curso"]);
                    if($tipocurso == 'extensao') {
                        $tabela .= "Extensão";
                    } else if($tipocurso == 'pos') {
                        $tabela .= "Pós-graduação";
                    }

                    $tabela .=  "</td>";
                    $tabela .= "    <td class=\"tipocadastro\">";

                    $tipocadastro = htmlspecialchars($linha["tipo_cadastro"]);
                    if($tipocadastro == 'instituto') {
                        $tabela .= "Instituto";
                    } else if($tipocadastro == 'faculdade inspirar') {
                        $tabela .= "Faculdade Inspirar";
                    }

                    $tabela .= "</td>";                    

                    $tabela .= "    <td class=\"status\" data-status=\"";
                    $tabela .= htmlspecialchars($linha["status"]). "\">";
                    if($linha["status"] === "inscrito"){
                        $tabela .= "Inscrito";
                    } else if($linha["status"] === "preinscrito"){
                        $tabela .= "Pré-inscrito";
                    } else if($linha["status"] === "desistente"){
                        $tabela .= "Desistente";
                    } else if($linha["status"] === "formado"){
                        $tabela .= "Formado";
                    } else if($linha["status"] === "inativo"){
                        $tabela .= "Inativo";
                    }
                    $tabela .= "</td>";

                    $sql  = "SELECT U.login FROM Usuario U, Aluno A WHERE A.idUsuario = U.id ";
                    $sql .= "AND A.numeroInscricao = ?";

                    $res = $conexao->prepare($sql);
                    $res->bindParam(1, $linha["idIndicador"], PDO::PARAM_INT);
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $res->execute();
                    $linhaIndicador = $res->fetch();

                    $tabela .= "    <td><a href=\"visualizar_aluno.php?id=";
                    $tabela .= $linha["numeroInscricao"] . "\">";
                    $tabela .= "<i class=\"fa fa-eye\"></i></a></td>";

                    if($linha["ativo"]) {
                        $tabela .= "    <td>";
                        $tabela .= "<i class=\"fa fa-check\" style=\"color: #0A0\"></i></td>";
                    } else {
                        $tabela .= "    <td><a href=\"rotinas/ativar_aluno.php?id=";
                        $tabela .= $linha["numeroInscricao"] . "&pagina=" . $_GET["pagina"] . "\">";
                        $tabela .= "<i class=\"fa fa-bolt\" style=\"color: orange\"></i></a></td>";
                    }

                    $tabela .= "    <td><a data-indicador=\"";
                    $tabela .= $linhaIndicador["login"];
                    $tabela .= "\" data-id=\"";
                    $tabela .= $linha["id"];
                    $tabela .= "\" data-login=\"";
                    $tabela .= htmlspecialchars($linha["login"]);
                    $tabela .= "\" data-cpf=\"";
                    $tabela .= $cpf;
                    $tabela .= "\" data-telefone=\"";
                    $tabela .= $linha["telefone"];
                    $tabela .= "\" data-escolaridade=\"";
                    $tabela .= $linha["escolaridade"];
                    $tabela .= "\" data-curso=\"";
                    $tabela .= $linha["curso"];
                    $tabela .= "\" data-cep=\"";
                    $tabela .= $cep;
                    $tabela .= "\" data-rua=\"";
                    $tabela .= $linha["rua"];
                    $tabela .= "\" data-numero=\"";
                    $tabela .= $linha["numero"];
                    $tabela .= "\" data-bairro=\"";
                    $tabela .= $linha["bairro"];
                    $tabela .= "\" data-cidade=\"";
                    $tabela .= $linha["cidade"];
                    $tabela .= "\" data-estado=\"";
                    $tabela .= $linha["estado"];
                    $tabela .= "\" data-complemento=\"";
                    $tabela .= $linha["complemento"];
                    $tabela .= "\" data-tipo_curso=\"";
                    $tabela .= $linha["tipo_curso"];
                    $tabela .= "\" data-tipo_cadastro=\"";
                    $tabela .= $linha["tipo_cadastro"];
                    $tabela .= "\"href=\"#\" data-toggle=\"modal\"";
                    $tabela .= " data-target=\"#modal-edita-aluno\">";
                    $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                    $tabela .= "    <td><a data-href=\"rotinas/aluno/";
                    $tabela .= "remover_aluno.php?id=";
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
                    <h1>Alunos</h1>
                    <?php
                        if(isset($_GET["sucesso"])){
                            $sucesso = htmlspecialchars($_GET["sucesso"]);
                            $mensagem = isset($_GET["mensagem"]) ? htmlspecialchars($_GET["mensagem"]):
                                "";
                        }
                        if(mb_strlen($mensagem, 'UTF-8') !== 0 && !$sucesso){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                        if($sucesso){
                            echo "<p class=\"sucesso\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-aluno">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Novo aluno</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_alunos.php" id="form-filtro">
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

                            <a id="label-status" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-status"]) && 
                                        mb_strlen(($_GET["filtro-status"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Status
                            </a>

                                <select name="filtro-status" id="filtro-status" class="form-control"
                                        style="display:inline;width:120px">
                                        <option value="" 
                                            <?=isset($_GET["filtro-status"]) &&
                                                htmlspecialchars($_GET["filtro-status"]) == "" ?
                                                'selected="selected"': '' ;?> >Nenhum
                                        </option>
                                        <option value="preinscrito"
                                            <?=isset($_GET["filtro-status"]) &&
                                                htmlspecialchars($_GET["filtro-status"]) == "preinscrito"?
                                            'selected="selected"':'';?> >
                                        Pré-inscrito</option>
                                        <option value="inscrito"
                                            <?=isset($_GET["filtro-status"]) &&
                                                htmlspecialchars($_GET["filtro-status"]) == "inscrito"?
                                            'selected="selected"':'';?> >
                                        Inscrito</option>
                                        <option value="desistente"
                                           <?=isset($_GET["filtro-status"]) &&
                                                htmlspecialchars($_GET["filtro-status"]) == "desistente"?
                                           'selected="selected"':'';?> >
                                        Desistente</option>
                                        <option value="formado"
                                            <?=isset($_GET["filtro-status"]) &&
                                                htmlspecialchars($_GET["filtro-status"]) == "formado"?
                                           'selected="selected"':'';?> >
                                        Formado</option>
                                        <option value="inativo"
                                            <?=isset($_GET["filtro-status"]) &&
                                                htmlspecialchars($_GET["filtro-status"]) == "inativo"?
                                           'selected="selected"':'';?> >
                                        Inativo</option>
                                    </select>

                            <a id="label-numero" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-numero"]) && 
                                        mb_strlen(($_GET["filtro-numero"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Inscrição
                            </a>

                            <input type="text" name="filtro-numero"
                                       id="filtro-numero"
                                       placeholder="Nº insc" class="form-control"
                                       style="display:inline;width:75px"
                                       value= <?= isset($_GET["filtro-numero"]) ? 
                                        htmlspecialchars($_GET["filtro-numero"]) : "" ?> >

                            <a id="label-data-min" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-data-min"]) && 
                                        mb_strlen(($_GET["filtro-data-min"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >Inscritos desde
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
                                >Inscritos até
                            </a>
                            <div id="div-data-max" style="display: inline">
                            <input type="date" name="filtro-data-max" id="filtro-data-max"
                                       placeholder="dd/mm/aaaa" class="form-control"
                                       style="display:inline;width:150px"
                                       value =<?= isset($_GET["filtro-data-max"]) ?
                                                htmlspecialchars($_GET["filtro-data-max"]) : "" ?> >
                            </div>
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
                            <div>
                                <a href="#" class="btn btn-primary pull-right" data-toggle="modal"
                                data-target="#modal-email"
                                id="sendTodos">
                                <p>Enviar e-mail para todos</p>
                            </a>
                            <a href="#" class="btn btn-primary pull-right" data-toggle="modal" 
                                data-target="#modal-email"
                                id="send" style="margin-right:2em; display:none">
                                <p>Enviar e-mail para os selecionados</p>
                            </a>
                                <a href="#" id="busca" class="btn btn-info" style="margin-left: 50px">
                                    Buscar
                                    <i href="#" class="fa fa-search"></i>
                               </a>
                                <a href="#" id="limpar" class="btn btn-info" style="margin-left: 10px">
                                    Limpar
                                    <i href="#" class="fa fa-eraser"></i>
                                </a>
                            </div>

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
                    <!-- fim dos filtros -->
                    <br>
                    <?php if($numeroRegistros !== 0){ ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="alunos">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width= "80px">Selecionar</th>
                                        <th width="90px" <?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?> >Inscrição</th>
                                        <th width="160px"
                                            <?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?> >Nome</th>
                                        <th width="180px"
                                            <?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?> >E-mail</th>
                                        <th width="70px">Etapa</th>
                                        <th width="70px">Ano</th>
                                        <th width="100px">Tipo</th>
                                        <th width="100px">Certificado</th>
                                        <th width="100px"
                                            <?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?> >Status</th>
                                        <th width="60px">Visualizar</th>
                                        <th width="60px">Ativar</th>
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
                    <?php } 
                        
                    ?>
                    
                </section>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para inserção de novo aluno -->
        <div class="modal fade" id="modal-novo-aluno" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-aluno" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_alunos.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Novo aluno</h4>
                        </div>
                        <div class="modal-body" style="padding-bottom: 0px">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="nome-novo">Nome do aluno:</label>
                                <input type="text" name="nome" id="nome-novo" required
                                       pattern="^.{3,50} .{1,50}$" title="O nome deve ter de 3 a 100 caracteres, insira o nome completo"
                                       placeholder="Nome" class="form-control" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="cpf-novo">CPF do aluno:</label>
                                <input type="text" name="cpf" id="cpf-novo" required
                                       pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                       placeholder="xxx.xxx.xxx-xx" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email-novo">E-mail do aluno:</label>
                                <input type="email" name="email" id="email-novo" required
                                       placeholder="E-mail"
                                       title="Insira um e-mail válido"
                                       class="form-control">
                            </div>

                            <div class="form-group col-sm-12" >
                                <label for="">Endereço do aluno:</label>
                                    <div style="display:block">
            
                                        <div  class="col-sm-6 col-md-4 " 
                                            style="padding-top:10px;padding-bot:10px">
                                            <label for="cep-novo" style="display:inline">CEP :</label>
                                            <input type="text" name="cep" id="cep-novo"
                                                pattern="^[0-9]{2}.?[0-9]{3}-?[0-9]{3}$" 
                                                placeholder="xxxxx-xxx"
                                                title="Insira um CEP válido"
                                                class="form-control"
                                                style="width:90px" required>
                                        </div>
                                        <div  class="col-sm-6 col-md-4"
                                        style="padding-top:10px;padding-bot:10px">
                                            <label for="rua-novo">Rua :</label>
                                            <input type="text" name="rua" id="rua-novo"
                                                pattern="^.{0,200}$" placeholder="Rua"
                                                title="A rua deve ter no máximo 200 caracteres"
                                                class="form-control"
                                                style="width:150px " required>
                                        </div>
                                        <div  class="col-sm-6 col-md-4"
                                        style="padding-top:10px;padding-bot:10px">
                                            <label for="numero-novo">
                                                Numero :</label>
                                            <input type="text" name="numero" id="numero-novo"
                                                placeholder="xx"
                                                title="Insira o numero da residência do aluno"
                                                class="form-control"
                                                style="width:80px ;" required>
                                        </div>
            
                                        <div  class="col-sm-6 col-md-4"
                                        style="padding-top:10px;padding-bot:10px">
                                            <label for="bairro-novo" >
                                                Bairro :</label>
                                            <input type="text" name="bairro" id="bairro-novo"
                                                placeholder="Bairro"
                                                title="Insira o bairro da residência do aluno"
                                                class="form-control"
                                                style="width:120px ;" required>
                                        </div>
            
                                        
                                        <div  class="col-sm-6 col-md-4"
                                        style="padding-top:10px;padding-bot:10px">
                                            <label for="cidade-novo" >
                                                Cidade :</label>
                                            <input type="text" name="cidade" id="cidade-novo"
                                                placeholder="Cidade"
                                                title="Insira o nome da cidade do aluno"
                                                class="form-control"
                                                style="width:150px ;" required>
                                        </div>
                                        <div  class="col-sm-6 col-md-4"
                                        style="padding-top:10px;padding-bot:10px">
                                            <label for="estado-novo">
                                                Estado :</label>
                                            <select name="estado" id="estado-novo" class="form-control"
                                            style="width:120px">
                                                <option value="AC">Acre</option>
                                                <option value="AL">Alagoas</option>
                                                <option value="AM">Amazonas</option>
                                                <option value="AP">Amapá</option>
                                                <option value="BA">Bahia</option>
                                                <option value="CE">Ceará</option>
                                                <option value="DF">Distrito Federal</option>
                                                <option value="ES">Espírito Santo</option>
                                                <option value="GO">Goiás</option>
                                                <option value="MA">Maranhão</option>
                                                <option value="MT">Mato Grosso</option>
                                                <option value="MS">Mato Grosso do Sul</option>
                                                <option value="MG">Minas Gerais</option>
                                                <option value="PA">Pará</option>
                                                <option value="PB">Paraíba</option>
                                                <option value="PR">Paraná</option>
                                                <option value="PE">Pernambuco</option>
                                                <option value="PI">Piauí</option>
                                                <option value="RJ">Rio de Janeiro</option>
                                                <option value="RN">Rio Grande do Norte</option>
                                                <option value="RO">Rondônia</option>
                                                <option value="RS">Rio Grande do Sul</option>
                                                <option value="RR">Roraima</option>
                                                <option value="SC">Santa Catarina</option>
                                                <option value="SE">Sergipe</option>
                                                <option value="SP">São Paulo</option>
                                                <option value="TO">Tocantins</option>
                                            </select>
                                        </div>
            
            
                                        <div  class="col-sm-6 col-md-12"
                                        style="padding-top:10px;padding-bot:10px">
                                            <label for="complemento-novo">
                                                Complemento :</label>
                                            <input type="text" name="complemento" id="complemento-novo"
                                                placeholder="Complemento"
                                                title="Insira o complemento da residência do aluno"
                                                class="form-control"
                                                style="width:200px" >
                                        </div>
            
                                    </div>
                                
                                </div>
                            <div class="form-group">
                                <label for="telefone-novo">Telefone do aluno:</label>
                                <input type="tel" name="telefone" id="telefone-novo" required
                                       placeholder="(xx)xxxx-xxxx" pattern="^\(?\d{2}\)?\d{4}-?\d{4,7}$"
                                       title="Insira um telefone válido"
                                       class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="escolaridade-novo">Nível de escolaridade:</label>
                                <select name="escolaridade" id="escolaridade-novo" class="form-control">
                                    <option value="fundamental incompleto" selected>
                                        Ensino Fundamental Incompleto
                                    </option>
                                    <option value="fundamental completo">
                                        Ensino Fundamental Completo
                                    </option>
                                    <option value="médio incompleto">
                                        Ensino Médio Incompleto
                                    </option>
                                    <option value="médio completo">
                                        Ensino Médio Completo
                                    </option>
                                    <option value="superior incompleto">
                                        Ensino Superior Incompleto
                                    </option>
                                    <option value="superior completo">
                                        Ensino Superior Completo
                                    </option>
                                    <option value="mestrado">
                                        Mestrado
                                    </option>
                                    <option value="doutorado">
                                        Doutorado
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="curso-novo">Curso superior cursado:</label>
                                <input type="text" name="curso-novo" id="curso-novo"
                                       pattern="^.{0,200}$" placeholder="Curso superior cursado"
                                       title="O curso deve ter no máximo 200 caracteres"
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
                                <label for="indicador-novo">
                                    Foi indicado por alguém?
                                    Em caso afirmativo, insira o nome de usuário do
                                    indicador:
                                </label>
                                <input type="text" name="indicador" id="indicador-novo"
                                       pattern="^.{3,100}$"
                                       placeholder="Nome de usuário do indicador, se existir"
                                       title="Esse campo deve ter um login de 3 a 100 caracteres ou ficar vazio"
                                       class="form-control" autocomplete="off">
                            </div>

                            <br>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir aluno
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de aluno -->
        <div class="modal fade" id="modal-edita-aluno" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-aluno" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/aluno/editar_aluno.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar aluno</h4>
                        </div>
                        <div class="modal-body" style="padding-bottom: 0px">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="insc" id="insc" value="">
                            <input type="hidden" name="id" id="id" value="">
                            <div class="form-group">
                                <label for="nome">Nome do aluno:</label>
                                <input type="text" name="nome" id="nome" required
                                       pattern="^.{3,50} .{1,50}$" title="O nome deve ter de 3 a 100 caracteres, insira o nome completo"
                                       placeholder="Nome" class="form-control" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="cpf">CPF do aluno:</label>
                                <input type="text" name="cpf" id="cpf" required
                                       pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                       placeholder="xxx.xxx.xxx-xx" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail do aluno:</label>
                                <input type="email" name="email" id="email" required
                                       placeholder="E-mail"
                                       title="Insira um e-mail válido"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="telefone">Telefone do aluno:</label>
                                <input type="tel" name="telefone" id="telefone" required
                                       placeholder="(xx)xxxx-xxxx" pattern="^\(?\d{2}\)?\d{4}-?\d{4,7}$"
                                       title="Insira um telefone válido"
                                       class="form-control">
                            </div>
                            <div class="form-group col-sm-12" >
                                <label for="">Endereço do aluno:</label>
                                <div style="display:block">
        
                                    <div  class="col-sm-6 col-md-4 " 
                                        style="padding-top:10px;padding-bot:10px">
                                        <label for="cep" style="display:inline">CEP :</label>
                                        <input type="text" name="cep" id="cep"
                                            pattern="^[0-9]{2}.?[0-9]{3}-?[0-9]{3}$" 
                                            placeholder="xxxxx-xxx"
                                            title="Insira um CEP válido"
                                            class="form-control"
                                            style="width:90px" required>
                                    </div>
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="rua">Rua :</label>
                                        <input type="text" name="rua" id="rua"
                                            pattern="^.{0,200}$" placeholder="Rua"
                                            title="A rua deve ter no máximo 200 caracteres"
                                            class="form-control"
                                            style="width:150px " required>
                                    </div>
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="numero">
                                            Numero :</label>
                                        <input type="text" name="numero" id="numero"
                                            placeholder="xx"
                                            title="Insira o numero da residência do aluno"
                                            class="form-control"
                                            style="width:80px ;" required>
                                    </div>
        
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="bairro" >
                                            Bairro :</label>
                                        <input type="text" name="bairro" id="bairro"
                                            placeholder="Bairro"
                                            title="Insira o bairro da residência do aluno"
                                            class="form-control"
                                            style="width:120px ;" required>
                                    </div>
        
                                    
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="cidade" >
                                            Cidade :</label>
                                        <input type="text" name="cidade" id="cidade"
                                            placeholder="Cidade"
                                            title="Insira o nome da cidade do aluno"
                                            class="form-control"
                                            style="width:150px ;" required>
                                    </div>
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="estado">
                                            Estado :</label>
                                        <select name="estado" id="estado" class="form-control"
                                        style="width:120px">
                                            <option value="AC">Acre</option>
                                            <option value="AL">Alagoas</option>
                                            <option value="AM">Amazonas</option>
                                            <option value="AP">Amapá</option>
                                            <option value="BA">Bahia</option>
                                            <option value="CE">Ceará</option>
                                            <option value="DF">Distrito Federal</option>
                                            <option value="ES">Espírito Santo</option>
                                            <option value="GO">Goiás</option>
                                            <option value="MA">Maranhão</option>
                                            <option value="MT">Mato Grosso</option>
                                            <option value="MS">Mato Grosso do Sul</option>
                                            <option value="MG">Minas Gerais</option>
                                            <option value="PA">Pará</option>
                                            <option value="PB">Paraíba</option>
                                            <option value="PR">Paraná</option>
                                            <option value="PE">Pernambuco</option>
                                            <option value="PI">Piauí</option>
                                            <option value="RJ">Rio de Janeiro</option>
                                            <option value="RN">Rio Grande do Norte</option>
                                            <option value="RO">Rondônia</option>
                                            <option value="RS">Rio Grande do Sul</option>
                                            <option value="RR">Roraima</option>
                                            <option value="SC">Santa Catarina</option>
                                            <option value="SE">Sergipe</option>
                                            <option value="SP">São Paulo</option>
                                            <option value="TO">Tocantins</option>
                                        </select>
                                    </div>
        
        
                                    <div  class="col-sm-6 col-md-12"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="complemento">
                                            Complemento :</label>
                                        <input type="text" name="complemento" id="complemento"
                                            placeholder="Complemento"
                                            title="Insira o complemento da residência do aluno"
                                            class="form-control"
                                            style="width:200px" >
                                    </div>
        
                                </div>
                                
                            </div>
                            <div class="form-group">
                                <label for="escolaridade">Nível de escolaridade:</label>
                                <select name="escolaridade" id="escolaridade" class="form-control">
                                    <option value="fundamental incompleto" selected>
                                        Ensino Fundamental Incompleto
                                    </option>
                                    <option value="fundamental completo">
                                        Ensino Fundamental Completo
                                    </option>
                                    <option value="médio incompleto">
                                        Ensino Médio Incompleto
                                    </option>
                                    <option value="médio completo">
                                        Ensino Médio Completo
                                    </option>
                                    <option value="superior incompleto">
                                        Ensino Superior Incompleto
                                    </option>
                                    <option value="superior completo">
                                        Ensino Superior Completo
                                    </option>
                                    <option value="mestrado">
                                        Mestrado
                                    </option>
                                    <option value="doutorado">
                                        Doutorado
                                    </option>
                                </select>
                            </div>
                            <div class="form-group" style="display:none">
                                <label for="curso">Curso superior cursado:</label>
                                <input type="text" name="curso" id="curso"
                                       pattern="^.{0,200}$" placeholder="Curso superior cursado"
                                       title="O curso deve ter no máximo 200 caracteres"
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
                                <label for="status">Status do aluno:</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="preinscrito" selected>Pré-inscrito</option>
                                    <option value="inscrito">Inscrito</option>
                                    <option value="desistente">Desistente</option>
                                    <option value="formado">Formado</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tipo_curso">Tipo de curso</label>
                                <select id="tipo_curso" name = "tipo_curso" class="form-control">
                                    <option value="extensao">Extensão</option>
                                    <option value="pos">Pós Graduação</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="tipo_cadastro">Tipo de Cadastro</label>
                                <select id="tipo_cadastro" name="tipo_cadastro" class="form-control">
                                    <option value="instituto">Instituto</option>
                                    <option value="faculdade inspirar">Faculdade Inspirar</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="indicador">
                                    Foi indicado por alguém?
                                    Em caso afirmativo, insira o nome de usuário do
                                    indicador:
                                </label>
                                <input type="text" name="indicador" id="indicador"
                                       pattern="^.{3,100}$"
                                       placeholder="Nome de usuário do indicador, se existir"
                                       title="Esse campo deve ter um login de 3 a 100 caracteres ou ficar vazio"
                                       class="form-control" autocomplete="off">
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
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar aluno
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de aluno -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de aluno</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover <span id="nome-aluno"></span>?</h3>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                        <a href="#" class="btn btn-danger danger">Sim</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal-email" tabindex="-1" role="dialog"
             aria-labelledby="modal-email" aria-hidden="true">
             <form method="POST" action="rotinas/gerenciar_email.php" id="form-email" name="form-email">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Email para alunos</h4>
                        </div>
                        <div class="modal-body">
                            <label for="title">Título do e-mail :</label>
                            <input type='text' class="form-control" 
                                name="title" id="title" placeholder="Título" required>

                            <br>
                            <label for="conteudo">Conteúdo do e-mail :</label>
                            <br>
                            <textarea name="conteudo" id="conteudo" 
                            class="form-control" 
                            cols="100"
                            rows="10"
                            placeholder="Mensagem"
                            required></textarea>
                        </div>
                        <input type="hidden" id="sendType" name="sendType" value="todos">
                        <input type="hidden" id="url-send" name="url-send">
                        <input type="hidden" id="vetGet" name="vetGet" value=<?= json_encode($_GET) ?>>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" >Enviar</button>
                        </div>
                    </div>
                </div>
            </form>
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
