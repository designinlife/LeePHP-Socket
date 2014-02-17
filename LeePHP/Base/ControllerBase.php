<?php
namespace LeePHP\Base;

use LeePHP\Interfaces\ISwoole;
use LeePHP\Interfaces\IController;
use LeePHP\Interfaces\IAsyncTask;
use LeePHP\Base\Base;
use LeePHP\Bootstrap;
use LeePHP\Protocol\DataParser;
use LeePHP\ArgumentException;

/**
 * 控制器基类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class ControllerBase extends Base implements IController {
    /**
     * ISwoole 实例。
     *
     * @var ISwoole
     */
    protected $serv;

    /**
     * 当前客户端文件描述符。
     *
     * @var int
     */
    protected $fd = 0;

    /**
     * 当前客户端信息。
     *
     * @var array
     */
    protected $client_info;

    /**
     * 当前控制器命令项。
     *
     * @var array
     */
    protected $cmd_data;

    /**
     * 构造函数。
     * 
     * @param Bootstrap $ctx     指定上下文对象。
     * @param ISwoole $serv      指定 Swoole 服务实例。
     * @param int $fd            指定客户端文件描述符。
     * @param array $client_info 指定客户端来源信息。
     * @param array $cmd_data    指定当前命令数据。
     */
    function __construct($ctx, $serv, $fd, $client_info, &$cmd_data) {
        parent::__construct($ctx);

        $this->serv        = $serv;
        $this->fd          = $fd;
        $this->cmd_data    = $cmd_data;
        $this->client_info = $client_info;
    }

    /**
     * 析构函数。
     */
    function __destruct() {
        parent::__destruct();

        unset($this->serv, $this->cmd_data);
    }

    /**
     * 初始化事件。
     */
    function initialize() {
        
    }

    /**
     * 内存释放。
     */
    function dispose() {
        
    }

    /**
     * 添加 IAsyncTask 异步任务。
     * 
     * @param IAsyncTask $task 指定异步任务 IAsyncTask 对象。
     * @return int             返回任务ID。
     */
    protected function addTask(IAsyncTask $task) {
        $task_id = $this->serv->task(serialize($task));

        return $task_id;
    }

    /**
     * 全服广播消息。
     * 
     * @param array|string $data    指定广播消息字符串或数组。
     * @param int $ci               指定模块编号。
     * @param int $ct               指定协议下行编号。
     * @param boolean $contain_self 指示是否包含自己？(默认值: True)
     * @throws ArgumentException
     */
    protected function broadcast($data, $ci = 0, $ct = 0, $contain_self = true) {
        if (!is_string($data)) {
            if (0 == $ci || 0 == $ct) {
                throw new ArgumentException('必须指定 $ci, $ct 参数!', -1);
            }

            $data_s = DataParser::std($ci, $ct, $data);
        } else {
            $data_s = &$data;
        }

        $start_fd = 0;
        while (true) {
            $conn_list = $this->serv->connection_list($start_fd, 10);
            if ($conn_list === false) {
                break;
            }
            $start_fd = end($conn_list);

            foreach ($conn_list as $fd) {
                if ($contain_self || (false === $contain_self && $fd != $this->fd))
                    $this->serv->send($fd, $data_s);
            }
        }
    }

    /**
     * 发送数据给客户端。
     * 
     * @param array $data 指定数据集合。
     * @param int $ci     指定模块编号。
     * @param int $ct     指定协议下行编号。
     */
    protected function send($data, $ci = 0, $ct = 0) {
        if (0 == $ci)
            $ci = $this->cmd_data[2];
        if (0 == $ct)
            $ct = $this->cmd_data[3];

        $this->serv->send($this->fd, DataParser::std($ci, $ct, $data));
    }

    /**
     * 发送错误信息给客户端。
     * 
     * @param int $errno
     * @param string $errstr
     */
    protected function error($errno, $errstr) {
        $this->serv->send($this->fd, DataParser::std($this->cmd_data[2], $this->cmd_data[3], NULL, $errno, $errstr));
    }
}
