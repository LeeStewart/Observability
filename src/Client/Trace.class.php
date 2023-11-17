<?php declare(strict_types=1);
/**
 * Client - Trace Class
 *
 * The main class for all Trace-related output.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



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

	const DUMP_STACK_TRACE = '--dump_stack_trace';
	const HIDE_HEADER = '--hide_header';
	const HIDE_FOOTER = '--hide_footer';
	const HIDE_HEADER_FOOTER = '--hide_header_footer';

	private static $skipCommonErrors = false;


	private function __construct() {}



	public static function __callStatic(string $name, array $arguments)
	{
		$caller = array();
		$tags   = array();

		if ($name == '_error')
		{
			$name = 'outputPHP';

			$caller = self::formatErrorCaller($arguments, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
			$arguments = self::mapErrorArguments($arguments);
			if (!$arguments) {
				return true;
			}

		}
		else if ($name == '_exception')
		{
			// @todo This will report the function name improperly.
			$name = 'outputException';
			$arguments[] = self::SEVERITY_ERROR;
			$caller = self::formatOutputCaller($arguments[0]->getTrace());

		}
		else if ($name == 'diaf')
		{
			$name = 'outputDIAF';
			$arguments[] = self::SEVERITY_ERROR;

		} else if ( $name == '_log' ) {
			$name      = 'outputLog';
			$caller    = $arguments[0]['caller'];
			$tags      = $arguments[0]['tags'];
			$arguments = $arguments[0]['args'];
		}

		if (substr($name, 0, strlen('output')) == 'output')
		{
			// The output Parameters will be built here.
			$params = self::formatOutputArguments($arguments);

			if ( $caller ) {
				$params['caller'] = $caller;
			} else {
				$params['caller'] = self::formatOutputCaller(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
			}

			if ( $params['type'] == 'stack_trace' ) {
				$params['output'] = print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true);
			}

			$params['option'] = strtolower(substr($name, strlen('output')));

			Core\Core::outputTrace( $params, $tags );

			if ( $params['option'] == 'diaf' ) {
				exit;
			}

			if ( $params['option'] == 'exception' ) {
				exit;
			}

			if ( $params['option'] == 'php' ) {
				return true;
			}

		}
		else
		{
			// @todo Handle unknown method...
		}

		return true;
	}



	public static function setErrorHandler()
	{
		set_error_handler(array('\Observability\Client\Trace', '_error'), E_ALL);
		//register_shutdown_function(array('\Observability\Client\Trace', '_shutdown'));
		set_exception_handler(array('\Observability\Client\Trace', '_exception'));
	}


	public static function skipCommonErrors( $skip = true ) {
		self::$skipCommonErrors = (bool) $skip;
	}

	private static function mapErrorArguments($input)
	{
		$arguments = array();

		// We'll store the error string.
		$errorString = $input[1];

		// Ugh, we'll skip these annoyances.
		if ( self::$skipCommonErrors ) {
			if ( substr( $errorString, 0, strlen( 'Undefined index:' ) ) == 'Undefined index:' ) {
				return $arguments;
			}
			if ( substr( $errorString, 0, strlen( 'Undefined offset:' ) ) == 'Undefined offset:' ) {
				return $arguments;
			}
			if ( substr( $errorString, 0, strlen( 'Undefined variable:' ) ) == 'Undefined variable:' ) {
				return $arguments;
			}
			if ( strstr( $errorString, "expected to be a reference" ) ) {
				return $arguments;
			}
		}


		$arguments[] = $errorString;

		$severity = self::SEVERITY_TRACING;
		switch($input[0])
		{
			case E_NOTICE:
			case E_USER_NOTICE:
			{
				$severity = self::SEVERITY_INFO;
				break;
			}

			case E_USER_WARNING:
			case E_CORE_WARNING:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
			case E_COMPILE_WARNING:
			case E_PARSE:
			{
				$severity = self::SEVERITY_WARNING;
				break;
			}

			case E_ERROR:
			case E_RECOVERABLE_ERROR:
			case E_CORE_ERROR:
			case E_USER_ERROR:
			case E_COMPILE_ERROR:
			{
				$severity = self::SEVERITY_ERROR;
				break;
			}

		}

		$arguments[] = $severity;
		return $arguments;
	}



	private static function formatOutputArguments(array $arguments)
	{
		$params = array();

		$params['severity'] = Trace::SEVERITY_TRACING;
		$params['isMultiLine'] = false;
		$params['label'] = '';
		$params['exception'] = '';

		$output = '';

		foreach ($arguments as $arg)
		{
			if ($arg === Trace::DUMP_STACK_TRACE)
			{
				$params['type'] = 'stack_trace';

			} else if ( ( $arg === Trace::SEVERITY_TRACING ) || ( $arg === Trace::SEVERITY_INFO ) || ( $arg === Trace::SEVERITY_WARNING ) || ( $arg === Trace::SEVERITY_ERROR ) ) {
				$params['severity'] = $arg;

			} else if ( ( $arg === Trace::HIDE_HEADER ) || ( $arg === Trace::HIDE_FOOTER ) || ( $arg === Trace::HIDE_HEADER_FOOTER ) ) {
				$params['displayOption'] = $arg;

			} else {
				// If we get one item, it's 'output' if we get a second string, then there was an optional 'label'.
				if ( ! $output ) {
					$output = $arg;
				} else {
					$params['label'] = print_r( $output, true );
					$output          = $arg;
				}
			}
		}


		if (!array_key_exists('type', $params))
			$params['type'] = gettype($output);


		// Format the output depending on what it is.
		if (is_array($output))
		{
			$output = print_r($output, true);
			$params['isMultiLine'] = true;

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
				$params['isMultiLine'] = true;
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


		if (!$output && !isset($output) )
		{
			$output = "[empty]";
		}

		$params['output'] = $output;

		return $params;
	}



	private static function formatErrorCaller(array $input, array $stackTrace)
	{
		$params = array();
		$params['file'] = $input[2];
		$params['line'] = $input[3];
		$params['stack'] = $stackTrace;

		return $params;
	}



	private static function formatOutputCaller(array $stackTrace)
	{
		$params = array();

		if ( isset( $stackTrace[0] ) && is_array( $stackTrace[0] ) ) {
			if ( array_key_exists( 'file', $stackTrace[0] ) ) {
				$params['file'] = $stackTrace[0]['file'];
			}

			if ( array_key_exists( 'line', $stackTrace[0] ) ) {
				$params['line'] = $stackTrace[0]['line'];
			}
		}

		$params['function'] = "";
		if ( array_key_exists( 1, $stackTrace ) ) {
			$params['function'] = "{$stackTrace[1]['function']}()";
			if ( array_key_exists( 'class', $stackTrace[1] ) ) {
				$params['function'] = "{$stackTrace[1]['class']}{$stackTrace[1]['type']}{$stackTrace[1]['function']}()";
			}

		}

		$params['stack'] = $stackTrace;

		return $params;
	}


	public static function logInfo( $output, array $tags = array() ) {
		if ( ! is_string( $output ) ) {
			$output = print_r( $output, true );
		}

		$args = array(
			'args'   => array(
				$output,
				self::SEVERITY_INFO,
			),
			'caller' => self::formatOutputCaller( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ),
			'tags'   => $tags,
		);
		call_user_func_array( array( '\Observability\Client\Trace', '_log' ), array( $args ) );
	}


	public static function logWarning( $output, array $tags = array() ) {
		if ( ! is_string( $output ) ) {
			$output = print_r( $output, true );
		}

		$args = array(
			'args'   => array(
				$output,
				self::SEVERITY_WARNING,
			),
			'caller' => self::formatOutputCaller( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ),
			'tags'   => $tags,
		);
		call_user_func_array( array( '\Observability\Client\Trace', '_log' ), array( $args ) );
	}


	public static function logError( $output, array $tags = array() ) {
		if ( ! is_string( $output ) ) {
			$output = print_r( $output, true );
		}

		$args = array(
			'args'   => array(
				$output,
				self::SEVERITY_ERROR,
			),
			'caller' => self::formatOutputCaller( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ),
			'tags'   => $tags,
		);
		call_user_func_array( array( '\Observability\Client\Trace', '_log' ), array( $args ) );
	}

}
