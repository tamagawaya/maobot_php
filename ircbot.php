<?php

/* ircbot.php
 * Bot FILE
 *
 * updateCH doesn't run well now.
 */

include_once "Net/SmartIRC.php";
include_once "./settings.php";
include_once "./irclib.php";
include_once "./imglib.php";

class Net_SmartIRC_module_IRCBot {
    public $name        = "maobot_php_test";
    public $description = "IRCBot for KGB";
    public $author      = "MaO";
    public $license     = "MIT";

    private $irc;
    private $handlerids;

    public function __construct($irc) {
        $this->irc = $irc;
        $this->handlerids = array(
            $irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL | SMARTIRC_TYPE_NOTICE, '.*', $this, 'getLog'),
            $irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '^!join', $this, 'joinCh'),
            $irc->registerActionHandler(SMARTIRC_TYPE_CHANNEL, '/https?:\/\/[a-zA-Z0-9\-\.\/\?\@&=:~_#]+/', $this, 'getUrl'),
//          $irc->registerActionHandler(SMARTIRC_TYPE_TOPICCHANGE | SMARTIRC_TYPE_JOIN | SMARTIRC_TYPE_PART, '.*', $this, 'updateCh'),
            $irc->registerTimeHandler(3000, $this, 'talkFromPHP'),
        );
    }

    public function __destruct() {
        $this->irc->unregisterActionID($this->handlerids);
    }

    private function encode($str) {
        return mb_convert_encoding($str, IRC_ENCODING);
    }

    private function decode($str) {
        return mb_convert_encoding($str, mb_internal_encoding(), IRC_ENCODING);
    }

    private function decodeData($data) {
        $class_name = get_class($data);
        $deData = new $class_name();
        $object_vars = get_object_vars($data);
        foreach ($object_vars as $name => $value) {
            if (is_string($value)) {
                $deData->$name = $this->decode($value);
            } elseif ($value != Null) {
                $daData->$name = $value;
            }
        }
        return $deData;
    }

    public function getLog($irc, $data) {
        $log = array(
            "user" => $this->decode($data->nick),
            "type" => $this->decode($data->rawmessageex[1]),
            "channel" => $this->decode($data->channel),
            "content" => $this->decode($data->message),
        );
        insertIRCLog($log);
    }

    public function joinCh($irc, $data) {
        if (isset($data->messageex[1])) {
            $channel = $data->messageex[1];
            $irc->join(array($channel));
            insertChannel($this->decode($channel));
        } else {
            $irc->message($data->type, $data->nick, 'wrong parameter count');
            $irc->message($data->type, $data->nick, 'usage: !join $channels');
        }
    }

    public function getUrl($irc, $data) {
        preg_match_all('/https?:\/\/[a-zA-Z0-9\-\.\/\?\@_&=:~#]+/', $data->message, $match);
        $url = $match[0][0];
        $urldata = file_get_contents($url);
        $urldata = mb_convert_encoding($urldata, "UTF-8");
        preg_match( "/<title>(.*?)<\/title>/i", $urldata, $matches);
        $decode_title = html_entity_decode(str_replace("&#10;", " ", $matches[1]));
        $irc->message(SMARTIRC_TYPE_NOTICE, $data->channel, $this->encode($decode_title));
        $log = array(
            "user" => "maobot",
            "type" => "NOTICE",
            "channel" => $this->decode($data->channel),
            "content" => $decode_title,
        );
        insertIRCLog($log);
        $links = dlImg($url);
        foreach ($links as $link) {
            $log = array(
                "user"    => "maobot",
                "type"    => "IMGLINK",
                "channel" => $this->decode($data->channel),
                "content" => $link,
            );
            insertIRCLog($log);
        }
        #exec('python3 ./imgdl.py "' . $url . '"');
    }

    public function updateCh($irc, $data) {
        foreach (selectChannels() as $ch) {
            updateChannelData($this->decodeData($irc->getChannel($ch->name)));
        }
    }

    public function talkFromPHP($irc) {
        $res = selectLogs_unsaid();
        if ($res!=Null) {
            foreach ($res as $row) {
                $channel = $this->encode($row->channel);
                $nick    = $this->encode($row->user);
                $content = $this->encode($row->content);
                $id      = $row->id;
                $irc->message(SMARTIRC_TYPE_CHANNEL, $channel, '('.$nick.') '.$content);
                updateUnsaid2Said($id);
            }
        }
    }
}

$irc = new Net_SmartIRC(array(
    'DebugLevel' => DEBUGLEVEL,
    'ChannelSyncing' => true,
));

if (IRCCHANNELS != Null) {
    $IRCCHANNELS = IRCCHANNELS;
} else {
    foreach (selectChannels() as $row) {
        $IRCCHANNELS[] = mb_convert_encoding(($row->name), IRC_ENCODING);
    }
}

$irc->loadModule('IRCBot')
    ->connect(IRCHOST, IRCPORT)
    ->login(IRCNAME, IRCNAME, 0, IRCNAME)
    ->join($IRCCHANNELS)
    ->listen()
    ->disconnect();

?>
