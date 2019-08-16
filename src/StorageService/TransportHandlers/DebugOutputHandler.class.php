<?php declare(strict_types=1);
/**
 * Storage Service - Debug Output Handler Class
 *
 * One of the handlers that allows the Storage Service to interface with other external
 * programs.  This class simply outputs Tracing to the console.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\StorageService\TransportHandlers;



class DebugOutputHandler extends \Observability\Client\Core\OutputConsole implements TransportHandlerInterface
{

	public function connect(array $params = [])
	{
		return true;
	}
}
