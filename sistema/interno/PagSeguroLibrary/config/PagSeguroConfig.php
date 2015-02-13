<?php

/*
 ************************************************************************
 PagSeguro Config File
 ************************************************************************
 */

$PagSeguroConfig = array();

$PagSeguroConfig['environment'] = "production"; // production, sandbox

$PagSeguroConfig['credentials'] = array();
$PagSeguroConfig['credentials']['email'] = "duvidahomeopatia@terra.com.br";
$PagSeguroConfig['credentials']['token']['production'] = "DE6677BCAA4944A78CAD60393920C44F";
<<<<<<< HEAD
$PagSeguroConfig['credentials']['token']['sandbox'] = "0061B39414444850AB060977BA356EE0";
=======
$PagSeguroConfig['credentials']['token']['sandbox'] = "";
>>>>>>> edição de cidade alterado

$PagSeguroConfig['application'] = array();
$PagSeguroConfig['application']['charset'] = "UTF-8"; // UTF-8, ISO-8859-1

$PagSeguroConfig['log'] = array();
$PagSeguroConfig['log']['active'] = true;
<<<<<<< HEAD
$PagSeguroConfig['log']['fileLocation'] = "";
=======
$PagSeguroConfig['log']['fileLocation'] = "";
>>>>>>> edição de cidade alterado
