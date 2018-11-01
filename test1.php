<?php
/**
 * Created by PhpStorm.
 * User: skymei
 * Date: 2018/10/23
 * Time: 11:39
 */
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    demo($workerId);
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped/n".PHP_EOL;
    sleep(5);
});

$pool->start();

function demo($id)
{
    $time = random_int(2,10);
    echo "$id is processing...... and sleep for $time s".PHP_EOL;

    sleep($time);
}