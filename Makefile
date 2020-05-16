
all: vendor/autoload.php wikipedia/portuguese wikipedia/english
	@echo "=> all tables generated!"

install:
	bin/composer install

vendor/autoload.php:
	bin/composer install

download/brasil/minister:
	@echo "=> downloading current data from Brazil's Ministry of Health"
	php bin/download-minister.php

download/brasil/rs:
	@echo '=> downloading Brasil/RS data from BrasilIO'
	php bin/download-brasilio.php rs

wikipedia/english:
	@echo '=> generating english wikipedia artiles'
	php bin/wikipedia.php en

wikipedia/portuguese:
	@echo '=> generating portuguese wikipedia articles'
	php bin/wikipedia.php pt

maps:
	@echo '=> creating maps'
	make maps/brasil

maps/brasil:
	@echo '=> creating COVID map for Brasil states'
	make maps/brasil/rs

maps/brasil/rs: download/brasilio/rs
	@echo '=> creating COVID map for Rio Grande do Sul'
	php bin/maps.php rs
