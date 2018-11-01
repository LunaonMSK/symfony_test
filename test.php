<?php
/**
 * Created by PhpStorm.
 * User: skymei
 * Date: 2018/10/23
 * Time: 11:39
 */
echo 'start  '.date('Y-m-d H:i:s').PHP_EOL;

$j = 0;
demo();
demo();
demo();
echo $j.PHP_EOL;

echo 'stop   '.date('Y-m-d H:i:s').PHP_EOL;

function demo()
{
    global $j;
    for ($i = 0; $i <100000000; $i++){$j += $i;}
}