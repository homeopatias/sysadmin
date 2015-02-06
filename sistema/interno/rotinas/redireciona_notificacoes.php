<?php 

	// se receber uma mensagem por POST, identifica se é uma notificação do pagseguro, 
	// se for, redireciona para seu devido lugar
	
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
    	//se existe uma transacao valida, redireciona
    	if($transacao){

    		//se há um parametro de identificação do sistema novo envia para o novo
    		//caso contrario, envia para o sistema atual
    		if( isset($_POST["sistema"]) && $_POST == "novo" ){
    			header("Location:./notificacoes_pagseguro.php",true,307);
    		}else{
    			header("Location:".$_SERVER["DOCUMENT_ROOT"]."/sistema/curso/curso_notificacoes.php",true,307);
    		}

    	}
    }

?>