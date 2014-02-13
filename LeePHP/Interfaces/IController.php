<?php
namespace LeePHP\Interfaces;

use LeePHP\Interfaces\IDisposable;
use LeePHP\Interfaces\ISwoole;

/**
 * Application 控制器接口。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IController extends IDisposable {
    /**
     * 构造函数。
     * 
     * @param Bootstrap $ctx  指定上下文对象。
     * @param ISwoole $serv   指定 Swoole 服务实例。
     * @param int $fd         指定客户端文件描述符。
     * @param array $cmd_data 指定当前命令数据。
     */
    function __construct($ctx, $serv, $fd, &$cmd_data);

    /**
     * 初始化事件。
     */
    function initialize();
}
