<?php declare(strict_types=1);



namespace Observability\Client\Core;




class Core
{
	const PLATFORM = "PHP";
	const VERSION = "2019.08.07.01";

	private static $initialized = false;
	private static $skipDisplay = false;

	/** @var OutputInterface[] $outputInterfaces */
	private static $outputInterfaces = array();

	private static $appTags = array();

	// How long did it take to execute this page?
	private static $startTimer = 0;

	private static $spanIdentifier = '';
	private static $parentSpanIdentifier = '';
	private static $userIdentifier = '';
	private static $userIdentifierString = '';
	private static $debugServerAddress = '';



	private function __construct() {}



	public static function addOutputInterface($type, OutputInterface $tracerOutput)
	{
		self::$outputInterfaces[$type] = $tracerOutput;
	}



	public static function getCurrentContext()
	{
		$context = array();

		$context['spanIdentifier'] = self::$spanIdentifier;
		$context['parentSpanIdentifier'] = self::$parentSpanIdentifier;
		$context['userIdentifier'] = self::$userIdentifier;
		$context['userIdentifierString'] = self::$userIdentifierString;
		$context['debugServerAddress'] = self::$debugServerAddress;
		$context['platform'] = Core::PLATFORM;
		$context['version'] = Core::VERSION;

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

		if (!self::$debugServerAddress)
			self::$debugServerAddress = $context['debugServerAddress'];

	}



	public static function setUserIdentifierString($userIdentifierString)
	{
		self::$userIdentifierString = $userIdentifierString;
		self::$userIdentifier = self::generateIdentifier($userIdentifierString);
	}



	public static function setAppTag($appTag)
	{
		self::$appTags[] = $appTag;
	}



	public static function startup()
	{
		if (self::$initialized)
			return;

		self::$initialized = true;


		self::$startTimer = microtime(true);

		self::$spanIdentifier = self::generateIdentifier();

		$params = self::formatStartupArguments();


		$tracerOutput = null;
		if ($params['server']['outputType'] == 'console')
		{
			$tracerOutput = new OutputConsole();
		}
		else
		{
			$tracerOutput = new OutputWeb();
		}

		$tracerOutput->skipDisplay(self::$skipDisplay);
		self::$outputInterfaces[$params['server']['outputType']] = $tracerOutput;

		// if Ajax, skip output.
		if ($params['server']['ajax'])
			self::skipDisplay(true);

		foreach (self::$outputInterfaces as $tracerOutput)
			$tracerOutput->startup($params);

		register_shutdown_function(array('Observability\Client\Core\Core','shutdown'));
	}



	public static function shutdown()
	{
		$params = self::formatShutdownArguments();
		foreach (self::$outputInterfaces as $tracerOutput)
			$tracerOutput->shutdown($params);
	}



	public static function outputTrace(array $params)
	{
		$context = self::getCurrentContext();
		$context['action'] = 'trace-output';

		$params = array_merge($context, $params);

		foreach (self::$outputInterfaces as $output)
			$output->output($params);
	}



	public static function skipDisplay($skip=true)
	{
		self::$skipDisplay = $skip;

		foreach (self::$outputInterfaces as $output)
			$output->skipDisplay(self::$skipDisplay);

	}



	private static function formatStartupArguments()
	{
		global $argv;

		$params = self::getCurrentContext();
		$params['action'] = 'start-up';

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


		$server = array();
		$server['timeStamp'] = self::$startTimer;
		// $server['server'] = @$_ENV['HOSTNAME'];
		$server['host'] = @$_SERVER['HTTP_HOST'] ?: "Command Line";
		$server['filename'] = @$_SERVER['SCRIPT_FILENAME'];
		$server['scheme'] = @$_SERVER['HTTPS']=='on'? "https": (@$_SERVER['HTTP_HOST']? "http": "");
		$server['method'] = @$_SERVER['REQUEST_METHOD'];
		$server['outputType'] = $outputType;
		$server['referrer'] = @$_SERVER['HTTP_REFERER'];
		$server['pid'] = getmypid();
		$server['ajax'] = $ajax;

		$params['server'] = $server;

		$params['argv'] = $argv;
		$params['get'] = $_GET;
		$params['post'] = $_POST;

		return $params;
	}



	private static function formatShutdownArguments()
	{
		$params = self::getCurrentContext();
		$params['action'] = 'shut-down';

		$internals = array();
		$internals["loadTime"] = (microtime(true) - self::$startTimer);
		$internals["bytesUsed"] = memory_get_peak_usage(true);
		$internals["bytesAvailable"] = intval(ini_get('memory_limit'))*1024*1024;
		$params['internals'] = $internals;

		$user = array();
		$user["ip"] = @($_SERVER['REMOTE_ADDR'] && $_SERVER['REMOTE_ADDR']!="::1") ?: "localhost";
		$user["userAgent"] = @$_SERVER['HTTP_USER_AGENT'];
		$params['user'] = $user;

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
