This post pertains to anyone that is administering a SecureTransport server.  This is a product for secure file transfers over HTTPS, SFTP, FTPS, AS2, and optionally insecured protocols such as HTTP and FTP with real-time PGP encryption/decryption.  With the never ceasing SSH brute force attacks among other garbage logs such as password has expired messages (152 times a minute) the logging can be pretty immense.  Add to that issue the fact that the search tool built into the product sucks for event log queries, we needed an alternative.  Here is how I achieved dumping the logs to a text file for grepping to expedite the incident reporting.

On the edge server

<ST root folder>/bin/cleanstdb.php
```php
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
```

All that this PHP script does is go through all events and delete entries that have in the past filled the database to the point of filling the partition.  Obviously for compliance and retention you need to keep some record of these events and that is not shown in this particular example, you’ll have to add your own logic or backup the raw logs frequently enough that you have that.

<ST root folder>/bin/cleanstdb.sh
```bash
#!/bin/bash
/usr/bin/php /<ST root>/bin/cleanstdb.php > /tmp/cleanstdb.out
```

This just runs the php script and outputs any error messages it encounters.  I link this script into cron.hourly to do an hourly cleaning of the database and it seems to have a tremendous impact on keeping the partition from filling.

The next problem to solve is exporting the valuable information from the event log database and putting it into a human readable, and greppable format.

<ST root folder>/bin/exporteventlog.php
```php
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
```
<ST root folder>/bin/exporteventlog.sh
```bash
#!/bin/bash
timestamp=`date +%Y%m%d_%H:%M:%S`
/usr/bin/php /<ST root>/dumpeventlog.php > /<Log Dir>/steventlog_$timestamp.log
```
This shell script calls the PHP script to dump the valuable fields to a text file, in my case I have this on a cron job to happen every 4 hours, as I’ve seen some attacks or warnings that have rotated the entire database within 6 hours.

The back-end server scripts are almost identically the same, the only difference is the database structure between an edge and back-end server differ a bit.  As the edge reports a lot more connection errors and attack attempts than the backend, the cleanup script isn’t required on the back-end server.

<ST root>/bin/exportxferlogs.php
```php
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
```
<ST root>/bin/exportxferlogs.sh
```bash
#!/bin/bash
timestamp=`date +%Y%m%d_%H:%M:%S`
/usr/bin/php /<ST root>/bin/dumplogs.php > /<Log Dir>/stxferlog_$timestamp.log
```
As this only logs transfers which require logging in successfully this can be cron’d to run at a less frequent schedule than the edge server.

Despite its event log search utility being pretty bad, SecureTransport is an extremely modifiable application that has proven to be fairly resilient.  In the future I might post some more hacks and scripts used to add functionality or increase its stability.
