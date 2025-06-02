# Scripts de Automação - SGL LaCInA

Este diretório contém scripts para automatizar tarefas de deploy, backup e monitoramento do Sistema de Gestão de Laboratório (SGL) do LaCInA.

## 📜 Scripts Disponíveis

### 🚀 `deploy.sh`
Script principal para deploy e atualização da aplicação em produção.

**Uso:**
```bash
./deploy.sh
```

**O que faz:**
- Faz backup automático do banco e fotos
- Atualiza o código do repositório
- Reconstrói e reinicia os containers
- Executa migrações
- Limpa e recria caches
- Corrige permissões
- Verifica se tudo está funcionando

### 🩺 `health-check.sh`
Verifica a saúde geral da aplicação e seus componentes.

**Uso:**
```bash
./health-check.sh
```

**O que verifica:**
- Status de todos os containers
- Conectividade da aplicação web
- Status do banco de dados
- Funcionamento do queue worker e scheduler
- Logs de erro recentes
- Uso de disco e memória

### 📦 `backup.sh`
Cria backups completos do banco de dados, fotos e configurações.

**Uso:**
```bash
./backup.sh
```

**O que faz:**
- Backup comprimido do banco PostgreSQL
- Backup das fotos dos cadastros
- Backup do arquivo `.env`
- Remove backups antigos (>7 dias)
- Mostra relatório dos backups criados

### 🔄 `restore.sh`
Restaura a aplicação a partir de backups previamente criados.

**Uso:**
```bash
./restore.sh <backup_banco> [backup_fotos]
```

**Exemplos:**
```bash
# Restaurar apenas banco de dados
./restore.sh db_backup_20250602_141251.sql.gz

# Restaurar banco e fotos
./restore.sh db_backup_20250602_141251.sql.gz fotos_backup_20250602_141251.tar.gz
```

**O que faz:**
- Para a aplicação temporariamente
- Restaura o banco de dados
- Restaura as fotos (opcional)
- Corrige permissões
- Reinicia a aplicação
- Verifica se tudo está funcionando

### 🔙 `rollback.sh`
Script de emergência para voltar à versão anterior em caso de problemas.

**Uso:**
```bash
./rollback.sh
```

**O que faz:**
- Volta o código para o commit anterior
- Reconstrói containers com versão anterior
- Permite restaurar backup do banco se necessário

## 📅 Automação com Crontab

### Backup Automático Diário
Para agendar backups automáticos todos os dias às 2h da manhã:

```bash
# Editar crontab
crontab -e

# Adicionar linha:
0 2 * * * /home/paulo/sgl-lacina/backup.sh >> /var/log/sgl-backup.log 2>&1
```

### Health Check de 5 em 5 minutos
Para monitoramento contínuo da aplicação:

```bash
# Adicionar ao crontab:
*/5 * * * * /home/paulo/sgl-lacina/health-check.sh >> /var/log/sgl-health.log 2>&1
```

### Alerta por Email em Caso de Problema
```bash
# Criar script de alerta (alert.sh):
#!/bin/bash
if ! curl -f -s http://localhost:16000/ > /dev/null; then
    echo "🚨 SGL-LaCInA está fora do ar em $(date)" | mail -s "ALERTA: SGL-LaCInA DOWN" admin@seudominio.com
fi

# Adicionar ao crontab para verificar a cada 5 minutos:
*/5 * * * * /home/paulo/sgl-lacina/alert.sh
```

## 🛠️ Comandos Úteis de Monitoramento

### Verificar Status dos Containers
```bash
docker compose -f docker-compose.prod.yml ps
```

### Ver Logs em Tempo Real
```bash
# Todos os serviços
docker compose -f docker-compose.prod.yml logs -f

# Serviço específico
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f nginx
docker compose -f docker-compose.prod.yml logs -f db
```

### Verificar Uso de Recursos
```bash
# CPU e memória dos containers
docker stats

# Espaço em disco
docker system df
df -h

# Volumes do Docker
docker volume ls | grep sgl-lacina
```

### Acessar Containers
```bash
# Container da aplicação
docker exec -it sgl-lacina_app_prod bash

# Banco de dados
docker exec -it sgl-lacina_db_prod psql -U sail -d laravel
```

## 🚨 Solução de Problemas

### Aplicação não responde
```bash
# Verificar status
./health-check.sh

# Ver logs
docker compose -f docker-compose.prod.yml logs app nginx

# Reiniciar se necessário
docker compose -f docker-compose.prod.yml restart app nginx
```

### Problemas de permissão com fotos
```bash
# Recriar links e corrigir permissões
docker exec sgl-lacina_app_prod php artisan storage:link
docker exec sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage
docker exec sgl-lacina_app_prod chmod 755 storage/app/public/fotos
docker exec sgl-lacina_app_prod find storage/app/public/fotos -type f -exec chmod 644 {} \;
```

### Banco de dados com problemas
```bash
# Verificar conectividade
docker exec sgl-lacina_db_prod pg_isready -U sail

# Ver logs do PostgreSQL
docker logs sgl-lacina_db_prod

# Verificar migrações
docker exec sgl-lacina_app_prod php artisan migrate:status
```

## 📞 Suporte

Para mais informações, consulte:
- **Guia completo**: `docs/deploy-producao.md`
- **Logs da aplicação**: `docker compose -f docker-compose.prod.yml logs`
- **Documentação Laravel**: https://laravel.com/docs

---

**Última atualização:** 2 de junho de 2025
