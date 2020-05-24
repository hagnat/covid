COMPOSER ?= bin/composer
.DEFAULT_GOAL := all

.PHONY: all
all: clear composer lint wikipedia

.PHONY: ci
ci: clear lint/composer composer | php-cs-fixer/fix phpstan test
	@echo '=> ready to commit'

.PHONY: clear
clear:
	rm -rf var/tmp/*

.PHONY: composer
composer: vendor/autoload.php

vendor/autoload.php:
	$(COMPOSER) install \
		--no-progress \
		--no-ansi

.PHONY: download/brasil/minister
download/brasil/minister: composer
	@echo "=> downloads current data from Brazil's Ministry of Health"
	php bin/download-minister.php

.PHONY: download/brasil/rs
download/brasil/rs: composer
	@echo '=> downloads Brasil/RS data from BrasilIO'
	php bin/download-brasilio.php rs

.PHONY: wikipedia
wikipedia: composer lint | wikipedia/english wikipedia/portuguese
	@echo '=> generates all wikipedia entries'

.PHONY: wikipedia/english
wikipedia/english: composer download/brasil/minister
	@echo '=> generates english wikipedia artiles'
	php bin/wikipedia.php en

.PHONY: wikipedia/portuguese
wikipedia/portuguese: composer download/brasil/minister
	@echo '=> generates portuguese wikipedia articles'
	php bin/wikipedia.php pt

.PHONY: maps
maps: composer | maps/brasil
	@echo '=> creates maps'

.PHONY: maps/brasil
maps/brasil: composer | maps/brasil/rs
	@echo '=> creates COVID map for Brasil states'

.PHONY: maps/brasil/rs
maps/brasil/rs: composer download/brasilio/rs
	@echo '=> creates COVID map for Rio Grande do Sul'
	php bin/maps.php rs

.PHONY: lint
lint: lint/composer lint/eol lint/trailling-whitespace lint/json lint/php php-cs-fixer/diff

.PHONY: lint/composer
lint/composer:
	@echo '=> validates composer.json and composer.lock'
	$(COMPOSER) validate --strict

.PHONY: lint/eol
lint/eol:
	@echo '=> validates if all files use linux file endings'
	@echo 'TODO: add a eol validator'

.PHONY: lint/json
lint/json:
	@echo '=> validates all JSON files'
	@git ls-files '*.json' | php -R 'echo "$$argn\t\t"; json_decode(file_get_contents($$argn)); if (0 !== json_last_error()) { echo "<-- invalid\n"; exit(1); } else { echo "\n"; }'
	@echo '=> all JSON files are OK!'

.PHONY: lint/php
lint/php: composer
	@echo '=> validates all PHP files'
	@git ls-files '*.php' | xargs bin/lint-php
	@echo '=> all PHP files are OK!'

.PHONY: lint/trailling-whitespace
lint/trailling-whitespace:
	@echo "=> validates if PHP files don't trailling whitespace"
	@! git ls-files '*.php' | xargs grep --files-with-matches --recursive --extended-regexp ' +$$' || ( echo 'Above files have trailling whitespace' && exit 1 )
	@echo "=> all PHP files don't have trailling whitespace!"

.phony: lint/yaml
lint/yaml: | lint/yaml-filetype
	@echo '=> validates all YAML files'

.PHONY: lint/yaml-filetype
lint/yaml-filetype: composer
	@echo '=> validates if all YAML files have the correct filetype'.
	$(eval yml_files := $(shell git ls-files '*.yml'))
	@ if [ $(yml_files) ]; then echo $(yml_files); echo 'Above files should be renamed to *.yaml'; exit 1; fi
	@echo '=> all YAML files have the correct filetype'

.PHONY: lint/yaml-syntax
lint/yaml-syntax:
	@echo '=> validates all YAML files'
	@git ls-files '*.yaml' | sed -r 's|/[^/]+$$||' | sort | uniq | while read folder; do echo -n "$$folder"; bin/console --no-debug --no-interaction  --env=test lint:yaml "$$folder" || exit 1; done
	if [ $(yaml_files) ]; then echo "Files found."; else echo "No files found."; fi

.PHONY: php-cs-fixer/diff
php-cs-fixer/diff: composer
	@echo '=> checks code for standards'
	bin/php-cs-fixer fix --allow-risky=yes --dry-run --diff

.PHONY: php-cs-fixer/fix
php-cs-fixer/fix: composer
	@echo '=> fixes code to standards'
	bin/php-cs-fixer fix --allow-risky=yes

.PHONY: phpstan
phpstan:
	bin/phpstan analyse --no-progress --no-interaction --no-ansi --level=4 src/

.PHONY: test
test: composer
	@echo 'TODO: add phpunit tests'
