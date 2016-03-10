<!doctype html>
<html>
<head>
 <meta charset="utf-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title> IRC LOG </title>
 <link href="css/bootstrap.min.css" rel="stylesheet">
 <link href="css/irc.css" rel="stylesheet">
</head>
<body>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
 <script src="js/bootstrap.min.js"></script>

<?php

include_once "irclib.php";

session_start();
session_regenerate_id(true);
//login check
#if(!isset($_SESSION["USERID"])) {
#    header("Location: logout.php");
#    exit;
#}


$starttime = date('Y-m-d\TH:i:s', time()-60*60*3);
$endtime   = date('Y-m-d\TH:i:s');
$channel   = "#maobot_test";

if (!isset($_POST["now"])) {
    if(isset($_POST["starttime"])) {
        $starttime = $_POST["starttime"];
    }

    if (isset($_POST["endtime"])) {
        $endtime = $_POST["endtime"];
    }

    if (isset($_POST["channel"])) {
        $channel = $_POST["channel"];
    }
}

echo show_log($channel, $starttime, $endtime);

if (isset($_POST["send"]) and isset($_POST["channel"]) and isset($_POST["nick"]) and isset($_POST["message"])) {
    insertPHPLog($_POST);
}

print(' <form action="/mypage/maobot_php/irclog.php#bottom" method="POST">');
echo disp_list($channel);
?>
 
 <input type="datetime-local" id="starttime" name="starttime" value="<?php echo $starttime?>" />
 <input type="datetime-local" id="endtime"   name="endtime"   value="<?php echo $endtime?>" />
 <input type="submit"         id="submit"    name="submit"    value="表示" />
 <input type="submit"         id="now"       name="now"       value="今"   /><br />
 <input id="nick" maxlength="50" name="nick" placeholder="名前" size="10" type="text" value="" />
 <input id="message" maxlength="1000" name="message" placeholder="発言内容" size="50" type="text" value="" />
 <input id="send" name="send" type="submit" value="送信" />
 </form>
 <div id="bottom"></div>
</body>
</html>

<?php
//----------------------
// funcitons            
//----------------------

function disp_list($selected_value) {
    $res = selectChannels();
    echo("<select name=\"channel\">");
    foreach ($res as $row) {
        echo("<option ");
        if ($selected_value == $row->name) {
            echo("selected ");
        }
        echo(" value=\"" . $row->name . "\">" . $row->name . "</option>");
    }
    echo("</select>");
}

function show_log($ch, $st, $et) {
    $res = selectLogs($ch, $st, $et);
    foreach ($res as $row) {
        echo "   <div class='irc'>{$row->created}</div>";
        echo "   <div class='irc'> ({$row->user})</div>";
        echo "   <div class='irc'> {$row->content}</div><br />\n";
    }
}

?>
