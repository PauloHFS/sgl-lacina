#!/bin/bash

# Script de restore para SGL-LaCInA
# Uso: ./restore.sh <arquivo_backup_db> [arquivo_backup_fotos]

if [ $# -eq 0 ]; then
    echo "❌ Uso: $0 <arquivo_backup_db> [arquivo_backup_fotos]"
    echo "Exemplo: $0 db_backup_20250602_141251.sql.gz fotos_backup_20250602_141251.tar.gz"
    echo ""
    echo "Backups disponíveis:"
    ls -la backups/ | grep backup
    exit 1
fi

DB_BACKUP=$1
FOTOS_BACKUP=$2
BACKUP_DIR="/home/paulo/sgl-lacina/backups"

echo "🔄 Iniciando restore do SGL-LaCInA..."

# Verificar se o arquivo de backup do banco existe
if [ ! -f "$BACKUP_DIR/$DB_BACKUP" ]; then
    echo "❌ Arquivo de backup do banco não encontrado: $BACKUP_DIR/$DB_BACKUP"
    exit 1
fi

# Confirmar ação
echo "⚠️ ATENÇÃO: Este processo irá substituir os dados atuais!"
echo "📄 Backup do banco: $DB_BACKUP"
if [ -n "$FOTOS_BACKUP" ]; then
    echo "📸 Backup das fotos: $FOTOS_BACKUP"
fi
echo ""
read -p "Deseja continuar? (sim/não): " confirm

if [ "$confirm" != "sim" ]; then
    echo "❌ Restore cancelado pelo usuário"
    exit 0
fi

# Parar aplicação temporariamente
echo "⏹️ Parando aplicação..."
docker compose -f docker-compose.prod.yml stop app scheduler queue nginx

# Restore do banco de dados
echo "🗄️ Restaurando banco de dados..."
if [[ $DB_BACKUP == *.gz ]]; then
    gunzip -c "$BACKUP_DIR/$DB_BACKUP" | docker exec -i sgl-lacina_db_prod psql -U sail -d laravel
else
    cat "$BACKUP_DIR/$DB_BACKUP" | docker exec -i sgl-lacina_db_prod psql -U sail -d laravel
fi

if [ $? -eq 0 ]; then
    echo "✅ Banco de dados restaurado com sucesso"
else
    echo "❌ Erro ao restaurar banco de dados"
    exit 1
fi

# Restore das fotos (se especificado)
if [ -n "$FOTOS_BACKUP" ] && [ -f "$BACKUP_DIR/$FOTOS_BACKUP" ]; then
    echo "📸 Restaurando fotos..."
    
    # Remover fotos existentes
    docker exec sgl-lacina_app_prod rm -rf storage/app/public/fotos
    
    # Extrair backup das fotos
    if [[ $FOTOS_BACKUP == *.tar.gz ]]; then
        cat "$BACKUP_DIR/$FOTOS_BACKUP" | docker exec -i sgl-lacina_app_prod tar -xzf - -C storage/app/public/
    else
        echo "❌ Formato de backup de fotos não suportado: $FOTOS_BACKUP"
    fi
    
    if [ $? -eq 0 ]; then
        echo "✅ Fotos restauradas com sucesso"
        
        # Corrigir permissões das fotos
        docker exec sgl-lacina_app_prod chmod 755 storage/app/public/fotos
        docker exec sgl-lacina_app_prod find storage/app/public/fotos -type f -exec chmod 644 {} \;
    else
        echo "❌ Erro ao restaurar fotos"
    fi
fi

# Reiniciar aplicação
echo "▶️ Reiniciando aplicação..."
docker compose -f docker-compose.prod.yml up -d

# Aguardar containers ficarem prontos
echo "⏳ Aguardando containers ficarem prontos..."
sleep 15

# Verificar se aplicação está funcionando
echo "🔍 Verificando aplicação..."
if curl -f -s http://localhost:16000/ > /dev/null; then
    echo "✅ Aplicação restaurada e funcionando corretamente"
else
    echo "⚠️ Aplicação pode não estar funcionando corretamente. Verifique os logs."
fi

echo "🎉 Restore concluído!"
