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
$PagSeguroConfig['credentials']['token']['sandbox'] = "";

$PagSeguroConfig['application'] = array();
$PagSeguroConfig['application']['charset'] = "UTF-8"; // UTF-8, ISO-8859-1

$PagSeguroConfig['log'] = array();
$PagSeguroConfig['log']['active'] = true;
$PagSeguroConfig['log']['fileLocation'] = "";
