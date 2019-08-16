<?php declare(strict_types=1);
/**
 * Client - Core - Output Trait
 *
 * A set of methods that implement the Output Interface.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\Client\Core;



/**
 * Trait OutputTrait
 *
 * @see OutputInterface
 */
trait OutputTrait
{
	private $skipDisplay = false;



	public function startup(array $params=[])
	{
	}



	public function shutdown(array $params=[])
	{
	}



	public function skipDisplay($skip)
	{
		$this->skipDisplay = $skip;
	}



	public function output(array $params=[])
	{
	}

}
