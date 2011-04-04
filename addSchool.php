<!DOCTYPE html>
<html>
  <head>
    <title>Awesome inter-school quiz</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="quiz.css" />
  </head>
  <body>
      <div id="container">
  <div id="header">Awesome Inter-School Quiz!</div>
<?php
require_once('include.php');

$msg = '';
$show_advice = false;
$sname = (isset($_POST['name'])) ? $_POST['name'] : '';
$surl = (isset($_POST['url'])) ? $_POST['url'] : '';
$skey = (isset($_POST['key'])) ? $_POST['key'] : '';
$ssec = (isset($_POST['sec'])) ? $_POST['sec'] : '';
$posted = isset($_POST['posted']);

if( $posted ) {
    $msgs = array();
    if($sname=='') {
        $msgs[] = 'You must specify a school name';
    }
    if($surl=='') {
        $msgs[] = 'You must specify a school url (eg http://my.frogbox.co.uk)';
    }
    if($skey=='') {
        $msgs[] = 'You must specify a consumer key';
        $show_advice = true;
    }
    if($ssec=='') {
        $msgs[] = 'You must specify a consumer secret';
        $show_advice = true;
    }
    
    if(sizeof($msgs)) {
        $msg = implode('<br />',$msgs);
    } else {
    
        // Add school and bounce back to index
        $url1 = $_POST['url'];
        $url2 = 'http://'.$url1;

        $sql = 'SELECT key FROM school where url="'.$url1.'" OR url="'.$url2.'"';
        $res = $db->arrayQuery($sql);
        if(sizeof($res)) { // url already exists
            $msg = 'URL already in use.';
        } else {
            $sql = 'INSERT INTO school (key, name, url, consumer_token, consumer_secret) VALUES ("'
            .md5($surl.microtime()).'","'. $sname.'","'.$surl.'","'
            .$skey.'","'.$ssec.'")';
            $db->queryexec($sql);
            header('Location: index.php');
        }
    }
} 
// Show form
echo '<h2>Add your school to the quiz!</h2>';
echo '<form action="addSchool.php" method="post">';
echo '<table class="addschool">';
if($msg != '') {
    echo '<tr class="error"><td colspan="2">'.$msg.'</td></tr>';
}
if($show_advice) {
    echo '<tr class="error"><td colspan="2">You need a consumer configured on your frog install. An '
        .'Administrator can create these in the toolkit, and provide you with the relevant key and '
        .'secret.</td></tr>';
}
echo '<tr><th>School Name:</td><td><input type="text" name="name" id="name" value="'.$sname.'" /></td></tr>';
echo '<tr><th>Frog Url:</td><td><input type="text" name="url" id="url" value="'.$surl.'" /></td></tr>';
echo '<tr><th>Consumer key:</td><td><input type="text" name="key" id="key" value="'.$skey.'" /></td></tr>';
echo '<tr><th>Consumer secret:</td><td><input type="text" name="sec" id="sec" value="'.$ssec.'" /></td></tr>';
echo '<input type="hidden" name="posted" id="posted" value="posted" />';
echo '<tr><td><a href="index.php">Cancel</a></td><td><input type="submit" value="Add school" /></td></tr>';
echo '</table>';

?>
      </body>
</html>
      