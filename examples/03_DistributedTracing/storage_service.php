<?php declare(strict_types=1);
/**
 * 03 - Distributed Tracing Example - Storage Service
 *
 * This file contains the server that will accept the trace output from the other modules.
 * Run this file before viewing the index.php page.
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


use Observability\StorageService;



// First, we'll create the main Storage Service object.
$storageService = new StorageService\StorageService();


// Second we'll configure the service for incoming data.
$socket = new StorageService\StorageServiceSocket();		// Defaults to 'tcp://localhost:55012'

// Verify that the Storage Service Socket is listening.
if (!$socket->checkConnection())
	die("The Storage Service could not be started.  Check if another instance is already running.\n");

$storageService->setStorageServiceSocket($socket);


// Next we need to define a Transport Handler for the output.
// This handler will simply output data to the console.
$handler = new StorageService\TransportHandlers\DebugOutputHandler();
if (!$handler->connect())
	die("Unable to connect to the debug output.\n");

$storageService->addTransportHandler($handler);



// Last step - start the Storage Service.
$storageService->startup();



exit;
