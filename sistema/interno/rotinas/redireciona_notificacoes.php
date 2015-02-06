<?php 
	session_start();
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
    	$statusPag = $transacao->getStatus();

    	//se existe uma transacao valida, redireciona
    	if ($statusPag->getValue() == 3) {
        	$referencia = $transacao->getReference();
        	$codigoTipo = mb_substr($referencia, 0, 1);

        	// se a referencia possuir um dos códigos do sistema novo, envia ao sistema novo
        	if ($codigoTipo === "M" || $codigoTipo =="A") {
    			//header("Location:./notificacoes_pagseguro.php",true,307);
    			    	?>

    			<form action='./notificacoes_pagseguro.php' method='post' name='frm'>

				<?php
					foreach ($_POST as $parametro => $valor) {
						echo "<input type='hidden' name='".htmlentities($parametro ).
							"' value='".htmlentities($valor)."'>";
					}
		
				?>
				</form>

				<script language="JavaScript">
					document.frm.submit();
				</script>



    	<?php
    		}else{
    			//header("Location:".$_SERVER["DOCUMENT_ROOT"]."/sistema/curso/curso_notificacoes.php",true,307);
    			   	?>

    			<form action=<?= "\"".$_SERVER["DOCUMENT_ROOT"]."/sistema/curso/curso_notificacoes.php\"" ?> 
    				method='post' name='frm'>

				<?php
					foreach ($_POST as $parametro => $valor) {
						echo "<input type='hidden' name='".htmlentities($parametro ).
							"' value='".htmlentities($valor)."'>";
					}
		
				?>
				</form>

				<script language="JavaScript">
					document.frm.submit();
				</script>
    		}

    	}
    }

?>