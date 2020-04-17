
all: vendor/autoload.php portuguese english
	@echo "=> all tables generated!"

install:
	bin/composer install

vendor/autoload.php:
	bin/composer install

download:
	@echo '=> downloading current data'
	php bin/download-data.php

english: download
	@echo '=> generating english table'
	php bin/english.php

portuguese: download
	@echo '=> generating english table'
	php bin/portuguese.php
