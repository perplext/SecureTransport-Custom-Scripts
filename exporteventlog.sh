#!/bin/bash
timestamp=`date +%Y%m%d_%H:%M:%S`
/usr/bin/php /<ST root>/dumpeventlog.php > /<Log Dir>/steventlog_$timestamp.log
