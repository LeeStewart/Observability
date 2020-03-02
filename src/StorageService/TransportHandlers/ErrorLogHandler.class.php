<?php declare(strict_types=1);
/**
 * Storage Service - Error Log Handler Class
 *
 * One of the handlers that allows the Storage Service to interface with other external
 * programs.  This class outputs a formatted message to the PHP error_log() function.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2020 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2020.02.22.01
 **/



namespace Observability\StorageService\TransportHandlers;



class ErrorLogHandler implements TransportHandlerInterface
{
	use TransportHandlerTrait;

	public function output(array $params=[])
	{
		// Only show tracing information.
		if ($params['action'] != 'trace-output') {
			return;
		}

		// Only show errors that were produced by PHP.
		if ($params['option'] != 'php') {
			return;
		}

		$output = $params['output'].' ';
		$output .= ! empty( $params['caller']['function'] ) ? "{$params['caller']['function']} in " : "";
		$output .= ! empty( $params['caller']['file'] ) ? $params['caller']['file'] : "";
		$output .= ! empty( $params['caller']['line'] ) ? ":{$params['caller']['line']}" : "";

		error_log($output);
	}

}
