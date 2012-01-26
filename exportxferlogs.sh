#!/bin/bash
timestamp=`date +%Y%m%d_%H:%M:%S`
/usr/bin/php /<ST root>/bin/dumplogs.php > /<Log Dir>/stxferlog_$timestamp.log
