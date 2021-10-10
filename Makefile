.DEFAULT_GOAL := help
.SILENT:

COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## help
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

## docker compose up
up:
	docker-compose up -d

## docker compose down
down:
	docker-compose down

## docker compose down + up
restart:
	docker-compose down
	docker-compose up -d

## enter in app container
app-shell:
	docker-compose exec app bash

## run composer install
composer-install:
	docker-compose exec app composer install

## setup
setup: certs-ssl up composer-install
	make restart
	printf "${COLOR_INFO}Up and running${COLOR_RESET}\n"
	printf "${COLOR_COMMENT}Go to https://localhost:9501${COLOR_RESET}\n"

## run psalm on src dir
psalm:
	docker-compose exec app ./vendor/bin/psalm --show-info=true --no-diff

## generate certificate for https
certs-ssl:
#	openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout certs/https-selfsigned.key -out certs/https-selfsigned.crt
	openssl req -config certs/server.cnf -x509 -nodes -days 3650 -newkey rsa:2048 -keyout certs/https-selfsigned.key -out certs/https-selfsigned.crt

## generate certificate for jwt
certs-jwt:
	ssh-keygen -t ed25519 -f certs/jwt_secret -C chacri_jwt

## install php-cs-fixer
install-php-cs-fixer:
	mkdir -p tools/php-cs-fixer
	composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer

## fix cs
php-cs-fixer-fix:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
