<?php

require_once("../config.php");
require_once("TrasmissioneFattureHandler.php");
require_once("../SoapServerDebug.php");

error_log('==== TrasmissioneFatture');
// $srv = new \SoapServer('TrasmissioneFatture_v1.1.wsdl');
$srv = new SoapServerDebug('TrasmissioneFatture_v1.1.wsdl');
$srv->setClass("TrasmissioneFattureHandler");
$srv->handle();
error_log('==== '. print_r($srv->getAllDebugValues(), true));

