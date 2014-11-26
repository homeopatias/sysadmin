
        <!-- barra de navegação -->

        <?php

            // incluindo todas as entidades necessárias para descobrir os menus
            // relevantes
            require_once("entidades/Administrador.php");
            require_once("entidades/Aluno.php");
            require_once("entidades/Associado.php");
            
        ?>

        <nav class="navbar navbar-default navbar-fixed-top bottom-border">
            <div class="navbar-header">
                <a href="index.php" class="navbar-brand">
                    Homeopatias.com - Sistema
                </a>

            <?php if(isset($_SESSION["usuario"])){ ?>

                <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

            <?php } ?>

            </div>

            <?php if(isset($_SESSION["usuario"])){ ?>

            <div id="navbarCollapse" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="index.php">
                            <i class="fa fa-fw fa-home fa-lg"></i>
                            <p style="display:inline">Página inicial</p>
                        </a>
                    </li>
                    <?php 
                        if(isset($_SESSION["usuario"]) &&
                           unserialize($_SESSION["usuario"]) instanceof Administrador &&
                           unserialize($_SESSION["usuario"])->getNivelAdmin() === "professor"){

                            /////////////// OPÇÕES PARA PROFESSORES ///////////////

                    ?>
                    <li>
                        <a href="gerenciar_definicoes_trabalho.php">
                            <i class="fa fa-fw fa-file-text"></i>
                            <p style="display:inline">Definições de trabalho</p>
                        </a>
                    </li>
                    <li>
                        <a href="aulas_professor.php">
                            <i class="fa fa-fw fa-graduation-cap"></i>
                            <p style="display:inline">Aulas a ministrar</p>
                        </a>
                    </li>
                    <?php
                        } // Fim das opções para professores

                        if(isset($_SESSION["usuario"]) &&
                           unserialize($_SESSION["usuario"]) instanceof Administrador &&
                           unserialize($_SESSION["usuario"])->getNivelAdmin() === "coordenador"){

                            /////////////// OPÇÕES PARA COORDENADORES ///////////////

                    ?>
                    <li>
                        <a href="visualizar_turmas.php">
                            <i class="fa fa-fw fa-list-alt"></i>
                            <p style="display:inline">Visualizar turmas</p>
                        </a>
                    </li>
                    <li>
                        <a href="selecao_turma_frequencias.php">
                            <i class="fa fa-fw fa-list-ul"></i>
                            <p style="display:inline">Frequência de alunos</p>
                        </a>
                    </li>
                    <?php
                        } // Fim das opções para coordenadores

                        else if(isset($_SESSION["usuario"]) &&
                           unserialize($_SESSION["usuario"]) instanceof Administrador &&
                           unserialize($_SESSION["usuario"])->getNivelAdmin() === "administrador"){
                            $permissoes = unserialize($_SESSION["usuario"])->getPermissoes();
                            /////////////// OPÇÕES PARA ADMINISTRADORES ///////////////
                    ?>

                    <?php 
                        if(1 & $permissoes){
                    ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle btn-toggle-dropdown"
                           data-toggle="dropdown">
                            <i class="fa fa-fw fa-users"></i>
                            <p style="display:inline">Pessoas</p>
                        </a>
                        <ul class="dropdown-menu drop">
                            <!-- dropdown de gerenciamento de pessoas -->
                            <?php if(16 & $permissoes){ ?>
                            <li>
                                <a href="gerenciar_administradores.php">
                                    <i class="fa fa-fw fa-key"></i>
                                    <p style="display:inline">Administradores</p>
                                </a>
                            </li>
                            <?php } ?>
                            <li>
                                <a href="gerenciar_alunos.php">
                                    <i class="fa fa-fw fa-graduation-cap"></i>
                                    <p style="display:inline">Alunos</p>
                                </a>
                            </li>
                            <li>
                                <a href="gerenciar_associados.php">
                                    <i class="fa fa-fw fa-child"></i>
                                    <p style="display:inline">Associados</p>
                                </a>
                            </li>
                            <li style="height: 2em">
                                <a href="gerenciar_coordenadores.php">
                                    <i class="fa fa-fw fa-check"></i>
                                    <p style="display:inline">Coordenadores</p>
                                </a>
                            </li>
                            <li style="height: 2em">
                                <a href="gerenciar_professores.php">
                                    <i class="fa fa-fw fa-pencil-square-o"></i>
                                    <p style="display:inline">Professores</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php  }
                        if(2 & $permissoes){
                        ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle btn-toggle-dropdown"
                           data-toggle="dropdown">
                            <i class="fa fa-fw fa-leaf"></i>
                            <p style="display:inline">Curso</p>
                        </a>
                        <ul class="dropdown-menu drop">
                            <!-- dropdown de gerenciamento de assuntos do curso -->
                            <li>
                                <a href="gerenciar_aulas.php">
                                    <i class="fa fa-fw fa-pencil"></i>
                                    <p style="display:inline">Aulas</p>
                                </a>
                            </li>
                            <li>
                                <a href="gerenciar_cidades.php">
                                    <i class="fa fa-fw fa-building-o"></i>
                                    <p style="display:inline">Cidades</p>
                                </a>
                            </li>
                            <li>
                                <a href="avaliar_ausencias.php">
                                    <i class="fa fa-fw fa-file-text-o"></i>
                                    <p style="display:inline">Justificativa de ausências</p>
                                </a>
                            </li>
                            <li>
                                <a href="gerenciar_definicoes_trabalho.php">
                                    <i class="fa fa-fw fa-file-text"></i>
                                    <p style="display:inline">Definições de trabalho</p>
                                </a>
                            </li>
                            <li>
                                <a href="gerenciar_notas_professores.php">
                                    <i class="fa fa-fw fa-thumbs-o-up"></i>
                                    <p style="display:inline">Notas de professores</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php  }
                        if(4 & $permissoes){
                        ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle btn-toggle-dropdown"
                           data-toggle="dropdown">
                            <i class="fa fa-fw fa-info"></i>
                            <p style="display:inline">Informações</p>
                        </a>
                        <ul class="dropdown-menu drop">
                            <!-- dropdown de gerenciamento de informações -->
                            <li>
                                <a href="gerenciar_artigos.php">
                                    <i class="fa fa-fw fa-quote-left"></i>
                                    <p style="display:inline">Artigos</p>
                                </a>
                            </li>
                            <li>
                                <a href="gerenciar_eventos.php">
                                    <i class="fa fa-fw fa-calendar"></i>
                                    <p style="display:inline">Eventos</p>
                                </a>
                            </li>
                            <li>
                                <a href="gerenciar_noticias.php">
                                    <i class="fa fa-fw fa-exclamation-circle"></i>
                                    <p style="display:inline">Notícias</p>
                                </a>
                            </li>
                            <li>
                                <a href="gerenciar_reunioes.php">
                                    <i class="fa fa-fw fa-suitcase"></i>
                                    <p style="display:inline">Reuniões</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php  }
                        if(8 & $permissoes){
                        ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle btn-toggle-dropdown"
                           data-toggle="dropdown">
                            <i class="fa fa-fw fa-cog"></i>
                            <p style="display:inline">Outros</p>
                        </a>
                        <ul class="dropdown-menu drop">
                            <!-- dropdown de gerenciamento de miscelânea -->
                            <li>
                                <a href="gerenciar_instituicoes.php">
                                    <i class="fa fa-fw fa-institution"></i>
                                    <p style="display:inline">Instituições</p>
                                </a>
                            </li>
                            <li>
                                <a href="gerenciar_livros.php">
                                    <i class="fa fa-fw fa-book"></i>
                                    <p style="display:inline">Livros</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php  } ?>

                    <?php
                        } // Fim das opções para administradores

                        else if(isset($_SESSION["usuario"]) &&
                           unserialize($_SESSION["usuario"]) instanceof Associado){

                            /////////////// OPÇÕES PARA ASSOCIADOS ///////////////

                            // exibimos as reuniões apenas para associados cujos documentos
                            // foram recebidos e aprovados, e cujos pagamentos estão em dia

                            require_once('rotinas/associado/checa_situacao_pagamentos.php');

                            $pagamentosEmDia = checa_situacao_pagamentos();

                            if(unserialize($_SESSION["usuario"])->getEnviouDocumentos() &&
                               $pagamentosEmDia) {
                    ?>

                    <li>
                        <a href="visualizar_reunioes.php">
                            <i class="fa fa-suitcase"></i>
                            <p style="display:inline">Reuniões</p>
                        </a>
                    </li>

                    <?php }

                        } // Fim das opções para associados

                        else if(isset($_SESSION["usuario"]) &&
                           unserialize($_SESSION["usuario"]) instanceof Aluno){

                            /////////////// OPÇÕES PARA ALUNOS ///////////////

                            if(unserialize($_SESSION["usuario"])->getStatus() === "inscrito") {
                                // exibe os trabalhos e aulas apenas para alunos inscritos
                    ?>

                    <li>
                        <a href="trabalhos_aluno.php">
                            <i class="fa fa-fw fa-file-text"></i>
                            <p style="display:inline">Trabalhos</p>
                        </a>
                    </li>
                    <li>
                        <a href="aulas_aluno.php">
                            <i class="fa fa-fw fa-graduation-cap"></i>
                            <p style="display:inline">Aulas desse ano</p>
                        </a>
                    </li>

                    <li>
                        <a href="frequencia_aluno.php">
                            <i class="fa fa-check-square-o"></i>
                            <p style="display:inline">Frequência</p>
                        </a>
                    </li>

                    <?php 
                            }
                        } // Fim das opções para alunos inscritos
                    ?>

                    <li>
                        <a href="rotinas/logout.php">
                            <i class="fa fa-power-off"></i>
                            <p style="display:inline">Logout</p>
                        </a>
                    </li>
                </ul>
            </div>
            <script>
                // script para que todos os dropdowns da navegação deslizem
                // para baixo, e não apareçam subitamente
                $(document).ready(function(){
                    $(".nav .btn-toggle-dropdown").click(function(){
                        if($(this).parent().hasClass("open")){
                            $(this).siblings(".drop").slideUp();
                        }else{
                            $(this).siblings(".drop").slideDown();
                            // aqui fazemos com que quaisquer outros dropdowns
                            // abertos na navegação subam
                            $(this).parent().siblings().children("ul").slideUp();
                        }
                    });
                    $(document).on('click.dropdown.data-api', function(){
                        $(".drop").slideUp();
                    });
                });
            </script>

            <?php } ?>

        </nav>
