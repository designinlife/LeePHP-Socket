<?php
namespace LeePHP\Interfaces;

/**
 * IProtocol 协议接口。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IProtocol {

    /**
     * Server启动在主进程的主线程回调此函数。
     * 
     * @param resource $sw
     */
    function onStart($sw);

    /**
     * 此事件在Server结束时发生。
     * 
     * @param resource $sw
     */
    function onShutdown($sw);

    /**
     * 有新的连接进入时，在worker进程中回调。
     * 
     * @param resource $sw
     * @param int $fd
     * @param int $from_id
     */
    function onConnect($sw, $fd, $from_id);

    /**
     * 连接关闭时在worker进程中回调。
     * 
     * @param resource $sw
     * @param int $fd
     * @param int $from_id
     */
    function onClose($sw, $fd, $from_id);

    /**
     * 此事件在worker进程启动时发生。这里创建的对象可以在worker进程生命周期内使用。
     * 
     * @param resource $sw
     * @param int $worker_id
     */
    function onWorkerStart($sw, $worker_id);

    /**
     * 此事件在worker进程终止时发生。在此函数中可以回收worker进程申请的各类资源。
     * 
     * @param resource $sw
     * @param int $worker_id
     */
    function onWorkerStop($sw, $worker_id);

    /**
     * 当连接被关闭时，回调此函数。与onConnect相同。onMasterConnect/onMasterClose都是在主进程中执行的。
     * 
     * @param resource $sw
     * @param int $fd
     * @param int $from_id
     */
    function onMasterConnect($sw, $fd, $from_id);

    /**
     * 当连接被关闭时，回调此函数。与onClose相同。onMasterConnect/onMasterClose都是在主进程中执行的。
     * 
     * @param resource $sw
     * @param int $fd
     * @param int $from_id
     */
    function onMasterClose($sw, $fd, $from_id);

    /**
     * 接收到数据时回调此函数，发生在worker进程中。
     * 
     * @param resource $sw
     * @param int $fd
     * @param int $from_id
     * @param string $data
     */
    function onReceive($sw, $fd, $from_id, $data);

    /**
     * 定时器触发。
     * 
     * @param resource $sw
     * @param int $interval
     */
    function onTimer($sw, $interval);

    /**
     * 在 task_worker 进程内被调用。worker 进程可以使用 swoole_server_task 函数向 task_worker 进程投递新的任务。
     * 
     * @param resource $sw
     * @param int $task_id
     * @param int $from_id
     * @param string $data
     */
    function onTask($sw, $task_id, $from_id, $data);

    /**
     * 当 worker 进程投递的任务在 task_worker 中完成时, task_worker 会通过 swoole_server_finish 函数将任务处理的结果发送给 worker 进程。
     * 
     * @param resource $sw
     * @param int $task_id
     * @param string $data
     */
    function onFinish($sw, $task_id, $data);
}
