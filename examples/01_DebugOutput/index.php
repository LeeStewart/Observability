<?php declare(strict_types=1);
/**
 * Debug Output
 *
 * This example shows the most basic method for using the Trace class.
 *
 */


ini_set('display_errors', "1");
error_reporting(E_ALL);


require_once(__DIR__."/../../vendor/autoload.php");

use \Observability\Client\Trace;


// A single command to initialize things.
\Observability\Client\Setup::startup();


// Call a test function that has some tracing output.
testFunction();


Trace::output("word", "test [{$_SERVER['DOCUMENT_ROOT']}]", Trace::SEVERITY_ERROR);
Trace::output("word", "Severity of Warning", Trace::SEVERITY_WARNING);
Trace::output("word", "Severity of INFO", Trace::SEVERITY_INFO);

Trace::output("test with no severity[{$_SERVER['DOCUMENT_ROOT']}]");
Trace::output("test [{$_SERVER['DOCUMENT_ROOT']}]", Trace::SEVERITY_ERROR);
Trace::output("Severity of Warning", Trace::SEVERITY_WARNING);
Trace::output("Severity of INFO", Trace::SEVERITY_INFO);


exit;



function testFunction()
{
	$payload = array(
					   'action' => 'echo',
					   'data' => 'testing',
					);
	Trace::output("This text shown before the output", $payload, Trace::SEVERITY_WARNING);

	Trace::output("This is test output");

}
