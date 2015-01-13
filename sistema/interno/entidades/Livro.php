<?php

/*****************************************
 * Livro.php                             *
 *                                       *
 *                                       *
 * Data de criação: 11/07/2014           *
 * Descrição: Classe que representa um   *
 * livro no sistema                      *
 *                                       *
 *****************************************/

class Livro{
    private $idLivro;
    private $nome;
    private $edicao;
    private $autor;
    private $editora;
    private $preço;
    private $quantidade;
    private $dataPublicacao;
    private $fornecedor;

    // Construtor
    public function __construct(){
        $this->idLivro        = -1;
        $this->nome           = "";
        $this->edicao         = -1;
        $this->autor          = "";
        $this->editora        = "";
        $this->preço          = -1;
        $this->quantidade     = -1;
        $this->dataPublicacao = "";
        $this->fornecedor     = "";
    }

    // Função que insere os dados do Livro armazenados no bd nesse objeto
    // Utiliza $this->idLivro para encontrar o livro no sistema
    // Recebe: 
    // $host:         host do banco de dados mysql
    // $bd:           banco de dados a ser acessado
    // $usuario:      nome de usuário a ser usado para acesso ao bd
    // $senha:        senha a ser usada para acesso ao bd
    //
    // Retorna: true caso o livro seja encontrado, do contrário, false
    public function recebeLivroId($host, $bd, $usuario, $senha){
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $textoQuery  = "SELECT valor, quantidade, nome, autor, editora, 
                        UNIX_TIMESTAMP(dataPublic) as data, edicao, fornecedor 
                        FROM Livro WHERE idLivro=?";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $this->idLivro, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // encontramos o livro no sistema
            $this->nome           = $linha["nome"];
            $this->edicao         = $linha["edicao"];
            $this->autor          = $linha["autor"];
            $this->editora        = $linha["editora"];
            $this->preço          = $linha["valor"];
            $this->quantidade     = $linha["quantidade"];
            $this->dataPublicacao = $linha["data"];
            $this->fornecedor     = $linha["fornecedor"];

            // encerramos a conexão com o BD
            $conexao = null;
            return true;
        }
        // encerramos a conexão com o BD
        $conexao = null;

        return false;
    }

    // Função que cadastra um livro no sistema
    // Recebe: 
    // $host:    host do banco de dados mysql
    // $bd:      banco de dados a ser acessado
    // $usuario: nome de usuario para acesso ao banco
    // $senha:   senha do banco de dados
    //
    // Retorna: true em caso de sucesso, false em caso de falha
    public function cadastrar($host, $bd, $usuario, $senha){
        // cria conexão com o banco
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $dataP = date("Y-m-d H:i:s", strtotime($this->dataPublicacao));
        $dadosLivro  = array($this->preço, $this->quantidade, $this->nome, $this->autor,
                             $this->editora, $dataP, $this->edicao, $this->fornecedor);
        $queryLivro  = "INSERT INTO Livro (valor, quantidade, nome, autor, editora, dataPublic, 
                        edicao, fornecedor) VALUES (?,?,?,?,?,?,?,?)";
        $query       = $conexao->prepare($queryLivro);
        $sucesso = $query->execute($dadosLivro);

        // Fecha a conexão
        $conexao = null;
        return $sucesso;
    }

    // Função que altera um livro no sistema, inserindo no livro de id igual a
    // $this->idLivro os dados desse objeto Livro
    // Recebe: 
    // $host:    host do banco de dados mysql
    // $bd:      banco de dados a ser acessado
    // $usuario: nome de usuario para acesso ao banco
    // $senha:   senha do banco de dados
    //
    // Retorna: true em caso de sucesso, false em caso de falha
    public function atualizar($host, $bd, $usuario, $senha){
        // Cria conexão com o banco
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $dataP = date("Y-m-d H:i:s", strtotime($this->dataPublicacao));
        $comando  = "UPDATE Livro SET valor=?, quantidade=?, nome=?, autor=?, editora=?, dataPublic=?, 
                     edicao=?, fornecedor=? WHERE idLivro = ?";
        $dadosLivro = array($this->preço, $this->quantidade, $this->nome, $this->autor,
                             $this->editora, $dataP, $this->edicao, $this->fornecedor, $this->idLivro);
        $query = $conexao->prepare($comando);
        $sucesso = $query->execute($dadosLivro);

        // Encerramos a conexão com o BD
        $conexao = null;

        return $sucesso;
    }

    // Getters e setters

    public function getIdLivro()
    {
        return $this->idLivro;
    }
    public function setIdLivro($idLivro)
    {
        $this->idLivro = $idLivro;

        return $this;
    }

    public function getNome()
    {
        return $this->nome;
    }
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    public function getEdicao()
    {
        return $this->edicao;
    }
    public function setEdicao($edicao)
    {
        $this->edicao = $edicao;

        return $this;
    }

    public function getAutor()
    {
        return $this->autor;
    }
    public function setAutor($autor)
    {
        $this->autor = $autor;

        return $this;
    }

    public function getEditora()
    {
        return $this->editora;
    }
    public function setEditora($editora)
    {
        $this->editora = $editora;

        return $this;
    }

    public function getPreco()
    {
        return $this->preço;
    }
    public function setPreco($preço)
    {
        $this->preço = $preço;

        return $this;
    }

    public function getQuantidade()
    {
        return $this->quantidade;
    }
    public function setQuantidade($quantidade)
    {
        $this->quantidade = $quantidade;

        return $this;
    }

    public function getDataPublicacao()
    {
        return $this->dataPublicacao;
    }
    public function setDataPublicacao($dataPublicacao)
    {
        $this->dataPublicacao = $dataPublicacao;

        return $this;
    }

    public function getFornecedor()
    {
        return $this->fornecedor;
    }
    public function setFornecedor($fornecedor)
    {
        $this->fornecedor = $fornecedor;

        return $this;
    }
}
?>