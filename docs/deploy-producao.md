# 🚀 Guia de Deploy para Produção - SGL LaCInA

Este guia documenta o processo completo para fazer deploy do Sistema de Gestão de Laboratório (SGL) do LaCInA em ambiente de produção usando Docker.

## 📋 Pré-requisitos

### Sistema Operacional
- **Ubuntu 20.04+ ou Debian 11+**
- **Docker Engine 24.0+**
- **Docker Compose v2.0+**
- **Git**
- **Mínimo 4GB RAM, 20GB espaço em disco**

### Verificação do Ambiente
```bash
# Verificar versões
docker --version
docker compose version
git --version

# Verificar espaço em disco
df -h

# Verificar memória disponível
free -h
```

## 🔧 Configuração Inicial do Servidor

### 1. Atualizar Sistema
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git vim htop
```

### 2. Configurar Firewall
```bash
# Permitir SSH, HTTP e HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 5432/tcp  # PostgreSQL (apenas se necessário acesso externo)
sudo ufw --force enable
```

### 3. Configurar Usuário para Docker
```bash
# Adicionar usuário ao grupo docker
sudo usermod -aG docker $USER
# Relogar ou executar:
newgrp docker
```

## 📁 Estrutura de Diretórios

### Criar Estrutura de Produção
```bash
# Criar diretórios de produção
sudo mkdir -p /var/lib/sgl-lacina/postgresql
sudo mkdir -p /var/log/sgl-lacina
sudo mkdir -p /backups/sgl-lacina

# Definir permissões
sudo chown -R $USER:$USER /var/lib/sgl-lacina
sudo chown -R $USER:$USER /var/log/sgl-lacina
sudo chown -R $USER:$USER /backups/sgl-lacina
```

# 🚀 Guia de Deploy para Produção - SGL LaCInA

Este guia documenta o processo completo para fazer deploy do Sistema de Gestão de Laboratório (SGL) do LaCInA em ambiente de produção usando Docker.

## 📋 Pré-requisitos

### Sistema Operacional

- **Ubuntu 20.04+ ou Debian 11+**
- **Docker Engine 24.0+**
- **Docker Compose v2.0+**
- **Git**
- **Mínimo 4GB RAM, 20GB espaço em disco**

### Verificação do Ambiente

```bash
# Verificar versões
docker --version
docker compose version
git --version

# Verificar espaço em disco
df -h

# Verificar memória disponível
free -h
```

## 🔧 Configuração Inicial do Servidor

### 1. Atualizar Sistema

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git vim htop
```

### 2. Configurar Firewall

```bash
# Permitir SSH, HTTP e HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 5432/tcp  # PostgreSQL (apenas se necessário acesso externo)
sudo ufw --force enable
```

### 3. Configurar Usuário para Docker

```bash
# Adicionar usuário ao grupo docker
sudo usermod -aG docker $USER
# Relogar ou executar:
newgrp docker
```

## 📁 Estrutura de Diretórios

### Criar Estrutura de Produção

```bash
# Criar diretórios de produção
sudo mkdir -p /var/lib/sgl-lacina/postgresql
sudo mkdir -p /var/log/sgl-lacina
sudo mkdir -p /backups/sgl-lacina

# Definir permissões
sudo chown -R $USER:$USER /var/lib/sgl-lacina
sudo chown -R $USER:$USER /var/log/sgl-lacina
sudo chown -R $USER:$USER /backups/sgl-lacina
```

## 🔄 Deploy da Aplicação

### 1. Clonar ou Atualizar Repositório

```bash
# Primeiro deploy
git clone https://github.com/seu-usuario/sgl-lacina.git /opt/sgl-lacina
cd /opt/sgl-lacina

# Deploys subsequentes
cd /opt/sgl-lacina
git pull origin main
```

### 2. Configurar Variáveis de Ambiente

#### Criar arquivo `.env` para produção:

```bash
cp .env.example .env
```

#### Configurações essenciais no `.env`:

```env
APP_NAME="SGL - LaCInA"
APP_ENV=production
APP_KEY=base64:SUA_CHAVE_AQUI
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://seu-dominio.com

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

# Database
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=sgl_lacina_prod
DB_USERNAME=sgl_lacina_user
DB_PASSWORD=SENHA_SEGURA_AQUI

# Cache e Session
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
CACHE_STORE=database

# Queue
QUEUE_CONNECTION=database
QUEUE_TRIES=3
QUEUE_TIMEOUT=90

# Mail (Resend)
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=SUA_API_KEY_RESEND
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="sgl-lacina@seu-dominio.com"
MAIL_FROM_NAME="${APP_NAME}"

# Logs
LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DAILY_DAYS=30

# Telescope (desabilitado em produção)
TELESCOPE_ENABLED=false
```

### 3. Gerar Chave da Aplicação

```bash
# Gerar nova chave (apenas no primeiro deploy)
docker run --rm -v $(pwd):/app -w /app php:8.4-cli php artisan key:generate --show
# Copiar a chave gerada para o .env
```

## 🐳 Deploy com Docker

### 1. Build e Inicialização

```bash
# Parar containers existentes (se houver)
docker compose -f docker-compose.prod.yml down

# Build e subir containers
docker compose -f docker-compose.prod.yml up -d --build --force-recreate

# Verificar status
docker compose -f docker-compose.prod.yml ps
```

### 2. Configuração Inicial da Aplicação

```bash
# Aguardar containers iniciarem
sleep 30

# Executar migrações
docker exec -it sgl-lacina_app_prod php artisan migrate --force

# Criar link simbólico para storage
docker exec -it sgl-lacina_app_prod php artisan storage:link

# Criar link no nginx (necessário para arquivos estáticos)
docker exec -it sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Limpar e gerar cache
docker exec -it sgl-lacina_app_prod php artisan config:cache
docker exec -it sgl-lacina_app_prod php artisan route:cache
docker exec -it sgl-lacina_app_prod php artisan view:cache

# Corrigir permissões
docker exec -it sgl-lacina_app_prod chown -R www-data:www-data storage bootstrap/cache
docker exec -it sgl-lacina_app_prod chmod -R 775 storage bootstrap/cache
```

### 3. Seeds Iniciais (apenas primeiro deploy)

```bash
# Executar seeders
docker exec -it sgl-lacina_app_prod php artisan db:seed --class=DatabaseSeeder
```

## 🔒 Configuração de Arquivos Estáticos

### Problema Comum: Fotos não acessíveis

Se as fotos não estiverem acessíveis via web, execute:

```bash
# Recriar links do storage
docker exec -it sgl-lacina_app_prod php artisan storage:link
docker exec -it sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Corrigir permissões específicas das fotos
docker exec -it sgl-lacina_app_prod chmod 755 storage/app/public/fotos
docker exec -it sgl-lacina_app_prod find storage/app/public/fotos -type f -exec chmod 644 {} \;

# Copiar arquivos public para volume compartilhado
docker exec -it sgl-lacina_app_prod cp -r public/* /var/www/html/public-shared/
```

## 📊 Monitoramento e Logs

### 1. Verificar Status dos Containers

```bash
# Status geral
docker compose -f docker-compose.prod.yml ps

# Logs em tempo real
docker compose -f docker-compose.prod.yml logs -f

# Logs específicos
docker logs -f sgl-lacina_app_prod
docker logs -f sgl-lacina_nginx_prod
docker logs -f sgl-lacina_db_prod
```

### 2. Monitoramento de Recursos

```bash
# Uso de recursos pelos containers
docker stats

# Espaço em disco
docker system df

# Verificar volumes
docker volume ls | grep sgl-lacina
```

### 3. Logs da Aplicação Laravel

```bash
# Ver logs do Laravel
docker exec -it sgl-lacina_app_prod tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Limpar logs antigos (execute periodicamente)
docker exec -it sgl-lacina_app_prod find storage/logs -name "*.log" -mtime +30 -delete
```

## 🔄 Script de Deploy Automatizado

### Criar script de deploy

```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Iniciando deploy de produção..."

# 1. Fazer backup do banco
echo "📦 Fazendo backup do banco de dados..."
docker exec sgl-lacina_db_prod pg_dump -U sgl_lacina_user sgl_lacina_prod > /backups/sgl-lacina/backup-$(date +%Y%m%d-%H%M%S).sql

# 2. Atualizar código
echo "📝 Atualizando código..."
git pull origin main

# 3. Rebuild containers
echo "🐳 Reconstruindo containers..."
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d --build

# 4. Aguardar inicialização
echo "⏳ Aguardando inicialização..."
sleep 30

# 5. Executar migrações
echo "🗄️ Executando migrações..."
docker exec sgl-lacina_app_prod php artisan migrate --force

# 6. Recriar caches
echo "🧹 Recriando caches..."
docker exec sgl-lacina_app_prod php artisan config:cache
docker exec sgl-lacina_app_prod php artisan route:cache
docker exec sgl-lacina_app_prod php artisan view:cache

# 7. Recriar links do storage
echo "🔗 Recriando links do storage..."
docker exec sgl-lacina_app_prod php artisan storage:link
docker exec sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# 8. Corrigir permissões
echo "🔐 Corrigindo permissões..."
docker exec sgl-lacina_app_prod chown -R www-data:www-data storage bootstrap/cache
docker exec sgl-lacina_app_prod chmod -R 775 storage bootstrap/cache
docker exec sgl-lacina_app_prod chmod 755 storage/app/public/fotos
docker exec sgl-lacina_app_prod find storage/app/public/fotos -type f -exec chmod 644 {} \;

echo "✅ Deploy concluído com sucesso!"
echo "🌐 Aplicação disponível em: http://seu-dominio.com"
```

### Tornar script executável:

```bash
chmod +x deploy.sh
```

## 🔙 Backup e Restore

### Backup Automático do Banco

```bash
#!/bin/bash
# backup-db.sh

BACKUP_DIR="/backups/sgl-lacina"
DATE=$(date +%Y%m%d-%H%M%S)
BACKUP_FILE="$BACKUP_DIR/backup-$DATE.sql"

# Criar backup
docker exec sgl-lacina_db_prod pg_dump -U sgl_lacina_user sgl_lacina_prod > $BACKUP_FILE

# Compactar backup
gzip $BACKUP_FILE

# Remover backups antigos (manter apenas 7 dias)
find $BACKUP_DIR -name "backup-*.sql.gz" -mtime +7 -delete

echo "Backup salvo em: $BACKUP_FILE.gz"
```

### Restore do Banco

```bash
# Restore de backup
gunzip -c /backups/sgl-lacina/backup-YYYYMMDD-HHMMSS.sql.gz | \
docker exec -i sgl-lacina_db_prod psql -U sgl_lacina_user -d sgl_lacina_prod
```

### Agendar Backups (crontab)

```bash
# Editar crontab
crontab -e

# Adicionar backup diário às 2h da manhã
0 2 * * * /opt/sgl-lacina/backup-db.sh
```

## 🚨 Troubleshooting

### Problemas Comuns

#### 1. Containers não iniciam

```bash
# Verificar logs
docker compose -f docker-compose.prod.yml logs

# Verificar espaço em disco
df -h

# Limpar recursos não utilizados
docker system prune -f
```

#### 2. Fotos não aparecem

```bash
# Verificar symbolic links
docker exec sgl-lacina_nginx_prod ls -la /var/www/html/public/storage

# Recriar links se necessário
docker exec sgl-lacina_app_prod php artisan storage:link
docker exec sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Corrigir permissões
docker exec sgl-lacina_app_prod chmod 755 storage/app/public/fotos
docker exec sgl-lacina_app_prod find storage/app/public/fotos -type f -exec chmod 644 {} \;
```

#### 3. Erro 500 na aplicação

```bash
# Verificar logs do Laravel
docker exec sgl-lacina_app_prod tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Verificar permissões
docker exec sgl-lacina_app_prod chown -R www-data:www-data storage bootstrap/cache
```

#### 4. Banco de dados inacessível

```bash
# Verificar status do container
docker exec sgl-lacina_db_prod pg_isready -U sgl_lacina_user

# Verificar logs do PostgreSQL
docker logs sgl-lacina_db_prod
```

### Rollback de Emergência

```bash
#!/bin/bash
# rollback.sh

echo "🔄 Iniciando rollback..."

# Parar aplicação atual
docker compose -f docker-compose.prod.yml down

# Voltar para commit anterior
git reset --hard HEAD~1

# Rebuild com versão anterior
docker compose -f docker-compose.prod.yml up -d --build

echo "✅ Rollback concluído!"
```

## 🔒 Segurança

### Configurações de Segurança

- Sempre usar HTTPS em produção
- Configurar firewall adequadamente
- Manter containers atualizados
- Usar senhas fortes
- Configurar backups regulares

### Monitoramento de Segurança

```bash
# Verificar tentativas de acesso
sudo tail -f /var/log/auth.log

# Monitorar logs do nginx
docker logs -f sgl-lacina_nginx_prod | grep -E "(40[0-9]|50[0-9])"
```

## 📞 Suporte

### Contatos de Emergência

- **Desenvolvedor**: seu-email@dominio.com
- **Infraestrutura**: admin@dominio.com

### Comandos Úteis de Diagnóstico

```bash
# Status completo do sistema
docker compose -f docker-compose.prod.yml ps
docker stats --no-stream
df -h
free -h

# Health check da aplicação
curl -I http://localhost:16000/login

# Verificar conectividade do banco
docker exec sgl-lacina_app_prod php artisan db:monitor
```

---

**📅 Última atualização**: 2 de junho de 2025  
**✅ Testado em**: Ubuntu 22.04, Docker 24.0.7, Docker Compose v2.21.0
```

**Configurações obrigatórias para produção:**

```env
# Aplicação
APP_NAME="SGL - LaCInA"
APP_ENV=production
APP_KEY=base64:GERAR_NOVA_CHAVE_AQUI
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://seudominio.com

# Localização
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

# Logs
LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DAILY_DAYS=14

# Banco de Dados
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=sgl_lacina_prod
DB_USERNAME=sgl_user
DB_PASSWORD=SENHA_FORTE_AQUI

# Sessão
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_PATH=/
SESSION_DOMAIN=.seudominio.com
SESSION_SAME_SITE=lax

# Cache e Queue
CACHE_STORE=database
CACHE_PREFIX=sgl_lacina
QUEUE_CONNECTION=database

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=SUA_API_KEY_RESEND
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="sgl-lacina@seudominio.com"
MAIL_FROM_NAME="${APP_NAME}"

# Outros
VITE_APP_NAME="${APP_NAME}"
SCOUT_DRIVER=database
TELESCOPE_ENABLED=false
```

### 3. Gerar APP_KEY

```bash
# Usando container temporário para gerar a chave
docker run --rm -v $(pwd):/app -w /app php:8.4-cli php artisan key:generate --show
```

## 🚀 Deploy Inicial

### 1. Build e Start dos Containers

```bash
# Build das imagens
docker compose -f docker-compose.prod.yml build --no-cache

# Iniciar os serviços
docker compose -f docker-compose.prod.yml up -d
```

### 2. Configuração da Aplicação

```bash
# Aguardar containers ficarem prontos
sleep 30

# Executar migrações
docker exec -it sgl-lacina_app_prod php artisan migrate --force

# Executar seeders (se aplicável)
docker exec -it sgl-lacina_app_prod php artisan db:seed --force

# Criar symbolic link para storage
docker exec -it sgl-lacina_app_prod php artisan storage:link

# Sincronizar arquivos public
docker exec -it sgl-lacina_app_prod cp -r public/* /var/www/html/public-shared/

# Criar link do storage no nginx
docker exec -it sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Ajustar permissões
docker exec -it sgl-lacina_app_prod chown -R www-data:www-data storage bootstrap/cache
docker exec -it sgl-lacina_app_prod chmod -R 775 storage bootstrap/cache
```

### 3. Verificação do Deploy

```bash
# Verificar status dos containers
docker compose -f docker-compose.prod.yml ps

# Verificar logs
docker compose -f docker-compose.prod.yml logs -f app

# Testar acesso à aplicação
curl -I http://localhost:16000
```

## 🔄 Processo de Atualização

### Script de Deploy Automatizado

Crie o arquivo `deploy.sh`:

```bash
#!/bin/bash

set -e

echo "🚀 Iniciando deploy do SGL LaCInA..."

# Backup do banco de dados
echo "📦 Fazendo backup do banco..."
docker exec sgl-lacina_db_prod pg_dump -U sail -d laravel > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup das fotos (se existirem)
echo "📸 Fazendo backup das fotos..."
docker exec sgl-lacina_app_prod tar -czf /tmp/fotos_backup_$(date +%Y%m%d_%H%M%S).tar.gz -C storage/app/public fotos/ || true

# Parar aplicação (mantém DB rodando)
echo "⏹️ Parando aplicação..."
docker compose -f docker-compose.prod.yml stop app scheduler queue nginx

# Atualizar código
echo "📥 Atualizando código..."
git pull origin main

# Rebuild containers
echo "🔨 Rebuilding containers..."
docker compose -f docker-compose.prod.yml build app scheduler queue

# Reiniciar serviços
echo "▶️ Reiniciando serviços..."
docker compose -f docker-compose.prod.yml up -d

# Aguardar containers ficarem prontos
echo "⏳ Aguardando containers..."
sleep 30

# Executar migrações
echo "🗃️ Executando migrações..."
docker exec -it sgl-lacina_app_prod php artisan migrate --force

# Limpar caches
echo "🧹 Limpando caches..."
docker exec -it sgl-lacina_app_prod php artisan config:clear
docker exec -it sgl-lacina_app_prod php artisan route:clear
docker exec -it sgl-lacina_app_prod php artisan view:clear

# Otimizar aplicação
echo "⚡ Otimizando aplicação..."
docker exec -it sgl-lacina_app_prod php artisan config:cache
docker exec -it sgl-lacina_app_prod php artisan route:cache
docker exec -it sgl-lacina_app_prod php artisan view:cache

# Sincronizar arquivos public
docker exec -it sgl-lacina_app_prod cp -r public/* /var/www/html/public-shared/

# Recriar symbolic link do storage
docker exec -it sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Ajustar permissões
docker exec -it sgl-lacina_app_prod chown -R www-data:www-data storage bootstrap/cache
docker exec -it sgl-lacina_app_prod chmod -R 775 storage bootstrap/cache

echo "✅ Deploy concluído com sucesso!"
echo "🌐 Aplicação disponível em: http://seu-servidor:16000"
```

Tornar o script executável:
```bash
chmod +x deploy.sh
```

## 🔍 Monitoramento e Logs

### Comandos Úteis para Monitoramento

```bash
# Ver logs em tempo real
docker compose -f docker-compose.prod.yml logs -f

# Ver logs de um serviço específico
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f nginx
docker compose -f docker-compose.prod.yml logs -f db

# Status dos containers
docker compose -f docker-compose.prod.yml ps

# Uso de recursos
docker stats

# Verificar espaço em disco
df -h
docker system df

# Acessar container da aplicação
docker exec -it sgl-lacina_app_prod bash

# Acessar banco de dados
docker exec -it sgl-lacina_db_prod psql -U sail -d laravel
```

### Logs da Aplicação Laravel

```bash
# Ver logs do Laravel dentro do container
docker exec -it sgl-lacina_app_prod tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Ver logs de erro
docker exec -it sgl-lacina_app_prod grep "ERROR" storage/logs/laravel-$(date +%Y-%m-%d).log
```

## 🔧 Manutenção

### Backup Automatizado

Crie um script de backup `backup.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/sgl-lacina"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup do banco
docker exec sgl-lacina_db_prod pg_dump -U sail -d laravel | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Backup das fotos
docker exec sgl-lacina_app_prod tar -czf - -C storage/app/public fotos/ > $BACKUP_DIR/fotos_backup_$DATE.tar.gz

# Remover backups antigos (manter últimos 7 dias)
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup concluído: $DATE"
```

### Limpeza do Sistema

```bash
# Remover imagens Docker não utilizadas
docker image prune -f

# Remover volumes órfãos
docker volume prune -f

# Remover containers parados
docker container prune -f

# Limpeza completa (cuidado!)
docker system prune -a
```

## 🛡️ Segurança

### SSL/HTTPS (Recomendado)

Para configurar HTTPS com Nginx e Let's Encrypt:

1. **Instalar Certbot:**
```bash
sudo apt install certbot python3-certbot-nginx
```

2. **Gerar certificado:**
```bash
sudo certbot --nginx -d seudominio.com
```

3. **Atualizar nginx.conf** para incluir redirecionamento HTTPS

4. **Atualizar .env:**
```env
APP_URL=https://seudominio.com
SESSION_SECURE_COOKIE=true
```

### Firewall

```bash
# Configurar UFW
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 16000/tcp  # Porta da aplicação
```

## 🚨 Troubleshooting

### Problemas Comuns

**1. Container não inicia:**
```bash
# Verificar logs
docker compose -f docker-compose.prod.yml logs app

# Verificar configuração
docker compose -f docker-compose.prod.yml config
```

**2. Erro de permissões:**
```bash
# Ajustar permissões
docker exec -it sgl-lacina_app_prod chown -R www-data:www-data storage bootstrap/cache
docker exec -it sgl-lacina_app_prod chmod -R 775 storage bootstrap/cache
```

**3. Fotos não aparecem:**
```bash
# Verificar symbolic link
docker exec -it sgl-lacina_nginx_prod ls -la /var/www/html/public/storage

# Recriar se necessário
docker exec -it sgl-lacina_nginx_prod ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Verificar permissões das fotos
docker exec -it sgl-lacina_app_prod chmod 755 storage/app/public/fotos
docker exec -it sgl-lacina_app_prod chmod 644 storage/app/public/fotos/*
```

**4. Erro de conexão com banco:**
```bash
# Verificar se o banco está rodando
docker compose -f docker-compose.prod.yml ps db

# Testar conexão
docker exec -it sgl-lacina_app_prod php artisan migrate:status
```

### Rollback

Se algo der errado, para fazer rollback:

```bash
# Parar containers
docker compose -f docker-compose.prod.yml down

# Voltar para commit anterior
git reset --hard HEAD~1

# Rebuild e restart
docker compose -f docker-compose.prod.yml up -d --build

# Restaurar backup do banco se necessário
docker exec -i sgl-lacina_db_prod psql -U sail -d laravel < backup_YYYYMMDD_HHMMSS.sql
```

## 📊 Checklist de Deploy

### Pré-Deploy
- [ ] Código testado em ambiente de staging
- [ ] Backup do banco de dados realizado
- [ ] Backup das fotos realizado
- [ ] Variáveis de ambiente conferidas
- [ ] Certificados SSL válidos (se aplicável)

### Durante o Deploy
- [ ] Containers buildados com sucesso
- [ ] Migrações executadas sem erro
- [ ] Symbolic links criados
- [ ] Permissões ajustadas
- [ ] Caches limpos e recriados

### Pós-Deploy
- [ ] Aplicação acessível via web
- [ ] Login funcionando
- [ ] Upload de fotos funcionando
- [ ] Emails sendo enviados
- [ ] Logs sem erros críticos
- [ ] Backup automático configurado

## 📞 Suporte

Em caso de problemas durante o deploy:

1. Verificar logs: `docker compose -f docker-compose.prod.yml logs`
2. Consultar este guia de troubleshooting
3. Verificar status dos containers: `docker compose -f docker-compose.prod.yml ps`
4. Contactar a equipe de desenvolvimento

---

**Última atualização:** 2 de junho de 2025
**Versão do guia:** 1.0
