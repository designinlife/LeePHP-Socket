<?php
namespace LeePHP\Core;

use LeePHP\Core\Base;

/**
 * Application 服务基类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class ServerBase extends Base {
    /**
     * 工作进程 PID 列表。
     *
     * @var int
     */
    protected $worker_id = 0;
}
