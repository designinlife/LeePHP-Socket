<?php
namespace Application\Controller;

use LeePHP\Base\WebBase;

class Demo extends WebBase {

    function start() {
        $this->ctx->template->setCacheEnable(true);
        $this->ctx->template->setAutoReload(true);
        $this->ctx->template->assign('name', 'Li Lei');
        $this->ctx->template->display('demo.tpl');
    }

    function terminate($execute_ms) {
        echo '<br/>耗费时间: ', $execute_ms;
    }
}
