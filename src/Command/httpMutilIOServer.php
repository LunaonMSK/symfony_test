<?php
/**
 * Created by PhpStorm.
 * User: skymei
 * Date: 2018/12/20
 * Time: 16:07
 */

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class httpMutilIOServer extends Command
{
    public function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('MutilIOserver:start')
            // the short description shown while running "php bin/console list"
            ->setDescription('start a simple http server.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('start a simple http server');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);
        // create socket server
        $socketServer = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socketServer === false) {
            $output->writeln('create socket server fail : ' . socket_strerror(socket_last_error()));
            die();
        }
        //!!!!! important  the timeout setting
        socket_set_option($socketServer, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
        socket_set_option($socketServer, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 3, "usec" => 0));

        // bind ip and port for server
        if (!socket_bind($socketServer, '127.0.0.1', 8081)) {
            $output->writeln('bind socket server fail : ' . socket_strerror(socket_last_error()));
            die();
        }
        // how many clients the server can listen
        if (!socket_listen($socketServer, 32)) {
            $output->writeln('listen socket client fail : ' . socket_strerror(socket_last_error()));
            die();
        }
        // continuously to handle the client's request
        $readSocket = [];  // the connection source which can be read
        $writeSocket = []; // the connection source which can be wrote
        $expectSocket = NULL;  // the connection source which will not be processed

        $readSocket[] = $socketServer;
        while (true) {
            $tmpReads = $readSocket;
            $tmpWrites = $writeSocket;

            if(false === socket_select($tmpReads, $tmpWrites, $expectSocket, Null)){//Asynchronous detection
                $output->writeln('select socket client fail : ' . socket_strerror(socket_last_error()));
                die();
            }

            foreach ($tmpReads as $read) {  // handle the source which can read
                if ($read == $socketServer) { // main server connection
                    $clientConnect = socket_accept($socketServer);

                    if ($clientConnect) {
                        socket_getpeername($clientConnect, $addr, $port);
                        $output->writeln("client $addr connect with port $port..");
                        $readSocket[] = $clientConnect;
                        $writeSocket[] = $clientConnect;
                    }
                } else {
                    $data = socket_read($read, 1024);
                    socket_getpeername($read, $addr, $port);

                    if(empty($data)){
                        unset($readSocket[array_search($read, $readSocket)]);
                        unset($writeSocket[array_search($read, $writeSocket)]);

                        socket_close($read);
                        $output->writeln("client $addr:$port disconnect !");
                    }else{
                        $output->writeln("receive data from client $addr:$port : " . $data);
                        $data = strtoupper($data);

                        if(in_array($read, $tmpWrites)){
                            socket_write($read , $data);
                        }
                    }
                }
            }
        }


        socket_close($socketServer);  // stop socket server
    }
}