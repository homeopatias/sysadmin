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

abstract class Usuario{
    protected $id;
    protected $cpf;
    protected $dataInscricao;
    protected $email;
    protected $login;
    protected $nome;

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