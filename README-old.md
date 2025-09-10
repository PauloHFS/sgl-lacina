# SGL - LaCInA

## Links

- [Icons](https://heroicons.com/)

## 📚 Documentação

### Deploy e Produção
- **[Deploy e Backup Completo](docs/deploy-e-backup-completo.md)** - Documentação completa do sistema de deploy, backup e rollback
- **[Deploy em Produção](docs/deploy-producao.md)** - Guia original detalhado de deploy
- **[Especificações Técnicas](docs/specs.md)** - Arquitetura e especificações do sistema

### Desenvolvimento
- **[Guia de Testes](docs/testing.md)** - Como executar e escrever testes
- **[Componentes UI](docs/daisyui.md)** - Documentação dos componentes daisyUI

### Scripts Disponíveis
- `./deploy.sh` - Deploy automatizado enterprise-grade
- `./backup.sh` - Sistema de backup completo
- `./rollback.sh` - Rollback granular e inteligente  
- `./health-check.sh` - Monitoramento de saúde da aplicação
- `./sgl.sh` - Script principal de gerenciamento

## Requisitos para o desenvolvimento

- Docker
- VSCode

## Como executar o projeto para desenvolvimento

1. Clone o repositório

2. Acesse o diretório do projeto

```bash
$ cd laravel
```

3. Copie o arquivo `.env.example` para `.env`

```bash
$ cp .env.example .env
```

4. Execute o comando para abrir o container

```bash
$ devcontainer open .
```

## Ambiente de Staging (Homologação)

Esta seção descreve como configurar e executar o ambiente de staging da aplicação Lacina.

### Pré-requisitos

- Docker e Docker Compose instalados e em execução.
- Git para clonar o repositório.

### Configuração Inicial

1. **Clone o repositório (se ainda não o fez):**

    ```bash
    git clone <url-do-repositorio>
    cd lacina
    ```

2. **Crie o arquivo de variáveis de ambiente para staging:**
   Copie o arquivo de exemplo `.env.example` para `.env.staging`.

    ```bash
    cp .env.example .env.staging
    ```

3. **Configure as variáveis de ambiente em `.env.staging`:**
   Abra o arquivo `.env.staging` e preencha todas as variáveis necessárias. Preste atenção especial às seguintes variáveis do banco de dados, pois elas são cruciais para a conexão com o PostgreSQL:

    ```text
    APP_ENV=staging
    APP_DEBUG=false # Recomendado para staging
    APP_URL=http://localhost:5566

    DB_CONNECTION=pgsql
    DB_HOST=db
    DB_PORT=5432
    DB_DATABASE=lacina_staging_db # Exemplo, escolha um nome
    DB_USERNAME=lacina_staging_user # Exemplo, escolha um usuário
    DB_PASSWORD=secret # Exemplo, escolha uma senha forte

    # Outras variáveis como MAIL_*, REDIS_*, MEILISEARCH_*
    # ...
    ```

    O serviço `db` no arquivo `docker-compose.staging.yml` está configurado para usar `DB_DATABASE`, `DB_USERNAME`, e `DB_PASSWORD` de `.env.staging` para inicializar o contêiner do PostgreSQL.

### Executando o Ambiente

1. **Construa e suba os containers Docker:**
   Use o arquivo `docker-compose.staging.yml` para construir as imagens (se for a primeira vez ou se houverem alterações nos Dockerfiles) e iniciar os containers em modo detached (`-d`).

    ```bash
    docker compose -f docker-compose.staging.yml up --build -d
    ```

    Este comando irá:

    - Construir as imagens `lacina_app_staging` e outras, se necessário.
    - Iniciar todos os serviços definidos em `docker-compose.staging.yml` (app, nginx, db, redis, meilisearch, mailpit).
    - O container do banco de dados (`lacina_db_staging`) será inicializado com as credenciais e nome do banco de dados especificados em `.env.staging`.

2. **Aguarde o banco de dados ficar saudável:**
   O serviço `db` possui um `healthcheck`. Você pode verificar o status dos containers com:

    ```bash
    docker compose -f docker-compose.staging.yml ps
    ```

    Espere até que o container `lacina_db_staging` mostre o status `healthy`.

3. **Execute as migrations do banco de dados:**
   Após os containers estarem no ar e o banco de dados saudável, execute as migrations do Laravel dentro do container da aplicação (`lacina_app_staging`):

    ```bash
    docker compose -f docker-compose.staging.yml exec app php artisan migrate
    ```

    Se necessário, você também pode executar seeders:

    ```bash
    docker compose -f docker-compose.staging.yml exec app php artisan db:seed
    ```

4. **Acesse a aplicação:**
    - **Aplicação Lacina:** [http://localhost:5566](http://localhost:5566)
    - **Mailpit (servidor de e-mail para desenvolvimento/staging):** [http://localhost:5567](http://localhost:5567)

### Comandos Úteis do Docker Compose para Staging

- **Verificar logs dos containers:**

    ```bash
    docker compose -f docker-compose.staging.yml logs <nome_do_servico>
    # Exemplo para o app:
    docker compose -f docker-compose.staging.yml logs app
    # Exemplo para o banco de dados:
    docker compose -f docker-compose.staging.yml logs db
    ```

- **Parar os containers:**

    ```bash
    docker compose -f docker-compose.staging.yml down
    ```

- **Parar e remover volumes (cuidado, isso apagará os dados do banco, Redis, etc.):**

    ```bash
    docker compose -f docker-compose.staging.yml down -v
    ```

- **Acessar o shell de um container (ex: app):**

    ```bash
    docker compose -f docker-compose.staging.yml exec app bash
    ```

- **Reconstruir as imagens e subir:**

    ```bash
    docker compose -f docker-compose.staging.yml up --build -d --force-recreate
    ```

### Solução de Problemas Comuns no Ambiente de Staging

- **Erro `500 Internal Server Error` na aplicação:**

    - Verifique se as migrations foram executadas (passo "Execute as migrations...").
    - Verifique os logs do container da aplicação: `docker compose -f docker-compose.staging.yml logs app`.
    - Verifique se as permissões de arquivo/pasta estão corretas (especialmente para `storage` e `bootstrap/cache`). Se estiver executando o `artisan` localmente antes de subir os containers, pode haver conflito de permissões. Dentro do container, as permissões devem estar corretas.

- **Container `lacina_db_staging` não inicia ou está "unhealthy":**

    - Verifique se as variáveis `DB_DATABASE`, `DB_USERNAME`, e `DB_PASSWORD` estão corretamente configuradas no arquivo `.env.staging`.
    - Verifique os logs do container do banco de dados: `docker compose -f docker-compose.staging.yml logs db`.
    - Certifique-se de que não há outro processo utilizando a porta do PostgreSQL se você a expôs no `docker-compose.staging.yml` (por padrão, não está exposta para o host, apenas para a rede Docker).

- **Problemas de permissão com arquivos gerados pelo Artisan:**
  Se você executar comandos `artisan` localmente que criam arquivos (como `php artisan ide-helper:generate`), e depois tentar subir os containers, pode haver problemas de permissão. É geralmente melhor executar esses comandos dentro do container:

    ```bash
    docker compose -f docker-compose.staging.yml exec app php artisan <seu-comando>
    ```

## Comando para gera DBML do banco

```bash
docker run --rm \
  --network host \
  -v "$(pwd)":/docs \
  node:18-alpine \
  sh -c 'npm install -g @dbml/cli && db2dbml postgres "postgresql://sail:password@localhost:5432/laravel?schemas=public" -o /docs/database.dbml'
```

## Problemas de permissão

```sh
./vendor/bin/sail exec app chown -R sail:sail /var/www/html/public/build
```
