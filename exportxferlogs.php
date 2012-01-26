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
 $result = mysql_query("select id, endTime, protocol, remoteHost, accountName, localFile, filename, filesize, status from TransferStatus order by startTime");
 if (!$result) {
 echo("<P>Error performing query: " .
 mysql_error() . "</P>");
 exit();
 }
 while ( $row = mysql_fetch_array($result) ) {
 echo $row["id"] . " " . date('mdY-H:i:s', $row["endTime"]/1000) . " " . $row["protocol"] . " " . $row["remoteHost"] . " " . $row["accountName"] . " " .
 $row["localFile"] . " " . $row["filename"] . " " . $row["filesize"] . " " . $row["status"] . "\r\n";
 }
 mysql_free_result($result);
 mysql_close($dbcnx);
?>
