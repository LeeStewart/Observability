<?php declare(strict_types=1);
/**
 * Storage Server - Socket Class
 *
 * Accepts incoming connections from Client code and accepts the Trace and Metrics info
 * that is being output.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2020 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2020.02.22.01
 **/



namespace Observability\StorageService;


use Observability\Client\Core\Core;


class StorageServiceSocket {
	/** @var resource $socket - we'll listen on this socket... */
	private $socket = null;

	/** @var resource[] $clients - incoming data will come from these sockets... */
	private $clients = array();

	private $connectionNum = 0;

	private $data = array();


	public function __construct( $port = 55012 ) {
		// @todo Change the socket into one that uses UDP.
		$this->socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );

		socket_set_option( $this->socket, SOL_SOCKET, SO_REUSEADDR, 1 );

		// Now "bind" the socket to the address to "localhost".
		socket_bind( $this->socket, '127.0.0.1', $port );

		socket_listen( $this->socket );
	}


	public function checkConnection() {
		return (bool) $this->socket;
	}


	public function processClients() {
		$read              = $this->clients;
		$read['listening'] = $this->socket;

		$write = $except = null;

		// Get a list of all the clients that have data.
		if ( socket_select( $read, $write, $except, null ) < 1 ) {
			return;
		}

		// Check if there is a client trying to connect.
		if ( isset( $read['listening'] ) ) {
			$this->connectionNum ++;

			$client = socket_accept( $this->socket );

			$request = json_decode( socket_read( $client, 1024 * 1024, PHP_NORMAL_READ ), true );

			if ( ! $request || ! array_key_exists( 'spanIdentifier', $request ) ) {
				// @todo Do something with the error.

			} else {
				$response = array(
					'platform'      => Core::PLATFORM,
					'version'       => Core::VERSION,
					'connectionNum' => $this->connectionNum,
					'timeStamp'     => microtime( true ),
				);
				socket_write( $client, json_encode( $response ) . "\n" );

				//echo "New client connected #{$this->connectionNum} - '{$request['spanIdentifier']}'\n";

				$this->clients[ $request['spanIdentifier'] ] = $client;

				$this->data[] = $request;
			}

			// Remove the listening socket from the clients-with-data array.
			unset( $read['listening'] );
		}


		// Loop through all the clients that have data.
		foreach ( $read as $identifier => $client ) {
			$data = @socket_read( $client, 1024 * 1024, PHP_NORMAL_READ );

			// If there was an error, we'll remove this socket.
			if ( $data === false ) {
				unset( $this->clients[ $identifier ] );

			} else {
				//echo "$identifier - ";
				//echo strlen($data)." bytes";
				//echo "\n";
				$data = json_decode( trim( $data ), true );
				unset( $data['files'] );
				$this->data[] = $data;
			}
		}
	}


	public function getIncomingData() {
		return array_shift( $this->data );
	}

}
