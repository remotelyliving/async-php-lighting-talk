<?php

namespace RemotelyLiving\AsyncPhp;

class Tasks
{
    public static function sleepyTime(int $seconds) : void
    {
        sleep($seconds);

        file_put_contents(md5(random_bytes(128)), "Slept for {$seconds} seconds" . PHP_EOL . "time: " . time());
    }
}
