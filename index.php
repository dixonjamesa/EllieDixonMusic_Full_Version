<?php
  // buffer the output:
  ob_start();
  // begin a session
  session_start();
  $ipaddr = get_ip_address();
  $sessiontimeout = false;

  // load configuration info:
  require_once 'configed.php'; // configuration settings
  //require_once 'dataconnector.php';
  require_once 'visitor.php';
  require_once 'login.php';

  $msdb_connection = init_dataconnection($servername, $sqldb, $sqluser, $sqlpass);

/* First output the head tags and common head html */

if( isset($_GET['page'])){
  $rpage = $_GET['page'];
}else{
  $rpage="";
}
include 'head.html';

 /* Now we'll do the body tag and some common stuff... */
 include 'commonbody.html';

/* Now some stuff to write out info to the log file, and the visitor file */
function get_ip_address() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                // trim for safety measures
                $ip = trim($ip);
                // attempt to validate IP
                if (validate_ip($ip)) {
                    return $ip;
                }
            }
        }
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}
/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function validate_ip($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}

function write_logfile($what)
{
	$logfile = file_put_contents('log.txt', $what.PHP_EOL, FILE_APPEND | LOCK_EX );
}

/* ------------- get data into variables ----------- */
$date = new DateTime();
$date = $date->format("y:m:d H:i:s");
$page = strtolower($rpage);

if($page=='')
{
	// send them to the home page:
	$page='home';
}

//visitor_logvisit($ipaddr, $date, $page."/".$_GET['data']);

include 'banner.html';

//phpinfo();
/* see if we support mod_rewrite:
$mods = apache_get_modules();
if (in_array('mod_rewrite', $mods)) {
    echo "Yes, Apache supports mod_rewrite.";
}

else {
    echo "Apache is not loading mod_rewrite.";
}
*/

if(login_proceed($msdb_connection, $page)){
  //echo 'GET PAGE';  /* It's OK to include the actual contents then... */
  if (file_exists ( $page.'.html' ) )
  {
      include $page.'.html';
  }
  else{
      echo "<div><p style='font-size:14pt;padding:20px;text-align:center;'>Oh dear! It appears the page ".$page." you are looking for doesn't exist.</p>";
      echo "<p style='font-size:14pt;padding:20px;text-align:center;'>Click <a href='http://elliedixonmusic.com'>here</a> to go home.</p></div>";
  }
}
else {
  // not logged in, but needs to be...
  include 'login.html';
}
include 'foot.html';

$msdb_connection->close();
ob_end_flush(); ?>
