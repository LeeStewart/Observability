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
		Setup::setAppPath("ShouldBeDropped");
		CoreClass::dropAppTags();

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
		CoreClass::dropAppTags();
		Setup::setAppPath("StringOne");

		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['tags'], array("StringOne"));

	}


	public function testVerifyMultipleSimpleStringTagss()
	{
		CoreClass::dropAppTags();
		Setup::setAppPath("String1", "String2");

		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['tags'], array("String1", "String2"));

	}



	public function testVerifyComplexStringTags()
	{
		CoreClass::dropAppTags();
		Setup::setAppPath("StringA.StringB");

		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['tags'], array("StringA", "StringB"));

	}



	public function testVerifyMultipleComplexStringTags()
	{
		CoreClass::dropAppTags();
		Setup::setAppPath("StringOneA.StringOneB", "StringTwoA.StringTwoB");

		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['tags'], array("StringOneA", "StringOneB", "StringTwoA", "StringTwoB"));

	}



	public function testVerifyMultipleMixedStringTags()
	{
		CoreClass::dropAppTags();
		Setup::setAppPath("StringOneA", "StringTwoA.StringTwoB", "StringThreeA");

		$context = json_decode(Setup::getCurrentContextString(), true);

		$this->assertEquals($context['tags'], array("StringOneA", "StringTwoA", "StringTwoB", "StringThreeA"));

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
