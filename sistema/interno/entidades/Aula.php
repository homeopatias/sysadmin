<?php

/*****************************************
 * Aula.php                              *
 *                                       *
 *                                       *
 * Data de criação: 03/07/2014           *
 * Descrição: Classe que representa uma  *
 * aula no sistema                       *
 *                                       *
 * Observação: Depende da classe         *
 * Administrador e da classe Cidade      *
 *                                       *
 *****************************************/

require_once($_SERVER["DOCUMENT_ROOT"]."/interno/phpass-0.3/PasswordHash.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/interno/entidades/Administrador.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/interno/entidades/Cidade.php");

class Aula{
    private $idAula;
    private $cidade;
    private $etapa;
    private $data;
    private $professor;
    private $nota;
    private $descricao;

    // Construtor
    public function __construct(){
        $this->idAula    = -1;
        $this->cidade    = new Cidade();
        $this->etapa     = -1;
        $this->data      = date("0000-00-00");
        $this->professor = null;
        $this->nota      = null;
        $this->descricao = null;
    }

    // Função que cadastra uma aula no sistema
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

        // checamos se já existe uma aula com mesma cidade, mesma etapa, mesma data
        // e mesmo professor no sistema, e se existir, não inserimos a aula
        $sql = "SELECT idAula FROM Aula WHERE chaveCidade=?, etapa=?, data=?, idProfessor=?";
        $query = $conexao->prepare($sql);
        $query->bindParam(1, $this->cidade->getIdCidade(), PDO::PARAM_INT);
        $query->bindParam(2, $this->etapa, PDO::PARAM_INT);
        $query->bindParam(3, date("Y-m-d H:i:s", $this->data), PDO::PARAM_STR);
        if($this->professor != null){
            $query->bindParam(4, $this->professor->getIdAdmin(), PDO::PARAM_INT);
        }else{
            $query->bindValue(4, null);
        }
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // essa aula já existe no sistema
            $conexao = null;
            return false;
        }

        $idProfessor = $this->professor != null ? $this->professor->getIdAdmin() : null;
        $dados  = array($this->cidade->getIdCidade(), $this->etapa, date("Y-m-d H:i:s",
                        $this->data), $idProfessor, $this->nota, $this->descricao);
        $textoQuery  = "INSERT INTO Aula (chaveCidade, etapa, data, idProfessor, nota, descricao) 
                        VALUES (?,?,?,?,?,?)";
        $query  = $conexao->prepare($textoQuery);
        $sucesso = $query->execute($dados);

        // Fecha a conexão
        $conexao = null;
        return $sucesso;
    }

    // Função que altera uma aula no sistema, inserindo na aula de id igual a
    // $this->idAula os dados desse objeto Aula
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

        $idProfessor = $this->professor != null ? $this->professor->getIdAdmin() : null;
        $comando  = "UPDATE Aula SET chaveCidade=?, etapa=?, data=?, idProfessor=?, nota=?,
                     descricao=? WHERE idAula = ?";
        $query = $conexao->prepare($comando);
        $dados  = array($this->cidade->getIdCidade(), $this->etapa, date("Y-m-d H:i:s",
                        $this->data), $idProfessor, $this->nota, $this->descricao,
                        $this->idAula);
        $sucesso = $query->execute($dados);

        // Encerramos a conexão com o BD
        $conexao = null;

        return $sucesso;
    }

    // Getters e setters

    public function getIdAula()
    {
        return $this->idAula;
    }
    public function setIdAula($idAula)
    {
        $this->idAula = $idAula;

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
    // Faz o mesmo que a função acima, porém recebe o id da cidade e a valida
    // antes de armazená-la
    // Recebe: 
    // $idCidade: id da cidade a ser colocada na Aula
    //
    // Retorna: true em caso de sucesso, false em caso de falha
    public function setCidadeId($idCidade){
        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);

        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }

        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        $cidade = new Cidade();
        $cidade->setIdCidade($idCidade);
        $sucesso = $cidade->recebeCidadeId($host, "homeopatias", $usuario, $senhaBD);
        
        if($sucesso){
            // essa cidade existe no sistema
            $this->cidade = $cidade;
            return true;
        }
        // essa cidade não existe no sistema
        return false;
    }


    public function getEtapa()
    {
        return $this->etapa;
    }
    public function setEtapa($etapa)
    {
        $this->etapa = $etapa;

        return $this;
    }


    public function getData()
    {
        return $this->data;
    }
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }


    public function getProfessor()
    {
        return $this->professor;
    }
    public function setProfessor($professor)
    {
        $this->professor = $professor;

        return $this;
    }
    // Faz o mesmo que a função acima, porém recebe o id do professor e o valida
    // antes de armazená-lo
    // Recebe: 
    // $idProfessor: id do professor a ser colocado na Aula
    //
    // Retorna: true em caso de sucesso, false em caso de falha
    public function setProfessorId($idprofessor){
        // caso idprofessor seja -1, devemos criar um professor nulo
        if($idprofessor == -1){
            $this->professor = null;
        }

        // lemos as credenciais do banco de dados
        $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
        $dados = json_decode($dados, true);

        foreach($dados as $chave => $valor) {
            $dados[$chave] = str_rot13($valor);
        }

        $host    = $dados["host"];
        $usuario = $dados["nome_usuario"];
        $senhaBD = $dados["senha"];

        $prof = new Administrador("");
        $prof->setIdAdmin($idprofessor);
        $sucesso = $prof->recebeAdminId($host, "homeopatias", $usuario, $senhaBD, "professor");
        
        if($sucesso){
            // esse professor existe no sistema
            $this->professor = $prof;
            return true;
        }
        // esse professor não existe no sistema
        return false;
    }


    public function getNota()
    {
        return $this->nota;
    }
    public function setNota($nota)
    {
        $this->nota = $nota;

        return $this;
    }


    public function getDescricao()
    {
        return $this->descricao;
    }
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;

        return $this;
    }
}
?>