<?php
  // buffer the output:
  ob_start();
  // begin a session
  session_start();
  $ipaddr = get_ip_address();
  $sessiontimeout = false;

  // load configuration info:
  require_once 'configed.php'; // configuration settings and database stuff
  if(file_exists('staging.php'))
	require_once 'staging.php'; // If this file is present, then the root becomes the staging directory - for testing
  require_once 'visitor.php';
  require_once 'login.php';

  $msdb_connection = init_dataconnection($servername, $sqldb, $sqluser, $sqlpass);

/* Get the IP address of the client PC that is connecting */
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

  
if( isset($_GET['page'])){
  $rpage = $_GET['page'];
}else{
  $rpage="";
}
/* First output the head tags and common head html */
include 'head.html';
 /* Now we'll do the body tag and some common stuff... */
 include 'commonbody.html';

function write_logfile($what)
{
	global $date;
	
	$out = $date.":  ".$what;
	$logfile = file_put_contents('log.txt', $out.PHP_EOL, FILE_APPEND | LOCK_EX );
}

/* ------------- get data into variables ----------- */
$date = new DateTime();
$date = $date->format("y/m/d H:i:s");
$page = strtolower($rpage);

if($page=='')
{
	// send them to the home page:
	$page='home';
}

// log this visit
//visitor_logvisit($ipaddr, $date, $page."/".$_GET['data']);

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
// function to put a horizontal spacer fixed image into the page
function spacer($height, $margin, $image)
{
	echo '<div class="scroll-image-ed hidden-xs" style="background-image:url(\''.$image.'\');opacity:0.8;margin:'.$margin.'px 0px;height:'.$height.'px;"></div>';
	echo '<div class="scroll-image-ed-sm visible-xs" style="background:#efd9db; background-image:none;opacity:0.8;margin:'.$margin.'px 0px;height:'.$height.'px;"></div>';
}

// first put he banner at the top of the page
include 'banner.html';


// Now decide exactly what page contents to show...
// If the page exists, and no login is required for it, then proceed to show it
// if it doesn't exist, show the page missing text
// if login is required, force the login page
if(login_proceed($msdb_connection, $page)){
  //echo 'GET PAGE';  /* It's OK to include the actual contents then... */
  write_logfile("Visitor ".$ipaddr." to page: ".$page);
  if (file_exists ( $page.'.html' ) )
  {
	  // page exists, let's go...
      include $page.'.html';
  }
  else{
	  // page doesn't exist, so tell the visitor
      echo "<div><p style='font-size:14pt;padding:20px;text-align:center;'>Oh dear! It appears the page you are looking for doesn't exist.</p>";
      echo "<p style='font-size:14pt;padding:20px;text-align:center;'>Click <a href='http://elliedixonmusic.com'>here</a> to go home.</p></div>";
	  echo "<p>";
	  phpinfo();
	  echo "<\p>";
  }
}
else {
  // not logged in, but needs to be...
  include 'login.html';
}

// now include the common page footer - social links etc.
include 'foot.html';


$msdb_connection->close();
ob_end_flush(); ?>
