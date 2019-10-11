<?php
	// buffer the output:
	ob_start();
	// begin a session
	session_start();

	require_once 'utility.php';
	$ipaddr = get_ip_address();
	$date = new DateTime();
	$date = $date->format("y/m/d H:i:s");

	// load configuration info:
	require_once 'configed.php'; // configuration settings and database stuff
	require_once 'visitor.php';
	require_once 'login.php';


	//set error handler
	set_error_handler("customError");

	/* see if we support mod_rewrite:
	$mods = apache_get_modules();
	if (in_array('mod_rewrite', $mods)) {
		echo "Yes, Apache supports mod_rewrite.";
	}
	else {
		echo "Apache is not loading mod_rewrite.";
	}*/


	// database connection:
	$msdb_connection = init_dataconnection($servername, $sqldb, $sqluser, $sqlpass);

	// get name of requested page, which was passed as the 'page' parameter:
	if( isset($_GET['page']) && $_GET['page'] != ""){
		$page = strtolower($_GET['page']);
	}else{
		// send them to the home page:
		$page='home';
	}

	/* Output the head tags and common head html */
	include 'head.html';
	/* Now we'll do the body tag and some common stuff... */
	include 'commonbody.html';
	// Put the banner at the top of the page
	include 'banner.html';
	if( $msdb_connection == false )
	{
		include 'servererror.html';
	}
	// debugging: cwd, current user, attempt to log something
	//echo "<p>".getcwd().":".posix_getpwuid(posix_geteuid())['name'].":".write_logfile("All good in the hood")."</p>";

	// Now decide exactly what page contents to show...
	// If the page exists, and no login is required for it, then proceed to show it
	// if it doesn't exist, show the page missing text
	// if login is required, force the login page
	if(session_loginproceed($msdb_connection, $page))
	{ //It's OK to include the actual contents then...
		//write_logfile("Visitor ".$ipaddr." to page: ".$page);
		//sendtomirror($ipaddr, $page);
		if (file_exists ( $page.'.html' ) )
		{ // page exists, let's go...
			include $page.'.html';
		}
		else
		{ // page doesn't exist, so tell the visitor
			/*
			echo '<p>Page: "'.$page.'"</p>';
			if( isset($_GET['data']) )
			{
				echo '<p>Data: '.$_GET['data'].'</p>';
			}
			else{
				echo'no data field';
			}*/
			include 'pagemissing.html';
		}
	}
	else
	{ // not logged in, but needs to be...
		include 'login.html';
	}

	// now include the common page footer - social links etc.
	include 'foot.html';

	// log this visit
	//visitor_logvisit($ipaddr, $date, $page."/".$_GET['data']);

	$msdb_connection->close();
	ob_end_flush();
?>
