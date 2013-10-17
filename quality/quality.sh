#!/bin/sh

phpunit --configuration quality/phpunit_pgsql.xml --coverage-text

php PHP_CodeSniffer/scripts/phpcs --standard=quality/cs_ruleset.xml vendor/ErrantWorks
