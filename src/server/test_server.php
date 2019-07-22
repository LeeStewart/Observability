<?php declare(strict_types=1);



require __DIR__ . '/../../vendor/bloatless/src/Connection.php';
require __DIR__ . '/../../vendor/bloatless/src/Socket.php';
require __DIR__ . '/../../vendor/bloatless/src/Server.php';

require __DIR__ . '/../../vendor/bloatless/src/Application/ApplicationInterface.php';
require __DIR__ . '/../../vendor/bloatless/src/Application/Application.php';
require __DIR__ . '/../../vendor/bloatless/src/Application/DemoApplication.php';
require __DIR__ . '/../../vendor/bloatless/src/Application/StatusApplication.php';



$server = new \Bloatless\WebSocket\Server('127.0.0.1', 8000);

// server settings:
$server->setMaxClients(100);
$server->setCheckOrigin(false);
$server->setAllowedOrigin('foo.lh');
$server->setMaxConnectionsPerIp(100);
$server->setMaxRequestsPerMinute(2000);

// Hint: Status application should not be removed as it displays usefull server informations:
$server->registerApplication('status', \Bloatless\WebSocket\Application\StatusApplication::getInstance());
$server->registerApplication('demo', \Bloatless\WebSocket\Application\DemoApplication::getInstance());

$server->run();
