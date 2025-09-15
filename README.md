# SGL - LaCInA

O SGL foi desenvolvido como uma plataforma web centralizada para atender aos requisitos de informação do LACINA, automatizando processos e otimizando a alocação de recursos.

[Paper](https://drive.google.com/file/d/1DGJ1X8XbEowwcvHyeO0IlEcSHr7eRtOD/view?usp=sharing)

## Pré-requisitos

- Docker
- Docker Compose

## Instalação

1.  **Clonar o repositório:**

    ```bash
    git clone <repository-url>
    cd <repository-name>
    ```

2.  **Copiar arquivo de ambiente:**

    ```bash
    cp .env.example .env
    ```

    _Ajuste as variáveis de ambiente no arquivo `.env` se necessário._

3.  **Instalar dependências com Composer:**

    ```bash
    docker run --rm -v $(pwd):/app composer:latest composer install
    ```

4.  **Iniciar os containers:**

    ```bash
    ./vendor/bin/sail up -d
    ```

    _A partir daqui recomendo abrir o devcontainer no vscode em vez de executar os comandos pelo docker._

5.  **Gerar a chave da aplicação:**

    ```bash
    docker exec -it sgl-lacina_app_dev php artisan key:generate
    ```

6.  **Executar as migrações do banco de dados:**

    ```bash
    docker exec -it sgl-lacina_app_dev php artisan migrate
    ```

7. **Instale as dependências do Front End**

    ```bash
    docker exec -it sgl-lacina_app_dev npm install
    ```

8. **Execute o Front End**

    ```bash
    docker exec -it sgl-lacina_app_dev npm run dev
    ```

A aplicação estará disponível em `http://localhost`.

## Gerenciamento em Produção

Utilize o `Makefile` para automatizar tarefas comuns de deploy, backup e rollback.

**Uso:**

```bash
make [comando]
```

**Comandos principais:**

- `make deploy` - Executa o deploy completo.
- `make backup` - Cria um backup do banco e arquivos.
- `make restore` - Restaura a partir de um backup.
- `make rollback` - Reverte para um commit anterior.
- `make health-check` - Verifica a saúde da aplicação.
- `make help` - Exibe a lista completa de comandos e descrições.

## Desenvolvimento

### Ambiente

O ambiente de desenvolvimento é gerenciado pelo Laravel Sail. Todos os comandos (Artisan, Composer, NPM) devem ser executados através do Sail.

**Exemplo:**

```bash
# Executar testes
./vendor/bin/sail artisan test

# Abrir um shell no container da aplicação
./vendor/bin/sail shell
```

### Alias para Sail (Opcional)

Para simplificar a execução dos comandos, configure um alias global para `sail` em seu ambiente shell. Adicione a seguinte linha ao seu arquivo de configuração (`~/.bashrc`, `~/.zshrc`, etc.):

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

Após adicionar o alias, reinicie seu terminal ou execute `source ~/.bashrc` (ou o arquivo correspondente) para aplicar as alterações. Os comandos poderão ser executados diretamente com `sail`.

**Exemplo:**

```bash
# Antes
./vendor/bin/sail up

# Depois
sail up
```

### VS Code (Dev Container)

Para utilizar o ambiente de desenvolvimento encapsulado, use a extensão "Remote - Containers" do VS Code.

1.  Abra o projeto no VS Code.
2.  Pressione `F1` e selecione `Remote-Containers: Reopen in Container`.

### Documentação Adicional

- **[Guia de Testes](docs/testing.md)**: Como executar e escrever testes.
- **[Exemplos de JSONB](docs/examples/jsonb_examples.php)**: Manipulação de JSONB no Eloquent.
- **[Deploy de Produção](docs/deploy.md)**: Documentação de deploy em produção.
