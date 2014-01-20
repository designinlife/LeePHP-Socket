<?php
namespace LeePHP\Interfaces;

/**
 * IDisposable 接口。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IDisposable {
    /**
     * 内存释放。
     */
    function dispose();
}