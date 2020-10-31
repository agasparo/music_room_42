<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class SocketWeb implements MessageComponentInterface {

	protected $clients;

	public function __construct() {

        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {

    	$this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {

    	$numRecv = count($this->clients);
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $sendNotAll = json_decode($msg, true)['type'];
        foreach ($this->clients as $client) {

        	if (!$sendNotAll)
        		$client->send($msg);
            if ($sendNotAll && $from !== $client)
                $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {

    	$this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    
    	echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

echo "server listen on localhost:4242\n";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SocketWeb()
        )
    ),
    4242
);

$server->run();