#!/bin/bash

echo "🩺 Verificando saúde da aplicação SGL-LaCInA..."

# Verificar containers
echo "📦 Status dos containers:"
docker compose -f docker-compose.prod.yml ps

# Verificar aplicação web
if curl -f -s http://localhost:16000/ > /dev/null; then
    echo "✅ Aplicação web: OK"
else
    echo "❌ Aplicação web: FALHOU"
fi

# Verificar banco de dados
if docker exec sgl-lacina_db_prod pg_isready -U sail > /dev/null 2>&1; then
    echo "✅ Banco de dados: OK"
else
    echo "❌ Banco de dados: FALHOU"
fi

# Verificar queue worker
if docker compose -f docker-compose.prod.yml ps queue | grep -q "running"; then
    echo "✅ Queue worker: OK"
else
    echo "❌ Queue worker: FALHOU"
fi

# Verificar scheduler
if docker compose -f docker-compose.prod.yml ps scheduler | grep -q "running"; then
    echo "✅ Scheduler: OK"
else
    echo "❌ Scheduler: FALHOU"
fi

# Verificar logs de erro
ERROR_COUNT=$(docker compose -f docker-compose.prod.yml logs --since=1h 2>/dev/null | grep -i error | wc -l)
if [ $ERROR_COUNT -eq 0 ]; then
    echo "✅ Logs: Sem erros na última hora"
else
    echo "⚠️ Logs: $ERROR_COUNT erros encontrados na última hora"
fi

# Verificar espaço em disco
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -lt 80 ]; then
    echo "✅ Espaço em disco: ${DISK_USAGE}% usado"
else
    echo "⚠️ Espaço em disco: ${DISK_USAGE}% usado (ATENÇÃO: acima de 80%)"
fi

# Verificar uso de memória
MEM_USAGE=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100.0}')
if [ $MEM_USAGE -lt 90 ]; then
    echo "✅ Uso de memória: ${MEM_USAGE}%"
else
    echo "⚠️ Uso de memória: ${MEM_USAGE}% (ATENÇÃO: acima de 90%)"
fi

echo "🩺 Verificação de saúde concluída!"
