<?php declare(strict_types=1);




namespace Observability\Client;





class Setup
{


	private function __construct() {}



	public static function addOutputInterface($type, Core\OutputInterface $tracerOutput)
	{
		Core\Core::addOutputInterface($type, $tracerOutput);
	}



	public static function startup()
	{
		Core\Core::startup();
	}



	public static function shutdown()
	{
		Core\Core::shutdown();
	}


	public static function skipDisplay($skip=true)
	{
		Core\Core::skipDisplay($skip);
	}


}
