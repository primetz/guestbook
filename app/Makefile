SHELL := /bin/bash
DOCKER_COMPOSE_FILE_PATH = ./../docker/docker-compose.yaml
PHP_FPM = php-fpm
PHP_FPM_SHELL = zsh
RUN_DEV_PHP_WORKER_COMMAND = "symfony run -d --watch=config,src,templates,vendor symfony console messenger:consume async"

.PHONY: admin tests dev-up dev-down drop prune php

admin:
	@php bin/console doctrine:query:sql -n "INSERT INTO admin VALUES (nextval('admin_id_seq'), 'admin', '[\"ROLE_ADMIN\"]', '\$$2y\$$13\$$fETyH/TyFaxhLtjV7I5qdecQnRNeP3Z4/klS4d9MopSfJ6pEsBfuG')"
	@echo Uername: admin
	@echo Password: admin

tests:
	@php bin/console doctrine:database:drop -n --if-exists --force --env=test
	@php bin/console doctrine:database:create -n --if-not-exists --env=test
	@php bin/console doctrine:migrations:migrate -n --env=test
	@php bin/console doctrine:fixtures:load -n --env=test
	@php bin/phpunit $@

dev-up:
	@docker compose -f $(DOCKER_COMPOSE_FILE_PATH) up -d --build
	@sleep 5
#	@docker exec -ti $(PHP_FPM) php bin/console doctrine:migrations:migrate -n
#	@docker exec -ti $(PHP_FPM) symfony run -d --watch=config,src,templates,vendor symfony console messenger:consume async
	@docker exec -ti $(PHP_FPM) $(PHP_FPM_SHELL)

dev-down:
#	@echo Server shutdown. Please wait...
#	@docker exec -ti $(PHP_FPM) kill $(shell docker exec -ti $(PHP_FPM) pgrep -f $(RUN_DEV_PHP_WORKER_COMMAND))
#	@sleep 5
#	@docker exec -ti $(PHP_FPM) symfony server:status
	@docker container rm -f $(shell docker container ls -qa)

drop:
	@docker image prune -af
	@docker network prune -f
	@docker volume prune -f

prune:
	@docker system prune -af --volumes

php:
	@docker exec -ti $(PHP_FPM) $(PHP_FPM_SHELL)