<?php

/**
 * 
 * This file clears our all sqlite db, and recreates them.
 * This should only be run ONCE before running the app. Using it afterwards will
 * delete all schools, users, scores etc from the local app.
 * 
 * Note, the queries all start with @, as older versions of sqlite dont allow you
 * to use 'IF NOT EXISTS' etc, and it is easier therefore to try the operations and
 * just ignore the warnings if the table does/doesnt exist.
 * 
 */

require_once('include.php');

function insertQuestion($db, $n, $q, $p, $a) {
    $query = 'INSERT INTO question ';
    $query .= ' (number, question, image, answers) VALUES ';
    $query .= '('.$n.',"'.$q.'","'.$p.'","'.$a.'");';
    $db->queryexec($query);
}

// Create questions
@$db->queryexec("DROP TABLE question");
$query = "CREATE TABLE question (
  number int(10) NOT NULL PRIMARY KEY,
  question text NOT NULL,
  image text NOT NULL,
  answers text NOT NULL
);";
@$db->queryexec($query);
// Create school table
@$db->queryexec("DROP TABLE school");
$query = "CREATE TABLE school (
    key varchar(40) NOT NULL PRIMARY KEY,
    name text NOT NULL,
    url text NOT NULL,
    consumer_token varchar(40) NOT NULL,
    consumer_secret varchar(40) NOT NULL
    )";
@$db->queryexec($query);
// Create user table
@$db->queryexec("DROP TABLE user");
$query = "CREATE TABLE user (
    school varchar(40) NOT NULL,
    key varchar(40) NOT NULL PRIMARY KEY,
    name varchar(255) NOT NULL,
    score int(2) DEFAULT 0
    )";
@$db->queryexec($query);

@$db->queryexec('delete from school where 1');
@$db->queryexec('delete from user where 1');

// Create the questions
$db->queryexec('DELETE FROM question WHERE 1');
insertQuestion($db, 1, 'Name the UK city', 'ben.jpg', '*London|Oslo|Birmingham|Tokyo');
insertQuestion($db, 2, 'Name the French Landmark', 'eiffel.jpg', 'Opera House|*Eiffel Tower|Buckingham Palace');
insertQuestion($db, 3, 'Where is this mountain?', 'fuji.jpg', 'Somerset|Canada|*Japan|Isle of Wight');
insertQuestion($db, 4, 'Where is this famous bridge?', 'goldengate.jpg', 'Bristol|*San Francisco|Cairo|The Moon');
insertQuestion($db, 5, 'Which country is divided by this?', 'greatwall.jpg', '*China|Scotland|Bahrain|Wales');
insertQuestion($db, 6, 'The statue of .....', 'liberty.jpg', 'Freedom|Power|Some woman|*Liberty');
insertQuestion($db, 7, 'A famous monument in which country?', 'mf_taj.jpg', 'England|Scotland|Ireland|*India');
insertQuestion($db, 8, 'The sydney ..... house', 'operahouse.jpg', '*Opera|Beach|White|Mouse');
insertQuestion($db, 9, 'Where is this?', 'pyramid.jpg', 'Cardiff|*Egypt|Weston-super-mare|Edinburgh');
insertQuestion($db, 10, 'Mount Rushmore, but who are they?', 'rushmore.jpg', 'The Beatles|The Rolling Stones|*American Presidents');



