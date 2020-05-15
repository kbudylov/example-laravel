#!/bin/bash
BASEDIR=$(dirname "$0")
php $BASEDIR/../../artisan --env=production queue:listen --tries=3 --queue=b24 --timeout=0