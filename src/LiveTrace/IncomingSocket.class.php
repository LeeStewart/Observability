<?php declare(strict_types=1);
/**
 * LiveTrace - Incoming Socket Class
 *
 * Accepts incoming connections from Storage Services and reads trace information that
 * is being sent.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.10.01
 **/



namespace Observability\LiveTrace;


use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;



class IncomingSocket
{
	/** @var Socket $socket - we'll listen on this socket... */
	private $socket = null;

	/** @var Socket[] $clients - incoming data from the Storage Services... */
	private $clients = array();

	private $connectionNum = 0;

	private $autoExit = false;


	public function __construct($address='tcp://localhost:31019')
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
				$this->clients[$this->connectionNum] = $client;
			}

		}
		catch (Exception $e)
		{

		}

		return;
	}



	public function getIncomingData()
	{
		foreach ($this->clients as $identifier=>$client)
		{
			$data = '';

			try
			{
				$client->assertAlive();

				$ret = socket_recv( $client->getResource(), $buffer, 1024 * 1024, MSG_DONTWAIT | MSG_PEEK );

				if ($ret === false)
				{
					// This means "no data"?

				}
				else if ($ret > 0)
				{
					$data = $client->read( 1024 * 1024, PHP_NORMAL_READ );

					$data = trim($data);
					$len = strlen($data);

					$data = json_decode($data, true);

					//if ($data['action'] == 'trace-output')
					//	$tracerOutput->output($data);

					//echo "{$data['spanIdentifier']} - $len bytes";
					//echo "\n";

					return $data;

				}
				else
				{
					$client->assertAlive();
					echo "Killing connection '$identifier'\n";
					unset($this->clients[$identifier]);
					if ( $this->autoExit && ! count( $this->clients ) ) {
						die( "Incoming Socket was set to Auto Exit, terminating." );
					}
				}

			}
			catch (Exception $e)
			{
				//echo "Killing connection '$identifier' {$e->getMessage()}\n";
				unset($this->clients[$identifier]);

			}

		}

		return array();
	}


	public function shutdown() {
		$this->socket->shutdown();
		$this->socket->close();
		$this->socket = null;
	}


	public function autoExit( $autoExit = true ) {
		$this->autoExit = $autoExit;
	}

}
