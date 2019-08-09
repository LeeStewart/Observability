<?php declare(strict_types=1);



namespace Observability\Client\Core;



use Socket\Raw\Factory;
use Socket\Raw\Socket;


class OutputSocket implements OutputInterface
{
	use OutputTrait;

	private $collectionServerAddress = '';

	/** @var Socket $socket */
	private $socket = null;



	public function __construct($collectionServerAddress='tcp://localhost:55012')
	{
		$this->collectionServerAddress = $collectionServerAddress;
	}



	public function output(array $params)
	{
		$this->socket->write(json_encode($params)."\n");
	}



	public function startup(array $params)
	{
		$factory = new Factory();

		$this->socket = $factory->createClient($this->collectionServerAddress);

		$this->socket->write(json_encode($params)."\n");

		// Receive and ignore response.
		$response = $this->socket->read(8192);
		//var_dump(htmlentities($response));

		// @todo Remove temporary debugging output...
		echo "<pre>";
		print_r($params);
		echo "</pre>";
	}



	public function shutdown(array $params)
	{
		$this->socket->write(json_encode($params)."\n");

		$this->socket->shutdown();
		$this->socket->close();

		// @todo Remove temporary debugging output...
		echo "<pre>";
		print_r($params);
		echo "</pre>";
	}

}
