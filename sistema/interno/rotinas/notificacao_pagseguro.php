<?php

// recebemos uma notificação do PagSeguro

// importa a biblioteca do PagSeguro
require('../PagSeguroLibrary/PagSeguroLibrary.php');

/* Tipo de notificação recebida */  
$tipoNotificacao = $_POST['notificationType'];  
  
/* Código da notificação recebida */  
$codigoNotificacao = $_POST['notificationCode']; 

$credenciais = PagSeguroConfig::getAccountCredentials();

if ($type === 'transaction') {
    $transacao = PagSeguroNotificationService::checkTransaction($credenciais,
                                                                $codigoNotificacao);

    $statusPag = $transacao->getStatus();

    // inserimos o pagamento caso a transação tenha o status
    // "PAID" (identificador 3)
    if ($status->getValue() == 3) {
        
    }
}