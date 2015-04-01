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
            }
            td {
                line-height: 5px !important;
            }
            th:nth-child(1), td:nth-child(1) {
                border-right-color: #000 !important;
            }
            body {
                background-color: #FFF;
            }
            .conteudo {
                margin-bottom: 100px;
                background-color: #FFF;
                -webkit-box-shadow: none;
                -moz-box-shadow:    none;
                box-shadow:         none;
            }
            footer {
                display: none;
            }
        </style>
    </head>
    <body>
        <?php
            require_once("entidades/Administrador.php");
            // exibe listas de chamada apenas para coordenadores logados
            if(isset($_SESSION["usuario"]) &&
               unserialize($_SESSION["usuario"]) instanceof Administrador &&
               unserialize($_SESSION["usuario"])->getNivelAdmin() === "coordenador"){
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
                <section class="conteudo">
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
                        $idCoordenador = unserialize($_SESSION['usuario'])->getIdAdmin();
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
                                        WHERE A.chaveCidade = C.idCidade AND A.etapa = ?
                                        AND C.ano = YEAR(NOW()) AND C.idCoordenador = ? AND
                                        A.data > NOW() 
                                        LIMIT 1";
                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $etapa, PDO::PARAM_INT);
                        $query->bindParam(2, $idCoordenador, PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();
                        $existeAula = $query->rowCount();
                        if($existeAula){
                            // salva dados da aula em uma variável
                            $aula = $query->fetch();
                            //pega informação do coordenador
                            $coordenador = unserialize($_SESSION["usuario"]);
                            // Busca os dados dos professores da aula
                            $professor = new Administrador("");
                            $professor->setIdAdmin($aula["idProfessor"]);
                            $professor->recebeAdminId("localhost", "homeopatias", $usuario, 
                                                      $senhaBD, "professor");
                            if($aula["idProfAdicionalPrimario"]){
                                $professorP = new Administrador("");
                                $professorP->setIdAdmin($aula["idProfAdicionalPrimario"]);
                                $professorP->recebeAdminId("localhost", "homeopatias", $usuario, 
                                                      $senhaBD, "professor");
                            }
                            if($aula["idProfAdicionalSecundario"]){
                                $professorS = new Administrador("");
                                $professorS->setIdAdmin($aula["idProfAdicionalSecundario"]);
                                $professorS->recebeAdminId("localhost", "homeopatias", $usuario, 
                                                      $senhaBD, "professor");
                            }
                            $textoQuery  = "SELECT U.nome, C.nome as nomeCidade, C.UF 
                                            FROM Matricula M INNER JOIN Cidade C ON C.idCidade 
                                            = M.chaveCidade INNER JOIN Aluno A ON M.chaveAluno = 
                                            A.numeroInscricao INNER JOIN Usuario U ON U.id = 
                                            A.idUsuario WHERE A.status = 'inscrito'
                                            AND M.etapa = ? AND C.ano = YEAR(NOW())
                                            AND C.idCoordenador = ?";
                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(1, $etapa, PDO::PARAM_INT);
                            $query->bindParam(2, $idCoordenador, PDO::PARAM_INT);
                            $query->setFetchMode(PDO::FETCH_ASSOC);
                            $query->execute();
                            $resultado = '<table class="table">
                                <th style="font-weight: bold">Nome do aluno</th>
                                <th style="font-weight: bold">Assinatura do aluno</th>';
                            $numAlunos = 0;
                            $nomeCidade = false;
                            while ($linha = $query->fetch()){
                                $resultado .= '
                            <tr>
                                <td>' . $linha["nome"] .'</td>
                                <td>_________________________________________________________________</td>
                            </tr>
                                ';
                                $numAlunos++;
                                // descobrimos o nome da cidade
                                if(!$nomeCidade){
                                    $nomeCidade = htmlspecialchars($linha["nomeCidade"]) . "/" . 
                                                  htmlspecialchars($linha["UF"]);
                                }
                            }
                            $resultado .= "</table>";
                            if($numAlunos == 0){
                                $resultado = "<b>Nenhum aluno matrículado nessa cidade nessa etapa.</b>";
                            }
                            if(mb_strlen($mensagem, 'UTF-8') !== 0){
                                echo "            <p class=\"warning\">$mensagem</p>";
                            }
                        }
                    ?>
                    <h2 style="font-weight:bold; display:inline">
                        <?php
                            // Pegamos informações da aula no BD
                            if($numAlunos != 0){
                                echo "$nomeCidade - " . $etapa . "ª etapa/" . date("Y");
                                echo "<br>";
                                echo "<p style='font-size:22px;texto-decoration:none'>
                                     Coordenador(a) : ".$coordenador->getNome()."</p>";
                                echo "<p style='font-size:18px;texto-decoration:none'>
                                    Data e horario : ".date("d/m/Y h:i:s" , $aula["data"])."</p>";
                                echo "<br>";
                                echo "<p style='font-size:20px;texto-decoration:none'>
                                    Professor(es) : 
                                    <ol>
                                        <li style='font-size:18px;texto-decoration:none'>".
                                        $professor->getNome()."</li>";
                                if($professorP){
                                    echo "<li style='font-size:18px;texto-decoration:none'>".
                                        $professorP->getNome()."</li>";
                                }
                                if($professorS){
                                    echo "<li style='font-size:18px;texto-decoration:none'>".
                                        $professorS->getNome()."</li>";
                                }
                                echo "</ol>";
                            }else if(!$existeAula){
                                "<p>Não é aulas futuras registradas para esta cidade</p>";
                            }
                        ?>
                    </h2>
                    <br><br><br>
                    <?php
                        echo $resultado;
                    ?>

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
