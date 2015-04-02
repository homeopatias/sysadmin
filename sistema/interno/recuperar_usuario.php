<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Homeopatias.com - Recuperar usuário</title>
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

            //se chegou aqui através de um formulário, tenta recuperar usuários
            if( isset( $_POST["submit"] ) ){

                $mensagem = "";
                $sucesso  = "";

                $email  = isset( $_POST["email"] ) ? $_POST["email"] : false;

                $emailValido  = isset($email) && mb_strlen($email, 'UTF-8') <= 100 &&
                                preg_match("/^.+\@.+\..+$/", $email);

            // -------------------------------------------------------------------


                //se e-mail for válido, checamos as contas que possuem este e-mail
                if($emailValido){

                    $textoQuery = "SELECT login FROM Usuario 
                                    WHERE email=:email";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(":email" , $email);

                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if($query->rowCount() > 0 ){
                        // Armazenamos os logins de usuário em um array
                        $userNames = array();

                        while( $linha = $query->fetch() ){
                            array_push($userNames, $linha["login"]);
                        }
                    
                        // Envia e-mail e notificação

                        //Dados do e-mail 
                        $conteudo = "";
                        $titulo   = "Recuperação de dados da conta";
                        $headers = "Content-type: text/plain; charset=utf-8 " .
                                    "From: Homeobras" . "\r\n" .
                                    "Reply-To: noreply@homeopatias.com" . "\r\n" .
                                    "X-Mailer: PHP/" . phpversion();

                        // Prepara o conteudo do e-mail

                        $conteudo .= "Recebemos um pedido de recuperação dos usuários :\n\n";
                        for($i = 0; $i < count($userNames); $i++){
                            $conteudo .= "- ".$userNames[$i]."\n";
                        }

                        $conteudo .= "\n\nCaso não consiga acessar nosso sistema, pedimos que tente recuperar ";
                        $conteudo .= "sua senha ou entre em contato conosco pelos telefones em nosso site.";

                        // ---------------------------------------------------

                        $enviou = mail($email , $titulo , $conteudo, $headers);
                        

                        if($sucesso && $enviou){
                            $conexao->commit();
                            $mensagem = "Os dados foram recuperados com sucesso e enviados para seu e-mail";
                        }else{
                            $conexao->rollBack();
                            $mensagem = "Não foi possível buscar os dados necessários, tente novamente";
                            $sucesso = false;
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

                    <h3>Recuperaçao de usuário:</h3>


                    <p>Por favor, entre com seu e-mail no campo abaixo e lhe enviaremos um e-mail contendo
                        seu(s) nome(s) de usuário</p>
                    
                    <p class="warning">Tenha certeza de que seu e-mail da(s) conta(s) registrada(s) 
                     ainda está em funcionamento para garantir o recebimento das senhas corretas em sua caixa de entrada.</p>

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
                        <br>
                    </form>
                </div>
            </div>
        </div>

            
        <?php
            include("modulos/rodape.php");

        ?>
    </body>
</html>