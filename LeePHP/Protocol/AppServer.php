<?php
namespace LeePHP\Protocol;

use LeePHP\Interfaces\IProtocol;
use LeePHP\Interfaces\ISwoole;
use LeePHP\Interfaces\IController;
use LeePHP\Interfaces\IAsyncTask;
use LeePHP\Base\ServerBase;
use LeePHP\Utility\Console;

/**
 * Socket 应用程序服务事件处理类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class AppServer extends ServerBase implements IProtocol {

    /**
     * Server启动在主进程的主线程回调此函数。
     * 
     * @param ISwoole $sw
     */
    function onStart($sw) {
        Console::info('Swoole 内核版本: ', $this->ctx->app->core_version, ', LeePHP Socket 框架版本: ', $this->ctx->app->version);
        Console::info(str_repeat('-', 120));
        Console::info('Application 服务「主进程」已于 ' . date('Y-m-d H:i:s', $this->ctx->timestamp) . ' 启动。(' . $this->ctx->getListener() . ')');

        $listeners = $this->ctx->getListeners();

        if (!empty($listeners)) {
            foreach ($listeners as $k => $v)
                Console::info('Service Port [', $k + 1, '] -> ', $v[0], ':', $v[1]);
        }
    }

    /**
     * 此事件在Server结束时发生。
     * 
     * @param ISwoole $sw
     */
    function onShutdown($sw) {
        
    }

    /**
     * 有新的连接进入时，在worker进程中回调。
     * 
     * @param ISwoole $sw
     * @param int $fd
     * @param int $from_id
     */
    function onConnect($sw, $fd, $from_id) {
        Console::info('发现新的客户端连接: $fd = ', $fd, ', $from_id = ', $from_id);
    }

    /**
     * 连接关闭时在worker进程中回调。
     * 
     * @param ISwoole $sw
     * @param int $fd
     * @param int $from_id
     */
    function onClose($sw, $fd, $from_id) {
        Console::info('客户端连接已断开: $fd = ', $fd, ', $from_id = ', $from_id);
    }

    /**
     * 此事件在worker进程启动时发生。这里创建的对象可以在worker进程生命周期内使用。
     * 
     * @param ISwoole $sw
     * @param int $worker_id
     */
    function onWorkerStart($sw, $worker_id) {
        $this->ctx->pid = getmypid();

        $user = posix_getpwnam($this->ctx->cfgs['default']['owner']['user']);

        posix_setuid($user['uid']);
        posix_setgid($user['gid']);

        $this->worker_id = $worker_id;
    }

    /**
     * 此事件在 Worker 进程终止时发生。在此函数中可以回收worker进程申请的各类资源。
     * 
     * @param ISwoole $sw
     * @param int $worker_id
     */
    function onWorkerStop($sw, $worker_id) {
        Console::info('工作进程(PID: ' . getmypid() . ')已停止: $worker_id = ', $worker_id);
    }

    /**
     * 当连接被关闭时，回调此函数。与onConnect相同。onMasterConnect/onMasterClose都是在主进程中执行的。
     * 
     * @param ISwoole $sw
     * @param int $fd
     * @param int $from_id
     */
    function onMasterConnect($sw, $fd, $from_id) {
        
    }

    /**
     * 当连接被关闭时，回调此函数。与onClose相同。onMasterConnect/onMasterClose都是在主进程中执行的。
     * 
     * @param ISwoole $sw
     * @param int $fd
     * @param int $from_id
     */
    function onMasterClose($sw, $fd, $from_id) {
        
    }

    /**
     * 接收到数据时回调此函数，发生在worker进程中。
     * 
     * @param ISwoole $sw
     * @param int $fd
     * @param int $from_id
     * @param string $data
     */
    function onReceive($sw, $fd, $from_id, $data) {
        // 读取客户端来源信息 ...
        $client_info = $sw->connection_info($fd);

        Console::debug('[接收数据] ', $data);

        // 解析客户端数据协议 ...
        $data_s = DataParser::decode($data);

        Console::debug('[OnReceive][Client IP: ', $client_info['remote_ip'], ', From: ', $client_info['from_port'], '] ', $data_s);

        if (!isset($this->ctx->cmds[$data_s['cmd']])) {
            Console::error('无效的命令编号(' . $data_s['cmd'] . ')。');
            return false;
        }

        // 实例化 IController 控制器对象并执行命令方法 ...
        $cls_n = $this->ctx->getControllerNs() . '\\' . $this->ctx->cmds[$data_s['cmd']][0];
        $cls_m = $this->ctx->cmds[$data_s['cmd']][1];
        $cls_o = new $cls_n($this->ctx, $sw, $fd, $client_info, $this->ctx->cmds[$data_s['cmd']]);

        if ($cls_o instanceof IController) {
            $cls_o->initialize();
            $cls_o->$cls_m($data_s);
            $cls_o->dispose();
        }

        $cls_o = NULL;
    }

    /**
     * 定时器触发。
     * 
     * @param ISwoole $sw
     * @param int $interval
     */
    function onTimer($sw, $interval) {
        
    }

    /**
     * 在 task_worker 进程内被调用。worker 进程可以使用 swoole_server_task 函数向 task_worker 进程投递新的任务。
     * 
     * @param ISwoole $sw
     * @param int $task_id
     * @param int $from_id
     * @param string $data
     */
    function onTask($sw, $task_id, $from_id, $data) {
        $task = unserialize($data);

        if ($task instanceof IAsyncTask) {
            $task->execute();
        }
    }

    /**
     * 当 worker 进程投递的任务在 task_worker 中完成时, task_worker 会通过 swoole_server_finish 函数将任务处理的结果发送给 worker 进程。
     * 
     * @param ISwoole $sw
     * @param int $task_id
     * @param string $data
     */
    function onFinish($sw, $task_id, $data) {
        Console::info('Swoole::onFinish() 事件被调用。');
    }
}
