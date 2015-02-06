<?php
session_start();

require_once(dirname(__FILE__)."/../entidades/Aluno.php");

// essa página foi criada apenas para que a senha do usuário não fique
// disponível dentro de html, o que é um risco muito grande à segurança
?>
<html>
    <body>
        <form id="login" method="post" action="http://homeopatias.com/area-do-aluno/moodle/login/index.php">
            <input type="hidden" id="username" name="username"
                   value=<?= '"' . unserialize($_SESSION["usuario"])->getLogin() . '"' ?>/>
            <input type="hidden" id="password" name="password"
                   value=<?= '"' . unserialize($_SESSION["usuario"])->getSenha() . '"' ?>/>
        </form>
        <script>
            document.getElementById("login").submit();
        </script>
    </body>
</html>