<?php

/*****************************************
 * Administrador.php                     *
 *                                       *
 *                                       *
 * Data de criação: 16/06/2014           *
 * Descrição: Classe que representa um   *
 * administrador no sistema              *
 *                                       *
 * Observação: Depende da classe Usuario *
 *                                       *
 *****************************************/

/*******************************************************************

Se você vai mexer nesse sistema, eu sinto muito, eu realmente sinto
muito.

********************************************************************/

require_once($_SERVER["DOCUMENT_ROOT"]."/interno/phpass-0.3/PasswordHash.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/interno/entidades/Usuario.php");

class Administrador extends Usuario{
    private $idAdmin;
    private $nivelAdmin;
    private $corrigeTrabalho;
    private $permissoes;

    // Construtor
    // Recebe: 
    // $login: Nome de usuario do administrador
    //
    // Retorna: Nada
    public function __construct($login){
        $this->login = $login;
        $this->cpf = "";
        $this->dataInscricao = new DateTime();
        $this->email = "";
        $this->nome = "";
        $this->idAdmin = -1;
        $this->nivelAdmin = "";
        $this->corrigeTrabalho = false;
        $this->permissoes = false;
    }

    // Função que confere os dados do administrador no sistema e
    // caso estejam corretos, preenche o objeto com os outros dados
    // Além disso, armazena o administrador na sessão
    // Recebe: 
    // $host:         host do banco de dados mysql
    // $bd:           banco de dados a ser acessado
    // $usuario:      nome de usuário a ser usado para acesso ao bd
    // $senha:        senha a ser usada para acesso ao bd
    // $senhaUsuario: senha do usuario que tentaremos autenticar
    //
    // Retorna: true caso os dados confiram, do contrário, false
    public function autenticaSessao($host, $bd, $usuario, $senha, $senhaUsuario){
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $textoQuery  = "SELECT U.id, U.cpf, UNIX_TIMESTAMP(U.dataInscricao) as data, U.email,
                        U.senha, U.nome, A.idAdmin, A.nivel, A.corrigeTrabalho, A.permissoes
                        FROM Usuario U,
                        Administrador A WHERE U.login=? AND A.idUsuario = U.id";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $this->login, PDO::PARAM_STR);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // encontramos o usuário no sistema
            // checamos se a senha está correta
            $hasher = new PasswordHash(8, false);
            $senhaCorreta = $hasher->CheckPassword($senhaUsuario, $linha["senha"]);
            if($senhaCorreta){
                $this->id              = $linha["id"];
                $this->cpf             = $linha["cpf"];
                $this->dataInscricao   = $linha["data"];
                $this->email           = $linha["email"];
                $this->nome            = $linha["nome"];
                $this->idAdmin         = $linha["idAdmin"];
                $this->nivelAdmin      = $linha["nivel"];
                $this->corrigeTrabalho = $linha["corrigeTrabalho"];
                $this->permissoes      = $linha["permissoes"];

                $_SESSION["usuario"] = serialize($this);

                // encerramos a conexão com o BD
                $conexao = null;
                return true;
            }

            // encerramos a conexão com o BD
            $conexao = null;
            return false;
        }
        // encerramos a conexão com o BD
        $conexao = null;

        return false;
    }

    // Função que insere os dados do Administrador armazenados no bd nesse objeto
    // Utiliza $this->idAdmin para encontrar o admin no sistema
    // Recebe: 
    // $host:         host do banco de dados mysql
    // $bd:           banco de dados a ser acessado
    // $usuario:      nome de usuário a ser usado para acesso ao bd
    // $senha:        senha a ser usada para acesso ao bd
    // $tipoAdmin:    nivel de admin que deve ser procurado no banco de dados
    //
    // Retorna: true caso o admin seja encontrado, do contrário, false
    public function recebeAdminId($host, $bd, $usuario, $senha, $tipoAdmin){
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $textoQuery  = "SELECT U.id, U.cpf, UNIX_TIMESTAMP(U.dataInscricao) as data, U.email,
                        U.senha, U.nome, U.login, A.nivel, A.corrigeTrabalho, A.permissoes
                        FROM Usuario U,
                        Administrador A WHERE A.idAdmin=? AND A.idUsuario = U.id AND A.nivel = ?";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $this->idAdmin, PDO::PARAM_INT);
        $query->bindParam(2, $tipoAdmin, PDO::PARAM_STR);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // encontramos o administrador no sistema
            $this->id              = $linha["id"];
            $this->cpf             = $linha["cpf"];
            $this->dataInscricao   = $linha["data"];
            $this->email           = $linha["email"];
            $this->nome            = $linha["nome"];
            $this->login           = $linha["login"];
            $this->nivelAdmin      = $linha["nivel"];
            $this->corrigeTrabalho = $linha["corrigeTrabalho"];
            $this->permissoes      = $linha["permissoes"];

            // encerramos a conexão com o BD
            $conexao = null;
            return true;
        }
        // encerramos a conexão com o BD
        $conexao = null;

        return false;
    }

    // Função que insere os dados do Administrador armazenados no bd nesse objeto
    // Utiliza $this->login para encontrar o admin no sistema
    // Recebe: 
    // $host:         host do banco de dados mysql
    // $bd:           banco de dados a ser acessado
    // $usuario:      nome de usuário a ser usado para acesso ao bd
    // $senha:        senha a ser usada para acesso ao bd
    // $tipoAdmin:    nivel de admin que deve ser procurado no banco de dados
    //
    // Retorna: true caso o admin seja encontrado, do contrário, false
    public function recebeAdminLogin($host, $bd, $usuario, $senha, $tipoAdmin){
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $textoQuery  = "SELECT U.id, U.cpf, UNIX_TIMESTAMP(U.dataInscricao) as data, U.email,
                        U.senha, U.nome, A.idAdmin, A.nivel, A.corrigeTrabalho FROM Usuario U,
                        Administrador A WHERE U.login=? AND A.idUsuario = U.id AND A.nivel = ?";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $this->login, PDO::PARAM_INT);
        $query->bindParam(2, $tipoAdmin, PDO::PARAM_STR);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // encontramos o administrador no sistema
            $this->id              = $linha["id"];
            $this->cpf             = $linha["cpf"];
            $this->dataInscricao   = $linha["data"];
            $this->email           = $linha["email"];
            $this->nome            = $linha["nome"];
            $this->idAdmin         = $linha["idAdmin"];
            $this->nivelAdmin      = $linha["nivel"];
            $this->corrigeTrabalho = $linha["corrigeTrabalho"];
            $this->permissoes      = $linha["permissoes"];

            // encerramos a conexão com o BD
            $conexao = null;
            return true;
        }
        // encerramos a conexão com o BD
        $conexao = null;

        return false;
    }

    // Função que cadastra um administrador no sistema
    // Recebe: 
    // $host:    host do banco de dados mysql
    // $bd:      banco de dados a ser acessado
    // $usuario: nome de usuario para acesso ao banco
    // $senha:   senha do banco de dados
    // $senhaUsuario: senha do usuario que tentaremos cadastrar
    //
    // Retorna: true em caso de sucesso, false em caso de falha
    public function cadastrar($host, $bd, $usuario, $senha, $senhaUsuario){
        // primeiramente, fazemos o hash da senha, por questões de segurança,
        // usando a biblioteca phppass
        $hasher = new PasswordHash(8, false);
        $hashSenha = $hasher->HashPassword($senhaUsuario);

        // cria conexão com o banco
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $query = $conexao->prepare("SELECT login FROM Usuario WHERE login=?");
        $query->bindParam(1, $this->login, PDO::PARAM_STR);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // já existe alguém com esse nome de usuário no sistema
            $conexao = null;
            return false;
        }

        // Usamos as TRANSACTIONs do MySql para garantir que caso haja
        // algum erro, as tabelas continuem consistentes
        $conexao->beginTransaction();

        $dataInscricao = date("Y-m-d H:i:s");
        $dadosUsuario  = array($this->cpf, $dataInscricao, $this->email, $this->login,
                               $hashSenha, $this->nome);
        $queryUsuario  = "INSERT INTO Usuario (cpf, dataInscricao, email, login, senha, nome) 
                          VALUES (?,?,?,?,?,?)";
        $query         = $conexao->prepare($queryUsuario);
        $sucessoUsuario = $query->execute($dadosUsuario);

        // descobrimos o id do usuário que acabamos de inserir
        $idUsuario = $conexao->lastInsertId();

        $dadosAdmin  = array($idUsuario, $this->nivelAdmin, $this->corrigeTrabalho , $this->permissoes);
        $queryAdmin  = "INSERT INTO Administrador (idUsuario, nivel, corrigeTrabalho, permissoes)
                        VALUES (?, ?, ?, ?)";

        $query = $conexao->prepare($queryAdmin);
        $sucessoAdmin = $query->execute($dadosAdmin);

        if($sucessoUsuario && $sucessoAdmin) {
            // deu tudo certo, o administrador é inserido
            $conexao->commit();
        } else {
            // algo falhou, revertemos as mudanças
            $conexao->rollBack();
        }

        // Fecha a conexão
        $conexao = null;
        return $sucessoUsuario && $sucessoAdmin;
    }

    // Função que altera um administrador no sistema, inserindo no administrador de id igual a
    // $this->idAdmin os dados desse objeto Administrador
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

        $comando = "UPDATE Usuario SET nome = :nome, cpf = :cpf, email = :email,
                    login = :login WHERE id = :id";
        $query = $conexao->prepare($comando);

        // Usamos as TRANSACTIONs do MySql para garantir que caso haja
        // algum erro, as tabelas continuem consistentes
        $conexao->beginTransaction();

        $query->bindParam(":nome", $this->nome, PDO::PARAM_STR);
        $query->bindParam(":cpf", $this->cpf, PDO::PARAM_STR);
        $query->bindParam(":email", $this->email, PDO::PARAM_STR);
        $query->bindParam(":login", $this->login, PDO::PARAM_STR);
        $query->bindParam(":id", $this->id, PDO::PARAM_INT);
        $sucessoUsuario = $query->execute();
        $comando = "UPDATE Administrador SET nivel = :nivel, corrigeTrabalho = :corrige,
         permissoes = :permissoes
                    WHERE idAdmin = :idAdmin";
        $query = $conexao->prepare($comando);
        $query->bindParam(":nivel", $this->nivelAdmin, PDO::PARAM_STR);
        $query->bindParam(":idAdmin", $this->idAdmin, PDO::PARAM_INT);
        $query->bindParam(":corrige", $this->corrigeTrabalho, PDO::PARAM_BOOL);
        $query->bindParam(":permissoes", $this->permissoes, PDO::PARAM_INT);

        $sucessoAdmin = $query->execute();

        if($sucessoUsuario && $sucessoAdmin) {
            // deu tudo certo, o administrador é atualizado
            $conexao->commit();
        } else {
            // algo falhou, revertemos as mudanças
            $conexao->rollBack();
        }

        // Encerramos a conexão com o BD
        $conexao = null;

        return $sucessoUsuario && $sucessoAdmin;
    }

    // Getters e setters
    public function getIdAdmin()
    {
        return $this->idAdmin;
    }
    public function setIdAdmin($idAdmin)
    {
        $this->idAdmin = $idAdmin;

        return $this;
    }

    public function getNivelAdmin()
    {
        return $this->nivelAdmin;
    }
    public function setNivelAdmin($nivelAdmin)
    {
        $this->nivelAdmin = $nivelAdmin;

        return $this;
    }

    public function getCorrigeTrabalho()
    {
        return $this->corrigeTrabalho;
    }
    public function setCorrigeTrabalho($corrigeTrabalho)
    {
        $this->corrigeTrabalho = $corrigeTrabalho;

        return $this;
    }    


    public function getPermissoes()
    {
        return $this->permissoes;
    }
    public function setPermissoes($permissoes)
    {
        $this->permissoes = $permissoes;

        return $this;
    }
}
?>