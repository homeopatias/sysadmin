<?php

session_start();

require('../entidades/Aluno.php');

if (isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Aluno) {

    $aluno = unserialize($_SESSION['usuario']);

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

    // descobrimos o quanto esse aluno vai pagar, e se os dados recebidos são válidos
    $parcelasRecebidas = isset($_POST['pgto-parcelas']) ? intval($_POST['pgto-parcelas']) : -1;
    $valorRecebido     = isset($_POST['pgto-valor']) ? $_POST['pgto-valor'] : -1;

    $parcelasValidas = preg_match("/^[0-9]+$/", $parcelasRecebidas) && $parcelasRecebidas != -1 &&
                       $parcelasValidas != 0;
    $valorValido = preg_match("/^[0-9]*\.?[0-9]+$/", $valorRecebido) && $valorRecebido != -1;

    if ($parcelasValidas && $valorValido) {
        // o usuário enviou um valor e um número de parcelas
        // redirecionamos o aluno de volta com uma mensagem de erro
        header('Location: ../visualizar_informacoes_curso.php?' .
               'mensagem=Envie um número de parcelas ou um valor a pagar', true, "302");
        die();
    }

    $valorPagar = 0;
    if ($parcelasValidas) {
        // checamos se o número de parcelas é válido, enquanto já
        // descobrimos o valor a ser pago, se tudo estiver correto

        $textoQuery  = "SELECT
                        sum( (((100 - R.desconto)/100) * R.valorTotal) - R.valorPago)
                        as valorPagar,
                        count(R.idPagMensalidade) as numParcelas FROM 
                        (SELECT P.valorTotal, P.desconto, P.valorPago, P.idPagMensalidade
                        FROM PgtoMensalidade P
                        INNER JOIN Matricula M ON M.idMatricula = P.chaveMatricula
                        WHERE M.chaveAluno = ? AND P.fechado = 0 AND P.ano <= YEAR(NOW())
                        ORDER BY P.ano DESC, P.numParcela DESC LIMIT ?) R";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $aluno->getNumeroInscricao(), PDO::PARAM_INT);
        $query->bindParam(2, $parcelasRecebidas, PDO::PARAM_INT);

        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()) {
            if ($linha['numParcelas']  != $parcelasRecebidas) {
                // foram recebidas mais parcelas do que é possível
                $parcelasValidas = false;
            } else {
                $valorPagar = $linha['valorPagar'];
            }
        } else {
            // redirecionamos o aluno de volta com uma mensagem de erro
            header('Location: ../visualizar_informacoes_curso.php?' .
                   'mensagem=Erro com o banco de dados', true, "302");
            die();
        }

    } else if ($valorValido) {
        // checamos se o valor a pagar é válido

        $textoQuery  = "SELECT sum( (((100 - P.desconto)/100) * P.valorTotal) - P.valorPago)
                        as valorFaltante FROM PgtoMensalidade P
                        INNER JOIN Matricula M ON M.idMatricula = P.chaveMatricula
                        WHERE M.chaveAluno = ? AND P.fechado = 0 AND P.ano <= YEAR(NOW())";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $aluno->getNumeroInscricao());

        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()) {
            if ($linha['valorFaltante']  < $valorRecebido) {
                // foi recebido um valor a pagar maior que o saldo devedor
                $valorValido = false;
            } else {
                $valorPagar = $valorRecebido;
            }
        } else {
            // redirecionamos o aluno de volta com uma mensagem de erro
            header('Location: ../visualizar_informacoes_curso.php?' .
                   'mensagem=Erro com o banco de dados', true, "302");
            die();
        }
    }

    if (!$parcelasValidas && !$valorValido) {
        // redirecionamos o aluno de volta com uma mensagem de erro
        header('Location: ../visualizar_informacoes_curso.php?' .
               'mensagem=Valor enviado inválido', true, "302");
        die();
    }

    // agora enviamos o usuário para a tela de pagamento
    require('../PagSeguroLibrary/PagSeguroLibrary.php');

    $reqPagamento = new PagSeguroPaymentRequest();
    $reqPagamento->addItem('0001', 'Parcela do curso de Homeopatia',
                           1, number_format($valorPagar, 2));
    $reqPagamento->setCurrency("BRL");


    $reqPagamento->setSender(  
        $aluno->getNome(),
        $aluno->getEmail(),
        mb_substr($aluno->getTelefone(), 0, 2),
        mb_substr($aluno->getTelefone(), 2)
    );

    $reqPagamento->setShippingAddress(  
        $aluno->getCep(),
        $aluno->getRua(),
        $aluno->getNumero(),
        $aluno->getComplemento(),
        $aluno->getBairro(),
        $aluno->getCidade(),
        $aluno->getEstado(),
        $aluno->getPais()
    );

    $reqPagamento->setShippingType(3);

    $reqPagamento->addParameter('tipoPagamento', 'mensalidade');
    $reqPagamento->addParameter('numeroInscricaoAluno', $aluno->getNumeroInscricao());

    $credenciais = PagSeguroConfig::getAccountCredentials();
    $url = $reqPagamento->register($credenciais);

    header('Location: ' . $url, true, "302");
    die();
}

// caso não tenha caído no caso anterior, redirecionamos o usuário
// para o index

header('Location: ../index.php', true, "302");