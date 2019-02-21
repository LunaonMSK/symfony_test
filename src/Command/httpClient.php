<?php
/**
 * Created by PhpStorm.
 * User: skymei
 * Date: 2018/12/20
 * Time: 15:09
 */

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class httpClient extends Command
{
    public function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('client:start')
            // the short description shown while running "php bin/console list"
            ->setDescription('start a simple http client.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('start a simple http client');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);
        $serverHost = '127.0.0.1';
        $serverPort = 8081;
        // create socket client
        $socketClient = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socketClient === false) {
            $output->writeln('create socket client fail : ' . socket_strerror(socket_last_error()));
            die();
        }
        //!!!!! important  the timeout setting
        socket_set_option($socketClient,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>1, "usec"=>0 ) );
        socket_set_option($socketClient,SOL_SOCKET,SO_SNDTIMEO,array("sec"=>3, "usec"=>0 ) );

        // create socket server connection
        $connection = socket_connect($socketClient, $serverHost, $serverPort);
        if ($connection === false) {
            $output->writeln('connect socket server fail : ' . socket_strerror(socket_last_error()));
            die();
        }
        // send data to socket server
        $res = socket_write($socketClient , 'Hello World!');
        if ($res === false) {
            $output->writeln('connect socket server fail : ' . socket_strerror(socket_last_error()));
            die();
        }
        // receive data from socket server
        while (true){
            $data = socket_read($socketClient, 1024);
            if(!empty($data)){
                $output->writeln("responce is $data");
            }else{
                break;
            }
            sleep(10);
        }
        $output->writeln("disconnect from server");
        socket_close($socketClient);
    }
}