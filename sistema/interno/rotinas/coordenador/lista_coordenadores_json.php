<?php

// Essa página apenas lista os coordenadores para determinado ano,
// assim como a lista_coordenadores, porém, diferente da primeira, retorna o resultado
// em JSON.
// Isso serve para que seja possível receber a lista de coordenadores via AJAX

require_once('lista_coordenadores.php');
require_once('../../entidades/Administrador.php');

$ano = htmlspecialchars($_POST["ano"]);

$coord = listaCoordenadores($ano);
$resultado = array();

// O JSON de cada coordenador é feito manualmente,
// pois a função json_encode está convertendo com falhas
foreach($coord as $atual) {
    $json_objeto  = '';
    $json_objeto .= '{';
    $json_objeto .= '"nome": "' . $atual->getNome() . '",';
    $json_objeto .= '"id": "' . $atual->getIdAdmin() . '"';
    $json_objeto .= '}';
    $resultado[] = $json_objeto;
}

echo json_encode($resultado);