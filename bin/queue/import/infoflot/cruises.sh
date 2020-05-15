#!/bin/bash
BASEDIR=$(dirname "$0")
php $BASEDIR/../../../../artisan --env=production queue:listen --tries=3 --queue=import_infoflot_cruises --timeout=0