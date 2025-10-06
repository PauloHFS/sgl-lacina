SHELL := /bin/bash

COMPOSE_FILE  := docker/docker-compose.prod.yml
APP_CONTAINER := sgl-lacina_app_prod
APP_CONTAINER_DEV := sgl-lacina_app_dev
DB_CONTAINER  := sgl-lacina_db_prod

BACKUP_DIR   := ./backups

ROLLBACK_STATE_FILE := ./.rollback_commit_hash
COMMITS ?= 1

.DEFAULT_GOAL := help

# ==============================================================================
# --- Comandos Principais ---
# ==============================================================================

.PHONY: help
help:
	@echo "🎯 SGL-LaCInA Management Makefile"
	@echo ""
	@echo "Uso: make [comando] [ARG=valor]"
	@echo ""
	@echo "Comandos Principais:"
	@echo "  deploy          - Executa o deploy com backup (detalhes: 'make help-deploy')."
	@echo "  rollback        - Reverte o código para um commit anterior (detalhes: 'make help-rollback')."
	@echo "  backup          - Cria um backup completo do banco de dados e arquivos."
	@echo "  restore         - Restaura a partir de um backup (detalhes: 'make help-restore')."
	@echo "  health-check    - Verifica a saúde da aplicação e do host."
	@echo ""
	@echo "Gerenciamento de Containers:"
	@echo "  start           - Inicia os containers em background."
	@echo "  stop            - Para e remove os containers."
	@echo "  restart         - Reinicia os containers."
	@echo "  status          - Exibe o status dos containers."
	@echo "  logs            - Exibe os logs dos containers em tempo real."
	@echo ""
	@echo "Utilitários e Ajuda:"
	@echo "  help-deploy     - Exibe ajuda detalhada para o comando 'deploy'."
	@echo "  help-rollback   - Exibe ajuda detalhada para o comando 'rollback'."
	@echo "  help-restore    - Exibe ajuda detalhada para o comando 'restore'."
	@echo "  shell           - Acessa o terminal (bash) do container da aplicação."
	@echo "  db              - Acessa o cliente psql do container do banco de dados."
	@echo "  clean           - Remove recursos Docker não utilizados."
	@echo "  list-backups    - Lista os arquivos de backup disponíveis."
	@echo "  reset-dev       - (DEV) Refaz as migrações e seeders."
	@echo ""

.PHONY: deploy
deploy: backup pull stop-app build start post-deploy
	@echo "🎉 Deploy concluído com sucesso!"

.PHONY: backup
backup: create-backup-dir backup-db backup-files backup-env clean-old-backups
	@echo "SUCCESS: Backup concluído."

.PHONY: restore
restore:
	@if [ -z "$(DB)" ]; then echo "❌ ERRO: Argumento 'DB=<arquivo>' é obrigatório."; $(MAKE) help-restore; exit 1; fi
	@if [ ! -f "$(BACKUP_DIR)/$(DB)" ]; then echo "❌ ERRO: Arquivo de banco de dados não encontrado."; exit 1; fi
	@if [ "$(CONFIRM)" != "true" ]; then echo "⚠️  ATENÇÃO: Ação destrutiva. Use CONFIRM=true para continuar."; $(MAKE) help-restore; exit 1; fi
	@echo "✅ Confirmação recebida. Iniciando restauração..."
	$(MAKE) stop-app
	$(MAKE) restore-db
	@if [ -n "$(FOTOS)" ]; then $(MAKE) restore-files; fi
	$(MAKE) start
	@echo "🎉 Restore concluído!"

.PHONY: rollback
rollback:
	@if [ "$(CONFIRM)" != "true" ]; then echo "⚠️  ATENÇÃO: Ação destrutiva. Use CONFIRM=true para reverter o código."; $(MAKE) help-rollback; exit 1; fi
	@echo "✅ Confirmação recebida. Iniciando rollback de $(COMMITS) commit(s)..."
	@git rev-parse HEAD > $(ROLLBACK_STATE_FILE)
	$(MAKE) backup-db
	@git reset --hard HEAD~$(COMMITS)
	$(MAKE) deploy
	@echo "🎉 Rollback concluído! Para desfazer, use: make rollback-undo CONFIRM=true"

.PHONY: rollback-undo
rollback-undo:
	@if [ ! -f "$(ROLLBACK_STATE_FILE)" ]; then echo "❌ ERRO: Nenhum estado de rollback salvo."; exit 1; fi
	@if [ "$(CONFIRM)" != "true" ]; then echo "⚠️  ATENÇÃO: Ação destrutiva. Use CONFIRM=true para desfazer o rollback."; exit 1; fi
	@echo "INFO: Desfazendo o rollback..."
	@git reset --hard $$(cat $(ROLLBACK_STATE_FILE))
	@rm $(ROLLBACK_STATE_FILE)
	$(MAKE) deploy
	@echo "🎉 Rollback desfeito com sucesso."

.PHONY: health-check
health-check:
	@echo "🩺 Verificando saúde da aplicação..."
	$(MAKE) status
	@echo "---"
	@echo "🔎 Verificações de Serviço:"
	@if curl -sf http://localhost:16000 > /dev/null; then echo "✅ Aplicação web: OK"; else echo "❌ Aplicação web: FALHOU"; fi
	@if docker exec $(DB_CONTAINER) pg_isready -U sail > /dev/null 2>&1; then echo "✅ Banco de dados: OK"; else echo "❌ Banco de dados: FALHOU"; fi
	@if docker compose -f $(COMPOSE_FILE) ps queue | grep -q "running"; then echo "✅ Queue worker: OK"; else echo "❌ Queue worker: FALHOU"; fi
	@if docker compose -f $(COMPOSE_FILE) ps scheduler | grep -q "running"; then echo "✅ Scheduler: OK"; else echo "❌ Scheduler: FALHOU"; fi
	@echo "---"
	@echo "💾 Verificações do Host:"
	@DISK_USAGE=$$(df -h / | awk 'NR==2 {print $$5}'); echo "✅ Espaço em disco: $${DISK_USAGE} usado"
	@MEM_USAGE=$$(free | grep Mem | awk '{printf "%.0f%%", $$3/$$2 * 100.0}'); echo "✅ Uso de memória: $${MEM_USAGE}"


# ==============================================================================
# --- Gerenciamento de Containers ---
# ==============================================================================

.PHONY: start
start:
	@echo "🚀 Iniciando containers..."
	@docker compose -f $(COMPOSE_FILE) up -d

.PHONY: stop
stop:
	@echo "⏹️  Parando e removendo containers..."
	@docker compose -f $(COMPOSE_FILE) down --remove-orphans

.PHONY: restart
restart:
	@echo "🔄 Reiniciando containers..."
	@docker compose -f $(COMPOSE_FILE) restart

.PHONY: status
status:
	@docker compose -f $(COMPOSE_FILE) ps

.PHONY: logs
logs:
	@echo "📋 Exibindo logs (Pressione Ctrl+C para sair)..."
	@docker compose -f $(COMPOSE_FILE) logs -f


# ==============================================================================
# --- Utilitários e Ajuda ---
# ==============================================================================

.PHONY: help-deploy
help-deploy:
	@echo " Ajuda para: make deploy"
	@echo "--------------------------------------------------------------------------------"
	@echo " Executa o processo de deploy completo e automatizado."
	@echo " O backup é realizado automaticamente antes de qualquer alteração."
	@echo ""
	@echo " Passos executados:"
	@echo "   1. backup       - Cria backup do banco, arquivos .env e fotos."
	@echo "   2. pull         - Atualiza o código-fonte via 'git pull'."
	@echo "   3. stop-app     - Para os containers da aplicação."
	@echo "   4. build        - Reconstrói as imagens Docker se necessário."
	@echo "   5. start        - Inicia todos os containers."
	@echo "   6. post-deploy  - Executa migrações, otimiza caches e ajusta permissões."
	@echo ""
	@echo " Uso: make deploy"
	@echo "--------------------------------------------------------------------------------"

.PHONY: help-restore
help-restore:
	@echo " Ajuda para: make restore"
	@echo "--------------------------------------------------------------------------------"
	@echo " Restaura a aplicação a partir de arquivos de backup."
	@echo " Esta é uma operação destrutiva e exige confirmação."
	@echo ""
	@echo " Argumentos:"
	@echo "   DB=<arquivo>      (Obrigatório) Nome do arquivo de backup do banco de dados."
	@echo "   FOTOS=<arquivo>   (Opcional)    Nome do arquivo de backup das fotos."
	@echo "   CONFIRM=true      (Obrigatório) Confirmação explícita para executar a operação."
	@echo ""
	@echo " Passos:"
	@echo "   1. Encontre os backups disponíveis com: make list-backups"
	@echo "   2. Execute o comando com os nomes dos arquivos."
	@echo ""
	@echo " Exemplo:"
	@echo "   make restore DB=db_backup_20250910_123000.sql.gz CONFIRM=true"
	@echo "--------------------------------------------------------------------------------"

.PHONY: help-rollback
help-rollback:
	@echo " Ajuda para: make rollback"
	@echo "--------------------------------------------------------------------------------"
	@echo " Reverte o código-fonte da aplicação para um commit anterior."
	@echo " Esta é uma operação de risco e exige confirmação."
	@echo ""
	@echo " Argumentos:"
	@echo "   COMMITS=<numero>  (Opcional) Número de commits a reverter. Padrão: 1."
	@echo "   CONFIRM=true      (Obrigatório) Confirmação explícita para executar a operação."
	@echo ""
	@echo " O processo inclui um backup do banco de dados antes da reversão e um deploy completo."
	@echo ""
	@echo " Exemplos:"
	@echo "   make rollback CONFIRM=true"
	@echo "   make rollback COMMITS=3 CONFIRM=true"
	@echo ""
	@echo " Para desfazer um rollback, use: make rollback-undo CONFIRM=true"
	@echo "--------------------------------------------------------------------------------"

.PHONY: shell
shell:
	@echo "🐚 Acessando o terminal do container da aplicação..."
	@docker exec -it $(APP_CONTAINER) sh

.PHONY: db
db:
	@echo "🗄️  Acessando o cliente psql..."
	@docker exec -it $(DB_CONTAINER) psql -U sail -d laravel

.PHONY: clean
clean:
	@echo "🧹 Limpando recursos Docker não utilizados..."
	@docker system prune -af

.PHONY: list-backups
list-backups:
	@echo "--- Backups Disponíveis em $(BACKUP_DIR)/ ---"
	@ls -1 $(BACKUP_DIR)

.PHONY: reset-dev
reset-dev:
	@echo "DEV: Refazendo migrações e seeders..."
	@docker exec $(APP_CONTAINER_DEV) php artisan migrate:fresh --seed
	@docker exec $(APP_CONTAINER_DEV) php artisan db:seed --class=DevelopmentSeeder
	@docker exec $(APP_CONTAINER_DEV) php artisan horarios:criar-usuarios
	@docker exec $(APP_CONTAINER_DEV) php artisan optimize


# ==============================================================================
# --- Alvos Internos (usados por outros comandos) ---
# ==============================================================================

.PHONY: pull
pull:
	@echo "INFO: Atualizando código do repositório..."
	@git pull origin main

.PHONY: stop-app
stop-app:
	@echo "INFO: Parando containers da aplicação..."
	@docker compose -f $(COMPOSE_FILE) stop app scheduler queue nginx

.PHONY: build
build:
	@echo "INFO: Construindo imagens Docker..."
	@docker compose -f $(COMPOSE_FILE) build

.PHONY: post-deploy
post-deploy: migrate optimize permissions scout-import
	@echo "SUCCESS: Tarefas de pós-deploy concluídas."

.PHONY: migrate
migrate:
	@echo "INFO: Executando migrações do banco de dados..."
	@docker exec $(APP_CONTAINER) php artisan migrate --force

.PHONY: optimize
optimize:
	@echo "INFO: Otimizando a aplicação (cache)..."
	@docker exec $(APP_CONTAINER) php artisan optimize

.PHONY: permissions
permissions:
	@echo "INFO: Ajustando permissões de diretórios..."
	@docker exec $(APP_CONTAINER) chown -R www-data:www-data storage bootstrap/cache
	@docker exec $(APP_CONTAINER) chmod -R 775 storage bootstrap/cache

.PHONY: scout-import
scout-import:
	@echo "INFO: Importando dados para o Meilisearch..."
	@docker exec $(APP_CONTAINER) php artisan scout:import "App\Models\OrgaoEmissor"

.PHONY: create-backup-dir
create-backup-dir:
	@mkdir -p $(BACKUP_DIR)

.PHONY: backup-db
backup-db:
	@echo "INFO: Fazendo backup do banco de dados..."
	@docker exec $(DB_CONTAINER) pg_dump -U sail -d laravel | gzip > $(BACKUP_DIR)/db_backup_$(shell date +%Y%m%d_%H%M%S).sql.gz

.PHONY: backup-files
backup-files:
	@echo "INFO: Fazendo backup dos arquivos..."
	@if docker exec $(APP_CONTAINER) [ -d "storage/app/public/fotos" ]; then \
		docker exec $(APP_CONTAINER) tar -czf - -C storage/app/public fotos/ > $(BACKUP_DIR)/files_backup_$(shell date +%Y%m%d_%H%M%S).tar.gz; \
	else \
		echo "WARN: Diretório de fotos não encontrado. Pulando."; \
	fi

.PHONY: backup-env
backup-env:
	@echo "INFO: Fazendo backup do arquivo .env..."
	@cp .env $(BACKUP_DIR)/env_backup_$(shell date +%Y%m%d_%H%M%S).env

.PHONY: clean-old-backups
clean-old-backups:
	@echo "INFO: Removendo backups com mais de 7 dias..."
	@find $(BACKUP_DIR) -name "*backup_*" -mtime +7 -exec rm {} \;

.PHONY: restore-db
restore-db:
	@echo "🗄️  Restaurando banco de dados..."
	@if [[ $(DB) == *.gz ]]; then \
		gunzip -c $(BACKUP_DIR)/$(DB) | docker exec -i $(DB_CONTAINER) psql -U sail -d laravel; \
	else \
		cat $(BACKUP_DIR)/$(DB) | docker exec -i $(DB_CONTAINER) psql -U sail -d laravel; \
	fi

.PHONY: restore-files
restore-files:
	@echo "📸 Restaurando fotos..."
	@if [ ! -f "$(BACKUP_DIR)/$(FOTOS)" ]; then echo "❌ ERRO: Arquivo de fotos não encontrado."; exit 1; fi
	@cat $(BACKUP_DIR)/$(FOTOS) | docker exec -i $(APP_CONTAINER) tar -xzf - -C storage/app/public/
	@docker exec $(APP_CONTAINER) chown -R www-data:www-data storage/app/public/fotos
