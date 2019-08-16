<?php declare(strict_types=1);
/**
 * Storage Server - Live Trace Handler Class
 *
 * Deals with sending all trace information to a remote Live Trace server, where it will
 * be distributed to any listening Viewers.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\StorageService\TransportHandlers;


use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;




class LiveTraceHandler implements TransportHandlerInterface
{
	use TransportHandlerTrait;


	/** @var Socket[] $connections - outgoing sockets */
	private $connections = array();



	public function __construct()
	{
	}



	public function connect(array $params=['liveTraceAddress'=>'tcp://localhost:31019'])
	{
		if (!array_key_exists('liveTraceAddress', $params))
			return false;

		// Already have a connection.
		if (array_key_exists($params['liveTraceAddress'], $this->connections))
			return true;

		try
		{
			$factory = new Factory();
			$this->connections[$params['liveTraceAddress']] = $factory->createClient($params['liveTraceAddress']);

		}
		catch (Exception $e)
		{
			// @todo Need to report this error
			unset($this->connections[$params['liveTraceAddress']]);
			return false;
		}

		return true;
	}



	public function output(array $params=[])
	{
		if (!$params['liveTraceAddress'])
			return;

		if (!array_key_exists($params['liveTraceAddress'], $this->connections))
		{
			if (!$this->connect($params))
				return;
		}

		try
		{
			$this->connections[$params['liveTraceAddress']]->write(json_encode($params)."\n");
		}
		catch (Exception $e)
		{
			unset($this->connections[$params['liveTraceAddress']]);
			$this->output($params);
		}
	}

}
