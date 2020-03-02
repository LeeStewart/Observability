<?php declare(strict_types=1);
/**
 * Client - Setup Class
 *
 * A static class that acts as the interface for all Client-side setup and configuration.
 *
 *****************************************************************************************
 * @author Lee Stewart <LeeStewart@RandomOddness.com>
 * @copyright (c) 2019 Lee Stewart
 * @license https://github.com/LeeStewart/obs-php/blob/master/LICENSE
 * @version 2019.08.14.01
 **/



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
		// shutdown is called automatically.
	}


	public static function setTags( array $tags ) {
		foreach ( $tags as $tag => $value ) {
			Core\Core::setTag( $tag, $value );
		}
	}



	public static function setUserIdentifierString($userIdentifierString)
	{
		Core\Core::setUserIdentifierString($userIdentifierString);
	}



	public static function setLiveTraceAddress($liveTraceAddress)
	{
		Core\Core::setLiveTraceAddress($liveTraceAddress);
	}



	public static function skipDisplay($skip=true)
	{
		Core\Core::skipDisplay($skip);
	}



	public static function setErrorHandler()
	{
		Trace::setErrorHandler();
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
