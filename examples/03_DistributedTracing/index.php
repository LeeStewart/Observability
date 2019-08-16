<?php declare(strict_types=1);
/**
 * 03 - Distributed Tracing Example - Web Page
 *
 * This page will show how to add tracing to a web page and then continue the tracing
 * through other asynchronous web modules.
 *
 * The output will go to storage_service.php - make sure to run that from the command-line
 * before viewing this page.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.10.01
 **/



ini_set('display_errors', "1");
error_reporting(E_ALL);


require_once(__DIR__."/../../vendor/autoload.php");
require_once(__DIR__."/../../src/__autoload.php");

use Observability\Client\Setup;
use Observability\Client\Trace;



$applicationName = "observability";
$exampleName = "distributed_tracing";
$moduleName = "web_page";


// First we'll create a socket that will be used to transmit data.
$socket = new Observability\Client\Core\OutputSocket();		// Defaults to 'tcp://localhost:55012'

// Verify that we connected to the Storage Service.
if (!$socket->checkConnection())
	die("The Storage Service (storage_service.php) isn't running or can't be found.");

Setup::addOutputInterface('socket', $socket);


// We want to set the "Application Path" for this web page.
Setup::setAppPath($applicationName, $exampleName, $moduleName);


// Add the user info, this should be unique like a database index: "ID 12345".
Setup::setUserIdentifierString('LeeStewart@RandomOddness.com');


// Now initialize things, so our trace data will be sent to the Storage Service.
Setup::startup();


Trace::output("This tracing statement is from the main web page that is calling the modules.");


callModule("module_one");
callModule("module_two");



exit;




function callModule($name)
{
	// We're going to send this info to the module.
	$context = Setup::getCurrentContextString();

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "http://{$_SERVER['HTTP_HOST']}/Debug/examples/03_DistributedTracing/{$name}.php");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Trace-Context: $context"
	));

	$moduleOutput = curl_exec($ch);
	//Trace::output($moduleOutput);
	//Trace::output("Curl Error", curl_error($ch));

	curl_close ($ch);

}
