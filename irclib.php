<?php

/* irclib.php
 * Libraries for IRC
 * 
 * insertPHPLog($data)
 *
 * insertChannel($channel)
 *
 * selectLogs()
 *
 * selectChannels()
 */

require_once "DBRecord.class.php";

function insertIRCLog($log) {
    $db = new DBRecord();
    return $db->insert('irclog', $log, '%s%s%s%s');
}

function insertPHPLog($data) {
    $log = array(
        "user"    => $data["nick"],
        "type"    => "PRIVMSG",
        "channel" => $data["channel"],
        "content" => $data["message"],
        "said"    => "0"
    );
    $db = new DBRecord();
    return $db->insert('irclog', $log, '%s%s%s%s%d');
}

function selectLogs_unsaid() {
    $db = new DBRecord();
    return $db->select('SELECT * FROM irclog WHERE said = ?', array(0), array('%d'));
}

function selectLogs($channel, $starttime, $endtime) {
    $db = new DBRecord();
    return $db->select('SELECT * FROM irclog WHERE channel = ? AND created BETWEEN ? AND ?', array($channel, $starttime, $endtime), array('%s','%s','%s'));
}

function selectChannels() {
    $db = new DBRecord();
    return $db->select('SELECT name FROM channel');
}

function selectImages($startnum, $endnum) {
    $db = new DBRecord();
    return $db->select('SELECT * FROM images ORDER BY id DESC LIMIT ?, ?', array($startnum, $endnum), array('%d', '%d'));
}

function selectImagesCount() {
    $db = new DBRecord();
    return $db->select('SELECt count(id) FROM images');
}

function insertChannel($ch) {
    $log = array(
        "name" => $ch,
    );
    $db = new DBRecord();
    return $db->insert('channel', $log, array('%s'));
}

function updateUnsaid2Said($id) {
    $data = array(
        "said" => 1,
    );
    $where = array(
        "said" => 0,
    );
    $db = new DBRecord();
    return $db->update('irclog', $data, array('%d'), $where, array('%d'));
}

function updateChannelData($chdata){
    if ($chdata->topic == Null) {
        $topic = "NULL";
    } else {
        $topic = $ch->topic;
    }
    $users = array();
    foreach ($chdata->users as $user) {
        $users[] = $user->nick;
    }
    $updateData = array(
        "topic"   => $topic,
        "usernum" => count($chdata->users),
        "users"   => implode(",", $users),
    );
    $where = array(
        "name" => $chdata->name,
    );
    $db = new DBRecord();
    return $db->update('channel', $updateData, array('%s','%s','%s'), $where, array('%s'));
}

