<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
 ?>
 <link rel="stylesheet" href="../estilos/estilo.css">

 <?php
	require_once($_SERVER["DOCUMENT_ROOT"].'/interno/entidades/Administrador.php');
    require_once($_SERVER["DOCUMENT_ROOT"].'/interno/entidades/Aluno.php');
	$admin = unserialize($_SESSION["usuario"]);
	if(isset($_SESSION['usuario']) && ($admin instanceof Administrador ) && 
		($admin->getNivelAdmin() === "administrador" || $admin->getNivelAdmin() === "coordenador") ){

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
        $db      = "homeopatias";
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        // check se foram passados o titulo , conteudo e a forma de envio do e-mail
		if( isset($_POST["title"]) && isset($_POST["conteudo"]) && isset($_POST["sendType"]) ){
            $get="";
            if( isset($_POST["vetGet"]) ){
                $get = json_decode($_POST["vetGet"]);
                $get = objectToArray($get);
            }

            //Prepara o cabeçalho e instancia as variaveis necessarias
			$mensagem = $_POST["conteudo"];
			$titulo   = $_POST["title"];
			$headers = "Content-type: text/plain; charset=utf-8 " .
                    "From: ". $admin->getNome() ." <".$admin->getEmail().">" . "\r\n" .
                    "Reply-To: noreply@homeopatias.com" . "\r\n" .
                    "X-Mailer: PHP/" . phpversion();

            // Se for coordenador , envia e-mail para alunos de sua cidade
            if($admin->getNivelAdmin() === "coordenador"){
                $etapa = isset($get['etapa']) ? $get['etapa'] : 1;

                // Se o tipo do email e´ para todos, buscamos cada aluno matriculado em uma cidade
                // que o coordenador e responsavel e enviamos o e-mail
                if($_POST["sendType"] === "todos"){
                	$textoQuery = "SELECT U.email, U.nome
                				   FROM Usuario U, Aluno A, Matricula M, Cidade C
                				   WHERE U.id = A.idUsuario AND M.chaveAluno = A.numeroInscricao AND
                				    C.idCidade = M.chaveCidade AND M.aprovado IS NULL 
                				    AND C.idCoordenador = ? AND M.etapa = ?";
                	$query = $conexao->prepare($textoQuery);
                	$query->bindParam(1, $admin->getIdAdmin(), PDO::PARAM_INT);
                    $query->bindParam(2, $etapa, PDO::PARAM_INT);
                	$query->execute();
                    $table = "<table>";
                	while($linha = $query->fetch()){
                        $table .= "<tr><td>".$linha['nome']."</td>";
    
                        if( mail($linha["email"],$titulo, $mensagem, $headers) ){
                            $table .= "<td class = 'sucesso'>Enviado</td>";
                        }else{
                            $table .= "<td class = 'warning'>Erro no Envio</td>";
                        }
    
                        $table .= "</tr>";
                	}
                    $table .= "</table>";
                    echo $table;
                }
    
                // Se o tipo do email e´ para selecionados, buscamos cada aluno selecionado e enciamos o e-mail
                if( isset($_POST['selecionados']) && mb_strlen($_POST['selecionados']) > 0 
                    && $_POST['sendType'] === "selecionados"){
                        
                    $ids = split(",", $_POST['selecionados']);
                    $tabela = "<table>";
                    foreach($ids as $id){
                        if( !is_nan( (double)$id) && $id != "" ){ 
                            $aluno = new Aluno("");
                            $aluno->setNumeroInscricao($id);
                            $aluno->recebeAlunoId($host,$db,$usuario,$senhaBD);

                            $textoQuery = "SELECT EXISTS(
                                            SELECT A.numeroInscricao
                                            FROM Aluno A, Matricula M , Cidade C
                                            WHERE A.numeroInscricao = M.chaveAluno AND
                                            M.chaveCidade = C.idCidade AND C.ano = YEAR(CURDATE())
                                            AND C.idCoordenador = ? AMD A.numeroInscricao = ?
                                            ) as existe";

                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(1, $admin->getIdAdmin(), PDO::PARAM_INT);
                            $query->bindParam(2, $id, PDO::PARAM_INT);
                            $query->execute();

                            if($linha = $query->fetch()){
                                if($linha['existe']){
                                    $tabela .= "<tr><td>".$aluno->getNome()."</td>";
    
                                    if(mail($aluno->getEmail(),$titulo, $mensagem, $headers) ){
                                        $tabela .= "<td class = 'sucesso'>Enviado</td>";
                                    }else{
                                        $tabela .= "<td class = 'warning'>Erro no Envio</td>";
                                    }
            
                                    $tabela .= "</tr>";
                                }
                            }

                            
                        }
                    }
                    $tabela .= "</table>";
                    echo $tabela;
                }
                echo "<a href='".$_POST["url-send"]."'>Continuar</a>";
            } // fim dos e-mails de coordenador

            //se for o administrador, envia o email para seus filtros

            else if($admin->getNivelAdmin() === "administrador" && ($admin->getPermissoes() & 1) ){

                // Se o tipo do email e´ para selecionados, buscamos cada aluno selecionado e enviamos o e-mail
                if( isset($_POST['selecionados']) && mb_strlen($_POST['selecionados']) > 0 
                    && $_POST['sendType'] === "selecionados"){
                        
                    $ids = split(",", $_POST['selecionados']);
                    $tabela = "<table>";
                    foreach($ids as $id){
                        if( !is_nan( (double)$id) && $id != "" ){ 
                            $aluno = new Aluno("");
                            $aluno->setNumeroInscricao($id);
                            $aluno->recebeAlunoId($host,$db,$usuario,$senhaBD); 
                            $tabela .= "<tr><td>".$aluno->getNome()."</td>";
    
                            if(mail($aluno->getEmail(),$titulo, $mensagem, $headers) ){
                                $tabela .= "<td class = 'sucesso'>Enviado</td>";
                            }else{
                                $tabela .= "<td class = 'warning'>Erro no Envio</td>";
                            }
    
                            $tabela .= "</tr>";
                        }
                    }
                    $tabela .= "</table>";
                    echo $tabela;
                }

                if( isset($_POST['sendType']) && $_POST['sendType'] === "todos"){
                    $filtroCidade    = null;
                    $queryAnoCidade   = null;

                    if( isset($get["filtro-cidade"] ) ){
                        $filtroCidade    = $get["filtro-cidade"] ;
                    }
                    if( isset($get["filtro-ano"]) ){
                        $filtroAnoCidade = htmlspecialchars( $get["anoCidade"] );
                    }

                    //filtra os resultados para gerar a query com os filtros selecionados na pagina de 
                    //gerenciar aluno, segue a mesma logica
    
                    $textoQuery  =  "SELECT U.email, U.nome FROM Usuario U, 
                                    Aluno A ";
    
                    $textoQuery .=  (mb_strlen($filtroCidade) > 0 || isset($get["filtro-etapa"]) 
                                     && $get["filtro-etapa"] != "0" || mb_strlen($filtroAnoCidade) >0 
                                     && $filtroAnoCidade != "0")
                                                ? ", Cidade C, Matricula M "
                                                : "";
    
                    $textoQuery .=  " WHERE A.idUsuario = U.id ";
    
                    $textoQuery .= ( mb_strlen($filtroCidade) > 0 || isset($get["filtro-etapa"]) 
                                     && $get["filtro-etapa"] != "0" || mb_strlen($filtroAnoCidade) >0 
                                     && $filtroAnoCidade != "0" )
                                                ?"AND M.chaveAluno = A.numeroInscricao  
                                                  AND M.chaveCidade = C.idCidade "
                                                : "";
                    $textoQuery .= mb_strlen($filtroCidade)  > 0 && $filtroCidade != "0"
                                                        ? "
                                                            AND C.nome = :filtrocidade
                                                            AND C.uf = :filtrouf "
                                                        : "" ;
    
                    $textoQuery .= mb_strlen($filtroAnoCidade) > 0 && $filtroAnoCidade != "0" 
                                                           ? " AND C.ano = :anoCidade"
                                                           : "" ;
    
                    // se algum filtro foi enviado, filtra os resultados da consulta
                    $filtroNome = $filtroCpf = $filtroStatus = $filtroNumero = 
                    $filtroDataMin = $filtroDataMax = false;
    
                    // como não há botão para submit, temos que checar se todas as variáveis
                    // existem
                    if(isset($get["filtro-nome"])     || isset($get["filtro-cpf"])      ||
                       isset($get["filtro-status"])   || isset($get["filtro-numero"])   ||
                       isset($get["filtro-data-min"]) || isset($get["filtro-data-max"]) ||
                       isset($get["filtro-cidade"])   || isset($get["filtro-ano"])      ||
                       isset($get["filtro-etapa"])                                         ){
                        $filtroNome    =  htmlspecialchars($get["filtro-nome"]);
                        $filtroCpf     =  htmlspecialchars($get["filtro-cpf"]);
                        $filtroStatus  =  htmlspecialchars($get["filtro-status"]);
                        $filtroNumero  =  htmlspecialchars($get["filtro-numero"]);
                        $filtroDataMin =  htmlspecialchars($get["filtro-data-min"]);
                        $filtroDataMax =  htmlspecialchars($get["filtro-data-max"]);
                        $filtroEtapa      =  htmlspecialchars($get["filtro-etapa"]);
    
                        if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                            // prepara o nome para ser colocado na query
                            $filtroNome    =  "%".$filtroNome."%";
                            $textoQuery .= "  AND U.nome LIKE :nome";
                        }
                        if(isset($filtroCpf) && mb_strlen($filtroCpf) > 0){
                            $textoQuery .= "  AND U.cpf LIKE :cpf";
                        }
                        if(isset($filtroStatus) && mb_strlen($filtroStatus) > 0){
                            $textoQuery .= " AND A.status LIKE :status";
                        }
                        if(isset($filtroNumero) && mb_strlen($filtroNumero) > 0) {
                            if(!is_nan($filtroNumero)){
                                $textoQuery .= " AND A.numeroInscricao = :numInsc";
                            }
                        }
                        if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                            $textoQuery .= " AND CAST(U.dataInscricao AS Date) >= ";
                            $textoQuery .= "CAST(:dataMin as Date)";
                        }
                        if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                            $textoQuery .= " AND CAST(U.dataInscricao AS Date) <= ";
                            $textoQuery .= "CAST(:dataMax as Date)";
                        }
                        if(isset($filtroEtapa) && mb_strlen($filtroEtapa) > 0 && 
                            !is_nan($filtroEtapa) && $filtroEtapa != "0"){
                            $textoQuery .= " AND M.etapa LIKE :filtroetapa ";
                        }
    
    
                    }
                    $query = $conexao->prepare($textoQuery);

                    if(isset($filtroNome) && mb_strlen($filtroNome) > 0){
                        $query->bindParam(":nome", $filtroNome);
                    }
                    if(isset($filtroCpf) && mb_strlen($filtroCpf) > 0){
                        // remove os '.' e '-' para comparar com o cpf do bd
                        $filtroCpf = str_replace(".","",$filtroCpf);
                        $filtroCpf = str_replace("-","",$filtroCpf);

                        $query->bindParam(":cpf", $filtroCpf);
                    }
                    if(isset($filtroStatus) && mb_strlen($filtroStatus) > 0){
                        $query->bindParam(":status", $filtroStatus);
                    }
                    if(isset($filtroNumero) && mb_strlen($filtroNumero) > 0) {
                        if(!is_nan($filtroNumero)){
                            $query->bindParam(":numInsc", $filtroNumero);
                        }
                    }
                    if(isset($filtroDataMin) && mb_strlen($filtroDataMin) > 0){
                        $query->bindParam(":dataMin" , $filtroDataMin);
                    }
                    if(isset($filtroDataMax) && mb_strlen($filtroDataMax) > 0){
                        $query->bindParam(":dataMax" , $filtroDataMax);
                    }
                    if(isset($filtroAnoCidade) && mb_strlen($filtroAnoCidade) > 0 ){
                        $query->bindParam(":anoCidade" , $filtroAnoCidade);

                    }
                    if(isset($filtroCidade) && mb_strlen($filtroCidade) > 0 ){
                        $vetorCidade = explode("/", $filtroCidade);
                        $nomeCidade = trim($vetorCidade[0]);
                        $ufCidade   = trim($vetorCidade[1]);
                        $query->bindParam(":filtrocidade", $nomeCidade);
                        $query->bindParam(":filtrouf"    , $ufCidade);
                    }
                    if(isset($filtroEtapa) && mb_strlen($filtroEtapa) > 0  &&
                         $filtroEtapa != "0"){
                         $query->bindParam(":filtroetapa",$filtroEtapa);
                    }

                    $query->execute();
                    $table = "<table>";
                    while($linha = $query->fetch()){
                        $table .= "<tr><td>".$linha['nome']."</td>";
    
                        if( mail($linha["email"],$titulo, $mensagem, $headers) ){
                            $table .= "<td class = 'sucesso'>Enviado</td>";
                        }else{
                            $table .= "<td class = 'warning'>Erro no Envio</td>";
                        }
    
                        $table .= "</tr>";
                    }
                    $table .= "</table>";
                    echo $table;
                }

                echo "<a href='".$_POST["url-send"]."'>Continuar</a>";
            }
		}
	}

	else{
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="0; url=../index.php">
        <script type="text/javascript">
            window.location = "../index.php";
        </script>
        <?php
                die();
    }


    // Codigo para tornar um stdObject em array, retirado do link:
    //http://goo.gl/8a39Rq
    function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        }
        else {
            // Return array
            return $d;
        }
    }

?>