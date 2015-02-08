<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Homeopatias.com - recuperar dados da conta</title>
        <script>
            $(document).ready(function(){
                
            });
        </script>
    </head>
    <body>
        <?php


            //inclui a navegaçao no topo do site
            include("modulos/navegacao.php");

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

            //se chegou aqui através de um formulário, tenta recuperar senhas
            if( isset( $_POST["submit"] ) ){

                $mensagem = "";
                $sucesso  = "";

								$email  = isset( $_POST["email"] ) ? $_POST["email"] : false;

								$emailValido  = isset($email) && mb_strlen($email, 'UTF-8') <= 100 &&
                                    preg_match("/^.+\@.+\..+$/", $email);

            // -------------------------------------------------------------------

                //se e-mail forem válido, checamos as contas que possuem esta combinação
                if($emailValido){

                    $textoQuery = "SELECT login FROM Usuario 
                                    WHERE email=:email";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(":email" , $email);

                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if($query->rowCount() > 0 ){
                        //Geramos uma senha aleatória de 10 digitos alfanuméricos
                        $alfanumerico = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o",
                            "p","q","r","s","t","u","v","w","x","y","z","1","2","3","4","5","6","7","8","9",
                            "0","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S",
                            "T","U","V","W","X","Y","Z");

                        //Pegamos 10 indices aleatórios e armazenamos em um array
                        $randKeys = array_rand($alfanumerico , 10);
                        

                        $senhaAleatoria = "";

                        // Armazenamos os logins de usuário em um array
                        $userNames = array();

                        while( $linha = $query->fetch() ){
                            array_push($userNames, $linha["login"]);
                        }
                        

                        //O array é varrido retornando os valores dos indices aleatórios
                        for($i = 0; $i < 10 ; $i++){
                            $senhaAleatoria .= $alfanumerico[ $randKeys[$i] ];
                        }

                        require_once(dirname(__FILE__)."/phpass-0.3/PasswordHash.php");

                        $hasher = new PasswordHash(8, false);
                        $hashSenha = $hasher->HashPassword($senhaAleatoria);

                        $conexao->beginTransaction();

                        //Altera as senhas das contas de usuário
                        $textoQuery = "UPDATE Usuario 
                                SET senha=:senha
                                WHERE email=:email";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(":senha" , $hashSenha);
                        $query->bindParam(":email" , $email);


                        $sucesso = $query->execute();

                        if($sucesso){
                            // Implementar envio de e-mail e notificação

                            //Dados do e-mail 
                            $conteudo = "";
                            $titulo   = "Recuperação de dados da conta";
                            $headers = "Content-type: text/plain; charset=utf-8 " .
                                        "From: Homeobras" . "\r\n" .
                                        "Reply-To: noreply@homeopatias.com" . "\r\n" .
                                        "X-Mailer: PHP/" . phpversion();

                            // Prepara o conteudo do e-mail

                            $conteudo .= "Recebemos um pedido de recuperação de conta dos usuários :\n\n";
                            for($i = 0; $i < count($userNames); $i++){
                                $conteudo .= "- ".$userNames[$i]."\n";
                            }

                            $conteudo .= "\nSua senha para acessar esta(s) conta(s) foi alterada para a senha";
                            $conteudo .= ": ".$senhaAleatoria." .";
                            
                            $conteudo .= "\n\nEsta senha foi gerada aleatóriamente pelo nosso sistema, pedimos que altere";
                            $conteudo .= " esta senha na próxima vez em que você acessar nosso sistema.";

                            $conteudo .= "\n\nCaso não consigo acessar nosso sistema, pedimos que tente recuperar novamente ";
                            $conteudo .= "a senha ou entre em contato conosco pelos telefones em nosso site.";

                            // ---------------------------------------------------

                            $enviou = mail($email , $titulo , $conteudo, $headers);
                            

                            if($sucesso && $enviou){
                                $conexao->commit();
                                $mensagem = "Os dados foram recuperados com sucesso e enviado para seu e-mail";
                            }else{
                                $conexao->rollBack;
                                $mensagem = "Não foi possível buscar os dados necessário, tente novamente";
                                $sucesso = false;
                            }
                        }else{
                            $mensagem = "Erro ao retornar dados da conta, tente novamente.";
                            $conexao->rollBack();
                        }

                    }
                    else{
                        $mensagem = "Não há contas registradas com os dados fornecidos!";
                        $sucesso  = false;
                    }

                }

            }

        ?>

        <!--  inicio dos formularios de recuperação -->
        <div class="col-xs-12 vertical-center" style="height:50%">
            <div class="center-block col-sm-6 no-float" 
                style="max-width: 820px">
                <div class="conteudo">
                    <?php 
                        if(isset($_POST["submit"])  ){
                            //Imprime mensagem com o resultado
                            echo "<h4 class=\"".($sucesso ? "sucesso" : "warning")."\">".$mensagem."</h4>";
                        }
                    ?>

                    <h3>Recuperaçao de usuario e senha:</h3>

                    <p>Por favor, entre com seu e-mail no campo abaixo e lhe enviaremos um e-mail contendo
                        seu(s) nome(s) de usuário e uma senha gerada pelo sistema para ser usada no seu próximo 
                        login</p>
                    
                    <p class="warning">A senha gerada pelo sistema substituirá sua senha atual e será necessária 
                    para seu próximo login no sistema, tenha certeza de que seu e-mail da(s) conta(s) registrada(s) 
                     ainda está
                     em funcionamento para garantir o recebimento das senhas corretas em sua caixa de entrada.</p>

                    <form action="#" method="POST">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required
                               placeholder="E-mail"
                               title="Insira um e-mail válido"
                               class="form-control">

                        <br>

                        <div align="center">
                            <button type="submit" name="submit" value="submit" id="recuperar"
                            class="btn btn-primary pull-right">
                            Recuperar conta
                            </button>   
                        </div>
                    </form>
                </div>
            </div>
        </div>

            
        <?php
            include("modulos/rodape.php");

        ?>
    </body>
</html>
