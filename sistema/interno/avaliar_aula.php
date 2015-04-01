<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
	session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Bem-vindo - Homeopatias.com</title>
    </head>
    <body>
        <?php
            require_once("entidades/Aluno.php");

        	// mensagem a ser exibida acima do formulário de avaliação, caso seja necessário
        	$mensagem = "";

            // representa se uma aula já foi avaliada com sucesso ou não
            $sucesso = false;

            if(isset($_GET["erro"])) {
                $mensagem = $_GET["erro"];
            }

            // mostra essa tela apenas para alunos logados
            if(isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Aluno) {

                // lemos as credenciais do banco de dados
                $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                $dados = json_decode($dados, true);

                foreach($dados as $chave => $valor) {
                    $dados[$chave] = str_rot13($valor);
                }

                // cria conexão com o banco para uso ao longo da página
                $conexao   = null;
                $host      = $dados["host"];
                $usuario   = $dados["nome_usuario"];
                $senhaBD   = $dados["senha"];
                $db      = "homeopatias";
                try {
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }

                // se o usuario chegou aqui atraves de um formulário, tenta fazer a avaliação
                if (isset($_POST["submit"])){
                    $idAula = $_POST['idAula'];
                    $nota   = $_POST['nota'];

                    $idAulaValido = isset($idAula) && preg_match("/^[0-9]+$/", $idAula);
                    $notaValida   = isset($nota) && ($nota == 0 || $nota == 1 || $nota == 2 ||
                                    $nota == 3);

                    if($idAulaValido && $notaValida) {
                        $textoQuery  = "SELECT COUNT(F.jaAvaliou) as quant, A.nota FROM Frequencia F
                                        INNER JOIN Aula A ON A.idAula = F.chaveAula WHERE A.idAula = ?
                                        AND F.jaAvaliou = 1";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAula);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $numAvaliacoes = 0;
                        $notaAtual = 0;

                        if($linha = $query->fetch()) {
                            $numAvaliacoes = $linha['quant'];
                            $notaAtual = is_null($linha['nota']) ? 0 : $linha['nota'];
                        } else {
                            echo "Presença ou aula não encontrada no sistema, erro";
                            die();
                        }

                        // agora baseado nos dados recebidos, calculamos a média

                        // ótima = 100%, boa = 66%, regular = 33%, péssima = 0%
                        $notaPorcentagem = (100/3) * $nota;

                        // calculamos a nota final usando um algoritmo que não
                        // precisa conhecer todas as notas enviadas anteriormente,
                        // apenas a soma delas e quantas elas são
                        // (assim podemos manter as notas que cada aluno deu em sigilo)
                        $notaFinal = ($notaAtual * $numAvaliacoes) + $notaPorcentagem;
                        
                        $notaFinal = $notaFinal / ($numAvaliacoes + 1);

                        $textoQuery  = "UPDATE Frequencia SET jaAvaliou = 1 WHERE chaveAula = ? AND ";
                        $textoQuery .= "chaveAluno = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $idAula);
                        $query->bindParam(2, unserialize($_SESSION['usuario'])->getNumeroInscricao());
                        $query->execute();

                        $textoQuery  = "UPDATE Aula SET nota = ? WHERE idAula = ?";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $notaFinal);
                        $query->bindParam(2, $idAula);
                        $query->execute();

                        $sucesso = true;

                    } else {
                        echo "Erro na avaliação";
                        die();
                    }
                }

                $idAula = isset($_GET['idAula']) ? $_GET['idAula'] : null;

                $aluno = unserialize($_SESSION['usuario']);

                if(is_null($idAula)) {
                    // se não foi recebido um id de aula, provavelmente o usuário acabou
                    // de avaliar uma aula (caso contrário houve algum erro/manipulação de
                    // valor), portanto procuramos outras aulas que o aluno ainda tenha que
                    // avaliar. Caso não haja nenhuma, redirecionamos o aluno para o index

                    $textoQuery  = "SELECT F.chaveAula FROM Frequencia F INNER JOIN Aula A ON 
                                    A.idAula = F.chaveAula WHERE chaveAluno = ? AND presenca = 1 
                                    AND jaAvaliou = 0 ORDER BY A.data ASC";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $aluno->getNumeroInscricao(),
                                         PDO::PARAM_INT);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if($linha = $query->fetch()) {
                        // encontramos uma aula que não foi avaliada, o aluno deve avaliar essa aula
                        $idAula = $linha['chaveAula'];
                    } else {
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="index.php?sucessoAval=true">
        <script type="text/javascript">
            window.location.href = "index.php?sucessoAval=true";
        </script>
        <?php
                    }
                } else if(!preg_match("/^[0-9]+$/", $idAula)) {
                    echo "Erro na obtenção de aula a avaliar";
                    die();
                }

                // procuramos os dados da aula que o aluno vai avaliar

                $textoQuery  = "SELECT UNIX_TIMESTAMP(Au.data) as data, Au.etapa, P.nome as nomeP, 
                                C.nome as nomeC, C.UF FROM Frequencia F INNER JOIN Aula Au ON 
                                F.chaveAula = Au.idAula INNER JOIN Cidade C ON C.idCidade = 
                                Au.chaveCidade INNER JOIN Administrador A ON Au.idProfessor 
                                = A.idAdmin INNER JOIN Usuario P ON P.id = A.idUsuario 
                                WHERE F.chaveAluno = ? AND F.chaveAula = ? AND F.presenca = 1 
                                AND F.jaAvaliou = 0";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1, $aluno->getNumeroInscricao());
                $query->bindParam(2, $idAula);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $data = '';
                $etapa = -1;
                $nomeProfessor = '';
                $nomeCidade = '';

                if($linha = $query->fetch()) {
                    $data = date('d/m/Y, \o\c\o\r\r\i\d\a à\s H \h\o\r\a\s \e i \m\i\n\u\t\o\s',
                                 htmlspecialchars($linha['data']));
                    $etapa = htmlspecialchars($linha['etapa']);
                    $nomeProfessor = htmlspecialchars($linha['nomeP']);
                    $nomeCidade = htmlspecialchars($linha['nomeC'] . '/' . $linha['UF']);
                } else {
                    echo "Erro na obtenção de aula a avaliar";
                    die();
                }
        ?>
        <div class="col-sm-12" style="margin-top: 5%">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0) {
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                    ?>
                    <a class="pull-right btn btn-danger" href="index.php">
                        Avaliar depois
                    </a>
                    <br><br>
                    <?php
                        if($sucesso) {
                    ?>
                    <p class="sucesso">Obrigado pela sua avaliação! Parece que você tem mais uma aula
                                       que pode avaliar</p>
                    <?php
                        }
                    ?>
                    <h4>Para que possamos oferecer um curso cada vez melhor, por favor avalie a aula
                        relacionada abaixo!</h4>
                    <br>
                    <h4><?= $nomeCidade . " - " . $etapa . "ª etapa - Aula do dia " . $data ?></h4>
                    <h4>Aula do(a) professor(a) <?= $nomeProfessor ?></h4>
                    <br>
                    <h4>Na sua opinião, essa aula foi:</h4>
                    <br>
                    <form action="avaliar_aula.php" method="POST"
                          style="margin:auto; width:150px; padding: 10px; padding-left:25px;
                                 border: 2px solid #888">
                        <input type="hidden" name="idAula" value=<?= '"' . $idAula . '"' ?>>
                        <div class="form-group">
                            <input type="radio" name="nota" id="otima" required value="3">
                            <label for="otima">Ótima</label>
                            <br>
                            <input type="radio" name="nota" id="boa" value="2">
                            <label for="boa">Boa</label>
                            <br>
                            <input type="radio" name="nota" id="regular" value="1">
                            <label for="regular">Regular</label>
                            <br>
                            <input type="radio" name="nota" id="pessima" value="0">
                            <label for="pessima">Péssima</label>
                            <br>
                        </div>
                        <br>
                        <button type="submit" name="submit" value="submit"
                                class="btn btn-success">Avaliar aula</button>
                    </form>
                    <br>
                </section>
            </div>
        </div>

        <?php
            }else{
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