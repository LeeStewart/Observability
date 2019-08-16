<?php declare(strict_types=1);
/**
 * 03 - Distributed Tracing Example - Module 2
 *
 * This module shows how to tracing of a web page (index.php) can be continued to other
 * other asynchronous web services.
 *
 * The output will go to storage_service.php - make sure to run that from the command-line
 * before viewing index.php
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
$moduleName = "module_two";



// First we'll create a socket that will be used to transmit data.
$socket = new Observability\Client\Core\OutputSocket();		// Defaults to 'tcp://localhost:55012'

// Verify that we connected to the Storage Service.
if (!$socket->checkConnection())
	die("The Storage Service (storage_service.php) isn't running or can't be found.");

Setup::addOutputInterface('socket', $socket);


// We want to set the "Application Path" for this web module.
Setup::setAppPath($applicationName, $exampleName, $moduleName);


// Add the user info, this should be unique like a database index: "ID 12345".
Setup::setUserIdentifierString('LeeStewart@RandomOddness.com');


// We'll get the headers for this transaction and store the parent trace context.
$headers = getallheaders();
Setup::setParentContextString($headers['Trace-Context']);


// Now initialize things, so our trace data will be sent to the Storage Service.
Setup::startup();


// We'll hide the tracing info from the output.
Setup::skipDisplay();

Trace::output("This tracing statement is from web service '{$moduleName}'.", Trace::SEVERITY_INFO);




// Output a message for the index.php script to display.
echo "Hello from {$applicationName}.{$moduleName}";



exit;
