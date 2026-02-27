<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/database.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

class MessageHandler implements MessageComponentInterface {
    protected $clients;
    private $subscriptions;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->subscriptions = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        if (isset($data->action) && $data->action == 'subscribe') {
            $this->subscriptions[$from->resourceId] = $data->projectId;
            echo "Client {$from->resourceId} subscribed to project {$data->projectId}\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->subscriptions[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function broadcast($projectId, $message) {
        foreach ($this->clients as $client) {
            if (isset($this->subscriptions[$client->resourceId]) && $this->subscriptions[$client->resourceId] == $projectId) {
                $client->send($message);
            }
        }
    }
}

$loop = LoopFactory::create();
$messageHandler = new MessageHandler();

$loop->addPeriodicTimer(1, function() use ($messageHandler) {
    $db = (new Database())->getConnection();
    if ($db) {
        $stmt = $db->query("SELECT * FROM notifications");
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($notifications as $notification) {
            $messageHandler->broadcast($notification['project_id'], $notification['message']);
            $deleteStmt = $db->prepare("DELETE FROM notifications WHERE id = ?");
            $deleteStmt->execute([$notification['id']]);
        }
    }
});

$socket = new Reactor('0.0.0.0:8080', $loop);
$server = new IoServer(
    new HttpServer(
        new WsServer(
            $messageHandler
        )
    ),
    $socket,
    $loop
);

echo "WebSocket server started on port 8080\n";
$server->run();

