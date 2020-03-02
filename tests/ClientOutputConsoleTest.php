<?php declare(strict_types=1);



require_once(__DIR__."/../src/__autoload.php");


use PHPUnit\Framework\TestCase;

use Observability\Client\Trace;



// This is a convenience for these tests, not something that's normally needed.
use Observability\Client\Core\OutputConsole as TracerOutputConsole;



class ClientOutputConsoleTest extends TestCase
{
	/** @var TracerOutputConsole $console */
	private $console = null;


	public function setUp(): void
	{
		$this->console = new TracerOutputConsole();

		return;
	}



	public function testCheckFormattingDefaultOutputString()
	{
		$params = array(
			"severity"=>Trace::SEVERITY_TRACING,
			"isMultiLine"=>false,
			"label"=>"",
			"output"=>"Test Output",
			"action"=>"trace-output",
			"type"=>"string",
			"option"=>"",
			"caller"=>array(
				"file"=>__FILE__,
				"line"=>__LINE__,
				"function"=>__FUNCTION__."()",
				"stack"=>array(),
			),
		);

		$output = TracerOutputConsole::FOREGROUND_SEVERITY_TRACING.$params['output'].TracerOutputConsole::FOREGROUND_RESET;
		$caller = TracerOutputConsole::FOREGROUND_CALLER."{$params['caller']['function']} in {$params['caller']['file']}:{$params['caller']['line']}".TracerOutputConsole::FOREGROUND_RESET;

		$this->expectOutputString($output.PHP_EOL.$caller.PHP_EOL);
		$this->console->output($params);

	}



	public function testCheckFormattingInfoOutputString()
	{
		$params = array(
			"severity"=>Trace::SEVERITY_INFO,
			"isMultiLine"=>false,
			"label"=>"",
			"output"=>"Test Output",
			"action"=>"trace-output",
			"type"=>"string",
			"option"=>"",
			"caller"=>array(
				"file"=>__FILE__,
				"line"=>__LINE__,
				"function"=>__FUNCTION__."()",
				"stack"=>array(),
			),
		);

		$output = TracerOutputConsole::FOREGROUND_SEVERITY_INFO.$params['output'].TracerOutputConsole::FOREGROUND_RESET;
		$caller = TracerOutputConsole::FOREGROUND_CALLER."{$params['caller']['function']} in {$params['caller']['file']}:{$params['caller']['line']}".TracerOutputConsole::FOREGROUND_RESET;

		$this->expectOutputString($output.PHP_EOL.$caller.PHP_EOL);
		$this->console->output($params);

	}



	public function testCheckFormattingWarningOutputString()
	{
		$params = array(
			"severity"=>Trace::SEVERITY_WARNING,
			"isMultiLine"=>false,
			"label"=>"",
			"output"=>"Test Output",
			"action"=>"trace-output",
			"type"=>"string",
			"option"=>"",
			"caller"=>array(
				"file"=>__FILE__,
				"line"=>__LINE__,
				"function"=>__FUNCTION__."()",
				"stack"=>array(),
			),
		);

		$output = TracerOutputConsole::FOREGROUND_SEVERITY_WARNING.$params['output'].TracerOutputConsole::FOREGROUND_RESET;
		$caller = TracerOutputConsole::FOREGROUND_CALLER."{$params['caller']['function']} in {$params['caller']['file']}:{$params['caller']['line']}".TracerOutputConsole::FOREGROUND_RESET;

		$this->expectOutputString($output.PHP_EOL.$caller.PHP_EOL);
		$this->console->output($params);

	}



	public function testCheckFormattingErrorOutputString()
	{
		$params = array(
			"severity"=>Trace::SEVERITY_ERROR,
			"isMultiLine"=>false,
			"label"=>"",
			"output"=>"Test Output",
			"action"=>"trace-output",
			"type"=>"string",
			"option"=>"",
			"caller"=>array(
				"file"=>__FILE__,
				"line"=>__LINE__,
				"function"=>__FUNCTION__."()",
				"stack"=>array(),
			),
		);

		$output = TracerOutputConsole::FOREGROUND_SEVERITY_ERROR.$params['output'].TracerOutputConsole::FOREGROUND_RESET;
		$caller = TracerOutputConsole::FOREGROUND_CALLER."{$params['caller']['function']} in {$params['caller']['file']}:{$params['caller']['line']}".TracerOutputConsole::FOREGROUND_RESET;

		$this->expectOutputString($output.PHP_EOL.$caller.PHP_EOL);
		$this->console->output($params);

	}



	public function testCheckFormattingOutputAndLabelString()
	{
		$params = array(
			"severity"=>Trace::SEVERITY_TRACING,
			"isMultiLine"=>false,
			"label"=>"Test Label",
			"action"=>"trace-output",
			"output"=>"Test Output",
			"type"=>"string",
			"option"=>"",
			"caller"=>array(
				"file"=>__FILE__,
				"line"=>__LINE__,
				"function"=>__FUNCTION__."()",
				"stack"=>array(),
			),
		);

		$output = TracerOutputConsole::FOREGROUND_SEVERITY_TRACING."{$params['label']}: {$params['output']}".TracerOutputConsole::FOREGROUND_RESET;
		$caller = TracerOutputConsole::FOREGROUND_CALLER."{$params['caller']['function']} in {$params['caller']['file']}:{$params['caller']['line']}".TracerOutputConsole::FOREGROUND_RESET;

		$this->expectOutputString($output.PHP_EOL.$caller.PHP_EOL);
		$this->console->output($params);

	}



	public function testCheckFormattingOutputArrayAndLabelString()
	{
		$params = array(
			"severity"=>Trace::SEVERITY_TRACING,
			"isMultiLine"=>true,
			"label"=>"Test Label",
			"action"=>"trace-output",
			"output"=>print_r($this->console, true),
			"type"=>"object",
			"option"=>"",
			"caller"=>array(
				"file"=>__FILE__,
				"line"=>__LINE__,
				"function"=>__FUNCTION__."()",
				"stack"=>array(),
			),
		);

		$output = TracerOutputConsole::FOREGROUND_SEVERITY_TRACING.$params['label'].TracerOutputConsole::FOREGROUND_RESET.PHP_EOL.$params['output'].TracerOutputConsole::FOREGROUND_RESET;
		$caller = TracerOutputConsole::FOREGROUND_CALLER."{$params['caller']['function']} in {$params['caller']['file']}:{$params['caller']['line']}".TracerOutputConsole::FOREGROUND_RESET;

		$this->expectOutputString($output.PHP_EOL.$caller.PHP_EOL);
		$this->console->output($params);

	}

}
