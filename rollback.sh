#!/bin/bash

# Rollback Script para SGL LaCInA - Produção
# Versão: 1.0
# Autor: Sistema SGL LaCInA

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funções utilitárias
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar se está no diretório correto
if [ ! -f "docker-compose.prod.yml" ]; then
    log_error "docker-compose.prod.yml não encontrado. Execute este script no diretório raiz do projeto."
    exit 1
fi

echo "🔄 Iniciando rollback do SGL LaCInA - Produção"
echo "=============================================="

# Mostrar commits recentes
log_info "Commits recentes:"
git log --oneline -5

echo ""

# Perguntar quantos commits voltar
read -p "Quantos commits voltar? [1]: " COMMITS_BACK
COMMITS_BACK=${COMMITS_BACK:-1}

# Confirmar rollback
echo ""
log_warning "ATENÇÃO: Esta operação irá:"
echo "- Voltar o código $COMMITS_BACK commit(s)"
echo "- Rebuildar e reiniciar os containers"
echo "- Pode causar perda de dados se houve migrações"
echo ""
read -p "Tem certeza que deseja continuar? [y/N]: " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    log_info "Rollback cancelado."
    exit 0
fi

# Backup atual antes do rollback
log_info "Fazendo backup antes do rollback..."
mkdir -p backups

# Backup do banco
BACKUP_FILE="backups/rollback_backup_$(date +%Y%m%d_%H%M%S).sql"
if docker exec sgl-lacina_db_prod pg_dump -U sail -d laravel > "$BACKUP_FILE" 2>/dev/null; then
    log_success "Backup do banco salvo em: $BACKUP_FILE"
else
    log_warning "Não foi possível fazer backup do banco"
fi

# Salvar commit atual
CURRENT_COMMIT=$(git rev-parse HEAD)
echo $CURRENT_COMMIT > backups/last_commit_before_rollback.txt
log_info "Commit atual salvo: $CURRENT_COMMIT"

# Fazer rollback do código
log_info "Fazendo rollback do código..."
git reset --hard HEAD~$COMMITS_BACK
log_success "Código voltou $COMMITS_BACK commit(s)"

# Parar containers
log_info "Parando containers..."
docker compose -f docker-compose.prod.yml down
log_success "Containers parados"

# Rebuild e restart
log_info "Rebuilding e reiniciando containers..."
docker compose -f docker-compose.prod.yml up -d --build

# Aguardar containers ficarem prontos
log_info "Aguardando containers ficarem prontos..."
sleep 30

# Verificar se containers estão rodando
if ! docker compose -f docker-compose.prod.yml ps | grep -q "running"; then
    log_error "Alguns containers não estão rodando. Verificando logs:"
    docker compose -f docker-compose.prod.yml logs --tail=20
    
    # Perguntar se deve voltar ao commit original
    echo ""
    read -p "Rollback falhou. Voltar ao commit original? [y/N]: " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        log_info "Voltando ao commit original..."
        git reset --hard $CURRENT_COMMIT
        docker compose -f docker-compose.prod.yml up -d --build
        sleep 30
    fi
    exit 1
fi

# Executar migrações (caso necessário)
log_info "Verificando se há migrações pendentes..."
if docker exec sgl-lacina_app_prod php artisan migrate:status | grep -q "Pending"; then
    log_warning "Há migrações pendentes. Executando..."
    docker exec sgl-lacina_app_prod php artisan migrate --force
    log_success "Migrações executadas"
else
    log_info "Nenhuma migração pendente"
fi

# Limpar e recriar caches
log_info "Limpando e recriando caches..."
docker exec sgl-lacina_app_prod php artisan config:clear
docker exec sgl-lacina_app_prod php artisan route:clear
docker exec sgl-lacina_app_prod php artisan view:clear
docker exec sgl-lacina_app_prod php artisan config:cache
docker exec sgl-lacina_app_prod php artisan route:cache
docker exec sgl-lacina_app_prod php artisan view:cache
log_success "Caches atualizados"

# Reconfigurar storage
log_info "Reconfigurando storage..."
docker exec sgl-lacina_app_prod php artisan storage:link || true
docker exec sgl-lacina_app_prod cp -r public/* /var/www/html/public-shared/ || true
docker exec sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage || true
log_success "Storage reconfigurado"

# Ajustar permissões
log_info "Ajustando permissões..."
docker exec sgl-lacina_app_prod chown -R www-data:www-data storage bootstrap/cache || true
docker exec sgl-lacina_app_prod chmod -R 775 storage bootstrap/cache || true
docker exec sgl-lacina_app_prod find storage/app/public -type d -exec chmod 755 {} \; || true
docker exec sgl-lacina_app_prod find storage/app/public -type f -exec chmod 644 {} \; || true
log_success "Permissões ajustadas"

# Verificação final
log_info "Verificando aplicação..."
sleep 5

HEALTH_CHECK_URL="http://localhost:16000"
if curl -s -o /dev/null -w "%{http_code}" "$HEALTH_CHECK_URL" | grep -q "200\|302"; then
    log_success "Aplicação está respondendo corretamente"
else
    log_warning "Aplicação pode não estar respondendo. Verificar logs."
fi

# Mostrar status
echo ""
log_info "Status atual dos containers:"
docker compose -f docker-compose.prod.yml ps

# Mostrar commit atual
CURRENT_COMMIT_AFTER=$(git rev-parse HEAD)
echo ""
log_info "Commit atual após rollback: $CURRENT_COMMIT_AFTER"

echo ""
echo "=============================================="
log_success "Rollback concluído!"
echo ""
echo "🌐 Aplicação disponível em: $HEALTH_CHECK_URL"
echo "📊 Para monitorar logs: docker compose -f docker-compose.prod.yml logs -f"
echo ""
log_info "Para reverter este rollback, execute:"
echo "git reset --hard $CURRENT_COMMIT"
echo "./deploy.sh"
echo ""

# Perguntar se deve mostrar logs
read -p "Deseja ver os logs da aplicação? [y/N]: " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Mostrando logs (Ctrl+C para sair):"
    docker compose -f docker-compose.prod.yml logs -f --tail=50
fi

log_success "Rollback finalizado!"
