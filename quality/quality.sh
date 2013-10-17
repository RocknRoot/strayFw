#!/bin/sh

phpunit --configuration phpunit_pgsql.xml --coverage-text

php ../php-cs-fixer.phar ../ --dry-run
