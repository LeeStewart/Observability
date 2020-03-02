<?php declare(strict_types=1);



require_once(__DIR__."/../src/__autoload.php");
require_once("ClientOutputMock.class.php");


use PHPUnit\Framework\TestCase;

use Observability\Client\Setup;
use Observability\Client\Trace;



class ClientOutputTest extends TestCase
{
	/** @var ClientOutputMock $output */
	private $output = null;


	public function setUp(): void
	{
		$this->output = new ClientOutputMock();
		Setup::skipDisplay();
		Setup::addOutputInterface("test", $this->output);
		Setup::startup();

		return;
	}



	public function testAreSeveritySettingsHandled()
	{
		Trace::output("Test Output");
		$this->assertEquals(
			Trace::SEVERITY_TRACING,
			$this->output->lastOutput['severity']
		);


		Trace::output(Trace::SEVERITY_ERROR);
		$this->assertEquals(
			Trace::SEVERITY_ERROR,
			$this->output->lastOutput['severity']
		);


		Trace::output(Trace::SEVERITY_ERROR, "Test Label", "Test Output");
		$this->assertEquals(
			Trace::SEVERITY_ERROR,
			$this->output->lastOutput['severity']
		);
		$this->assertEquals(
			"Test Label",
			$this->output->lastOutput['label']
		);

	}



	public function testAreLabelsHandled()
	{
		Trace::output("Test Label");
		$this->assertNotEquals(
			"Test Label",
			$this->output->lastOutput['label']
		);


		Trace::output("Test Label", "Test Output");
		$this->assertEquals(
			"Test Label",
			$this->output->lastOutput['label']
		);

	}



	public function testIsOutputStringHandled()
	{
		Trace::output("Test Output");
		$this->assertEquals(
			"Test Output",
			$this->output->lastOutput['output']
		);


		Trace::output("Test Label", "Test Output");
		$this->assertEquals(
			"Test Output",
			$this->output->lastOutput['output']
		);
		$this->assertEquals(
			"Test Label",
			$this->output->lastOutput['label']
		);
		$this->assertFalse(
			$this->output->lastOutput['isMultiLine']
		);

	}



	public function testIsOutputArrayHandled()
	{
		Trace::output(array("Test Array"));
		$this->assertEquals(
			print_r(array("Test Array"), true),
			$this->output->lastOutput['output']
		);


		Trace::output("Test Label", array("Test Array"));
		$this->assertEquals(
			print_r(array("Test Array"), true),
			$this->output->lastOutput['output']
		);
		$this->assertTrue(
			$this->output->lastOutput['isMultiLine']
		);

	}



	public function testIsOutputObjectHandled()
	{
		$object = new ArrayObject();
		$object->append("Test Object");


		Trace::output($object);
		$this->assertEquals(
			print_r($object, true),
			$this->output->lastOutput['output']
		);


		Trace::output("Test Label", $object);
		$this->assertEquals(
			print_r($object, true),
			$this->output->lastOutput['output']
		);
		$this->assertTrue(
			$this->output->lastOutput['isMultiLine']
		);

	}



	public function testIsOutputExceptionHandled()
	{
		try
		{
			throw new Exception("Test Exception");

		}
		catch (Exception $e)
		{
			Trace::output($e);
			$this->assertEquals(
				'Test Exception',
				$this->output->lastOutput['output']
			);
			$this->assertEquals(
				'exception',
				$this->output->lastOutput['type']
			);
			$this->assertEquals(
				'Exception',
				$this->output->lastOutput['exception']
			);
			$this->assertFalse(
				$this->output->lastOutput['isMultiLine']
			);


			Trace::output("Test Label", $e);
			$this->assertEquals(
				'Test Exception',
				$this->output->lastOutput['output']
			);
			$this->assertEquals(
				'Test Label',
				$this->output->lastOutput['label']
			);
			$this->assertEquals(
				'exception',
				$this->output->lastOutput['type']
			);

		}

	}



	public function testIsOutputBooleanHandled()
	{
		Trace::output(true);
		$this->assertEquals(
			"boolean = [true]",
			$this->output->lastOutput['output']
		);


		Trace::output(false);
		$this->assertEquals(
			"boolean = [false]",
			$this->output->lastOutput['output']
		);


		Trace::output("Test Boolean", true);
		$this->assertEquals(
			"boolean = [true]",
			$this->output->lastOutput['output']
		);


		Trace::output("Test Boolean", false);
		$this->assertEquals(
			"boolean = [false]",
			$this->output->lastOutput['output']
		);
		$this->assertFalse(
			$this->output->lastOutput['isMultiLine']
		);

	}



	public function testWhenNothingIsPassedIn()
	{
		Trace::output();
		$this->assertEquals(
			"",
			$this->output->lastOutput['output']
		);
		$this->assertFalse(
			$this->output->lastOutput['isMultiLine']
		);


		Trace::output(Trace::SEVERITY_ERROR);
		$this->assertEquals(
			"",
			$this->output->lastOutput['output']
		);
		$this->assertEquals(
			Trace::SEVERITY_ERROR,
			$this->output->lastOutput['severity']
		);


		Trace::output("");
		$this->assertEquals(
			"",
			$this->output->lastOutput['output']
		);

	}



	public function testAreCallerFileAndLineHandled()
	{
		Trace::output("Test Output");		$file = __FILE__;
		$this->assertEquals(
			$file,
			$this->output->lastOutput['caller']['file']
		);


		Trace::output("Test Output");		$line = __LINE__;
		$this->assertEquals(
			$line,
			$this->output->lastOutput['caller']['line']
		);
		$this->assertEquals(
			self::class."->".__FUNCTION__."()",
			$this->output->lastOutput['caller']['function']
		);


		Trace::output("Test Output");
		$this->assertIsArray(
			$this->output->lastOutput['caller']['stack']
		);

	}



	public function testAreOutputOptionsHandled()
	{
		Trace::output("Test Output");

		$this->assertEquals(
			"",
			$this->output->lastOutput['option']
		);


		Trace::outputTracer("Test Output");
		$this->assertEquals(
			"tracer",
			$this->output->lastOutput['option']
		);


		Trace::outputSQL("Test Output");
		$this->assertEquals(
			"sql",
			$this->output->lastOutput['option']
		);

	}


}
