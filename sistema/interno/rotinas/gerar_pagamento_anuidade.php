<?php

session_start();

require('../entidades/Associado.php');

if (isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Associado) {
    $associado = unserialize($_SESSION["usuario"]);

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

    //recebemos e analizamos o que ele deseja pagar

    // os tipos anuidade e inscrição somente estão disponíveis para pagamento no ano atual
    $inscricao = isset($_POST["target"]) && $_POST["target"] === "inscricao";
    $anuidade  = isset($_POST["target"]) && $_POST["target"] === "anuidade";

    // pode ter sido passado um valor negativo
    $valor = isset($_POST["pgto-valor"]) && $_POST["pgto-valor"] > 0 ? $_POST["pgto-valor"] : 0 ;
    if(($valor && $inscricao) || ($valor && $anuidade) || ($inscricao && $anuidade)){
        header('Location: ../visualizar_pagamentos_associado.php?mensagem=Selecione o valor a enviar ou selecione pagamento de inscrição/anuidade, não os dois.', true, "302");
        die();
    }

    //Se anuidade ou inscrição foram passados, analisa se é permitido usar essas funcionalidades
    if($inscricao || $anuidade){
         // Consultamos o banco para receber o valor da dívida do associado em anos anteriores
        
        $textoQuery = "SELECT sum(valorTotal - valorPago) as valorPagar
                       FROM   PgtoAnuidade
                       WHERE  chaveAssoc = ? AND ano < YEAR(CURDATE())";
        $query = $conexao->prepare($textoQuery);
        $query->bindParam( 1, $associado->getIdAssoc(), PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if($linha = $query->fetch()){
            $dividaAnterior = $linha["valorPagar"];
        }else{
            // Houve algum erro de consulta ao banco ou o usuário não possui dividas no banco
            header('Location: ../visualizar_pagamentos_associado.php?mensagem=Erro ao consultar divida, favor, comunicar a administração', true, "302");
            die();
        }
        if($dividaAnterior == 0){

            if($inscricao){

                //Agora analizamos quanto falta para ele pagar da inscrição
                $textoQuery = "SELECT sum(valorTotal - valorPago) as valorPagar
                           FROM   PgtoAnuidade
                           WHERE  chaveAssoc = ? AND ano = YEAR(CURDATE()) AND
                           inscricao = 1";
                $query = $conexao->prepare($textoQuery);
                $query->bindParam( 1, $associado->getIdAssoc(), PDO::PARAM_INT);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                if($linha = $query->fetch()){
                    $valor = $linha["valorPagar"];
                }else{

                    // Houve algum erro de consulta ao banco ou o usuário não possui dividas no banco
                    header('Location: ../visualizar_pagamentos_associado.php?mensagem=Erro ao consultar divida, favor, comunicar a administração', true, "302");
                    die();

                }
            }
            else{

                // Agora analisamos quanto falta para ele pagar da anuidade e se a inscrição foi paga
                $textoQuery = "SELECT sum(valorTotal - valorPago) as valorPagar,
                               EXISTS(SELECT sum(valorTotal - valorPago) as valorPagar
                                    FROM   PgtoAnuidade
                                    WHERE  chaveAssoc = ? AND ano = YEAR(CURDATE()) AND
                                    inscricao = 1 AND fechado = 1) as existe
                               FROM   PgtoAnuidade
                               WHERE  chaveAssoc = ? AND ano = YEAR(CURDATE()) AND
                               inscricao = 0";
                $query = $conexao->prepare($textoQuery);
                $query->bindParam( 1, $associado->getIdAssoc(), PDO::PARAM_INT);
                $query->bindParam( 2, $associado->getIdAssoc(), PDO::PARAM_INT);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                if($linha = $query->fetch()){
                    if($linha["existe"]){
                        $valor = $linha["valorPagar"];
                    }
                    else{
                        header('Location: ../visualizar_pagamentos_associado.php?mensagem=Erro: a inscricao não foi paga, funcionalidade indisponível', true, "302");
                    die();
                    }
                }else{

                    // Houve algum erro de consulta ao banco ou o usuário não possui dividas no banco
                    header('Location: ../visualizar_pagamentos_associado.php?mensagem=Erro ao consultar divida, favor, comunicar a administração', true, "302");
                    die();

                }
            }

        }
        if($dividaAnterior >0 || $valor == 0){
            // caso ele tenha divida anterior, esta funcionalidade não pode ser
            //usada
            header('Location: ../visualizar_pagamentos_associado.php?mensagem=Erro: funcionalidadeñão autorizada', true, "302");
            die();
        }


    }

    if($valor > 0){
        // Consultamos o banco para receber o valor da dívida do associado
        
        $textoQuery = "SELECT sum(valorTotal - valorPago) as valorPagar
                       FROM   PgtoAnuidade
                       WHERE  chaveAssoc = ?";
        $query = $conexao->prepare($textoQuery);
        $query->bindParam( 1, $associado->getIdAssoc(), PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        //-------------------------------------------------------------------
        if($linha = $query->fetch()){
            $divida = $linha["valorPagar"];
        }else{
            // Houve algum erro de consulta ao banco ou o usuário não possui dividas no banco
            header('Location: ../visualizar_pagamentos_associado.php?mensagem=Erro ao consultar divida, favor, comunicar a administração', true, "302");
            die();
        }
        if($valor > $divida || $valor == 0){
            header('Location: ../visualizar_pagamentos_associado.php?mensagem=favor, entre com um valor válido entre 0 e '.$divida, true, "302");
        die();
        }

    }

    //se correu tudo bem, o valor a ser pago deve estar setado e pronto para ser redirecionado ao pagSeguro
    // agora enviamos o usuário para a tela de pagamento
    require('../PagSeguroLibrary/PagSeguroLibrary.php');

    $reqPagamento = new PagSeguroPaymentRequest();
    if($inscricao){

        $reqPagamento->addItem('0002', 'Inscrição de associação ao curso de Homeopatia',
                           1, number_format($valor, 2, ".", ""));   
    }else{
        $reqPagamento->addItem('0003', 'Anuidade de associação ao curso de Homeopatia',
                           1, number_format($valor, 2, ".", ""));
    }
    $reqPagamento->setCurrency("BRL");


    $reqPagamento->setSender(  
        $associado->getNome(),
        $associado->getEmail(),
        mb_substr($associado->getTelefone(), 0, 2),
        mb_substr($associado->getTelefone(), 2)
    );

    $reqPagamento->setShippingAddress(  
        $associado->getCep(),
        $associado->getRua(),
        $associado->getNumero(),
        $associado->getComplemento(),
        $associado->getBairro(),
        $associado->getCidade(),
        $associado->getEstado(),
        $associado->getPais()
    );

    $reqPagamento->setShippingType(3);

    // a referência desse pagamente será a letra "A" de anuidade,
    // seguida do id de Associado deste associado
    $reqPagamento->setReference("A" . $associado->getIdAssoc());

    // adiciona um parâmetro para identificar que o pagamento é do sistema novo
    // addParameter ( parameterName, parameterValue )
    $reqPagamento->addParameter("sistema","novo");

    $credenciais = PagSeguroConfig::getAccountCredentials();
    $url = $reqPagamento->register($credenciais);

    header('Location: ' . $url, true, "302");
    die();
}

// caso não tenha caído no caso anterior, redirecionamos o usuário
// para o index

header('Location: ../index.php', true, "302");
