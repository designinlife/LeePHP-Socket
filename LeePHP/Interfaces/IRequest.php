<?php
namespace LeePHP\Interfaces;

use LeePHP\Entity\HTTPOption;
use LeePHP\Entity\HTTPResponseMessage;
use LeePHP\NetworkException;

/**
 * Uri 请求对象接口。
 * 
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
interface IRequest {
    /**
     * 发送远程请求。(注: 此方法仅发送请求, 并不返回响应结果.)
     * 
     * @param string $url        指定下载的内容地址。
     * @param HTTPOption $option 指定 HTTP 参数选项集合。
     */
    function download($url, $option);

    /**
     * 下载远程页面文本内容。
     * 
     * @param string $url        指定下载的内容地址。
     * @param HTTPOption $option 指定 HTTP 参数选项集合。
     * @return HTTPResponseMessage|boolean
     * @throws NetworkException
     */
    function downloadString($url, $option);

    /**
     * 下载远程地址内容并存储到本地文件。
     * 
     * @param string $url                  指定下载的内容地址。
     * @param HTTPOption $option           指定 HTTP 参数选项集合。
     * @param string $savedir              指定文件下载后保存的目录。(注: 绝对路径)
     * @param boolean $auto_hash           指示是否生成 Hash 子目录？(默认值: True)
     * @param int $hash_id                 指定生成 Hash 子目录的 ID 基数值。(默认值: 0 | 当此参数值大于 0 时, $auto_hash 参数才有效.)
     * @param string $filename             指定文件名称。(默认值: Null | 不包含扩展名; 当不传入此参数时, 系统自动生成唯一标识字符串作为文件名)
     * @return HTTPResponseMessage|boolean
     * @throws NetworkException
     */
    function downloadFile($url, $option, $savedir, $auto_hash = true, $hash_id = 0, $filename = NULL);
}
