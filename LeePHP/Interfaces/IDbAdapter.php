<?php
namespace LeePHP\Interfaces;

use LeePHP\Interfaces\IDisposable;

/**
 * IDbAdapter 接口定义。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IDbAdapter extends IDisposable {
    /**
     * 执行 Adapter 对象并返回结果集。
     * 
     * @return array
     */
    function execute();
}
