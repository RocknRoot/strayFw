#!/bin/sh

phpunit --configuration quality/phpunit_pgsql.xml --coverage-text

php php-cs-fixer.phar . --dry-run
