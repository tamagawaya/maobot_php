<?php
require_once "settings.php";
require_once "Config.php";

function dlImg($url) {
    if (!file_exists('imgdl.ini')) {
        $conf  = array(
            'nicoseiga.jp' => array(
                'user' => NICOUSER,
                'pass' => NICOPASS
            ),
            'pixiv.net' => array(
                'user' => PIXIUSER,
                'pass' => PIXIPASS
            )
        );
        $config = new Config();
        $config->parseConfig($conf, 'phparray', array('name' => 'conf'));
        $config->writeConfig('imgdl.ini', 'inifile');
    }
    $fullPath = 'python3 ./imgdl.py "' . $url . '"';
    exec($fullPath, $outpara);
    return $outpara;
}

?>
