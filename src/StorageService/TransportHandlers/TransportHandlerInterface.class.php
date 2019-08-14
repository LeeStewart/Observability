<?php declare(strict_types=1);



namespace Observability\StorageService\TransportHandlers;



/**
 * Interface TransportHandlerInterface
 *
 * @see TransportHandlerTrait
 */
interface TransportHandlerInterface
{
	public function startup(array $params);
	public function shutdown(array $params);
	public function output(array $params);
}
