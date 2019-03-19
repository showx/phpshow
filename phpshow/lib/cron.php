<?php
/**
 * Created by PhpStorm.
 * User: pengyongsheng
 * Date: 2019/1/17
 * Time: 4:02 PM
 */

namespace phpshow\lib;


class cron
{
    public $config = [
        '* */2 * * *' => 'a.php',
        '* * * * *' => 'a.php',
        '* * * * *' => 'a.php',
    ];
}