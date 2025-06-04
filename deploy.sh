#!/bin/bash

# Deploy Script para SGL LaCInA - Produção
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

# Verificar se Docker está rodando
if ! docker info > /dev/null 2>&1; then
    log_error "Docker não está rodando. Inicie o Docker e tente novamente."
    exit 1
fi

echo "🚀 Iniciando deploy do SGL LaCInA - Produção"
echo "================================================"

# Perguntar se deve fazer backup
read -p "Fazer backup antes do deploy? [y/N]: " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log_info "Fazendo backup do banco de dados..."
    
    # Criar diretório de backup se não existir
    mkdir -p backups
    
    # Backup do banco
    BACKUP_FILE="backups/db_backup_$(date +%Y%m%d_%H%M%S).sql"
    if docker exec sgl-lacina_db_prod pg_dump -U sail -d laravel > "$BACKUP_FILE" 2>/dev/null; then
        log_success "Backup do banco salvo em: $BACKUP_FILE"
    else
        log_warning "Não foi possível fazer backup do banco (container pode não estar rodando)"
    fi
    
    # Backup das fotos
    FOTOS_BACKUP="backups/fotos_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    if docker exec sgl-lacina_app_prod tar -czf "/tmp/fotos_backup.tar.gz" -C storage/app/public fotos/ 2>/dev/null; then
        docker cp sgl-lacina_app_prod:/tmp/fotos_backup.tar.gz "$FOTOS_BACKUP"
        docker exec sgl-lacina_app_prod rm -f /tmp/fotos_backup.tar.gz
        log_success "Backup das fotos salvo em: $FOTOS_BACKUP"
    else
        log_warning "Não foi possível fazer backup das fotos"
    fi
fi

# Atualizar código
log_info "Atualizando código do repositório..."
git fetch origin
git pull origin main
log_success "Código atualizado"

# Verificar se há diferenças no docker-compose ou Dockerfiles
if git diff HEAD~1 HEAD --name-only | grep -E "(docker-compose|Dockerfile)" > /dev/null; then
    log_info "Detectadas mudanças nos arquivos Docker. Será necessário rebuild completo."
    NEED_REBUILD=true
else
    NEED_REBUILD=false
fi

# Parar containers da aplicação (manter DB rodando)
log_info "Parando containers da aplicação..."
docker compose -f docker-compose.prod.yml stop app scheduler queue nginx || true
log_success "Containers parados"

# Build das imagens
if [ "$NEED_REBUILD" = true ]; then
    log_info "Fazendo rebuild completo das imagens..."
    docker compose -f docker-compose.prod.yml build --no-cache app scheduler queue
else
    log_info "Fazendo build das imagens..."
    docker compose -f docker-compose.prod.yml build app scheduler queue
fi
log_success "Build concluído"

# Iniciar containers
log_info "Iniciando containers..."
docker compose -f docker-compose.prod.yml up -d
log_success "Containers iniciados"

# Aguardar containers ficarem prontos
log_info "Aguardando containers ficarem prontos..."
sleep 30

# Verificar se containers estão rodando (aceitar diferentes estados válidos)
if ! docker compose -f docker-compose.prod.yml ps | grep -E "(healthy|running|Up)" > /dev/null; then
    log_error "Alguns containers não estão rodando. Verificar logs:"
    docker compose -f docker-compose.prod.yml logs --tail=20
    exit 1
fi

# Executar migrações
log_info "Executando migrações do banco de dados..."
if docker exec sgl-lacina_app_prod php artisan migrate --force; then
    log_success "Migrações executadas com sucesso"
else
    log_error "Erro ao executar migrações"
    exit 1
fi

# Limpar caches
log_info "Limpando caches..."
docker exec sgl-lacina_app_prod php artisan config:clear || true
docker exec sgl-lacina_app_prod php artisan route:clear || true
docker exec sgl-lacina_app_prod php artisan view:clear || true
log_success "Caches limpos"

# Otimizar aplicação
log_info "Otimizando aplicação..."
docker exec sgl-lacina_app_prod php artisan config:cache
docker exec sgl-lacina_app_prod php artisan route:cache
docker exec sgl-lacina_app_prod php artisan view:cache
log_success "Aplicação otimizada"

# Configurar storage
log_info "Configurando storage e symbolic links..."

# Criar symbolic link no app
docker exec sgl-lacina_app_prod php artisan storage:link || true

# Sincronizar arquivos public
docker exec sgl-lacina_app_prod cp -r public/* /var/www/html/public-shared/ || true

# Criar symbolic link no nginx
docker exec sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage || true

log_success "Storage configurado"

# Ajustar permissões
log_info "Ajustando permissões..."
docker exec sgl-lacina_app_prod chown -R www-data:www-data storage bootstrap/cache || true
docker exec sgl-lacina_app_prod chmod -R 775 storage bootstrap/cache || true

# Ajustar permissões das fotos se existirem
docker exec sgl-lacina_app_prod find storage/app/public -type d -exec chmod 755 {} \; || true
docker exec sgl-lacina_app_prod find storage/app/public -type f -exec chmod 644 {} \; || true

log_success "Permissões ajustadas"

# Verificações finais
log_info "Executando verificações finais..."

# Verificar se a aplicação está respondendo
HEALTH_CHECK_URL="http://localhost:16000"
sleep 5

if curl -s -o /dev/null -w "%{http_code}" "$HEALTH_CHECK_URL" | grep -q "200\|302"; then
    log_success "Aplicação está respondendo corretamente"
else
    log_warning "Aplicação pode não estar respondendo corretamente. Verificar logs."
fi

# Mostrar status dos containers
echo ""
log_info "Status atual dos containers:"
docker compose -f docker-compose.prod.yml ps

# Mostrar informações finais
echo ""
echo "================================================"
log_success "Deploy concluído com sucesso!"
echo ""
echo "🌐 Aplicação disponível em: $HEALTH_CHECK_URL"
echo "📊 Para monitorar logs: docker compose -f docker-compose.prod.yml logs -f"
echo "🔍 Para verificar status: docker compose -f docker-compose.prod.yml ps"
echo ""

# Perguntar se deve mostrar logs
read -p "Deseja ver os logs da aplicação? [y/N]: " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Mostrando logs (Ctrl+C para sair):"
    docker compose -f docker-compose.prod.yml logs -f --tail=50
fi

log_success "Deploy finalizado!"
