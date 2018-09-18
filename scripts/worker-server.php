<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Loop;
use Amp\Socket\ServerSocket;

$server = new \RemotelyLiving\AsyncPhp\Server();
$workerFactory = new \Amp\Parallel\Worker\DefaultWorkerFactory();

Loop::run(function () use (&$server, &$workerFactory) {

    $taskQueue = [
        new \RemotelyLiving\AsyncPhp\BlockingTask(\RemotelyLiving\AsyncPhp\Tasks::class . '::sleepyTime', mt_rand(1,10)),
        new \RemotelyLiving\AsyncPhp\BlockingTask(\RemotelyLiving\AsyncPhp\Tasks::class . '::sleepyTime', mt_rand(1,10)),
        new \RemotelyLiving\AsyncPhp\BlockingTask(\RemotelyLiving\AsyncPhp\Tasks::class . '::sleepyTime', mt_rand(1,10)),
        new \RemotelyLiving\AsyncPhp\BlockingTask(\RemotelyLiving\AsyncPhp\Tasks::class . '::sleepyTime', mt_rand(1,10)),
    ];

    $clientHandler = function (ServerSocket $socket, &$workerFactory, &$taskQueue) {
        $workers = [];
        $data = yield $socket->read();

        if (stristr($data, 'start')) {

            $workers = [
                $workerFactory->create(),
                $workerFactory->create(),
                $workerFactory->create(),
            ];

            /** @var \Amp\Parallel\Worker\Worker $worker */
            foreach ($workers as $worker) {
                foreach ($taskQueue as $task) {
                    $worker->enqueue($task);
                }
            }

            $body = 'workers started' . PHP_EOL;
            $bodyLength = \strlen($body);

            yield $socket->end("HTTP/1.1 200 OK\r\nConnection: close\r\nContent-Length: {$bodyLength}\r\n\r\n{$body}");
        }

        if (stristr($data, 'stop')) {
            foreach ($workers as $index => $worker) {
                $worker->shutdown();
                unset($workers[$index]);
            }

            $body = 'workers stopped' . PHP_EOL;
            $bodyLength = \strlen($body);

            yield $socket->end("HTTP/1.1 200 OK\r\nConnection: close\r\nContent-Length: {$bodyLength}\r\n\r\n{$body}");
        }

    };

    while ($client = yield $server->start()) {
        Amp\asyncCall($clientHandler, $client, $workerFactory, $taskQueue);
    }

});

// curl --request POST --data 'start' http://127.0.0.1:8080
// curl --request POST --data 'stop' http://127.0.0.1:8080

// https://github.com/amphp/http-server#example (much cooler server)