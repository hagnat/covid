
all: vendor/autoload.php portuguese english
	@echo "=> all tables generated!"

install:
	bin/composer install

vendor/autoload.php:
	bin/composer install

english:
	@echo '=> generating english table'
	php bin/english.php

portuguese:
	@echo '=> generating english table'
	php bin/portuguese.php
