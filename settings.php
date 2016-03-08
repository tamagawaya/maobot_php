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
#define("IRCCHANNELS", array(
#    "#maobot_test",
#    "#maobot_test2"
#));
define("IRCCHANNELS", Null);
define("IRC_ENCODING", "iso-2022-jp");
mb_internal_encoding("UTF-8");
?>

