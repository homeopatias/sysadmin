<?php
    ini_set('default_charset', 'utf-8'); 
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Trabalhos - Homeopatias.com</title>
        <script src="./jquery/jquery.tablesorter.min.js"></script>
        <script src="./jquery/colResizable.min.js"></script>
        <!-- polyfill para funcionalidades do HTML5 -->
        <script src="./webshim-1.14.5/polyfiller.js"></script>
        <script>
            $(document).ready(function(){
                // usamos um polyfill para que os campos de data e hora funcionem mesmo
                // em navegadores que não implementem essas funcionalidades (você sabe quais)

                // permite redimensionar as colunas da tabela
                $("#trabalhos").colResizable({
                    liveDrag: true,
                    minWidth: 60
                });

                // torna a tabela ordenavel pelas colunas

                // parser para ordenar datas
                $.tablesorter.addParser({
                    id: "datetime",
                    is: function(s) {
                        return false; 
                    },
                    format: function(s,table) {
                        s = s.replace(/\-/g,"/");
                        s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
                        return $.tablesorter.formatFloat(new Date(s).getTime());
                    },
                    type: "numeric"
                });

                $("#trabalhos").tablesorter({ headers: {
                    2 : { sorter: false },
                    3 : { sorter: false },
                    4 : { sorter: false },
                    5 : { sorter: false }
                }});

                $("#ano").change(function(){
                    $("#busca-ano").submit();
                });

                // passa os dados do href para o modal de justificativa quando
                // necessário
                $("#modal-justificativa").on('show.bs.modal', function(e) {
                    $(this).find('#idAula').val($(e.relatedTarget).data('chaveaula'));
                });
            });
        </script>
    </head>
    <body>
        <?php

            include('modulos/navegacao.php');
            $mensagem = '';

            if(isset($_GET["erro"])){
                $mensagem = $_GET['erro'];
            }
            require_once("entidades/Aluno.php");

            //Exibe frequência somente para alunos insritos
            if(isset($_SESSION['usuario']) && unserialize($_SESSION['usuario']) instanceof Aluno &&
                unserialize($_SESSION['usuario'])->getStatus() === 'inscrito'){

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

                $justificativaEnviada = false;

                // checamos se recebemos dados de formulário
                if (isset($_POST['submit'])) {
                    // em caso afirmativo, enviamos a justificativa de ausência recebida
                    $textoQuery = "UPDATE Frequencia SET aprovacaoPendente = 1,
                                   justificativaAusencia = ? WHERE chaveAluno = ?
                                   AND chaveAula = ?";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, htmlspecialchars($_POST['justificativa']));
                    $query->bindParam(2, unserialize($_SESSION['usuario'])->getNumeroInscricao());
                    $query->bindParam(3, $_POST['idAula']);

                    $sucesso = $query->execute();

                    if (!$sucesso) {
                        $mensagem = "Erro no envio de justificativa de ausência";
                    } else {
                        $justificativaEnviada = true;
                    }
                }

                $ano = "";
                if( isset($_GET["ano"]) ){
                    $ano = $_GET["ano"];
                }
                else{
                    $ano = date("Y");
                }

                $textoQuery = "SELECT A.chaveCidade, A.etapa, A.data, P.nome, F.presenca,
                                F.aprovacaoPendente, F.chaveAula FROM Aula A INNER JOIN Administrador Ad
                                ON Ad.idAdmin = A.idProfessor INNER JOIN Usuario P ON 
                                Ad.idUsuario = P.id INNER JOIN Cidade C ON
                                A.chaveCidade = C.idCidade INNER JOIN Matricula M ON
                                M.chaveCidade = C.idCidade INNER JOIN Frequencia F ON
                                F.chaveAluno  = M.chaveAluno AND F.chaveAula = A.idAula
                                WHERE M.chaveAluno = :chaveAluno
                                AND C.ano = :ano AND A.etapa = M.etapa";

                $query = $conexao->prepare($textoQuery);

                $query->bindParam(":chaveAluno",
                                  unserialize($_SESSION["usuario"])->getNumeroInscricao());
                $query->bindParam(":ano" , $ano);

                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $presencas = 0;
                $aulas = 0;

                $tabela = "";

                while ($linha = $query->fetch()){

                        // listamos os dados de cada aula
                        $tabela .= "<tr>";
    
                        require_once("entidades/Cidade.php");
                        $cidade = new Cidade();
                        $cidade->setIdCidade($linha["chaveCidade"]);
                        $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);
    
                        $tabela .= "    <td class=\"cidade\" data-id-cidade=\"";
                        $tabela .= $linha["chaveCidade"]."\">";
                        $tabela .= htmlspecialchars($cidade->getNome())             ."</td>";
                        $tabela .= "    <td class=\"professor\">";
                        $tabela .= htmlspecialchars($linha["nome"])             ."</td>";
                        $tabela .= "    <td class=\"etapa\">";
                        $tabela .= htmlspecialchars($linha["etapa"])                ."</td>";
                        $tabela .= "    <td class=\"data\" data-data-html=\"";
                        $tabela .= str_replace("-", "/", $linha["data"])."\">";
                        $tabela .= date("d/m/Y H:i", strtotime($linha["data"])) ."</td>";
    
                        if ($linha['aprovacaoPendente']) {
                            $tabela .= "    <td><i class=\"fa fa-ellipsis-h\"></i>
                                            </td>";
                        } else if($linha['presenca']) {
                            $tabela .= "    <td><i class=\"fa fa-check-square-o sucesso\"></i>
                                            </td>";
                            $presencas++;
                        } else {
                            $tabela .= "<td><a href=\"#\" data-chaveaula=\"" .
                                       htmlspecialchars($linha['chaveAula']) .
                                       "\" data-toggle=\"modal\" data-target=\"#modal-justificativa\">
                                       <i class=\"fa fa-minus-square-o warning\"></i>
                                        </a></td>";
                        }
                        $tabela .= "</tr> ";

                        $aulas++;
    
                }       

                $porcentagemFrequencia = (100 * $presencas)/$aulas;

                // Lê os anos das mariculas que o usuário possui para
                // permitir selecionar o ano a ser exibido

                $textoQuery = "SELECT C.ano 
                                FROM Cidade C, Aluno A, Matricula M
                                WHERE C.idCidade = M.chaveCidade AND M.chaveAluno = :chaveAluno AND
                                C.ano <= YEAR(CURDATE())";

                $query = $conexao->prepare($textoQuery);
                $query->bindParam(":chaveAluno",
                                  unserialize($_SESSION["usuario"])->getNumeroInscricao());
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $query->execute();

                $anos = [];

                $matriculas = $query->rowCount();
                $select = "";
                if($matriculas){
                    $select = "<select id='ano' name='ano'
                                       class='form-control' style='width: 100px'>";

                    while($linha = $query->fetch()){
                        if(!in_array($linha["ano"], $anos)){
                            $select .= "<option value=\"".$linha["ano"]."\"";
                            $select .= $ano == $linha["ano"] ? "selected = selected" : "";
                            $select .= "> ".$linha["ano"]."</option>";
                            
                            $anos[] = $linha["ano"];
                        }
                    }

                    $select .= "</select>";

                }
        ?>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">
                <section class="conteudo">
                    <h1>
                        Frequências Lançadas:
                    </h1>
                    <?php if (mb_strlen($mensagem, 'utf-8') > 0)
                            echo '<p class="warning">' . $mensagem . '</p>';
                        if($justificativaEnviada)
                            echo '<p class="sucesso">Justificativa enviada com sucesso</p>';
                    ?>
                    <br>
                    <p>Para justificar uma ausência, clique no ícone
                       <i class="fa fa-minus-square-o"></i> respectivo à essa ausência
                    </p>
                    <?php if($matriculas){
                            echo "<form id='busca-ano' name='busca-ano' 
                                    action='frequencia_aluno.php' method='GET'>
                                    Ano letivo:".$select."</form>
                                    <br>";

                            if($aulas){
                                echo "
                                        <p class=\"".($porcentagemFrequencia >= 80 
                                                    ? "sucesso" : "warning")."\">
                                             Você esteve presente em : ".
                                             number_format( $porcentagemFrequencia ,2 )."
                                             % das
                                            aulas lançadas neste ano
                                        </p>";  
                            }else{
                                echo "<p class= 'warning'>
                                        Não foram lançadas presenças para este ano até o momento
                                      </p>";
                            }
                            
                        } 
                    ?>
                    <div class="flip-scroll">
                        <div class="wrapper-scroll">
                            <table class="table table-bordered table-striped" id="trabalhos">
                                <thead style="background-color: #AAA">
                                    <tr>
                                        <th width="350px">Cidade</th>
                                        <th>Professor</th>
                                        <th>Etapa</th>
                                        <th>Data</th>
                                        <th>Presente?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= $tabela ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- popup "modal" do bootstrap para justificativa de ausência -->
        <div class="modal fade" id="modal-justificativa" tabindex="-1" role="dialog"
             aria-labelledby="modal-justificativa" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="" id="envia-justificativa">
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        X
                    </button>
                    <h4 class="modal-title">Justificativa de ausência</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idAula" name="idAula"></input>
                        <label for="justificativa">Favor justificar sua ausência abaixo</label>
                        <textarea name="justificativa" id="justificativa" rows="8" cols="50"
                            maxlength="10000" required
                            title="A justificativa deve ser preenchida e ter até 10000 caracteres"
                            class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="submit">Enviar</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <?php
            } else{
        ?>
        <!-- redireciona o usuário para o index.php -->
        <meta http-equiv="refresh" content="0; url=index.php">
        <script type="text/javascript">
            window.location = "index.php?mensagem=Apenas alunos inscritos podem ver os frequência";
        </script>
        <?php
                die();
            }
            include("modulos/rodape.php");
        ?>
    </body>
</html>