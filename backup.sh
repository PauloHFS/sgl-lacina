#!/bin/bash

BACKUP_DIR="/home/paulo/sgl-lacina/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Criar diretório de backup se não existir
mkdir -p $BACKUP_DIR

echo "📦 Iniciando backup do SGL-LaCInA..."

# Backup do banco de dados
echo "🗄️ Fazendo backup do banco de dados..."
docker exec sgl-lacina_db_prod pg_dump -U sail -d laravel | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

if [ $? -eq 0 ]; then
    echo "✅ Backup do banco de dados concluído: db_backup_$DATE.sql.gz"
else
    echo "❌ Erro no backup do banco de dados"
    exit 1
fi

# Backup das fotos
echo "📸 Fazendo backup das fotos..."
if docker exec sgl-lacina_app_prod [ -d "storage/app/public/fotos" ]; then
    docker exec sgl-lacina_app_prod tar -czf - -C storage/app/public fotos/ > $BACKUP_DIR/fotos_backup_$DATE.tar.gz
    
    if [ $? -eq 0 ]; then
        echo "✅ Backup das fotos concluído: fotos_backup_$DATE.tar.gz"
    else
        echo "⚠️ Erro no backup das fotos"
    fi
else
    echo "ℹ️ Diretório de fotos não encontrado, pulando backup de fotos"
fi

# Backup do arquivo .env
echo "⚙️ Fazendo backup do arquivo .env..."
cp .env $BACKUP_DIR/env_backup_$DATE
echo "✅ Backup do .env concluído: env_backup_$DATE"

# Remover backups antigos (manter últimos 7 dias)
echo "🧹 Removendo backups antigos (mais de 7 dias)..."
find $BACKUP_DIR -name "*backup_*.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*backup_*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "env_backup_*" -mtime +7 -delete

# Mostrar tamanho dos backups
echo "📊 Tamanho dos backups criados:"
ls -lh $BACKUP_DIR/*_$DATE* 2>/dev/null || echo "Nenhum backup encontrado"

echo "✅ Backup concluído com sucesso!"
echo "📁 Backups salvos em: $BACKUP_DIR"
