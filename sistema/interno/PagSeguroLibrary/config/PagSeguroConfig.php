<?php

/*
 ************************************************************************
 PagSeguro Config File
 ************************************************************************
 */

$PagSeguroConfig = array();

$PagSeguroConfig['environment'] = "sandbox"; // production, sandbox

$PagSeguroConfig['credentials'] = array();
$PagSeguroConfig['credentials']['email'] = "duvidahomeopatia@terra.com.br";
$PagSeguroConfig['credentials']['token']['production'] = "your_production_pagseguro_token";
$PagSeguroConfig['credentials']['token']['sandbox'] = "0061B39414444850AB060977BA356EE0";

$PagSeguroConfig['application'] = array();
$PagSeguroConfig['application']['charset'] = "UTF-8"; // UTF-8, ISO-8859-1

$PagSeguroConfig['log'] = array();
$PagSeguroConfig['log']['active'] = true;
$PagSeguroConfig['log']['fileLocation'] = "";