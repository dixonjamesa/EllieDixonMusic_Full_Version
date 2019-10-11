<?php

/* Get the IP address of the client PC that is connecting */
function get_ip_address()
{
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
	global $date;

	$out = $date.":  ".$what;
	$out = $out."(".file_put_contents('./log.txt', $out.PHP_EOL, FILE_APPEND | LOCK_EX ).")";
  return $out;
}

//error handler function
function customError($errno, $errstr) {
  /* do nothing*/
}

function sendtomirror($ip, $page)
{
	// send to mirror display as a brand new visitor
	$cu = curl_init();
	// look up location details of the ip we have...
	curl_setopt($cu, CURLOPT_URL, "ipinfo.io/".$ip);
	curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);

	$georesult = curl_exec($cu);
	$geodata = json_decode($georesult, true);
	$udmessage = $geodata['city'].", ".$geodata['region'].", ".$geodata['country'].":".$page.":".$ip;
	$udmessage = str_replace(" ","%20",$udmessage); // to properly format the url
	curl_setopt($cu, CURLOPT_URL, "general.usdixons.com:6097/syslog?type=INFO&message=".$udmessage);
	curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
	$wresult = curl_exec($cu);

	curl_close($cu);
}

// function to put a horizontal spacer fixed image into the page
function spacer($height, $height_sm, $margin, $image)
{
	echo '<div class="scroll-image-ed hidden-xs" style="background-image:url(\''.$image.'\');opacity:0.7;margin:'.$margin.'px 0px;height:'.$height.'px;"></div>';
	echo '<div class="scroll-image-ed scroll-image-ed-sm visible-xs" style="margin:'.$margin.'px 0px;height:'.$height_sm.'px;"></div>';
}

 ?>
