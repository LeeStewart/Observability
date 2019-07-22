<?php declare(strict_types=1);



namespace Observability\Client\Core;




class Core
{
	private static $initialized = false;
	private static $skipDisplay = false;

	/** @var OutputInterface[] $outputInterfaces */
	private static $outputInterfaces = array();

	// How long did it take to execute this page?
	private static $startTimer = 0;


	private function __construct() {}



	public static function addOutputInterface($type, OutputInterface $tracerOutput)
	{
		self::$outputInterfaces[$type] = $tracerOutput;
	}



	public static function startup()
	{
		if (self::$initialized)
			return;

		self::$initialized = true;


		self::$startTimer = microtime(true);

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

		register_shutdown_function(array('Observability\Client\Setup','shutdown'));
	}



	public static function shutdown()
	{
		$params = self::formatShutdownArguments();
		foreach (self::$outputInterfaces as $tracerOutput)
			$tracerOutput->shutdown($params);
	}



	public static function outputTrace(array $params)
	{
		$params['action'] = 'trace-output';
		$params['platform'] = 'php';

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
/*
		$server = array(
////			"script"=>@$_SERVER['SCRIPT_URL'] ?: realpath($argv[0].""),
////			"server"=>@$_ENV['HOSTNAME'],
//			"sandbox"=>$currentSandbox,
//			"boxset"=>(($_ENV['BOXSET']!='stage')? $_ENV['BOXSET']: ($_ENV['IS_UAT']? "uat": 'stage')),
			"timeStamp"=>self::$startTimer,
			"host"=>@$_SERVER['HTTP_HOST'] ?: "Command Line",
			"filename"=>@$_SERVER['SCRIPT_FILENAME'],
			"scheme"=>@$_SERVER['HTTPS']=='on'? "https": (@$_SERVER['HTTP_HOST']? "http": ""),
			"method"=>@$_SERVER['REQUEST_METHOD'],
			"outputType"=>$outputType,
			"referrer" => @$_SERVER['HTTP_REFERER'],
//			"pid" => getmypid(),
		);
*/

		$server = array();
		$server['timeStamp'] = self::$startTimer;
		$server['host'] = @$_SERVER['HTTP_HOST'] ?: "Command Line";
		$server['filename'] = @$_SERVER['SCRIPT_FILENAME'];
		$server['scheme'] = @$_SERVER['HTTPS']=='on'? "https": (@$_SERVER['HTTP_HOST']? "http": "");
		$server['method'] = @$_SERVER['REQUEST_METHOD'];
		$server['outputType'] = $outputType;
		$server['referrer'] = @$_SERVER['HTTP_REFERER'];
		$server['ajax'] = $ajax;

		$params['server'] = $server;

		$params['argv'] = $argv;
		$params['get'] = $_GET;
		$params['post'] = $_POST;

		return $params;
	}



	private static function formatShutdownArguments()
	{
		$params = array();

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

}
