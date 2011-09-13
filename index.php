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

echo '
    <h2>Top '. num_scores .' School Averages</h2>
    <div id="schoolavg">
        <table class="board overall">
            <tr><th class="name">School</th><th class="score">Average Score</th></tr>';
// Show Scoreboard
$sql = "SELECT school.name,sum(score) as total, count(user.key) as num FROM user,school WHERE school.key=user.school AND score>0 GROUP BY school";
$res = $db->arrayQuery($sql);
$schools = array();
foreach($res as $r) {
    $schools[$r['school.name']] = ($r['total'] / $r['num']);
}
arsort($schools);
$schools = array_slice($schools,0,num_scores);
foreach($schools as $n=>$a) {
    echo '<tr><td class="name">'. $n .'</td><td class="score">'. $a .'</td></tr>';
}
echo '</table></div>';

if($logged_in) {
    echo '
    <h2>Your School Top '. num_scores .' scores</h2>
    <div id="myboard">
        <table class="board mine">
            <tr><th class="name">User</th><th class="score">Total Score</th></tr>';

    // My School leaderboard
    $res = $db->arrayQuery('SELECT * FROM user where school="'.$me['school'].'" AND score>0 LIMIT '.num_scores);
    foreach($res as $r) {
        echo '<tr><td class="name">'.$r['name'].'</td><td class="score">'.$r['score'].'</td></tr>';
    }
    echo '</table></div>';
    echo '<div id="summary">';
    // Option to take quiz, or you score
    if($me['score'] == 0) {
        echo "<a href='takeQuiz.php'>Take the quiz ".$me['name']."!</a>";
    } else {
        echo '<p>'.$me['name'].', you have completed the quiz!</p>';
        echo '<p>You scored<br /><span class="myscore">'.$me['score'].'</span></p>';
        // My friends
        
    }
    echo '</div>';
    echo '<div id="logout"><a href="index.php?logout">Logout</a></div>';
} else {
    // Get school list
    echo '<div id="login">';
    echo '<p><strong>You are not logged in.</strong><br />Please select your school below:</p>';
    echo '<ul id="schoollist" style="list-style:none;">';
    $sql = "SELECT * FROM school ORDER BY name ASC";
    $res = $db->arrayQuery($sql);
    if(sizeof($res)) {
        foreach($res as $r) {
            echo '<li><a href="doAuth.php?sk='.$r['key'].'">'.$r['name'].'</a></li>';
        }
    } else {
        echo '<li>No school registered yet.</li>';
    }
    echo '</li></div>';
    echo '<div id="notlisted">Is your school not listed? <a href="addSchool.php">Add it here</a></div>';
}
?>
      </div>
  </body>
</html>