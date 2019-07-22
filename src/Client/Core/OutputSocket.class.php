<?php declare(strict_types=1);



namespace Observability\Client\Core;





class OutputSocket implements OutputInterface
{
	use OutputTrait;



	public function output(array $params)
	{

	}


	public function startup(array $params)
	{
		echo "<pre>";
		print_r($params);
		echo "</pre>";
	}


	public function shutdown(array $params)
	{
		echo "<pre>";
		print_r($params);
		echo "</pre>";
	}

}
