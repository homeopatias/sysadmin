<?php

/*****************************************
 * Cidade.php                            *
 *                                       *
 *                                       *
 * Data de criação: 02/07/2014           *
 * Descrição: Classe que representa uma  *
 * cidade no sistema                     *
 *                                       *
 * Observação: Depende da classe         *
 * Administrador                         *
 *                                       *
 *****************************************/

require_once(dirname(__FILE__)."/../phpass-0.3/PasswordHash.php");
require_once(dirname(__FILE__)."/../entidades/Administrador.php");

class Cidade{
    private $idCidade;
    private $UF;
    private $ano;
    private $nome;
    private $coordenador;
    private $local;
    private $inscricao;
    private $parcela;
    private $limiteInscricao;
    private $nomeEmpresa;
    private $cnpjEmpresa;
    private $custoCurso;

    // Construtor
    public function __construct(){
        $this->idCidade        = -1;
        $this->UF              = "";
        $this->ano             = -1;
        $this->nome            = "";
        $this->coordenador     = new Administrador("");
        $this->local           = "";
        $this->inscricao       = .0;
        $this->parcela         = .0;
        $this->limiteInscricao = date("0000-00-00");
        $this->nomeEmpresa     = "";
        $this->cnpjEmpresa     = -1;
        $this->custoCurso     = 0;
    }


    // Função que insere os dados da Cidade armazenados no bd nesse objeto
    // Utiliza $this->idCidade para encontrar a cidade no sistema
    // Recebe: 
    // $host:         host do banco de dados mysql
    // $bd:           banco de dados a ser acessado
    // $usuario:      nome de usuário a ser usado para acesso ao bd
    // $senha:        senha a ser usada para acesso ao bd
    //
    // Retorna: true caso a cidade seja encontrada, do contrário, false
    public function recebeCidadeId($host, $bd, $usuario, $senha){
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $textoQuery  = "SELECT idCidade, UF, ano, nome, idCoordenador, local, precoInscricao, 
                        precoParcela, limiteInscricao, nomeEmpresa, cnpjEmpresa, custoCurso
                        FROM Cidade WHERE idCidade = ?";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $this->idCidade, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // encontramos o administrador no sistema
            $this->idCidade        = $linha["idCidade"];
            $this->UF              = $linha["UF"];
            $this->ano             = $linha["ano"];
            $this->nome            = $linha["nome"];
            $this->local           = $linha["local"];
            $this->inscricao       = $linha["precoInscricao"];
            $this->parcela         = $linha["precoParcela"];
            $this->limiteInscricao = $linha["limiteInscricao"];
            $this->nomeEmpresa     = $linha["nomeEmpresa"];
            $this->cnpjEmpresa     = $linha["cnpjEmpresa"];
            $this->custoCurso      = $linha["custoCurso"];


            $this->setCoordenadorId($linha["idCoordenador"]);

            // encerramos a conexão com o BD
            $conexao = null;
            return true;
        }
        // encerramos a conexão com o BD
        $conexao = null;

        return false;
    }

    // Função que cadastra uma cidade no sistema
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

        // checamos se já existe uma cidade com mesmo nome, ano e UF no sistema
        // se existir, não inserimos a cidade
        $query = $conexao->prepare("SELECT idCidade FROM Cidade WHERE UF=?, ano=?, nome=?");
        $query->bindParam(1, $this->UF, PDO::PARAM_STR);
        $query->bindParam(2, $this->ano, PDO::PARAM_INT);
        $query->bindParam(3, $this->nome, PDO::PARAM_STR);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // essa cidade já existe no sistema
            $conexao = null;
            return false;
        }

        $dados  = array($this->UF, $this->ano, $this->nome, $this->coordenador->getIdAdmin(),
                         $this->local, $this->inscricao, $this->parcela,
                         date("Y-m-d H:i:s", strtotime($this->limiteInscricao)),
                         $this->nomeEmpresa, $this->cnpjEmpresa, $this->custoCurso);
        $textoQuery  = "INSERT INTO Cidade (UF, ano, nome, idCoordenador, local, 
                        precoInscricao, precoParcela, limiteInscricao, nomeEmpresa,
                        cnpjEmpresa,custoCurso) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $query  = $conexao->prepare($textoQuery);
        $sucesso = $query->execute($dados);

        // Fecha a conexão
        $conexao = null;
        return $sucesso;
    }

    // Função que altera uma cidade no sistema, inserindo na cidade de id igual a
    // $this->idCidade os dados desse objeto Cidade
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


        $comando  = "UPDATE Cidade SET UF=?, ano=?, nome=?, idCoordenador=?,
                     local=?, precoInscricao=?, precoParcela=?, limiteInscricao=?,
                     nomeEmpresa=?, cnpjEmpresa=?, custoCurso=? WHERE idCidade = ?";
        $query = $conexao->prepare($comando);
        $dados  = array($this->UF, $this->ano, $this->nome, $this->coordenador->getIdAdmin(),
                        $this->local, $this->inscricao, $this->parcela,
                        date("Y-m-d H:i:s", strtotime($this->limiteInscricao)),
                        $this->nomeEmpresa, $this->cnpjEmpresa, $this->custoCurso,
                        $this->idCidade);
        $sucesso = $query->execute($dados);

        // Encerramos a conexão com o BD
        $conexao = null;

        return $sucesso;
    }

    public function getIdCidade()
    {
        return $this->idCidade;
    }
    public function setIdCidade($idCidade)
    {
        $this->idCidade = $idCidade;

        return $this;
    }

    public function getUF()
    {
        return $this->UF;
    }
    public function setUF($UF)
    {
        $this->UF = mb_strtoupper($UF, 'UTF-8');

        return $this;
    }

    public function getAno()
    {
        return $this->ano;
    }
    public function setAno($ano)
    {
        $this->ano = $ano;

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

    public function getCoordenador()
    {
        return $this->coordenador;
    }
    public function setCoordenador($coordenador)
    {
        $this->coordenador = $coordenador;

        return $this;
    }

    // Faz o mesmo que a função acima, porém recebe o id do coordenador e o valida
    // antes de armazená-lo
    // Recebe: 
    // $idCoordenador: id do coordenador a ser colocado na cidade
    //
    // Retorna: true em caso de sucesso, false em caso de falha
    public function setCoordenadorId($idCoordenador){
        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);

        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }

        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senha   = $dados["senha"];

        $coord = new Administrador("");
        $coord->setIdAdmin($idCoordenador);
        $sucesso = $coord->recebeAdminId($host, "homeopatias", $usuario, $senha, "coordenador");
        
        if($sucesso){
            // esse coordenador existe no sistema
            $this->coordenador = $coord;
            return true;
        }
        // esse coordenador não existe no sistema
        return false;
    }

    public function getLocal()
    {
        return $this->local;
    }
    public function setLocal($local)
    {
        $this->local = $local;

        return $this;
    }

    public function getInscricao(){
        return $this->inscricao;
    }
    public function setInscricao($inscricao){
        $this->inscricao = $inscricao;

        return $this;
    }

    public function getParcela(){
        return $this->parcela;
    }
    public function setParcela($parcela){
        $this->parcela = $parcela;

        return $this;
    }

    public function getLimiteInscricao(){
        return $this->limiteInscricao;
    }
    public function setLimiteInscricao($limiteInscricao){
        $this->limiteInscricao = $limiteInscricao;

        return $this;
    }

    public function getNomeEmpresa(){
        return $this->nomeEmpresa;
    }
    public function setNomeEmpresa($nomeEmpresa){
        $this->nomeEmpresa = $nomeEmpresa;

        return $this;
    }

    public function getCnpjEmpresa(){
        return $this->cnpjEmpresa;
    }
    public function setCnpjEmpresa($cnpjEmpresa){
        // esse setter recebe o CNPJ no formato padrão e o torna puramente numérico
        // funciona também caso o CNPJ já seja puramente numérico
        $cnpjEmpresa = str_replace(".","",$cnpjEmpresa);
        $cnpjEmpresa = str_replace("/","",$cnpjEmpresa);
        $cnpjEmpresa = str_replace("-","",$cnpjEmpresa);
        $this->cnpjEmpresa = $cnpjEmpresa;

        return $this;
    }
    public function getCustoCurso()
    {
        return $this->custoCurso;
    }

    public function setCustoCurso($custoCurso)
    {
        $this->custoCurso = $custoCurso;

        return $this;
    }

}
?>