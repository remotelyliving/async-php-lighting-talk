<?php
// The function returned by this script is run by process.php in a separate process.
// echo, print, printf, etc. in this script are written to STDERR of the parent.
// $argc and $argv are available in this process as any other cli PHP script.
use Amp\Parallel\Sync\Channel;

return function (Channel $channel): \Generator {

    $sleepSeconds = yield $channel->receive();

    printf("Received the following from parent: %d\n", $sleepSeconds);
    print "Sleeping for {$sleepSeconds} seconds...\n";

    sleep($sleepSeconds); // Blocking call in process.

    print "Done sleeping for {$sleepSeconds} seconds...\n";

    yield $channel->send("Data sent from child.");

    return mt_rand(1,10);

};