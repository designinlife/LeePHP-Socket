<?php
namespace LeePHP\Base;

use LeePHP\Interfaces\IProcessHandler;
use LeePHP\Base\Base;
use LeePHP\C;
use LeePHP\Bootstrap;
use LeePHP\Utility\Console;
use LeePHP\System\Application;
use LeePHP\ArgumentException;
use LeePHP\RuntimeException;
use LeePHP\OptionKit\GetOptionKit;

declare(ticks = 1);

/**
 * 进程控制器基类。
 * 
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class ProcessBase extends Base {
    /**
     * 已启动的任务数量。
     *
     * @var boolean
     */
    protected $jobsStarted = 0;

    /**
     * 当前多进程任务队列。
     *
     * @var array
     */
    protected $jobs = array();

    /**
     * 进程信号队列。
     *
     * @var array
     */
    protected $signals = array();

    /**
     * 主进程 PID。
     *
     * @var int
     */
    protected $pid = 0;

    /**
     * GetOptionKit 对象实例。
     *
     * @var GetOptionKit
     */
    protected $option = NULL;

    /**
     * IProcessHandler 实例变量。
     *
     * @var IProcessHandler
     */
    private $handler = NULL;

    /**
     * 构造函数。
     * 
     * @param Bootstrap $scope 指定 Bootstrap 上下文对象。
     */
    function __construct(&$scope) {
        parent::__construct($scope);

        $this->option = new GetOptionKit();

        $this->pid = getmypid();
        pcntl_signal(SIGCHLD, array($this, "childSignalHandler"));
    }

    /**
     * 析构函数。
     */
    function __destruct() {
        parent::__destruct();

        unset($this->handler);
    }

    /**
     * 预初始化事件。(注: 此方法在 initialize() 之前调用)
     */
    function onPreInitialize() {
        
    }

    /**
     * 初始化事件。
     */
    function initialize() {
        $this->option->add('h|help', '显示命令帮助信息。', 'help');
        $this->option->parse($this->ctx->argv);

        if (true === $this->option->getValue('help')) {
            $this->option->printOptions();
            Application::bye(0);
        }
    }

    /**
     * 内存释放。
     */
    function dispose() {
        
    }

    /**
     * 等待工作子进程结束。
     */
    protected function waitfor() {
        while (count($this->jobs)) {
            sleep(1);
        }
    }

    /**
     * 进程信号处理函数。
     * 
     * @param int $signo  信号编号。
     * @param int $pid    进程编号。
     * @param int $status 进程状态。
     * @return boolean
     */
    function childSignalHandler($signo, $pid = NULL, $status = NULL) {
        if (!$pid) {
            $pid = pcntl_waitpid(-1, $status, WNOHANG);
        }

        while ($pid > 0) {
            if ($pid && isset($this->jobs[$pid])) {
                $exit_code = pcntl_wexitstatus($status);
                if ($exit_code != 0) {
                    $this->handler->onSignalHandler($pid, $status, '子进程已退出.(exit code = ' . $exit_code . ')');
                } else {
                    $this->handler->onSignalHandler($pid, $status, '子进程已正常退出.');
                }
                unset($this->jobs[$pid]);
            } else if ($pid) {
                $this->signals[$pid] = $status;
                $this->handler->onSignalHandler($pid, $status, '信号: ' . $signo . ', 子进程已启动.');
            }
            $pid = pcntl_waitpid(-1, $status, WNOHANG);
        }

        return true;
    }

    /**
     * 启动多进程工作任务。
     */
    protected function launch(&$data) {
        if (!$this->handler) {
            throw new ArgumentException('尚未设置 IProcessHandler 对象引用!', -1);
        }

        $pid = pcntl_fork();
        if ($pid == -1) {
            Console::error('Could not launch new job, exiting');
            return false;
        } else if ($pid) {
            $this->jobs[$pid] = $data;

            if (isset($this->signals[$pid])) {
                $this->childSignalHandler(SIGCHLD, $pid, $this->signals[$pid]);
                unset($this->signals[$pid]);
            }
        } else {
            $exit_code = 0;

            $this->ctx->pid = getmypid();

            // do something ...
            $this->handler->doWork($data);

            exit($exit_code);
        }
        return true;
    }

    /**
     * 设置 IProcessHandler 对象实例引用。
     * 
     * @param IProcessHandler $handler
     */
    protected function setProcessHandler($handler) {
        if (!($handler instanceof IProcessHandler))
            throw new RuntimeException('传入的实例引用必须实现 IProcessHandler 接口。', -1);

        $this->handler = $handler;
    }
}
