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
    private $limiteInscricao;
    private $nomeEmpresa;
    private $cnpjEmpresa;
    private $custoCurso;
    private $cadastroAtivo;
    private $tipoCurso;
    private $modalidadeCurso;
    private $parcelaExtensaoRegular;
    private $parcelaPosRegular;
    private $parcelaExtensaoIntensivo;
    private $parcelaPosIntensivo;
    private $inscricaoExtensaoRegular;
    private $inscricaoPosRegular;
    private $inscricaoExtensaoIntensivo;
    private $inscricaoPosIntensivo;
    private $inscricaoInstitutoRegular;
    private $inscricaoInstitutoIntensivo; 

    // Construtor
    public function __construct(){
        $this->idCidade        = -1;
        $this->UF              = "";
        $this->ano             = -1;
        $this->nome            = "";
        $this->coordenador     = new Administrador("");
        $this->local           = "";
        $this->limiteInscricao = date("0000-00-00");
        $this->nomeEmpresa     = "";
        $this->cnpjEmpresa     = -1;
        $this->custoCurso      = 0;
        $this->cadastroAtivo   = 1;
        $this->tipoCurso       = "";
        $this->modalidadeCurso = "";
        $this->parcelaExtensaoRegular       = 0;
        $this->parcelaPosRegular            = 0;
        $this->parcelaExtensaoIntensivo     = 0;
        $this->parcelaPosIntensivo          = 0;
        $this->parcelaInstitutoIntensivo    = 0;
        $this->parcelaInstitutoPosIntensivo = 0;
        $this->inscricaoExtensaoRegular     = 0;
        $this->inscricaoPosRegular          = 0;
        $this->inscricaoExtensaoIntensivo   = 0;
        $this->inscricaoPosIntensivo        = 0;
        $this->inscricaoInstitutoRegular    = 0;
        $this->inscricaoInstitutoIntensivo  = 0;
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

        $textoQuery  = "SELECT idCidade, UF, ano, nome, idCoordenador, local, 
                        limiteInscricao, nomeEmpresa, cnpjEmpresa, custoCurso,
                        cadastro_ativo, tipo_curso, modalidadeCurso, parcela_extensao_regular, 
                        parcela_extensao_intensivo, parcela_pos_regular, parcela_pos_intensivo,
                        inscricao_extensao_regular, inscricao_extensao_intensivo,
                        inscricao_pos_regular, inscricao_pos_intensivo, 
                        inscricao_instituto_regular, inscricao_instituto_intensivo,
                        parcela_instituto_regular, parcela_instituto_intensivo
                        FROM Cidade WHERE idCidade = ?";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $this->idCidade, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // encontramos o administrador no sistema
            $this->idCidade          = $linha["idCidade"];
            $this->UF                = $linha["UF"];
            $this->ano               = $linha["ano"];
            $this->nome              = $linha["nome"];
            $this->local             = $linha["local"];
            $this->limiteInscricao   = $linha["limiteInscricao"];
            $this->nomeEmpresa       = $linha["nomeEmpresa"];
            $this->cnpjEmpresa       = $linha["cnpjEmpresa"];
            $this->custoCurso        = $linha["custoCurso"];
            $this->cadastroAtivo     = $linha["cadastro_ativo"];
            $this->tipoCurso         = $linha["tipo_curso"];
            $this->modalidadeCurso   = $linha["modalidadeCurso"];

            $this->parcelaExtensaoRegular       = $linha["parcela_extensao_regular"];
            $this->parcelaPosRegular            = $linha["parcela_pos_regular"];
            $this->parcelaExtensaoIntensivo     = $linha["parcela_extensao_intensivo"];
            $this->parcelaPosIntensivo          = $linha["parcela_pos_intensivo"];
            $this->parcelaInstitutoRegular      = $linha["parcela_instituto_regular"];
            $this->parcelaInstitutoIntensivo    = $linha["parcela_instituto_intensivo"];
            $this->inscricaoExtensaoRegular     = $linha["inscricao_extensao_regular"];
            $this->inscricaoPosRegular          = $linha["inscricao_pos_regular"];
            $this->inscricaoExtensaoIntensivo   = $linha["inscricao_extensao_intensivo"];
            $this->inscricaoPosIntensivo        = $linha["inscricao_pos_intensivo"];
            $this->inscricaoInstitutoRegular    = $linha["inscricao_intituto_regular"];
            $this->inscricaoInstitutoIntensivo  = $linha["inscricao_instituto_intensivo"];


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
                         $this->local,
                         date("Y-m-d H:i:s", strtotime($this->limiteInscricao)),
                         $this->nomeEmpresa, $this->cnpjEmpresa, $this->custoCurso,
                         $this->cadastroAtivo, $this->tipoCurso, $this->modalidadeCurso,
                         $this->parcelaExtensaoRegular, $this->parcelaPosRegular,
                         $this->parcelaExtensaoIntensivo, $this->parcelaPosIntensivo,
                         $this->parcelaInstitutoRegular, $this->parcelaInstitutoIntensivo  
                         $this->inscricaoExtensaoRegular, $this->inscricaoPosRegular,
                         $this->inscricaoExtensaoIntensivo, $this->inscricaoPosIntensivo
                         $this->inscricaoInstitutoRegular, $this->inscricaoInstitutoIntensivo);
        $textoQuery  = "INSERT INTO Cidade (UF, ano, nome, idCoordenador, local, 
                        limiteInscricao, nomeEmpresa,
                        cnpjEmpresa,custoCurso, cadastro_ativo,tipo_curso,modalidadeCurso,
                        parcela_extensao_regular, parcela_pos_regular, parcela_extensao_intensivo,
                        parcela_pos_intensivo, parcela_instituto_regular, 
                        parcela_instituto_intensivo, inscricao_extensao_regular,
                        inscricao_pos_regular, inscricao_extensao_intensivo, 
                        inscricao_pos_intensivo, inscricao_instituto_regular,
                        inscricao_instituto_intensivo
                        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
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


        $comando  = "UPDATE Cidade SET UF=?, nome=?, idCoordenador=?,
                     limiteInscricao=?, cadastro_ativo =?,
                     tipo_curso=?, modalidadeCurso=?,
                      parcela_extensao_regular=?, parcela_pos_regular=?, 
                      parcela_extensao_intensivo=?,
                      parcela_pos_intensivo=?, parcela_instituto_regular=?, 
                      parcela_instituto_intensivo=?, inscricao_extensao_regular=?,
                      inscricao_pos_regular=?, inscricao_extensao_intensivo=?, 
                      inscricao_pos_intensivo=?, inscricao_instituto_regular=?,
                      inscricao_instituto_intensivo=? WHERE idCidade = ?";
        $query = $conexao->prepare($comando);
        $dados  = array($this->UF, $this->nome, $this->coordenador->getIdAdmin(),
                        date("Y-m-d H:i:s", strtotime($this->limiteInscricao)),
                        $this->cadastroAtivo, $this->tipoCurso,$this->modalidadeCurso,
                         $this->parcelaExtensaoRegular, $this->parcelaPosRegular,
                         $this->parcelaExtensaoIntensivo, $this->parcelaPosIntensivo,
                         $this->parcelaInstitutoRegular, $this->parcelaInstitutoIntensivo  
                         $this->inscricaoExtensaoRegular, $this->inscricaoPosRegular,
                         $this->inscricaoExtensaoIntensivo, $this->inscricaoPosIntensivo
                         $this->inscricaoInstitutoRegular, $this->inscricaoInstitutoIntensivo,
                         $this->idCidade);
        $sucesso = $query->execute($dados);

        // Encerramos a conexão com o BD
        $conexao = null;

        return $sucesso;
    }

    // Getters e Setters
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

    //---
    public function getCadastroAtivo()
    {
        return $this->cadastroAtivo;
    }

    public function setCadastroAtivo($cadastroAtivo)
    {
        $this->cadastroAtivo = $cadastroAtivo;
        return $this;
    }

    public function getTipoCurso()
    {
        return $this->tipoCurso;
    }

    public function setTipoCurso($tipoCurso)
    {
        $this->tipoCurso = $tipoCurso;

        return $this;
    }
    public function getInscricaoExtensao()
    {
        return $this->inscricaoExtensao;
    }

    public function setInscricaoExtensao($inscricaoExtensao)
    {
        $this->inscricaoExtensao = $inscricaoExtensao;

        return $this;
    }

    public function getInscricaoPos()
    {
        return $this->inscricaoPos;
    }

    public function setInscricaoPos($inscricaoPos)
    {
        $this->inscricaoPos = $inscricaoPos;

        return $this;
    }
    public function getParcelaExtensao()
    {
        return $this->parcelaExtensao;
    }

    public function setParcelaExtensao($parcelaExtensao)
    {
        $this->parcelaExtensao = $parcelaExtensao;

        return $this;
    }
    public function getParcelapos()
    {
        return $this->parcelaPos;
    }

    public function setParcelapos($parcelaPos)
    {
        $this->parcelaPos = $parcelaPos;

        return $this;
    }

    public function getModalidadeCurso()
    {
        return $this->modalidadeCurso;
    }

    public function setModalidadeCurso($modalidadeCurso)
    {
        $this->modalidadeCurso = $modalidadeCurso;

        return $this;
    }

    public function getParcelaExtensaoRegular()
    {
        return $this->parcelaExtensaoRegular;
    }

    public function setParcelaExtensaoRegular($parcelaExtensaoRegular)
    {
        $this->parcelaExtensaoRegular = $parcelaExtensaoRegular;

        return $this;
    }

    public function getParcelaPosRegular()
    {
        return $this->parcelaPosRegular;
    }

    public function setParcelaPosRegular($parcelaPosRegular)
    {
        $this->parcelaPosRegular = $parcelaPosRegular;

        return $this;
    }

    public function getParcelaExtensaoIntensivo()
    {
        return $this->parcelaExtensaoIntensivo;
    }

    public function setParcelaExtensaoIntensivo($parcelaExtensaoIntensivo)
    {
        $this->parcelaExtensaoIntensivo = $parcelaExtensaoIntensivo;

        return $this;
    }

    public function getParcelaPosIntensivo()
    {
        return $this->parcelaPosIntensivo;
    }

    public function setParcelaPosIntensivo($parcelaPosIntensivo)
    {
        $this->parcelaPosIntensivo = $parcelaPosIntensivo;

        return $this;
    }

    public function getInscricaoExtensaoRegular()
    {
        return $this->inscricaoExtensaoRegular;
    }

    public function setInscricaoExtensaoRegular($inscricaoExtensaoRegular)
    {
        $this->inscricaoExtensaoRegular = $inscricaoExtensaoRegular;

        return $this;
    }

    public function getInscricaoPosRegular()
    {
        return $this->inscricaoPosRegular;
    }

    public function setInscricaoPosRegular($inscricaoPosRegular)
    {
        $this->inscricaoPosRegular = $inscricaoPosRegular;

        return $this;
    }

    public function getInscricaoExtensaoIntensivo()
    {
        return $this->inscricaoExtensaoIntensivo;
    }

    public function setInscricaoExtensaoIntensivo($inscricaoExtensaoIntensivo)
    {
        $this->inscricaoExtensaoIntensivo = $inscricaoExtensaoIntensivo;

        return $this;
    }

    public function getInscricaoPosIntensivo()
    {
        return $this->inscricaoPosIntensivo;
    }

    public function setInscricaoPosIntensivo($inscricaoPosIntensivo)
    {
        $this->inscricaoPosIntensivo = $inscricaoPosIntensivo;

        return $this;
    }

    public function getInscricaoInstitutoRegular()
    {
        return $this->inscricaoInstitutoRegular;
    }

    public function setInscricaoInstitutoRegular($inscricaoInstitutoRegular)
    {
        $this->inscricaoInstitutoRegular = $inscricaoInstitutoRegular;

        return $this;
    }

    public function getInscricaoInstitutoIntensivo()
    {
        return $this->inscricaoInstitutoIntensivo;
    }

    public function setInscricaoInstitutoIntensivo($inscricaoInstitutoIntensivo)
    {
        $this->inscricaoInstitutoIntensivo = $inscricaoInstitutoIntensivo;

        return $this;
    }
}
?>