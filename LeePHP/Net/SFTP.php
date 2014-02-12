<?php
namespace LeePHP\Net;

use LeePHP\NetworkException;
use LeePHP\PermissionException;
use LeePHP\IOException;
use \Exception;

/**
 * SFTP 协议管理对象。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class SFTP {
    private $sess;
    private $sftp;
    private $_host;
    private $_port;
    private $_user;
    private $_pass;

    /**
     * 构造函数。
     * 
     * @param string $host 指定 SSH 主机名称。
     * @param int $port    指定连接端口。
     * @param string $user 指定登录名称。
     * @param string $pass 指定登录密码。
     */
    function __construct($host, $port, $user, $pass) {
        $this->_host = $host;
        $this->_port = $port;
        $this->_user = $user;
        $this->_pass = $pass;
    }

    /**
     * 连接 SSH 主机。
     * 
     * @throws NetworkException
     */
    function connect() {
        $this->sess = ssh2_connect($this->_host, $this->_port);

        if (!$this->sess)
            throw new NetworkException('连接 SSH 服务器失败。');
    }

    /**
     * 远程 SSH 登录。
     * 
     * @throws PermissionException
     * @throws NetworkException
     */
    function login() {
        $ok = ssh2_auth_password($this->sess, $this->_user, $this->_pass);

        if (!$ok)
            throw new PermissionException('SSH 登录验证失败。');

        $this->sftp = ssh2_sftp($this->sess);

        if (!$this->sftp)
            throw new NetworkException('初始化 SFTP 对象实例失败。');
    }

    /**
     * 发送本地文件至远程服务器。
     * 
     * @param string $local_file  指定需要上传的本地文件路径。
     * @param string $remote_file 指定远程文件完整路径。(注: 远程路径目录必须存在。)
     * @param callable $callback  指定回调通知处理函数。(默认值: Null | * 带两个参数: [已发送的字节数], [总字节数])
     * @throws NetworkException
     * @throws Exception
     */
    function send($local_file, $remote_file, $callback = NULL) {
        $context = stream_context_create();

        $fp = fopen('ssh2.sftp://' . $this->_user . ':' . $this->_pass . '@' . $this->_host . ':' . $this->_port . $remote_file, 'w', false, $context);

        if (!$fp)
            throw new NetworkException('不能 fopen() 远程文件 ' . $remote_file);

        if ($callback) {
            // 采用断点续传方式 ...
            $fr = fopen($local_file, 'rb');
            if (!$fr)
                throw new IOException('打开本地文件 (' . $local_file . ') 出错!');

            $chunk       = 8192;
            $bytes_sent  = 0;
            $bytes_total = filesize($local_file);

            while (!feof($fr)) {
                $data_to_send = fread($fr, $chunk);
                if (fwrite($fp, $data_to_send)) {
                    $bytes_sent += strlen($data_to_send);

                    call_user_func_array($callback, array($bytes_sent, $bytes_total));
                }
            }
            fclose($fr);
        } else {
            $data_to_send = file_get_contents($local_file);
            if ($data_to_send === false)
                throw new Exception("Could not open local file: $local_file.");

            if (fwrite($fp, $data_to_send) === false)
                throw new Exception("Could not send data from file: $local_file.");
        }

        fclose($fp);
    }
}
