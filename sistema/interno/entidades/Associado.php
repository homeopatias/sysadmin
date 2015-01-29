<?php

/*****************************************
 * Associado.php                         *
 *                                       *
 *                                       *
 * Data de criação: 24/06/2014           *
 * Descrição: Classe que representa um   *
 * associado no sistema                  *
 *                                       *
 * Observação: Depende da classe Usuario *
 *                                       *
 *****************************************/

require_once(dirname(__FILE__)."/../phpass-0.3/PasswordHash.php");
require_once(dirname(__FILE__)."/../entidades/Usuario.php");

class Associado extends Usuario{
    private $idAssoc;
    private $instituicao;
    private $formacaoTerapeutica;
    private $telefone;
    private $enviouDocumentos;
    private $numObjeto;
    private $dataEnvioCarteirinha;

    //Variáveis relacionadas ao endereço
    private $cep;
    private $rua;
    private $numero;
    private $complemento;
    private $bairro;
    private $cidade;
    private $estado;
    private $pais;

    // Construtor
    // Recebe: 
    // $login: Nome de usuario do associado
    //
    // Retorna: Nada
    public function __construct($login){
        $this->login                = $login;
        $this->cpf                  = "";
        $this->dataInscricao        = new DateTime();
        $this->email                = "";
        $this->nome                 = "";
        $this->idAssoc              = -1;
        $this->instituicao          = "";
        $this->formacaoTerapeutica  = "";
        $this->telefone             = -1;
        $this->endereco             = "";
        $this->cep                  = "";
        $this->rua                  = "";
        $this->numero               = -1;
        $this->complemento          = "";
        $this->bairro               = "";
        $this->cidade               = "";
        $this->estado               = "";
        $this->pais                 = "";
        $this->numObjeto            = null;
        $this->dataEnvioCarteirinha = null;
        $this->enviouDocumentos     = false;
    }

    // Função que confere os dados do associado no sistema e
    // caso estejam corretos, preenche o objeto com os outros dados
    // Além disso, armazena o associado na sessão
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

        $textoQuery = "SELECT U.id, U.cpf, UNIX_TIMESTAMP(U.dataInscricao) as data, U.email,
                       U.senha, U.nome, A.idAssoc, A.instituicao, A.formacaoTerapeutica, 
                       A.telefone, A.cep, A.rua, A.numero, A.complemento, A.bairro,
                       A.cidade, A.estado, A.pais, A.enviouDocumentos, A.numObjeto,
                       A.dataEnvioCarteirinha
                       FROM Usuario U, Associado A
                       WHERE U.login=? AND A.idUsuario = U.id";

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
                $this->id                   = $linha["id"];
                $this->cpf                  = $linha["cpf"];
                $this->dataInscricao        = $linha["data"];
                $this->email                = $linha["email"];
                $this->nome                 = $linha["nome"];
                $this->idAssoc              = $linha["idAssoc"];
                $this->instituicao          = $linha["instituicao"];
                $this->formacaoTerapeutica  = $linha["formacaoTerapeutica"];
                $this->telefone             = $linha["telefone"];
                $this->cep                  = $linha["cep"];
                $this->rua                  = $linha["rua"];
                $this->numero               = $linha["numero"];
                $this->complemento          = $linha["complemento"];
                $this->bairro               = $linha["bairro"];
                $this->cidade               = $linha["cidade"];
                $this->estado               = $linha["estado"];
                $this->pais                 = $linha["pais"];
                $this->numObjeto            = $linha["numObjeto"];
                $this->dataEnvioCarteirinha = $linha["dataEnvioCarteirinha"];
                $this->enviouDocumentos     = $linha["enviouDocumentos"];

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

    // Função que insere os dados do Associado armazenados no bd nesse objeto
    // Utiliza $this->idAssoc para encontrar o associado no sistema
    // Recebe: 
    // $host:         host do banco de dados mysql
    // $bd:           banco de dados a ser acessado
    // $usuario:      nome de usuário a ser usado para acesso ao bd
    // $senha:        senha a ser usada para acesso ao bd
    //
    // Retorna: true caso o associado seja encontrado, do contrário, false
    public function recebeAssociadoId($host, $bd, $usuario, $senha){
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $textoQuery = "SELECT U.id, U.cpf, UNIX_TIMESTAMP(U.dataInscricao) as data, U.email,
                       U.senha, U.nome, A.idAssoc, A.instituicao, A.formacaoTerapeutica, 
                       A.telefone, A.cep, A.rua, A.numero, A.complemento, A.bairro,
                       A.cidade, A.estado, A.pais, A.enviouDocumentos, A.numObjeto,
                       A.dataEnvioCarteirinha
                       FROM Usuario U, Associado A
                       WHERE A.idAssoc = ? AND A.idUsuario = U.id";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $this->login, PDO::PARAM_STR);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // encontramos o usuário no sistema
            $this->id                   = $linha["id"];
            $this->cpf                  = $linha["cpf"];
            $this->dataInscricao        = $linha["data"];
            $this->email                = $linha["email"];
            $this->nome                 = $linha["nome"];
            $this->idAssoc              = $linha["idAssoc"];
            $this->instituicao          = $linha["instituicao"];
            $this->formacaoTerapeutica  = $linha["formacaoTerapeutica"];
            $this->telefone             = $linha["telefone"];
            $this->cep                  = $linha["cep"];
            $this->rua                  = $linha["rua"];
            $this->numero               = $linha["numero"];
            $this->complemento          = $linha["complemento"];
            $this->bairro               = $linha["bairro"];
            $this->cidade               = $linha["cidade"];
            $this->estado               = $linha["estado"];
            $this->pais                 = $linha["pais"];
            $this->numObjeto            = $linha["numObjeto"];
            $this->dataEnvioCarteirinha = $linha["dataEnvioCarteirinha"];
            $this->enviouDocumentos     = $linha["enviouDocumentos"];

            // encerramos a conexão com o BD
            $conexao = null;
            return true;
        }
        // encerramos a conexão com o BD
        $conexao = null;

        return false;
    }

    // Função que cadastra um associado no sistema
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
        $dadosUsuario  = array($this->cpf, $dataInscricao, $this->email,
                               $this->login, $hashSenha, $this->nome);
        $queryUsuario  = "INSERT INTO Usuario (cpf, dataInscricao,
                          email, login, senha, nome) 
                          VALUES (?,?,?,?,?,?)";
        $query         = $conexao->prepare($queryUsuario);
        $sucessoUsuario = $query->execute($dadosUsuario);

        // descobrimos o id do usuário que acabamos de inserir
        $idUsuario = $conexao->lastInsertId();

        $dadosAssoc  = array($idUsuario, $this->instituicao, $this->formacaoTerapeutica,
                             $this->telefone, $this->cidade, $this->estado,
                              $this->enviouDocumentos , $this->cep, $this->rua,
                              $this->numero, $this->bairro, $this->pais,
                              $this->complemento);
        $queryAssoc  = "INSERT INTO Associado (idUsuario, instituicao, formacaoTerapeutica,
                        telefone, cidade, estado, enviouDocumentos, cep, rua,
                        numero, bairro, pais, complemento) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $query = $conexao->prepare($queryAssoc);
        $sucessoAssoc = $query->execute($dadosAssoc);
        $idAssocInserido = $conexao->lastInsertId();

        // agora descobrimos o valor que deve ser pago por esse associado
        // de acordo com a instituição que escolheu
        $queryValor = "SELECT valorInscricao, ano FROM Instituicao WHERE nome = ?";
        $query = $conexao->prepare($queryValor);
        $query->bindParam(1, $this->instituicao);
        $query->setFetchMode(PDO::FETCH_ASSOC);

        $query->execute();

        $dados = $query->fetch();
        $valorInscricao = $dados['valorInscricao'];
        $valorAnuidade = $dados['valorAnuidade'];
        $ano = $dados['ano'];

        // por fim registramos os pagamentos que esse associado deverá fazer
        $queryPgtos = "INSERT INTO PgtoAnuidade (chaveAssoc, inscricao,
                       valorTotal, valorPago, data, ano, fechado) VALUES
                       (?, 1, ?, 0, NULL, ?, 0), (?, 0, ?, 0, NULL, ?, 0)";
        $query = $conexao->prepare($queryPgtos);
        $dados = array($idAssocInserido, $valorInscricao, $ano, $idAssocInserido,
                       $valorAnuidade, $ano);
        $sucessoPgtos = $query->execute($dados);

        if($sucessoUsuario && $sucessoAssoc && $sucessoPgtos) {
            // deu tudo certo, inserimos o associado
            $conexao->commit();
        } else {
            // algo deu errado, desfazemos as mudanças
            $conexao->rollBack();
        }

        // Fecha a conexão 
        $conexao = null;
        return $sucessoUsuario && $sucessoAssoc && $sucessoPgtos;
    }

    // Função que altera um associado no sistema, inserindo no associado de id igual a
    // $this->id os dados desse objeto Associado
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

        $comando = "UPDATE Usuario SET nome = :nome, cpf = :cpf, email = :email, login = :login 
                    WHERE id = :id";
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
        $comando = "UPDATE Associado SET instituicao = :instituicao, telefone = :telefone, 
                    formacaoTerapeutica = :form, cidade = :cidade, estado = :estado,
                    enviouDocumentos = :doc, numObjeto = :numObjeto,
                    dataEnvioCarteirinha = :data, rua = :rua,numero = :numero
                    , bairro = :bairro, cep = :cep, pais = :pais, complemento = :complemento
                    WHERE idAssoc = :idAssoc";
        $query = $conexao->prepare($comando);
        $query->bindParam(":instituicao", $this->instituicao, PDO::PARAM_STR);
        $query->bindParam(":form", $this->formacaoTerapeutica, PDO::PARAM_STR);
        $query->bindParam(":telefone", $this->telefone, PDO::PARAM_STR);
        $query->bindParam(":cidade", $this->cidade, PDO::PARAM_STR);
        $query->bindParam(":estado", $this->estado, PDO::PARAM_STR);
        $query->bindParam(":doc", $this->enviouDocumentos, PDO::PARAM_BOOL);
        $query->bindParam(":idAssoc", $this->idAssoc, PDO::PARAM_INT);
        $query->bindParam(":numObjeto", $this->numObjeto, PDO::PARAM_INT);
        $query->bindParam(":data", $this->dataEnvioCarteirinha);
        $query->bindParam(":cep", $this->cep, PDO::PARAM_STR);
        $query->bindParam(":rua", $this->rua, PDO::PARAM_STR);
        $query->bindParam(":numero", $this->numero, PDO::PARAM_STR);
        $query->bindParam(":bairro", $this->bairro, PDO::PARAM_STR);
        $query->bindParam(":pais", $this->pais, PDO::PARAM_STR);
        $query->bindParam(":complemento", $this->complemento, PDO::PARAM_STR);
        $sucessoAssoc = $query->execute();

        if($sucessoUsuario && $sucessoAssoc) {
            // deu tudo certo, atualizamos o associado
            $conexao->commit();
        } else {
            // algo deu errado, desfazemos as mudanças
            $conexao->rollBack();
        }

        // Encerramos a conexão com o BD
        $conexao = null;

        return $sucessoUsuario && $sucessoAssoc;
    }

    // Getters e setters
    public function getIdAssoc()
    {
        return $this->idAssoc;
    }
    public function setIdAssoc($idAssoc)
    {
        $this->idAssoc = $idAssoc;

        return $this;
    }

    public function getInstituicao()
    {
        return $this->instituicao;
    }
    public function setInstituicao($instituicao)
    {
        $this->instituicao = $instituicao;

        return $this;
    }

    public function getFormacaoTerapeutica()
    {
        return $this->formacaoTerapeutica;
    }
    public function setFormacaoTerapeutica($formacaoTerapeutica)
    {
        $this->formacaoTerapeutica = $formacaoTerapeutica;

        return $this;
    }

    public function getTelefone()
    {
        return $this->telefone;
    }
    public function setTelefone($telefone)
    {
        $telefone = str_replace("(","",$telefone);
        $telefone = str_replace(")","",$telefone);
        $telefone = str_replace("-","",$telefone);
        $this->telefone = $telefone;

        return $this;
    }

    public function getRua()
    {
        return $this->rua;
    }
    public function setRua($rua)
    {
        $this->rua = $rua;

        return $this;
    }

    public function getCep()
    {
        return $this->cep;
    }
    public function setCep($cep)
    {
        $this->cep = $cep;

        return $this;
    }
    public function getNumero()
    {
        return $this->numero;
    }
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    public function getBairro()
    {
        return $this->bairro;
    }
    public function setBairro($bairro)
    {
        $this->bairro = $bairro;

        return $this;
    }
    public function getCidade()
    {
        return $this->cidade;
    }
    public function setCidade($cidade)
    {
        $this->cidade = $cidade;

        return $this;
    }

    public function getEstado()
    {
        return $this->estado;
    }
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }
    public function getComplemento()
    {
        return $this->complemento;
    }
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;

        return $this;
    }

    public function getPais()
    {
        return $this->pais;
    }
    public function setPais($pais)
    {
        $this->pais = $pais;

        return $this;
    }
    
    public function retornaEndereco()
    {

        $endereco = "";
        if($this->rua != null){
            $endereco = $this->rua;
        }
        if( $this->numero  != null && $this->rua != null){
            $endereco .= ", ".$this->numero;
        }

        if( $this->cidade  != null){
            $endereco .= " ". $this->cidade;
        }

        if($this->estado  != null){
            $endereco .= " ".$this->estado;
        }

        if( $this->complemento != null && $this->rua != null){
            $endereco .= " Complemento: ".$this->complemento;
        }
        return $endereco;
    }



    public function getNumObjeto()
    {
        return $this->numObjeto;
    }
    public function setNumObjeto($numObjeto)
    {
        $this->numObjeto = $numObjeto;

        return $this;
    }

    public function getDataEnvioCarteirinha()
    {
        return $this->dataEnvioCarteirinha;
    }
    public function setDataEnvioCarteirinha($dataEnvioCarteirinha)
    {
        $this->dataEnvioCarteirinha = $dataEnvioCarteirinha;

        return $this;
    }

    public function getEnviouDocumentos()
    {
        return $this->enviouDocumentos;
    }
    public function setEnviouDocumentos($enviouDocumentos)
    {
        $this->enviouDocumentos = $enviouDocumentos;

        return $this;
    }
}
?>