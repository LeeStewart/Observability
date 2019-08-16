<?php declare(strict_types=1);
/**
 * Storage Service - Mongo Handler Class
 *
 * One of the handlers that allows the Storage Service to interface with other external
 * programs.  This class will store data in a Mongo DB server.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\StorageService\TransportHandlers;


use \MongoClient;



class MongoHandler implements TransportHandlerInterface
{
	use TransportHandlerTrait;

	private $db = null;



	public function __construct()
	{
	}


	public function connect(array $params=[])
	{
		$client = new MongoClient();
		$this->db = $client->selectDB("observability");

		return true;
	}



	public function startup(array $params=[])
	{
		$this->db->logs->insert($params);

	}



	public function shutdown(array $params=[])
	{
		$this->db->logs->insert($params);

	}



	public function output(array $params=[])
	{
		$this->db->logs->insert($params);

	}

}
