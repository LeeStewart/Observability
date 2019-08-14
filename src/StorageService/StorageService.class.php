<?php declare(strict_types=1);



namespace Observability\StorageService;



use Observability\StorageService\TransportHandlers\TransportHandlerInterface;



class StorageService
{
	const PLATFORM = "PHP";
	const VERSION = "2019.08.07.01";


	/** @var StorageServiceSocket $socket - for incoming data... */
	private $socket = null;

	/** @var TransportHandlerInterface[] $transportHandlers - for outgoing data... */
	private $transportHandlers = array();


	private $running = false;



	public function __construct()
	{

	}


	public function setStorageServiceSocket(StorageServiceSocket $socket)
	{
		$this->socket = $socket;

	}



	public function addTransportHandler(TransportHandlerInterface $handler)
	{
		$this->transportHandlers[] = $handler;
	}



	public function output(array $params)
	{
		foreach ($this->transportHandlers as $handler)
			$handler->output($params);

	}



	public function startup()
	{
		$params = array();

		foreach ($this->transportHandlers as $handler)
			$handler->startup($params);

//		register_shutdown_function(array('Observability\StorageService\Core\Core','shutdown'));

		$this->running = true;

		while ($this->running)
		{
			// This will be a "start-up" action from the remote.
			$data = $this->socket->acceptIncomingConnections();
			if ($data)
				$this->output($data);

			$data = $this->socket->getIncomingData();
			if ($data)
				$this->output($data);
		}

	}


	public function shutdown()
	{
		$this->running = false;
	}




}
