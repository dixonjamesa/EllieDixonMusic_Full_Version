<?php
// login.php

function session_loginproceed($msdb_conn, $page)
{
	global $sessionTimeout, $loginrequired, $sessiontimedout; // from configed.php
	// assume no login required:
	$requirelogin=false;
	// look to see if the page requires a login, as defined by $loginrequired in configed.php:
	$arrlength = count($loginrequired);

	for($x = 0; $x < $arrlength; $x++)
	{
		if(strcasecmp($page,$loginrequired[$x])==0)
		{
			$requirelogin = true;
		}
	}
	if( !$requirelogin )
	{
		return true;
	}
	// first let's see if there's a session, and whether it's timed out or not. If that's
	// the case, then there's no need to login and we can just return true...
	if ( isset($_SESSION['user']) )
	{
		//echo 'You are logged in as '.$_SESSION['user'];
		if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $sessionTimeout))
		{
			// last request was more than specified session timeout
			//echo 'Session timed out';
			session_logout();
			$sessiontimedout = true;
		}
		else
		{
			$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
			return true;
		}
	}
	return false;
}

function session_login($email, $pass)
{
	global $msdb_connection;
	$passhash = hash('sha256', $pass); // password hashing using SHA256

	$res=$msdb_connection->query("SELECT userId, userFirst, userLast, userPass, userAuth FROM users WHERE userEmail='$email'");
	$row=$res->fetch_array();
	$count = $res->num_rows;
	$res->close();
	// if uname/pass correct it should return just the 1 row
	if( $count == 1 && $row['userPass']==$passhash )
	{	/* ALL OK ... */
		$_SESSION['user'] = $row['userId'];
		$_SESSION['userAuth'] = $row['userAuth'];
		$_SESSION['userFirst'] = $row['userFirst'];
		$_SESSION['userLast'] = $row['userLast'];
		$_SESSION['userRegDate'] = $row['reg_date'];
		return true;
	}
	return false;
}

function session_loginisadmin()
{
    if( $_SESSION['userAuth'] == "Full" )
	{
        return true;
    }
	return false;
}

function session_logout()
{
    session_unset();     // unset $_SESSION variable for the run-time
    session_destroy();   // destroy session data in storage
}

function password_reset($email)
{
	// test this is a known user first:
	global $msdb_connection;
	$res=$msdb_connection->query("SELECT userId FROM users WHERE userEmail='$email'");
	$row=$res->fetch_array();
	$count = $res->num_rows;
	$res->close();
	if( $count != 1 ) return false;
	// set up a new password
	$alphabet = "abcdefghijklmnopqrstuwxyz0123456789ABCDEFGHIJKLMNOPQRSTUWXYZ0123456789 _£!$%*()<>";
	$newpass = array();
	$alen = strlen($alphabet)-1;
	for ($i = 0; $i < 15; $i++) {
			$n = rand(0, $alen);
			$newpass[$i] = $alphabet[$n];
	}
	$newpass = implode($newpass); // convert from array to string
	// change the password
	$passhash = hash('sha256', $newpass);
	$res=$msdb_connection->query("UPDATE users SET userPass='".$passhash."' WHERE userId='". $row['userId']."'");
	if($res == 1 )
	{
		// email it to the address
		$message = "Your new password is ".$newpass;
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers .= "From: admin@elliedixonmusic.com <admin@elliedixonmusic.com>\r\n";
		$headers .= "Reply-To: admin@elliedixonmusic.com <admin@elliedixonmusic.com>\r\n";
		$headers .= "Return-Path: admin@elliedixonmusic.com <admin@elliedixonmusic.com>\r\n";
		$headers .= "X-Priority: 3\r\n";
		$headers .= "X-MSMail-Priority: Normal\r\n";
		if (mail($email, "Account password change request", $message, $headers ) )
		{
			// reset sent
			return true;
		}
	}
	return false;
}
?>
