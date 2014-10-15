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

                $ano = "";
                if( isset($_GET["ano"]) ){
                    $ano = $_GET["ano"];
                }
                else{
                    $ano = date("Y");
                }

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

                $textoQuery = "SELECT A.chaveCidade, A.etapa, A.data,
                                P.nome , F.presenca FROM Aula A INNER JOIN Administrador Ad
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
    
                        $tabela .= "    <td><i class=\"fa " .($linha["presenca"] == 1
                                                            ? "fa-check-square-o sucesso"
                                                            : "fa-minus-square-o warning").
                                        "\"></i>
                                        </td>
                                    </tr> ";
                        if($linha["presenca"] == 1){
                            $presencas++;
                        }
                        

                        $aulas ++;
    
                }       

                $porcentagemFrequencia = (100 * $presencas)/$aulas;

                //Lê os anos das mariculas que o usuário possui para permitir selecionar o ano a ser
                //exibido

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
                    $select = "<select id='ano' name='ano'>";

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
                    </h1><br><br>
                    <?php if($matriculas){
                            echo "<form id='busca-ano' name='busca-ano' 
                                    action='frequencia_aluno.php' method='GET'>
                                    Ano letivo:".$select."</form>
                                    <br>";

                            if($porcentagemFrequencia){
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
                                        <th> Professor </th>
                                        <th> Etapa </th>
                                        <th> Data </th>
                                        <th> Presente? </th>
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
        <?php
            } else{
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