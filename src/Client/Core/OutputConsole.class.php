<?php declare(strict_types=1);
/**
 * Client - Core - Output Console Class
 *
 * One of the classes that allows the Core code to output to different devices or other
 * servers. This class outputs to the console and attempts to color code the Trace data.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\Client\Core;


use Observability\Client\Trace;



class OutputConsole implements OutputInterface
{
	use OutputTrait;


	const FOREGROUND_RESET = "\033[0m";
	const FOREGROUND_WHITE = "\033[1;37m";
	const FOREGROUND_CALLER = "\033[1;30m";

	const FOREGROUND_SEVERITY_TRACING = "\033[1;37m";
	const FOREGROUND_SEVERITY_INFO = "\033[1;32m";
	const FOREGROUND_SEVERITY_WARNING = "\033[1;36m";
	const FOREGROUND_SEVERITY_ERROR = "\033[1;31m";



	public function output(array $params=[])
	{
		if ($this->skipDisplay || ($params['action'] != 'trace-output'))
			return;


		$output = self::FOREGROUND_SEVERITY_TRACING;
		switch ($params['severity'])
		{
			case Trace::SEVERITY_INFO:
				$output = self::FOREGROUND_SEVERITY_INFO;
				break;
			case Trace::SEVERITY_WARNING:
				$output = self::FOREGROUND_SEVERITY_WARNING;
				break;
			case Trace::SEVERITY_ERROR:
				$output = self::FOREGROUND_SEVERITY_ERROR;
				break;
		}

		if ($params['label'])
		{
			$output .= $params['label'];

			if ($params['multiLine'])
			{
				$output .= self::FOREGROUND_RESET;
				$output .= PHP_EOL;
			}
			else
			{
				$output .= ": ";
			}
		}

		$output .= $params['output'];
		$output .= self::FOREGROUND_RESET;

		$caller  = self::FOREGROUND_CALLER;
		$caller .= $params['caller']['function']? "{$params['caller']['function']} in ": "";
		$caller .= "{$params['caller']['file']}:{$params['caller']['line']}";
		$caller .= $params['option']? " ({$params['option']})": "";
		$caller .= self::FOREGROUND_RESET;

		echo $output.PHP_EOL.$caller.PHP_EOL;
	}

}
