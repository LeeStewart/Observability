<?php declare(strict_types=1);
/**
 * Client - Core - Output Web Class
 *
 * One of the classes that allows the Core code to output to different devices or other
 * servers. This class outputs to a web page and attempts to color code the Trace data.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\Client\Core;


use Observability\Client\Trace;



class OutputWeb implements OutputInterface
{
	use OutputTrait;


	const CLASS_SEVERITY_TRACING = "tracer-severity_tracing";
	const CLASS_SEVERITY_INFO = "tracer-severity_info";
	const CLASS_SEVERITY_WARNING = "tracer-severity_warning";
	const CLASS_SEVERITY_ERROR = "tracer-severity_error";



	public function output(array $params=[])
	{
		if ($this->skipDisplay || ($params['action'] != 'trace-output'))
			return;


		$class = self::CLASS_SEVERITY_TRACING;
		switch ($params['severity'])
		{
			case Trace::SEVERITY_INFO:
				$class = self::CLASS_SEVERITY_INFO;
				break;
			case Trace::SEVERITY_WARNING:
				$class = self::CLASS_SEVERITY_WARNING;
				break;
			case Trace::SEVERITY_ERROR:
				$class = self::CLASS_SEVERITY_ERROR;
				break;
		}

		$output = "";

		$label = "";
		if ($params['label'])
		{
			$label .= "<span class='tracer-label'>{$params['label']}</span>";
		}


		if ($params['isMultiLine'])
		{
			$output .= "{$label}<pre class='tracer-message'>{$params['output']}</pre>";
		}
		else
		{
			$output .= "<p class='tracer-message'>";
			if ($label)
				$output .= "{$label}: ";

			$output .= "{$params['output']}</p>";
		}


		$caller  = "<p class='tracer-caller_info'>";
		$caller .= $params['caller']['function']? "{$params['caller']['function']} in ": "";
		$caller .= "{$params['caller']['file']}:{$params['caller']['line']}";
		$caller .= $params['option']? " ({$params['option']})": "";
		$caller .= "</p>";

		echo "<div class='tracer-output $class'>$output $caller</div>";
	}



	public function shutdown(array $params=[])
	{
		if ($this->skipDisplay)
			return;

		echo "	<style>
					.tracer-output {
						width: 90%;
						background-color: #eee;
						color: #000;
						margin: 0 auto 0;
						padding: 5px 10px;
						border: 1px solid #999;
						font-size: 12px;
						font-family: verdana, arial, sans-serif;
					}
					.tracer-output p, .tracer-output pre {
						margin: 0;
						padding: 2px 6px;
					}
					.tracer-output +.tracer-output {
						border-top: none;
					}
					.tracer-caller_info {
						color: #666;
						font-size: 10px;
					}
					.tracer-label {
						font-weight: bold;
					}

					.tracer-output.tracer-severity_info {
						color: #00529B;
						background-color: #BDE5F8;
					}

					.tracer-output.tracer-severity_warning {
						color: #9F6000;
						background-color: #FEEFB3;
					}

					.tracer-output.tracer-severity_error {
						color: #D8000C;
						background-color: #FFD2D2;
    				}
				</style>";
	}

}
