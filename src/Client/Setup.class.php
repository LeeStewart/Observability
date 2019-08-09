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
		// Core::shutdown is called automatically.
	}



	public static function setUserIdentifierString($userIdentifierString)
	{
		Core\Core::setUserIdentifierString($userIdentifierString);
	}



	public static function skipDisplay($skip=true)
	{
		Core\Core::skipDisplay($skip);
	}



	public static function getCurrentContextString()
	{
		return json_encode(Core\Core::getCurrentContext());
	}



	public static function setParentContextString($contextString)
	{
		Core\Core::setParentContext(json_decode($contextString, true));
	}

}
