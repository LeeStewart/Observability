<?php declare(strict_types=1);



require_once(__DIR__."/../src/__autoload.php");


use PHPUnit\Framework\TestCase;

use Observability\Client\Setup;


// This is a convenience for these tests, not something that's normally needed.
use Observability\Client\Core\Core as CoreClass;


class ClientCoreTest extends TestCase
{

	public function testDroppingTags()
	{
		Setup::setTags(array("tag"=>"ShouldBeDropped"));
		CoreClass::dropTags();

		$context = json_decode(Setup::getCurrentContextString(), true);
		$this->assertEquals($context['tags'], array());

	}


	public function testVerifyEmptyTags()
	{
		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['tags'], array());

	}


	public function testVerifySimpleStringTag()
	{
		CoreClass::dropTags();
		Setup::setTags(array("string_one"=>"value"));

		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['tags'], array("string_one"=>"value"));

	}


	public function testVerifyMultipleSimpleStringTags()
	{
		CoreClass::dropTags();
		Setup::setTags(array("string_one"=>"value", "string_two"=>"value"));

		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['tags'], array("string_one"=>"value", "string_two"=>"value"));

	}



	public function testGenerateUserIdentifier()
	{
		$identifier = CoreClass::generateIdentifier('LeeStewart@RandomOddness.com');

		$this->assertEquals($identifier, CoreClass::generateIdentifier('LeeStewart@RandomOddness.com  '));
		$this->assertEquals($identifier, CoreClass::generateIdentifier('leestewart@randomoddness.com'));
		$this->assertEquals($identifier, CoreClass::generateIdentifier(' LEESTEWART@randomoddness.com  '));
		$this->assertNotEquals($identifier, CoreClass::generateIdentifier('LeeStewart@RandomOddness.comx'));

	}



	public function testGenerateRandomIdentifier()
	{
		$identifier = CoreClass::generateIdentifier();

		$this->assertNotEquals($identifier, CoreClass::generateIdentifier());

	}



	public function testParentSpanHandling()
	{
		$contextString = Setup::getCurrentContextString();
		Setup::setParentContextString($contextString);

		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['spanIdentifier'], $context['parentSpanIdentifier']);

	}

}
