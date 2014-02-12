<?php
/*
 * This file is part of the GetOptionKit package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace LeePHP\OptionKit;

use LeePHP\OptionKit\OptionSpecCollection;
use LeePHP\OptionKit\ContinuousOptionParser;

class ContinuousOptionKit extends GetOptionKit {

    function __construct() {
        $this->specs  = new OptionSpecCollection;
        $this->parser = new ContinuousOptionParser($this->specs);
    }

    function parse($argv) {
        return $this->parser->parse($argv);
    }

    function isEnd() {
        return $this->parser->isEnd();
    }

    function continueParse() {
        return $this->parser->continueParse();
    }
}
