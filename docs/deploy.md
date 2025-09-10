# Guia de Deploy para Produção - SGL LaCInA

Este guia detalha o processo de deploy e gerenciamento do Sistema de Gestão de Laboratório (SGL) do LaCInA em ambiente de produção, utilizando uma interface de linha de comando centralizada via Makefile.

## Pré-requisitos

O servidor de produção deve ter os seguintes componentes instalados:

- Sistema Operacional: Ubuntu 24.04+ ou Debian 11+
- Ambiente de Containerização:
    - Docker Engine 24.0+
    - Docker Compose v2.0+
- Gerenciamento de Código: Git
- Utilitário de Build: Make

## Processo de Deploy

O processo de deploy da aplicação é unificado no comando make deploy. Este comando orquestra todas as etapas necessárias de forma segura e automatizada.

1. Clonar o Repositório:

```bash
git clone [https://github.com/seu-usuario/sgl-lacina.git](https://github.com/seu-usuario/sgl-lacina.git) /opt/sgl-lacina
cd /opt/sgl-lacina
```

2. Configurar Variáveis de Ambiente:

- Crie o arquivo `.env` para produção a partir do `.env.example`.
- Preencha as variáveis de ambiente com as informações do seu ambiente.

3. Executar o Deploy:

```bash
make deploy
```

Este comando executa a seguinte sequência de ações:

- `backup` Cria um backup completo do banco de dados e dos arquivos.
- `pull`: Atualiza o código-fonte via git pull.
- `stop-app`: Para os contêineres da aplicação para garantir consistência.
- `build`: Reconstrói as imagens Docker.
- `start`: Inicia todos os contêineres da aplicação.
- `post-deploy`: Executa migrações, otimiza caches e ajusta permissões.

## Comandos de Gerenciamento

### Backup e Restauração

| Comando             | Descrição                                                                                       |
| ------------------- | ----------------------------------------------------------------------------------------------- |
| `make backup`       | Cria backups do banco de dados, arquivos e `.env`.                                              |
| `make restore`      | Restaura a aplicação a partir de um backup. Requer o argumento `DB=<arquivo>` e `CONFIRM=true`. |
| `make list-backups` | Lista os backups disponíveis no diretório `./backups`.                                          |

#### Exemplo de Restauração:

```bash
make restore DB=db_backup_20250910_123000.sql.gz CONFIRM=true
```

### Rollback

| Comando              | Descrição                                                                                                                                        |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| `make rollback`      | Reverte o código da aplicação para um ou mais commits anteriores. Requer `CONFIRM=true`. Use `COMMITS=<numero>` para reverter múltiplos commits. |
| `make rollback-undo` | Desfaz o último rollback. Requer CONFIRM=true.                                                                                                   |

### Monitoramento e Saúde

| Comando             | Descrição                                                                     |
| ------------------- | ----------------------------------------------------------------------------- |
| `make health-check` | Verifica a saúde dos contêineres e serviços, além do uso de recursos do host. |
| `make status`       | Exibe o status de todos os contêineres Docker.                                |
| `make logs`         | Exibe os logs dos contêineres em tempo real.                                  |

### Utilitários

| Comando      | Descrição                                                  |
| ------------ | ---------------------------------------------------------- |
| `make shell` | Acessa o terminal `bash` do contêiner da aplicação.        |
| `make db`    | Acessa o cliente `psql` do contêiner do banco de dados.    |
| `make clean` | Remove recursos Docker não utilizados para liberar espaço. |

## Solução de Problemas

Em caso de falha no deploy ou na aplicação:

1. Verifique os Logs: Utilize `make logs` para monitorar os logs dos contêineres.
2. Verifique a Saúde: Execute `make health-check` para identificar a causa do problema (contêiner parado, banco de dados inacessível, etc.).
3. Inicie o Rollback: Se necessário, execute `make rollback CONFIRM=true` para reverter o código e restaurar a aplicação ao estado anterior.

## Segurança

- Firewall: Habilite o firewall para permitir apenas o tráfego necessário (portas 80, 443, 22).
- SSL/HTTPS: Configure um certificado SSL para a aplicação.

## Manutenção

- Backups: O processo `make deploy` inclui um backup automático. Backups manuais podem ser feitos com `make backup`.
- Limpeza: O comando `make clean` pode ser usado para remover recursos obsoletos do Docker.
