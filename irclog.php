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


$starttime = date('Y-m-d\TH:i', time()-60*60*3);
$endtime   = date('Y-m-d\TH:i');
$channel   = "#化学部";
$image_display = '0';

if (isset($_COOKIE['nick'])) {
    $nick = $_COOKIE['nick'];
} else {
    $nick = "";
}

if (isset($_POST["image_display"])) {
    $image_display = $_POST["image_display"];
}

if (isset($_POST["nick"])) {
    $nick = $_POST["nick"];
    setcookie('nick', $nick, time() + 60*60*24*14);
}

if ((!isset($_POST["now"])) && (!isset($_POST["send"]))) {
    if(isset($_POST["starttime"])) {
        $starttime = $_POST["starttime"];
    }

    if (isset($_POST["endtime"])) {
        $endtime = $_POST["endtime"];
    }
}

if (isset($_POST["mae"])) {
    $starttime = date('Y-m-d\TH:i', strtotime($starttime.'-3 hours'));
    $endtime = date('Y-m-d\TH:i', strtotime($endtime.'-3 hours'));
}

if (isset($_POST["tugi"])) {
    $starttime = date('Y-m-d\TH:i', strtotime($starttime.'+3 hours'));
    $endtime = date('Y-m-d\TH:i', strtotime($endtime.'+3 hours'));
}

if (isset($_POST["channel"])) {
    $channel = $_POST["channel"];
}

#echo show_log($channel, $starttime, date('Y-m-d\TH:i', strtotime($endtime.'+1 minute')));
#print(strtotime($endtime.'+1 minute'));

if (isset($_POST["send"]) and isset($_POST["channel"]) and isset($_POST["nick"]) and isset($_POST["message"])) {
    insertPHPLog($_POST);
}

echo show_log($channel, $starttime, date('Y-m-d\TH:i', strtotime($endtime.'+1 minute')), $image_display);

#echo " <form action='/mypage/maobot_php/irclog.php#bottom' method='POST'>\n";
echo " <form action='irclog.php#bottom' method='POST'>\n";
echo disp_list($channel);
?>
 
 <input type="datetime-local" id="starttime" name="starttime" value="<?php echo $starttime?>" />
 <input type="datetime-local" id="endtime"   name="endtime"   value="<?php echo $endtime?>" />
 <input type="submit"         id="display"   name="display"   value="表示" />
 <input type="submit"         id="mae"       name="mae"       value="＜前" />
 <input type="submit"         id="now"       name="now"       value="今"   />
 <input type="submit"         id="tugi"      name="tugi"      value="次＞" /><br />
 <select name="image_display" onChange="submit()">
  <option <?php if ($image_display == '0') echo("selected ");?> value='0'>False</option>
  <option <?php if ($image_display == '1') echo("selected ");?> value='1'>True</option>
 </select>
 <input id="nick" maxlength="50" name="nick" placeholder="名前" size="10" type="text" value="<?php echo $nick;?>" />
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
    echo("  <select name=\"channel\" onChange=\"submit()\">\n");
    foreach ($res as $row) {
        echo("   <option ");
        if ($selected_value == $row->name) {
            echo("selected ");
        }
        echo("value=\"" . $row->name . "\">" . $row->name . "</option>\n");
    }
    echo("  </select>");
}

function show_log($ch, $st, $et, $image_display) {
    $res = selectLogs($ch, $st, $et);
    foreach ($res as $row) {
        if ($row->type == "IMGLINK" && $image_display=='1') {
            echo "    <img src={$row->content} /><br />";
        } else {
            $user = htmlspecialchars($row->user, ENT_QUOTES);
            $content = htmlspecialchars($row->content, ENT_QUOTES);
            echo "   <div class='irc time'>{$row->created}</div>";
            if ($row->said == 1) {
                echo "   <div class='irc name'> [{$user}]</div>";
            } else {
                echo "   <div class='irc name'> ({$user})</div>";
            }
            if ($row->type == "PRIVMSG") {
                echo "   <div class='irc priv'> {$content}</div><br />\n";
            } elseif ($row->type == "NOTICE") {
                echo "   <div class='irc noti'> {$content}</div><br />\n";
            }
        }
    }
}

?>
