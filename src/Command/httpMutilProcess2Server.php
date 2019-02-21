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

class httpMutilProcess2Server extends Command
{
    public function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('Mutilserver2:start')
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
        while(true){
            for ($i = 0; $i < 2; $i++) {
                if (pcntl_fork() == 0) { //重点在这，fork子进程来处理！
                    while(true){
                        $clientConnect = socket_accept($socketServer);  // connect the client
                        if ($clientConnect) {
                            socket_getpeername($clientConnect, $addr, $port);
                            $output->writeln("client $addr connect with port $port..");
                            while(true){
                                $data = socket_read($clientConnect, 1024);  // read data from client(1024 bytes one time)

                                if (!empty($data)) {
                                    $output->writeln('receive data from client : ' . $data);
                                    $data = strtoupper($data);  // handle the request data
                                    socket_write($clientConnect, $data);  // respond the client
                                } else {
                                    $output->writeln("client $addr:$port disconnect !");
                                    socket_close($clientConnect);   // close the connection
                                    break;
                                }
                            }
                        }
                        sleep(10);
                    }
                }
            }
            sleep(1000);
        }

        socket_close($socketServer);  // stop socket server
    }

}