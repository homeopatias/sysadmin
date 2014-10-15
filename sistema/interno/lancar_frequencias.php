<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include('modulos/head.php'); ?>
        <title>Frequências - Homeopatias.com</title>
        <script src='./jquery/colResizable.min.js'></script>
        <script>
            $(document).ready(function(){

                // permite redimensionar as colunas da tabela
                $('#alunos').colResizable({
                    liveDrag: true,
                    minWidth: 60
                });

                checaTamanhoTela();
            }); 

            //------------Checa se tamanho minimo da tela é o tamanho minimo do css
            function checaTamanhoTela(){
                tamanhoTela = $(window).width();

                if (tamanhoTela < 700) {
                    $("table").colResizable({
                        disable:true
                    }); 
                    $(".flip-scroll th").css("width","150px");
                }
                else {
                    $("table").colResizable({
                        disable:false
                    }); 
                }
            }

            //----Checa se ao redimencionar a tela atingiu o tamanho minimo da tela
            $(window).resize(function() {
                checaTamanhoTela();
            });
        </script>
    </head>
    <body>
        <?php

            include('modulos/navegacao.php');

            // mensagem a ser exibida acima da listagem de alunos de cada aula, caso seja necessário
            $mensagem = '';

            if(isset($_GET['erro'])){
                $mensagem = $_GET['erro'];
            }

            // exibe alunos apenas para coordenadores logados
            if(isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Administrador
               && unserialize($_SESSION['usuario'])->getNivelAdmin() === 'coordenador'){

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
                $db      = 'homeopatias';
                try{
                    $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                }catch (PDOException $e){
                    echo $e->getMessage();
                }

                // recebemos o id da aula cujas frequências estão sendo inseridas
                $idAula = $_GET['aula'];

                if(!isset($idAula) || !preg_match("/^[0-9]*$/", $idAula)){
                    // id de aula inválido, redirecionamos o usuário de volta
                    // com uma mensagem de erro
        ?>
                <meta http-equiv="refresh"
                      content="0; url=selecao_turma_frequencias.php?erro=Aula inválida!">
                <script type="text/javascript">
                    window.location = "selecao_turma_frequencias.php?erro=Aula inválida!";
                </script>
        <?php
                    die();
                }

                // se o usuário chegou até aqui através de um formulário, registra as frequências
                if(isset($_POST['submit'])){
                    $alunos = $_POST['alunos'];
                    $presencas = $_POST['presencas'];

                    // criamos a query para inserir todas as presenças
                    $textoQuery = 'INSERT INTO Frequencia (chaveAluno, chaveAula, presenca) VALUES';
                    $numAlunos = count($alunos);

                    // loop nos alunos para determinar quem está presente e quem não está,
                    // preenchendo a query de acordo
                    for($i = 0; $i < $numAlunos; $i++) {
                        if(in_array($alunos[$i], $presencas)) {
                            // aluno presente
                            $textoQuery .= ' (?,?,1)';
                        } else {
                            // aluno ausente
                            $textoQuery .= ' (?,?,0)';
                        }
                        if($i < $numAlunos - 1){
                            $textoQuery .= ',';
                        }
                    }

                    $textoQuery .= ' ON DUPLICATE KEY UPDATE presenca = VALUES(presenca)';

                    $query = $conexao->prepare($textoQuery);

                    // loop nos alunos para fazer o binding dos valores no PDO
                    for($i = 0; $i < $numAlunos; $i++) {
                        $indice = ($i * 2) + 1;
                        $query->bindParam($indice    , $alunos[$i], PDO::PARAM_INT);
                        $query->bindParam($indice + 1, $idAula, PDO::PARAM_INT);
                    }

                    $sucesso = $query->execute();
                    
                    if(!$sucesso) {
                        $mensagem = 'Erro no envio dos dados de frequências';
                    } else {
                        // redirecionamos o usuário para que não haja reenvio do formulários

                        $url = 'lancar_frequencias.php?aula='.$idAula.'&sucesso=true';
        ?>

        <!-- redireciona o usuário para a lista de alunos -->
        <meta http-equiv="refresh" content=<?= "\"0; url=". $url . "\"" ?>>
        <script type="text/javascript">
            window.location = <?= $url ?>;
        </script>

        <?php
                    }
                }

                $textoQuery  = 'SELECT Al.numeroInscricao, U.nome, F.presenca FROM Usuario U
                                INNER JOIN Aluno Al ON Al.idUsuario = U.id LEFT JOIN Frequencia F ON
                                F.chaveAluno = Al.numeroInscricao AND F.chaveAula = ?
                                INNER JOIN Matricula M ON M.chaveAluno = Al.numeroInscricao
                                INNER JOIN Cidade C ON M.chaveCidade = C.idCidade INNER JOIN
                                Aula A ON A.chaveCidade = C.idCidade WHERE C.idCidade =
                                (SELECT chaveCidade FROM Aula WHERE idAula = ?) AND M.etapa =
                                (SELECT etapa FROM Aula WHERE idAula = ?) AND Al.status = \'inscrito\'
                                AND A.idAula = ? ORDER BY U.nome';

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(1, $idAula);
                $query->bindParam(2, $idAula);
                $query->bindParam(3, $idAula);
                $query->bindParam(4, $idAula);
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $numeroRegistros = 0;
                $tabela = "";

                while ($linha = $query->fetch()){

                    // listamos os dados de cada aluno matriculado na cidade/etapa
                    $tabela .= '<tr>';
                    $tabela .= '    <td class="insc">';
                    $tabela .= htmlspecialchars($linha["numeroInscricao"])  .'</td>';
                    $tabela .= '    <td class="nome">';
                    $tabela .= htmlspecialchars($linha["nome"])             .'</td>';

                    $tabela .= '    <td><input type="hidden" name="alunos[]" id="';
                    $tabela .= htmlspecialchars($linha["numeroInscricao"]);
                    $tabela .= '" value="' . htmlspecialchars($linha["numeroInscricao"]);
                    $tabela .= '"> <input type="checkbox" name="presencas[]" id="';
                    $tabela .= htmlspecialchars($linha["numeroInscricao"]);
                    $tabela .= '" value="' . $linha['numeroInscricao'] . '"';
                    $tabela .= (!is_null($linha['presenca']) && $linha['presenca']) ? "checked " : '';
                    $tabela .= '></td>';

                    $numeroRegistros++;
                }
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <a style="float:right" href="selecao_turma_frequencias.php">
                        Voltar para seleção de aula
                    </a>
                    <h1>Lançar frequências</h1><br>    
                    <?php 
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                        if(isset($_GET['sucesso'])) {
                            echo "<p class=\"sucesso\">Frequências enviadas com sucesso</p>";
                        }
                    ?>
                    <p>Um aluno não marcado como presente é considerado ausente</p>
                    <br><br>
                    <?php if($numeroRegistros !== 0){ ?>
                    <form action method="POST">
                        <input type="hidden" id="aula" name="aula" value=<?= "\"" . $idAula . "\"" ?>>
                        <div class="flip-scroll">
                            <div class="wrapper-scroll">
                                <table class="table table-bordered table-striped" id="alunos">
                                    <thead style="background-color: #AAA">
                                        <tr>
                                            <th>Número de inscrição</th>
                                            <th>Nome do aluno</th>
                                            <th>Presente?</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?= $tabela ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button type="submit" name="submit" value="submit"
                                class="btn btn-primary pull-right">
                            Enviar dados de frequência
                        </button>
                        <br>
                    </form>
                    <?php } else { ?>
                    <h3>Nenhum aluno matrículado nessa cidade para essa etapa esse ano</h3>
                    <?php } ?>                    
                </section>
            </div>
        </div>
        <?php
            }else{
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="0; url=index.php">
        <script type="text/javascript">
            window.location = "index.php";
        </script>
        <?php
                die();
            }
            include("modulos/rodape.php");
        ?>
    </body>
</html>