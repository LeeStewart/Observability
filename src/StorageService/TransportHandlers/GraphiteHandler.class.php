<?php declare(strict_types=1);
/**
 * Storage Server - Graphite Handler Class
 *
 * Deals with sending all trace information to a remote Live Trace server, where it will
 * be distributed to any listening Viewers.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.11.08.01
 **/



namespace Observability\StorageService\TransportHandlers;


use Socket\Raw\Exception;
use Socket\Raw\Factory;
use Socket\Raw\Socket;




class GraphiteHandler implements TransportHandlerInterface
{
	use TransportHandlerTrait;


	/** @var Socket $connection - outgoing socket */
	private $connection = null;

	protected $tagFilter = null;
	protected $pathFormatter = null;


	public function __construct()
	{
		$this->setTagFilter(array('\Observability\StorageService\TransportHandlers\GraphiteHandler', 'filterTags'));
		$this->setPathFormatter(array('\Observability\StorageService\TransportHandlers\GraphiteHandler', 'formatPath'));
	}



	public function connect(array $params=['graphiteAddress'=>'tcp://localhost:31019'])
	{
		return;
		if (!array_key_exists('graphiteAddress', $params))
			return false;

		// Already have a connection.
		if ($this->connection)
			return true;

		try
		{
			$factory = new Factory();
			$this->connection = $factory->createClient($params['graphiteAddress']);

		}
		catch (Exception $e)
		{
			// @todo Need to report this error
			$this->connection = null;
			return false;
		}

		return true;
	}


	public function setTagFilter(callable $func) {
		$this->tagFilter = $func;
	}
	public function setPathFormatter(callable $func) {
		$this->pathFormatter = $func;
	}


	public function output(array $params=[])
	{
		if (!isset($params['action']) || (isset($params['action']) && $params['action']!='metrics-output'))
			return;

		$path = call_user_func($this->pathFormatter, $params);
		$path = self::sanitizePath($path);

		$message = join( ".", $path );

		$tags = call_user_func($this->tagFilter, $params);
		$tags = self::sanitizeTags($tags);
		if (is_array($tags) && count($tags))
		{
			$parts = array();
			foreach ($tags as $key=>$value)
				$parts[] = "$key=$value";
			$message .= ";".join(';', $parts);
		}

		$message .= " ".$params['metric_value'];
		$message .= " ".time();

		echo "\n\n[$message]\n";
		print_r($tags);


		return;

		try
		{
			$this->connection->write(json_encode($params)."\n");
		}
		catch (Exception $e)
		{
			$this->connection = null;
			$this->output($params);
		}
	}



	protected static function sanitizeTags($tags)
	{
		$ret = array();

		foreach ($tags as $key=>$value)
		{
			$key = str_replace(array(';','!','^','='), '', $key);
			$value = str_replace(array(';','~'), '', $value);

			$ret[$key] = $value;
		}

		return $ret;
	}



	protected static function sanitizePath($path)
	{
		$ret = array();

		foreach ($path as $value)
		{
			$ret[] = str_replace(array('.',';','!','^','='), '', $value);
		}

		return $ret;
	}



	public static function formatPath( array $params ) {
		return array_values( $params['tags'] );
	}



	public static function filterTags( array $params ) {
		return $params['tags'];
	}
}
