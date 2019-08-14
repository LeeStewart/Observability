<?php declare(strict_types=1);



namespace Observability\StorageService;



use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;



class StorageServiceSocket
{
	/** @var Socket $socket - we'll listen on this socket... */
	private $socket = null;

	/** @var Socket[] $clients - incoming data will come from these sockets... */
	private $clients = array();

	private $connectionNum = 0;



	public function __construct($address='tcp://localhost:55012')
	{
		try
		{
			$factory = new Factory();
			$this->socket = $factory->createServer($address);

			$this->socket->listen();
			$this->socket->setBlocking(false);

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



	public function acceptIncomingConnections()
	{
		$client = null;
		try
		{
			if ($client = $this->socket->accept())
			{
				$this->connectionNum++;
				$request = json_decode($client->read(16 * 1024, PHP_NORMAL_READ), true);

				if (!$request || !array_key_exists('spanIdentifier', $request))
				{
					// @todo Do something with the error.
					print_r($request);
				}
				else
				{
					$response = array(
						'connectionNum'=> $this->connectionNum,
					);
					$client->write(json_encode($response)."\n");

					echo "New client connected #{$this->connectionNum} - '{$request['spanIdentifier']}'\n";

					//				print_r($request);

					$this->clients[$request['spanIdentifier']] = $client;
					return $request;
				}
			}

		}
		catch (Exception $e)
		{

		}

		return array();
	}



	public function getIncomingData()
	{
		foreach ($this->clients as $identifier=>$client)
		{
			$data = '';

			try
			{
				$client->assertAlive();

				//			$data = $client->recv(16*1024, MSG_DONTWAIT | MSG_PEEK);
				$ret = socket_recv($client->getResource(), $buffer, 16 * 1024, MSG_DONTWAIT | MSG_PEEK);

				if ($ret === false)
				{
					// This means "no data"?

				}
				else if ($ret > 0)
				{
					$data = $client->read(16 * 1024, PHP_NORMAL_READ);

					$data = trim($data);

					echo "$identifier - ";
					echo strlen($data)." bytes";
					echo "\n";

					$data = json_decode($data, true);
					//if ($data['action'] == 'trace-output')
					//	$tracerOutput->output($data);

					return $data;

				}
				else
				{
					$client->assertAlive();
					echo "Killing connection '$identifier'\n";
					unset($this->clients[$identifier]);
				}

			}
			catch (Exception $e)
			{
				echo "Killing connection '$identifier' {$e->getMessage()}\n";
				unset($this->clients[$identifier]);

			}

		}

		return array();
	}


}
