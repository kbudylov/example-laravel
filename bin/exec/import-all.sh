#!/bin/bash
BASEDIR=$(dirname "$0")
php $BASEDIR/../../artisan --env=production import:all