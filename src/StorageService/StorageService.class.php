<?php declare(strict_types=1);
/**
 * Storage Service Class
 *
 * This is the main code that deals with the logic for receiving Trace and Metrics data
 * and sending it to the different subsystems.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2020 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2020.02.22.01
 **/



namespace Observability\StorageService;


use Observability\StorageService\TransportHandlers\TransportHandlerInterface;


class StorageService {

	/** @var StorageServiceSocket $server - for incoming data... */
	private $server = null;

	/** @var TransportHandlerInterface[] $transportHandlers - for outgoing data... */
	private $transportHandlers = array();

	private $running = false;


	public function __construct() {

	}


	public function setStorageServiceSocket( StorageServiceSocket $server ) {
		$this->server = $server;
	}


	public function addTransportHandler( TransportHandlerInterface $handler ) {
		$this->transportHandlers[] = $handler;
	}


	public function output( array $params ) {
		foreach ( $this->transportHandlers as $handler ) {
			$handler->output( $params );
		}

	}


	public function startup() {
		$params = array();

		foreach ( $this->transportHandlers as $handler ) {
			$handler->startup( $params );
		}

		register_shutdown_function( array( $this, 'shutdown' ) );

		$this->running = true;

		while ( $this->running ) {
			$this->server->processClients();

			while ( $data = $this->server->getIncomingData() ) {
				$this->output( $data );
			}

			usleep( 1 );
		}

	}


	public function shutdown() {
		$this->running = false;
	}

}
