<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Lista de chamada</title>
        <style>
            body {
                padding-top: 0px;
                color: #000;
            }
            td {
                line-height: 30px !important;
            }
            table {
                width: 80% !important;
            }
            td, th {
                border: solid 1px black;
            }
            body {
                background-color: #FFF;
            }
            footer {
                display: none;
            }
        </style>
    </head>
    <body>
        <?php
            require_once("entidades/Administrador.php");
            // exibe listas de chamada apenas para administradores logados
            if(isset($_SESSION["usuario"]) &&
               unserialize($_SESSION["usuario"]) instanceof Administrador &&
               unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){
                $mensagem = "";
                $etapa = false;
                if(isset($_GET["etapa"])){
                    if(preg_match("/^[1-4]*$/", $_GET["etapa"])){
                        $etapa = htmlspecialchars($_GET["etapa"]);
                    }else{
                        $mensagem = "Etapa inválida!";
                        $etapa = 1;
                    }
                }
        ?>
        <br><br>
        <div class="col-sm-12">
            <div class="center-block col-sm-12 no-float">

                <?php
                    // lemos as credenciais do banco de dados
                    $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
                    $dados = json_decode($dados, true);
                    foreach($dados as $chave => $valor) {
                        $dados[$chave] = str_rot13($valor);
                    }
                    $host    = $dados["host"];
                    $usuario = $dados["nome_usuario"];
                    $senhaBD = $dados["senha"];
                    $idCidade = htmlspecialchars($_GET["idCidade"]);
                    $idAula = htmlspecialchars($_GET["idAula"]);
                    // cria conexão com o banco
                    $conexao = null;
                    $db      = "homeopatias";
                    try{
                        $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
                    }catch (PDOException $e){
                        echo $e->getMessage();
                    }
                    if($mensagem){
                        echo "<h1>$mensagem</h1>";
                        die();
                    }
                    // busca a próxima aula da cidade
                    $textoQuery  = "SELECT A.data, A.idProfessor, A.idProfAdicionalSecundario,
                                    A.idProfAdicionalPrimario, C.idCoordenador, C.UF
                                    FROM Aula A, Cidade C
                                    WHERE C.idCidade = ? AND A.idAula = ? LIMIT 1";
                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $idCidade, PDO::PARAM_INT);
                    $query->bindParam(2, $idAula, PDO::PARAM_INT);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();
                    $existeAula = $query->rowCount();
                    if($existeAula){
                        // salva dados da aula em uma variável
                        $aula = $query->fetch();
                        $textoQuery  = "SELECT U.nome, C.nome as nomeCidade, C.UF, A.modalidade_curso
                                        FROM Matricula M INNER JOIN Cidade C ON C.idCidade 
                                        = M.chaveCidade INNER JOIN Aluno A ON M.chaveAluno = 
                                        A.numeroInscricao INNER JOIN Usuario U ON U.id = 
                                        A.idUsuario WHERE A.status = 'inscrito' AND
                                        A.tipo_curso = ?
                                        AND M.etapa = ? AND C.idCidade = ?";
                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, htmlspecialchars($_GET['tipo']));
                        $query->bindParam(2, $etapa, PDO::PARAM_INT);
                        $query->bindParam(3, $idCidade, PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();
                        $resultado = '<table style="border: 1px solid black;">
                            <th style="font-weight: bold">Nº</th>
                            <th style="font-weight: bold">NOME LEGÍVEL</th>
                            <th style="font-weight: bold">ASSINATURA</th>
                            <th style="font-weight: bold">CURSO</th>';
                        $numAlunos = 0;
                        $nomeCidade = false;
                        $contador = 1;
                        while ($linha = $query->fetch()){
                            $resultado .= '
                        <tr>
                            <td>' . $contador .'.</td>
                            <td>' . $linha["nome"] .'</td>
                            <td></td>
                            <td>' . ucfirst($linha["modalidade_curso"]) .'</td>
                        </tr>
                            ';
                            $numAlunos++;
                            // descobrimos o nome da cidade
                            if(!$nomeCidade){
                                $nomeCidade = htmlspecialchars($linha["nomeCidade"]) . " - " . 
                                              htmlspecialchars($linha["UF"]);
                            }
                            $contador++;
                        }
                        $resultado .= "</table>";
                        if($numAlunos == 0){
                            $resultado = "<b>Nenhum aluno matrículado nessa cidade nessa etapa.</b>";
                        }
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "            <p class=\"warning\">$mensagem</p>";
                        }
                    }

                    $tituloPagina = "";
                    $tipo = $_GET['tipo'];

                    if($tipo === 'extensao') {
                        $tituloPagina = 'CURSO DE HOMEOPATIA<br>';
                    } else if($tipo === 'instituto') {
                        $tituloPagina = 'CURSO DE HOMEOPATIA<br><p style="font-weight: normal">INSTITUTO TECNOLÓGICO HAHNEMANN</p>';
                    } else if($tipo === 'pos') {
                        $tituloPagina = '<p style="font-weight: normal">Pós Graduação Lato Sensu –<br>Especialização em Ciência da Homeopatia</p>';
                    }
                ?>
                <img <?= $tipo === 'instituto' ?
                            'src="fotos/instituto.png" width="150px" height="140px"' :
                            'src="fotos/inspirar.png"  width="204px" height="100px"'?>
                    style="float:left"
                >
                <h1 style="font-weight:bold; font-size: 2.3em; display:inline; float: left; margin-left: 100px; text-align: center">
                    <?php
                        // Pegamos informações da aula no BD
                        if($numAlunos != 0){
                            echo $tituloPagina;
                            echo $nomeCidade;
                            echo "<br>";
                            // echo "<p style='font-size:18px;texto-decoration:none'>";
                            //echo "Data e horario : ".date("d/m/Y h:i:s" , strtotime($aula["data"]))."</p>";
                            // echo "<br>";

                        }else if(!$existeAula){
                            "<p>Não é aulas futuras registradas para esta cidade</p>";
                        }
                    ?>
                </h1>
                <br><br><br><br><br><br><br><br><br>
                <p style="margin-left: 300px">DATA: ___/___/20___</p>
                <?php
                    echo $resultado;
                ?>

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
