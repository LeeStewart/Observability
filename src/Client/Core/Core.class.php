<?php declare(strict_types=1);
/**
 * Client - Core Class
 *
 * An internally used class that deal with the formatting and output of all Trace and
 * Metrics data.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2020 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2020.02.22.01
 **/



namespace Observability\Client\Core;



class Core
{
	const PLATFORM = "PHP";
	const VERSION = "2020.02.02.01";

	private static $initialized = false;
	private static $skipDisplay = false;

	/** @var OutputInterface[] $outputInterfaces */
	private static $outputInterfaces = array();

	private static $tags = array();

	// How long did it take to execute this page?
	private static $startTimer = 0;

	private static $spanIdentifier = '';
	private static $parentSpanIdentifier = '';
	private static $userIdentifier = '';
	private static $userIdentifierString = '';
	private static $liveTraceAddress = '';



	private function __construct() {}



	public static function addOutputInterface($type, OutputInterface $tracerOutput)
	{
		self::$outputInterfaces[$type] = $tracerOutput;
	}


	public static function getCurrentContext( array $tags = array() )
	{
		$context = array();

		$tags = array_merge( self::$tags, $tags );
		foreach ( $tags as $key => $value ) {
			if ( ! $value ) {
				unset( $tags[ $key ] );
			}
		}

		$context['spanIdentifier']       = self::$spanIdentifier;
		$context['parentSpanIdentifier'] = self::$parentSpanIdentifier;
		$context['userIdentifier']       = self::$userIdentifier;
		$context['userIdentifierString'] = self::$userIdentifierString;
		$context['liveTraceAddress']     = self::$liveTraceAddress;
		$context['tags']                 = $tags;

		return $context;
	}



	public static function setParentContext(array $context)
	{
		if (!self::$userIdentifier)
			self::$userIdentifier = $context['userIdentifier'];

		if (!self::$userIdentifierString)
			self::$userIdentifierString = $context['userIdentifierString'];

		if (!self::$parentSpanIdentifier)
			self::$parentSpanIdentifier = $context['spanIdentifier'];

		if (!self::$liveTraceAddress)
			self::$liveTraceAddress = $context['liveTraceAddress'];

	}



	public static function setUserIdentifierString($userIdentifierString)
	{
		self::$userIdentifierString = $userIdentifierString;
		self::$userIdentifier = self::generateIdentifier($userIdentifierString);
	}



	public static function setLiveTraceAddress($liveTraceAddress)
	{
		self::$liveTraceAddress = $liveTraceAddress;
	}


	public static function dropTags() {
		self::$tags = array();
	}


	public static function setTag( $tag, $value ) {
		self::$tags[ $tag ] = $value;
	}



	public static function startup()
	{
		if (self::$initialized)
			return;

		self::$initialized = true;


		self::$startTimer = microtime(true);

		self::$spanIdentifier = self::generateIdentifier();

		$header = self::getMessageHeader('start-up');
		$context = self::getCurrentContext();
		$params = self::formatStartupArguments();

		$params = array_merge($header, $context, $params);

		$tracerOutput = null;
		if ($params['outputType'] == 'console')
		{
			$tracerOutput = new OutputConsole();
		}
		else
		{
			$tracerOutput = new OutputWeb();
		}

		$tracerOutput->skipDisplay(self::$skipDisplay);
		self::$outputInterfaces[$params['outputType']] = $tracerOutput;

		// if Ajax, skip output.
		if ($params['isAjax'])
			self::skipDisplay(true);

		foreach (self::$outputInterfaces as $tracerOutput)
			$tracerOutput->startup($params);

		register_shutdown_function(array('Observability\Client\Core\Core','shutdown'));
	}



	public static function shutdown()
	{
		$header = self::getMessageHeader('shut-down');
		$context = self::getCurrentContext();
		$params = self::formatShutdownArguments();

		$params = array_merge($header, $context, $params);

		foreach (self::$outputInterfaces as $tracerOutput)
			$tracerOutput->shutdown($params);
	}


	public static function outputTrace( array $params, array $tags = array() ) {
		$header  = self::getMessageHeader( 'trace-output' );
		$context = self::getCurrentContext( $tags );

		$params = array_merge($header, $context, $params);

		foreach (self::$outputInterfaces as $output)
			$output->output($params);
	}


	public static function outputMetric( array $params, array $tags = array() ) {
		$header  = self::getMessageHeader( 'metrics-output' );
		$context = self::getCurrentContext( $tags );

		$params = array_merge( $header, $context, $params );

		foreach ( self::$outputInterfaces as $output ) {
			$output->output( $params );
		}
	}


	public static function startTiming( array $params, array $tags = array() ) {
		$header  = self::getMessageHeader( 'timing-start' );
		$context = self::getCurrentContext( $tags );

		$params = array_merge( $header, $context, $params );

		foreach ( self::$outputInterfaces as $output ) {
			$output->output( $params );
		}
	}


	public static function outputTiming( array $params, array $tags = array() ) {
		$header  = self::getMessageHeader( 'timing-output' );
		$context = self::getCurrentContext( $tags );

		$params = array_merge( $header, $context, $params );

		foreach ( self::$outputInterfaces as $output ) {
			$output->output( $params );
		}
	}


	public static function skipDisplay($skip=true)
	{
		self::$skipDisplay = $skip;

		foreach (self::$outputInterfaces as $output)
			$output->skipDisplay(self::$skipDisplay);

	}



	private static function getMessageHeader($action)
	{
		$header = array();

		$header['action'] = $action;
		$header['platform'] = Core::PLATFORM;
		$header['version'] = Core::VERSION;
		$header['timeStamp'] = microtime(true);

		return $header;
	}



	private static function formatStartupArguments()
	{
		global $argv;

		$params = array();

		$outputType = "";
		if (php_sapi_name() == 'cli')
		{
			$outputType = "console";
		}
		else
		{
			$outputType = "web";
		}

		$ajax = false;
		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			$ajax = true;

		// $params['server'] = @$_ENV['HOSTNAME'];
		$params['host'] = isset($_SERVER['HTTP_HOST'])? $_SERVER['HTTP_HOST']: "Command Line";
		$params['filename'] = @$_SERVER['SCRIPT_FILENAME'];
		$server['scheme'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')? "https": (@$_SERVER['HTTP_HOST']? "http": "");
		$params['method'] = @$_SERVER['REQUEST_METHOD'];
		$params['outputType'] = $outputType;
		$params['referrer'] = @$_SERVER['HTTP_REFERER'];
		$params['pid'] = getmypid();
		$params['isAjax'] = $ajax;

		$params['argv'] = $argv;
		$params['get'] = $_GET;
		$params['post'] = $_POST;

		return $params;
	}



	private static function formatShutdownArguments()
	{
		$params = array();

		$params["duration"] = (microtime(true) - self::$startTimer);
		$params["bytesUsed"] = memory_get_peak_usage(true);

		$memoryLimit = ini_get( 'memory_limit' );
		if ( $memoryLimit == - 1 ) {
			$params["bytesAvailable"] = 0;

		} else if ( $memoryLimit == "" . intval( $memoryLimit ) ) {
			$params["bytesAvailable"] = $memoryLimit;

		} else if ( preg_match( '/^(\d+)(.)$/', $memoryLimit, $matches ) ) {
			$memoryLimit = 0;

			if ( $matches[2] == 'G' ) {
				$memoryLimit = $matches[1] * 1024 * 1024 * 1024; // nnnG -> nnn bytes
			} else if ( $matches[2] == 'M' ) {
				$memoryLimit = $matches[1] * 1024 * 1024; // nnnM -> nnn bytes
			} else if ( $matches[2] == 'K' ) {
				$memoryLimit = $matches[1] * 1024; // nnnK -> nnn bytes
			}
			$params["bytesAvailable"] = $memoryLimit;
		}

		$params["userIP"] = @($_SERVER['REMOTE_ADDR'] && $_SERVER['REMOTE_ADDR']!="::1") ?: "localhost";
		$params["userAgent"] = @$_SERVER['HTTP_USER_AGENT'];

		$files = array();
		foreach (get_included_files() as $file)
		{
			if (!realpath($file))
			{
				if (!realpath(dirname($_SERVER['SCRIPT_FILENAME'])."/{$file}"))
					$files[] = $file;

				else
					$files[] = realpath(dirname($_SERVER['SCRIPT_FILENAME'])."/{$file}");
			}
			else
				$files[] = realpath($file);
		}
		$params['files'] = $files;

		return $params;
	}



	public static function generateIdentifier($user="")
	{
		if ($user)
		{
			$hash = md5(strtolower(trim($user)));

			return sprintf('%08s-%04s-%04x-%04x-%12s',

				// 32 bits for "time_low"
				substr($hash, 0, 8),

				// 16 bits for "time_mid"
				substr($hash, 8, 4),

				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 3
				(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

				// 48 bits for "node"
				substr($hash, 20, 12)
			);
		}
		else
		{
			return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff),

				// 16 bits for "time_mid"
				mt_rand(0, 0xffff),

				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand(0, 0x0fff) | 0x4000,

				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand(0, 0x3fff) | 0x8000,

				// 48 bits for "node"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
			);
		}
	}

}
