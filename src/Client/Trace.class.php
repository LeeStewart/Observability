<?php declare(strict_types=1);



namespace Observability\Client;



/**
 * Class Trace
 *
 * @method static output($str, ... $str)
 * @method static diaf($str, ... $str)
 */
class Trace
{
	const SEVERITY_TRACING = '--tracing';
	const SEVERITY_INFO = '--info';
	const SEVERITY_WARNING = '--warning';
	const SEVERITY_ERROR = '--error';

	private static $initialized = false;
	private static $skipDisplay = false;



	private function __construct() {}



	public static function startup()
	{
		if (self::$initialized)
			return;

		self::$initialized = true;
	}



	public static function shutdown()
	{


	}


	public static function skipDisplay($skip=true)
	{
		self::$skipDisplay = $skip;
	}



	public static function __callStatic(string $name, array $arguments)
	{
		if ($name == 'diaf')
		{
			$arguments[] = self::SEVERITY_ERROR;
			$name = 'outputDIAF';
		}

		if (substr($name, 0, strlen('output')) == 'output')
		{
			// The output Parameters will be built here.
			$params = self::formatOutputArguments($arguments);

			$params['caller'] = self::formatOutputCaller(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

			$params['option'] = strtolower(substr($name, strlen('output')));

			Core\Core::outputTrace($params);

			if ($params['option'] == 'diaf')
				exit;

		}
		else
		{
			// @todo Handle unknown method...
		}

	}



	private static function formatOutputArguments(array $arguments)
	{
		$params = array();

		$params['severity'] = Trace::SEVERITY_TRACING;
		$params['multiLine'] = false;
		$params['label'] = '';
		$params['exception'] = '';

		$output = '';

		foreach ($arguments as $arg)
		{
			if (($arg === Trace::SEVERITY_TRACING) || ($arg === Trace::SEVERITY_INFO) || ($arg === Trace::SEVERITY_WARNING) || ($arg === Trace::SEVERITY_ERROR))
				$params['severity'] = $arg;
			else
			{
				// If we get one item, it's 'output' if we get a second string, then there was an optional 'label'.
				if (!$output)
				{
					$output = $arg;
				}
				else
				{
					$params['label'] = print_r($output, true);
					$output = $arg;
				}
			}
		}


		$params['type'] = gettype($output);


		// Format the output depending on what it is.
		if (is_array($output))
		{
			$output = print_r($output, true);
			$params['multiLine'] = true;

		}
		else if (is_object($output))
		{
			/** @var /Exception $output */
			if (get_class($output) == "Exception" || is_subclass_of($output, "Exception"))
			{
				$params['exception'] = get_class($output);

				$output = $output->getMessage();
				$params['type'] = 'exception';
			}
			else
			{
				$output = print_r($output, true);
				$params['multiLine'] = true;
			}

		}
		else if (is_bool($output))
		{
			$output = "boolean = [".($output? "true": "false")."]";

		}
		else if (is_resource($output))
		{
			$output = "resource = [".get_resource_type($output)."]";
		}


		if (!$output)
		{
			$output = "[empty string]";
		}

		$params['output'] = $output;

		return $params;
	}



	private static function formatOutputCaller(array $stackTrace)
	{
		$params = array();

		$params['file'] = $stackTrace[0]['file'];
		$params['line'] = $stackTrace[0]['line'];

		$params['function'] = "";
		if (array_key_exists(1, $stackTrace))
		{
			$params['function'] = "{$stackTrace[1]['function']}()";
			if (array_key_exists('class', $stackTrace[1]))
				$params['function'] = "{$stackTrace[1]['class']}{$stackTrace[1]['type']}{$stackTrace[1]['function']}()";

		}

		$params['stack'] = $stackTrace;

		return $params;
	}

}
