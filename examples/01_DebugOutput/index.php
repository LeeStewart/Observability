<?php declare(strict_types=1);
/**
 * 01 - Debug Output - Web Page
 *
 * This page will show how to add tracing to a web page.  The output will go directly to
 * the web page.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.10.01
 **/



ini_set('display_errors', "1");
error_reporting(E_ALL);


require_once(__DIR__."/../../src/__autoload.php");

use Observability\Client\Trace;



// A single command to initialize things.
Observability\Client\Setup::startup();


// Call a test function that has some tracing output.
testFunction();

Trace::output("This is test output using the default format.");

Trace::output("word", "test [{$_SERVER['DOCUMENT_ROOT']}]", Trace::SEVERITY_ERROR);
Trace::outputLabel("word", "Severity of Warning", Trace::SEVERITY_WARNING);
Trace::output("word", "Severity of INFO", Trace::SEVERITY_INFO);

Trace::output("test with no severity[{$_SERVER['DOCUMENT_ROOT']}]");
Trace::output("test [{$_SERVER['DOCUMENT_ROOT']}]", Trace::SEVERITY_ERROR);
Trace::output("Severity of Warning", Trace::SEVERITY_WARNING);
Trace::output("Severity of INFO", Trace::SEVERITY_INFO);


exit;



function testFunction()
{
	Trace::output("This is test output using the default format.");

	$payload = array(
					   'action' => 'echo',
					   'data' => 'testing',
					);

	Trace::output("This text shown before the output", $payload, Trace::SEVERITY_WARNING);
}
