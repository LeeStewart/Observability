<?php declare(strict_types=1);
/**
 * Client - Core - Output Interface
 *
 * All of the Output Interfaces will use this interface.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



namespace Observability\Client\Core;



/**
 * Interface OutputInterface
 *
 * @see OutputTrait
 */
interface OutputInterface
{
	public function startup(array $params=[]);
	public function shutdown(array $params=[]);
	public function skipDisplay($skip);
	public function output(array $params=[]);
}
