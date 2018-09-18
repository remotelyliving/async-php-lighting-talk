<?php

namespace RemotelyLiving\AsyncPhp;

use Amp\Promise;
use function Amp\Socket\listen;

class Server
{
    /**
     * @var \Amp\Socket\Server
     */
    private $_server;

    public function __construct(string $uri = '127.0.0.1:8080')
    {
        $this->_server = listen($uri);

    }

    public function start() : Promise {

        echo "Client accepted on " . $this->_server->getAddress() . "/" . PHP_EOL;

        return $this->_server->accept();

    }
}
