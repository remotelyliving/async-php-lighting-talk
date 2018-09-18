<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Loop;
use Amp\Socket\ServerSocket;
use function Amp\asyncCoroutine;

$client = new Amp\Artax\DefaultClient();
$server = new \RemotelyLiving\AsyncPhp\Server();

Loop::run(function () use ($server, $client) {

    $questions = [
        'Will I ever give you up',
        'Will I ever let you down',
        'Will I ever run around and desert you',
    ];

    $clientHandler = function (ServerSocket $socket, string $question) use ($client) {
        $response = yield $client->request("https://8ball.delegator.com/magic/JSON/{$question}");
        $body = yield $response->getBody();
        $bodyLength = \strlen($body);

        yield $socket->end("HTTP/1.1 200 OK\r\nConnection: close\r\nContent-Length: {$bodyLength}\r\n\r\n{$body}");
    };

    while ($client = yield $server->start()) {
        $question = $questions[array_rand($questions)];
        echo "Asking: {$question}" . PHP_EOL;
        Amp\asyncCall($clientHandler, $client, $question);
    }

});

// curl http://127.0.0.1:8080 & curl http://127.0.0.1:8080 & curl http://127.0.0.1:8080