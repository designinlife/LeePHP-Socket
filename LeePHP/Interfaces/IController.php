<?php
namespace LeePHP\Interfaces;

use LeePHP\Interfaces\IDisposable;

/**
 * Application 控制器接口。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IController extends IDisposable {
    /**
     * 预初始化事件。(注: 此方法在 initialize() 之前调用)
     */
    function onPreInitialize();

    /**
     * 初始化事件。
     */
    function initialize();
}
