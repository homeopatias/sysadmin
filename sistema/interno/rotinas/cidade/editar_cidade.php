<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados da cidade
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id          = $_POST["idCidade"];
        $nome        = $_POST["nome"];
        $UF          = $_POST["UF"];
        //$ano         = $_POST["ano"];
        //$local       = $_POST["local"];
        $idCoord     = $_POST["coord"];
        $limite      = $_POST["limite"];
        //$nomeEmpresa = $_POST["nomeEmpresa"];
        //$cnpjEmpresa = $_POST["cnpjEmpresa"];
        //$custoCurso  = $_POST["custoCurso"];
        $cadastroAtivo = isset($_POST["cadastroAtivo"]) ? 1 : 0;
        $tipoCurso   = $_POST["tipo-curso"];
        $InscExt     = $_POST["inscricao-ext"];
        $ParceExt    = $_POST["parcela-ext"];
        $InscPos     = $_POST["inscricao-pos"];
        $ParcePos    = $_POST["parcela-pos"];



        $tipoCursoValido = isset($_POST["tipo-curso"]) &&
                                        $tipoCurso == "extensão" ||
                                        $tipoCurso == "pós" ||
                                        $tipoCurso == "ambos";

        if($tipoCurso == "extensão"){
            $InscPos     = 0;
            $ParcePos    = 0;
        }else if($tipoCurso == "pós"){
            $InscExt     = 0;
            $ParceExt    = 0;
        }

        $idValido      = isset($id) && preg_match("/^[0-9]*$/", $id);
        $nomeValido    = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                         mb_strlen($nome, 'UTF-8') <= 100;
        $UfValido      = isset($UF) && mb_strlen($UF, 'UTF-8') === 2;
        /*$anoValido     = isset($ano) && intval($ano) < date("Y") + 3;*/
        /*$localValido   = isset($local) && mb_strlen($local, 'UTF-8') >= 3 &&
                         mb_strlen($local, 'UTF-8') <= 200;*/
        $idCoordValido = isset($idCoord) && preg_match("/^[0-9]*$/", $idCoord);
        $limiteValido    = isset($limite) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $limite);
        /*$empresaValida   = isset($nomeEmpresa) && mb_strlen($nomeEmpresa, 'UTF-8') <= 100 &&
                           mb_strlen($nomeEmpresa, 'UTF-8') >= 3;*/
        /*$cnpjValido      = isset($cnpjEmpresa) &&
                           preg_match("/^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/",
                           $cnpjEmpresa);*/
        /*$custoCursoValido   = isset($custoCurso) && preg_match("/^[0-9]*\.?[0-9]+$/",
                                                                 $custoCurso);*/

        $inscricaoExtValida = isset($InscExt) && preg_match("/^[0-9]*\.?[0-9]+$/", $InscExt);
        $parcelaExtValida   = isset($ParceExt) && preg_match("/^[0-9]*\.?[0-9]+$/", $ParceExt);
        $inscricaoPosValida = isset($InscPos) && preg_match("/^[0-9]*\.?[0-9]+$/", $InscPos);
        $parcelaPosValida   = isset($ParcePos) && preg_match("/^[0-9]*\.?[0-9]+$/", $ParcePos);

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
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        if($idCoordValido) {
            // checamos se esse coordenador já coordena outra cidade nesse ano,
            // caso coordene, esse coordenador é inválido
            $textoQuery  = 'SELECT idCidade FROM Cidade WHERE ano = ?
                            AND idCoordenador = ? AND idCidade <> ?';
            $query = $conexao->prepare($textoQuery);
            $query->bindParam(1, $ano);
            $query->bindParam(2, $idCoord);
            $query->bindParam(3, $id);

            $query->setFetchMode(PDO::FETCH_ASSOC);
            $query->execute();

            // se esse coordenador é de outra cidade no ano dado, não é válido
            if($query->fetch()) $idCoordValido = false;
        }

        // se todos os dados estão válidos, a cidade é editada
        if($idValido && $nomeValido && $UfValido  && $idCoordValido  && $limiteValido
           && $tipoCursoValido && $inscricaoExtValida && $parcelaExtValida &&
           $inscricaoPosValida && $parcelaPosValida){

            require_once("../../entidades/Cidade.php");

            $atualizar = new Cidade();
            $atualizar->setIdCidade($id);
            $atualizar->setNome($nome);
            $atualizar->setUF($UF);
            //$atualizar->setAno($ano);
            //$atualizar->setLocal($local);
            $atualizar->setLimiteInscricao($limite);
            //$atualizar->setNomeEmpresa($nomeEmpresa);
            //$atualizar->setCnpjEmpresa($cnpjEmpresa);
            //$atualizar->setCustoCurso($custoCurso);
            $coordExiste = $atualizar->setCoordenadorId($idCoord);
            $atualizar->setCadastroAtivo($cadastroAtivo);
            $atualizar->setTipoCurso($tipoCurso);
            $atualizar->setInscricaoExtensao($InscExt);
            $atualizar->setInscricaoPos($InscPos);
            $atualizar->setParcelaExtensao($ParceExt);
            $atualizar->setParcelaPos($ParcePos);

            if($coordExiste){
                $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

                if($sucesso){
                    $mensagem = "";
                }else{
                    $mensagem = "Erro na edição de cidade";
                }
            }else{
                // o coordenador informado não existe
                $mensagem = "Esse coordenador não existe no sistema";
            }
        }else if(!$nomeValido){
            $mensagem = "Nome inválido!";
        }else if(!$UfValido){
            $mensagem = "Estado inválido!";
        }else if(!$anoValido){
            $mensagem = "Ano inválido!";
        }else if(!$localValido){
            $mensagem = "Local inválido!";
        }else if(!$idCoordValido){
            $mensagem = "Id de coordenador inválido!";
        }else if(!$inscricaoValida){
            $mensagem = "Valor de inscrição inválido!";
        }else if(!$parcelaValida){
            $mensagem = "Valor de parcela inválido!";
        }else if(!$limiteValido){
            $mensagem = "Data limite de matrícula inválida!";
        }else if(!$idValido){
            $mensagem = "Dados inconsistentes";
        }else if(!$empresaValida) {
            $mensagem = "Nome da empresa inválida!";
        }else if(!$cnpjValido) {
            $mensagem = "CNPJ inválido!";
        }else if(!$custoCurso) {
            $mensagem = "Custo do curso inválido!";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_cidades.php'.$mensagem, true, "302");
die();