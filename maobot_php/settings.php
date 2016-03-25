<?php

require_once "Net/SmartIRC.php";

define("DEBUGLEVEL", SMARTIRC_DEBUG_NONE);

//DB settings
define("DBUSER", "maobot");
define("DBPASS", "msc3824");
define("DBHOST", "localhost");
define("DBNAME", "maobot");

//IRC settings
define("IRCHOST", "irc.ircnet.ne.jp");
define("IRCPORT", "6667");
define("IRCNAME", "maobot");

// Only supported by php7
#define("IRCCHANNELS", array(
#    "#maobot_test",
#    "#maobot_test2"
#));

//Image DL settings
define("NICOUSER", 'maoson0307@gmail.com');
define("NICOPASS", 'msc3824');
define("PIXIUSER", 'maoson0307');
define("PIXIPASS", '930307');

define("IRCCHANNELS", Null);
define("IRC_ENCODING", "iso-2022-jp");
mb_internal_encoding("UTF-8");
?>

