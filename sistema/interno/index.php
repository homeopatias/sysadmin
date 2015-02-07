<?php
    ini_set('default_charset', 'utf-8');
    header('Content-Type: text/html; charset=utf-8');
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include("modulos/head.php"); ?>
        <title>Bem-vindo - Homeopatias.com</title>
        <script>
            $(document).ready(function(){
                // marca as notificações do aluno como lidas, caso isso seja aplicável
                if($("#modal-notificacoes")){
                    $("#modal-notificacoes").on('show.bs.modal', function(e) {
                        $.get('rotinas/notificacoes_lidas.php', {}, function(sucesso){
                            if(sucesso) {
                                $("#alerta-notificacoes").remove();
                            }
                        });
                    });
                }

                $("#modal-muda-dados #escolaridade").change(function(){
                    if($(this).val() === "superior incompleto" || $(this).val() === "superior completo"   ||
                       $(this).val() === "mestrado"            || $(this).val() === "doutorado" ){
                        $("#modal-muda-dados #curso").parent().show(500);
                    }else{
                        $("#modal-muda-dados #curso").parent().hide(500);
                    }
                });
            });
        </script>
    </head>
    <body>
        <?php
            // mensagem a ser exibida acima do formulário de login, caso seja necessário
            $mensagem = isset($_GET['mensagem']) ? htmlspecialchars($_GET['mensagem']) : "";

            // importa a função para execução do login e armazenamento da sessão
            include("rotinas/processa_login.php");

            // lemos as credenciais do banco de dados
            $dados = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/../config.json");
            $dados = json_decode($dados, true);

            foreach($dados as $chave => $valor) {
                $dados[$chave] = str_rot13($valor);
            }

            $host    = $dados["host"];
            $usuario = $dados["nome_usuario"];
            $senhaBD = $dados["senha"];

            // cria conexão com o banco para uso ao longo da página
            $conexao = null;
            $db      = "homeopatias";
            try {
                $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $usuario, $senhaBD);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }

            // se o usuario chegou aqui atraves de um formulário, tenta fazer login
            if (isset($_POST["submit"])) {
                // executa a função importada
                $mensagem = processaLogin($_POST["login"], $_POST["senha"]);

                $sucesso = isset($_SESSION['usuario']);

                // caso o login tenha sido bem sucedido e tenha sido de um aluno, ele é
                // redirecionado para a tela de avaliação de professores caso ele já tenha
                // feito uma aula e não tenha avaliado ainda (e caso o professor dessa aula
                // já tenha sido definido)

                if($sucesso && unserialize($_SESSION['usuario']) instanceof Aluno) {
                    $idAluno = unserialize($_SESSION['usuario'])->getNumeroInscricao();

                    $textoQuery  = "SELECT F.chaveAula FROM Frequencia F INNER JOIN Aula A ON 
                                    A.idAula = F.chaveAula WHERE F.chaveAluno = ? AND F.presenca = 1 
                                    AND F.jaAvaliou = 0 AND A.idProfessor IS  NOT NULL ORDER BY 
                                    A.data ASC";

                    $query = $conexao->prepare($textoQuery);
                    $query->bindParam(1, $idAluno, PDO::PARAM_INT);
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    if($linha = $query->fetch()) {
                        // encontramos uma aula que não foi avaliada, o aluno deve avaliar essa aula
                        $idAula = $linha['chaveAula'];

                        $url = 'avaliar_aula.php?idAula=' . $idAula;
        ?>

        <!-- redireciona o usuário para a página de avaliação de aula -->
        <meta http-equiv="refresh" content=<?= '"0; url=' . $url . '"' ?>>
        <script type="text/javascript">
            window.location = <?= '"' . $url . '"' ?>;
        </script>

        <?php
                    }
                }
            }

            include("modulos/navegacao.php");

            // mostra o formulário de login se o usuario nao estiver logado no sistema
            if (!isset($_SESSION["usuario"])){
        ?>
        <div class="col-xs-12 vertical-center" style="height:50%">
            <div class="center-block col-sm-6 no-float"
                 style="max-width: 300px">
                <form method="POST" class="conteudo" id="form-login" action="index.php "
                      style="margin-top: 50%">
                    <?php
                        if(mb_strlen($mensagem, 'UTF-8') !== 0){
                            echo "<p class=\"warning\">$mensagem</p>";
                        }
                        if(isset($_GET["cadastroSucesso"])) {
                            echo "<p class=\"sucesso\">Cadastro efetuado com sucesso!</p>";
                        }
                    ?>
                    <div class="form-group">
                        <label for="login">Nome de usuário: </label>
                        <input type="text" name="login" id="login" class="form-control input-mir" 
                               required pattern=".{3,100}"
                               title="O login deve ter de 3 a 100 caracteres"
                               placeholder="Nome de usuario" autocomplete="login" autofocus>
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha: </label>
                        <input type="password" name="senha" id="senha" class="form-control input-mir"
                               required pattern=".{6,72}"
                               title="A senha deve ter de 6 a 72 caracteres"
                               placeholder="Senha">
                    </div>
                    <input type="submit" name="submit" value="Login" class="btn btn-primary pull-right">
                    <br>
                </form>
                
                <div class="conteudo" style="position: relative; top: -70px">
                    <a href="cadastro_aluno_extensao.php">Cadastro no curso de extensão</a>
                    <a href="cadastro_aluno_pos.php">Cadastro no curso de pós graduação</a>
                    <br>
                    <a href="recuperar_conta.php">Recuperar senha ou usuário</a>
                    <br>
                    <?php
                        $sql = "SELECT nome FROM Instituicao WHERE inicioInsc <= CURDATE() AND
                                fimInsc >= CURDATE()";
                        $query = $conexao->prepare($sql);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        $instituicoes = array();
                        while($linha = $query->fetch()) {
                            $instituicoes[] = $linha['nome'];
                        }

                        if(in_array('atenemg', $instituicoes)) {
                    ?>
                    <a href="cadastro_associado_atenemg.php">Associar-se à ATENEMG</a>
                    <br>
                    <?php
                        }

                        if(in_array('conahom', $instituicoes)) {
                    ?>
                    <a href="cadastro_associado_conahom.php">Associar-se ao CONAHOM</a>
                    <?php
                        }
                    ?>
                </div>
               
            </div>
        </div>
        <?php
            }else{
                $usuarioLogado = unserialize($_SESSION["usuario"]);
                $mensagem = "";
                if(isset($_GET["mensagem"])){
                    $mensagem = htmlspecialchars($_GET["mensagem"]);
                }
                require_once("entidades/Administrador.php");
                require_once("entidades/Associado.php");
                require_once("entidades/Aluno.php");
        ?>
        <header class="conteudo col-xs-10" style="margin-left: 10%">
            <?php
                if(isset($_GET["sucessoSenha"]) && $_GET["sucessoSenha"]) {
            ?>
            <h4 class="sucesso">Senha alterada com sucesso</h4>
            <?php
                } else if(isset($_GET["sucessoEdicao"]) && $_GET["sucessoEdicao"]) {
            ?>
            <h4 class="sucesso">Dados editados com sucesso</h4>
            <?php
                } else if(isset($_GET["sucessoAval"]) && $_GET["sucessoAval"]){
            ?>
            <h4 class="sucesso">Obrigado pela sua avaliação!</h4>
            <?php
                } else if(isset($_GET["pgtoSucesso"])) {
            ?>
            <p class="sucesso">A Homeobrás agradece seu apoio!</p>   
            <?php
                } else if(isset($_GET["assocRenovada"])) {
            ?>
            <p class="sucesso">Sua associação foi renovada!</p>
            <?php
                } else {
            ?>
            <p class="warning"><?= $mensagem ?></p>
            <?php
                }
            ?>
            <h1 style="font-weight: bold;">
                Bem-vindo, 
                <?= htmlspecialchars($usuarioLogado->getNome()) ?>!
            </h1>
            <a href="#" data-toggle="modal"
               data-target="#modal-muda-senha">Alterar senha</a>
			<div id="foto" class="pull-right" align="center">
				<img src=<?php 
					$usuario = unserialize($_SESSION['usuario']);
					if( file_exists("fotos/".$usuario->getId().".png" ) ){
						echo "\"fotos/".$usuario->getId().".png\"";
					}else{
						echo "\"fotos/Padrao.png\"";;
					}
				?>
				width="150px" height="200px">
				<br>
				<a href="#" data-toggle="modal"
					data-target="#modal-muda-foto">Alterar foto</a>
				<br>
				<a href="#" data-toggle="modal"
					data-target="#modal-exclui-foto">Excluir foto</a>
			</div>
            <br>
            <a href="#" data-toggle="modal"
               data-target="#modal-muda-dados">Alterar dados cadastrais</a>
            <?php

                if (unserialize($_SESSION['usuario']) instanceof Associado &&
                    unserialize($_SESSION['usuario'])->getEnviouDocumentos()) {
            ?>
            <br><br>
            <a href="visualizar_pagamentos_associado.php">Visualizar pagamentos</a>
            <?php

                    $sql = "SELECT
                                EXISTS(SELECT nome FROM Instituicao WHERE inicioInsc <= CURDATE() AND
                                       fimInsc >= CURDATE() AND nome = ?)
                                as inscAberta,
                                
                                EXISTS(SELECT P.idPagAnuidade FROM PgtoAnuidade P, Instituicao I
                                       WHERE P.ano = I.ano AND I.nome = ? AND P.chaveAssoc = ?)
                                as inscritoAno,

                                valorInscricao, valorAnuidade

                            FROM Instituicao WHERE inicioInsc <= CURDATE() AND
                                       fimInsc >= CURDATE() AND nome = ?";
                    $query = $conexao->prepare($sql);
                    $query->bindParam(1, unserialize($_SESSION['usuario'])->getInstituicao());
                    $query->bindParam(2, unserialize($_SESSION['usuario'])->getInstituicao());
                    $query->bindParam(3, unserialize($_SESSION['usuario'])->getIdAssoc());
                    $query->bindParam(4, unserialize($_SESSION['usuario'])->getInstituicao());
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    $query->execute();

                    $linha = $query->fetch();
                    $inscAberta = $linha['inscAberta'];
                    $inscritoAno = $linha['inscritoAno'];

                    if ($inscAberta && !$inscritoAno) {
						?>
						<br>
						<a href="#" data-toggle="modal" data-target="#modal-reassociar">Renovar associação</a>     
						<!-- popup "modal" do bootstrap para reassociação -->
						<div class="modal fade" id="modal-reassociar" tabindex="-1" role="dialog"
							aria-labelledby="modal-reassociar" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
										X
									</button>
									<h4 class="modal-title">Renovação de associação</h4>
									</div>
									<div class="modal-body"
										style="font-size: 0.9em; white-space: pre-line; overflow: none">
										<?php
											$instituicao = unserialize($_SESSION['usuario'])->getInstituicao();

											$valorInscricao = htmlspecialchars($linha['valorInscricao']);
											$valorAnuidade  = htmlspecialchars($linha['valorAnuidade']);

											$valorInscricao = number_format($valorInscricao, 2);
											$valorAnuidade  = number_format($valorAnuidade, 2);
										?>
										Inscrição: R$ <?= $valorInscricao ?>

										Anuidade: R$ <?= $valorAnuidade ?>

										Faça sua pré-inscrição e tenha acesso às formas de pagamento pelo PagSeguro.

										Sua inscrição será efetivada após confirmação do pagamento e aprovação dos documentos abaixo. 

										Documentos necessários:

										<b>1 Foto 3x4

										Curriculum vitae

										Xerox Autenticado em cartório de:

										1) Identidade;

										2) CPF;

										3) Comprovante de endereço;

										<?php
											if ($instituicao === "atenemg") {
										?>

										4) Certificados de curso das terapias com as quais trabalha - Mínimo de 180 horas, por especialidade.</b>


										Sua documentação será analisada e a resposta será enviada por e-mail ou carta.


										Endereço para envio:

										ATENEMG
										Av. Antônio Abraão Caram, 430/701
										31275-000 Belo Horizonte/MG
										Telefone: (31) 3439 2500
										<?php
											} else if ($instituicao === "conahom") {
										?>
										4) Certificados de curso de Homeopatia - Mínimo de 400 horas.

										5) Certificados de curso de Fitoterapia - Mínimo de 180 horas.</b>


										Sua documentação será analisada e a resposta será enviada por e-mail ou carta.

										Endereço para envio:

										CONAHOM
										Av. Antônio Abraão Caram, 430/701
										31275-000 Belo Horizonte/MG
										Telefone: (31) 3439-2500
										<?php
											}
										?>
										<br>
										<label style="width: 100%; white-space: normal; font-size: 1.2em">
										Marcando a opção abaixo, você confirma que leu e compreendeu
										todas as informações do curso expostas na tela de informações
										referida acima. Confirma que concorda com todos os termos e
										está ciente dos procedimentos informados referentes às aulas,
										certificados, trancamento de inscrição, módulos do curso,
										e todas as outras abrangidas.
										</label>
										<br>
										<div style="white-space: normal">
											<input type="checkbox" id="li-termos" style="font-size: 1.1em">
											<label for="li-termos" style="font-size: 1.1em">Confirmo</label>
										</div>
									</div>
									<div class="modal-footer">
										<form action="rotinas/associado/renovar_associacao.php" method="POST">
											<input type="submit" class="btn btn-primary" id="btn-reassociar"
											name="submit" value="Reassociar" disabled>
										</form>
									</div>
								</div>
							</div>
						</div>
						<br>
						<br><br>
						<?php
							if ($usuarioLogado instanceof Administrador &&
								$usuarioLogado->getNivelAdmin() === "administrador" &&
								($usuarioLogado->getPermissoes() & 2) ) {
								// caso o usuário logado seja administrador de curso, avisamos
								// se existem justificativas de ausência a ser avaliadas
								$textoQuery = "SELECT count(aprovacaoPendente) as numPendentes
											FROM Frequencia F WHERE aprovacaoPendente = 1
											AND presenca = 0";

								$query = $conexao->prepare($textoQuery);
								$query->setFetchMode(PDO::FETCH_ASSOC);
								$query->execute();

								$numPendentes = $query->fetch()['numPendentes'];
								if ($numPendentes) {
									$numJustificativas = $numPendentes . " justificativa" .
														($numPendentes != 1 ? 's' : '');
						?>
						<a href="avaliar_ausencias.php" style="text-decoration: none">
							<b class="warning">
								Você tem <?= $numJustificativas ?> de ausência para avaliar
							</b>
						</a>
						<br><br>
						<?php
								}
							}
					}
            }
            ?>
            <br><br>
            <p><b>Tipo de usuário:</b>
                <?php
                    if($usuarioLogado instanceof Administrador){
                        // determinamos o tipo de administrador pelo nível de privilégio
                        // do mesmo no sistema
                        echo mb_convert_case($usuarioLogado->getNivelAdmin(), MB_CASE_TITLE, "UTF-8");
                        echo "</p>";
                    }else if($usuarioLogado instanceof Aluno){
                        echo "Aluno";
                        // procuramos no banco de dados se o aluno tem notificações não-lidas
                        $textoQuery = "SELECT titulo, texto FROM Notificacao
                                       WHERE chaveAluno = ? AND lida = 0
                                       ORDER BY idNotificacao DESC";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $usuarioLogado->getNumeroInscricao());
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();

                        if($query->rowCount()){
                            // se houverem notificações a ser lidas, criamos
                            // o modal para exibí-las
                ?>
            <!-- modal para listagem de notificações -->
            <div class="modal fade" id="modal-notificacoes" tabindex="-1" role="dialog" 
                 aria-labelledby="modal-notificacoes" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Notificações</h4>
                        </div>
                        <div class="modal-body">
                            <p style="border-bottom: dashed #AAA 5px"></p>
                <?php
                        while($linha = $query->fetch()) {
                ?>
                            <h4><b><?= htmlspecialchars($linha['titulo']) ?></b></h4>
                            <p>
                                <?= nl2br(htmlspecialchars($linha['texto'])) ?>
                            </p>
                            <p style="border-bottom: dashed #AAA 5px"></p>
                <?php
                        }
                ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- agora criamos o ícone de visualização de notificações -->
            <a class="fa-stack fa-lg pull-right" id="alerta-notificacoes"
               style="position:relative; top:-130px; color: #400; text-decoration: none"
               href="#" data-toggle="modal" data-target="#modal-notificacoes">
                <i class="fa fa-circle fa-stack-2x warning"></i>
                <i class="fa fa-exclamation fa-stack-1x fa-inverse"></i>
            </a>
                <?php

                        }
                ?>
            <p>
                <b>Número de inscrição:</b>
                <?php
                        echo htmlspecialchars($usuarioLogado->getNumeroInscricao());
                ?>
            </p>
            <p>
                <b>Status:</b>
                <?php
                        if($usuarioLogado->getStatus() === "preinscrito")
                            echo "Pré-inscrito";
                        else if($usuarioLogado->getStatus() === "inscrito")
                            echo "Inscrito";
                        else if($usuarioLogado->getStatus() === "desistente")
                            echo "Desistente";
                        else if($usuarioLogado->getStatus() === "formado")
                            echo "Formado";
                        else if($usuarioLogado->getStatus() === "inativo"){
                            echo "Inativo";
                        }
                        echo "</p>";
                ?>
            </p>
            <p>
                <a href="rotinas/acessar_moodle.php">Acessar a área do aluno</a>
            </p>
                <?php
                        // agora vamos mostrar os dados de matrícula do aluno, para isso
                        // descobrimos se o aluno está matriculado atualmente

                        $textoQuery  = "SELECT M.idMatricula, M.etapa, M.aprovado, C.nome 
                                        FROM Matricula M, Cidade C 
                                        WHERE M.chaveAluno = ? AND M.chaveCidade = C.idCidade 
                                        AND C.ano = YEAR(CURDATE())";

                        $query = $conexao->prepare($textoQuery);
                        $query->bindParam(1, $usuarioLogado->getNumeroInscricao(), PDO::PARAM_INT);
                        $query->setFetchMode(PDO::FETCH_ASSOC);
                        $query->execute();
                        $matriculado = false;

                        if($linha = $query->fetch()) {
                            $matriculado = true;
                ?>
            <br>
                <?php
                        if(!is_null($linha["aprovado"])) {
                ?>
            <p class=<?= "\"" . ($linha["aprovado"] ? "sucesso" : "warning") . "\"" ?>><b>
                Aluno <?= $linha["aprovado"] ? "aprovado" : "reprovado" ?> no ano atual
            </b></p> 
                <?php
                        }
                ?>
            <p><b>Aluno atualmente matriculado em <?= htmlspecialchars($linha["nome"]) ?></b></p>
            <p><b>Id da matrícula: </b> <?= htmlspecialchars($linha["idMatricula"]) ?></b></p>
            <p><b>Etapa: </b> <?= htmlspecialchars($linha["etapa"]) ?></b>
            <br>
                <?php

                        } else {
                ?>
            <br>
            <p><b>Aluno não-matriculado no momento</b></p>
                <?php
                        }
                ?>
                    <p class="col-sm-12">
                        <a style="cursor: pointer"
                           href= "visualizar_informacoes_curso.php">
                            Visualizar dados de curso
                        </a>
                    </p>
                    <br><br>
                <?php    }else if($usuarioLogado instanceof Associado){
                        echo "Associado";
                ?>
            <p>
                <b>Instituição:</b>
                <?php
                        echo htmlspecialchars(mb_strtoupper($usuarioLogado->getInstituicao(), 'UTF-8'));
                        echo "</p>";
                    }else{
                        echo "Erro no sistema";
                    }
                ?>
            <p>
                <b>CPF:</b>
                <?php
                    $cpfNum = str_split($usuarioLogado->getCPF());

                    $cpf  = implode("", array_slice($cpfNum, 0, 3)) . ".";
                    $cpf .= implode("", array_slice($cpfNum, 3, 3)) . ".";
                    $cpf .= implode("", array_slice($cpfNum, 6, 3)) . "-";
                    $cpf .= implode("", array_slice($cpfNum, 9, 2));
                    $cpf  = htmlspecialchars($cpf);
                    echo $cpf;
                ?>
            </p>
            <p>
                <b>Data de inscrição:</b>
                <?= date("d/m/Y h:i:s" ,$usuarioLogado->getDataInscricao()) ?>
            </p>
            <p>
                <b>E-mail:</b>
                <?= $usuarioLogado->getEmail() ?>
            </p>
            <p>
                <b>Login:</b>
                <?= $usuarioLogado->getLogin() ?>
            </p>                        
        </header>
        <!-- modal de mudanca de senha -->
        <div class="modal fade" id="modal-muda-senha" tabindex="-1" role="dialog" 
             aria-labelledby="modal-muda-senha" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/mudar_senha.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Mudança de senha</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="antiga">Insira sua senha atual:</label>
                                <input type="password" name="antiga" id="antiga" required
                                       pattern="^.{6,72}$" placeholder="Senha atual"
                                       title="A senha deve ter de 6 a 72 caracteres"
                                       class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="nova">Senha nova:</label>
                                <input type="password" name="nova" id="nova" required
                                       pattern="^.{6,72}$" placeholder="Senha nova"
                                       title="A senha deve ter de 6 a 72 caracteres"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Alterar senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- modal para alteração de dados cadastrais -->
        <div class="modal fade" id="modal-muda-dados" tabindex="-1" role="dialog" 
             aria-labelledby="modal-muda-dados" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/alterar_dados.php">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Alterar dados</h4>
                        </div>
                        <div class="modal-body">
                            <!-- o formulário em si fica dentro dessa div -->

                            <div class="form-group">
                                <label for="nome">Nome:</label>
                                <input type="text" name="nome" id="nome" required
                                       pattern="^.{3,100}$" title="O nome deve ter de 3 a 100 caracteres"
                                       placeholder="Nome" class="form-control" autocomplete="off"
                                       value=<?= "\"" . $usuarioLogado->getNome() . "\"" ?>>
                            </div>
                            <div class="form-group">
                                <label for="login">Nome de usuário:</label>
                                <input type="text" name="login" id="login" required
                                       pattern="^.{3,100}$" placeholder="Nome de usuário"
                                       title="O login deve ter de 3 a 100 caracteres"
                                       class="form-control"
                                       value=<?= "\"" . $usuarioLogado->getLogin() . "\"" ?>>
                            </div>
                            <?php
                                if(!($usuarioLogado instanceof Administrador) ||
                                     $usuarioLogado->getNivelAdmin() !== 'administrador') {
                                    // formatando o cpf

                                    $cpfOriginal = str_split($usuarioLogado->getCPF());
                                    $cpf  = implode("", array_slice($cpfOriginal, 0, 3)) . ".";
                                    $cpf .= implode("", array_slice($cpfOriginal, 3, 3)) . ".";
                                    $cpf .= implode("", array_slice($cpfOriginal, 6, 3)) . "-";
                                    $cpf .= implode("", array_slice($cpfOriginal, 9, 2));
                                    $cpf  = htmlspecialchars($cpf);
                            ?>
                            <div class="form-group">
                                <label for="cpf">CPF:</label>
                                <input type="text" name="cpf" id="cpf" required
                                       pattern="^(\d{3}\.\d{3}\.\d{3}\-\d{2})|(\d{11})$"
                                       placeholder="xxx.xxx.xxx-xx" class="form-control"
                                       value=<?= "\"" . $cpf . "\"" ?>>
                            </div>
                            <?php } else { ?>
                            <input type="hidden" name="cpf" value='999.999.999-99'>
                            <?php } ?>
                            <div class="form-group">
                                <label for="email">E-mail:</label>
                                <input type="email" name="email" id="email" required
                                       placeholder="E-mail"
                                       title="Insira um e-mail válido"
                                       class="form-control"
                                       value=<?= "\"" . $usuarioLogado->getEmail() . "\"" ?>>
                            </div>
                            <?php 
                                // se o usuário atual é um aluno ou associado, permitimos
                                // alterar o telefone e endereço
                                if($usuarioLogado instanceof Aluno ||
                                   $usuarioLogado instanceof Associado) {
                                    $telefoneOriginal = $usuarioLogado->getTelefone();
                                    $telefoneOriginal = str_split($telefoneOriginal);
                                    $telefone  = '(';
                                    $telefone .= implode('', array_slice($telefoneOriginal, 0, 2));
                                    $telefone .= ')';
                                    $telefone .= implode('', array_slice($telefoneOriginal, 2, 4));
                                    $telefone .= '-';
                                    $telefone .= implode('', array_slice($telefoneOriginal, 6));
                            ?>
                            <div class="form-group">
                                <label for="telefone">Telefone do aluno:</label>
                                <input type="tel" name="telefone" id="telefone" required
                                       placeholder="(xx)xxxx-xxxx" pattern="^\(?\d{2}\)?\d{4}-?\d{4,7}$"
                                       title="Insira um telefone válido"
                                       class="form-control"
                                       value=<?= "\"" . $telefone . "\"" ?>>
                            </div>
                            <div class="form-group col-sm-12" >
                                <label for="">Endereço do aluno:</label>
                                <div style="display:block">
                                    <?php
                                        $cepOriginal = str_split($usuarioLogado->getCep());

                                        $cep  = implode("", array_slice($cepOriginal, 0, 5)) . "-"; ;
                                        $cep .= implode("", array_slice($cepOriginal, 5, 8));
                                    ?>
                                    <div  class="col-sm-6 col-md-4 " 
                                        style="padding-top:10px;padding-bot:10px">
                                        <label for="cep" style="display:inline">CEP :</label>
                                        <input type="text" name="cep" id="cep"
                                            pattern="^[0-9]{2}.?[0-9]{3}-?[0-9]{3}$" 
                                            placeholder="xxxxx-xxx"
                                            title="Insira um CEP válido"
                                            class="form-control"
                                            style="width:90px" required
                                            value=<?= "\"". $cep . "\""?>>
                                    </div>
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="rua">Rua :</label>
                                        <input type="text" name="rua" id="rua"
                                            pattern="^.{0,200}$" placeholder="Rua"
                                            title="A rua deve ter no máximo 200 caracteres"
                                            class="form-control"
                                            style="width:150px " required
                                            value=<?= "\"" . $usuarioLogado->getRua() . "\"" ?>>
                                    </div>
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="numero">
                                            Numero :</label>
                                        <input type="text" name="numero" id="numero"
                                            placeholder="xx"
                                            title="Insira o numero de sua residência"
                                            class="form-control"
                                            style="width:80px ;" required
                                            value=<?= "\"" . $usuarioLogado->getNumero() . "\"" ?>>

                                    </div>
        
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="bairro" >
                                            Bairro :</label>
                                        <input type="text" name="bairro" id="bairro"
                                            placeholder="Bairro"
                                            title="Insira o bairro de sua residência"
                                            class="form-control"
                                            style="width:120px ;" required
                                            value=<?= "\"" . $usuarioLogado->getBairro() . "\"" ?>>
                                    </div>
        
                                    
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="cidade" >
                                            Cidade :</label>
                                        <input type="text" name="cidade" id="cidade"
                                            placeholder="Cidade"
                                            title="Insira o nome de sua cidade"
                                            class="form-control"
                                            style="width:150px ;" required
                                            value=<?= "\"" . $usuarioLogado->getCidade() . "\"" ?>>
                                    </div>
                                    <div  class="col-sm-6 col-md-4"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="estado">
                                            Estado :</label>
                                        <select name="estado" id="estado" class="form-control"
                                        style="width:120px">
                                            <option value="AC" 
                                                <?= $usuarioLogado->getEstado() === 'AC' ? 'selected' : '' ?>>Acre</option>
                                            <option value="AL" 
                                                <?= $usuarioLogado->getEstado() === 'AL' ? 'selected' : '' ?>>Alagoas</option>
                                            <option value="AM" 
                                                <?= $usuarioLogado->getEstado() === 'AM' ? 'selected' : '' ?>>Amazonas</option>
                                            <option value="AP" 
                                                <?= $usuarioLogado->getEstado() === 'AP' ? 'selected' : '' ?>>Amapá</option>
                                            <option value="BA" 
                                                <?= $usuarioLogado->getEstado() === 'BA' ? 'selected' : '' ?>>Bahia</option>
                                            <option value="CE" 
                                                <?= $usuarioLogado->getEstado() === 'CE' ? 'selected' : '' ?>>Ceará</option>
                                            <option value="DF" 
                                                <?= $usuarioLogado->getEstado() === 'DF' ? 'selected' : '' ?>>Distrito Federal</option>
                                            <option value="ES" 
                                                <?= $usuarioLogado->getEstado() === 'ES' ? 'selected' : '' ?>>Espírito Santo</option>
                                            <option value="GO" 
                                                <?= $usuarioLogado->getEstado() === 'GO' ? 'selected' : '' ?>>Goiás</option>
                                            <option value="MA" 
                                                <?= $usuarioLogado->getEstado() === 'MA' ? 'selected' : '' ?>>Maranhão</option>
                                            <option value="MT" 
                                                <?= $usuarioLogado->getEstado() === 'MT' ? 'selected' : '' ?>>Mato Grosso</option>
                                            <option value="MS" 
                                                <?= $usuarioLogado->getEstado() === 'MS' ? 'selected' : '' ?>>Mato Grosso do Sul</option>
                                            <option value="MG" 
                                                <?= $usuarioLogado->getEstado() === 'MG' ? 'selected' : '' ?>>Minas Gerais</option>
                                            <option value="PA" 
                                                <?= $usuarioLogado->getEstado() === 'PA' ? 'selected' : '' ?>>Pará</option>
                                            <option value="PB" 
                                                <?= $usuarioLogado->getEstado() === 'PB' ? 'selected' : '' ?>>Paraíba</option>
                                            <option value="PR" 
                                                <?= $usuarioLogado->getEstado() === 'PR' ? 'selected' : '' ?>>Paraná</option>
                                            <option value="PE" 
                                                <?= $usuarioLogado->getEstado() === 'PE' ? 'selected' : '' ?>>Pernambuco</option>
                                            <option value="PI" 
                                                <?= $usuarioLogado->getEstado() === 'PI' ? 'selected' : '' ?>>Piauí</option>
                                            <option value="RJ" 
                                                <?= $usuarioLogado->getEstado() === 'RJ' ? 'selected' : '' ?>>Rio de Janeiro</option>
                                            <option value="RN" 
                                                <?= $usuarioLogado->getEstado() === 'RN' ? 'selected' : '' ?>>Rio Grande do Norte</option>
                                            <option value="RO" 
                                                <?= $usuarioLogado->getEstado() === 'RO' ? 'selected' : '' ?>>Rondônia</option>
                                            <option value="RS" 
                                                <?= $usuarioLogado->getEstado() === 'RS' ? 'selected' : '' ?>>Rio Grande do Sul</option>
                                            <option value="RR" 
                                                <?= $usuarioLogado->getEstado() === 'RR' ? 'selected' : '' ?>>Roraima</option>
                                            <option value="SC" 
                                                <?= $usuarioLogado->getEstado() === 'SC' ? 'selected' : '' ?>>Santa Catarina</option>
                                            <option value="SE" 
                                                <?= $usuarioLogado->getEstado() === 'SE' ? 'selected' : '' ?>>Sergipe</option>
                                            <option value="SP" 
                                                <?= $usuarioLogado->getEstado() === 'SP' ? 'selected' : '' ?>>São Paulo</option>
                                            <option value="TO" 
                                                <?= $usuarioLogado->getEstado() === 'TO' ? 'selected' : '' ?>>Tocantins</option>
                                        </select>
                                    </div>
                                    <div  class="col-sm-6 col-md-12"
                                    style="padding-top:10px;padding-bot:10px">
                                        <label for="complemento">
                                            Complemento :</label>
                                        <input type="text" name="complemento" id="complemento"
                                            placeholder="Complemento"
                                            title="Insira o complemento de sua residência"
                                            class="form-control"
                                            value=<?= "\"" . $usuarioLogado->getComplemento() . "\"" ?>>
                                    </div>
                                </div>
                            </div>
                            <?php if($usuarioLogado instanceof Aluno) { ?>
                            <div class="form-group">
                                <label for="escolaridade">Nível de escolaridade:</label>
                                <select name="escolaridade" id="escolaridade" class="form-control">
                                    <option value="fundamental incompleto"
                                        <?= ($usuarioLogado->getEscolaridade() === "fundamental incompleto") ?
                                            "selected" : '' ?>>
                                        Ensino Fundamental Incompleto
                                    </option>
                                    <option value="fundamental completo"
                                        <?= ($usuarioLogado->getEscolaridade() === "fundamental completo") ?
                                            "selected" : '' ?>>
                                        Ensino Fundamental Completo
                                    </option>
                                    <option value="médio incompleto"
                                        <?= ($usuarioLogado->getEscolaridade() === "médio incompleto") ?
                                            "selected" : '' ?>>
                                        Ensino Médio Incompleto
                                    </option>
                                    <option value="médio completo"
                                        <?= ($usuarioLogado->getEscolaridade() === "médio completo") ?
                                            "selected" : '' ?>>
                                        Ensino Médio Completo
                                    </option>
                                    <option value="superior incompleto"
                                        <?= ($usuarioLogado->getEscolaridade() === "superior incompleto") ?
                                            "selected" : '' ?>>
                                        Ensino Superior Incompleto
                                    </option>
                                    <option value="superior completo"
                                        <?= ($usuarioLogado->getEscolaridade() === "superior completo") ?
                                            "selected" : '' ?>>
                                        Ensino Superior Completo
                                    </option>
                                    <option value="mestrado"
                                        <?= ($usuarioLogado->getEscolaridade() === "mestrado") ?
                                            "selected" : '' ?>>
                                        Mestrado
                                    </option>
                                    <option value="doutorado"
                                        <?= ($usuarioLogado->getEscolaridade() === "doutorado") ?
                                            "selected" : '' ?>>
                                        Doutorado
                                    </option>
                                </select>
                            </div>
                            <div class="form-group" <?= is_null($usuarioLogado->getCurso()) ? 
                                                        "style=\"display: none\"" : "" ?>>
                                <label for="curso">Curso superior cursado:</label>
                                <input type="text" name="curso" id="curso"
                                       pattern="^.{0,200}$" placeholder="Curso superior cursado"
                                       title="O curso deve ter no máximo 200 caracteres"
                                       class="form-control"
                                       value=<?= "\"" . $usuarioLogado->getCurso() . "\""?>>
                            </div>
                            <?php } } ?>
                            <br>
                            <div class="form-group">
                                <label for="senha">Insira sua senha:</label>
                                <input type="password" name="senha" id="senha" required
                                       pattern="^.{6,72}$" placeholder="Senha"
                                       title="A senha deve ter de 6 a 72 caracteres"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Atualizar informações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- modal para mudanca de foto -->
        <div class="modal fade" id="modal-muda-foto" tabindex="-1" role="dialog" 
             aria-labelledby="modal-muda-foto" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- colocamos a tag form aqui para que possamos enviar o formulário
                        no rodapé do modal -->
                    <form method="POST" action="rotinas/mudar_foto.php" enctype="multipart/form-data">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                                X
                            </button>
                            <h4 class="modal-title">Mudança de foto</h4>
                        </div>
                        <div class="modal-body">
                            <h4 class="warning">A imagem deve estar em um dos seguintes formatos:</h4>
                            <h4 class="warning"> ( PNG, JPG, JPEG ou GIF )</h4>
                            <br>
                            <h4 class="warning"> Para uma melhor imagem, enviar na resolucao 150x200</h4>
                            <!-- o formulário em si fica dentro dessa div -->
                            <div class="form-group">
                                <label for="foto">Selecione o arquivo:</label>
                                <input type="file" name="foto" id="foto" style="width:100%">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">
                                Alterar foto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                <!-- popup "modal" do bootstrap para confirmação de remoção de reuniao -->
        <div class="modal fade" id="modal-exclui-foto" tabindex="-1" role="dialog"
             aria-labelledby="modal-exclui-foto" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="rotinas/mudar_foto.php">
                    <div class="modal-content">
                        <div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
								X
							</button>
                        <h4 class="modal-title">Remoção de foto</h4>
                        </div>
                        <div class="modal-body">
                            <h3>Tem certeza que deseja remover sua foto?</h3>
                        </div>
                        <input type="hidden" name="excluir" id="excluir" value="1">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Não</button>
                            <button type="submit" name="submit" id="submit" class="btn btn-danger" >Sim</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
            }
            include("modulos/rodape.php");
        ?>
    </body>
</html>