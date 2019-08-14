<?php declare(strict_types=1);

ini_set('display_errors', "1");
error_reporting(E_ALL);


require_once(__DIR__."/../../vendor/autoload.php");
require_once(__DIR__."/../../src/__autoload.php");

use Observability\Client\Setup;
use Observability\Client\Trace;


// First we'll create a socket that will be used to transmit data.
$socket = new Observability\Client\Core\OutputSocket();		// Defaults to 'tcp://localhost:55012'

// Verify that we connected to the Storage Service.
if (!$socket->checkConnection())
	die("The Storage Service (server.php) isn't running or can't be found.");

Setup::addOutputInterface('socket', $socket);


// Now initialize things, so our trace data will be sent to the Storage Service.
Setup::startup();


Trace::output("word", "test [{$_SERVER['DOCUMENT_ROOT']}]", Trace::SEVERITY_ERROR);
Trace::output("word", "Severity of Warning", Trace::SEVERITY_WARNING);
Trace::output("word", "Severity of INFO", Trace::SEVERITY_INFO);


// The rest of the tracing will not be displayed, but will still be sent to the Storage Service.
Setup::skipDisplay();

Trace::output("test with no severity [{$_SERVER['DOCUMENT_ROOT']}]");
Trace::output("test [{$_SERVER['DOCUMENT_ROOT']}]", Trace::SEVERITY_ERROR);
Trace::output("Severity of Warning", Trace::SEVERITY_WARNING);
Trace::output("Severity of INFO", Trace::SEVERITY_INFO);


exit;
