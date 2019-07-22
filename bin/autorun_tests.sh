#!/usr/bin/env bash
while inotifywait -r -q -e modify *; do
	./vendor/bin/phpunit --bootstrap vendor/autoload.php --testdox tests
done
