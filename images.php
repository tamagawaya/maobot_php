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

echo "<table>";
echo image_list($now_page);
echo "</table>";

echo page_list($now_page);

function image_list($page, $row=10, $column=5) {
    $res = selectImages($row*$column*($page-1), $row*$column*$page-1);
    $r = 1;
    $c = 1;
    echo "<tr>";
    foreach ($res as $image) {
        echo "<td><div class='text-center'><a href={$image->loc}><img src={$image->thum} /></a></td>\n";
        if ($r % row == 0) {
            if ($c != column) {
                $c++;
                $r = 0;
                echo "</tr><tr>\n";
            } else {
                echo "</tr>\n";
            }
        }
        $r++;
    }
    echo "</tr>";
}

function page_list($now_page) {
    echo "<form> accept-charset='UTF-8' action='images.php' method='POST'>";
    echo "<select id='page' name='page'>\n";
    $res = selectImagesCount();
    $count = $res[0]->count(id);
    $page_count = round($count/50);
    for($page = 1; $page <= $page_count; $page++) {
        echo "<option ";
        if ($page == $now_page) {
            echo "selected ";
        }
        echo "value=".$page."></option>\n";
    }
    echo "<input class='btn' name='commit' type='submit' value='表示' />";
    echo "</form>";
}

?>
