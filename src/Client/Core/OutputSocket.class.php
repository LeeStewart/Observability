<?php declare(strict_types=1);
/**
 * Client - Core - Output Socket Class
 *
 * One of the classes that allows the Core code to output to different devices or other
 * servers. This class outputs to a Storage Service.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/


namespace Observability\Client\Core;


use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;



class OutputSocket implements OutputInterface
{
	use OutputTrait;

	/** @var Socket $socket */
	private $socket = null;



	public function __construct($storageServiceAddress='tcp://localhost:55012')
	{
		try
		{
			$factory = new Factory();
			$this->socket = $factory->createClient($storageServiceAddress);

		}
		catch (Exception $e)
		{
			// @todo Need to report this error
			$this->socket = null;
		}

	}



	public function checkConnection()
	{
		return (bool)$this->socket;
	}



	public function output(array $params=[])
	{
		if (!$this->socket)
			return;

		$this->socket->write(json_encode($params)."\n");
	}



	public function startup(array $params=[])
	{
		if (!$this->socket)
			return;

		$this->socket->write(json_encode($params)."\n");

		// Receive and ignore response.
		$response = $this->socket->read(8192);
		//var_dump(htmlentities($response));


		// @todo Remove temporary debugging output...
		echo "<pre>";
		print_r($params);
		echo "</pre>";
	}



	public function shutdown(array $params=[])
	{
		if (!$this->socket)
			return;

		$this->socket->write(json_encode($params)."\n");

		$this->socket->shutdown();
		$this->socket->close();

		// @todo Remove temporary debugging output...
		echo "<pre>";
		print_r($params);
		echo "</pre>";
	}

}
