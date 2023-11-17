<?php declare(strict_types=1);
/**
 * LiveTrace - Viewer Socket Class
 *
 * Waits for incoming WebSocket connections from Viewers and adds them to an internal
 * list of sockets that will receive tracing data.  Uses the userIdentifier of the
 * sender to route the information to the correct Viewer.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.10.01
 **/



namespace Observability\LiveTrace;


use Observability\Client\Trace;
use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;



class ViewerSocket
{
	/** @var Socket $socket - we'll listen on this socket... */
	private $socket = null;

	/** @var Socket[] $viewers - outgoing data to the Viewers... */
	private $viewers = array();

	private $connectionNum = 0;

	// By default, we'll send the trace data to all of the viewers.
	protected $filterByUserIdentifier = false;


	public function __construct($address='tcp://localhost:61211')
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
		$viewer = null;
		try
		{
			if ($viewer = $this->socket->accept())
			{
				$this->connectionNum++;
				$request = $this->performHandshake($viewer);

				if (!$request || !array_key_exists('userIdentifier', $request))
				{
					// @todo Do something with the error.
					print_r($request);
				}
				else
				{
					$this->viewers[$request['userIdentifier']] = $viewer;
					return $request;
				}
			}

		}
		catch (Exception $e)
		{

		}

		return array();
	}



	private function performHandshake(Socket $viewer)
	{
		$headers = $viewer->read( 1024 * 1024, PHP_BINARY_READ );

		if (preg_match("/Sec-WebSocket-Version: (.*)\r\n/", $headers, $match))
		{
			$version = $match[1];
		}
		else
		{
			Trace::output("The client doesn't support WebSocket");
			return array();
		}

		if ($version == 13)
		{
			// Extract header variables
			if (preg_match("/GET (.*) HTTP/", $headers, $match))
				$root = $match[1];
			if (preg_match("/Host: (.*)\r\n/", $headers, $match))
				$host = $match[1];
			if (preg_match("/Origin: (.*)\r\n/", $headers, $match))
				$origin = $match[1];
			if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match))
				$key = $match[1];

			$acceptKey = $key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
			$acceptKey = base64_encode(sha1($acceptKey, true));

			$upgrade = "HTTP/1.1 101 Switching Protocols\r\n".
					   "Upgrade: websocket\r\n".
					   "Connection: Upgrade\r\n".
					   "Sec-WebSocket-Accept: $acceptKey".
					   "\r\n\r\n";

			$viewer->write($upgrade);

			// Get the first message from the Viewer, this should contain the userIdentifier.
			$message = $this->decodeData( $viewer->read( 1024 * 1024, PHP_BINARY_READ ) );
			// Trace::output($message);

			return json_decode($message, true);
		}
		else
		{
			Trace::output("WebSocket version 13 required (the client supports version {$version})");
			return array();
		}

	}



	public function sendOutgoingData(array $params)
	{
		if ( $this->filterByUserIdentifier ) {
			// Make sure we have a User Identifier and a connection for that user.
			if ( ! array_key_exists( 'userIdentifier', $params ) ) {
				return;
			}

			if ( ! array_key_exists( $params['userIdentifier'], $this->viewers ) ) {
				return;
			}

			try {
				$this->viewers[ $params['userIdentifier'] ]->write( $this->encodeData( $params ) );
			}
			catch ( Exception $e ) {
				echo "[LiveTrace ViewerSocket] User {$params['userIdentifier']} disconnected.\n";
				unset ( $this->viewers[ $params['userIdentifier'] ] );
			}

		} else {
			// Send the data to all of the viewers.
			foreach ( $this->viewers as $identifier => $socket ) {
				try {
					$socket->write( $this->encodeData( $params ) );
				}
				catch ( Exception $e ) {
					echo "[LiveTrace ViewerSocket] User $identifier disconnected.\n";
					unset ( $this->viewers[ $identifier ] );
				}
			}
		}

		return;

	}



	private function decodeData($payload)
	{
		$length = ord($payload[1]) & 127;

		if ($length == 126)
		{
			$masks = substr($payload, 4, 4);
			$data = substr($payload, 8);
		}
		elseif ($length == 127)
		{
			$masks = substr($payload, 10, 4);
			$data = substr($payload, 14);
		}
		else
		{
			$masks = substr($payload, 2, 4);
			$data = substr($payload, 6);
		}

		$text = '';
		for ($i = 0; $i < strlen($data); ++$i)
		{
			$text .= $data[$i] ^ $masks[$i % 4];
		}

		return $text;
	}



	private function encodeData(array $params)
	{
		$text = json_encode($params);

		$b = 129; // FIN + text frame
		$len = strlen($text);

		if ($len < 126) {
			return pack('CC', $b, $len) . $text;
		} elseif ($len < 65536) {
			return pack('CCn', $b, 126, $len) . $text;
		} else {
			return pack('CCNN', $b, 127, 0, $len) . $text;
		}
	}


	public function filterByUserIdentifier( $filter = true ) {
		$this->filterByUserIdentifier = $filter;
	}


	public function shutdown() {
		$this->socket->shutdown();
		$this->socket->close();
		$this->socket = null;
	}
}
