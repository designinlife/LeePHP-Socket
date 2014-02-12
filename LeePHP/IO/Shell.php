<?php
namespace LeePHP\IO;

use LeePHP\Base\Base;
use LeePHP\Entity\ShellResult;
use LeePHP\PermissionException;
use LeePHP\NetworkException;

/**
 * Shell 命令行工具辅助类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class Shell extends Base {
    /**
     * Linux Shell 会话对象。
     *
     * @var resource
     */
    private $shell_session;

    /**
     * Shell 连接状态标识。
     *
     * @var boolean
     */
    private $shell_ok = false;

    /**
     * 析构函数。
     */
    function __destruct() {
        parent::__destruct();

        $this->shell_ok      = false;
        $this->shell_session = NULL;
    }

    /**
     * 连接远程 Shell 主机。
     * 
     * @param string $host 指定主机地址。
     * @param int $port    指定端口。
     * @param string $user 指定登录名称。
     * @param string $pass 指定密码。
     * @throws PermissionException
     * @throws NetworkException
     */
    function connect($host, $port, $user, $pass) {
        $this->shell_session = ssh2_connect($host, $port);

        if ($this->shell_session) {
            $ok = ssh2_auth_password($this->shell_session, $user, $pass);

            if (!$ok)
                throw new PermissionException('远程 Shell 登录失败!', -1);

            $this->shell_ok = true;
        } else
            throw new NetworkException('连接到远程 Shell 主机失败!', -1);
    }

    /**
     * 执行远程命令。
     * 
     * @param string $command 指定命令字符串。
     * @return resource|boolean
     * @throws NetworkException
     */
    function execute($command) {
        if (!$this->shell_ok)
            throw new NetworkException('尚未连接远程主机。', -1);

        $r = ssh2_exec($this->shell_session, $command);

        return $r;
    }

    /**
     * 执行 Shell 命令并返回结果。
     * 
     * @param string|array $command 指定 Shell 命令语法。
     * @return ShellResult
     */
    static function exec($command) {
        $output     = NULL;
        $return_var = false;
        $cmd_s      = NULL;

        if (is_array($command))
            $cmd_s = implode('; ', $command);
        elseif (is_string($command))
            $cmd_s = $command;

        $last_line = exec($cmd_s, $output, $return_var);

        $r = new ShellResult();

        $r->exit_code = $return_var;
        $r->lines     = $output;
        $r->last_line = $last_line;

        return $r;
    }
}
