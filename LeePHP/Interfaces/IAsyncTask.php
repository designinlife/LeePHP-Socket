<?php
namespace LeePHP\Interfaces;

/**
 * IAsyncTask 异步任务接口。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IAsyncTask extends \Serializable {
    /**
     * 执行任务。
     */
    function execute();
}