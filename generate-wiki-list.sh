#!/bin/bash

// Generates a list of wikis
// To be run on tool labs
mysql --defaults-file="${HOME}"/replica.my.cnf -h s7.labsdb -e 'SELECT dbname, url, slice FROM wiki;' --batch --raw meta_p > wikis.tsv
