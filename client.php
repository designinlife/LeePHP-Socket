<?php
//$clients = array();
//
//for ($i = 0; $i < 10; $i++) {
//    $clients[$i] = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //异步非阻塞
//    $clients[$i]->connect('127.0.0.1', 9501, 0.5);
//    $ok          = $clients[$i]->send("hello, ID = " . $i . "!");
//    var_dump($ok);
//}
//
//for ($i = 0; $i < 10; $i++) {
//    $clients[$i]->close();
//}
//$clients = array();
//for ($i = 0; $i < 2; $i++) {
//    $client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //同步阻塞
//    $ret    = $client->connect('127.0.0.1', 9501, 0.5, 0);
//    if (!$ret) {
//        echo "Connect Server fail.errCode=" . $client->errCode;
//    } else {
//        $client->send("HELLO WORLD\n");
//        $clients[$client->sock] = $client;
//    }
//}
//
//while (!empty($clients)) {
//    $write = $error = array();
//    $read  = array_values($clients);
//    $n     = swoole_client_select($read, $write, $error, 0.6);
//    if ($n > 0) {
//        foreach ($read as $index => $c) {
//            echo "Recv #{$c->sock}: " . $c->recv() . "\n";
//            unset($clients[$c->sock]);
//        }
//    }
//}

$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC); //异步非阻塞

$client->on("connect", function(swoole_client $cli) {
    $cli->send("GET / HTTP/1.1\r\n\r\n");
});

$client->on("receive", function(swoole_client $cli, $data) {
    echo "Receive: $data";
    sleep(1);
    $cli->send("GET / HTTP/1.1\r\n\r\n");
});

$client->on("error", function(swoole_client $cli) {
    exit("error\n");
});

$client->on("close", function(swoole_client $cli) {
    echo "Connection close";
});

$client->connect('127.0.0.1', 9501, 0.5);

echo "connect to 127.0.0.1:9501";
