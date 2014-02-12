<?php
/**
 * LeePHP Socket 全局配置文件。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
$g_conf = array(
    # [Sys]
    # ---------------------------------------------------------
    'sys'    => array(
        'name'        => 'LeePHP Socket',
        'description' => 'An Socket Framework.',
        'version'     => '1.0.0'
    ),
    # [Swoole]
    # ---------------------------------------------------------
    'swoole' => array(
        'daemonize'         => 0,
        'dispatch_mode'     => 2,
        'timeout'           => 2.5,
        'poll_thread_num'   => 4,
        'writer_num'        => 2,
        'worker_num'        => 4,
        'backlog'           => 128,
        'open_cpu_affinity' => 1,
        'open_tcp_nodelay'  => 1,
        'open_eof_check'    => 1,
        'data_eof'          => "\r\n",
        'log_file'          => '/tmp/leephp-socket.log'
    ),
    # [Owner]
    # ---------------------------------------------------------
    'owner'  => array(
        'group' => 'www',
        'user'  => 'www'
    ),
    # [DB]
    # ---------------------------------------------------------
    'db'     => array(
        'default' => array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'pass' => '111111',
            'name' => 'socket_demo'
        )
    )
);
