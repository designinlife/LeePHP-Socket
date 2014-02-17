<?php
namespace LeePHP\Interfaces;

/**
 * Swoole 扩展接口定义。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface ISwoole {
    /**
     * 设置运行时参数。
     * 
     * @param array $args
     */
    function set($args);

    /**
     * 增加服务监听端口。
     * 
     * @param string $host
     * @param int $port
     * @param int $sock_type
     */
    function addlistener($host, $port, $sock_type);

    /**
     * 设置定时器。
     * 
     * @param int $interval
     */
    function addtimer($interval);
    
    /**
     * 添加异步任务。
     * 
     * @param string $data
     */
    function task($data);
    
    /**
     * 此函数用于在task_worker进程中通知worker进程，投递的任务已完成。
     * 
     * @param string $data
     */
    function finish($data);

    /**
     * 启动服务。
     */
    function start();

    /**
     * 重启所有工作进程。
     */
    function reload();
    
    /**
     * 关闭服务器。(此方法可在 Worker 进程中调用)
     */
    function shutdown();
    
    /**
     * 关闭客户端连接。
     * 
     * @param int $fd
     * @param int $from_id
     */
    function close($fd, $from_id = 0);
    
    /**
     * 向客户端发送数据。
     * 
     * @param int $fd
     * @param string $data
     * @param int $from_id
     */
    function send(int $fd, string $data, int $from_id = 0);
    
    /**
     * 事件回调注册。
     * 
     * @param string $event
     * @param callable $callback
     */
    function on($event, $callback);
    
    /**
     * 获取连接信息。
     * 
     * @param int $fd
     * @return array|boolean
     */
    function connection_info($fd);
    
    /**
     * 获取客户端列表。
     * 
     * @param int $start_fd
     * @param int $page_size
     */
    function connection_list($start_fd = 0, $page_size = 10);
}
