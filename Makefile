# ─── Lysandro Makefile ───────────────────────────────────────────────────────
.PHONY: help up down restart logs shell db-shell composer \
        migrate fixtures jwt-keys test lint

help: ## Zeigt diese Hilfe
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ─── Docker ──────────────────────────────────────────────────────────────────
up: ## Container starten
	docker compose up -d --build

down: ## Container stoppen
	docker compose down

restart: ## Container neu starten
	docker compose restart

logs: ## Logs streamen (alle Container)
	docker compose logs -f

logs-php: ## Nur PHP-FPM Logs
	docker compose logs -f php

logs-worker: ## Nur Messenger Worker Logs
	docker compose logs -f messenger_worker

# ─── Symfony Backend ─────────────────────────────────────────────────────────
shell: ## Shell im PHP Container
	docker compose exec php bash

composer: ## composer install im Container ausführen
	docker compose exec php composer install

migrate: ## Doctrine Migrations ausführen
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

migrate-diff: ## Migration aus Entity-Änderungen generieren
	docker compose exec php php bin/console doctrine:migrations:diff

fixtures: ## Test-Fixtures laden (dev only)
	docker compose exec php php bin/console doctrine:fixtures:load --no-interaction

cache-clear: ## Symfony Cache leeren
	docker compose exec php php bin/console cache:clear

jwt-keys: ## JWT Schlüsselpaar generieren (einmalig nötig)
	docker compose exec php mkdir -p config/jwt
	docker compose exec php openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:lysandro_dev
	docker compose exec php openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:lysandro_dev

routes: ## Alle Routen anzeigen
	docker compose exec php php bin/console debug:router

# ─── Datenbank ───────────────────────────────────────────────────────────────
db-shell: ## MySQL Shell
	docker compose exec mysql mysql -u lysandro -psecret lysandro

db-create: ## Datenbank erstellen
	docker compose exec php php bin/console doctrine:database:create --if-not-exists

# ─── Tests ───────────────────────────────────────────────────────────────────
test: ## PHPUnit Tests ausführen
	docker compose exec php php bin/phpunit

test-unit: ## Nur Unit Tests
	docker compose exec php php bin/phpunit --testsuite Unit

test-integration: ## Nur Integration Tests
	docker compose exec php php bin/phpunit --testsuite Integration

# ─── URLs (Info) ─────────────────────────────────────────────────────────────
urls: ## Alle lokalen URLs anzeigen
	@echo ""
	@echo "  Backend API:  http://localhost:8080"
	@echo "  Adminer:      http://localhost:8090  (Server: mysql, User: lysandro, PW: secret)"
	@echo "  Mailpit:      http://localhost:8025"
	@echo ""
