<?php
namespace LeePHP\Base;

use LeePHP\Bootstrap;
use LeePHP\Interfaces\IDb;
use LeePHP\Interfaces\ITemplate;

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
     * IDb 数据管理对象。
     *
     * @var IDb
     */
    protected $db;

    /**
     * ITemplate 模版对象。
     *
     * @var ITemplate
     */
    protected $template;

    /**
     * 构造函数。
     * 
     * @param Bootstrap $ctx 指定 Bootstrap 上下文对象实例引用。
     */
    function __construct($ctx) {
        $this->ctx      = $ctx;
        $this->db       = $this->ctx->db;
        $this->template = $this->ctx->template;
    }

    /**
     * 析构函数。
     */
    function __destruct() {
        unset($this->db, $this->template, $this->ctx);
    }
}
