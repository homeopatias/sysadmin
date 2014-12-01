<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once("../../entidades/Administrador.php");

$mensagem = "Você não possui permissão para fazer isso";

if(isset($_SESSION["usuario"]) && unserialize($_SESSION["usuario"]) instanceof Administrador
   && unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){

    // se o usuário chegou até aqui através de um formulário, altera os dados do coordenador
    if(isset($_POST["submit"])){
        // validamos todos os dados recebidos
        $id         = $_POST["id"];
        $nome       = $_POST["nome"];
        $edicao     = $_POST["edicao"];
        $autor      = $_POST["autor"];
        $editora    = $_POST["editora"];
        $preco      = $_POST["preco"];
        $quantidade = $_POST["quantidade"];
        $data       = $_POST["dataPublic"];
        $fornecedor = $_POST["fornecedor"];

        $idValido      = preg_match("/^\d+$/", $id);
        $nomeValido    = isset($nome) && mb_strlen($nome, 'UTF-8') >= 3 &&
                         mb_strlen($nome, 'UTF-8') <= 500;
        $edicaoValido  = isset($edicao) && preg_match("/^\d+$/", $edicao);
        $autorValido   = isset($autor) && mb_strlen($autor, 'UTF-8') >= 3 &&
                         mb_strlen($autor, 'UTF-8') <= 100;
        $editoraValida = isset($editora) && mb_strlen($editora, 'UTF-8') >= 3 &&
                         mb_strlen($editora, 'UTF-8') <= 100;
        $precoValido   = isset($preco) && preg_match("/^[0-9]*\.?[0-9]+$/", $preco);
        $quantValida   = isset($edicao) && preg_match("/^\d+$/", $edicao);
        $dataValida    = isset($data) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $data);
        $fornecValido  = isset($fornecedor) && mb_strlen($fornecedor, 'UTF-8') >= 3 &&
                         mb_strlen($fornecedor, 'UTF-8') <= 200;

        // se todos os dados estão válidos, o coordenador é editado
        if($idValido && $nomeValido && $edicaoValido && $autorValido && $editoraValida &&
           $precoValido && $quantValida && $dataValida && $fornecValido){

            // lemos as credenciais do banco de dados
            $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
            $dados = json_decode($dados, true);
            foreach($dados as $chave => $valor) {
                $dados[$chave] = str_rot13($valor);
            }
            $host    = $dados["host"];
            $usuario = $dados["nome_usuario"];
            $senhaBD = $dados["senha"];

            require_once("../../entidades/Livro.php");

            $atualizar = new Livro();
            $atualizar->setIdLivro($id);
            $atualizar->setNome($nome);
            $atualizar->setEdicao($edicao);
            $atualizar->setAutor($autor);
            $atualizar->setEditora($editora);
            $atualizar->setPreco($preco);
            $atualizar->setQuantidade($quantidade);
            $atualizar->setDataPublicacao($data);
            $atualizar->setFornecedor($fornecedor);

            $sucesso = $atualizar->atualizar($host, "homeopatias", $usuario, $senhaBD);

            if($sucesso){
                $mensagem = "";
            }else{
                $mensagem = "Erro na edição de livro";
            }
        }else if(!$nomeValido){
            $mensagem = "Nome inválido!";
        }else if(!$edicaoValido){
            $mensagem = "Edição inválida!";
        }else if(!$autorValido){
            $mensagem = "Nome de autor inválido!";
        }else if(!$editoraValida){
            $mensagem = "Nome da editora inválido!";
        }else if(!$precoValido){
            $mensagem = "Preço inválido!";
        }else if(!$quantValida){
            $mensagem = "Quantidade inválida!";
        }else if(!$dataValida){
            $mensagem = "Data inválida!";
        }else if(!$fornecValido){
            $mensagem = "Nome de fornecedor inválido!";
        }else if(!$idValido){
            $mensagem = "Dados inconsistentes";
        }
    }else{
        $mensagem = "Erro de envio de formulário";
    }
}

if($mensagem !== ""){
    $mensagem = "?erro=".$mensagem;
}

header('Location: ../../gerenciar_livros.php'.$mensagem, true, "302");
die();