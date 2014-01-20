<?php
namespace LeePHP\Interfaces;

use LeePHP\Interfaces\IController;

/**
 * IProcessor 命令行应用程序接口。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IProcess extends IController {
    /**
     * 运行 CLI 应用程序。
     */
    function start();
}
