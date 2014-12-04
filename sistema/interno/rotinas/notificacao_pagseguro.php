<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set("log_errors", 1);
ini_set("error_log", "logs/erro.log");

// recebemos uma notificação do PagSeguro

// importa a biblioteca do PagSeguro
require('../PagSeguroLibrary/PagSeguroLibrary.php');

/* Tipo de notificação recebida */  
$tipoNotificacao = $_POST['notificationType'];  
  
/* Código da notificação recebida */  
$codigoNotificacao = $_POST['notificationCode']; 

$credenciais = PagSeguroConfig::getAccountCredentials();

if ($tipoNotificacao === 'transaction') {
    $transacao = PagSeguroNotificationService::checkTransaction($credenciais,
                                                                $codigoNotificacao);

    $statusPag = $transacao->getStatus();

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

    // inserimos o pagamento caso a transação tenha o status
    // "PAID" (identificador 3)
    if ($statusPag->getValue() == 3) {
        $referencia = $transacao->getReference();
        $codigoTipo = mb_substr($referencia, 0, 1);
        if ($codigoTipo === "M") {
            // pagamento de mensalidade, registramos o pagamento do aluno
            $idAluno = intval(mb_substr($referencia, 1));

            $textoQuery = "SELECT P.idPagMensalidade, P.valorPago, P.valorTotal, P.desconto,
                           P.numParcela, P.ano, P.metodo
                           FROM Matricula M INNER JOIN PgtoMensalidade P
                           ON P.chaveMatricula = M.idMatricula
                           INNER JOIN Aluno A ON A.numeroInscricao = M.chaveAluno
                           WHERE A.numeroInscricao = ? AND P.fechado = 0";

            $query = $conexao->prepare($textoQuery);
            $query->bindParam(1, $idAluno);

            $query->setFetchMode(PDO::FETCH_ASSOC);

            $query->execute();

            $pagamentos = array();
            $anos = array();

            $menorAno = 0;

            // essa área foi baseada no código de pagamento por coordenador
            while ($linha = $query->fetch()) {
                $anoPag     = $linha['ano'];
                $numParcela = $linha['numParcela'];

                if ($anoPag < $menorAno) {
                    $menorAno = $anoPag;
                }
                if(!in_array($anoPag, $anos)){
                    $anos[] = $anoPag;
                }

                $pagamentos[$anoPag][$numParcela]['id'] = $linha['idPagMensalidade'];
                $pagamentos[$anoPag][$numParcela]['valor'] = $linha['valorTotal'];
                $pagamentos[$anoPag][$numParcela]['pago']   = $linha['valorPago'];
                $pagamentos[$anoPag][$numParcela]['metodo']   = $linha['metodo'];                
                $pagamentos[$anoPag][$numParcela]['fechado']  = 0;
                $pagamentos[$anoPag][$numParcela]['desconto']  = $linha['desconto'];
                $pagamentos[$anoPag][$numParcela]['editado'] = 0;
            }

            sort($anos);

            $valorTotalPago = $valor = $transacao->getGrossAmount();

            for ($i = 0; $i < count($anos) && $valor > 0; $i++) {
                $ano = $anos[$i];

                for($j = 0; $j < 12 && $valor > 0; $j++) {
                    if (isset($pagamentos[$ano][$j])) {
                        // Valor é o que sobrar do pagamento, 
                        // ja que ele pode terminar de pagar,
                        // e caso não feche o pagamento, retornará
                        // um valor negativo

                        $pagamentos[$ano][$j]['pago'] += $valor;

                        $desconto = ($pagamentos[$ano][$j]['desconto'] / 100) *
                                     $pagamentos[$ano][$j]['valor'];

                        $valor = $pagamentos[$ano][$j]['pago'] - $pagamentos[$ano][$j]['valor']
                                 + $desconto;
                        $pagamentos[$ano][$j]['editado'] = 1;
                        // Se o valor pago >= valor da parcela,
                        // o pagamento foi suficiente para fechar a parcela

                        if( $pagamentos[$ano][$j]['pago'] >= 
                            $pagamentos[$ano][$j]['valor'] - $desconto){

                            $pagamentos[$ano][$j]['pago']  =
                                $pagamentos[$ano][$j]['valor'] - $desconto;

                            // se o pagamento foi suficiente para pagar o 
                            // restante da parcela, fecha a parcela
                            $pagamentos[$ano][$j]['fechado'] = "1";
                        }
                    }
                }
            }

            $conexao->beginTransaction();
            $sucesso = 1;

            // agora registramos o pagamento genérico no banco
            $textoQuery = 'INSERT INTO Pagamento (chaveUsuario, valor,
                           metodo, objetivo, ano)
                           VALUES (?, ?, "PagSeguro", "mensalidade", ?)';
            $query = $conexao->prepare($textoQuery);

            $query->bindParam(1, $idAluno);
            $query->bindParam(2, $valorTotalPago);
            $query->bindParam(3, $anos[count($anos) - 1]);

            $sucesso = $query->execute();

            for ($i = 0; $i < count($anos); $i++) {
                $ano = $anos[$i];

                for ($j = 0 ; $j < 12 && $sucesso ; $j++) {
                    if( isset($pagamentos[$ano][$j]) &&
                        $pagamentos[$ano][$j]['editado'] ){
                        $textoQuery = "UPDATE PgtoMensalidade 
                                    SET valorPago = ?,
                                    fechado = ? , data = CURDATE(), metodo = ?
                                    WHERE idPagMensalidade = ?";

                        $metodosList= array();
                        if(strrpos($pagamentos[$ano][$j]['metodo'], "|") ){

                            $metodosList = explode("|", 
                            strtolower($pagamentos[$ano][$j]['metodo']));
                        }else{
                            $metodosList = array( 
                                strtolower( 
                                    $pagamentos[$ano][$j]['metodo'])
                                    );
                        }

                        
                        // Se o método passado não está na lista de métodos , adiciona ele
                        if(!in_array("pagseguro", $metodosList ) ){
                            $metodosList[] = "pagseguro";
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
                        $pagamentos[$ano][$j]['metodo'] = $metodoUpdate;

                        $queryArray = array(
                            $pagamentos[$ano][$j]['pago'],
                            $pagamentos[$ano][$j]['fechado'],
                            $pagamentos[$ano][$j]['metodo'],
                            $pagamentos[$ano][$j]['id'],
                            );
                        $query = $conexao->prepare($textoQuery);
                        $sucesso = $query->execute($queryArray);

                    }
                }
            }

            // se conseguiu lançar o pagamento da inscrição do ano 
            // atual e
            // ela fechou, muda o status do aluno para inscrito
            if($sucesso && isset($pagamentos[date("Y")][0]) &&
                $pagamentos[date("Y")][0]['editado']){
                
                if($pagamentos[date("Y")][0]['fechado']){
                    
                    $textoQueryUpdate = "UPDATE Aluno 
                                         SET status = 'inscrito'
                                         WHERE numeroInscricao = ?";
                    
                    $query = $conexao->prepare($textoQueryUpdate);
                    $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                    $sucesso = $query->execute();

                    // notificamos ao indicador que ele recebeu desconto por este aluno
                    $sucessoNotificacao = false;

                        //faremos 10 tentativas para notificar o aluno , se todas falharem
                        //mostramos que não foi possível notificar o aluno
                        for($i = 0;$i < 10 && !$sucessoNotificacao;$i++){

                            //gera notificação para o indicador que ele recebeu 10% de desconto
                            //nas próximas parcelas
                            $conexao->beginTransaction();

                            $titulo = "Desconto por indicação";

                            $texto  = "Um de seus indicados deu inicio ao curso, seu desconto de 10%";
                            $texto .= " por sua indicação foi adicionado às próximas";
                            $texto .= " parcelas";

                            $textoQuery = "INSERT INTO Notificacao(titulo,texto,chaveAluno)
                                            VALUES (:titulo, :texto,:idIndicador)";
                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(":titulo", $titulo, PDO::PARAM_STR);
                            $query->bindParam(":texto", $texto, PDO::PARAM_STR);
                            $query->bindParam(":idIndicador", 
                                $indicadorNovo->getNumeroInscricao(),PDO::PARAM_INT);

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

                        // ----------------------------------------------------------------------
                    }
                }
            }

            // se todos os pagamentos foram atualizados confirma a 
            // atualização, se não, da rollback
            if($sucesso){
                include_once("../entidades/Aluno.php");

                $aluno = new Aluno("");
                $aluno->setNumeroInscricao($idAluno);
                $aluno->recebeAlunoId($host, "homeopatias", $usuario, $senhaBD);

                // enviamos um email confirmando o envio do pagamento
                $quantiaPaga = $valorTotalPago;
                $quantiaPaga = number_format($quantiaPaga, 2);
                $assunto = "Homeopatias.com - Pagamento recebido - " . date("d/m/Y");
                $msg = "<b>Essa é uma mensagem automática do sistema Homeopatias.com, favor não respondê-la.</b>";
                $msg .= "<br><br><b>Pagamento recebido:</b><br><b>Valor:</b> R$" . $quantiaPaga;
                $msg .= "<br><b>Data:</b> " . date("d/m/Y") . "<br><b>Horário:</b> " . date("H:i");
                $msg .= "<br><b>Método:</b> Pagamento através do sistema PagSeguro";
                $msg .= "<br><br>Obrigado,<br>Equipe Homeobrás.";
                $headers = "Content-type: text/html; charset=utf-8 " .
                    "From: Sistema Financeiro Homeopatias.com <sistema@homeopatias.com>" . "\r\n" .
                    "Reply-To: noreply@homeopatias.com" . "\r\n" .
                    "X-Mailer: PHP/" . phpversion();

                mail($aluno->getEmail(), $assunto, $msg, $headers);

                // agora registramos no sistema uma notificação para o aluno
                $texto  = "Pagamento recebido:\nValor: R$" . $quantiaPaga;
                $texto .= "\nData: " . date("d/m/Y") . "\nHorário: " . date("H:i");
                $texto .= "\nMétodo: Pagamento através do sistema PagSeguro";
                $queryNotificacao = $conexao->prepare("INSERT INTO Notificacao 
                                    (titulo, texto, chaveAluno, lida) VALUES (?, ?, ?, 0)");
                $dados = array("Pagamento recebido", $texto, $idAluno);
                $queryNotificacao->execute($dados);

                $conexao->commit(); 
            }
            else{
                $conexao->rollback();
            }

        }
    }
}