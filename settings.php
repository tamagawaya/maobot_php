<?php

require_once "Net/SmartIRC.php";

define("DEBUGLEVEL", SMARTIRC_DEBUG_NONE);

//DB settings
define("DBUSER", "");
define("DBPASS", "");
define("DBHOST", "");
define("DBNAME", "");

//IRC settings
define("IRCHOST", "");
define("IRCPORT", "");
define("IRCNAME", "");

// Only supported by php7
#define("IRCCHANNELS", array(
#    "#maobot_test",
#    "#maobot_test2"
#));

//Image DL settings
define("NICOUSER", '');
define("NICOPASS", '');
define("PIXIUSER", '');
define("PIXIPASS", '');

define("IRCCHANNELS", Null);
define("IRC_ENCODING", "iso-2022-jp");
mb_internal_encoding("UTF-8");
?>

