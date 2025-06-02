#!/bin/bash

# Script de gerenciamento simplificado do SGL-LaCInA
# Uso: ./sgl.sh [comando]

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Função para mostrar ajuda
show_help() {
    echo "🎯 SGL-LaCInA Management Script"
    echo ""
    echo "Uso: ./sgl.sh [comando]"
    echo ""
    echo "Comandos disponíveis:"
    echo "  start       - Iniciar todos os containers"
    echo "  stop        - Parar todos os containers"
    echo "  restart     - Reiniciar todos os containers"
    echo "  status      - Mostrar status dos containers"
    echo "  logs        - Mostrar logs em tempo real"
    echo "  health      - Verificar saúde da aplicação"
    echo "  backup      - Criar backup completo"
    echo "  deploy      - Fazer deploy/atualização"
    echo "  restore     - Restaurar de backup"
    echo "  shell       - Acessar shell do container da aplicação"
    echo "  db          - Acessar PostgreSQL"
    echo "  clean       - Limpar containers e imagens não usados"
    echo ""
    echo "Exemplos:"
    echo "  ./sgl.sh start"
    echo "  ./sgl.sh health"
    echo "  ./sgl.sh backup"
    echo "  ./sgl.sh restore db_backup_20250602_141251.sql.gz"
}

# Função para verificar se os containers estão rodando
check_containers() {
    if ! docker compose -f docker-compose.prod.yml ps | grep -q "running"; then
        echo "⚠️ Alguns containers não estão rodando. Execute './sgl.sh start' primeiro."
        return 1
    fi
    return 0
}

case "$1" in
    "start")
        echo "🚀 Iniciando containers do SGL-LaCInA..."
        docker compose -f docker-compose.prod.yml up -d
        echo "✅ Containers iniciados!"
        echo "🌐 Aplicação disponível em: http://localhost:16000"
        ;;
    
    "stop")
        echo "⏹️ Parando containers do SGL-LaCInA..."
        docker compose -f docker-compose.prod.yml down
        echo "✅ Containers parados!"
        ;;
    
    "restart")
        echo "🔄 Reiniciando containers do SGL-LaCInA..."
        docker compose -f docker-compose.prod.yml restart
        echo "✅ Containers reiniciados!"
        ;;
    
    "status")
        echo "📊 Status dos containers:"
        docker compose -f docker-compose.prod.yml ps
        ;;
    
    "logs")
        echo "📋 Logs em tempo real (Ctrl+C para sair):"
        docker compose -f docker-compose.prod.yml logs -f
        ;;
    
    "health")
        if [ -f "health-check.sh" ]; then
            ./health-check.sh
        else
            echo "❌ Script health-check.sh não encontrado!"
        fi
        ;;
    
    "backup")
        if [ -f "backup.sh" ]; then
            ./backup.sh
        else
            echo "❌ Script backup.sh não encontrado!"
        fi
        ;;
    
    "deploy")
        if [ -f "deploy.sh" ]; then
            ./deploy.sh
        else
            echo "❌ Script deploy.sh não encontrado!"
        fi
        ;;
    
    "restore")
        if [ -f "restore.sh" ]; then
            if [ -z "$2" ]; then
                echo "❌ Especifique o arquivo de backup!"
                echo "Uso: ./sgl.sh restore <backup_banco> [backup_fotos]"
                echo ""
                echo "Backups disponíveis:"
                ls -la backups/ 2>/dev/null | grep backup || echo "Nenhum backup encontrado"
            else
                ./restore.sh "$2" "$3"
            fi
        else
            echo "❌ Script restore.sh não encontrado!"
        fi
        ;;
    
    "shell")
        if check_containers; then
            echo "🐚 Acessando shell do container da aplicação..."
            docker exec -it sgl-lacina_app_prod bash
        fi
        ;;
    
    "db")
        if check_containers; then
            echo "🗄️ Acessando PostgreSQL..."
            docker exec -it sgl-lacina_db_prod psql -U sail -d laravel
        fi
        ;;
    
    "clean")
        echo "🧹 Limpando containers e imagens não utilizados..."
        docker container prune -f
        docker image prune -f
        docker volume prune -f
        echo "✅ Limpeza concluída!"
        ;;
    
    "help"|"-h"|"--help"|"")
        show_help
        ;;
    
    *)
        echo "❌ Comando não reconhecido: $1"
        echo ""
        show_help
        exit 1
        ;;
esac
