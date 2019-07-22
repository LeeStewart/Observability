<?php declare(strict_types=1);



namespace Observability\Client\Core;



/**
 * Trait OutputTrait
 *
 * @see OutputInterface
 */
trait OutputTrait
{
	private $skipDisplay = false;


	public function startup(array $params)
	{
	}



	public function shutdown(array $params)
	{
	}



	public function skipDisplay($skip)
	{
		$this->skipDisplay = $skip;
	}



	public function output(array $params)
	{
	}
}
