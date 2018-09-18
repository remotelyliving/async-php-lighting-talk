<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Loop;

Loop::run(function () {

    $timer = Loop::repeat(1000, function () {
        static $i;
        $i = $i ? ++$i : 1;
        print "Demonstrating how alive the parent is for the {$i}th time.\n";
    });

    try {

        // Create a new child process that does some blocking stuff.
        $process_1 = \Amp\Parallel\Context\Process::run(__DIR__ . "/blocking.php");
        $process_2 = \Amp\Parallel\Context\Process::run(__DIR__ . "/blocking.php");

        print "Waiting 2 seconds to send start data...\n";
        yield new \Amp\Delayed(2000);
        yield $process_1->send(5);
        yield $process_2->send(1);

        printf("Received the following from process 1: %s\n", yield $process_1->receive());
        printf("Received the following from process 2: %s\n", yield $process_2->receive());

        // wait for everyone to finish up and see what happened
        printf("Process 1 ended with value %d!\n", yield $process_1->join());
        printf("Process 2 ended with value %d!\n", yield $process_2->join());

    } finally {
        Loop::cancel($timer);
    }

});

