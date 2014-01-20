<?php
namespace LeePHP\Interfaces;

use LeePHP\Interfaces\IDisposable;

/**
 * 进程信号回调处理对象接口。<br>
 * 注: 使用命令行多进程模式必须实现此接口。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0
 */
interface IProcessHandler extends IDisposable {
    /**
     * 子进程任务处理函数。
     * 
     * @param mixed $data 传入参数对象。
     */
    function doWork(&$data);

    /**
     * 进程信号状态控制回调函数。
     * 
     * @param int $pid        进程 ID。
     * @param int $status     进程状态标识码。
     * @param string $message 消息描述文字。
     */
    function onSignalHandler($pid, $status, $message);
}
