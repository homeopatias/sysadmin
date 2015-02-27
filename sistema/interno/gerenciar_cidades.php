<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Cidades - Homeopatias.com</title>
        <!-- plugin de JQuery para tornar tabelas ordenaveis -->
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <!-- polyfill para funcionalidades do HTML5 -->
        <script src="./webshim-1.14.5/polyfiller.js"></script>
        <script>
            // usamos um polyfill para que os campos de data funcionem mesmo
            // em navegadores que não implementem essas funcionalidades (você sabe quais)

            webshims.activeLang("pt-BR");
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date', replaceUI: true});
            webshims.polyfill('forms forms-ext');

            var podeMudarPagina = true;
            $(document).ready(function(){

                // Atualiza os campos de pagamento de nova cidade assim que carregar
                // a pagina
                atualizaPagamentosNova();

                // permite redimensionar as colunas da tabela
                $("#cidades").colResizable({
                    liveDrag: true
                    // aqui não determinamos tamanho mínimo, pois a tabela contém muitos campos
                });

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

                // torna a tabela ordenavel pelas colunas
                $("#cidades").tablesorter({ headers: {
                    4 : { sorter: "datetime" },
                    8 : { sorter: false },
                    9 : { sorter: false },
                    10 : { sorter: false }
                }});
                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-cidade').text(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                });
                // passa os dados da cidade para o modal para a edição
                $("#modal-edita-cidade").on('show.bs.modal', function(e) {
                    $(this).find('#idCidade').val(
                        $(e.relatedTarget).data('id')
                    );
                    $(this).find('#nome').val(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                    $(this).find('#UF').val(
                        $(e.relatedTarget).parent().siblings('.UF').text()
                    );
                    $(this).find('#ano').val(
                        $(e.relatedTarget).parent().siblings('.ano').text()
                    );
                    $(this).find('#local').val(
                        $(e.relatedTarget).parent().siblings('.local').text()
                    );

                    // carregamos os dados dos coordenadores do ano dessa cidade
                    $.post("rotinas/coordenador/lista_coordenadores_json.php", {
                        'ano': $("#ano").val()
                    }, function(data){
                        $("#coord").find('option').remove().end();
                        $("#coord").append(
                                '<option value="' +
                                $(e.relatedTarget).parent().siblings('.nome-coord').data('id-coord') + '">' +
                                $(e.relatedTarget).parent().siblings('.nome-coord').data('nome-coord') +
                                '</option>'
                        );
                        data = JSON.parse(data);
                        data.forEach(function(coordenador){
                            var coord = JSON.parse(coordenador);
                            $("#coord").append(
                                '<option value="' + coord['id'] + '">' +
                                coord['nome'] + '</option>'
                            );
                        });
                    });
                    $(this).find("#limite").val(
                        $(e.relatedTarget).parent().siblings('.limite').data('limite')
                    );
                    $(this).find('#nomeEmpresa').val(
                        $(e.relatedTarget).data('empresa')
                    );
                    $(this).find('#cnpjEmpresa').val(
                        $(e.relatedTarget).data('cnpj')
                    );
                    $(this).find('#custoCurso').val(
                        $(e.relatedTarget).data('custo')
                    );

                    if($(e.relatedTarget).data('cadastro-ativo') == 1){
                        $(this).find('#cadastroAtivo').prop("checked", true);
                    }else{
                        $(this).find('#cadastroAtivo').prop("checked", false);
                    }

                    var extensao = $("#modal-edita-cidade #preco-extensao");
                    var pos = $("#modal-edita-cidade #preco-pos");

                    if($(e.relatedTarget).data('tipo-curso') == "extensão"){
                        extensao.show();
                        pos.hide(); 
                    }else{
                        if($(e.relatedTarget).data('tipo-curso') == "pós"){
                            extensao.hide();
                            pos.show();
                        }else{
                            extensao.show();
                            pos.show();
                        }
                    }

                    $(this).find('#inscricao-ext').val(
                        $(e.relatedTarget).data('insc-ext')
                    );
                    $(this).find('#inscricao-pos').val(
                        $(e.relatedTarget).data('insc-pos')
                    );
                    $(this).find('#parcela-ext').val(
                        $(e.relatedTarget).data('parc-ext')
                    );
                    $(this).find('#parcela-pos').val(
                        $(e.relatedTarget).data('parc-pos')
                    );

                    if( $(this).find('#cadastroAtivo').prop("checked")){
                        $(this).find('#cadastroPermitido').show();
                    }else{
                        $(this).find('#cadastroPermitido').hide();
                    }
                });
                /*
                    Sempre que o tipo de curso da cidade for alterado, altera também
                    os inputs de preço
                */
                $('#modal-edita-cidade #cadastroAtivo').change(function(){
                    if( $(this).prop("checked")){
                        $('#modal-edita-cidade #cadastroPermitido').show();
                    }else{
                        $('#modal-edita-cidade #cadastroPermitido').hide();
                    }
                });
                        
                    
                // caso o tipo de curso seja trocado, muda os campos de preços
                // em inserir nova e em editar uma cidade
                $("#modal-nova-cidade #tipo-curso").change(function(){
                    atualizaPagamentosNova();
                });

                // Muda os campos de pagamentos de nova cidade de acordo com a modalidade
                $("#modal-nova-cidade #modalidade-curso").change(function(){
                    atualizaPagamentosNova();
                });

                $("#modal-edita-cidade #tipo-curso").change(function(){
                    var extensao  = $("#modal-edita-cidade #preco-extensao");
                    var pos       = $("#modal-edita-cidade #preco-pos");
                    var instituto = $("#modal-edita-cidade #preco-instituto");

                    //var inscricao_ext = $("#modal-edita-cidade #inscricao-ext");
                    //var inscricao_pos = $("#modal-edita-cidade #inscricao-pos");

                    //var parcela_ext = $("#modal-edita-cidade #parcela-ext");
                    //var parcela_pos = $("#modal-edita-cidade #parcela-pos");

                    if( $(this).val() == "extensão" ){
                        extensao.show();
                        pos.hide();
                        instituto.hide(); 

                        /*inscricao_ext.prop("required",true); 
                        inscricao_pos.prop("required",true); 
                        parcela_ext.prop("required",true);
                        parcela_pos.prop("required",true);*/

                    }else{
                        if($(this).val() == "pós"){
                            extensao.hide();
                            pos.show();
                            /*inscricao_ext.prop("required",false); 
                            inscricao_pos.prop("required",true); 
                            parcela_ext.prop("required",false);
                            parcela_pos.prop("required",true);*/
                        }
                        else if($(this).val() == "instituto"){
                            extensao.show();
                            pos.show();
                            /*inscricao_ext.prop("required",true); 
                            inscricao_pos.prop("required",true); 
                            parcela_ext.prop("required",true);
                            parcela_pos.prop("required",true);*/
                        }
                        else{

                        }
                    }
                });

                // seta por padrão os preços de pós ocultos
                $("#modal-nova-cidade #preco-pos").hide();

                // seta por padrão os preços de pós ocultos
                $("#modal-nova-cidade #preco-pos").hide();


                // fazemos com que caso a entrada de ano da cidade seja mudada, os coordenadores
                // listados também mudem, assim só ficam visíveis os coordenadores que não
                // coordenam nenhuma cidade no ano escolhido
                $("#ano-nova").change(function(){
                    $.post("rotinas/coordenador/lista_coordenadores_json.php", {
                        'ano': $("#ano-nova").val()
                    }, function(data){
                        $("#coord-nova").find('option').remove().end();
                        data = JSON.parse(data);
                        data.forEach(function(coordenador){
                            var coord = JSON.parse(coordenador);
                            $("#coord-nova").append(
                                '<option value="' + coord['id'] + '">' +
                                coord['nome'] + '</option>'
                            );
                        });
                    });
                });

                // repetimos o processo para edição
                $("#ano").change(function(){
                    $.post("rotinas/coordenador/lista_coordenadores_json.php", {
                        'ano': $("#ano").val()
                    }, function(data){
                        $("#coord").find('option').remove().end();
                        data = JSON.parse(data);
                        data.forEach(function(coordenador){
                            var coord = JSON.parse(coordenador);
                            $("#coord").append(
                                '<option value="' + coord['id'] + '">' +
                                coord['nome'] + '</option>'
                            );
                        });
                    });

                    $(this).find('#custoCurso').val(
                        $(e.relatedTarget).data('custo')
                    );
                });

                // esconde inputs de busca

                $("#filtro-nome").hide();
                $("#filtro-uf").hide();
                $("#filtro-ano").hide();
                $("#filtro-coordenador").hide();
                $("#filtro-local").hide();
                $("#div-data-max").hide();
                $("#ipp").hide();   


                // alterna campos de texto com campos de input
                $("#label-nome").click(function(){
                    $(this).hide();
                    $("#filtro-nome").show(300);
                    $("#filtro-nome").focus();
                });

                $("#label-uf").click(function(){
                    $(this).hide();
                    $("#filtro-uf").show(300);
                    $("#filtro-uf").focus();
                });

                $("#label-ano").click(function(){
                    $(this).hide();
                    $("#filtro-ano").show(300);
                    $("#filtro-ano").focus();
                });                

                $("#label-coordenador").click(function(){
                    $(this).hide();
                    $("#filtro-coordenador").show(300);
                    $("#filtro-coordenador").focus();
                });                

                $("#label-local").click(function(){
                    $(this).hide();
                    $("#filtro-local").show(300);
                    $("#filtro-local").focus();
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
                    $("#filtro-nome").val("");
                    $("#filtro-uf").val("");
                    $("#filtro-ano").val("");
                    $("#filtro-coordenador").val("");
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

                //remove inputs em branco do form antes de enviar
                $("#form-filtro").submit(function(){

                    $(':input', this).each(function() {
                         this.disabled = !($(this).val());
                    });

                    if($('#filtro-ano').val() == 0) {
                        $('#filtro-ano')[0].disabled = true;
                    }
                    if($('#filtro-uf').val() == 0) {
                        $('#filtro-uf')[0].disabled = true;
                    }
                    if($('#filtro-coordenador').val() == 0) {
                        $('#filtro-coordenador')[0].disabled = true;
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

                var anos = new Array();
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
                $db      = "homeopatias";
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                $textoQuery  = "SELECT ano FROM Cidade ORDER BY ano DESC";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                // variável para garantir que inicializaremos o vetor para cada
                // ano sempre que estivermos utilizando-o pela primeira vez
                $anos = [];

                while ($linha = $query->fetch()){
                    $ano  = "\"".htmlspecialchars($linha["ano"])."\"";
                    if(!in_array($linha["ano"], $anos)){
                        $anos[] = $linha["ano"];
                ?>
                    anos.push(<?= $linha["ano"] ?>);
                <?php
                    }
                }
                ?>

                var coordenadores = new Array();
                <?php
                // Realizaremos uma busca para encontrar os professores no sistema
                $textoQuery  = "SELECT A.idAdmin,U.nome
                                FROM Usuario U, Administrador A WHERE A.idUsuario = U.id AND 
                                A.nivel = \"coordenador\" ";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                while ($linha = $query->fetch()){
                    $id   = "\"".htmlspecialchars($linha["idAdmin"])."\"";
                    $nome = "\"".htmlspecialchars($linha["nome"])."\"";
            ?>

                    coordenadores.push({
                        id:   <?= $id ?>,
                        nome: <?= $nome ?>
                    });

            <?php 
                }

                // encerramos a conexão
                $conexão = null ;
            ?>

            // Preenche o select de coordenadores

            // se há coordenador filtrado, seleciona ele
            // remove os sinais de + que são passados e transforma em uma entidade html
            var selecionado = <?= isset($_GET["filtro-coordenador"])?
                          htmlspecialchars( str_replace("+","",$_GET["filtro-coordenador"]) ) : "0"?>;


            // A primeira opção indica nenhum coordenador
            var opcao = '<option value= 0>Todos</option>';
                        $("#filtro-coordenador").append(opcao);
            coordenadores.forEach(function(coord){
                if(selecionado != "0" && selecionado == coord.id){
                    var opcao = '<option value=" '+ coord.id +' " selected = selected>'
                        + coord.nome + '</option>';
                    $("#filtro-coordenador").append(opcao);
                }
                else{
                    var opcao = '<option value=" '+ coord.id +' ">'
                        + coord.nome + '</option>';
                    $("#filtro-coordenador").append(opcao);
                }
            });

            // Preenche o select de anos

            // se há ano filtrado, seleciona ele
            // remove os sinais de + que são passados e transforma em uma entidade html
            var selecionado = <?= isset($_GET["filtro-ano"])?
                          htmlspecialchars( str_replace("+","",$_GET["filtro-ano"]) ) : "0"?>;


            // A primeira opção indica nenhum ano
            var opcao = '<option value= 0>Todos</option>';
                        $("#filtro-ano").append(opcao);
            anos.forEach(function(ano){
                if(selecionado != "0" && selecionado == ano.id){
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
                    else {
                        direcao = 2;
                    }
                    $("#numeroTableHeader").val(position);
                    $("#cimaOuBaixo").val(direcao);

                    // Envia o formulário para atualizar a tabela com os filtros desejados
                    atualizaPagina();
                });

            checaTamanhoTela();

            // chamamos a função change do dropdown de ano de nova cidade
            // para carregar os coordenadores possíveis
            $("#ano-nova").change();
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

        // atualiza pagamentos do formulario de nova cidade
        function atualizaPagamentosNova(){
            var tipo_curso = $("#modal-nova-cidade #tipo-curso");
            var modalidade = $("#modal-nova-cidade #modalidade-curso");

            var extensao  = $("#modal-nova-cidade #preco-extensao");
            var pos       = $("#modal-nova-cidade #preco-pos");
            var instituto = $("#modal-nova-cidade #preco-instituto");

            var extensaoRegular = $("#modal-nova-cidade #preco-extensao #cursoRegular");
            var extensaoIntensivo = $("#modal-nova-cidade #preco-extensao #cursoIntensivo");
            var posRegular = $("#modal-nova-cidade #preco-pos #cursoRegular");
            var posIntensivo = $("#modal-nova-cidade #preco-pos #cursoIntensivo");
            var institutoRegular = $("#modal-nova-cidade #preco-instituto #cursoRegular");
            var institutoIntensivo = $("#modal-nova-cidade #preco-instituto #cursoIntensivo");

            var inscricao_ext_reg  = $("#modal-nova-cidade #inscricao-nova-ext-reg");
            var inscricao_pos_reg  = $("#modal-nova-cidade #inscricao-nova-pos-reg");
            var inscricao_ins_reg = $("#modal-nova-cidade #inscricao-nova-instituto-reg");
            var inscricao_ext_int  = $("#modal-nova-cidade #inscricao-nova-ext-int");
            var inscricao_pos_int  = $("#modal-nova-cidade #inscricao-nova-pos-int");
            var inscricao_ins_int = $("#modal-nova-cidade #inscricao-nova-instituto-int");

            var parcela_ext_reg = $("#modal-nova-cidade #parcela-nova-ext-reg");
            var parcela_pos_reg = $("#modal-nova-cidade #parcela-nova-pos-reg");
            var parcela_ins_reg = $("#modal-nova-cidade #parcela-nova-instituto-reg");
            var parcela_ext_int = $("#modal-nova-cidade #parcela-nova-ext-int");
            var parcela_pos_int = $("#modal-nova-cidade #parcela-nova-pos-int");
            var parcela_ins_int = $("#modal-nova-cidade #parcela-nova-instituto-int");

            if( tipo_curso.val() == "extensão" ){
                extensao.show();
                pos.hide();
                instituto.hide(); 
                if(modalidade.val() == "regular"){
                    extensaoRegular.show();
                    extensaoIntensivo.hide();


                    inscricao_ext_reg.prop("required",true);
                    parcela_ext_reg.prop("required",true);
                    inscricao_ext_int.prop("required",false);
                    parcela_ext_int.prop("required",false);

                }else if(modalidade.val() == "intensivo"){
                    extensaoRegular.hide();
                    extensaoIntensivo.show();

                    inscricao_ext_reg.prop("required",false);
                    parcela_ext_reg.prop("required",false);
                    inscricao_ext_int.prop("required",true);
                    parcela_ext_int.prop("required",true);
                }else{
                    extensaoRegular.show();
                    extensaoIntensivo.show();

                    inscricao_ext_reg.prop("required",true);
                    parcela_ext_reg.prop("required",true);
                    inscricao_ext_int.prop("required",true);
                    parcela_ext_int.prop("required",true);
                }
                // remove required dos outros tipos de curso
                inscricao_pos_reg.prop("required",false);
                inscricao_pos_int.prop("required",false); 
                inscricao_ins_int.prop("required",false);
                inscricao_ins_reg.prop("required",false);
                parcela_pos_reg.prop("required",false);
                parcela_pos_int.prop("required",false);
                parcela_ins_int.prop("required",false);
                parcela_ins_reg.prop("required",false);

            }else{
                if(tipo_curso.val() == "pós"){
                    extensao.hide();
                    pos.show();
                    instituto.hide();

                    if(modalidade.val() == "regular"){
                        posRegular.show();
                        posIntensivo.hide();

                        inscricao_pos_reg.prop("required",true);
                        parcela_pos_reg.prop("required",true);
                        inscricao_pos_int.prop("required",true);
                        parcela_pos_int.prop("required",true);

                    }else if(modalidade.val() == "intensivo"){
                        posRegular.hide();
                        posIntensivo.show();

                        inscricao_pos_reg.prop("required",false);
                        parcela_pos_reg.prop("required",false);
                        inscricao_pos_int.prop("required",true);
                        parcela_pos_int.prop("required",true);
                    }else{
                        posRegular.show();
                        posIntensivo.show();

                        inscricao_pos_reg.prop("required",true);
                        parcela_pos_reg.prop("required",true);
                        inscricao_pos_int.prop("required",true);
                        parcela_pos_int.prop("required",true);
                    }

                    // remove required dos outros tipos de curso
                    inscricao_ext_reg.prop("required",false);
                    inscricao_ext_int.prop("required",false); 
                    inscricao_ins_int.prop("required",false);
                    inscricao_ins_reg.prop("required",false);
                    parcela_ext_reg.prop("required",false);
                    parcela_ext_int.prop("required",false);
                    parcela_ins_reg.prop("required",false);
                    parcela_ins_int.prop("required",false);

                }else if(tipo_curso.val() == "instituto"){
                    extensao.hide();
                    pos.hide();
                    instituto.show();

                    if(modalidade.val() == "regular"){
                        institutoRegular.show();
                        institutoIntensivo.hide();

                        inscricao_ins_reg.prop("required",true);
                        parcela_ins_reg.prop("required",true);
                        inscricao_ins_int.prop("required",false);
                        parcela_ins_int.prop("required",false);

                    }else if(modalidade.val() == "intensivo"){
                        institutoRegular.hide();
                        institutoIntensivo.show();

                        inscricao_ins_reg.prop("required",false);
                        parcela_ins_reg.prop("required",false);
                        inscricao_ins_int.prop("required",true);
                        parcela_ins_int.prop("required",true);
                    }else{
                        institutoRegular.show();
                        institutoIntensivo.show();

                        inscricao_ins_reg.prop("required",true);
                        parcela_ins_reg.prop("required",true);
                        inscricao_ins_int.prop("required",true);
                        parcela_ins_int.prop("required",true);
                    }

                    // remove required dos outros tipos de curso
                    inscricao_ext_reg.prop("required",false);
                    inscricao_ext_int.prop("required",false); 
                    inscricao_pos_reg.prop("required",false);
                    inscricao_pos_int.prop("required",false);
                    parcela_pos_reg.prop("required",false);
                    parcela_pos_int.prop("required",false);
                    parcela_ext_reg.prop("required",false);
                    parcela_ext_int.prop("required",false);

                }else{
                    extensao.show();
                    pos.show();
                    instituto.show();

                    if(modalidade.val() == "regular"){
                        extensaoRegular.show();
                        extensaoIntensivo.hide();
                        posRegular.show();
                        posIntensivo.hide();
                        institutoRegular.show();
                        institutoIntensivo.hide();

                        inscricao_ext_reg.prop("required",true);
                        inscricao_ext_int.prop("required",false);
                        inscricao_pos_reg.prop("required",true); 
                        inscricao_pos_int.prop("required",false);
                        inscricao_ins_reg.prop("required",true);
                        inscricao_ins_int.prop("required",false);
                        parcela_ext_reg.prop("required",true);
                        parcela_ext_int.prop("required",false);
                        parcela_pos_reg.prop("required",true);
                        parcela_pos_int.prop("required",false);
                        parcela_ins_reg.prop("required",true);
                        parcela_ins_int.prop("required",false);

                    }else if(modalidade.val() == "intensivo"){
                        extensaoRegular.hide();
                        extensaoIntensivo.show();
                        posRegular.show();
                        posIntensivo.hide();
                        institutoRegular.hide();
                        institutoIntensivo.show();

                        inscricao_ext_reg.prop("required",false);
                        inscricao_ext_int.prop("required",true);
                        inscricao_pos_reg.prop("required",false); 
                        inscricao_pos_int.prop("required",true);
                        inscricao_ins_reg.prop("required",false);
                        inscricao_ins_int.prop("required",true);
                        parcela_ext_reg.prop("required",false);
                        parcela_ext_int.prop("required",true);
                        parcela_pos_reg.prop("required",false);
                        parcela_pos_int.prop("required",true);
                        parcela_ins_reg.prop("required",false);
                        parcela_ins_int.prop("required",true);
                    }else{
                        extensaoRegular.show();
                        extensaoIntensivo.show();
                        posRegular.show();
                        posIntensivo.show();
                        institutoRegular.show();
                        institutoIntensivo.show();

                        inscricao_ext_reg.prop("required",true);
                        inscricao_ext_int.prop("required",true);
                        inscricao_pos_reg.prop("required",true); 
                        inscricao_pos_int.prop("required",true);
                        inscricao_ins_reg.prop("required",true);
                        inscricao_ins_int.prop("required",true);
                        parcela_ext_reg.prop("required",true);
                        parcela_ext_int.prop("required",true);
                        parcela_pos_reg.prop("required",true);
                        parcela_pos_int.prop("required",true);
                        parcela_ins_reg.prop("required",true);
                        parcela_ins_int.prop("required",true);
                    }
                }
            }
        }
        // atualiza pagamentos do formulario de editar cidade
        function atualizaPagamentosEdita(){
            var tipo_curso = $("#modal-edita-cidade #tipo-curso");
            var modalidade = $("#modal-edita-cidade #modalidade-curso");

            var extensao  = $("#modal-edita-cidade #preco-extensao");
            var pos       = $("#modal-edita-cidade #preco-pos");
            var instituto = $("#modal-edita-cidade #preco-instituto");

            var extensaoRegular = $("#modal-edita-cidade #preco-extensao #cursoRegular");
            var extensaoIntensivo = $("#modal-edita-cidade #preco-extensao #cursoIntensivo");
            var posRegular = $("#modal-edita-cidade #preco-pos #cursoRegular");
            var posIntensivo = $("#modal-edita-cidade #preco-pos #cursoIntensivo");
            var institutoRegular = $("#modal-edita-cidade #preco-instituto #cursoRegular");
            var institutoIntensivo = $("#modal-edita-cidade #preco-instituto #cursoIntensivo");

            var inscricao_ext_reg  = $("#modal-edita-cidade #inscricao-edita-ext-reg");
            var inscricao_pos_reg  = $("#modal-edita-cidade #inscricao-edita-pos-reg");
            var inscricao_ins_reg = $("#modal-edita-cidade #inscricao-edita-instituto-reg");
            var inscricao_ext_int  = $("#modal-edita-cidade #inscricao-edita-ext-int");
            var inscricao_pos_int  = $("#modal-edita-cidade #inscricao-edita-pos-int");
            var inscricao_ins_int = $("#modal-edita-cidade #inscricao-edita-instituto-int");

            var parcela_ext_reg = $("#modal-edita-cidade #parcela-edita-ext-reg");
            var parcela_pos_reg = $("#modal-edita-cidade #parcela-edita-pos-reg");
            var parcela_ins_reg = $("#modal-edita-cidade #parcela-edita-instituto-reg");
            var parcela_ext_int = $("#modal-edita-cidade #parcela-edita-ext-int");
            var parcela_pos_int = $("#modal-edita-cidade #parcela-edita-pos-int");
            var parcela_ins_int = $("#modal-edita-cidade #parcela-edita-instituto-int");

            if( tipo_curso.val() == "extensão" ){
                extensao.show();
                pos.hide();
                instituto.hide(); 
                if(modalidade.val() == "regular"){
                    extensaoRegular.show();
                    extensaoIntensivo.hide();


                    inscricao_ext_reg.prop("required",true);
                    parcela_ext_reg.prop("required",true);
                    inscricao_ext_int.prop("required",false);
                    parcela_ext_int.prop("required",false);

                }else if(modalidade.val() == "intensivo"){
                    extensaoRegular.hide();
                    extensaoIntensivo.show();

                    inscricao_ext_reg.prop("required",false);
                    parcela_ext_reg.prop("required",false);
                    inscricao_ext_int.prop("required",true);
                    parcela_ext_int.prop("required",true);
                }else{
                    extensaoRegular.show();
                    extensaoIntensivo.show();

                    inscricao_ext_reg.prop("required",true);
                    parcela_ext_reg.prop("required",true);
                    inscricao_ext_int.prop("required",true);
                    parcela_ext_int.prop("required",true);
                }
                // remove required dos outros tipos de curso
                inscricao_pos_reg.prop("required",false);
                inscricao_pos_int.prop("required",false); 
                inscricao_ins_int.prop("required",false);
                inscricao_ins_reg.prop("required",false);
                parcela_pos_reg.prop("required",false);
                parcela_pos_int.prop("required",false);
                parcela_ins_int.prop("required",false);
                parcela_ins_reg.prop("required",false);

            }else{
                if(tipo_curso.val() == "pós"){
                    extensao.hide();
                    pos.show();
                    instituto.hide();

                    if(modalidade.val() == "regular"){
                        posRegular.show();
                        posIntensivo.hide();

                        inscricao_pos_reg.prop("required",true);
                        parcela_pos_reg.prop("required",true);
                        inscricao_pos_int.prop("required",true);
                        parcela_pos_int.prop("required",true);

                    }else if(modalidade.val() == "intensivo"){
                        posRegular.hide();
                        posIntensivo.show();

                        inscricao_pos_reg.prop("required",false);
                        parcela_pos_reg.prop("required",false);
                        inscricao_pos_int.prop("required",true);
                        parcela_pos_int.prop("required",true);
                    }else{
                        posRegular.show();
                        posIntensivo.show();

                        inscricao_pos_reg.prop("required",true);
                        parcela_pos_reg.prop("required",true);
                        inscricao_pos_int.prop("required",true);
                        parcela_pos_int.prop("required",true);
                    }

                    // remove required dos outros tipos de curso
                    inscricao_ext_reg.prop("required",false);
                    inscricao_ext_int.prop("required",false); 
                    inscricao_ins_int.prop("required",false);
                    inscricao_ins_reg.prop("required",false);
                    parcela_ext_reg.prop("required",false);
                    parcela_ext_int.prop("required",false);
                    parcela_ins_reg.prop("required",false);
                    parcela_ins_int.prop("required",false);

                }else if(tipo_curso.val() == "instituto"){
                    extensao.hide();
                    pos.hide();
                    instituto.show();

                    if(modalidade.val() == "regular"){
                        institutoRegular.show();
                        institutoIntensivo.hide();

                        inscricao_ins_reg.prop("required",true);
                        parcela_ins_reg.prop("required",true);
                        inscricao_ins_int.prop("required",false);
                        parcela_ins_int.prop("required",false);

                    }else if(modalidade.val() == "intensivo"){
                        institutoRegular.hide();
                        institutoIntensivo.show();

                        inscricao_ins_reg.prop("required",false);
                        parcela_ins_reg.prop("required",false);
                        inscricao_ins_int.prop("required",true);
                        parcela_ins_int.prop("required",true);
                    }else{
                        institutoRegular.show();
                        institutoIntensivo.show();

                        inscricao_ins_reg.prop("required",true);
                        parcela_ins_reg.prop("required",true);
                        inscricao_ins_int.prop("required",true);
                        parcela_ins_int.prop("required",true);
                    }

                    // remove required dos outros tipos de curso
                    inscricao_ext_reg.prop("required",false);
                    inscricao_ext_int.prop("required",false); 
                    inscricao_pos_reg.prop("required",false);
                    inscricao_pos_int.prop("required",false);
                    parcela_pos_reg.prop("required",false);
                    parcela_pos_int.prop("required",false);
                    parcela_ext_reg.prop("required",false);
                    parcela_ext_int.prop("required",false);

                }else{
                    extensao.show();
                    pos.show();
                    instituto.show();

                    if(modalidade.val() == "regular"){
                        extensaoRegular.show();
                        extensaoIntensivo.hide();
                        posRegular.show();
                        posIntensivo.hide();
                        institutoRegular.show();
                        institutoIntensivo.hide();

                        inscricao_ext_reg.prop("required",true);
                        inscricao_ext_int.prop("required",false);
                        inscricao_pos_reg.prop("required",true); 
                        inscricao_pos_int.prop("required",false);
                        inscricao_ins_reg.prop("required",true);
                        inscricao_ins_int.prop("required",false);
                        parcela_ext_reg.prop("required",true);
                        parcela_ext_int.prop("required",false);
                        parcela_pos_reg.prop("required",true);
                        parcela_pos_int.prop("required",false);
                        parcela_ins_reg.prop("required",true);
                        parcela_ins_int.prop("required",false);

                    }else if(modalidade.val() == "intensivo"){
                        extensaoRegular.hide();
                        extensaoIntensivo.show();
                        posRegular.show();
                        posIntensivo.hide();
                        institutoRegular.hide();
                        institutoIntensivo.show();

                        inscricao_ext_reg.prop("required",false);
                        inscricao_ext_int.prop("required",true);
                        inscricao_pos_reg.prop("required",false); 
                        inscricao_pos_int.prop("required",true);
                        inscricao_ins_reg.prop("required",false);
                        inscricao_ins_int.prop("required",true);
                        parcela_ext_reg.prop("required",false);
                        parcela_ext_int.prop("required",true);
                        parcela_pos_reg.prop("required",false);
                        parcela_pos_int.prop("required",true);
                        parcela_ins_reg.prop("required",false);
                        parcela_ins_int.prop("required",true);
                    }else{
                        extensaoRegular.show();
                        extensaoIntensivo.show();
                        posRegular.show();
                        posIntensivo.show();
                        institutoRegular.show();
                        institutoIntensivo.show();

                        inscricao_ext_reg.prop("required",true);
                        inscricao_ext_int.prop("required",true);
                        inscricao_pos_reg.prop("required",true); 
                        inscricao_pos_int.prop("required",true);
                        inscricao_ins_reg.prop("required",true);
                        inscricao_ins_int.prop("required",true);
                        parcela_ext_reg.prop("required",true);
                        parcela_ext_int.prop("required",true);
                        parcela_pos_reg.prop("required",true);
                        parcela_pos_int.prop("required",true);
                        parcela_ins_reg.prop("required",true);
                        parcela_ins_int.prop("required",true);
                    }
                }
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

            // mensagem a ser exibida acima da listagem de cidades, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }

            // exibe cidades apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
               2 & unserialize($_SESSION["usuario"])->getPermissoes() ){

                // se o usuário chegou até aqui através de um formulário, registra a nova
                // cidade no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $nome        = $_POST["nome"];
                    $UF          = $_POST["UF"];
                    $ano         = $_POST["ano-nova"];
                    $local       = $_POST["local"];
                    $idCoord     = $_POST["coord"];
                    $limite      = $_POST["limite"];
                    $nomeEmpresa = "disabled";
                    $cnpjEmpresa = "00.000.000/0000-00";
                    $custoCurso  = 0;
                    $tipoCurso   = $_POST["tipo-curso"];
                    $modalidadeCurso   = $_POST["modalidade-curso"];
                    $InscExtReg        = $_POST["inscricao-ext-reg"];
                    $InscExtInt        = $_POST["inscricao-ext-int"];
                    $InscPosReg        = $_POST["inscricao-pos-reg"];
                    $InscPosInt        = $_POST["inscricao-pos-int"];
                    $InscInsReg        = $_POST["inscricao-instituto-reg"];
                    $InscInsInt        = $_POST["inscricao-instituto-int"];
                    
                    $ParcExtReg     = $_POST["parcela-ext-reg"];
                    $ParcExtInt     = $_POST["parcela-ext-int"];
                    $ParcPosReg     = $_POST["parcela-pos-reg"];
                    $ParcPosInt     = $_POST["parcela-pos-int"];
                    $ParcInsReg     = $_POST["parcela-instituto-reg"];
                    $ParcInsInt     = $_POST["parcela-instituto-int"];


                    if($tipoCurso == "extensao"){
                        $InscPosReg   =  0;
                        $InscPosInt   =  0;
                        $InscInsReg   =  0;
                        $InscInsInt   =  0;
                        $ParcPosReg   =  0;
                        $ParcPosInt   =  0;
                        $ParcInsReg   =  0;
                        $ParcInsInt   =  0;
                        if($modalidadeCurso == "regular"){
                            $InscExtInt   = 0;
                            $ParcExtInt   = 0;
                        }else if($modalidadeCurso == "regular"){
                            $InscExtReg   = 0;
                            $ParcExtReg   = 0;
                        }
                    }else if($tipoCurso == "pos"){
                        $InscExtReg   =  0;
                        $InscExtInt   =  0;
                        $InscInsReg   =  0;
                        $InscInsInt   =  0;
                        $ParcExtReg   =  0;
                        $ParcExtInt   =  0;
                        $ParcInsReg   =  0;
                        $ParcInsInt   =  0;
                        if($modalidadeCurso == "regular"){
                            $InscPosInt   = 0;
                            $ParcPosInt   = 0;
                        }else if($modalidadeCurso == "regular"){
                            $InscPosReg   = 0;
                            $ParcPosReg   = 0;
                        }
                    }else if($tipoCurso == "instituto"){
                        $InscExtReg   =  0;
                        $InscExtInt   =  0;
                        $InscPosReg   =  0;
                        $InscPosInt   =  0;
                        $ParcExtReg   =  0;
                        $ParcExtInt   =  0;
                        $ParcPosReg   =  0;
                        $ParcPosInt   =  0;
                        if($modalidadeCurso == "regular"){
                            $InscInsInt   = 0;
                            $ParcInsInt   = 0;
                        }else if($modalidadeCurso == "regular"){
                            $InscInsReg   = 0;
                            $ParcInsReg   = 0;
                        }
                    }

                    $nomeValido      = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                                       mb_strlen($nome, 'UTF-8') <= 100;
                    $UfValido        = isset($UF) && mb_strlen($UF, 'UTF-8') === 2;
                    $anoValido       = isset($ano) && intval($ano) >= date("Y") && intval($ano) < date("Y") + 3;
                    $localValido     = isset($local) && mb_strlen($local, 'UTF-8') >= 3 &&
                                       mb_strlen($local, 'UTF-8') <= 200;
                    $modalidadeCursoValida = isset($modalidadeCurso) && ($modalidadeCurso == "regular"
                                        || $modalidadeCurso == "intensivo" || 
                                        $modalidadeCurso == "ambos");
                    $idCoordValido   = isset($idCoord) && preg_match("/^[0-9]*$/", $idCoord);
                    $limiteValido    = isset($limite) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $limite);
                    $empresaValida   = isset($nomeEmpresa) && mb_strlen($nomeEmpresa, 'UTF-8') <= 100 &&
                                       mb_strlen($nomeEmpresa, 'UTF-8') >= 3;
                    $cnpjValido      = isset($cnpjEmpresa) &&
                                       preg_match("/^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/",
                                       $cnpjEmpresa);
                    $custoCursoValido   = isset($custoCurso) && preg_match("/^[0-9]*\.?[0-9]+$/",
                                                                 $custoCurso);

                    $tipoCursoValido = isset($tipoCurso) && 
                                        (
                                            $tipoCurso == "extensao" ||
                                            $tipoCurso == "pos" ||
                                            $tipoCurso == "instituto" ||
                                            $tipoCurso == "todos"
                                        );


                    if($cnpjValido){
                        // checamos se os dígitos verificadores do cnpj conferem
                        $cnpjChecar = str_replace(".","",$cnpjEmpresa);
                        $cnpjChecar = str_replace("-","",$cnpjChecar);
                        $cnpjChecar = str_replace("/","",$cnpjChecar);
                        $cnpjChecar = str_split($cnpjChecar);
                        $somaChecagem = 0;
                        for($i = 13; $i >= 2; $i = $i - 1){
                            $somaChecagem += (int)($cnpjChecar[13 - $i]) * 
                                             ($i > 9 ? ($i % 9) + 1 : $i);
                        }
                        $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
                        if($digito != $cnpjChecar[12]){
                            $cnpjValido = false;
                        }else{
                            // agora checamos o segundo dígito
                            $somaChecagem = 0;
                            for($i = 14; $i >= 2; $i = $i - 1){
                                $somaChecagem += (int)($cnpjChecar[14 - $i]) *
                                                 ($i > 9 ? ($i % 9) + 1 : $i);
                            }
                            $digito = ($somaChecagem % 11) < 2 ? 0 : 11 - ($somaChecagem % 11);
                            if($digito != $cnpjChecar[13]){
                                $cnpjValido = false;
                            }
                        }
                    }

                    if($idCoordValido) {
                        // checamos se esse coordenador já coordena outra cidade nesse ano,
                        // caso coordene, esse coordenador é inválido
                        $textoQuery  = 'SELECT idCidade FROM Cidade WHERE ano = ?
                                        AND idCoordenador = ?';
                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $ano);
                        $query->bindParam(2, $idCoord);

                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        // se esse coordenador é de outra cidade no ano dado, não é válido
                        if($query->fetch()) $idCoordValido = false;
                    }

                    // checa validade dos campos de preços

                    $inscricaoExtRegValida = isset($InscExtReg) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $InscExtReg);
                    $inscricaoExtIntValida = isset($InscExtInt) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $InscExtInt);
                    $inscricaoPosRegValida = isset($InscPosReg) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $InscPosReg);
                    $inscricaoPosIntValida = isset($InscPosInt) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $InscPosInt);
                    $inscricaoInsRegValida = isset($InscInsReg) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $InscInsReg);
                    $inscricaoInsIntValida = isset($InscInsInt) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $InscInsInt);

                    $parcelaExtRegValida = isset($ParcExtReg) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $ParcExtReg);
                    $parcelaExtIntValida = isset($ParcExtInt) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $ParcExtInt);
                    $parcelaPosRegValida = isset($ParcPosReg) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $ParcPosReg);
                    $parcelaPosIntValida = isset($ParcPosInt) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $ParcPosInt);
                    $parcelaInsRegValida = isset($ParcInsReg) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $ParcInsReg);
                    $parcelaInsIntValida = isset($ParcInsInt) && 
                                             preg_match("/^[0-9]*\.?[0-9]+$/", $ParcInsInt);

                    $pagamentosValidos = $inscricaoExtRegValida && $inscricaoExtIntValida &&
                                         $inscricaoPosRegValida && $inscricaoPosIntValida &&
                                         $inscricaoInsRegValida && $inscricaoInsIntValida &&
                                         $parcelaExtRegValida   && $parcelaExtIntValida   &&
                                         $parcelaPosRegValida   && $parcelaPosIntValida   &&
                                         $parcelaInsRegValida   && $parcelaInsIntValida;

                    // se todos os dados estão válidos, a cidade é cadastrada
                                         var_dump($tipoCursoValido);
                    if($nomeValido && $UfValido && $anoValido && $localValido && $idCoordValido &&
                       $limiteValido && $empresaValida && $pagamentosValidos &&$cnpjValido && 
                       $custoCursoValido && $tipoCursoValido && $modalidadeCursoValida){

                        require_once("entidades/Cidade.php");

                        $nova = new Cidade();
                        $nova->setNome($nome);
                        $nova->setUF($UF);
                        $nova->setAno($ano);
                        $nova->setLocal($local);
                        $nova->setLimiteInscricao($limite);
                        $nova->setNomeEmpresa($nomeEmpresa);
                        $nova->setCnpjEmpresa($cnpjEmpresa);
                        $nova->setCustoCurso($custoCurso);
                        $coordExiste = $nova->setCoordenadorId($idCoord);
                        $nova->setCadastroAtivo(1);
                        $nova->setTipoCurso($tipoCurso);
                        $nova->setModalidadeCidade($modalidadeCurso);

                        $nova->setParcelaExtensaoRegular($ParcExtReg);
                        $nova->setParcelaPosRegular($ParcPosReg);
                        $nova->setParcelaExtensaoIntensivo($ParcExtInt);
                        $nova->setParcelaPosIntensivo($ParcPosInt);
                        $nova->setParcelaInstitutoRegular($ParcInsReg);
                        $nova->setParcelaInstitutoIntensivo($ParcInsInt);
                        $nova->setInscricaoExtensaoRegular($InscExtReg);
                        $nova->setInscricaoPosRegular($InscPosReg);
                        $nova->setInscricaoExtensaoIntensivo($InscExtInt);
                        $nova->setInscricaoPosIntensivo($InscPosInt);
                        $nova->setInscricaoInstitutoRegular($InscInsReg);
                        $nova->setInscricaoInstitutoIntensivo($InscInsInt); 

                        if($coordExiste){
                            $sucesso = $nova->cadastrar($host, "homeopatias", $usuario, $senhaBD);
                            if($sucesso){
                                $mensagem = "";
                            }else{
                                $mensagem = "Essa cidade já existe";
                            }
                        }else{
                            // o coordenador informado não existe
                            $mensagem = "Esse coordenador não existe no sistema";
                        }
                    }else if(!$nomeValido){
                        $mensagem = "Nome inválido!";
                    }else if(!$UfValido){
                        $mensagem = "Estado inválido!";
                    }else if(!$anoValido){
                        $mensagem = "Ano inválido!";
                    }else if(!$localValido){
                        $mensagem = "Local inválido!";
                    }else if(!$idCoordValido){
                        $mensagem = "Id de coordenador inválido!";
                    }else if(!$limiteValido){
                        $mensagem = "Data limite de inscrição inválida!";
                    }else if(!$empresaValida) {
                        $mensagem = "Nome da empresa inválida!";
                    }else if(!$cnpjValido) {
                        $mensagem = "CNPJ inválido!";
                    }else if(!$tipoCursoValido){
                        $mensagem = "Tipo de curso inválido";
                    }

                }
            
                // cria conexão com o banco
                $conexao = null;
                $host    = "localhost";
                $db      = "homeopatias";
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                $textoQuery  = "SELECT C.idCidade, C.UF, C.ano, A.idAdmin, C.nome, 
                                C.precoInscricao, C.precoParcela, C.idCoordenador, 
                                C.local, C.limiteInscricao, C.nomeEmpresa, C.cnpjEmpresa,
                                C.custoCurso, C.cadastro_ativo, C.tipo_curso, 
                                C.v_inscricao_extensao, C.v_inscricao_pos,
                                C.v_parcela_extensao, C.v_parcela_pos
                                FROM Cidade C, Administrador A WHERE C.idCoordenador 
                                = A.idAdmin AND A.nivel = \"coordenador\" ";

                // se algum filtro foi enviado, filtra os resultados da consulta
                $filtroNome = $filtroAno = $filtroUf = $filtroCoordenador = 
                $filtroLocal = $filtroDataMax = false;

                // como não há botão para submit, temos que checar se todas as variáveis
                // existem
                if(isset($_GET["filtro-nome"])     || isset($_GET["filtro-ano"])     ||
                   isset($_GET["filtro-uf"])   || isset($_GET["filtro-coordenador"])  ||
                   isset($_GET["filtro-local"]) || isset($_GET["filtro-data-max"])){
                    $filtroNome    =  htmlspecialchars($_GET["filtro-nome"]);
                    $filtroAno     =  htmlspecialchars($_GET["filtro-ano"]);
                    $filtroUf  =  htmlspecialchars($_GET["filtro-uf"]);
                    $filtroCoordenador  =  htmlspecialchars($_GET["filtro-coordenador"]);
                    $filtroLocal =  htmlspecialchars($_GET["filtro-local"]);
                    $filtroDataMax =  htmlspecialchars($_GET["filtro-data-max"]);

                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        // prepara o nome para ser colocado na query
                        $filtroNome    =  "%".$filtroNome."%";
                        $textoQuery .= "  AND C.nome LIKE :nome";
                    }
                    if(isset($filtroAno) && mb_strlen($filtroAno) > 0 &&
                        !is_nan($filtroAno) && $filtroAno != "0"){
                        $textoQuery .= "  AND C.ano = :ano";
                    }
                    if(isset($filtroUf) && mb_strlen($filtroUf) > 0 && $filtroUf != "0"){
                        $textoQuery .= " AND C.UF LIKE :uf";
                    }
                    if(isset($filtroCoordenador) && mb_strlen($filtroCoordenador) > 0 &&
                        $filtroCoordenador != "0"){
                            $textoQuery .= " AND C.idCoordenador = :coordenador";
                    }
                    if(isset($filtroLocal) && mb_strlen($filtroLocal) > 0){
                        $textoQuery .= " AND C.local = :local";
                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        $textoQuery .= " AND CAST(C.limiteInscricao AS Date) <= ";
                        $textoQuery .= "CAST(:dataMax as Date)";
                    }

                }

                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY ano DESC, nome ASC" ;
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
                                $orderBy = " ORDER BY C.nome " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY C.UF " ;
                                break;
                            case '2':
                                $orderBy = " ORDER BY C.ano " ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY C.local " ;
                                break;
                            case '4':
                                $orderBy = " ORDER BY C.limiteInscricao " ;
                                break;
                            case '5':
                                $orderBy = " ORDER BY C.idCoordenador " ;
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

                if(isset($_GET["filtro-nome"])     || isset($_GET["filtro-ano"])     ||
                   isset($_GET["filtro-uf"])   || isset($_GET["filtro-coordenador"])  ||
                   isset($_GET["filtro-local"]) || isset($_GET["filtro-data-max"])){
                    if(isset($filtroNome) && mb_strlen($filtroNome)){
                        $query->bindParam(":nome",$filtroNome);
                    }
                    if(isset($filtroAno) && mb_strlen($filtroAno) > 0 &&
                        !is_nan($filtroAno) && $filtroAno != "0"){
                        $query->bindParam(":ano",$filtroAno);
                    }
                    if(isset($filtroUf) && mb_strlen($filtroUf) > 0 && $filtroUf != "0"){
                        $query->bindParam(":uf",$filtroUf);
                    }
                    if(isset($filtroCoordenador) && mb_strlen($filtroCoordenador) > 0 &&
                        $filtroCoordenador != "0"){
                        $query->bindParam(":coordenador" , $filtroCoordenador);
                    }
                    if(isset($filtroLocal) && mb_strlen($filtroLocal) > 0){
                        $query->bindParam(":local" , $filtroLocal);
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
                        // formatando o texto do cnpj
                        $cnpjOriginal = str_split($linha["cnpjEmpresa"]);
    
                        $cnpj  = implode("", array_slice($cnpjOriginal, 0, 2)) . ".";
                        $cnpj .= implode("", array_slice($cnpjOriginal, 2, 3)) . ".";
                        $cnpj .= implode("", array_slice($cnpjOriginal, 5, 3)) . "/";
                        $cnpj .= implode("", array_slice($cnpjOriginal, 8, 4)) . "-";
                        $cnpj .= implode("", array_slice($cnpjOriginal, 12, 4));
    
                        $cnpj  = htmlspecialchars($cnpj);
    
                        // listamos os dados de cada cidade
                        $tabela .= "<tr>";
                        $tabela .= "    <td class=\"nome\">";
                        $tabela .= htmlspecialchars($linha["nome"])            ."</td>";
                        $tabela .= "    <td class=\"UF\">";
                        $tabela .= htmlspecialchars($linha["UF"])              ."</td>";
                        $tabela .= "    <td class=\"ano\">";
                        $tabela .= htmlspecialchars($linha["ano"])             ."</td>";
                        $tabela .= "    <td class=\"local\">";
                        $tabela .= htmlspecialchars($linha["local"])           ."</td>";
                        $tabela .= "    <td class=\"limite\" data-limite=\"";
    
                        $dataLimite = strtotime($linha["limiteInscricao"]);
    
                        $tabela .= date("Y-m-d", $dataLimite) ."\">";
                        $tabela .= date("d/m/Y", $dataLimite) ."</td>";
                        $tabela .= "    <td class=\"nome-coord\" data-id-coord=\"";
                        $tabela .= $linha["idCoordenador"];
                        $tabela .= "\"";
    
                        require_once("entidades/Administrador.php");
                        $coord = new Administrador("");
                        $coord->setIdAdmin($linha["idCoordenador"]);
                        $coord->recebeAdminId("localhost", "homeopatias", $usuario, $senhaBD,
                                              "coordenador");
    
                        $tabela .= "data-nome-coord=\"" . htmlspecialchars($coord->getNome());
                        $tabela .= "\">";
                        $tabela .= htmlspecialchars($coord->getNome())."</td>";
/*    
                        $tabela .= "    <td class=\"inscricao\">R$ ";
                        $tabela .= number_format(htmlspecialchars($linha["precoInscricao"]), 2, ".", " ")."</td>";
                        $tabela .= "    <td class=\"parcela\">R$ ";
                        $tabela .= number_format(htmlspecialchars($linha["precoParcela"]), 2, ".", " ")."</td>";
                        $tabela .= "    <td class=\"custo\">R$ ";
                        $tabela .= number_format(htmlspecialchars($linha["custoCurso"]), 2, ".", " ")."</td>";
    */
                        $tabela .= "    <td><a data-id=\"";
                        $tabela .= $linha['idCidade'];
                        $tabela .= "\" data-empresa=\"" . $linha['nomeEmpresa'];
                        $tabela .= "\" data-cnpj=\"". $cnpj;
                        $tabela .= "\"href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-custo=\"".$linha["custoCurso"]."\"";
                        $tabela .= " data-target=\"#modal-edita-cidade\"";
                        $tabela .= " data-tipo-curso=\"".$linha["tipo_curso"]."\"";
                        $tabela .= " data-cadastro-ativo=\"".$linha["cadastro_ativo"]."\"";
                        $tabela .= " data-insc-ext=\"".$linha["v_inscricao_extensao"]."\"";
                        $tabela .= " data-insc-pos=\"".$linha["v_inscricao_pos"]."\"";
                        $tabela .= " data-parc-ext=\"".$linha["v_parcela_extensao"]."\"";
                        $tabela .= " data-parc-pos=\"".$linha["v_parcela_pos"]."\">";
                        $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                        $tabela .= "    <td><a data-href=\"rotinas/cidade/";
                        $tabela .= "remover_cidade.php?id=";
                        $tabela .= $linha['idCidade'];
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
                    <h1>Cidades</h1>
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-nova-cidade">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Nova cidade</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_cidades.php" id="form-filtro">
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
                            </select>
                            <a id="label-uf" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-uf"]) && 
                                        mb_strlen(($_GET["filtro-uf"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-uf"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >UF
                            </a>

                                <select name="filtro-uf" id="filtro-uf" class="form-control"
                                        style="display:inline;width:120px">
                                    <option value="0" >Todos</option>
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

                            <a id="label-ano" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-ano"]) && 
                                        mb_strlen(($_GET["filtro-ano"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-ano"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Ano
                            </a>
                            <select name="filtro-ano" id="filtro-ano" class="form-control"
                                        style="display:inline;width:120px">

                            </select>

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

                            <a id="label-data-max" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-data-max"]) && 
                                        mb_strlen(($_GET["filtro-data-max"])) > 0) ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?>
                                >Data máxima para matrícula
                            </a>
                            <div id="div-data-max" style="display: inline">
                            <input type="date" name="filtro-data-max" id="filtro-data-max"
                                       placeholder="dd/mm/aaaa" class="form-control"
                                       style="display:inline;width:150px"
                                       value =<?= isset($_GET["filtro-data-max"]) ?
                                                htmlspecialchars($_GET["filtro-data-max"]) : "" ?> >
                            </div>
                            
                            
                            <a id="label-coordenador" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-coordenador"]) && 
                                        mb_strlen(($_GET["filtro-coordenador"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-coordenador"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Coordenador
                            </a>
                            <select name="filtro-coordenador" id="filtro-coordenador" 
                                        class="form-control"
                                        style="display:inline;width:175px">
                            </select>
                            
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
                            <table class="table table-bordered table-striped" id="cidades">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="150px" <?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Nome</th>
                                        <th width="60px" <?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>UF</th>
                                        <th width="60px" <?= $indexHeader == 2 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Ano</th>
                                        <th width="180px" <?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Local</th>
                                        <th width="170px" <?= $indexHeader == 4 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data limite para matrícula</th>
                                        <th width="110px" <?= $indexHeader == 5 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Coordenador</th>
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
        <!-- popup "modal" do bootstrap para inserção de nova cidade -->
        <div class="modal fade" id="modal-nova-cidade" tabindex="-1" role="dialog" 
             aria-labelledby="modal-nova-cidade" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_cidades.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Nova cidade</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="nome-nova">Nome da cidade:</label>
                                <input type="text" name="nome" id="nome-nova" required
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="UF-nova">Estado:</label>
                                <select name="UF" id="UF-nova" class="form-control">
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
                            <div class="form-group">
                                <label for="ano-nova">Ano:</label>
                                <select name="ano-nova" id="ano-nova" required
                                        class="form-control">
                                    <?php
                                        for($i = 0; $i < 3; $i++){
                                            echo '<option value="' . (date("Y") + $i) . 
                                                  '">' . (date("Y") + $i) . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="local-nova">Local:</label>
                                <input type="text" name="local" id="local-nova" required
                                       pattern="^.{3,200}$" placeholder="Nome do local"
                                       title="O local deve ter de 3 a 200 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="coord-nova">Coordenador da cidade:</label>
                                <select name="coord" id="coord-nova" class="form-control" required>
                                    <option value="">Escolha um ano acima...</option>
                                </select>
                            </div>
                            <div class="florm-group">
                                <label for="tipo-curso">Tipo de Curso:</label>
                                <select name="tipo-curso" id="tipo-curso" class="form-control">
                                    <option value="extensao">Extensão</option>
                                    <option value="pos">Pós-Graduação</option>
                                    <option value="instituto">Instituto Hahnemman</option>
                                    <option value="todos">Todos</option>
                                </select>
                            </div>
                            <div class="florm-group">
                                <label for="modalidade-curso">Modalidade do Curso:</label>
                                <select name="modalidade-curso" id="modalidade-curso" class="form-control">
                                    <option value="regular">Regular</option>
                                    <option value="intensivo">Intensivo</option>
                                    <option value="ambos">Ambos</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="limite-nova">Data limite para matrícula:</label>
                                <input type="date" name="limite" id="limite-nova"
                                       placeholder="dd/mm/aaaa" required class="form-control">
                            </div>
                            <span id="preco-extensao" name="preco-extensao">
                                <span id="cursoRegular" name="cursoRegular">
                                    <div class="form-group">
                                        <label for="inscricao-nova">Preço de inscrição da extensão regular:</label>
                                        <input type="text" name="inscricao-ext-reg" id="inscricao-nova-ext-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-nova">Valor da parcela da extensão regular:</label>
                                        <input type="text" name="parcela-ext-reg" id="parcela-nova-ext-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                                <span id="cursoIntensivo" name="cursoIntensivo">
                                    <div class="form-group">
                                        <label for="inscricao-nova">Preço de inscrição da extensão intensiva:</label>
                                        <input type="text" name="inscricao-ext-int" id="inscricao-nova-ext-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-nova">Valor da parcela da extensão intensiva:</label>
                                        <input type="text" name="parcela-ext-int" id="parcela-nova-ext-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                            </span>
                            <span id="preco-pos" name="preco-pos">
                                <span id="cursoRegular" name="cursoRegular">
                                    <div class="form-group">
                                        <label for="inscricao-nova">Preço de inscrição da pós regular:</label>
                                        <input type="text" name="inscricao-pos-reg" id="inscricao-nova-pos-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-nova">Valor da parcela da pos regular:</label>
                                        <input type="text" name="parcela-pos-reg" id="parcela-nova-pos-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                                <span id="cursoIntensivo" name="cursoIntensivo">
                                    <div class="form-group">
                                        <label for="inscricao-nova">Preço de inscrição da pós intensiva:</label>
                                        <input type="text" name="inscricao-pos-int" id="inscricao-nova-pos-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-nova">Valor da parcela da pós intensiva:</label>
                                        <input type="text" name="parcela-pos-int" id="parcela-nova-pos-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                            </span>
                            <span id="preco-instituto" name="preco-instituto">
                                <span id="cursoRegular" name="cursoRegular">
                                    <div class="form-group">
                                        <label for="inscricao-nova">Preço de inscrição do instituto regular:</label>
                                        <input type="text" name="inscricao-instituto-reg" id="inscricao-nova-instituto-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-nova">Valor da parcela da instituto regular:</label>
                                        <input type="text" name="parcela-instituto-reg" id="parcela-nova-instituto-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                                <span id="cursoIntensivo" name="cursoIntensivo">
                                    <div class="form-group">
                                        <label for="inscricao-nova">Preço de inscrição da instituto intensivo:</label>
                                        <input type="text" name="inscricao-instituto-int" id="inscricao-nova-instituto-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-nova">Valor da parcela da instituto intensivo:</label>
                                        <input type="text" name="parcela-instituto-int" id="parcela-nova-instituto-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                            </span>
                            <!-- Disabled pois não é necessário no momento
                            <div class="form-group">
                                <label for="custo-curso-novo">Custo para efetivar o curso:</label>
                                <input type="text" name="custoCurso" id="custo-curso-novo" 
                                        required
                                       pattern="^[0-9]*\.?[0-9]+$" placeholder="Custo do curso"
                                       title="O valor de custo deve ser um número real"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="nomeEmpresa-nova">Empresa que oferece 
                                       o curso nessa cidade:</label>
                                <input type="text" name="nomeEmpresa" id="nomeEmpresa-nova" required
                                       pattern="^.{3,100}$" placeholder="Nome da empresa"
                                       title="O nome da empresa deve ter de 3 a 100 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cnpjEmpresa-nova">CNPJ da empresa:</label>
                                <input type="text" name="cnpjEmpresa" id="cnpjEmpresa-nova" required
                                       pattern="^(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}|\d{14})$"
                                       placeholder="xx.xxx.xxx/xxxx-xx"
                                       title="O CNPJ da empresa deve estar no formado xx.xxx.xxx/xxx-xx ou
                                       ser apenas numérico"
                                       class="form-control">
                            </div>

                            !-->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir cidade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de cidade -->
        <div class="modal fade" id="modal-edita-cidade" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-cidade" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/cidade/editar_cidade.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar cidade</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="idCidade" id="idCidade" value="">
                            <div class="form-group">
                                <label for="nome">Nome da cidade:</label>
                                <input type="text" name="nome" id="nome" required
                                       pattern="^.{3,100}$" title="O nome da cidade deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="UF">Estado:</label>
                                <select name="UF" id="UF" class="form-control">
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
                            <div class="form-group">
                                <label for="limite">Data limite para matrícula:</label>
                                <input type="date" name="limite" id="limite"
                                       placeholder="dd/mm/aaaa" required class="form-control">
                            </div>
                            <div class="form-group">
                                <div class="col-sm-6" style="text-align: left">
                                    <label for="cadastroAtivo">Cadastro ativo : </label>
                                </div>
                                <div class="col-sm-6">
                                    <input type="checkbox" id="cadastroAtivo" class="form-control"
                                    name="cadastroAtivo" value="true" />
                                </div>
                            </div>

                            <span id="cadastroPermitido">
                                <div class="form-group">
                                    <label for="coord">Coordenador da cidade:</label>
                                    <select name="coord" id="coord" class="form-control">
                                        <option value="">Escolha um ano acima...</option>
                                    </select>
                                </div>
                                <div class="florm-group">
                                    <label for="tipo-curso">Tipo de Curso:</label>
                                    <select name="tipo-curso" id="tipo-curso" class="form-control">
                                        <option value="extensao">Extensão</option>
                                        <option value="pos">Pós-Graduação</option>
                                        <option value="instituto">Instituto Hahnemman</option>
                                        <option value="todos">Todos</option>
                                    </select>
                                </div>
                                <div class="florm-group">
                                    <label for="modalidade-curso">Modalidade do Curso:</label>
                                    <select name="modalidade-curso" id="modalidade-curso" class="form-control">
                                        <option value="regular">Regular</option>
                                        <option value="intensivo">Intensivo</option>
                                        <option value="ambos">Ambos</option>
                                    </select>
                                </div>
                                <span id="preco-extensao" name="preco-extensao">
                                <span id="cursoRegular" name="cursoRegular">
                                    <div class="form-group">
                                        <label for="inscricao-edita">Preço de inscrição da extensão regular:</label>
                                        <input type="text" name="inscricao-ext-reg" id="inscricao-edita-ext-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-edita">Valor da parcela da extensão regular:</label>
                                        <input type="text" name="parcela-ext-reg" id="parcela-edita-ext-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                                <span id="cursoIntensivo" name="cursoIntensivo">
                                    <div class="form-group">
                                        <label for="inscricao-edita">Preço de inscrição da extensão intensiva:</label>
                                        <input type="text" name="inscricao-ext-int" id="inscricao-edita-ext-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-edita">Valor da parcela da extensão intensiva:</label>
                                        <input type="text" name="parcela-ext-int" id="parcela-edita-ext-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                            </span>
                            <span id="preco-pos" name="preco-pos">
                                <span id="cursoRegular" name="cursoRegular">
                                    <div class="form-group">
                                        <label for="inscricao-edita">Preço de inscrição da pós regular:</label>
                                        <input type="text" name="inscricao-pos-reg" id="inscricao-edita-pos-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-edita">Valor da parcela da pos regular:</label>
                                        <input type="text" name="parcela-pos-reg" id="parcela-edita-pos-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                                <span id="cursoIntensivo" name="cursoIntensivo">
                                    <div class="form-group">
                                        <label for="inscricao-edita">Preço de inscrição da pós intensiva:</label>
                                        <input type="text" name="inscricao-pos-int" id="inscricao-neditaova-pos-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-edita">Valor da parcela da pós intensiva:</label>
                                        <input type="text" name="parcela-pos-int" id="parcela-edita-pos-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                            </span>
                            <span id="preco-instituto" name="preco-instituto">
                                <span id="cursoRegular" name="cursoRegular">
                                    <div class="form-group">
                                        <label for="inscricao-edita">Preço de inscrição do instituto regular:</label>
                                        <input type="text" name="inscricao-instituto-reg" id="inscricao-edita-instituto-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-edita">Valor da parcela da instituto regular:</label>
                                        <input type="text" name="parcela-instituto-reg" id="parcela-edita-instituto-reg" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                                <span id="cursoIntensivo" name="cursoIntensivo">
                                    <div class="form-group">
                                        <label for="inscricao-edita">Preço de inscrição da instituto intensivo:</label>
                                        <input type="text" name="inscricao-instituto-int" id="inscricao-edita-instituto-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Inscrição"
                                               title="O valor de inscrição deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="parcela-edita">Valor da parcela da instituto intensivo:</label>
                                        <input type="text" name="parcela-instituto-int" id="parcela-edita-instituto-int" required
                                               pattern="^[0-9]*\.?[0-9]+$" placeholder="Parcela do curso"
                                               title="A parcela deve ser um número real"
                                               class="form-control" value="0">
                                    </div>
                                </span>
                            </span>
                            </span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar cidade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de cidade -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de cidade</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover <span id="nome-cidade"></span>?</h3>
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
