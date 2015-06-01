<?php

/*****************************************
 * Aluno.php                             *
 *                                       *
 *                                       *
 * Data de criação: 24/06/2014           *
 * Descrição: Classe que representa um   *
 * aluno no sistema                      *
 *                                       *
 * Observação: Depende da classe Usuario *
 *                                       *
 *****************************************/

require_once(dirname(__FILE__)."/../phpass-0.3/PasswordHash.php");
require_once(dirname(__FILE__)."/../entidades/Usuario.php");

class Aluno extends Usuario{
    private $numeroInscricao;
    private $senha;
    private $status;
    private $idIndicador;
    private $telefone;
    private $telefone2;
    private $telefone3;
    private $escolaridade;
    private $curso;
    private $tipoCurso;
    private $tipoCadastro;
    private $modalidadeCurso;
    private $ativo;
    private $recebeEmail;
    private $observacao;

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
    // $login: Nome de usuario do aluno
    //
    // Retorna: Nada
    public function __construct($login){
        $this->login           = $login;
        $this->cpf             = "";
        $this->dataInscricao   = new DateTime();
        $this->email           = "";
        $this->nome            = "";
        $this->numeroInscricao = -1;
        $this->senha           = "";
        $this->status          = "";
        $this->idIndicador     = "";
        $this->telefone        = -1;
        $this->telefone2       = -1;
        $this->telefone3       = -1;
        $this->escolaridade    = "";
        $this->curso           = null;
        $this->cep             = "";
        $this->rua             = "";
        $this->numero          = -1;
        $this->complemento     = "";
        $this->bairro          = "";
        $this->cidade          = "";
        $this->estado          = "";
        $this->pais            = "";
        $this->tipoCurso       = "";
        $this->modalidadeCurso = "";
        $this->tipoCadastro    = "";
        $this->ativo           = false;
        $this->recebeEmail     = false;
        $this->observacao      = "";
    }

    // Função que confere os dados do aluno no sistema e
    // caso estejam corretos, preenche o objeto com os outros dados
    // Além disso, armazena o aluno na sessão
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

        $textoQuery  = "SELECT U.id, U.cpf, UNIX_TIMESTAMP(U.dataInscricao) as data, U.email, U.senha, 
                        U.nome, A.numeroInscricao, A.status, A.idIndicador, A.telefone, A.telefone2,
                        A.telefone3, A.escolaridade, A.curso,A.cep, A.rua, A.numero, A.complemento,
                        A.bairro, A.cidade, A.estado, A.pais , A.tipo_curso, 
                        A.modalidade_curso, A.tipo_cadastro, A.ativo, A.recebeEmail, A.observacao

                        FROM Usuario U, Aluno A WHERE U.login=? AND A.idUsuario = U.id";

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
                $this->numeroInscricao = $linha["numeroInscricao"];
                $this->status          = $linha["status"];
                $this->idIndicador     = $linha["idIndicador"];
                $this->telefone        = $linha["telefone"];
                $this->telefone2       = $linha["telefone2"];
                $this->telefone3       = $linha["telefone3"];
                $this->escolaridade    = $linha["escolaridade"];
                $this->curso           = $linha["curso"];
                $this->cep             = $linha["cep"];
                $this->rua             = $linha["rua"];
                $this->numero          = $linha["numero"];
                $this->complemento     = $linha["complemento"];
                $this->bairro          = $linha["bairro"];
                $this->cidade          = $linha["cidade"];
                $this->estado          = $linha["estado"];
                $this->pais            = $linha["pais"];
                $this->tipoCurso       = $linha["tipo_curso"];
                $this->modalidadeCurso = $linha["modalidade_curso"];
                $this->tipoCadastro    = $linha["tipo_cadastro"];
                $this->senha           = $senhaUsuario;
                $this->ativo           = $linha["ativo"];
                $this->recebeEmail     = $linha["recebeEmail"];
                $this->observacao      = $linha["observacao"];

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

    // Função que insere os dados do Aluno armazenados no bd nesse objeto
    // Utiliza $this->numeroInscricao para encontrar o aluno no sistema
    // Recebe: 
    // $host:         host do banco de dados mysql
    // $bd:           banco de dados a ser acessado
    // $usuario:      nome de usuário a ser usado para acesso ao bd
    // $senha:        senha a ser usada para acesso ao bd
    //
    // Retorna: true caso o aluno seja encontrado, do contrário, false
    public function recebeAlunoId($host, $bd, $usuario, $senha){
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        $textoQuery  = "SELECT U.id, U.cpf, U.login, UNIX_TIMESTAMP(U.dataInscricao) as data, U.email, U.senha, 
                        U.nome, A.numeroInscricao, A.status, A.idIndicador, A.telefone, A.telefone2, A.telefone3,
                        A.escolaridade, A.curso ,A.cep, A.rua, A.numero, A.complemento,
                        A.bairro, A.cidade, A.estado, A.pais , A.tipo_curso, A.modalidade_curso,
                        A.tipo_cadastro, A.ativo, A.recebeEmail, A.observacao

                        FROM Usuario U, Aluno A WHERE A.numeroInscricao=? AND A.idUsuario = U.id";

        $query = $conexao->prepare($textoQuery);
        $query->bindParam(1, $this->numeroInscricao, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if ($linha = $query->fetch()){
            // encontramos o usuário no sistema
            $this->id              = $linha["id"];
            $this->login           = $linha["login"];
            $this->cpf             = $linha["cpf"];
            $this->dataInscricao   = $linha["data"];
            $this->email           = $linha["email"];
            $this->nome            = $linha["nome"];
            $this->numeroInscricao = $linha["numeroInscricao"];
            $this->status          = $linha["status"];
            $this->idIndicador     = $linha["idIndicador"];
            $this->telefone        = $linha["telefone"];
            $this->telefone2       = $linha["telefone2"];
            $this->telefone3       = $linha["telefone3"];
            $this->escolaridade    = $linha["escolaridade"];
            $this->curso           = $linha["curso"];
            $this->cep             = $linha["cep"];
            $this->rua             = $linha["rua"];
            $this->numero          = $linha["numero"];
            $this->complemento     = $linha["complemento"];
            $this->bairro          = $linha["bairro"];
            $this->cidade          = $linha["cidade"];
            $this->estado          = $linha["estado"];
            $this->pais            = $linha["pais"];
            $this->tipoCurso       = $linha["tipo_curso"];
            $this->modalidadeCurso = $linha["modalidade_curso"];
            $this->tipoCadastro    = $linha["tipo_cadastro"];
            $this->ativo           = $linha["ativo"];
            $this->recebeEmail     = $linha["recebeEmail"];
            $this->observacao      = $linha["observacao"];

            // encerramos a conexão com o BD
            $conexao = null;
            return true;
        }

        // encerramos a conexão com o BD
        $conexao = null;

        return false;
    }

    // Função que cadastra um aluno no sistema
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
        $dadosUsuario  = array($this->cpf, $dataInscricao, $this->email, $this->login, $hashSenha, $this->nome );
        $queryUsuario  = "INSERT INTO Usuario (cpf, dataInscricao, email, login, senha, nome) 
                          VALUES (?,?,?,?,?,?)";
        $query         = $conexao->prepare($queryUsuario);
        $sucessoUsuario = $query->execute($dadosUsuario);


        // descobrimos o id do usuário que acabamos de inserir
        $idUsuario = $conexao->lastInsertId();

        $dadosAluno = $queryAluno = "";
        // conferimos se há um indicador para esse aluno e formatamos as queries de acordo,
        // para evitar problemas no banco de dados
        if($this->idIndicador === ""){
            $dadosAluno  = array($idUsuario, $this->status, $this->telefone, $this->telefone2, $this->telefone3,
                                 $this->escolaridade,
                                 $this->curso, $this->cep, $this->rua, $this->numero, 
                                 $this->complemento, $this->bairro,
                                 $this->cidade, $this->estado, $this->pais, $this->tipoCurso,
                                 $this->modalidadeCurso, $this->tipoCadastro, $this->ativo, $this->recebeEmail,
                                 $this->observacao);
            $queryAluno  = "INSERT INTO Aluno (idUsuario, status, telefone, telefone2, telefone3, 
                             escolaridade, curso, cep, rua, numero , complemento, bairro,
                             cidade, estado, pais, tipo_curso,modalidade_curso,
                              tipo_cadastro, ativo, recebeEmail, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                             ?, ?, ?, ?, ?, ?, ?, ?, ?)";


        }else{
            $dadosAluno  = array($idUsuario, $this->status, $this->telefone, $this->telefone2, $this->telefone3,
                                 $this->idIndicador, $this->escolaridade, $this->curso,
                                 $this->cep, $this->rua, $this->numero, 
                                 $this->complemento, $this->bairro,
                                 $this->cidade, $this->estado, $this->pais, $this->tipoCurso, 
                                 $this->modalidadeCurso, $this->tipoCadastro, $this->ativo, $this->recebeEmail,
                                 $this->observacao);
            $queryAluno  = "INSERT INTO Aluno (idUsuario, status, telefone, telefone2, telefone3,
                            idIndicador, escolaridade, curso, cep, rua, numero , complemento, bairro,
                             cidade, estado, pais, tipo_curso, modalidade_curso,
                             tipo_cadastro, ativo, recebeEmail, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                             ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        }
        $query = $conexao->prepare($queryAluno);
        $sucessoAluno = $query->execute($dadosAluno);
        $this->numeroInscricao = $conexao->lastInsertId();

        if($sucessoUsuario && $sucessoAluno) {
            // deu tudo certo, inserimos o aluno
            $conexao->commit();
        } else {
            // algo deu errado, desfazemos as mudanças
            $conexao->rollBack();
        }

        // Fecha a conexão
        $conexao = null;
        return $sucessoUsuario && $sucessoAluno;
    }

    // Função que altera um aluno no sistema, inserindo no aluno de id igual a
    // $this->id os dados desse objeto Aluno
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

        //Pega o status e o indicador atual do aluno no banco-------------------------
        $comando = "SELECT status, idIndicador FROM Aluno WHERE numeroInscricao = ?";
        $query = $conexao->prepare($comando);
        $query->bindParam(1, $this->numeroInscricao, PDO::PARAM_INT);
        $query->execute();

        $linha = $query->fetch();

        $statusAnterior    = $linha["status"];
        $indicadorAnterior = $linha["idIndicador"];

        //----------------------------------------------------------------------------

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

        $comando = "UPDATE Aluno SET status = :status, idIndicador = :indicador, 
                    telefone = :telefone, telefone2 = :telefone2, telefone3 = :telefone3,
                    escolaridade = :escolaridade, 
                    curso = :curso ,numeroInscricao = :numInsc,
                    cep = :cep, rua = :rua, numero = :numero, complemento = :complemento ,
                    cidade = :cidade, estado = :estado, bairro = :bairro, pais = :pais ,
                    tipo_curso = :tipo_curso, modalidade_curso= :modalidade_curso, 
                    tipo_cadastro = :tipo_cadastro, ativo = :ativo, recebeEmail = :recebeemail,
                    observacao = :observacao

                    WHERE numeroInscricao = :numInsc";
        $query = $conexao->prepare($comando);

        $query->bindParam(":status", $this->status, PDO::PARAM_STR);
        if($this->idIndicador === ""){
            $query->bindValue(":indicador", null, PDO::PARAM_INT);
        }else{
            $query->bindParam(":indicador", $this->idIndicador, PDO::PARAM_INT);
        }

        $query->bindParam(":telefone", $this->telefone, PDO::PARAM_STR);
        $query->bindParam(":telefone2", $this->telefone2, PDO::PARAM_STR);
        $query->bindParam(":telefone3", $this->telefone3, PDO::PARAM_STR);
        $query->bindParam(":numInsc", $this->numeroInscricao, PDO::PARAM_INT);
        $query->bindParam(":escolaridade", $this->escolaridade, PDO::PARAM_STR);
        $query->bindParam(":curso", $this->curso, PDO::PARAM_STR);
        $query->bindParam(":cep", $this->cep, PDO::PARAM_INT);
        $query->bindParam(":rua", $this->rua, PDO::PARAM_STR);
        $query->bindParam(":numero", $this->numero, PDO::PARAM_INT);
        $query->bindParam(":complemento", $this->complemento, PDO::PARAM_STR);
        $query->bindParam(":cidade", $this->cidade, PDO::PARAM_STR);
        $query->bindParam(":estado", $this->estado, PDO::PARAM_STR);
        $query->bindParam(":bairro", $this->bairro, PDO::PARAM_STR);
        $query->bindParam(":pais", $this->pais, PDO::PARAM_STR);
        $query->bindParam(":tipo_curso", $this->tipoCurso, PDO::PARAM_STR);
        $query->bindParam(":modalidade_curso", $this->modalidadeCurso, PDO::PARAM_STR);
        $query->bindParam(":tipo_cadastro", $this->tipoCadastro, PDO::PARAM_STR);
        $query->bindParam(":ativo", $this->ativo, PDO::PARAM_STR);
        $query->bindParam(":recebeemail", $this->recebeEmail);
        $query->bindParam(":observacao", $this->observacao);

        $sucessoAluno = $query->execute();

        //Tratamento de desconto de alunos indicadores

        //Primeiro checamos se o aluno pagou a inscição deste ano (se gerou desconto)

        $comando = "SELECT EXISTS(
                        SELECT Pg.numParcela
                        FROM PgtoMensalidade Pg, Aluno A, Usuario U,Matricula M, Cidade C
                        WHERE A.idUsuario     = U.id 
                        AND M.chaveAluno      = A.numeroInscricao 
                        AND M.chaveCidade     = C.idCidade
                        AND C.ano             = YEAR(CURDATE()) 
                        AND Pg.chaveMatricula = M.idMatricula 
                        AND Pg.numParcela     = 0
                        AND Pg.numParcela     = 0 
                        AND Pg.fechado        = 1 
                        AND A.numeroInscricao = ?
                        ) as existe";

        $query = $conexao->prepare($comando);
        $query->bindParam(1,$this->numeroInscricao, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        $atualizaDescontosAntigo = 0;

        $linha = $query->fetch();
        $emDia = $linha["existe"];
        if($emDia){
            //Estas variáveis armazenam se será necessário atualizar ou não os descontos do
            //anterior e/ou do novo 

            

            //Se o aluno ja pagou a inscrição deste ano, checa se será necessário remover o
            //desconto do indicador, se possuir indicador

            //checa se ele possui indicador e se não possuir, se agora possui
            if( ($indicadorAnterior != null) ||($this->idIndicador != null) ){
                //O usuário mudou de id de indicador, remove o desconto do anterior e soma 
                //ao novo

                if($indicadorAnterior != $this->idIndicador){
                    //Só será necessário mudar o desconto de um aluno válido
                    if($indicadorAnterior != null){
                        $atualizaDescontosAntigo = true;
                    }

                }

            }

        }

        $sucessoAluno = $sucessoAluno && $this->atualizaDesconto($host, $bd, $usuario, $senha);

        //--------------------------------------------------------------------------

        if($sucessoUsuario && $sucessoAluno) {
            // deu tudo certo, atualizamos o aluno
            $conexao->commit();

            if($atualizaDescontosAntigo){
                    require_once(dirname(__FILE__)."/../entidades/Aluno.php");
                    $indicadorAntigo = new Aluno;
                    $indicadorAntigo->setNumeroInscricao($indicadorAnterior);
                    $indicadorAntigo->recebeAlunoId($host, $bd, $usuario, $senha);
                    $indicadorAntigo->atualizaDesconto($host, $bd, $usuario, $senha);

            }

            if($this->idIndicador != null){
                require_once(dirname(__FILE__)."/../entidades/Aluno.php");
                $indicadorNovo = new Aluno;
                $indicadorNovo->setNumeroInscricao($this->idIndicador);
                $indicadorNovo->recebeAlunoId($host, $bd, $usuario, $senha);
                $sucesso = $indicadorNovo->atualizaDesconto($host, $bd, $usuario, $senha);

                $sucessoNotificacao = false;

                //faremos 10 tentativas para notificar o aluno , se todas falharem
                //mostramos que não foi possível notificar o aluno
                for($i = 0;$i < 10 && !$sucessoNotificacao;$i++){

                    //gera notificação para o indicador que ele recebeu 10% de desconto
                    //nas próximas parcelas
                    $conexao->beginTransaction();

                    $titulo = "Desconto por indicação";

                    $texto  = "Por uma correção do sistema, um aluno corrigiu corrigiu seu indicador para";
                    $texto .= " aluno correto, seu desconto de 10% foi removido das próximas parcelas";

                    $textoQuery = "INSERT INTO Notificacao(titulo,texto,chaveAluno)
                                    VALUES (:titulo, :texto,:idIndicador)";
                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(":titulo", $titulo, PDO::PARAM_STR);
                    $query->bindParam(":texto", $texto, PDO::PARAM_STR);
                    $query->bindParam(":idIndicador", 
                        $indicadorNovo->getNumeroInscricao(),PDO::PARAM_INT);

                    $sucessoNotificacao = $query->execute();

                    if(!$sucessoNotificacao){
                        $conexao->rollback();
                    }
                }

                //se conseguiu notificar, confirma transação
                if($sucessoNotificacao){
                    $conexao->commit();
                }else{
                    //se não, mostra mensagem na tela
                    $mensagem = "Não foi possível notificar o aluno 
                                de seu desconto.";
                }

                //Se o aluno estiver em dia e mudou de status,
                // notifica o indicador que ele perdeu ou ganhou
                // seu desconto novamente
                if($emDia && $this->status != $statusAnterior){

                    //Caso o aluno tenha desistido, notifica o indicador que ele perdeu o desconto
                    if ($this->status !== "inscrito" && $statusAnterior === "inscrito") {
                        $sucessoNotificacao = false;

                        //faremos 10 tentativas para notificar o aluno , se todas falharem
                        //mostramos que não foi possível notificar o aluno
                        for($i = 0;$i < 10 && !$sucessoNotificacao;$i++){

                            //gera notificação para o indicador que ele recebeu 10% de desconto
                            //nas próximas parcelas
                            $conexao->beginTransaction();

                            $titulo = "Desconto por indicação";

                            $texto  = "Um de seus indicados encerrou o curso/a etapa, seu desconto de 10%";
                            $texto  .= " por sua indicação foi removido das próximas parcelas";

                            $textoQuery = "INSERT INTO Notificacao(titulo,texto,chaveAluno)
                                            VALUES (:titulo, :texto,:idIndicador)";
                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(":titulo", $titulo, PDO::PARAM_STR);
                            $query->bindParam(":texto", $texto, PDO::PARAM_STR);
                            $query->bindParam(":idIndicador", 
                                $indicadorNovo->getNumeroInscricao(),PDO::PARAM_INT);

                            $sucessoNotificacao = $query->execute();

                            if(!$sucessoNotificacao){
                                $conexao->rollback();
                            }
                        
                        }

                        //se conseguiu notificar, confirma transação
                        if($sucessoNotificacao){
                            $conexao->commit();
                        }else{
                            //se não, mostra mensagem na tela
                            $mensagem = "Não foi possível notificar o aluno 
                                        de seu desconto.";
                        }

                    } // if($this->status === "desistente")

                    else if($this->status === "inscrito" && $statusAnterior != "inscrito"){
                        $sucessoNotificacao = false;

                        //faremos 10 tentativas para notificar o aluno , se todas falharem
                        //mostramos que não foi possível notificar o aluno
                        for($i = 0;$i < 10 && !$sucessoNotificacao;$i++){

                            //gera notificação para o indicador que ele recebeu 10% de desconto
                            //nas próximas parcelas
                            $conexao->beginTransaction();

                            $titulo = "Desconto por indicação";

                            $texto  = "Um de seus indicados retomou o curso, seu desconto de 10%";
                            $texto .= " por sua indicação foi adicionado novamente às próximas";
                            $texto .= " parcelas";

                            $textoQuery = "INSERT INTO Notificacao(titulo,texto,chaveAluno)
                                            VALUES (:titulo, :texto,:idIndicador)";
                            $query = $conexao->prepare($textoQuery);
                            $query->bindParam(":titulo", $titulo, PDO::PARAM_STR);
                            $query->bindParam(":texto", $texto, PDO::PARAM_STR);
                            $query->bindParam(":idIndicador", 
                                $indicadorNovo->getNumeroInscricao(),PDO::PARAM_INT);

                            $sucessoNotificacao = $query->execute();

                            if(!$sucessoNotificacao){
                                $conexao->rollback();
                            }
                        
                        }

                        //se conseguiu notificar, confirma transação
                        if($sucessoNotificacao){
                            $conexao->commit();
                        }else{
                            //se não, mostra mensagem na tela
                            $mensagem = "Não foi possível notificar o aluno 
                                        de seu desconto.";
                        }
                    }
                }
                
            }
        } else {
            // algo deu errado, desfazemos as mudanças
            $conexao->rollBack();
        }

        // Encerramos a conexão com o BD
        $conexao = null;

        return $sucessoUsuario && $sucessoAluno;
    }

    public function atualizaDesconto($host, $bd, $usuario, $senha){
        //Abrimos a conexão com o banco de
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        //primeiro buscamos a matricula do aluno deste ano
        $textoQuery = "SELECT M.idMatricula, M.desconto_individual
                        FROM Matricula M, Cidade C
                        WHERE M.chaveCidade = C.idCidade AND C.ano = YEAR(CURDATE())
                        AND M.chaveAluno = :id";
        $query = $conexao->prepare($textoQuery);
        $query->bindParam(":id", $this->numeroInscricao, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if($linha = $query->fetch()){
            $idMatricula = $linha["idMatricula"];

            $desconto_individual = $linha["desconto_individual"];
    
            //buscamos agora os indicados que garantem desconto ao aluno
            $textoQuery = "SELECT A.numeroInscricao
                           FROM   Aluno A, Matricula M, Cidade C, PgtoMensalidade Pg
                           WHERE  M.chaveAluno = A.numeroInscricao AND M.chaveCidade = C.idCidade
                           AND    C.ano = YEAR( CURDATE()) AND Pg.chaveMatricula = M.idMatricula
                           AND    Pg.numParcela = 0 AND Pg.fechado = 1 AND A.idIndicador = ? 
                           AND    A.status = 'inscrito'";
            $query = $conexao->prepare($textoQuery);
            $query->bindParam(1,$this->numeroInscricao, PDO::PARAM_INT);
            $query->execute();
    
            //O aluno ganha 10% de desconto para cada indicado e mais 10% por ser indicador/indicado,
            // contamos as linhas da query que correspondem aos indicados
    
            $indicados = $query->rowCount();
            $desconto = $indicados * 10;
            if($this->idIndicador){
                $desconto += 10;
            }
            // soma o desconto individual do aluno ao desconto das parcelas
            $desconto += $desconto_individual;
    
            if($desconto > 100) $desconto = 100;
    
            //Agora atualizamos as tabelas alteradas e usaremos transaction para garantir 
            //integridade de dados
            $sucesso = true;
            $textoQuery = "UPDATE PgtoMensalidade
                            SET    desconto = :desconto
                            WHERE  chaveMatricula = :idMatricula AND valorPago = 0 AND numParcela <> 0";
            $query = $conexao->prepare($textoQuery);
            $query->bindParam(":desconto",$desconto,PDO::PARAM_INT);
            $query->bindParam(":idMatricula",$idMatricula, PDO::PARAM_INT);
            $sucesso = $query->execute();
    
            return $sucesso;
    
        }

        return true;

    }

    public function atualizaDescontoAnteriores($host, $bd, $usuario, $senha, $ano){
        //Abrimos a conexão com o banco de
        $conexao = null;
        try{
            $conexao = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $senha);
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        //primeiro buscamos a matricula do aluno deste ano
        $textoQuery = "SELECT M.idMatricula, M.desconto_individual
                        FROM Matricula M, Cidade C
                        WHERE M.chaveCidade = C.idCidade AND C.ano = :ano
                        AND M.chaveAluno = :id";
        $query = $conexao->prepare($textoQuery);
        $query->bindParam(":id", $this->numeroInscricao, PDO::PARAM_INT);
        $query->bindParam(":ano", $ano, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute();

        if($linha = $query->fetch()){
            $idMatricula = $linha["idMatricula"];

            $desconto_individual = $linha["desconto_individual"];
    
            //buscamos agora os indicados que garantem desconto ao aluno
            $textoQuery = "SELECT A.numeroInscricao
                           FROM   Aluno A, Matricula M, Cidade C, PgtoMensalidade Pg
                           WHERE  M.chaveAluno = A.numeroInscricao AND M.chaveCidade = C.idCidade
                           AND    C.ano = :ano AND Pg.chaveMatricula = M.idMatricula
                           AND    Pg.numParcela = 0 AND Pg.fechado = 1 AND A.idIndicador = ? 
                           AND    A.status = 'inscrito'";
            $query = $conexao->prepare($textoQuery);
            $query->bindParam(":ano", $ano, PDO::PARAM_INT);
            $query->bindParam(1,$this->numeroInscricao, PDO::PARAM_INT);
            $query->execute();
    
            //O aluno ganha 10% de desconto para cada indicado e mais 10% por ser indicador/indicado,
            // contamos as linhas da query que correspondem aos indicados
    
            $indicados = $query->rowCount();
            $desconto = $indicados * 10;
            if($this->idIndicador){
                $desconto += 10;
            }
            // soma o desconto individual do aluno ao desconto das parcelas
            $desconto += $desconto_individual;
    
            if($desconto > 100) $desconto = 100;
    
            //Agora atualizamos as tabelas alteradas e usaremos transaction para garantir 
            //integridade de dados
            $sucesso = true;
            $textoQuery = "UPDATE PgtoMensalidade
                            SET    desconto = :desconto
                            WHERE  chaveMatricula = :idMatricula AND valorPago = 0 AND numParcela <> 0";
            $query = $conexao->prepare($textoQuery);
            $query->bindParam(":desconto",$desconto,PDO::PARAM_INT);
            $query->bindParam(":idMatricula",$idMatricula, PDO::PARAM_INT);
            $sucesso = $query->execute();
    
            return $sucesso;
    
        }

        return true;

    }

    // Getters e setters
    public function getNumeroInscricao()
    {
        return $this->numeroInscricao;
    }
    public function setNumeroInscricao($numeroInscricao)
    {
        $this->numeroInscricao = $numeroInscricao;

        return $this;
    }
    public function getLogin()
    {
        return $this->login;
    }

    public function getSenha()
    {
        return $this->senha;
    }
    public function setSenha($senha)
    {
        $this->senha = $senha;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getIdIndicador()
    {
        return $this->idIndicador;
    }
    public function setIdIndicador($idIndicador)
    {
        $this->idIndicador = $idIndicador;

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

    public function getTelefone2()
    {
        return $this->telefone2;
    }
    public function setTelefone2($telefone2)
    {
        $telefone2 = str_replace("(","",$telefone2);
        $telefone2 = str_replace(")","",$telefone2);
        $telefone2 = str_replace("-","",$telefone2);
        $this->telefone2 = $telefone2;

        return $this;
    }

    public function getTelefone3()
    {
        return $this->telefone3;
    }
    public function setTelefone3($telefone3)
    {
        $telefone3 = str_replace("(","",$telefone3);
        $telefone3 = str_replace(")","",$telefone3);
        $telefone3 = str_replace("-","",$telefone3);
        $this->telefone3 = $telefone3;

        return $this;
    }

    public function getEscolaridade()
    {
        return $this->escolaridade;
    }
    public function setEscolaridade($escolaridade)
    {
        $this->escolaridade = $escolaridade;

        return $this;
    }

    public function getCurso()
    {
        return $this->curso;
    }
    public function setCurso($curso)
    {
        $this->curso = $curso;

        return $this;
    }

    public function getAtivo()
    {
        return $this->ativo;
    }
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;

        return $this;
    }

    public function getCep()
    {
        
        $cepFormatado = $this->cep;
        return $this->cep;
    }
    public function setCep($cep)
    {
        $this->cep = $cep;

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

    public function getNumero()
    {
        return $this->numero;
    }
    public function setNumero($numero)
    {
        $this->numero = $numero;

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

    public function getPais()
    {
        return $this->pais;
    }
    public function setPais($pais)
    {
        $this->pais = $pais;

        return $this;
    }

    public function setTipoCurso($tipoCurso)
    {
        return $this->tipoCurso = $tipoCurso;
    }
    public function getTipoCurso(){
        return $this->tipoCurso;
    }

    public function setTipoCadastro($tipoCadastro){
        return $this->tipoCadastro = $tipoCadastro;
    }
    public function getTipoCadastro(){
        return $this->tipoCadastro;
    }

    // Função que retorna o indicador, não apenas seu ID
    // Recebe: 
    // $host:         host do banco de dados mysql
    // $bd:           banco de dados a ser acessado
    // $usuario:      nome de usuário a ser usado para acesso ao bd
    // $senha:        senha a ser usada para acesso ao bd
    //
    // Retorna: retorna o indicador caso ele exista, do contrário, retorna null
    public function getIndicador($host, $bd, $usuario, $senha)
    {
        $indicador = new Aluno("");
        $indicador->setNumeroInscricao($this->idIndicador);
        $sucesso = $indicador->recebeAlunoId($host, $bd, $usuario, $senha);
        if(!$sucesso){
            // esse indicador não existe
            return null;
        }
        return $indicador;
    }

    // Função que retorna o endereço formatado
    //
    // Retorna: retorna uma string contendo o endereço formatado

    public function retornaEndereco()
    {

        $endereco = "";
        if($this->rua != null){
            $endereco = $this->rua;
        }
        if( $this->numero  != null && $this->rua != null){
            $endereco .= ", ".$this->numero;
        }

        if( $this->complemento != null && $this->rua != null){
            $endereco .= ", Complemento: ".$this->complemento;
        }

        if( $this->bairro  != null){
            $endereco .= " - ". $this->bairro." -";
        }

        if( $this->cidade  != null){
            $endereco .= " ". $this->cidade;
        }

        if($this->estado  != null && $this->cidade != null){
            $endereco .= "/".$this->estado;
        }else if( $this->estado != null &&$this->cidade == null ){
            $endereco .= " ".$this->estado;
        }

        
        return $endereco;
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

    public function getRecebeEmail()
    {
        return $this->recebeEmail;
    }

    public function setRecebeEmail($recebeEmail)
    {
        $this->recebeEmail = $recebeEmail;

        return $this;
    }

    public function getObservacao()
    {
        return $this->observacao;
    }

    public function setObservacao($observacao)
    {
        $this->observacao = $observacao;

        return $this;
    }
}
?>
