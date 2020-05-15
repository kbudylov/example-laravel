#!/bin/bash
BASEDIR=$(dirname "$0")
php $BASEDIR/../../../../artisan --env=production queue:listen --tries=3 --queue=sync_cabins_volgaline --timeout=0
