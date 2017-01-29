<?php
// buffer the output:
ob_start();
// begin a session
  session_start();
    $requirelogin = 0;

/* First output the head tags and common head html */ ?>
<html lang="en">
  <head>

<?php
if( isset($_GET['page'])){
  $page = $_GET['page'];
}else{
  $page="";
}
include 'head.html';
?>

</head>

<?php /* Now we'll do the body tag and some common stuff... */ ?>
<body>

<?php /* ------------ Google Tag Manager -------------- */ ?>
    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-KZKWTX"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
         j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
         '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
         })(window,document,'script','dataLayer','GTM-KZKWTX');
    </script>
<?php /*----------- End Google Tag Manager ------------ */ ?>
<?php /* ------------------- FB SDK -------------------- */ ?>
	  <div id="fb-root"></div>
	  <script>
	  window.fbAsyncInit = function () {
	    FB.Event.subscribe('edge.create', function(href, widget) {
	    ga('send','event','Facebook','Like', href);
	  });
	  };

	  (function(d, s, id)
	  {
	    var js, fjs = d.getElementsByTagName(s)[0];
	    if (d.getElementById(id)) return;
	    js = d.createElement(s); js.id = id;
	    js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.6&appId=161403293114";
	    fjs.parentNode.insertBefore(js, fjs);
	  }(document, 'script', 'facebook-jssdk'));
	  </script>
<?php
    /* --------------- End FB SDK ------------------- */

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
$date = $date->format("y:m:d h:i:s");
$mmess = get_ip_address();
$page = strtolower($page);
if($page=='')
{
	// visit with no page specified, so log visit...

    write_logfile( $date.": New visitor: ".$mmess );

	foreach ($_SERVER as $name => $value) {
	    write_logfile($name.": ".$value);
	}

	// send them to the home page:
	$page='home';
}

// All home requests logged to usdixons (except when local testing!)
if( $page=='home' && $mmess !='::1')
{
  $cu = curl_init();
  // look up location details of the ip we have...
  curl_setopt($cu, CURLOPT_URL, "ipinfo.io/".$mmess);
  curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);

  $georesult = curl_exec($cu);
  $geodata = json_decode($georesult, true);

  $udmessage = $geodata['city'].", ".$geodata['region'].", ".$geodata['country'];
  $udmessage = str_replace(" ","%20",$udmessage); // to properly format the url
  curl_setopt($cu, CURLOPT_URL, "general.usdixons.com:6097/syslog?type=INFO&message=".$udmessage);
  curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
  $wresult = curl_exec($cu);

  curl_close($cu);
}
// write out to visitors log
$visfile = file_put_contents('visitors.txt', "<p>".$date.": ".$mmess.": ".$page."</p>".PHP_EOL, FILE_APPEND | LOCK_EX );

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
if( $requirelogin )
{
  $sessionOK=false;
  // it will never let you open index(login) page if session is set
  if ( isset($_SESSION['user']) ) {
   //echo 'You are logged in as '.$_SESSION['user'];
   if (isset($_SESSION['LAST_ACTIVITY']) || (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
       // last request was more than 30 minutes ago
   }
   else {
     $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
     $sessionOK=true;
   }
  }
  if(!$sessionOK){
    //echo 'NO SESSION DETAILS...';
    session_unset();     // unset $_SESSION variable for the run-time
    session_destroy();   // destroy session data in storage
    include 'logintest.html';
  }
}
if( !$requirelogin || isset($_SESSION['user'])!="" ){
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
  // Do nothing - not logged in
}
include 'foot.html';
?>

</body>
<?php ob_end_flush(); ?>
