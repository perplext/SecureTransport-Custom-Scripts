<?php
 $dbcnx = mysql_connect("127.0.0.1:33060", "root", "tumbleweed");
 if (!$dbcnx) {
 echo( "<P>Unable to connect to the " .
 "database server at this time.</P>" );
 exit();
 }
 mysql_select_db("st", $dbcnx);
 if (! @mysql_select_db("st") ) {
 echo( "<P>Unable to locate the st " .
 "database at this time.</P>" );
 exit();
 }
 $result = mysql_query("select event_id, timestamp, rendered_message, ndc from logging_event where rendered_message NOT LIKE 'SSH: Sent SSH_MSG_USERAUTH_INFO_REQUEST%' order by timestamp;");
 if (!$result) {
 echo("<P>Error performing query: " .
 mysql_error() . "</P>");
 exit();
 }
 while ( $row = mysql_fetch_array($result) ) {
 echo $row["event_id"] . " " . date('mdY-H:i:s', $row["timestamp"]/1000) . " " .
 $row["rendered_message"] . " " . $row["ndc"] . "\r\n";
 }
 mysql_free_result($result);
 mysql_close($dbcnx);
?>
