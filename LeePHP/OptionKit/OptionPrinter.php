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

class OptionPrinter implements OptionPrinterInterface {
    public $specs;

    function __construct($specs) {
        $this->specs = $specs;
    }

    /**
     * Render option descriptions.
     * 
     * @param int $width
     * @return string
     */
    function outputOptions($width = 32) {
        # echo "* Available options:\n";
        $line_s = str_repeat('-', 120);

        $lines   = array();
        $lines[] = '* Available options:';
        $lines[] = $line_s;
        foreach ($this->specs->all() as $spec) {
            if ($spec instanceof OptionSpec) {
                $c1 = $spec->getReadableSpec();
                if (strlen($c1) > $width) {
                    $line = "\t" . ($spec->isAttributeRequire() ? '*    ' : '     ') . sprintf("%-{$width}s", $c1) . PHP_EOL . $spec->description;  # wrap text
                } else {
                    $line = "\t" . ($spec->isAttributeRequire() ? '*    ' : '     ') . sprintf("%-{$width}s   %s", $c1, $spec->description);
                }
                $lines[] = $line;
            }
        }

        $lines[] = $line_s;

        return $lines;
    }

    /**
     * Print options descriptions to stdout.
     */
    function printOptions() {
        $lines = $this->outputOptions();
        echo join(PHP_EOL, $lines), PHP_EOL;
    }
}
