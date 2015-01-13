<?php
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8');
session_start();

require("../entidades/Administrador.php");
require("../entidades/Aluno.php");
require("../entidades/Associado.php");

if(isset($_SESSION["usuario"])){

    $usuario = unserialize($_SESSION["usuario"]);
    
    if( isset($_POST["submit"]) ){
        if(isset($_POST["excluir"]) ){

            if( file_exists("../fotos/".$usuario->getId().".png" ) ){
                    unlink("../fotos/".$usuario->getId().".png");
                }

        }else{

            $fotoEnviada = $_FILES["foto"];
            $extensoes_permitidas = array("btm","png","jpg");
    
            $extensao = mb_convert_case(
                            pathinfo($fotoEnviada['name'], PATHINFO_EXTENSION),
                            MB_CASE_LOWER, "UTF-8"
                        );

    
            if(in_array($extensao, $extensoes_permitidas) ){
    
    
                if( file_exists("../fotos/".$usuario->getId().".png" ) ){
                    unlink("../fotos/".$usuario->getId().".png");
                }

                imagepng(imagecreatefromstring(file_get_contents($_FILES['foto']['tmp_name'])), 
                    "../fotos/".$usuario->getId().".png");
    
            }

        }

    }
}

header('Location: ../index.php?', true, "302");
die();

?>