<?php declare(strict_types=1);



namespace Observability\Client\Core;


/**
 * Interface OutputInterface
 *
 * @see OutputTrait
 */
interface OutputInterface
{
	public function startup(array $params);
	public function shutdown(array $params);
	public function skipDisplay($skip);
	public function output(array $params);
}
