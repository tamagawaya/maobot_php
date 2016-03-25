while :
do
    if ! ps aux | grep "php" | grep -v grep; then
        php ircbot.php
    fi
done
