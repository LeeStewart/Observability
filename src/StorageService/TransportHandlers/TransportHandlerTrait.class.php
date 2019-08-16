<?php declare(strict_types=1);
/**
 * Storage Service - Transport Handler Trait
 *
 * A set of methods that implement the Transport Handler Interface.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\StorageService\TransportHandlers;



/**
 * Trait TransportHandlerTrait
 *
 * @see TransportHandlerInterface
 */
trait TransportHandlerTrait
{


	public function startup(array $params = [])
	{
	}



	public function shutdown(array $params = [])
	{
	}



	public function output(array $params = [])
	{
	}



	public function connect(array $params = [])
	{
		return true;
	}

}
