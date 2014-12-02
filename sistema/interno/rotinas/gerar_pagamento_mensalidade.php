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
    $valorRecebido = isset($_POST['pgto-valor']) ? $_POST['pgto-valor'] : -1;
    if (isset($_POST['pagarInsc'])) {
        $textoQuery  = "SELECT ((((100 - P.desconto)/100) * P.valorTotal) - P.valorPago) as
                        valorPagar
                        FROM PgtoMensalidade P
                        INNER JOIN Matricula M ON M.idMatricula = P.chaveMatricula
                        WHERE M.chaveAluno = ? AND P.fechado = 0 AND P.ano <= YEAR(NOW())
                        AND P.numParcela = 0";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $aluno->getNumeroInscricao());

        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()) {
            $valorRecebido = $linha['valorPagar'];
        } else {
            $valorRecebido = -1;
        }
    }

    $valorValido = preg_match("/^[0-9]*\.?[0-9]+$/", $valorRecebido) && $valorRecebido != -1;

    if ($valorValido) {
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
            }
        } else {
            // redirecionamos o aluno de volta com uma mensagem de erro
            header('Location: ../visualizar_informacoes_curso.php?' .
                   'mensagem=Erro com o banco de dados', true, "302");
            die();
        }
    } else {
        // redirecionamos o aluno de volta com uma mensagem de erro
        header('Location: ../visualizar_informacoes_curso.php?' .
               'mensagem=Valor enviado inválido', true, "302");
        die();
    }

    // agora enviamos o usuário para a tela de pagamento
    require('../PagSeguroLibrary/PagSeguroLibrary.php');

    $reqPagamento = new PagSeguroPaymentRequest();
    $reqPagamento->addItem('0001', 'Parcela do curso de Homeopatia',
                           1, number_format($valorRecebido, 2));
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

    // a referência desse pagamente será a letra "M" de mensalidade,
    // seguida do número de inscrição desse aluno
    $reqPagamento->setReference("M" . $aluno->getNumeroInscricao());

    $credenciais = PagSeguroConfig::getAccountCredentials();
    $url = $reqPagamento->register($credenciais);

    header('Location: ' . $url, true, "302");
    die();
}

// caso não tenha caído no caso anterior, redirecionamos o usuário
// para o index

header('Location: ../index.php', true, "302");