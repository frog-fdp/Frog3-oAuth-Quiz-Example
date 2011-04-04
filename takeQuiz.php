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

if(!$me || $me['score']>0) {
    header('Location: index.php');
}

$q = isset($_GET['q']) ? (int)$_GET['q'] : 1;
$q = min(max($q,1),11);
$tot = ($q==1) ? array() : isset($_SESSION['total']) && is_array($_SESSION['total']) ? $_SESSION['total'] : array();
$_SESSION['total'] = $tot;

if($q<11) {
    $qry = "SELECT * FROM question WHERE number=".$q;
    $res = $db->arrayQuery($qry);
    $question = $res[0];
    $answers = explode('|',$question['answers']);
}

if($q>1) {
    $answer = isset($_GET['a']) ? (int)$_GET['a'] : -1;
    if($answer != -1) {
        $qry = "SELECT answers from question WHERE number = ".($q-1);
        $res = $db->arrayQuery($qry);

        $prev_answers = explode('|',$res[0]['answers']);
        if(substr($prev_answers[$answer],0,1) == '*') 
            $tot[$q] = 1;
        else
            $tot[$q] = 0;
        $_SESSION['total'] = $tot;
    }
}

if($q<11) {
//    echo $me['name'].'<br />';
?>
  <div id="question_holder">
      <div id="quiz_image">
        <img src="images/quiz/<?=$question['image']?>"/>
      </div>
      <div id="question">
        Question <?=$q?> : <?=$question['question']?>
      </div>
    <form action="takeQuiz.php" method="GET">
    <div id="answers">
<?php
    for($co=0; $co<sizeof($answers); $co++) {
        echo "<input type='radio' class='answer' id='a' name='a' value='".$co."'/>";
        if(substr($answers[$co],0,1)=="*") {
            echo substr($answers[$co],1);
        } else {
            echo $answers[$co];
        }
        echo "<br />";
    }
?>
    </div>
        <input type="hidden" name="q" value="<?=($q+1)?>" />
        <input type="submit" value="Next" id="next" />
        </form>
    </div>
<?php
} else {
    $tot = array_sum($_SESSION['total']);
    $sql = "update user set score=".$tot.' WHERE key="'.$_SESSION['lik'].'"';
    $db->queryexec($sql);
    
    header("Location: index.php");
}
?>
      </div>
  </body>
</html>