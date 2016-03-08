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

echo "<table>";
image_list();
echo "</table>";


function image_list() {
    $res = selectImages();
    foreach ($res as $image) {
        echo "<div class='text-center'><a href={$image->loc}><img src={$image->thum} /></a>";
    }
}
