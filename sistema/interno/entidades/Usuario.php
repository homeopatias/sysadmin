<?php

/*****************************************
 * Usuario.php                           *
 *                                       *
 *                                       *
 * Data de criação: 19/06/2014           *
 * Descrição: Classe que representa um   *
 * usuário no sistema, do qual os outros *
 * usuarios vão herdar					 *
 *                                       *
 *****************************************/

require_once(dirname(__FILE__)."/../phpass-0.3/PasswordHash.php");

abstract class Usuario{
    protected $id;
    protected $cpf;
    protected $dataInscricao;
    protected $email;
    protected $login;
    protected $nome;

    // Mudar a senha do usuário
    public function mudaSenha($novaSenha){

        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);

        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }

        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        // Cria conexão com o banco
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=homeopatias;charset=utf8", $usuario, $senhaBD);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $comando = "UPDATE Usuario SET senha = :senha WHERE id = :id";
        $query = $conexao->prepare($comando);

        // Fazemos o hash da senha usando a biblioteca phppass
        $hasher = new PasswordHash(8, false);
        $hashSenha = $hasher->HashPassword($novaSenha);

        $query->bindParam(":senha", $hashSenha, PDO::PARAM_STR);
        $query->bindParam(":id", $this->id, PDO::PARAM_INT);

        $sucesso = $query->execute();

        // Encerramos a conexão com o BD
        $conexao = null;

        return $sucesso;
    }

    // Getters e setters
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getCpf()
    {
        return $this->cpf;
    }
    public function setCpf($cpf)
    {
        // esse setter recebe o CPF no formato padrão e o torna puramente numérico
        // funciona também caso o CPF já seja puramente numérico
        $cpf = str_replace(".","",$cpf);
        $cpf = str_replace("-","",$cpf);
        $this->cpf = $cpf;

        return $this;
    }

    public function getDataInscricao()
    {
        return $this->dataInscricao;
    }
    public function setDataInscricao($dataInscricao)
    {
        $this->dataInscricao = $dataInscricao;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getLogin()
    {
        return $this->login;
    }
    public function setLogin($login)
    {
        $this->login = $login;

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
}
?>