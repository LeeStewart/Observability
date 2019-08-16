<?php declare(strict_types=1);
/**
 * Storage Service - Transport Handler Interface
 *
 * All of the Transport Handlers will use this interface.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\StorageService\TransportHandlers;



/**
 * Interface TransportHandlerInterface
 *
 * @see TransportHandlerTrait
 */
interface TransportHandlerInterface
{
	public function startup(array $params=[]);
	public function shutdown(array $params=[]);
	public function output(array $params=[]);
	public function connect(array $params=[]);
}
