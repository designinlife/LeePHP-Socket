<?php
namespace LeePHP\Entity;

/**
 * Shell 命令执行结果对象。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class ShellResult {
    /**
     * 执行结果集合。
     *
     * @var array
     */
    public $lines;

    /**
     * 控制台输出的最后一行信息。
     *
     * @var string
     */
    public $last_line;

    /**
     * 程序退出代码。
     *
     * @var int
     */
    public $exit_code = 0;
}
