#!/usr/bin/env bash

# Clone contents of master db to stage db
mysqldump brainbo4_tmlpstats | mysql brainbo4_tmlpstats_stage
