<?php
// login.php

function login_proceed($msdb_conn, $page)
{
  global $loginrequired, $loginfree, $sessiontimeout; // from configed.php
  $requirelogin=false;
  // look to see if the page is excepted using the loginfree array:
  $arrlength = count($loginrequired);

  for($x = 0; $x < $arrlength; $x++) {
      if(strcasecmp($page,$loginrequired[$x])==0) {
        $requirelogin = true;
      }
  }
  if( !$requirelogin ) {
    return true;
  }
  // first let's see if there's a session, and whether it's timed out or not. If that's
  // the case, then there's no need to login and we can just return true...
  if ( isset($_SESSION['user']) )
  {
    //echo 'You are logged in as '.$_SESSION['user'];
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 60*60*24))
    {
      // last request was more than 24 hours ago
      //echo 'Session timed out';
      session_unset();     // unset $_SESSION variable for the run-time
      session_destroy();   // destroy session data in storage
      $sessiontimeout = true;
    }
    else
    {
      $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
      if( $_SESSION['userAuth'] == "Full" ){
        return true;
      }
    }
  }
  return false;
}
?>
