#!/bin/sh
dir=$(cd `dirname $0` && pwd)
$dir/../vendor/bin/tester -p php -c $dir/php.ini-unix $dir/alchemist/
