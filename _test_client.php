<?php declare(strict_types=1);


require_once(__DIR__."/src/__autoload.php");

//require_once(__DIR__."/examples/Stackdriver/src/OutputStackdriver.class.php");


use \Observability\Client\Setup;
use \Observability\Client\Trace;


ini_set('display_errors', "1");
error_reporting(E_ALL);


//$sd = new Observability\Client\Core\OutputStackdriver();
//Setup::addOutputInterface('stack-driver', $sd);


$net = new Observability\Client\Core\OutputSocket();
Setup::addOutputInterface('net', $net);

//Setup::skipDisplay();
Setup::startup();


testFunction();


Trace::outputMonkey("word", "test with no severity[{$_SERVER['DOCUMENT_ROOT']}]");
Trace::output("word", "test [{$_SERVER['DOCUMENT_ROOT']}]", Trace::SEVERITY_ERROR);
Trace::output("word", "Severity of Warning", Trace::SEVERITY_WARNING);
Trace::output("word", "Severity of INFO", Trace::SEVERITY_INFO);

Trace::output("test with no severity[{$_SERVER['DOCUMENT_ROOT']}]");
Trace::output("test [{$_SERVER['DOCUMENT_ROOT']}]", Trace::SEVERITY_ERROR);
Trace::output("Severity of Warning", Trace::SEVERITY_WARNING);
Trace::output("Severity of INFO", Trace::SEVERITY_INFO);
Trace::outputPre("PRE!", $_SERVER, Trace::SEVERITY_WARNING);
Trace::diaf($_SERVER, Trace::SEVERITY_WARNING);


exit;



function testFunction()
{

	$payload = array(
					   'action' => 'echo',
					   'data' => 'dos',
					);
	Trace::output("Test Label", $payload, Trace::SEVERITY_WARNING);

	Trace::output("Test Output");

}
