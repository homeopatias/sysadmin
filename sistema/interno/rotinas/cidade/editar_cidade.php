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
        $ano         = $_POST["ano"];
        $local       = $_POST["local"];
        $idCoord     = $_POST["coord"];
        $limite      = $_POST["limite"];
        //$nomeEmpresa = $_POST["nomeEmpresa"];
        //$cnpjEmpresa = $_POST["cnpjEmpresa"];
        //$custoCurso  = $_POST["custoCurso"];
        $cadastroAtivo = isset($_POST["cadastroAtivo"]) ? 1 : 0;
        $tipoCurso   = $_POST["tipo-curso"];
        $modalidadeCurso   = $_POST["modalidade-curso"];
        $InscExtReg        = $_POST["inscricao-ext-reg"];
        $InscExtInt        = $_POST["inscricao-ext-int"];
        $InscPosReg        = $_POST["inscricao-pos-reg"];
        $InscPosInt        = $_POST["inscricao-pos-int"];
        $InscInsReg        = $_POST["inscricao-instituto-reg"];
        $InscInsInt        = $_POST["inscricao-instituto-int"];
        
        $ParcExtReg     = $_POST["parcela-ext-reg"];
        $ParcExtInt     = $_POST["parcela-ext-int"];
        $ParcPosReg     = $_POST["parcela-pos-reg"];
        $ParcPosInt     = $_POST["parcela-pos-int"];
        $ParcInsReg     = $_POST["parcela-instituto-reg"];
        $ParcInsInt     = $_POST["parcela-instituto-int"];

        if($tipoCurso == "extensao"){
            $InscPosReg   =  0;
            $InscPosInt   =  0;
            $InscInsReg   =  0;
            $InscInsInt   =  0;
            $ParcPosReg   =  0;
            $ParcPosInt   =  0;
            $ParcInsReg   =  0;
            $ParcInsInt   =  0;
            if($modalidadeCurso == "regular"){
                $InscExtInt   = 0;
                $ParcExtInt   = 0;
            }else if($modalidadeCurso == "regular"){
                $InscExtReg   = 0;
                $ParcExtReg   = 0;
            }
        }else if($tipoCurso == "pos"){
            $InscExtReg   =  0;
            $InscExtInt   =  0;
            $InscInsReg   =  0;
            $InscInsInt   =  0;
            $ParcExtReg   =  0;
            $ParcExtInt   =  0;
            $ParcInsReg   =  0;
            $ParcInsInt   =  0;
            if($modalidadeCurso == "regular"){
                $InscPosInt   = 0;
                $ParcPosInt   = 0;
            }else if($modalidadeCurso == "regular"){
                $InscPosReg   = 0;
                $ParcPosReg   = 0;
            }
        }else if($tipoCurso == "instituto"){
            $InscExtReg   =  0;
            $InscExtInt   =  0;
            $InscPosReg   =  0;
            $InscPosInt   =  0;
            $ParcExtReg   =  0;
            $ParcExtInt   =  0;
            $ParcPosReg   =  0;
            $ParcPosInt   =  0;
            if($modalidadeCurso == "regular"){
                $InscInsInt   = 0;
                $ParcInsInt   = 0;
            }else if($modalidadeCurso == "regular"){
                $InscInsReg   = 0;
                $ParcInsReg   = 0;
            }
        }

        $idValido      = isset($id) && preg_match("/^[0-9]*$/", $id);
        $nomeValido    = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                         mb_strlen($nome, 'UTF-8') <= 100;
        $UfValido      = isset($UF) && mb_strlen($UF, 'UTF-8') === 2;
        $anoValido     = isset($ano) && intval($ano) < date("Y") + 3;
        $localValido   = isset($local) && mb_strlen($local, 'UTF-8') >= 3 &&
                         mb_strlen($local, 'UTF-8') <= 200;
        $idCoordValido = isset($idCoord) && preg_match("/^[0-9]*$/", $idCoord);
        $limiteValido    = isset($limite) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $limite);
        $empresaValida   = true; /*isset($nomeEmpresa) && mb_strlen($nomeEmpresa, 'UTF-8') <= 100 &&
                           mb_strlen($nomeEmpresa, 'UTF-8') >= 3;*/
        $cnpjValido      = true; /*isset($cnpjEmpresa) &&
                           preg_match("/^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/",
                           $cnpjEmpresa);*/
        $custoCursoValido   = true; /*isset($custoCurso) && preg_match("/^[0-9]*\.?[0-9]+$/",
                                                                 $custoCurso);*/

        $modalidadeCursoValida = isset($modalidadeCurso) && ($modalidadeCurso == "regular"
                                        || $modalidadeCurso == "intensivo" || 
                                        $modalidadeCurso == "ambos");
        $tipoCursoValido = isset($tipoCurso) && 
                                        (
                                            $tipoCurso == "extensao" ||
                                            $tipoCurso == "pos" ||
                                            $tipoCurso == "instituto" ||
                                            $tipoCurso == "todos"
                                        );

        // checa validade dos campos de preços

        $inscricaoExtRegValida = isset($InscExtReg) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $InscExtReg);
        $inscricaoExtIntValida = isset($InscExtInt) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $InscExtInt);
        $inscricaoPosRegValida = isset($InscPosReg) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $InscPosReg);
        $inscricaoPosIntValida = isset($InscPosInt) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $InscPosInt);
        $inscricaoInsRegValida = isset($InscInsReg) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $InscInsReg);
        $inscricaoInsIntValida = isset($InscInsInt) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $InscInsInt);

        $parcelaExtRegValida = isset($ParcExtReg) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $ParcExtReg);
        $parcelaExtIntValida = isset($ParcExtInt) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $ParcExtInt);
        $parcelaPosRegValida = isset($ParcPosReg) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $ParcPosReg);
        $parcelaPosIntValida = isset($ParcPosInt) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $ParcPosInt);
        $parcelaInsRegValida = isset($ParcInsReg) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $ParcInsReg);
        $parcelaInsIntValida = isset($ParcInsInt) && 
                                 preg_match("/^[0-9]*\.?[0-9]+$/", $ParcInsInt);

        $pagamentosValidos = $inscricaoExtRegValida && $inscricaoExtIntValida &&
                             $inscricaoPosRegValida && $inscricaoPosIntValida &&
                             $inscricaoInsRegValida && $inscricaoInsIntValida &&
                             $parcelaExtRegValida   && $parcelaExtIntValida   &&
                             $parcelaPosRegValida   && $parcelaPosIntValida   &&
                             $parcelaInsRegValida   && $parcelaInsIntValida;

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
           && $tipoCursoValido && $modalidadeCursoValida && $pagamentosValidos){

            require_once("../../entidades/Cidade.php");

            $atualizar = new Cidade();
            $atualizar->setIdCidade($id);
            $atualizar->setNome($nome);
            $atualizar->setUF($UF);
            $atualizar->setAno($ano);
            $atualizar->setLocal($local);
            $atualizar->setLimiteInscricao($limite);
            //$atualizar->setNomeEmpresa($nomeEmpresa);
            //$atualizar->setCnpjEmpresa($cnpjEmpresa);
            //$atualizar->setCustoCurso($custoCurso);
            $coordExiste = $atualizar->setCoordenadorId($idCoord);
            $atualizar->setCadastroAtivo($cadastroAtivo);
            $atualizar->setTipoCurso($tipoCurso);
            $atualizar->setModalidadeCidade($modalidadeCurso);

            $atualizar->setParcelaExtensaoRegular($ParcExtReg);
            $atualizar->setParcelaPosRegular($ParcPosReg);
            $atualizar->setParcelaExtensaoIntensivo($ParcExtInt);
            $atualizar->setParcelaPosIntensivo($ParcPosInt);
            $atualizar->setParcelaInstitutoRegular($ParcInsReg);
            $atualizar->setParcelaInstitutoIntensivo($ParcInsInt);
            $atualizar->setInscricaoExtensaoRegular($InscExtReg);
            $atualizar->setInscricaoPosRegular($InscPosReg);
            $atualizar->setInscricaoExtensaoIntensivo($InscExtInt);
            $atualizar->setInscricaoPosIntensivo($InscPosInt);
            $atualizar->setInscricaoInstitutoRegular($InscInsReg);
            $atualizar->setInscricaoInstitutoIntensivo($InscInsInt);

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
            $mensagem = "Coordenador inválido!";
        }else if(!$limiteValido){
            $mensagem = "Data limite de matrícula inválida!";
        }else if(!$idValido){
            $mensagem = "Dados inconsistentes";
        }else if(!$empresaValida) {
            $mensagem = "Nome da empresa inválida!";
        }else if(!$cnpjValido) {
            $mensagem = "CNPJ inválido!";
        }else if(!$custoCursoValido) {
            $mensagem = "Custo do curso inválido!";
        }else if(!$modalidadeCursoValida){
            $mensagem = "Modalidade inválida";
        }else if(!$pagamentosValidos){
            $mensagem = "Valor inserido nas parcelas inválido";
        }else if(!$tipoCursoValido){
            $mensagem = "Tipo de curso inválido";
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