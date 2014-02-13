<?php
namespace Application\Controller;

use LeePHP\Base\ControllerBase;

/**
 * 读取系统 API 列表。
 *
 * @author Lei Lee <web.developer.network@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2013, Lei Lee
 */
class ApiList extends ControllerBase {

    /**
     * 返回所有 API 接口列表。
     */
    function all() {
        $api = array(
            1 => '玩家基本数据',
            2 => '游戏系统设置'
        );

        $cmds = array();
        foreach ($this->ctx->cmds as $key => $value) {
            $i = $value[2];

            $cmds[$i][] = array(
                'controller' => $value[0],
                'method'     => $value[1],
                'module'     => $value[2],
                'up'         => $key,
                'down'       => $value[3],
                'auth_mode'  => $value[4],
                'label'      => $value[5],
                'params'     => $value[6]
            );
        }

        $dr = array();

        foreach ($api as $key => $value) {
            $dr[$key]['label'] = $value;
            $dr[$key]['items'] = $cmds[$key];
        }

        $this->send($dr);
    }
}
