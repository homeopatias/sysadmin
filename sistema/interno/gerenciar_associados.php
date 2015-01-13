<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Associados - Homeopatias.com</title>
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

            var pagamentos = new Array();

            var podeMudarPagina = true;
            $(document).ready(function(){

                // permite redimensionar as colunas da tabela
                $("#associados").colResizable({
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

                $("#associados").tablesorter({ headers: {
                    2 : { sorter: false },
                    5 : { sorter: "datetime" },
                    6 : { sorter: false },
                    7 : { sorter: false },
                    8 : { sorter: false },
                    9 : { sorter: false }
                }});
                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                    $(this).find('#nome-associado').text(
                        $(e.relatedTarget).parent().siblings('.nome').text()
                    );
                });
                // passa os dados do associado para o modal para a edição
                $("#modal-edita-associado").on('show.bs.modal', function(e) {
                    $(this).find('#id').val(
                        $(e.relatedTarget).data('id')
                    );
                    $(this).find('#idAssoc').val(
                        $(e.relatedTarget).data('id-assoc')
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

                    //------------------------
                    $(this).find('#login').val(
                        $(e.relatedTarget).parent().siblings('.login').text()
                    );
                    instituicao = $(e.relatedTarget).parent().siblings('.instituicao').data('inst');
                    $(this).find('#instituicao').val(
                        instituicao === "atenemg" ? 1 : 2
                    );
                    $(this).find('#documentos').prop("checked",
                        $(e.relatedTarget).data('documentos')
                    );
                    // formatamos a data para inserção no formulário de edição
                    var data = new Date($(e.relatedTarget).data("dataenvio"));
                    var ano = data.getFullYear();
                    var mes = data.getMonth() < 9 ? "0": "";
                    var dia = data.getDate() < 10 ? "0": "";
                    mes = mes + "" + (data.getMonth() + 1);
                    dia = dia + "" + data.getDate();

                    if(!isNaN(dia)) {
                        $(this).find('#data-envio').val(data.getFullYear() + "-" + mes + "-" + dia);
                        $(this).find('#nobjeto').val(
                            $(e.relatedTarget).data("nobjeto")
                        );
                    }

                    $(this).find('#form-terapeutica').val(
                        $(e.relatedTarget).data('formterapeutica')
                    );
                });

                $("#modal-visualiza-pagamentos").on('show.bs.modal', function(e) {
                    $.post("rotinas/associado/lista_pagamentos_associado.php", {
                        'idAssoc': $(e.relatedTarget).data("idassoc")
                    }, function(data){
                        var tbody = $("#modal-visualiza-pagamentos").find('table tbody');
                        tbody.empty();
                        data = JSON.parse(data);
                        var tr;
                        data.forEach(function(pagamento){
                            var pag = JSON.parse(pagamento);
                            tr = tbody.append("<tr>");
                            if(pag['inscricao'] == 1){
                                tr.append("<td>Inscrição</td>");
                            }else{
                                tr.append("<td>Anuidade</td>");
                            }
                            tr.append("<td>" + pag["valorTotal"] +" </td>");
                            tr.append("<td>" + pag["valorPago"] +" </td>");
                            if(pag['data']){
                                tr.append("<td>" + pag["data"] +" </td>");
                            }else{
                                tr.append("<td>N/A </td>");
                            }
                            tr.append("<td>" + pag["ano"] +" </td>");
                        });
                    });
                });

                // esconde inputs de busca

                $("#filtro-nome").hide();
                $("#filtro-cpf").hide();
                $("#filtro-cidade").hide();
                $("#filtro-estado").hide();
                $("#filtro-instituicao").hide();
                $("#ipp").hide();

                // alterna campos de texto com campos de input
                $("#label-nome").click(function(){
                    $(this).hide();
                    $("#filtro-nome").show(300);
                    $("#filtro-nome").focus();
                });

                $("#label-cidade").click(function(){
                    $(this).hide();
                    $("#filtro-cidade").show(300);
                    $("#filtro-cidade").focus();
                });

                $("#label-estado").click(function(){
                    $(this).hide();
                    $("#filtro-estado").show(300);
                    $("#filtro-estado").focus();
                });

                $("#label-cpf").click(function(){
                    $(this).hide();
                    $("#filtro-cpf").show(300);
                    $("#filtro-cpf").focus();
                });

                $("#label-instituicao").click(function(){
                    $(this).hide();
                    $("#filtro-instituicao").show(300);
                    $("#filtro-instituicao").focus();
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
                    $("#filtro-cpf").val("");
                    $("#filtro-estado").val("0");
                    $("#filtro-cidade").val("0");
                    $("#filtro-instituicao").val("");
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

                $("#form-filtro input").change(function(){
                    podeMudarPagina = false;
                });

                //Preencher os vetores de cidades e estados
            var nomesCidades = new Array();
            var estados      = new Array();
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

                $textoQuery  = "SELECT cidade, estado FROM Associado";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                // variável para garantir que inicializaremos os valores para cada
                // cidade e estado sempre que estivermos utilizando-os pela primeira vez
                $nomesCidades = [];
                $estados      = [];

                while ($linha = $query->fetch()){
                    $estado   = "\"".htmlspecialchars($linha["estado"])."\"";
                    $estado = str_replace("+"," ",$estado);
                    $estado = trim($estado);

                    $nome = "\"".htmlspecialchars($linha["cidade"])."\"";
                    $nome = str_replace("+"," ",$nome);
                    $nome = trim($nome);
                    
                    if(!in_array($nome, $nomesCidades)){
                        $nomesCidades[] = $nome;
                    ?>
                         nomesCidades.push(<?= $nome ?>);
                <?php
                    }
                    if(!in_array($estado,$estados)){
                        $estados[]      = $estado;
                    ?>
                        estados.push(<?= $estado ?>);
                <?php
                    }
                }
            ?>
                // se há cidade filtrada, seleciona ela
                // remove os sinais de + que são passados e transforma em uma entidade html
                var selecionado = <?= isset($_GET["filtro-cidade"]) ?
                             "\"" . str_replace("+","",$_GET["filtro-cidade"]) . "\"" 
                             : "0"?>;


                // A primeira opção indica nenhuma(todos os filtros) cidade
                var opcao = '<option value= 0>Todas</option>';
                            $("#filtro-cidade").append(opcao);

                

                nomesCidades.forEach(function(cidade){
                    if(selecionado != "0" && selecionado == cidade && 
                        cidade != "" && cidade != " "){
                        var opcao = '<option value=" '+ cidade +' " selected = selected>'
                            + cidade + '</option>';
                        $("#filtro-cidade").append(opcao);

                    }
                    else{
                        if(cidade != "" && cidade != " "){
                            var opcao = '<option value=" '+ cidade +' ">'
                            + cidade + '</option>';
                            $("#filtro-cidade").append(opcao);
                        }
                    }
                });

                // se há estado filtrado, seleciona ele
                // remove os sinais de + que são passados e transforma em uma entidade html
                var selecionado = <?= isset($_GET["filtro-estado"]) ?
                             "\"" . str_replace("+","",$_GET["filtro-estado"]) . "\"" 
                             : "0"?>;


                // A primeira opção indica nenhuma cidade
                var opcao = '<option value= 0 >Todos</option>';
                            $("#filtro-estado").append(opcao);


                estados.forEach(function(estado){
                    if(selecionado != "0" && selecionado == estado){
                        var opcao = '<option value=" '+ estado +' " selected = selected>'
                            + estado + '</option>';
                        $("#filtro-estado").append(opcao);

                    }
                    else{
                        if(estado != "" && estado != " "){
                            var opcao = '<option value=" '+ estado +' ">'
                            + estado + '</option>';
                            $("#filtro-estado").append(opcao);
                        }
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

            // mensagem a ser exibida acima da listagem de associados, caso seja necessário
            $mensagem = "";

            if(isset($_GET["erro"])){
                $mensagem = $_GET["erro"];
            }
            if( isset($_GET["sucesso"]) ){
                $sucesso = true;
            }

            // exibe associados apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador" && 
               1 & unserialize($_SESSION["usuario"])->getPermissoes() ){

                // se o usuário chegou até aqui através de um formulário, registra o novo
                // associado no sistema
                if(isset($_POST["submit"])){
                    // validamos todos os dados recebidos
                    $nome            = $_POST["nome"];
                    $cpf             = $_POST["cpf"];
                    $email           = $_POST["email"];
                    $login           = $_POST["login"];
                    $senha           = $_POST["senha"];
                    $instituicao     = $_POST["instituicao"];
                    $telefone        = $_POST["telefone"];
                    $cep             = $_POST["cep"];
                    $rua             = $_POST["rua"];
                    $numero          = $_POST["numero"];
                    $complemento     = $_POST["complemento"];
                    $bairro          = $_POST["bairro"];
                    $cidade          = $_POST["cidade"];
                    $estado          = $_POST["estado"];
                    $formTerapeutica = $_POST["form-terapeutica-novo"];
                    $documentos      = $_POST["documentos"] === "on";

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
                        //Checa se ja existe este cpf no sistema cadastrado como associado
                        $cpfNumerico = str_replace(".","",$cpf);
                        $cpfNumerico = str_replace("-","",$cpfNumerico);
                        $textoQuery = "SELECT U.cpf
                                       FROM Usuario U , Associado A
                                       WHERE U.id = A.idUsuario AND U.cpf = ?";
        
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
                        //Checa se ja existe este email no sistema cadastrado como associado
                        $textoQuery = "SELECT U.email
                                       FROM Usuario U , Associado A
                                       WHERE U.id = A.idUsuario AND U.email = ?";
        
                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1,$email, PDO::PARAM_STR);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();
    
                        if($linha = $query->fetch()){
                            $emailValido = false;
                            $emailExistente = true;
                        }
                    }
                    $loginValido       = isset($login) && mb_strlen($login, 'UTF-8') >= 3 &&
                                         mb_strlen($login, 'UTF-8') <= 100;
                    $senhaValida       = isset($senha) && mb_strlen($senha, 'UTF-8') >= 6 &&
                                         mb_strlen($senha, 'UTF-8') <= 72;
                    $instituicaoValida = isset($instituicao) && ($instituicao == 1 || $instituicao == 2);

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

                    $formTerapeuticaValida = isset($formTerapeutica) &&
                                         mb_strlen($formTerapeutica, "UTF-8") >= 3 &&
                                         mb_strlen($formTerapeutica, "UTF-8") <= 200;

                    // se todos os dados estão válidos, o associado é cadastrado
                    if($nomeValido && $cpfValido && $emailValido && $loginValido && $senhaValida &&
                       $instituicaoValida && $telefoneValido && $enderecoValido && 
                       $formTerapeuticaValida){

                        // lemos as credenciais do banco de dados
                        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                        $dados = json_decode($dados, true);

                        foreach($dados as $chave => $valor) {
                            $dados[$chave] = str_rot13($valor);
                        }

                        $host    = $dados["host"];
                        $usuario = $dados["nome_usuario"];
                        $senhaBD = $dados["senha"];

                        require_once("entidades/Associado.php");

                        if($instituicao == 1){
                            $instituicao = "atenemg";
                        }else{
                            $instituicao = "conahom";
                        }

                        $novo = new Associado($login);
                        $novo->setNome($nome);
                        $novo->setCpf($cpf);
                        $novo->setEmail($email);
                        $novo->setTelefone($telefone);
                        $novo->setInstituicao($instituicao);
                        $novo->setFormacaoTerapeutica($formTerapeutica);
                        $novo->setCep($cep);
                        $novo->setRua($rua);
                        $novo->setNumero($numero);
                        $novo->setComplemento($complemento);
                        $novo->setBairro($bairro);
                        $novo->setCidade($cidade);
                        $novo->setEstado($estado);
                        $novo->setPais("BRL");
                        $novo->setEnviouDocumentos($documentos);

                        $sucesso = $novo->cadastrar($host, "homeopatias", $usuario, $senhaBD, $senha);

                        if(!$sucesso){
                            $mensagem = "Já existe um usuário com esse nome 
                                         de usuário no sistema";
                        }else{
                            $msg      = "Usuário Adcionado com sucesso";
                        }
                    } else if (!$nomeValido){
                        $mensagem = "Nome inválido!";
                    }else if(!$cpfValido && !$cpfExistente){
                        $mensagem = "CPF inválido!";
                    }else if($cpfExistente){
                        $mensagem = "CPF ja cadastrado!";
                    }else if(!$emailValido && !$emailExistente){
                        $mensagem = "E-mail inválido!";
                    }else if($emailExistente){
                        $mensagem = "E-mail ja cadastrado!";
                    } else if (!$loginValido){
                        $mensagem = "Nome de usuário inválido!";
                    } else if (!$senhaValida){
                        $mensagem = "Senha inválida!";
                    } else if (!$instituicaoValida){
                        $mensagem = "Instituição inválida!";
                    } else if (!$telefoneValido){
                        $mensagem = "Telefone inválido!";
                    } else if (!$enderecoValido){
                        $mensagem = "Endereço inválido!";
                    } else if (!$cidadeValida) {
                        $mensagem = "Cidade inválida!";
                    } else if (!$estadoValido) {
                        $mensagem = "Estado inválido";
                    } else if (!$formTerapeuticaValida) {
                        $mensagem = "Formação terapeutica inválida";
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
                $host    = "localhost";
                $db      = "homeopatias";
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                $textoQuery  = "SELECT U.id, U.cpf, U.dataInscricao, U.email, 
                                U.nome, U.login, A.idAssoc, A.instituicao, A.telefone,
                                A.cep, A.rua, A.numero, A.bairro, A.cidade, A.estado, A.pais,
                                A.complemento, A.enviouDocumentos,
                                A.formacaoTerapeutica, A.numObjeto, A.dataEnvioCarteirinha
                                FROM Usuario U, Associado A WHERE A.idUsuario = U.id ";
                
                // se algum filtro foi enviado, filtra os resultados da consulta
                $filtroCpf = $filtroNome = $filtroInstituicao = $filtroCidade =
                $filtroEstado = false;
                if(isset($_GET["filtro-nome"])         || isset($_GET["filtro-cpf"])    ||
                    isset($_GET["filtro-instituicao"]) || isset($_GET["filtro-cidade"]) ||
                    isset($_GET["filtro-estado"])) {

                    $filtroCpf            =  htmlspecialchars($_GET["filtro-cpf"]);
                    $filtroNome           =  htmlspecialchars($_GET["filtro-nome"]);
                    $filtroInstituicao    =  htmlspecialchars($_GET["filtro-instituicao"]);
                    $filtroCidade         =  htmlspecialchars($_GET["filtro-cidade"]);
                    $filtroEstado         =  htmlspecialchars($_GET["filtro-estado"]);

                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $filtroNome    =  "%".mb_strtoupper($filtroNome)."%";
                        $textoQuery .= " AND UPPER(U.nome) LIKE :filtronomeassociado ";
                    } 
                    if(isset($filtroCidade) && $filtroCidade != "0" ){
                        $filtroCidade = str_replace("+"," ",$filtroCidade);
                        $filtroCidade = trim($filtroCidade);
                        $textoQuery .= " AND A.cidade LIKE :filtrocidade ";
                    }  
                    if(isset($filtroEstado) && $filtroEstado != "0" ){
                        $filtroEstado = str_replace("+"," ",$filtroEstado);
                        $filtroEstado = trim($filtroEstado);
                        $textoQuery .= " AND A.estado LIKE :filtroestado ";
                    }       
                    if(isset($filtroCpf) && mb_strlen($filtroCpf) > 0){

                        // Remove os '.' e '-' para comparar com o cpf do bd
                        $filtroCpf = str_replace(".","",$filtroCpf);
                        $filtroCpf = str_replace("-","",$filtroCpf);

                        $textoQuery .= " AND U.cpf LIKE :filtrocpf ";
                    }

                    if(isset($filtroInstituicao) && mb_strlen($filtroInstituicao) > 0 && 
                        $filtroInstituicao != "0"){
                        if($filtroInstituicao == 1){
                            $filtroInstituicao = "atenemg";
                        } else if($filtroInstituicao == 2) {
                            $filtroInstituicao = "conahom";
                        }
                        if(($filtroInstituicao != '0')){
                            $textoQuery .= " AND A.instituicao LIKE :filtroinstituicao";
                        }
                    } 
                }
                //------- Prepara o necessário para a ordenação

                // variáveis com valores defaults
                $orderBy = " ORDER BY U.id DESC" ;
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
                                $orderBy = " ORDER BY U.nome " ;
                                break;
                            case '1':
                                $orderBy = " ORDER BY U.login " ;
                                break;
                            case '3':
                                $orderBy = " ORDER BY U.instituicao " ;
                                break;
                            case '4':
                                $orderBy = " ORDER BY U.email " ;
                                break;
                            case '5':
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

                //---------SE algum index foi excolhido para ordenação, ordena---------
                
                if($indexHeader != -1){
                    $textoQuery .= $orderBy;
                }

                $textoQuery .= " LIMIT ".($itemsPorPagina+1).
                                " OFFSET ".(($pagina)*$itemsPorPagina);

                //---------------------------------------------------------------------

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);

                if(isset($_GET["filtro-nome"]) || isset($_GET["filtro-cpf"])){
                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $query->bindParam(":filtronomeassociado",$filtroNome);
                    }
                    if(isset($filtroCpf) && mb_strlen($filtroCpf) > 0){
                        $query->bindParam(":filtrocpf",$filtroCpf);
                    }
                    if(isset($filtroCidade) && $filtroCidade != "0" ){
                        $query->bindParam(":filtrocidade",$filtroCidade);
                    }  
                    if(isset($filtroEstado) && $filtroEstado != "0" ){
                        $query->bindParam(":filtroestado",$filtroEstado);
                    }  
                    if(isset($filtroInstituicao) && mb_strlen($filtroInstituicao) > 0 && 
                        $filtroInstituicao != "0") {
                        $query->bindParam(":filtroinstituicao",$filtroInstituicao);
                    }
                }

                $query->execute();

                $numeroRegistros = $query->rowCount();

                $possuiProximaPagina = false;
                $contador = 0;
                $tabela = "";

                require_once('rotinas/associado/checa_situacao_pagamentos.php');

                $i = 0;
                while ($linha = $query->fetch()){
                    if($contador != $itemsPorPagina){
                        //--------------------------------------------------
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
                        $tabela .= "    <td class=\"nome\">";
                        $tabela .= htmlspecialchars($linha["nome"])         ."</td>";
                        $tabela .= "    <td class=\"login\">";
                        $tabela .= htmlspecialchars($linha["login"])        ."</td>";
                        $tabela .= "    <td class=\"cpf\">";
                        $tabela .= $cpf                                     ."</td>";
                        $tabela .= "    <td class=\"instituicao\" data-inst=\"";
                        $tabela .= $linha["instituicao"] . "\">";
                        if($linha["instituicao"] === "atenemg"){
                            $tabela .= "Atenemg";
                        }else if($linha["instituicao"] === "conahom"){
                            $tabela .= "Conahom";
                        }
                        $tabela .= "</td>";
                        $tabela .= "<td class=\"email\">";
                        $tabela .= htmlspecialchars($linha["email"])            ."</td>";
                        $tabela .= "    <td class=\"datainsc\">";
                        $tabela .= date("d/m/Y H:i:s", 
                                        strtotime(htmlspecialchars($linha["dataInscricao"])))."</td>";
                        $tabela .= "<td><a href=\"#\" data-idassoc =\"".$linha["idAssoc"]. 
                                        "\" data-toggle=\"modal\" data-target=\"#modal-visualiza-pagamentos\">
                                        <i class = \"fa fa-money sucesso\"></i></a></td>";
                        $emDia   = checa_situacao_pagamentos_por_id($linha["idAssoc"]) 
                            ? $tabela .= "<td class=\"sucesso\">Sim</td>" 
                            : $tabela .= "<td class=\"warning\">Não</td>";
                        $tabela .= "    <td><a data-id=\"";
                        $tabela .= $linha["id"];
                        $tabela .= "\" data-id-assoc=\"";
                        $tabela .= $linha["idAssoc"];
                        $tabela .= "\" data-telefone=\"";
                        $tabela .= $linha["telefone"];
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
                        $tabela .= "\" data-documentos=\"";
                        $tabela .= $linha["enviouDocumentos"];
                        $tabela .= "\" data-nobjeto=\"";
                        $tabela .= $linha["numObjeto"];
                        $tabela .= "\" data-dataenvio=\"";
                        $tabela .= date("d/m/Y",
                                    strtotime(htmlspecialchars($linha["dataEnvioCarteirinha"])));
                        $tabela .= "\" data-formterapeutica=\"";
                        $tabela .= $linha["formacaoTerapeutica"];
                        $tabela .= "\"";
                        $tabela .= " href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-edita-associado\">";
                        $tabela .= "<i class=\"fa fa-pencil\"></i></a></td>";
                        $tabela .= "    <td><a data-href=\"rotinas/associado/remover_associado.php?id=";
                        $tabela .= $linha["id"];
                        $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                        $tabela .= " data-target=\"#modal-confirma-deleta\">";
                        $tabela .= "<i class=\"fa fa-trash-o\"></i></a></td>";
                        $tabela .= "</tr>";
                        $i++;
    
                     }
                    else{
                        $possuiProximaPagina = true;
                    }
                    $contador++;
                }  

                //fecha o script que armazena os pagamentos 
        ?>
        </script>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>Associados</h1>
                    <?php 
                        if(isset($_GET["erro"]) || mb_strlen($mensagem) != 0 ){
                            $mensagem = isset($_GET["erro"]) ? $_GET["erro"] : $mensagem;
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                        if(isset($_GET["sucesso"]) && 
                            mb_strlen($_GET["msg"], 'UTF-8') !== 0){
                            $msg = $_GET["msg"];
                            echo "<p class=\"sucesso\">$msg</p>";
                        }
                    ?>
                    <a href="#" class="btn" data-toggle="modal" data-target="#modal-novo-associado">
                        <i href="#" class="fa fa-plus"></i>
                        <p style="display:inline">Novo associado</p>
                    </a>
                    <!-- formulario para implementar filtros -->
                    <form method="GET" action="gerenciar_associados.php" id="form-filtro">
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

                            <a id="label-instituicao" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-instituicao"]) && 
                                        mb_strlen(($_GET["filtro-instituicao"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-instituicao"]) != "0") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Instituição
                            </a>
                            <select name="filtro-instituicao" id="filtro-instituicao" 
                                        class="form-control"
                                        style="display:inline;width:120px">
                                    <option value="0" <?=(isset($_GET["filtro-instituicao"]) &&
                                            htmlspecialchars($_GET["filtro-instituicao"]) == "0" ) ?
                                            'selected="selected"':''; ?> >Todas</option>
                                    <option value="1" <?=(isset($_GET["filtro-instituicao"]) &&
                                            htmlspecialchars($_GET["filtro-instituicao"]) == "1") ?
                                            'selected="selected"':''; ?> >Atenemg</option>
                                    <option value="2" <?=(isset($_GET["filtro-instituicao"]) &&
                                            htmlspecialchars($_GET["filtro-instituicao"]) == "2" ) ?
                                            'selected="selected"':''; ?> >Conahom</option>
                            </select>

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
                            <a id="label-estado" href="#" class="btn" 
                                style=  <?= (isset($_GET["filtro-estado"]) && 
                                        mb_strlen(($_GET["filtro-estado"])) > 0 &&
                                        htmlspecialchars($_GET["filtro-estado"]) != "0" &&
                                        htmlspecialchars($_GET["filtro-estado"]) != "") ? 
                                            "display:inline;color:#336600" : "display:inline";
                                        ?> 
                                >Estado
                            </a>
                            <select name="filtro-estado" id="filtro-estado" 
                                        class="form-control"
                                        style="display:inline;width:120px">
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
                            <table class="table table-bordered table-striped" id="associados">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="180px"<?= $indexHeader == 0 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Nome</th>
                                        <th width="120px"<?= $indexHeader == 1 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Nome de usuário</th>
                                        <th width="100px">CPF</th>
                                        <th width="100px"<?= $indexHeader == 3 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Instituição</th>
                                        <th width="150px"<?= $indexHeader == 4 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>E-mail</th>
                                        <th width="120px"<?= $indexHeader == 5 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Data e hora de inscrição</th>
                                        <th width="80px"<?= $indexHeader == 6 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Pagamentos</th>
                                        <th width="50px"<?= $indexHeader == 7 ? 
                                            ($direcao == 1? "class =\"headerSortUp\"" : 
                                                "class =\"headerSortDown\"") : "" ?>>Em dia</th>
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
        <!-- popup "modal" do bootstrap para inserção de novo associado -->
        <div class="modal fade" id="modal-novo-associado" tabindex="-1" role="dialog" 
             aria-labelledby="modal-novo-associado" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="gerenciar_associados.php ">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Novo associado</h4>

                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="nome-novo">Nome do associado:</label>
                                <input type="text" name="nome" id="nome-novo" required
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cpf-novo">CPF do associado:</label>
                                <input type="text" name="cpf" id="cpf-novo" required
                                       pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                       placeholder="xxx.xxx.xxx-xx" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email-novo">E-mail do associado:</label>
                                <input type="email" name="email" id="email-novo" required
                                       placeholder="E-mail"
                                       title="Insira um e-mail válido"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="telefone-novo">Telefone do associado:</label>
                                <input type="tel" name="telefone" id="telefone-novo" required
                                       placeholder="(xx)xxxx-xxxx" pattern="^\(?\d{2}\)?\d{4}-?\d{4,7}$"
                                       title="Insira um telefone válido"
                                       class="form-control">
                            </div>
                            <div class="form-group col-sm-12" >
                                <label for="">Endereço do Associado:</label>
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
                                            <label for="numero" >
                                                Cidade :</label>
                                            <input type="text" name="cidade" id="cidade"
                                                placeholder="Cidade"
                                                title="Insira o numero da residência do aluno"
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
                                <label for="form-terapeutica-novo">Formação terapêutica:</label>
                                <input type="text" name="form-terapeutica-novo" id="form-terapeutica-novo" required
                                       pattern="^.{3,200}$" placeholder="Formação terapêutica"
                                       title="O campo de formação terapêutica deve ter de 3 a 200 caracteres"
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
                                <label for="instituicao-novo">Instituição:</label>
                                <select name="instituicao" id="instituicao-novo" class="form-control">
                                    <option value="1" selected>Atenemg</option>
                                    <option value="2">Conahom</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <br>
                                <input type="checkbox" name="documentos" id="documentos-novo">
                                <label for="documentos-novo">Já teve seus documentos aprovados?</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Inserir associado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para edição de associado -->
        <div class="modal fade" id="modal-edita-associado" tabindex="-1" role="dialog" 
             aria-labelledby="modal-edita-associado" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/associado/editar_associado.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Editar associado</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <input type="hidden" name="idAssoc" id="idAssoc" value="">
                            <input type="hidden" name="id" id="id" value="">
                            <div class="form-group">
                                <label for="nome">Nome do associado:</label>
                                <input type="text" name="nome" id="nome" required
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cpf">CPF do associado:</label>
                                <input type="text" name="cpf" id="cpf" required
                                       pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                       placeholder="xxx.xxx.xxx-xx" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail do associado:</label>
                                <input type="email" name="email" id="email" required
                                       placeholder="E-mail"
                                       title="Insira um e-mail válido"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="telefone">Telefone do associado:</label>
                                <input type="tel" name="telefone" id="telefone" required
                                       placeholder="(xx)xxxx-xxxx" pattern="^\(?\d{2}\)?\d{4}-?\d{4,7}$"
                                       title="Insira um telefone válido"
                                       class="form-control">
                            </div>
                            <div class="form-group col-sm-12" >
                                <label for="">Endereço do Associado:</label>
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
                                            <label for="numero" >
                                                Cidade :</label>
                                            <input type="text" name="cidade" id="cidade"
                                                placeholder="Cidade"
                                                title="Insira o numero da residência do aluno"
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
                                <label for="form-terapeutica">Formação terapêutica:</label>
                                <input type="text" name="form-terapeutica" id="form-terapeutica" required
                                       pattern="^.{3,200}$" placeholder="Formação terapêutica"
                                       title="O campo de formação terapêutica deve ter de 3 a 200 caracteres"
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
                                <label for="instituicao">Instituição:</label>
                                <select name="instituicao" id="instituicao" class="form-control">
                                    <option value="1" selected>Atenemg</option>
                                    <option value="2">Conahom</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nobjeto">Número do objeto (correio):</label>
                                <input type="text" name="nobjeto" id="nobjeto"
                                       pattern="^.{3,100}$"
                                       placeholder="Número da carteirinha do associado no correio"
                                       title="O número do objeto deve ter de 3 a 100 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="data-envio">Data de envio da carteirinha:</label>
                                <input type="date" name="data-envio" id="data-envio"
                                       placeholder="dd/mm/aaaa" class="form-control">
                            </div>
                            <div class="form-group">
                                <br>
                                <input type="checkbox" name="documentos" id="documentos">
                                <label for="documentos">Já teve seus documentos aprovados?</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Editar associado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para confirmação de remoção de associado -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Remoção de associado</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja remover <span id="nome-associado"></span>?</h3>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                        <a href="#" class="btn btn-danger danger">Sim</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para visualização de pagamentos do associado -->
        <div class="modal fade" id="modal-visualiza-pagamentos" tabindex="-1" role="dialog"
             aria-labelledby="modal-visualiza-pagamentos" aria-hidden="true" 
             >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Pagamentos efetuados</h4>
                    </div>
                    <div class="modal-body">
                        <table id="pagamentos" class = "table table-bordered table-striped CRZ" >
                             <thead style="background-color: #AAA">
                                <th>Tipo</th>
                                <th>Valor Total</th>
                                <th>Valor Pago</th>
                                <th>Data</th>
                                <th>Ano Relacionado</th>
                            </thead>
                            <tbody id="pag"></tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">
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
            window.location = "index.php";
        </script>
        <?php
                die();
            }
            include("modulos/rodape.php");
        ?>
    </body>
</html>