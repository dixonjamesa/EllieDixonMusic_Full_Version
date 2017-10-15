<?php
//staging.php
// contains additional/overriding staging config settings

// change the base directory for files to be the staging sub-directory:
if( $ipaddr == '::1' ) {
  $homeURL="/EllieDixonMusic/staging";
}
else {
  $homeURL="/staging";
}
