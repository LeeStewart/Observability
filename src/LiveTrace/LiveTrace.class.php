<?php declare(strict_types=1);
/**
 * Live Trace Class
 *
 * This is the main code that handles the logic for accepting incoming data from the
 * Storage Servers and sending it to Viewers.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.10.01
 **/



namespace Observability\LiveTrace;



class LiveTrace
{
	const PLATFORM = "PHP";
	const VERSION = "2019.08.07.01";

	/** @var IncomingSocket $incomingSocket - data from the Storage Services... */
	private $incomingSocket = null;

	/** @var ViewerSocket $viewerSocket - connection to the remote Viewers... */
	private $viewerSocket = null;

	private $running = false;

	// By default, we'll send the trace data to all of the viewers.
	protected $filterByUserIdentifier = false;


	public function __construct()
	{

	}



	public function setIncomingSocket(IncomingSocket $socket)
	{
		$this->incomingSocket = $socket;
	}



	public function setViewerSocket(ViewerSocket $socket)
	{
		$socket->filterByUserIdentifier( $this->filterByUserIdentifier );
		$this->viewerSocket = $socket;
	}



	public function startup()
	{
		register_shutdown_function(array($this,'shutdown'));

		$this->running = true;

		while ($this->running)
		{
			// See if a Viewer is connecting to us...
			$this->viewerSocket->acceptIncomingConnections();

			// See if a Storage Service is connecting to us...
			$this->incomingSocket->acceptIncomingConnections();

			// Grab input from a Storage Service and forward it on.
			$data = $this->incomingSocket->getIncomingData();
			if ($data)
				$this->viewerSocket->sendOutgoingData($data);

			usleep(1);
		}

	}


	public function filterByUserIdentifier( $filter = true ) {
		$this->filterByUserIdentifier = $filter;
	}


	public function shutdown()
	{
		$this->viewerSocket->shutdown();
		$this->incomingSocket->shutdown();
		$this->running = false;
	}


}
