<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Visualização de aluno - Homeopatias.com</title>
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
            // assim podemos atualizar a lista de cidades dinamicamente durante a inserção de
            // matrícula
            
            var cidades = new Array();
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

                // cria conexão com o banco para uso ao longo da página
                $conexao = null;
                $db      = "homeopatias";
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                // procuramos o aluno desejado no banco de dados
                require_once("entidades/Aluno.php");
                $aluno = new Aluno("");
                $aluno->setNumeroInscricao($_GET["id"]);
                $sucesso = $aluno->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);

                $textoQuery  = "SELECT idCidade, UF, nome, ano FROM Cidade WHERE
                                CURDATE() < limiteInscricao AND 
                                tipo_curso = '" .$aluno->getTipoCurso(). "' 
                                OR tipo_curso = 'todos' AND modalidadeCidade = '".
                                $aluno->getModalidadeCurso()."' OR modalidadeCidade = 'ambos'";

                // caso uma cidade tenha valor 0 no preço de determinado tipo de curso,
                // isso indica que essa cidade não permite matrícula nesse tipo de curso/modalidade
                $nomeCampoPreco = "_" . $aluno->getTipoCurso() . "_" . $aluno->getModalidadeCurso();
                $textoQuery .= " AND parcela" . $nomeCampoPreco . " <> 0";


                $textoQuery .= " ORDER BY ano DESC, nome ASC";

                $query = $conexao->prepare($textoQuery);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                // variável para garantir que inicializaremos o vetor para cada
                // ano sempre que estivermos utilizando-o pela primeira vez
                $anos = [];

                // Armazenamos uma lista de cidades do ano atual para facilitar a retificação
                // de matrícula
                $cidadesAnoAtual = array();

                while ($linha = $query->fetch()){
                    // para cada cidade encontrada criamos um objeto no
                    // código javascript para representá-la
                    $id     = "\"".htmlspecialchars($linha["idCidade"])."\"";
                    $uf     = "\"".htmlspecialchars($linha["UF"])."\"";
                    $nome   = "\"".htmlspecialchars($linha["nome"])."\"";
                    $ano    = "\"".htmlspecialchars($linha["ano"])."\"";

                    if($linha["ano"] == date("Y")) {
                        $nomeCidade = htmlspecialchars($linha["nome"]). "/" . htmlspecialchars($linha["UF"]);
                        $cidadesAnoAtual[$nomeCidade] = htmlspecialchars($linha["idCidade"]);
                    }

                    if(!in_array($linha["ano"], $anos)){
                        $anos[] = $linha["ano"];
            ?>
            
            cidades[ <?= $ano ?> ] = new Array();
            <?php } ?>

            cidades[ <?= $ano ?> ].push({
                id:     <?= $id ?>,
                uf:     <?= $uf ?>,
                nome:   <?= $nome ?>,
                ano:    <?= $ano ?>
            });
            
            <?php
                }
            ?>

            $(document).ready(function(){
                // atualizamos o dropdown de cidades quando o usuário seleciona um ano durante
                // a criação de matrícula
                $("form #ano").change(function(){
                     $("form #cidade").find('option').remove().end();
                    var ano  = $(this).val();
                    if(cidades[ano]){
                        cidades[ano].forEach(function(cidade){
                            $("form #cidade")
                                .append('<option value="' + cidade.id + '">' + cidade.nome + "/"
                                        + cidade.uf + '</option>')
                        });
                    }
                });

                // abrimos o menu de nova matrícula quando o botão de nova matrícula é pressionado
                $("#efetuar-mat").click(function(){
                    $("#form-mat").toggle(500);
                });

                // passa os dados do href para o modal de confirmação de deleção quando
                // necessário
                $("#modal-confirma-deleta").on('show.bs.modal', function(e) {
                    $(this).find('.danger').attr('href', $(e.relatedTarget).data('href'));
                });

                // passa o id da aula para o modal de mudança de presença quando
                // necessário
                $("#modal-muda-frequencia").on('show.bs.modal', function(e) {
                    $(this).find('#idAula').val($(e.relatedTarget).data('idaula'));
                });

                // passa os dados para o modal de edição de pagamento quando
                // necessário
                $("#modal-edita-pag").on('show.bs.modal', function(e) {
                    $(this).find('#idPag').val($(e.relatedTarget).data('idpag'));
                    $(this).find('#pago').val($(e.relatedTarget).data('pago'));
                    $(this).find('#parcela').val($(e.relatedTarget).data('parcela'));
                    $(this).find('#desconto').val($(e.relatedTarget).data('desc'));
                    $(this).find('#data').val($(e.relatedTarget).data('datapag'));
                    $(this).find('#metodo').val($(e.relatedTarget).data('metodopag'));
                });

                $("#efetua_pagamento").click(function(){
                    $("#form-lanca-pagamento").submit();
                });

                // caso o usuário tente pagar todas as parcelas até determinado
                // mês, preenchemos o formulário de pagamento de valor e o enviamos
                // com o valor correto
                $(".pgtoParcelas").click(function() {
                    if($(this).data('valor') < 1000) {
                        $("#pgto-valor").val($(this).data('valor'));
                        $("#pgto-valor").parent().submit();
                    } else {
                        alert("O sistema PagSeguro não aceita pagamentos com valor de R$ 1000,00 ou superior.\n" +
                              "\nGentileza pagar as parcelas anteriores, para que o valor acumulado fique\n"+
                              " inferior a R$ 1000,00.");
                    }
                });
                $("form #ano").change();

                $("#modal-edita-observacao").on('show.bs.modal', function(e) {
                    $(this).find('#observacoes').val($("#obs").text());
                });
            });
        </script>
    </head>
    <body>
        <?php
            require_once("entidades/Aluno.php");

            $idAluno = $_GET["id"];
            if(!isset($idAluno) || !preg_match("/^[0-9]*$/", $idAluno)){
                // o id passado foi inválido
                // redirecionamos o usuário para a página de gerenciamento de alunos
                // com uma mensagem de erro

        ?>

        <!-- redireciona o usuário -->
        <meta http-equiv="refresh" content="0; url=gerenciar_alunos.php?erro=Dados inválidos!">
        <script type="text/javascript">
            window.location.href = "gerenciar_alunos.php?erro=Dados inválidos!";
        </script>

        <?php
                die();
            }

            // procuramos o aluno desejado no banco de dados
            $aluno = new Aluno("");
            $aluno->setNumeroInscricao($idAluno);
            $sucesso = $aluno->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);
            if(!$sucesso){
                // o id passado não representa um aluno no sistema
                // redirecionamos o usuário para a página de gerenciamento de alunos
                // com uma mensagem de erro
        ?>

        <!-- redireciona o usuário -->
        <meta http-equiv="refresh" content="0; url=gerenciar_alunos.php?erro=Dados inválidos!">
        <script type="text/javascript">
            window.location.href = "gerenciar_alunos.php?erro=Dados inválidos!";
        </script>

        <?php
            }

            include("modulos/navegacao.php");

            $mensagem = "";

            // exibe dados do aluno apenas para administradores logados
            if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
               && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

                //se receber um valor de pagamento e um  método, efetua o pagamento
                if( isset( $_POST['valor-pagamento'] ) && isset( $_POST["metodo-pagamento"] ) ){

                    // procuramos os pagamentos desse ano, tanto pendentes
                    // como efetuados

                    $anoPagamento = date("Y");
                    if( isset($_GET["ano"]) ){
                        $anoPagamento = $_GET["ano"];
                    }

                    $textoQuery  = "SELECT P.idPagMensalidade, P.valorPago, P.valorTotal, P.data, P.desconto,
                                     P.metodo, P.ano, P.numParcela ,P.fechado 
                                    FROM Matricula M, PgtoMensalidade P
                                    WHERE M.chaveAluno = ?
                                    AND P.chaveMatricula = M.idMatricula
                                    AND P.ano = ?
                                    ORDER BY P.data DESC";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                    $query->bindParam(2, $anoPagamento, PDO::PARAM_STR);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    $numPagamentos = $query->rowCount();

                    $anos = array();
                    $pagamentos = array();
                    while($linha = $query->fetch()){
                        $anoPag = $linha['ano'];

                        // Inicia a divida para o ano atual caso não tenha sido iniciada ainda
                        if(!in_array($anoPag, $anos)){
                            $anos[] = $anoPag;
                            $pagamentos[$anoPag]['divida'] = 0;
                        }
                        $numParcela = $linha['numParcela'];
                        $pagamentos[$anoPag][$numParcela]['id'] = $linha['idPagMensalidade'];
                        $pagamentos[$anoPag][$numParcela]['valor'] = $linha['valorTotal'];
                        $pagamentos[$anoPag][$numParcela]['pago']   = $linha['valorPago'];
                        $pagamentos[$anoPag][$numParcela]['data']  = $linha['data'];
                        $pagamentos[$anoPag][$numParcela]['fechado']  = $linha['fechado'];
                        $pagamentos[$anoPag][$numParcela]['desconto']  = $linha['desconto'];
                        $pagamentos[$anoPag][$numParcela]['metodo'] = $linha['metodo'];
                        $pagamentos[$anoPag][$numParcela]['editado'] = 0;

                        
                        if(!$pagamentos[$anoPag][$numParcela]['fechado']){
                            $pagamentos[$anoPag]['divida'] += $linha['valorTotal'] -
                            ( ($linha['valorTotal']) * ($linha['desconto']/100) )
                                - $linha['valorPago'];
                        }
                    } // fim while($linha = $query->fetch()){


                    $valor = isset( $_POST["valor-pagamento"] ) ? (float)$_POST["valor-pagamento"] : 0;
                    $valorValido = !is_nan($valor) && $valor <= $pagamentos[$anoPagamento]["divida"];

                    $metodo = isset( $_POST["metodo-pagamento"] ) ? $_POST["metodo-pagamento"] : "";
                    $metodoValido =  ( isset($metodo) && 
                                                mb_strlen($metodo, 'UTF-8') >= 3 && 
                                                mb_strlen($metodo, 'UTF-8') <= 200
                                             );

                    if($valorValido && $metodoValido){

                        // Varremos o vetor com os pagamentos do ano desejado fazendo o
                        // cascateamento do valor pago

                        for ($i = 0 ; $i < 12 && $valor > 0; $i++) {
                            if(!$pagamentos[$anoPagamento][$i]['fechado']){
                                if($valor > 0 ){
                                    // Valor é o que sobrar do pagamento, 
                                    // ja que ele pode terminar de pagar,
                                    // e caso não feche o pagamento, retornará
                                    // um valor negativo

                                    $pagamentos[$anoPagamento][$i]['pago'] += $valor;

                                    $desconto = ($pagamentos[$anoPagamento][$i]['valor'] *
                                            $pagamentos[$anoPagamento][$i]['desconto'] /100);

                                    $valor = $pagamentos[$anoPagamento][$i]['pago']  - 
                                        $pagamentos[$anoPagamento][$i]['valor'] + $desconto ;

                                    $pagamentos[$anoPagamento][$i]['editado'] = 1;
                                    // Se o valor pago >= valor da parcela,
                                    // o pagamento foi suficiente para fechar a parcela


                                    if( $pagamentos[$anoPagamento][$i]['pago'] >= 
                                        ($pagamentos[$anoPagamento][$i]['valor'] - $desconto))
                                        {

                                        $pagamentos[$anoPagamento][$i]['pago']  =
                                            $pagamentos[$anoPagamento][$i]['valor'] - $desconto;

                                        // se o pagamento foi suficiente para pagar o 
                                        // restante da parcela, fecha a parcela
                                        $pagamentos[$anoPagamento][$i]['fechado'] = "1";
                                    }
                                } // if($valor > 0 ){
                            } // fim if(!$pagamentos[$anoPagamento][$i]['fechado']){
                        } // fim for ($i = 0 ; $i < 12 && $valor > 0; $i++) {

                        $conexao->beginTransaction();
                        $sucesso = 1;

                        // agora registramos o pagamento genérico no banco
                        $textoQuery = 'INSERT INTO Pagamento (chaveUsuario, valor, data,
                                       metodo, objetivo, ano)
                                       VALUES (?, ?, NOW(), ?, "mensalidade", ?)';
                        $query = $conexao->prepare($textoQuery);

                        $valorTotalPago = (float) $_POST["valor-pagamento"];
                        $idAlunoPagante = $aluno->getId();
                        $query->bindParam(1, $idAlunoPagante);
                        $query->bindParam(2, $valorTotalPago);
                        $query->bindParam(3, $metodo);
                        $query->bindParam(4, $anoPagamento);

                        $sucesso = $query->execute();

                        for ($i = 0 ; $i < 12 && $sucesso ; $i++) {
                            if( $pagamentos[$anoPagamento][$i]['editado'] ){
                                $textoQuery = "UPDATE PgtoMensalidade 
                                            SET valorTotal = ?, valorPago = ?,
                                            fechado = ? , data = CURDATE(), metodo = ?
                                            WHERE idPagMensalidade = ?";

                                $metodosList= array();
                                if(strrpos($pagamentos[$anoPagamento][$i]['metodo'], "|") ){

                                    $metodosList = explode("|",
                                    strtolower($pagamentos[$anoPagamento][$i]['metodo']));
                                }else{
                                    $metodosList = array( 
                                        strtolower( 
                                            $pagamentos[$anoPagamento][$i]['metodo'])
                                            );
                                }

                                
                                // Se o método passado não está na lista de métodos , adiciona ele
                                if(!in_array(strtolower($metodo), $metodosList ) ){
                                    $metodosList[] = $metodo;
                                }

                                $metodoUpdate = "";
                                // Separa os métodos por '|' no bd
                                foreach ($metodosList as $metodo) {
                                    $metodo = ucfirst($metodo);
                                    if(strlen($metodoUpdate) == 0){
                                        $metodoUpdate = $metodo;
                                    }
                                    else{
                                        $append = "|".$metodo;
                                        $metodoUpdate = $metodoUpdate.$append; 
                                    }

                                }
                                $pagamentos[$anoPagamento][$i]['metodo'] = $metodoUpdate;

                                $queryArray = array(
                                    $pagamentos[$anoPagamento][$i]['valor'],
                                    $pagamentos[$anoPagamento][$i]['pago'],
                                    $pagamentos[$anoPagamento][$i]['fechado'],
                                    $pagamentos[$anoPagamento][$i]['metodo'],
                                    $pagamentos[$anoPagamento][$i]['id'],
                                    );
                                $query = $conexao->prepare($textoQuery);
                                $sucesso = $query->execute($queryArray);

                            } // fim if( $pagamentos[$anoPagamento][$i]['editado'] ){
                        } // fim for ($i = 0 ; $i < 12 && $sucesso ; $i++) {


                        // se conseguiu lançar o pagamento da inscrição do ano 
                        // atual e
                        // ela fechou, muda o status do aluno para inscrito
                        if($sucesso && $pagamentos[date("Y")][0]['editado']){
                            
                            if($pagamentos[date("Y")][0]['fechado']){
                                $textoQueryUpdate = "UPDATE Aluno 
                                                     SET status = 'inscrito'
                                                     WHERE numeroInscricao = ?";
                                
                                $query = $conexao->prepare($textoQueryUpdate);
                                $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                                $sucesso = $query->execute();

                            }

                        }

                        //se todos os pagamentos foram atualizados confirma a 
                        // atualização , se não, da rollback
                        if($sucesso){
                            // enviamos um email confirmando o envio do pagamento
                            $quantiaPaga = htmlspecialchars($_POST["valor-pagamento"]);
                            $quantiaPaga = number_format($quantiaPaga, 2);
                            /*$assunto = "Homeopatias.com - Pagamento recebido - " . date("d/m/Y");
                            $msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la.</b>";
                            $msg .= "<br><br><b>Pagamento recebido:</b><br><b>Valor:</b> R$" . $quantiaPaga;
                            $msg .= "<br><b>Data:</b> " . date("d/m/Y") . "<br><b>Horário:</b> " . date("H:i");
                            $msg .= "<br><b>Método:</b> " . $metodo;
                            $msg .= "<br><br>Obrigado,<br>Equipe Homeobrás.";
                            $headers = "Content-type: text/html; charset=utf-8 " .
                                "From: Sistema Financeiro Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
                                "Reply-To: noreply@homeopatias.com" . "\r\n" .
                                "X-Mailer: PHP/" . phpversion();

                            mail($aluno->getEmail(), $assunto, $msg, $headers);*/

                            // agora registramos no sistema uma notificação para o aluno
                            $texto = "Pagamento recebido:\nValor: R$" . $quantiaPaga;
                            $texto .= "\nData: " . date("d/m/Y") . "\nHorário: " . date("H:i");
                            $texto .= "\nMétodo: " . $metodo;
                            $queryNotificacao = $conexao->prepare("INSERT INTO Notificacao 
                                                (titulo, texto, chaveAluno, lida) VALUES (?, ?, ?, 0)");
                            $dados = array("Pagamento recebido", $texto, $idAluno);
                            $queryNotificacao->execute($dados);
                        

                            if($sucesso){
                                $conexao->commit(); 

                                //Se a inscrição foi paga, atualiza desconto
                                if($pagamentos[date("Y")][0]['fechado']){
                                    require_once(dirname(__FILE__).
                                        "/entidades/Aluno.php");

                                    $aluno = new Aluno("");
                                    $aluno->setNumeroInscricao($idAluno);
                                    $aluno->recebeAlunoId($host, $db, $usuario, $senhaBD);
            
                                    $indicador = new Aluno("");
                                    $indicador->setNumeroInscricao($aluno->getIdIndicador());
                                    $indicador->recebeAlunoId($host, $db, $usuario, $senhaBD);
                                    $indicador->atualizaDesconto($host, $db,
                                                     $usuario, $senhaBD);
                                    $sucessoNotificacao = false;

                                    if($aluno->getIdIndicador()){
                                        //faremos 10 tentativas para notificar o aluno , se todas falharem
                                        //mostramos que não foi possível notificar o aluno
                                        for($i = 0;$i < 10 && !$sucessoNotificacao;$i++){
                                            //gera notificação para o indicador que ele recebeu 10% de desconto
                                            //nas próximas parcelas
                                            $conexao->beginTransaction();
                                            $titulo = "Desconto por indicação";
                                            $texto  = "Você recebeu 10% de desconto por ter indicado ";
                                            $texto .= "o(a) aluno(a) : ".$aluno->getNome();

                                            $textoQuery = "INSERT INTO Notificacao(titulo,texto,chaveAluno)
                                                            VALUES (:titulo, :texto,:idIndicador)";
                                            $query = $conexao->prepare($textoQuery);
                                            $query->bindParam(":titulo", $titulo, PDO::PARAM_STR);
                                            $query->bindParam(":texto", $texto, PDO::PARAM_STR);
                                            $query->bindParam(":idIndicador", 
                                                $indicador->getNumeroInscricao(),PDO::PARAM_INT);

                                            $sucessoNotificacao = $query->execute();

                                            if(!$sucessoNotificacao){
                                                $conexao->rollback();
                                            }
                                        
                                        }

                                        //se conseguiu notificar, confirma transação
                                        if($sucessoNotificacao){
                                            $conexao->commit();
                                        }else{
                                            //se não, mostra mensagem na tela
                                            $mensagem = "Não foi possível notificar o aluno 
                                                        de seu desconto.";
                                        }
                                    }
                                }
                            }
                            
                            else{
                                $conexao->rollback();
                            }
                        }
                        else{
                            $conexao->rollback();
                        }

                    } // fim if($valorValido && $metodoValido){

                } // fim  if( isset( $_POST['valor-pagamento'] ) && isset( $_POST["metodo-pagamento"] ) ){
  
                // se receber um desconto por POST, altera o desconto do aluno atual
                if( isset($_POST["desconto-individual"]) ){
                    $desconto = (float)$_POST["desconto-individual"];
                    $anoPagamento = isset($_GET["ano"]) ? $_GET["ano"] : date("Y");

                    if($desconto < 0 || $desconto > 100 || is_nan($desconto)){
                        $mensagem = "Desconto Inválido!";
                    }else{
                        $textoQuery = "UPDATE Matricula M, Cidade C set M.desconto_individual = :desconto
                        WHERE M.chaveAluno = :idAluno AND M.chaveCidade = C.idCidade AND C.ano = :ano";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(":desconto", $desconto, PDO::PARAM_INT);
                        $query->bindParam(":idAluno" , $idAluno , PDO::PARAM_INT );
                        $query->bindParam(":ano" , $anoPagamento );

                        $query->execute();   

                        if($anoPagamento < date("Y")){
                            $aluno->atualizaDescontoAnteriores($host, $db, $usuario,$senhaBD, $anoPagamento);   
                        }else{
                            $aluno->atualizar($host, $db, $usuario,$senhaBD);
                        }
                    }
                }

                // caso o usuário tenha chegado aqui através de um formulário, cria a nova
                // matrícula
                if(isset($_POST["submit"])){
                    // validamos os dados recebidos
                    $id       = $_GET["id"];
                    $idCidade = $_POST["cidade"];
                    $etapa    = $_POST["etapa"];

                    $idValido       = isset($id) && preg_match("/^\d+$/", $id);
                    $idCidadeValido = isset($idCidade) && preg_match("/^\d*$/", $idCidade);
                    $etapaValida    = isset($etapa) && preg_match("/^[1-4]$/", $etapa);

                    $anoValido = true;
                    // checamos se o aluno já está matrículado no ano recebido

                    // primeiro descobrimos o ano dessa cidade
                    $ano = 0;
                    $textoQuery  = "SELECT ano FROM Cidade WHERE idCidade = ?";

                    $query = $conexao->prepare($textoQuery);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute(array($idCidade));
                    if($linha = $query->fetch()){
                        $ano = intval($linha["ano"]);
                    }else{
                        // essa cidade não existe
                        $idCidadeValido = false;
                    }

                    $textoQuery  = "SELECT C.idCidade, C.nome FROM Cidade C, Matricula M 
                                    WHERE M.chaveCidade = C.idCidade AND 
                                    M.chaveAluno = ? AND C.ano = ?";

                    $query = $conexao->prepare($textoQuery);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute(array($id, $ano));

                    if($linha = $query->fetch()){
                        $anoValido = false;
                    }

                    if($ano < date("Y")) {
                        // caso o ano seja anterior ao atual
                        $anoValido = false;
                    }

                    if($idValido && $idCidadeValido && $etapaValida && $anoValido){

                        // Usamos as TRANSACTIONs do MySql para garantir que caso haja
                        // algum erro, as tabelas continuem consistentes
                        $conexao->beginTransaction();

                        $dadosMatricula  = array($id, $etapa, $idCidade);
                        $queryMatricula  = "INSERT INTO Matricula (chaveAluno, etapa, chaveCidade) 
                                            VALUES (?,?,?)";
                        $query  = $conexao->prepare($queryMatricula);
                        $sucessoMatricula = $query->execute($dadosMatricula);
                        $idUltimaMatricula = $conexao->lastInsertId();

                        // agora fazemos com que o aluno passe a constar como pré-inscrito
                        $queryInscrito  = "UPDATE Aluno SET status = 'preinscrito' 
                                           WHERE numeroInscricao = ?";
                        $query           = $conexao->prepare($queryInscrito);
                        $query->bindParam(1, $id);
                        $sucessoInscrito = $query->execute();
                        $aluno->setStatus('preinscrito');

                        // agora tentamos criar os pagamentos

                        // pega os valores de inscrição e parcelas da cidade
                        $textoQuery = "SELECT C.ano, ";
                        //pega as parcelas de acordo com tipo e modalidade
                            //do aluno
                            if($aluno->getTipoCurso() === "extensao"){
                                if($aluno->getModalidadeCurso() == "regular"){
                                    $textoQuery .= "C.inscricao_extensao_regular
                                                    as inscricao,
                                                    C.parcela_extensao_regular
                                                    as parcela";
                                }
                                if($aluno->getModalidadeCurso() == "intensivo"){
                                    $textoQuery .= "C.inscricao_extensao_intensivo
                                                    as inscricao,
                                                    C.parcela_extensao_intensivo
                                                    as parcela";
                                }
                            }else if($aluno->getTipoCurso() === "pos"){
                                if($aluno->getModalidadeCurso() == "regular"){
                                    $textoQuery .= "C.inscricao_pos_regular
                                                    as inscricao,
                                                    C.parcela_pos_regular
                                                    as parcela";
                                }
                                if($aluno->getModalidadeCurso() == "intensivo"){
                                    $textoQuery .= "C.inscricao_pos_intensivo
                                                    as inscricao,
                                                    C.parcela_pos_intensivo
                                                    as parcela";
                                }
                            }else if($aluno->getTipoCurso() === "instituto"){
                                if($aluno->getModalidadeCurso() == "regular"){
                                    $textoQuery .= "C.inscricao_instituto_regular
                                                    as inscricao,
                                                    C.parcela_instituto_regular
                                                    as parcela";
                                }
                                if($aluno->getModalidadeCurso() == "intensivo"){
                                    $textoQuery .= "C.inscricao_instituto_intensivo
                                                    as inscricao,
                                                    C.parcela_instituto_intensivo
                                                    as parcela";
                                }
                            }

                        $textoQuery .= " FROM Cidade C, Matricula M
                                       WHERE C.idCidade = M.chaveCidade AND
                                       M.idMatricula = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1,$idUltimaMatricula);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        
                        $queryInsert = "";
                        $insertArray = [];

                        $sucessoPgto = false;

                        if($linha = $query->fetch()){
                            for($i = 0; $i < 12; $i++){

                                if($i == 0){ // parcela numero 0 será considerada valor da
                                             // inscrição
                                    $queryInsert    = "INSERT INTO `homeopatias`.`PgtoMensalidade` 
                                                    (`chaveMatricula`, `numParcela`, `ValorTotal`, `ValorPago`, 
                                                        `desconto`, `fechado`,`ano`) 
                                                    VALUES (?, '0', ?, '0', '0', '0', ?) ";
                                    $insertArray  = array($idUltimaMatricula, $linha["inscricao"], $linha["ano"]);

                                } 
                                else{
                                    $queryInsert    .= " , (?, ?, ?, '0', '0', '0', ?) ";
                                    $insertArray[]  = $idUltimaMatricula;
                                    $insertArray[]  = $i;
                                    $insertArray[]  = $linha["parcela"];
                                    $insertArray[]  = $linha["ano"];
                                }
                            }
                            $query = $conexao->prepare($queryInsert);
                            $sucessoPgto = $query->execute($insertArray);

                        } else {
                            // a cidade não foi encontrada, cancela
                            $conexao->rollBack();
                            $mensagem = "Cidade não encontrada";
                        }

                        if(!$sucessoMatricula) {
                            // erro na matrícula, desfazemos as mudanças
                            $conexao->rollBack();
                            $mensagem = "Erro na matrícula";
                        } else if(!$sucessoInscrito) {
                            // erro na mudança para inscrito, desfazemos as mudanças
                            $conexao->rollBack();
                            $mensagem = "Erro na atualização de status de aluno após matrícula";
                        } else if(!$sucessoPgto) {
                            // erro na criação dos pagamentos, desfazemos as mudanças
                            $conexao->rollBack();
                            $mensagem = "Erro na criação dos pagamentos do ano";
                        } else {
                            // tudo certo, confirmamos as mudanças
                            $conexao->commit();                            
                        }

                    }else if(!$idValido){
                        $mensagem = "Dados inconsistentes";
                    }else if(!$idCidadeValido){
                        $mensagem = "Cidade inválida!";
                    }else if(!$etapaValida){
                        $mensagem = "Etapa inválida!";
                    }else if(!$anoValido){
                        $mensagem = "Não é possível matricular o aluno nesse ano!";
                    }
                }
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <?php
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?> 
                    <a class="pull-right" href="gerenciar_alunos.php">
                        Voltar para alunos
                    </a>

                    <br>
                    <!-- //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////// SUMÁRIO DO ALUNO ////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////////
                         Dados principais do aluno -->

                    <div id="foto" >
                        <img src=<?php 
                            if( file_exists("fotos/".$aluno->getId().".png" ) ){
                                echo "\"fotos/".$aluno->getId().".png\"";
                            }else{
                                echo "\"fotos/Padrao.png\"";;
                            }
                           ?>
                           width="150px" height="200px">
                    </div>
                    
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Aluno:</b>
                            <?= htmlspecialchars($aluno->getNome()); ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Nome de usuário:</b>
                            <?= htmlspecialchars($aluno->getLogin()); ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Número de inscrição:</b>
                            <?= htmlspecialchars($aluno->getNumeroInscricao()) ?>
                        </p>
                        <?php
                            $status = "";
                            if($aluno->getStatus() === "inscrito"){
                                $status = "Inscrito";
                            } else if($aluno->getStatus() === "preinscrito"){
                                $status = "Pré-inscrito";
                            } else if($aluno->getStatus() === "desistente"){
                                $status = "Desistente";
                            } else if($aluno->getStatus() === "formado"){
                                $status = "Formado";
                            } else if($aluno->getStatus() === "inativo"){
                                $status = "Inativo";
                            }
                        ?>
                    </div>
                    <div class="row">
                        <?php
                            $indicador = 
                                $aluno->getIndicador($host, "homeopatias", $usuario, $senhaBD);
                        ?>
                        <p style="display:inline" class="col-sm-3">
                            <b>Indicador:</b>
                            <?= $indicador != null ? htmlspecialchars($indicador->getNome()) : "Nenhum" ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Status:</b>
                            <?= htmlspecialchars($status); ?>
                        </p>
                    </div>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Endereço:</b>
                            <?= htmlspecialchars( $aluno->retornaEndereco() ); ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Telefone 1:</b>
                            <?= "(" . substr(htmlspecialchars($aluno->getTelefone()), 0, 2) . ")" . 
                                      substr(htmlspecialchars($aluno->getTelefone()), 2, 4) . "-" .
                                      substr(htmlspecialchars($aluno->getTelefone()), 6) ?>
                        </p>
                        <?php if($aluno->getTelefone2()) { ?>
                            <p style="display:inline" class="col-sm-3">
                                <b>Telefone 2:</b>
                                <?= "(" . substr(htmlspecialchars($aluno->getTelefone2()), 0, 2) . ")" . 
                                          substr(htmlspecialchars($aluno->getTelefone2()), 2, 4) . "-" .
                                          substr(htmlspecialchars($aluno->getTelefone2()), 6) ?>
                            </p>
                        <?php } ?>
                        <?php if($aluno->getTelefone3()) { ?>
                            <p style="display:inline" class="col-sm-3">
                                <b>Telefone 3:</b>
                                <?= "(" . substr(htmlspecialchars($aluno->getTelefone3()), 0, 2) . ")" . 
                                          substr(htmlspecialchars($aluno->getTelefone3()), 2, 4) . "-" .
                                          substr(htmlspecialchars($aluno->getTelefone3()), 6) ?>
                            </p>
                        <?php } ?>
                        <?php
                            $escolaridade = $aluno->getEscolaridade();
                            $escolaridade = mb_strpos($escolaridade, "completo", 0, "UTF-8") ?
                                            "Ensino " . $escolaridade :
                                            mb_convert_case($escolaridade, MB_CASE_TITLE, "UTF-8");

                        ?>
                    </div>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Escolaridade:</b>
                            <?= htmlspecialchars($escolaridade); ?>
                        </p>
                        <?php if($aluno->getCurso() != null) { ?>
                        <p style="display:inline" class="col-sm-4">
                            <b>Curso superior:</b>
                            <?= htmlspecialchars($aluno->getCurso()); ?>
                        </p>
                        <?php } ?>
                    </div>
                    <?php
                        $tipoCurso = $aluno->getTipoCurso();
                        if($tipoCurso === "pos")
                            $tipoCurso = "Pós-graduação";
                        else if($tipoCurso === "extensao")
                            $tipoCurso = "Extensão";
                        else if($tipoCurso === "instituto")
                            $tipoCurso = "Instituto Hahnemann";

                        $modalidade = ucfirst($aluno->getModalidadeCurso());

                        $certificado = $aluno->getTipoCadastro();
                        if($certificado === "instituto")
                            $certificado = "Instituto Hahnemann";
                        else if($certificado === "faculdade inspirar")
                            $certificado = "Faculdade Inspirar";

                    ?>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Tipo do curso:</b>
                            <?= htmlspecialchars($tipoCurso); ?>
                        </p>
                        <p style="display:inline" class="col-sm-4">
                            <b>Modalidade:</b>
                            <?= htmlspecialchars($modalidade); ?>
                        </p>
                        <p style="display:inline" class="col-sm-4">
                            <b>Tipo de certificado:</b>
                            <?= htmlspecialchars($certificado); ?>
                        </p>
                    </div>
                    <?php
                        if($aluno->getTipoCurso() === "pos" && !$aluno->getAtivo()) {
                    ?>
                    <div class="row">
                        <p style="display:inline" class="col-sm-12">
                            <b>Documentos não enviados</b>
                            <a href=<?= '"rotinas/ativar_aluno.php?id=' . 
                                          $aluno->getNumeroInscricao() . '"' ?>
                                style="color: white">
                                <p class="btn btn-primary" style="margin-left: 15px">
                                    Clique aqui quando a documentação do aluno for recebida
                                </p>
                            </a>
                        </p>
                        $textoBtn = "Inserir";
                        if(mb_strlen($aluno->getObservacao()) > 0) {
                            $textoBtn = "Editar";
                    ?>
                    <div class="row">
                        <p style="display:inline" class="col-sm-12">
                            <b>Observação:</b><br>
                            <span id="obs"><?= nl2br(htmlspecialchars($aluno->getObservacao())); ?></span>
                    </div>
                    <?php
                        }
                    ?>
                    <div class="btn btn-primary" id="btnObservacao" data-toggle="modal" 
                         data-target="#modal-edita-observacao">
                        <?= $textoBtn ?> observação
                    </div><br>

                    <!-- //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         ////////////////////////// MATRÍCULAS ////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         ////////////////////////////////////////////////////////////////////// -->
                    <?php
                        // descobrimos se o aluno está matriculado atualmente
                        $matriculado = false;
                        $etapa = -1;
                        $idCidade = -1;
                        $idMatricula = -1;

                        $textoQuery  = "SELECT M.idMatricula, M.etapa, M.chaveCidade 
                                        FROM Matricula M, Cidade C
                                        WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                        AND C.ano = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, date("Y"), PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        if($linha = $query->fetch()){
                            // foi encontrada uma matrícula desse aluno no
                            // período atual

                            $etapa = $linha["etapa"];
                            $idCidade = $linha["chaveCidade"];
                            $idMatricula = $linha["idMatricula"];
                            $matriculado = true;
                        }
                        
                        // checamos se o aluno está matriculado em duas cidades
                        if($linha = $query->fetch()){
                            // esse aluno está matriculado em duas cidades, erro
                            echo "<b style=\"margin-left: 20px\">Esse aluno está matrículado em
                                  duas cidades ao mesmo tempo, o que não deveria ocorrer.</b><br><br>";
                        }
   
                        if($matriculado){ 

                            // agora checamos se esse aluno já pagou a inscrição
                            $textoQuery  = "SELECT P.valorPago
                                            FROM PgtoMensalidade P INNER JOIN Matricula M
                                            ON P.chaveMatricula = M.idMatricula
                                            INNER JOIN Cidade C ON M.chaveCidade = C.idCidade
                                            WHERE M.chaveAluno = ? AND C.ano = YEAR(CURDATE())
                                            AND numParcela = 0";

                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                            $query->setFetchMode(PDO::FETCH_ASSOC);
                            $query->execute();

                            $inscricaoPaga = $query->fetch()['valorPago'] != 0;
                        ?>

                    <!-- mostramos todos os dados da matricula atual do aluno -->
                    <br>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Matriculado no período atual</b>
                        </p>
                        <?php
                            if( isset($_GET["ano"]) && $_GET["ano"] != date("Y") ){ ?>

                            <a href=  
                                <?= "visualizar_aluno.php?id=". $idAluno ?>>
                                Visualizar pagamentos do ano atual
                            </a>
                            
                        <?php
                            } 
                            if(!$inscricaoPaga) {
                        ?>
                            <!-- Funcionalidade desativada
                            <a style="cursor: pointer" data-target="#modal-retificacao"
                               data-toggle="modal">
                                Retificar dados de matrícula
                            </a>-->
                        <?php
                            }

                        ?>

                        <p class="col-sm-2" id="cancelar-mat">
                            <a style="cursor: pointer" data-target="#modal-confirma-deleta"
                               data-toggle="modal"
                               data-href=<?= "\"rotinas/matricula/remover_matricula.php?id=" .
                                                $idMatricula . "&aluno=" . $idAluno . "\"" ?> >
                                Cancelar matrícula
                            </a>
                        </p>
                    </div>
                    <div class="row">
                        <p style="display:inline" class="col-sm-3">
                            <b>Cidade:</b>
                            <?php
                                require_once("entidades/Cidade.php");
                                $cidade = new Cidade();
                                $cidade->setIdCidade($idCidade);
                                $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);
                                echo $cidade->getNome() . "/" . $cidade->getUF();
                            ?>
                        </p>
                        <p style="display:inline" class="col-sm-3">
                            <b>Etapa:</b>
                            <?= $etapa ?>
                        </p>
                    </div>

                    <?php }else{ ?>

                    <br>
                    <div class="row">
                        <b class="col-sm-3">Não-matriculado no período atual</b>
                    </div>
                    <?php } 

                        // agora checamos se o aluno está matrículado no ano seguinte
                        $matriculadoProxAno = false;

                        $textoQuery  = "SELECT M.idMatricula FROM Matricula M, Cidade C ";
                        $textoQuery .= "WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade ";
                        $textoQuery .= "AND C.ano = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindValue(2, date("Y")+1, PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        if($linha = $query->fetch()){
                            $matriculadoProxAno = true;
                        }

                        // damos a opção de criação de nova matrícula caso o aluno
                        // não esteja matriculado no ano atual ou no seguinte

                        $anoAtual = date("Y"); // ano atual para uso na nova matrícula

                        // O aluno só pode se matricular no ano atual, e apenas caso
                        // ainda não esteja matriculado
                        if(!$matriculado //|| !$matriculadoProxAno)
                            && $aluno->getStatus() !== "desistente"
                            && $aluno->getStatus() !== "formado"){
                    ?>
                    <div class="row">
                        <a style="cursor: pointer" class="col-sm-2" id="efetuar-mat">
                            Efetuar matrícula
                        </a>
                    </div>
                    <div class="row">
                        <!-- formulário de nova matrícula -->
                        <form class="form-inline col-sm-12" style="display: none"
                              id="form-mat" action method="POST">
                            <br>
                            <div class="form-group" style="margin-left: 20px">
                                <label for="ano">Ano:</label>
                                <select name="ano" id="ano" class="form-control" required>
                                    <?php if(!$matriculado){ // permitimos matrícula no ano atual ?>

                                    <option value=<?= $anoAtual ?> >
                                        <?= $anoAtual ?>
                                    </option>
                                    <?php }
                                    /*    if(!$matriculadoProxAno){ // permitimos matrícula no ano seguinte


                                    <option value=<?= $anoAtual + 1 ?> >
                                        <?= $anoAtual + 1 ?>
                                    </option>
                                    } */?>

                                </select>
                            </div>
                            <div class="form-group" style="margin-left: 20px">
                                <label for="cidade">Cidade:</label>
                                <select name="cidade" id="cidade"
                                        class="form-control" required>
                                    <option value="">Escolha um ano ao lado...</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-left: 20px">
                                <label for="etapa">Etapa:</label>
                                <select name="etapa" id="etapa" class="form-control" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                            <button type="submit" name="submit" value="submit"
                                    class="btn btn-primary pull-right">
                                Matricular aluno
                            </button>
                        </form>
                    </div>

                    <?php
                        }

                        // agora checamos se esse aluno possui matrículas futuras, e se possuir,
                        // as listamos

                        // essa variável determina se foram encontradas matrículas futuras ou não
                        $futuras = false;

                        $textoQuery  = "SELECT M.etapa, M.idMatricula, C.ano, C.nome 
                                        FROM Matricula M, Cidade C 
                                        WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                        AND C.ano > ? ORDER BY C.ano DESC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, date("Y"), PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $tabela = "";

                        while($linha = $query->fetch()){
                            if(!$futuras) $futuras = true;
                            // listamos as matrículas futuras

                            $tabela .= "<tr>";
                            $tabela .= "    <td>". htmlspecialchars($linha["ano"])."</td>";
                            $tabela .= "    <td>". htmlspecialchars($linha["etapa"])."</td>";
                            $tabela .= "    <td>". htmlspecialchars($linha["nome"])."</td>";
                            $tabela .= "<td><a data-href=\"rotinas/matricula/";
                            $tabela .= "remover_matricula.php?id=";
                            $tabela .= $linha["idMatricula"] . "&aluno=" . $idAluno;
                            $tabela .= "\" href=\"#\" data-toggle=\"modal\"";
                            $tabela .= " data-target=\"#modal-confirma-deleta\">";
                            $tabela .= "<i class=\"fa fa-trash-o\"></i></a></td>";
                            $tabela .= "</tr>";
                        }

                        if($futuras){
                    ?>
                    <h3>Pré-matrículas</h3>
                    <table class="table table-bordered table-striped" id="alunos">
                        <thead style="background-color: #AAA">
                            <tr>
                                <th>Ano</th>
                                <th>Etapa</th>
                                <th>Cidade</th>
                                <th>Cancelar matrícula</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $tabela ?>
                        </tbody>
                    </table>
                    <?php
                        }

                        // agora checamos se esse aluno possui matrículas anteriores, e se possuir,
                        // as listamos

                        // essa variável determina se foram encontradas matrículas anteriores ou não
                        $anteriores = false;

                        $textoQuery  = "SELECT M.aprovado, M.etapa, C.ano, C.nome 
                                        FROM Matricula M, Cidade C 
                                        WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                        AND C.ano < ? ORDER BY C.ano DESC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, date("Y"), PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $tabela = "";

                        while($linha = $query->fetch()){
                            if(!$anteriores) $anteriores = true;
                            // listamos as matrículas anteriores

                            $tabela .= "<tr>";
                            $tabela .= "    <td>". htmlspecialchars($linha["ano"])."</td>";
                            $tabela .= "    <td>". htmlspecialchars($linha["etapa"])."</td>";
                            $tabela .= "    <td>". htmlspecialchars($linha["nome"])."</td>";
                            $tabela .= "    <td>";
                            if(is_null($linha["aprovado"])){
                                $tabela .= "<i class=\"fa fa-ellipsis-h\"></i>";
                            }else{
                                $tabela .= htmlspecialchars($linha["aprovado"]) ?
                                           "<i class=\"fa fa-check sucesso\"></i>"  :
                                           "<i class=\"fa fa-times warning\"></i>";
                            }
                            $tabela .= "</td>";
                            $tabela .= "    <td><a href=\"visualizar_aluno.php?id=".$idAluno.
                                       "&ano=".$linha["ano"]."\" >
                                        <i class=\"fa fa-money\"></i>
                                        </a></td>";
                            $tabela .= "</tr>";
                        }

                        if($anteriores){
                    ?>
                    <h3>Matrículas em períodos anteriores</h3>
                    <table class="table table-bordered table-striped" id="alunos">
                        <thead style="background-color: #AAA">
                            <tr>
                                <th>Ano</th>
                                <th>Etapa</th>
                                <th>Cidade</th>
                                <th width="100px">Aprovado?</th>
                                <th width="200px">Visualizar Pagamentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $tabela ?>
                        </tbody>
                    </table>
                    <?php
                        }
                    ?>

                    <!-- //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         ////////////////////////// PAGAMENTOS ////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         //////////////////////////////////////////////////////////////////////
                         Pagamentos efetuados e pendentes desse aluno -->
                    <?php

                        // os pagamentos são criados na hora em que o aluno se matricula,
                        // e a medida que o aluno paga, eles vão sendo fechados.

                        // procuramos os pagamentos , tanto pendentes
                        // como efetuados 

                        $anoPagamento = date("Y");
                        if( isset($_GET["ano"]) ){
                            $anoPagamento = $_GET["ano"];
                        }

                        $textoQuery  = "SELECT P.idPagMensalidade, P.valorPago, P.valorTotal, P.fechado, P.metodo,
                                        P.data, P.desconto, P.ano, P.numParcela, M.desconto_individual, M.chaveCidade
                                        FROM Matricula M, PgtoMensalidade P
                                        WHERE M.chaveAluno = ?
                                        AND P.chaveMatricula = M.idMatricula 
                                        AND P.ano = ?
                                        ORDER BY P.data DESC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                        $query->bindParam(2, $anoPagamento, PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $idCidadePag = -1;
                        $pagamentos = array();
                        $divida = 0;
                        $desconto_individual = 0;
                        while($linha = $query->fetch()){
                            $idCidadePag = $linha["chaveCidade"];
                            $anoPag = $linha['ano'];
                            $desconto_individual = $linha["desconto_individual"];
                            $numParcela = $linha['numParcela'];
                            $pagamentos[$anoPag][$numParcela]['valor']     = $linha['valorTotal'];
                            $pagamentos[$anoPag][$numParcela]['pago']      = $linha['valorPago'];
                            $pagamentos[$anoPag][$numParcela]['data']      = $linha['data'];
                            $pagamentos[$anoPag][$numParcela]['desconto']  = $linha['desconto'];
                            $pagamentos[$anoPag][$numParcela]['metodo'] = $linha['metodo'];
                            $pagamentos[$anoPag][$numParcela]['fechado']  = $linha['fechado'];
                            $pagamentos[$anoPag][$numParcela]['id']        = $linha['idPagMensalidade'];

                            $divida += $linha['valorTotal'] - 
                                    ($linha['valorTotal'] * $linha['desconto']/100) - $linha['valorPago'];
                        }

                        if( isset( $pagamentos[$anoPagamento] ) ) {
                    ?>
                    <br>
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modal-pgto">
                        Visualizar lista de pagamentos
                    </a>
                    <?php if($divida > 0) { ?>
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modal-lanca-pagamento">
                            Efetuar pagamento manual
                        </a>

                    <?php }
                 ?>

                        <h3>Parcelas do ano atual</h3>
                        <h4 >Desconto especial do aluno nesta matrícula : 
                            <?= $desconto_individual ?>% 
                            <a href="#" class="btn" data-toggle="modal" data-target="#modal-edita-desconto">
                                Editar
                            </a>

                        </h4>

                
                    <table class="table table-bordered table-striped" id="alunos">
                        <thead style="background-color: #AAA">
                            <tr>
                                <th width="20px" style="background-color: #777"></th>
                                <th>Inscrição</th>
                    <?php
                        for($i = 1; $i < 12; $i++) {
                            echo "<th>$i</th>";
                        }
                    ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style='background-color: #AAA'><b>Valor total</b></td>
                    <?php
                        for($i = 0; $i < 12; $i ++) {
                            echo "<td>R$ " . 
                                 number_format($pagamentos[$anoPagamento][$i]['valor'], 2)
                                 . "</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Valor a pagar</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            $desconto = $pagamentos[$anoPagamento][$i]['valor'] *
                                $pagamentos[$anoPagamento][$i]['desconto']/100;
                            echo "<td>R$ " . 
                                 number_format($pagamentos[$anoPagamento][$i]['valor'] - 
                                    $desconto, 2)
                                 . "</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Valor pago</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            echo "<td>R$ " .
                                 number_format($pagamentos[$anoPagamento][$i]['pago'], 2)
                                 . "</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Data do pagamento</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            $data = $pagamentos[$anoPagamento][$i]['data'];
                            $data =  $data ? date("d/m/Y", strtotime($data)) : 'N/A';
                            echo "<td>" . $data . "</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Finalizada?</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            if($pagamentos[$anoPagamento][$i]['fechado'] === "1"){
                                echo "<td><i class=\"fa fa-check-square-o sucesso\"></i></td>";
                            }
                            else{
                                echo "<td><i class=\"fa fa-minus-square-o warning\"></i></td>";
                            }
                            
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Metodo(s)</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            $metodo = $pagamentos[$anoPagamento][$i]['metodo'];
                            echo "<td> ";

                            echo $metodo
                                 ? htmlspecialchars(str_replace("|", ", ", $metodo))
                                 : "N/A";
                            echo "</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Desconto</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            echo "<td>" .
                                 number_format($pagamentos[$anoPagamento][$i]['desconto'], 2)
                                 . "%</td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Editar</b></td>";
                        for($i = 0; $i < 12; $i ++) {
                            echo "<td><a href=\"#\" data-toggle=\"modal\" data-target=";
                            echo "\"#modal-edita-pag\" data-parcela=\"";
                            echo number_format($pagamentos[$anoPagamento][$i]['valor'], 2);
                            echo "\" data-pago=\"";
                            echo number_format($pagamentos[$anoPagamento][$i]['pago'], 2);
                            echo "\" data-idpag=\"" . $pagamentos[$anoPagamento][$i]['id'];
                            echo "\" data-desc=\"";
                            echo number_format($pagamentos[$anoPagamento][$i]['desconto'], 2);
                            echo "\" data-datapag=\"";
                            echo is_null($pagamentos[$anoPagamento][$i]['data']) ? "" : date("Y-m-d", strtotime($pagamentos[$anoPagamento][$i]['data']));
                            echo "\" data-metodopag=\"";
                            echo $pagamentos[$anoPagamento][$i]['metodo'];
                            echo "\"><i class=\"fa fa-pencil\"></i></a></td>";
                        }
                        echo "</tr><tr>";
                        echo "<td style='background-color: #AAA'><b>Gerar boleto</b></td>";
                        $valorAcumulado = 0;

                        $cidadePag = new Cidade();
                        $cidadePag->setIdCidade($idCidadePag);
                        $cidadePag->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);

                        $mesInicio = $cidadePag->getMesInicio();
                        $anoInicio = $cidadePag->getAno();

                        // caso o mês atual esteja no ano após
                        // o início das aulas nessa cidade,
                        // somamos 12 meses, para facilitar os cálculos
                        $mesCalculo = date("m");
                        if(date("Y") > $anoInicio) {
                            $mesCalculo += 12;
                        }

                        $parcelaAtual = $mesCalculo - $mesInicio + 1;
                        if($parcelaAtual > 11) {
                            $parcelaAtual = 11;
                        }

                        for($i = 0; $i < 12; $i ++) {
                            if(!$pagamentos[$anoPagamento][$i]['fechado'] &&
                                $pagamentos[$anoPagamento][$i]['pago'] > 0) {

                                $valorPagar = $pagamentos[$anoPagamento][$i]['valor'] -
                                               ($pagamentos[$anoPagamento][$i]['valor'] *
                                               ($pagamentos[$anoPagamento][$i]['desconto']/100)) -
                                               $pagamentos[$anoPagamento][$i]['pago'];

                                $valorAcumulado += $valorPagar;

                                echo '<td><a href="#" class="pgtoParcelas" data-valor="' . 
                                     $valorPagar
                                     . '">Pagar restante da parcela</a></td>';

                            }else if(!$pagamentos[$anoPagamento][$i]['fechado'] /*&& $i >= $parcelaAtual*/) {

                                $valorAcumulado += $pagamentos[$anoPagamento][$i]['valor'] -
                                                   $pagamentos[$anoPagamento][$i]['valor'] *
                                                   ($pagamentos[$anoPagamento][$i]['desconto']/100);

                                echo '<td><a href="#" class="pgtoParcelas" data-valor="' . 
                                     $valorAcumulado
                                     . '"><i class="fa fa-money"></i></a></td>';
                            }/* else if (!$pagamentos[$anoPagamento][$i]['fechado'] && $i < $parcelaAtual) {
                                $valorAcumulado += $pagamentos[$anoPagamento][$i]['valor'] -
                                                   $pagamentos[$anoPagamento][$i]['valor'] *
                                                   ($pagamentos[$anoPagamento][$i]['desconto']/100);

                                echo '<td><i class="fa fa-ellipsis-h"></i></td>';  
                            } */else {
                                echo '<td><i class="fa fa-check sucesso"></i></td>';                                
                            }
                        }
                    ?>
                            </tr>
                        </tbody>
                    </table>

                    <?php
                        }

                        // exibimos as frequências dos alunos matriculados no ano atual
                        if($matriculado) {
                            $textoQuery = "SELECT A.data, A.idAula, F.presenca, F.aprovacaoPendente FROM Aula A
                                            INNER JOIN Cidade C ON
                                            A.chaveCidade = C.idCidade INNER JOIN Matricula M ON
                                            M.chaveCidade = C.idCidade LEFT JOIN Frequencia F ON
                                            F.chaveAluno  = M.chaveAluno AND F.chaveAula = A.idAula
                                            WHERE M.chaveAluno = :chaveAluno
                                            AND C.ano = :ano AND A.etapa = M.etapa";

                            $query = $conexao->prepare($textoQuery);

                            $query->bindParam(":chaveAluno",
                                              $aluno->getNumeroInscricao());
                            $query->bindParam(":ano" , date("Y"));

                            $query->setFetchMode(PDO::FETCH_ASSOC);
                            $query->execute();

                            $tabela = "";

                            $linhas = $query->fetchAll();
                    ?>

                    <h3>Frequências desse ano</h3>
                    <table class="table table-bordered table-striped" id="alunos">
                        <tbody>
                            <tr>
                            <?php
                                echo "<th style=\"width: 10%;background-color: #AAA;\">Data da aula</th>";
                                foreach ($linhas as $linha){
                                    // listamos os dados de cada aula
                                    echo "<th class=\"data\" style=\"background-color: #AAA;\">";
                                    echo date("d/m/Y", strtotime($linha["data"]));
                                    echo "</th> ";                
                                }
                            ?>
                            </tr>
                            <tr>
                            <?php
                                echo "<th style=\"background-color: #AAA; width:10%\">Horário da aula</th>";
                                foreach ($linhas as $linha){
                                    // listamos os dados de cada aula
                                    echo "<th class=\"hora\">";
                                    echo date("H:i", strtotime($linha["data"]));
                                    echo "</th> ";                
                                }
                            ?>
                            </tr>
                            <tr>
                            <?php
                                echo "<th style=\"background-color: #AAA; width:10%\">Aluno presente?</th>";
                                foreach ($linhas as $linha) {
                                    echo "<td>";
                                    if ($linha['aprovacaoPendente'] || is_null($linha['presenca'])) {
                                        echo "<i class=\"fa fa-ellipsis-h\"></i>";
                                    } else if($linha['presenca']) {
                                        echo "<i class=\"fa fa-check-square-o sucesso\"></i>";
                                    } else {
                                        echo "<i class=\"fa fa-minus-square-o warning\"></i>";
                                    }
                                    echo "</td>";
                                }
                            ?>
                            </tr>
                            <tr>
                            <?php
                                echo "<th style=\"background-color: #AAA; width:10%\">Mudar presença</th>";
                                foreach ($linhas as $linha) {
                                    echo "<td>";
                                    echo "<a href=\"#\" data-toggle=\"modal\"
                                             data-target=\"#modal-muda-frequencia\"
                                             data-idaula=\"" . $linha["idAula"] . "\">
                                            <i class=\"fa fa-pencil\"></i>
                                          </a>";
                                    echo "</td>";
                                }
                            ?>
                            </tr>
                        </tbody>
                    </table>

                    <?php
                        }
                    ?>

                </section>
            </div>
        </div>

        <?php if(isset($aluno)) { ?>
        <!-- popup "modal" do bootstrap para visualização dos dados de pagamento -->
        <div class="modal fade" id="modal-pgto" tabindex="-1" role="dialog"
             aria-labelledby="modal-pgto" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Pagamentos</h4>
                    </div>
                    <div class="modal-body">
                        <?php
                            $textoQuery = "SELECT valor, data, metodo, ano FROM Pagamento
                                           WHERE chaveUsuario = ? AND objetivo = 'mensalidade'
                                           ORDER BY idPagamento DESC";

                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(1, $aluno->getId());
                            $query->execute();

                            if ($query->rowCount() == 0) {
                                echo "<p>Nenhum pagamento registrado para esse aluno!</p>";
                            } else {
                                $tabela = "";
                                while ($linha = $query->fetch()) {
                                    $data = htmlspecialchars($linha['data']);
                                    $data = strtotime($data);
                                    $data = date("d/m/Y H:i:s", $data);
                                    $tabela .= "<tr>";
                                    $tabela .= "<td>" . htmlspecialchars($linha['valor'])  . "</td>";
                                    $tabela .= "<td>" . $data                              . "</td>";
                                    $tabela .= "<td>" . htmlspecialchars($linha['metodo']) . "</td>";
                                    $tabela .= "<td>" . htmlspecialchars($linha['ano'])    . "</td>";
                                    $tabela .= "</tr>";
                                }
                        ?>
                        <table class="table table-bordered table-striped">
                            <thead style="background-color: #AAA">
                                <tr>
                                    <th>Valor</th>
                                    <th>Data do pagamento</th>
                                    <th>Forma de pagamento</th>
                                    <th>Ano referente ao pagamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?= $tabela ?>
                            </tbody>
                        </table>
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if($idMatricula != -1) { ?>
            <!-- popup "modal" do bootstrap para retificação de matrícula -->
            <div class="modal fade" id="modal-retificacao" tabindex="-1" role="dialog"
                 aria-labelledby="modal-retificacao" aria-hidden="true">
                <div class="modal-dialog">
                    <form action=<?= "\"rotinas/matricula/retificar_matricula.php?id=" .
                                                    $idMatricula . "\""?> method="POST">
                        <div class="modal-content">
                            <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Retificação de matrícula</h4>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="idaluno" value=<?= "\"" . $idAluno . "\"" ?>>
                                <div class="form-group">
                                    <label for="etapa-retificacao">Etapa:</label>
                                    <select type="dropdown" name="etapa-retificacao" class="form-control">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cidade-retificacao">Cidade:</label>
                                    <select name="cidade-retificacao" id="cidade-retificacao"
                                            class="form-control" required>
                                        <?php
                                        foreach ($cidadesAnoAtual as $cidadeAtual => $idAtual) {
                                        ?>
                                        <option value=<?= "\"" . $idAtual . "\"" ?>>
                                            <?= $cidadeAtual ?>
                                        </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="submit" value="Retificar dados"
                                       class="btn btn-primary form-control">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
        
        <!-- popup "modal" do bootstrap para confirmação de cancelamento de matrícula -->
        <div class="modal fade" id="modal-confirma-deleta" tabindex="-1" role="dialog"
             aria-labelledby="modal-confirma-deleta" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Cancelamento de matrícula</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Tem certeza que deseja cancelar essa matrícula?</h3>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                        <a href="#" class="btn btn-danger danger">Sim</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para lançamento de pagamento -->
        <div class="modal fade" id="modal-lanca-pagamento" tabindex="-1" role="dialog"
             aria-labelledby="modal-lanca-pagamento" aria-hidden="true">
            <form method="POST" id="form-lanca-pagamento" action="#">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Lançamento de pagamento</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-sm-12">
                            <div class="col-sm-12">
                                <p> 
                                Lançamento de pagamento para o aluno  <?= $aluno->getNome() ?> para o ano de <?= isset($_GET["ano"]) 
                                                ? htmlspecialchars($_GET["ano"]) 
                                                : date("Y")  ?>
                                </p>
                            </div>
                            <div class="col-sm-12">
                                <p>
                                Divida total : <?= "R$".
                                    number_format( $divida, 2)
                                     ?>
                                </p>
                            </div>

                                <div class="col-sm-12">
                                <label for="valor-pagamento" class="col-sm-6">
                                    Valor do Pagamento:
                                </label>
                                <input type="text" class="col-sm-6" id="valor-pagamento" name=" valor-pagamento" >

                            </div>
                            <div class="col-sm-12">
                                <label for="metodo-pagamento" class="col-sm-6">
                                    Método de Pagamento:
                                </label>
                                <select id="metodo-pagamento" name="metodo-pagamento"
                                        class="form-control" style="width: 100px">
                                    <option value="Dinheiro">Dinheiro</option>
                                    <option value="Cheque"  >Cheque</option>
                                </select>
                            </div><br>
                            <div class="col-sm-12">
                                <h5 class="warning">Não é permitido lançar um pagamento maior do que a divida total!</h5>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="button" name="efetua_pagamento" id="efetua_pagamento" 
                        value="Efetuar Pagamento" class="btn btn-primary">
                    </div>
                </div>
            </div>
            </form>
        </div>

        <!-- popup "modal" do bootstrap para edição do desconto individual do aluno -->
        <div class="modal fade" id="modal-edita-desconto" tabindex="-1" role="dialog"
             aria-labelledby="modal-edita-desconto" aria-hidden="true">
            <div class="modal-dialog">
                <form action="#" method="POST" id="formDesconto">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Edição de desconto individual</h4>
                        </div>
                        <div class="modal-body">
                            <label for="desconto_individual">Desconto individual do aluno:</label>
                            <input id="desconto-individual" name="desconto-individual" type="text"
                                pattern="^(\d)+(\.)?(\d)*$" required
                                placeholder="x.x" title="Insira apenas números e o ponto. Ex: 3.14"
                                value=  <?= "\"".$desconto_individual."\"" ?> width="100%">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" data-dismiss="modal">Cancelar</button>
                            <input type="submit" class="btn" id="alterarDesconto" 
                                name="alterarDesconto" value="Alterar"></input>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para edição de observação do aluno -->
        <div class="modal fade" id="modal-edita-observacao" tabindex="-1" role="dialog"
             aria-labelledby="modal-edita-observacao" aria-hidden="true">
            <div class="modal-dialog">
                <form action="rotinas/edita_observacao.php" method="POST" id="formObservacao">
                    <div class="modal-content">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Edição de observação de aluno</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id"
                                   value=<?= '"' . $aluno->getNumeroInscricao() . '"' ?>>
                            <div class="form-group">
                                <label for="observacoes">Observações</label>
                                <textarea name="observacoes" id="observacoes" 
                                class="form-control" 
                                cols="100"
                                rows="5"
                                placeholder="Observações" maxlength="1000"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
                            <input type="submit" class="btn btn-primary" id="alterarObs" 
                                name="alterarObs" value="Alterar"></input>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para mudança de presença de aluno em aula -->
        <div class="modal fade" id="modal-muda-frequencia" tabindex="-1" role="dialog"
             aria-labelledby="modal-muda-frequencia" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="rotinas/alterar_frequencia.php" method="POST">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Mudar presença de aluno</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="idAluno" value=<?= '"' . $aluno->getNumeroInscricao() . '"' ?>>
                            <input type="hidden" name="idAula" id="idAula">
                            <h3>Status da presença do aluno:</h3>
                            <br>
                            <input type="radio" name="presenca" value="1" required
                                   title="Insira algum valor"> Presente<br>
                            <input type="radio" name="presenca" value="0"> Ausente
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                            <input type="submit" class="btn btn-danger danger" value="Sim">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- popup "modal" do bootstrap para mudança de dados do pagamento do aluno -->
        <div class="modal fade" id="modal-edita-pag" tabindex="-1" role="dialog"
             aria-labelledby="modal-edita-pag" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="rotinas/alterar_pagamento.php" method="POST">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                            X
                        </button>
                        <h4 class="modal-title">Mudar dados do pagamento <span id="valor-pag"></span></h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="idPag" id="idPag">
                            <input type="hidden" name="idAluno" id="idAluno" value=
                                <?= '"' . $aluno->getNumeroInscricao() . '"' ?>>
                            <h3>Valor da parcela:</h3>
                            <input type="text" name="parcela" id="parcela" required
                                   pattern="^[0-9]*\.?[0-9]+$" placeholder="Valor dessa parcela"
                                   title="A parcela deve ser um número real"
                                   class="form-control">
                            <h3>Valor pago:</h3>
                            <input type="text" name="pago" id="pago" required
                                   pattern="^[0-9]*\.?[0-9]+$" placeholder="Valor pago pelo aluno"
                                   title="O valor pago deve ser um número real"
                                   class="form-control">
                            <h3>Porcentagem de desconto</h3>
                            <input id="desconto" name="desconto" type="text"
                                pattern="^(\d)+(\.)?(\d)*$" required
                                placeholder="x.x" title="Insira apenas números e o ponto. Ex: 3.14"
                                width="100%" class="form-control">
                            <h3>Data do pagamento</h3>
                            <input id="data" name="data" type="date"
                                title="Insira uma data no formato dd/mm/aaaa"
                                width="100%" class="form-control">
                            <h3>Método de pagamento</h3>
                            <select id="metodo" name="metodo" class="form-control">
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Cheque"  >Cheque</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Cancelar</button>
                            <input type="submit" class="btn btn-danger danger" value="Editar">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <form action="rotinas/gerar_pagamento_mensalidade.php" method="POST" style="display: none">
            <a id="label-valor" href="#" class="btn btn-primary" 
                style="display:block; width:300px">
                Pagar valor
            </a>
            <input type="hidden" id="idAluno" name="idAluno" value=<?= '"' . $idAluno . '"' ?>>
            <input type="number" name="pgto-valor" id="pgto-valor"
                   placeholder="Quantidade em R$" class="form-control"
                   autocomplete="off" pattern="^[0-9]*\.?[0-9]+$"
                   style="display:none;width:205px;"
                   title="O valor pago deve ser maior que uma parcela e menor que o saldo em aberto e menor que 1000">
            <input type="submit" value="Gerar" class="btn btn-primary" style="display:none">
        </form>
        <?php
            }else{
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
