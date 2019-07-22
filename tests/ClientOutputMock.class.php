<?php declare(strict_types=1);



class ClientOutputMock implements Observability\Client\Core\OutputInterface
{
	use Observability\Client\Core\OutputTrait;


	public $lastOutput = null;



	public function output(array $params)
	{
		$this->lastOutput = $params;
	}

}
