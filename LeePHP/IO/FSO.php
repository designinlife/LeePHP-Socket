<?php
namespace LeePHP\IO;

use LeePHP\ArgumentException;

/**
 * 文件系统对象管理类。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class FSO {
    /**
     * 创建目录。
     * 
     * @param string $dir 指定目录绝对路径。
     * @return boolean
     */
    static function mkdir($dir) {
        if (mkdir($dir, 0777, true))
            return true;

        return false;
    }

    /**
     * 创建文本或二进制文件。
     * 
     * @param string $file    指定文件路径。
     * @param string $content 指定文件内容。
     * @param boolean $binary 指示是否二进制方式创建？(默认值: False)
     * @return boolean
     */
    static function create($file, $content, $binary = false) {
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fp = fopen($file, 'w' . (!$binary? : 'b'));
        if ($fp) {
            fwrite($fp, $content);
            fclose($fp);

            return true;
        }

        return false;
    }

    /**
     * 复制文件或目录。
     * 
     * @param string $src 指定源文件路径。
     * @param string $dst 指定目标文件路径。
     * @return boolean
     * @throws ArgumentException
     */
    static function cp($src, $dst) {
        if (!is_file($src))
            throw new ArgumentException('第一个参数必须是一个有效的文件绝对路径。', -1);

        $dir = dirname($dst);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (copy($src, $dst))
            return true;

        return false;
    }

    /**
     * 删除文件或目录。
     * 
     * @param string $file_or_dir
     * @return boolean
     */
    static function rm($file_or_dir) {
        if (is_file($file_or_dir)) {
            unlink($file_or_dir);
            return true;
        } elseif (is_dir($file_or_dir)) {
            $lst = self::ls($file_or_dir);

            if (!empty($lst['files'])) {
                foreach ($lst['files'] as $v) {
                    if (is_file($v)) {
                        unlink($v);
                    }
                }
            }

            if (!empty($lst['dirs'])) {
                foreach ($lst['dirs'] as $v) {
                    if (is_dir($v)) {
                        rmdir($v);
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * 读取文件内容。
     * 
     * @param string $file 指定文件路径。
     * @return string|boolean
     */
    static function cat($file) {
        if (!is_file($file))
            return false;
        if (!is_readable($file))
            return false;

        return file_get_contents($file);
    }

    /**
     * 获取目录文件列表。
     * 
     * @param string $dir 指定目录路径。
     * @return array
     */
    static function ls($dir) {
        $d = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);

        $lst = array();

        foreach ($d as $c) {
            if ($c->isFile()) {
                $lst['files'][] = $c->getPathName();
            } elseif ($c->isDir()) {
                $lst['dirs'][] = $c->getPathName();
            }
        }

        return $lst;
    }
}
