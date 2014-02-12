<?php
namespace Application\Process;

use LeePHP\Interfaces\IProcess;
use LeePHP\Base\ProcessBase;
use LeePHP\Net\HTTP;
use LeePHP\Entity\HTTPOption;
use LeePHP\Entity\HTTPResponseMessage;
use LeePHP\NetworkException;

class Demo extends ProcessBase implements IProcess {

    /**
     * 预初始化事件。(注: 此方法在 initialize() 之前调用)
     */
    function onPreInitialize() {
        $this->option->add('f|file:', '指定执行文件路径。', 'file');
    }

    /**
     * 运行 CLI 应用程序。
     */
    function start() {
        $option = new HTTPOption();
        // $option->setEncodeCookies(true);
        // $option->setCookies($this->cookies);
        $option->setAcceptMimeType('text/html');
        
        $http = HTTP::instance($this->ctx);
        $a = $http->downloadString('http://www.9abbs.com/forum.php?mod=viewthread&tid=471406&extra=page%3D1%26filter%3Dtypeid%26typeid%3D31%26typeid%3D31', $option);
        
//        try {
//            $obj = new PHPMailer();
//
//            $obj->SMTPDebug = 0;
//            $obj->isSMTP();
//            $obj->setFrom('wuxuexuan@126.com', '小石头');
//            $obj->addAddress('13871213292@163.com', '李磊');
//            $obj->SMTPAuth  = true;
//            $obj->Host      = 'smtp.126.com';
//            $obj->Port      = 25;
//            $obj->Username  = 'wuxuexuan@126.com';
//            $obj->Password  = 'tVsk2a4v';
//            $obj->Subject   = 'SMTP 测试邮件!';
//            $obj->Body      = '来自 PHPMailer 的测试邮件！';
//            $obj->CharSet   = 'UTF-8';
//
//            if ($obj->send())
//                Console::info('发送成功!');
//            else
//                Console::error('发送失败!');
//        } catch (Exception $ex) {
//            Console::error($ex->getMessage() . PHP_EOL . $ex->getTraceAsString());
//        }
    }
}
