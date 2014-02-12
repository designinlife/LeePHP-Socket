<?php
namespace LeePHP\Net;

use LeePHP\Bootstrap;
use LeePHP\Base\Base;
use LeePHP\Interfaces\IRequest;
use LeePHP\Net\RequestFactory;
use LeePHP\Entity\HTTPOption;
use LeePHP\Entity\HTTPResponseMessage;
use LeePHP\NetworkException;
use LeePHP\ArgumentException;

/**
 * 基于 Pecl Http 扩展的 HTTP 请求管理类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0
 */
class HTTP extends Base implements IRequest {
    /**
     * HTTP 对象实例引用。
     *
     * @var HTTP
     */
    static private $instance = NULL;

    /**
     * 静态创建 HTTP (Singleton) 对象实例。
     * 
     * @param Bootstrap $scope 指定 Bootstrap 上下文对象。
     * @return HTTP
     */
    static function instance($scope) {
        if (!self::$instance)
            self::$instance = new HTTP($scope);

        return self::$instance;
    }

    /**
     * 发送远程请求。(注: 此方法仅发送请求, 并不返回响应结果.)
     * 
     * @param string $url
     * @param HTTPOption $option
     */
    function download($url, $option) {
        http_get($url, $option->getOptions());
    }

    /**
     * 下载远程页面文本内容。
     * 
     * @param string $url
     * @param HTTPOption $option
     * @return HTTPResponseMessage|boolean
     * @throws HTTPException
     */
    function downloadString($url, $option) {
        $info = NULL;

        $content = $this->_download($url, $option, $info);

        if ($content && $info) {
            $hrm = new HTTPResponseMessage($info['response_code']);

            if ($hrm->getStatusCode() != RequestFactory::S_OK) {
                return $hrm;
            }

            $c = http_parse_message($content);

            $content = NULL;

            if ($c) {
                if (!isset($c->headers['Content-Type'])) {
                    throw new NetworkException('服务器未返回有效的 MIME 类型。', RequestFactory::E_NOT_MIME_TYPE);
                }

                $hrm->setContentType($c->headers['Content-Type']);

                if (false == $option->isAcceptMimeType($hrm->getMimeType())) {
                    throw new NetworkException('不支持服务器返回的文档 MIME 类型。(' . $hrm->getMimeType() . ')', RequestFactory::E_NOT_ACCEPT_MIME_TYPE);
                }

                $hrm->setContent($c->body);
                $hrm->setLength($c->headers['Content-Length']);

                return $hrm;
            }
        }

        return false;
    }

    /**
     * 下载远程地址内容并存储到本地文件。
     * 
     * @param string $url        指定下载的内容地址。
     * @param HTTPOption $option 指定 HTTP 参数选项集合。
     * @param string $savedir    指定文件下载后保存的目录。(注: 绝对路径)
     * @param boolean $auto_hash 指示是否生成 Hash 子目录？(默认值: True)
     * @param int $hash_id       指定生成 Hash 子目录的 ID 基数值。(默认值: 0 | 当此参数值大于 0 时, $auto_hash 参数才有效.)
     * @param string $filename   指定文件名称。(默认值: Null | 不包含扩展名; 当不传入此参数时, 系统自动生成唯一标识字符串作为文件名)
     * @return HTTPResponseMessage|boolean
     * @throws HTTPException
     */
    function downloadFile($url, $option, $savedir, $auto_hash = true, $hash_id = 0, $filename = NULL) {
        $info = NULL;

        $content = $this->_download($url, $option, $info);

        if ($content && $info) {
            $hrm = new HTTPResponseMessage($info['response_code']);

            if ($hrm->getStatusCode() != RequestFactory::S_OK) {
                return $hrm;
            }

            $c = http_parse_message($content);

            $content = NULL;

            if ($c) {
                if (!isset($c->headers['Content-Type'])) {
                    throw new NetworkException('服务器未返回有效的 MIME 类型。', RequestFactory::E_NOT_MIME_TYPE);
                }

                $hrm->setContentType($c->headers['Content-Type']);

                if (false == $option->isAcceptMimeType($hrm->getMimeType())) {
                    throw new NetworkException('不支持服务器返回的文档 MIME 类型。(' . $hrm->getMimeType() . ')', RequestFactory::E_NOT_ACCEPT_MIME_TYPE);
                }

                $hrm->setContent($c->body);
                $hrm->setLength($c->headers['Content-Length']);
                $hrm->setAbsoluteDir(rtrim($savedir, DIRECTORY_SEPARATOR));

                $subdir = '';

                if ($auto_hash && is_int($hash_id) && $hash_id > 0)
                    $subdir = sprintf('%03d', $hash_id % 200);

                $hrm->setSubDirectory($subdir);

                if ($filename) {
                    $hrm->setFilenameBase($filename);
                } else {
                    $fnb = uniqid(getmypid(), true);

                    $hrm->setFilenameBase($fnb);
                }

                switch ($hrm->getMimeType()) {
                    case 'image/pjpeg':
                    case 'image/jpeg':
                    case 'image/jpg':
                        $hrm->setExtName('.jpg');
                        break;
                    case 'image/gif':
                        $hrm->setExtName('.gif');
                        break;
                    case 'image/png':
                        $hrm->setExtName('.png');
                        break;
                    case 'text/html':
                        $hrm->setExtName('.html');
                        break;
                    case 'text/plain':
                        $hrm->setExtName('.txt');
                        break;
                    default:
                        throw new NetworkException('不支持的 MIME 文件类型。(' . $hrm->getMimeType() . ')', RequestFactory::E_NOT_ACCEPT_MIME_TYPE);
                }

                $hrm->done();

                $dir = dirname($hrm->getAbsoluteFile());
                if (!is_dir($dir))
                    mkdir($dir, 0777, true);

                $fp = fopen($hrm->getAbsoluteFile(), 'w');
                if ($fp) {
                    fwrite($fp, $c->body);
                    fclose($fp);

                    return $hrm;
                }
            }
        }

        return false;
    }

    /**
     * 解析 Cookie 会话字符串。
     * 
     * @param string $cookie_str
     * @return array
     */
    function doParseCookie($cookie_str) {
        $c = http_parse_cookie($cookie_str);
        if ($c)
            return $c->cookies;

        return false;
    }

    /**
     * 从文本文件读取 Cookie 会话信息并解析。
     * 
     * @param string $cookie_file
     * @return array
     */
    function doParseCookieFile($cookie_file) {
        $fp = fopen($cookie_file, 'r');
        if ($fp) {
            $cookie_str = fread($fp, 8192);
            fclose($fp);

            $c = http_parse_cookie($cookie_str);
            if ($c)
                return $c->cookies;
        }

        return false;
    }

    /**
     * 下载远程 HTTP 请求内容。
     * 
     * @param string $url
     * @param HTTPOption $option
     * @param array $info
     * @return string
     * @throws ArgumentException
     */
    private function _download($url, $option, &$info) {
        if (!$option)
            throw new ArgumentException('必须指定 HTTPOption 参数对象。', -1);

        $content = \http_get($url, $option->getOptions(), $info);
        return $content;
    }
}
