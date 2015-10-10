#!/usr/bin/env bash

# Clone contents of master db to stage db
mysqldump tmlpstats_main | mysql tmlpstats_stage
