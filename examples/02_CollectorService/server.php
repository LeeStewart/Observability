<?php declare(strict_types=1);


ini_set('display_errors', "1");
error_reporting(E_ALL);


//require_once(__DIR__."/src/__autoload.php");
require_once(__DIR__."/vendor/autoload.php");


use Observability\Client\Core\OutputConsole;
use \Observability\Client\Trace;


//\Observability\Client\Setup::startup();



$factory = new \Socket\Raw\Factory();

$server = $factory->createServer('tcp://localhost:55012');

$server->listen();
$server->setBlocking(false);


$tracerOutput = new OutputConsole();


$clients = array();
$connectionNum = 0;
while (true)
{
	try
	{
		if ($client = $server->accept())
		{
			$connectionNum++;
			$request = json_decode($client->read(16 * 1024, PHP_NORMAL_READ), true);

			if (!$request || !array_key_exists('spanIdentifier', $request))
			{
				// Do something with the error.
				print_r($request);
			}
			else
			{
				$response = array(
					'connectionNum'=> $connectionNum,
				);
				$client->write(json_encode($response)."\n");

				echo "New client connected #$connectionNum - '{$request['spanIdentifier']}'\n";

//				print_r($request);

				$clients[$request['spanIdentifier']] = $client;
			}
		}

	}
	catch (Exception $e)
	{

	}

	foreach ($clients as $identifier=>$client)
	{
		try
		{
			$client->assertAlive();

//			$data = $client->recv(16*1024, MSG_DONTWAIT | MSG_PEEK);
			$ret = socket_recv($client->getResource(), $buffer, 16*1024, MSG_DONTWAIT | MSG_PEEK);


			$data = '';

			if ($ret === false)
			{
				// This means "no data"?

			}
			else if ($ret > 0)
			{
				$data = $client->read(16 * 1024, PHP_NORMAL_READ);

				$data = trim($data);

				echo "$identifier - ";
				echo strlen($data)." bytes";
				echo "\n";

				$data = json_decode($data, true);
				if ($data['action'] == 'trace-output')
					$tracerOutput->output($data);

			}
			else
			{

				$client->assertAlive();
				echo "Killing connection '$identifier' {$e->getMessage()}\n";
				unset($clients[$identifier]);
			}

		}
		catch (Exception $e)
		{
			echo "Killing connection '$identifier' {$e->getMessage()}\n";
			unset($clients[$identifier]);

		}


	}


}



exit;



// Put here so the PC init is run first...
require_once(__DIR__ . '/vendor/tracer/__autoload.php');




use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Tracer\TraceServer;

try
{
	echo "Creating server...\n";

	$ws = new WsServer(new TraceServer());

	// Make sure you're running this as root
	$server = IoServer::factory(new HttpServer($ws), 5000);

	echo "running on port {$server->socket->getAddress()}...\n";

	$server->run();

} catch (Exception $e) {
	echo "{$e->getMessage()}\n";
}
