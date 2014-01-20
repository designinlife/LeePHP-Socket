<?php
namespace LeePHP\Core;

/**
 * 应用程序对象类。(包含框架本身的基础信息等。)
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class Application extends Base {
    /**
     * 框架名称。
     *
     * @var string
     */
    public $name = 'LeePHP Web Application Framework';

    /**
     * 框架作者。
     *
     * @var string
     */
    public $author = 'Lei Lee';

    /**
     * 框架版本号。
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Swoole 扩展版本号。
     *
     * @var string
     */
    public $swoole_version = '';

    /**
     * 构造函数。
     * 
     * @param \LeePHP\Bootstrap $ctx 指定 Bootstrap 上下文对象实例引用。
     */
    function __construct($ctx) {
        parent::__construct($ctx);

        $this->swoole_version = swoole_version();
    }

    /**
     * 退出应用程序。
     * 
     * @param int $exit_code 指定退出代码。
     */
    static function bye($exit_code = 0) {
        exit($exit_code);
    }
}
