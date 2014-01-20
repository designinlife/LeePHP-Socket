<?php
$clients = array();

for ($i = 0; $i < 10; $i++) {
    $clients[$i] = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //异步非阻塞
    $clients[$i]->connect('127.0.0.1', 9501, 0.5);
    $ok          = $clients[$i]->send("hello, ID = " . $i . "!");
    var_dump($ok);
}

for ($i = 0; $i < 10; $i++) {
    $clients[$i]->close();
}