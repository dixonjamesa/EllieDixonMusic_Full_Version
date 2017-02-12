<?php
// visitor.php

// visitor_log_visit
// record a visit from as specific IP to a specific page
// adds new visitors to the visitor table
// then adds the specific page hit to the pagehits table
function visitor_logvisit($ip, $date, $page)
{
  global $msdb_connection;

  {
    // see if the ip is already in the visitors table:
    $result=$msdb_connection->query("SELECT id, ip FROM visitors WHERE ip='$ip'");
    if( $result->num_rows == 0 ) {
      // nope, so let's add the details:
      if( $ip != '::1' )
      {
        $cu = curl_init();
        // look up location details of the ip we have...
        curl_setopt($cu, CURLOPT_URL, "ipinfo.io/".$ip);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);

        $georesult = curl_exec($cu);
        $geodata = json_decode($georesult, true);

        $sql = "INSERT INTO visitors (ip, city, region, country, host, date)
        VALUES ('".$ip."', '".$geodata['city']."', '".$geodata['region']."', '".$geodata['country']."', '".$geodata['hostname']."', '".$date."')";

        if ($msdb_connection->query($sql) === TRUE) {
            //echo "New record created successfully";
        } else {
            //echo "Error: " . $sql . "<br>" . $msdb_connection->error;
        }
        // also log visit details...
        write_logfile( $date.": New visitor: ".$ip );

        foreach ($_SERVER as $name => $value) {
          write_logfile($name.": ".$value);
        }
      }
      else {
        $sql = "INSERT INTO visitors (ip, city, region, country, host, date)
        VALUES ('::1', 'Cambridge', 'Cambs', 'UK', 'Dixonia', '".$date."')";

        $msdb_connection->query($sql);
      }
      // send to mirror display as a brand new visitor
      $udmessage = $geodata['city'].", ".$geodata['region'].", ".$geodata['country'];
      $udmessage = str_replace(" ","%20",$udmessage); // to properly format the url
      curl_setopt($cu, CURLOPT_URL, "general.usdixons.com:6097/syslog?type=INFO&message=".$udmessage);
      curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
      $wresult = curl_exec($cu);

      curl_close($cu);
    }
    // write out to visitors log
    //$visfile = file_put_contents('visitors.txt', "<p>".$date.": ".$ip.": ".$page."</p>".PHP_EOL, FILE_APPEND | LOCK_EX );

    // and write the pagehit:
    $sql = "INSERT INTO pagehits (ip, url, date) VALUES ('".$ip."', '".$page."', '".$date."')";

    if ($msdb_connection->query($sql) === TRUE) {
        //echo "New record created successfully";
    } else {
        //echo "Error: " . $sql . "<br>" . $msdb_connection->error;
    }

  }
}

?>
