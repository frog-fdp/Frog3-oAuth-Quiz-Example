<?php
require_once('include.php');

// -- prerequisites
if (!function_exists('apc_fetch'))
    exit('Requires the APC extension');
if (!function_exists('oauth_urlencode'))
    exit('Requires the PECL oAuth extension');

// Some defs

define('request_token_url','/api/1/oauth1.php/request-token');
define('frog_auth_endpoint','/app/login');
define('access_token_url','/api/1/oauth1.php/access-token');
define('whoami_endpoint','/api/1/?method=auth.whoami');
$self = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

// Are we already logged in?
if(isset($_SESSION['lik'])) {
    header("Location: index.php");
}

// If we dont have a school key in session or get request, we cant look up tokens
if(!isset($_SESSION['sk_key']) && !isset($_GET['sk'])) {
    throw new Exception('No school specified');
}

// Get our consumer tokens for auth
$school = isset($_GET['sk']) ? $_GET['sk'] : $_SESSION['sk_key'];
$sql = 'SELECT url,consumer_token,consumer_secret FROM school WHERE key="'.$school.'"';
$res = $db->arrayQuery($sql);
if(!sizeof($res)) {
    throw new Exception('Unknown school: '.$school);
}
$_SESSION['sk_key'] = $school;
$school_host = $res[0]['url'];

// Initiate oauth object
$oauth = new OAuth($res[0]['consumer_token'], $res[0]['consumer_secret'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
$oauth->enableDebug();
$oauth->setRequestEngine(OAUTH_REQENGINE_STREAMS);

if(!isset($_SESSION['oa_stage']))
    $_SESSION['oa_stage'] = 1;

$msg='';
try {
    // Get req token
    if($_SESSION['oa_stage']==1 && !isset($_SESSION['req_token'])) {
        // Try and get a token
        $req_token = $oauth->getRequestToken($school_host.request_token_url, $self);
        $_SESSION['req_token'] = $req_token;
        $_SESSION['oa_stage'] = 2;
        // Fire off request to auth at remote host
        header('Location: '.$school_host.frog_auth_endpoint.'?oauth_token='.$req_token['oauth_token']);
    } else {
        // We are now in stage 2, and got a response back from the login process
        $declined = false;
        if(!isset($_GET['oauth_verifier']) || $_GET['oauth_token'] != $_SESSION['req_token']['oauth_token']) {
            if(isset($_GET['denied'])) { // We clicked cancel
                $msg = 'You must approve access on your frog box to log in.';
            } else { // Our token expired
                $msg = 'Your request token is invalid.';
            }
            $declined = true;
        } else {
            // Try to get access token
            $oauth->setToken($_SESSION['req_token']['oauth_token'],$_SESSION['req_token']['oauth_token_secret']);
            $atoken = $oauth->getAccessToken($school_host.access_token_url,null,$_GET['oauth_verifier']);
            $declined = true;

            // Do api request
            $oauth->setToken($atoken['oauth_token'],$atoken['oauth_token_secret']);
            $oauth->fetch($school_host.whoami_endpoint);
            $data = json_decode($oauth->getLastResponse(),true);
            if(isset($data['data'])) {
                $name = $data['data']['firstname'].' '.$data['data']['surname'];
                $user_key = md5($data['data']['id'].$data['data']['company']['key']);
                // See if we already exist
                $sql = 'SELECT * FROM user WHERE key="'.$user_key.'"';
                $res = $db->arrayQuery($sql);
                if(sizeof($res)) {
                    // We already exist, update name just in case it has changed
                    $sql = 'UPDATE user SET name="'.$name.'" WHERE key="'.$user_key.'"';
                    $db->queryexec($sql);
                } else { // New user so insert fresh record
                    $sql = 'INSERT INTO user (school, key, name, score) VALUES ("'.
                            $_SESSION['sk_key'].'","'.$user_key.'","'.$name.'",0);';
                    $db->queryexec($sql);
                }
                // Update our session vars, save access token in case we need to do more
                // api requests later
                $_SESSION['lik'] = $user_key;
                $_SESSION['acc_token'] = $atoken['oauth_token'];
                $_SESSION['acc_token_sec'] = $atoken['oauth_token_secret'];
                $declined=false;
            }
        }
        if($declined) {
            $_SESSION = array();
            echo 'Authentication failed:<br />';
            if($msg != '')
                echo $msg.'<br />';
            echo '<p><a href="index.php">Back to home page</a></p>';
            
            die();
        } else {
            header('Location: index.php');            
        }
    }

} catch (Exception $e) { // we authed, but we cant continue in the app.
    $_SESSION = array();

    echo '<p>Access was refused. This could be due to one of the following reasons:</p>';
    echo '<ul>';
    echo '<li>Your account on frog does not have access to oauth</li>';
    echo '<li>Your user profile does not have access to the needed api.</li>';
    echo '</ul>';
    echo '<p>To connect with this test app via oauth, you need to ensure that your profile has access to the '.
         'auth.whoami frog api. Both of these should be easily set up by an administrator on your '.
         'frog box.</p>';
    
    echo '<p><a href="index.php">Back to home page</a></p>';
    die();
}

