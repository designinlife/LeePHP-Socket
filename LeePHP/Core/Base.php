<?php
namespace LeePHP\Core;

use LeePHP\Bootstrap;

/**
 * Application 基类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class Base {
    /**
     * Bootstrap 上下文对象实例变量。
     *
     * @var Bootstrap
     */
    protected $ctx = NULL;

    /**
     * 构造函数。
     * 
     * @param Bootstrap $ctx 指定 Bootstrap 上下文对象实例引用。
     */
    function __construct($ctx) {
        $this->ctx = $ctx;
    }

    /**
     * 析构函数。
     */
    function __destruct() {
        unset($this->ctx);
    }
}