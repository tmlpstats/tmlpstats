#!/bin/bash
#
MYSQLDUMP=mysqldump -u root --password=doesntmatter 

$MYSQLDUMP --no-data tmlpstats_main > /output/010-structure.sql 
$MYSQLDUMP --no-create-info tmlpstats_main \
	migrations accountabilities withdraw_codes > /output/020-lookups.sql
