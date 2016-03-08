<?php
$url = "http://www.pixiv.net/member_illust.php?mode=medium&illust_id=55669436";
dlImg($url);
function dlImg($url) {
    $fullPath = 'python3 ./imgdl.py ' . $url;
    exec($fullPath, $outpara);
    var_dump($outpara);
    foreach($outpara as $url) {
        echo $url."\n";
    }
    return $outpara;
}

?>
