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
 $result = mysql_query("delete from logging_event where rendered_message LIKE 'SSH: Sent SSH_MSG_USERAUTH_INFO_REQUEST%';");
 if (!$result) {
 echo("<P>Error performing query: " .
 mysql_error() . "</P>");
 exit();
 }
 $result = mysql_query("delete from logging_event where rendered_message LIKE 'FTP session starting from <NAT address>';");
 if (!$result) {
 echo("<P>Error performing query: " .
 mysql_error() . "</P>");
 exit();
 }
 mysql_close($dbcnx);
?>
