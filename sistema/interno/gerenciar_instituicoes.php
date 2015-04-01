<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php

            include("modulos/head.php");
            require_once("entidades/Administrador.php");

            $logado = isset($_SESSION['usuario']) ? unserialize($_SESSION['usuario']) : -1;

            if ($logado instanceof Administrador && $logado->getNivelAdmin() === "administrador" &&
               ( 8 & $logado->getPermissoes()) ) {

                // lemos as credenciais do banco de dados
                $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                $dados = json_decode($dados, true);

                foreach($dados as $chave => $valor) {
                    $dados[$chave] = str_rot13($valor);
                }

                $host    = $dados["host"];
                $usuario = $dados["nome_usuario"];
                $senhaBD = $dados["senha"];

                // cria conexão com o banco para ser usada ao longo da página
                $conexao = null;
                $host    = "localhost";
                $db      = "homeopatias";
                try {
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }


                $mensagem = "";
                $sucesso = false;

                // caso tenha-se chegado aqui através de um formulário, alteramos os dados da instituição
                if (isset($_POST['submit'])) {
                    $instituicoes = array('atenemg', 'conahom');

                    for ($i = 0; $i < count($instituicoes); $i++) {
                        $valorInscricao = $_POST['insc-'           . $instituicoes[$i]];
                        $valorAnuidade  = $_POST['anuidade-'       . $instituicoes[$i]];
                        $inicioInsc     = $_POST['inicio-insc-'    . $instituicoes[$i]];
                        $fimInsc        = $_POST['fim-insc-'       . $instituicoes[$i]];
                        $ano            = $_POST['ano-'            . $instituicoes[$i]];

                        $inscricaoValida  = isset($valorInscricao) &&
                                            preg_match("/^[0-9]*\.?[0-9]+$/", $valorInscricao);
                        $anuidadeValida   = isset($valorAnuidade) &&
                                            preg_match("/^[0-9]*\.?[0-9]+$/", $valorAnuidade);
                        $inicioInscValido = isset($inicioInsc) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $inicioInsc);
                        $fimInscValido    = isset($fimInsc) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $fimInsc);
                        $anoValido        = isset($ano) && preg_match("/^\d{4}$/", $ano);

                        if ($inscricaoValida && $anuidadeValida && $inicioInscValido &&
                            $fimInscValido && $anoValido) {
                            $textoQuery = "UPDATE Instituicao SET valorInscricao = ?, valorAnuidade = ?,
                                                                  inicioInsc = ?, fimInsc = ?, ano = ?
                                                  WHERE nome = ?";
                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(1, $valorInscricao);
                            $query->bindParam(2, $valorAnuidade);
                            $query->bindParam(3, $inicioInsc);
                            $query->bindParam(4, $fimInsc);
                            $query->bindParam(5, $ano);
                            $query->bindParam(6, $instituicoes[$i]);

                            $sucesso = $query->execute();

                            if (!$sucesso) {
                                if ($mensagem !== ""){ $mensagem = " e " . lcfirst($mensagem); }
                                $mensagem = "Erro de banco de dados para a instituição " .
                                             $instituicoes[$i] . $mensagem;
                            } else {
                                $mensagem = "Dados atualizados com sucesso!";
                            }

                        } else if(!$inscricaoValida) {
                            if ($mensagem !== ""){ $mensagem = " e " . lcfirst($mensagem); }
                            $mensagem = "Valor de inscrição inválido para a instituição " .
                                         $instituicoes[$i] . $mensagem;
                        } else if(!$anuidadeValida) {
                            if ($mensagem !== ""){ $mensagem = " e " . lcfirst($mensagem); }
                            $mensagem = "Valor de anuidade inválido para a instituição " .
                                         $instituicoes[$i] . $mensagem;
                        } else if(!$inicioInscValid) {
                            if ($mensagem !== ""){ $mensagem = " e " . lcfirst($mensagem); }
                            $mensagem = "Data de início de inscrição inválida para a instituição " .
                                         $instituicoes[$i] . $mensagem;
                        } else if(!$fimInscValido) {
                            if ($mensagem !== ""){ $mensagem = " e " . lcfirst($mensagem); }
                            $mensagem = "Data de fim de inscrição inválida para a instituição " .
                                         $instituicoes[$i] . $mensagem;
                        } else if(!$anoValido) {
                            if ($mensagem !== ""){ $mensagem = " e " . lcfirst($mensagem); }
                            $mensagem = "Ano inválido para a instituição " .
                                         $instituicoes[$i] . $mensagem;
                        }

                    }
                }

        ?>
        <title>Instituições - Homeopatias.com</title>
        <!-- polyfill para funcionalidades do HTML5 -->
        <script src="./webshim-1.14.5/polyfiller.js"></script>
        <script>
            // usamos um polyfill para que os campos de data e hora funcionem mesmo
            // em navegadores que não implementem essas funcionalidades (você sabe quais)

            webshims.activeLang("pt-BR");
            webshims.setOptions('waitReady', false);
            webshims.setOptions('forms-ext', {types: 'date', replaceUI: true});
            webshims.polyfill('forms forms-ext');
        </script>
    </head>
    <body>
        <?php include("modulos/navegacao.php"); ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h2 style="font-weight:bold">Editar Instituições</h2>
                    <?php
                        if ($mensagem) {
                    ?>
                    <p class=<?= $sucesso ? "\"sucesso\"" : "\"warning\"" ?>><?= $mensagem ?></p>
                    <?php
                        }
                    ?>
                    <form method="POST">
        <?php
            $textoQuery = "SELECT idInstituicao, nome, valorInscricao, valorAnuidade, inicioInsc, fimInsc, ano
                           FROM Instituicao";
            $query = $conexao->prepare($textoQuery);
            $query->setFetchMode(PDO::FETCH_ASSOC);
            $query->execute();

            while ($linha = $query->fetch()) {
                $nome = htmlspecialchars($linha['nome']);
        ?>
                        <h1><?= strtoupper($nome) ?></h1>
                        <div style="float: left">
                            <label for=<?= "\"insc-" . $nome . "\"" ?>>Valor da inscrição (R$)</label>
                            <br>
                            <input type="number" name=<?= "\"insc-" . $nome . "\"" ?> min="0"
                            value=<?= number_format(htmlspecialchars($linha['valorInscricao']), 2) ?>>
                        </div>
                        <div style="float: left; margin-left: 20px">
                            <label for=<?= "\"anuidade-" . $nome . "\"" ?>>Valor da anuidade (R$)</label>
                            <br>
                            <input type="number" name=<?= "\"anuidade-" . $nome . "\"" ?> min="0"
                            value=<?= number_format(htmlspecialchars($linha['valorAnuidade']), 2) ?>>
                        </div>
                        <div style="float: left; margin-left: 20px">
                            <label for=<?= "\"inicio-insc-" . $nome . "\"" ?>>Início do período de inscrição</label>
                            <br>
                            <input type="date"   name=<?= "\"inicio-insc-" . $nome . "\"" ?>
                            value=<?= htmlspecialchars($linha['inicioInsc']) ?>>
                        </div>
                        <div style="float: left; margin-left: 20px">
                            <label for=<?= "\"fim-insc-" . $nome . "\"" ?>>Fim do período de inscrição</label>
                            <br>
                            <input type="date"   name=<?= "\"fim-insc-" . $nome . "\"" ?>
                            value=<?= htmlspecialchars($linha['fimInsc']) ?>>
                        </div>
                        <div style="float: left; margin-left: 20px">
                            <label for=<?= "\"ano-insc-" . $nome . "\"" ?>>Ano referente</label>
                            <br>
                            <input type="text"   name=<?= "\"ano-" . $nome . "\"" ?>
                                   value=<?= htmlspecialchars($linha['ano']) ?>>
                        </div>
                        <div style="clear: both"></div>
        <?php
            }
        ?>
                        <br>
                        <input type="submit" class="btn btn-primary" name="submit" value="Atualizar dados">
                    </form>
                </section>
            </div>
        </div>
        <?php
            } else {
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="0; url=index.php">
        <script type="text/javascript">
            window.location.href = "index.php";
        </script>
        <?php
                die();
            }

            include("modulos/rodape.php");
        ?>
    </body>
</html>