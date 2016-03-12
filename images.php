<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset=utf-8 />
<meta name="description" content="irc images" />
<title>irc images</title>
<link rel="stylesheet" href="css/screen.css">
<link rel="stylesheet" href="css/lightbox.css">
<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/lightbox.js"></script>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/irc.css">
<!-- <link rel="stylesheet" href="common/base.css" /> -->
<!-- <link rel="shortcut icon" href="" -->
<!--[if IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>
<body>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
 <script src="js/bootstrap.min.js"></script>

<?php

include_once "irclib.php";

session_start();
session_regenerate_id(true);

$now_page = 1;
if (isset($_POST["page"])) {
    $now_page = $_POST["page"];
}
if ($now_page >= 11) {
    $ten_before = $now_page-10;
} else {
    $ten_before = 1;
}
echo "<ul>\n";
//page settings
echo "</ul>\n";

echo "<table>\n";
echo image_list($now_page);
echo "</table>\n";

echo page_list($now_page);

function image_list($page, $row=10, $column=5) {
    $res = selectImages($row*$column*($page-1), $row*$column*$page);
    $r = 1;
    $c = 1;
    echo "<tr>\n";
    foreach ($res as $image) {
        echo "<td>\n<div class='text-center'><a href={$image->loc} class='example-image-link' data-title=\"<a target='_blank' href={$image->orig}>{$image->orig}</a>\" data-lightbox='example-set'><img alt='Generic placeholder thumbnail' class='img-rounded img-responsive' src={$image->thum}></img></a></div>\n</td>\n";
        if ($r % $row == 0) {
            if ($c != $column) {
                $c++;
                $r = 0;
                echo "</tr>\n<tr>\n";
            } else {
                echo "</tr>\n";
            }
        }
        $r++;
    }
    echo "</tr>\n";
}

function page_list($now_page) {
    echo "<form accept-charset='UTF-8' action='images.php' method='POST'>\n";
    echo "<select id='page' name='page'>\n";
    $res = selectImagesCount();
    $count = $res[0]->{"count(id)"};
    $page_count = (int) ($count/50 + 1);
    for($page = 1; $page <= $page_count; $page++) {
        echo "<option ";
        if ($page == $now_page) {
            echo "selected ";
        }
        echo "value=".$page.">$page</option>\n";
    }
    echo "</select>\n";
    echo "<input class='btn' name='commit' type='submit' value='表示' />";
    echo "</form>";
}

?>
