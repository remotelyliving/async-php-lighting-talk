<?php

namespace RemotelyLiving\AsyncPhp;

use Amp\Parallel\Worker\Environment;
use Amp\Parallel\Worker\Task;

class BlockingTask implements Task
{
    private $function;

    private $args;

    public function __construct(callable $function, ...$args) {
        $this->function = $function;
        $this->args = $args;
    }

    public function run(Environment $environment) {
        return ($this->function)(...$this->args);
    }

    public function getArgs() {
        return $this->args;
    }
}
