<?php
session_start();
define('db_name','quiz_db');
define('num_scores',5);

// Connect to db
$db = new SQLiteDatabase(db_name,0666,$err);
if($err) exit($err);

if(isset($_GET['logout'])) {
    session_destroy();
    $_SESSION=array();
}

$logged_in = isset($_SESSION['lik']) ? $_SESSION['lik'] : null;
if($logged_in) {
    $res = $db->arrayQuery('SELECT * from user where key="'.$_SESSION['lik'].'"');
    if(!sizeof($res)) {
        session_destroy();
        $_SESSION=array();
        $logged_in = null;
    } else {
        $me = $res[0];
    }
} else {
    $me = null;
}