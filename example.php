<?php

require "vendor/autoload.php";

$webRequest = new \ByJG\Util\WebRequest('http://www.byjg.com.br/ws/cep');

echo $webRequest->get([
	'httpmethod' => 'obterVersao'
]) . "\n";

echo $webRequest->post([
	'httpmethod' => 'obterLogradouro',
	'cep' => '30130000'
]) . "\n";

echo $webRequest->soapCall('obterLogradouro', ['cep' => '30130000']) . "\n";
