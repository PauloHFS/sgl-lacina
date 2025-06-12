# Prompt para Geração de Cards de Feature Ponta-a-Ponta

## Contexto

Você é um especialista em desenvolvimento full-stack que precisa gerar cards de implementação para features ponta-a-ponta no **Sistema de RH LaCInA-UFCG**, usando Laravel 11 + React 18 + Inertia.js + daisyUI.

## Estrutura de Cards Obrigatória

Para cada feature, gere exatamente **6 cards** na seguinte ordem:

### 1. **ANÁLISE & DESIGN** 🎯

- **Objetivo**: Definir requisitos, regras de negócio e arquitetura
- **Entregáveis**:
    - User stories detalhadas com critérios de aceitação
    - Regras de negócio e validações necessárias
    - Fluxo de dados e estados da aplicação
    - Mockups/wireframes das telas (se aplicável)
    - Definição de permissões e autorização

### 2. **DATABASE & MIGRATIONS** 🗄️

- **Objetivo**: Estrutura de dados e migrações
- **Entregáveis**:
    - Migrations com campos necessários e relacionamentos
    - Seeders para dados de teste/desenvolvimento
    - Índices de performance quando necessário
    - Validações de integridade referencial
    - Documentação do schema

### 3. **BACKEND (Models & Controllers)** ⚙️

- **Objetivo**: Lógica de negócio e APIs
- **Entregáveis**:
    - Models com relationships, casts e scopes
    - Controllers com actions CRUD + específicas
    - Form Requests para validação
    - Services/Actions para lógica complexa
    - Middleware de autorização (se necessário)

### 4. **FRONTEND (Components & Pages)** 🎨

- **Objetivo**: Interface de usuário responsiva
- **Entregáveis**:
    - Páginas Inertia.js com TypeScript
    - Componentes React reutilizáveis
    - Forms com validação cliente-servidor
    - Estilização com Tailwind + daisyUI
    - Estados de loading, erro e sucesso

### 5. **TESTES & QUALIDADE** 🧪

- **Objetivo**: Cobertura de testes e qualidade
- **Entregáveis**:
    - Testes Pest PHP para backend (Feature + Unit)
    - Testes Vitest para frontend (se necessário)
    - Testes de autorização e permissões
    - Testes de edge cases e validações
    - Code review checklist

### 6. **DOCUMENTAÇÃO & DEPLOY** 📚

- **Objetivo**: Documentação e preparação para produção
- **Entregáveis**:
    - Documentação técnica da feature
    - Guia de uso para usuários finais
    - Verificação de performance
    - Checklist de segurança
    - Plano de rollback (se necessário)

## Template de Card

````markdown
## Card X: [NOME DO CARD] [EMOJI]

### 📋 Descrição

[Descrição clara do que deve ser implementado]

### 🎯 Objetivos

- [ ] [Objetivo específico 1]
- [ ] [Objetivo específico 2]
- [ ] [Objetivo específico 3]

### 📦 Entregáveis

- [ ] [Entregável específico 1]
- [ ] [Entregável específico 2]
- [ ] [Entregável específico 3]

### 🔧 Implementação

#### Arquivos a Criar/Modificar:

- `path/to/file.php` - [Descrição]
- `path/to/component.tsx` - [Descrição]

#### Código Chave:

```php
// Exemplo de código relevante
```
````

### ✅ Critérios de Aceitação

- [ ] [Critério específico 1]
- [ ] [Critério específico 2]
- [ ] [Critério específico 3]

### 🚨 Pontos de Atenção

- [Ponto de atenção 1]
- [Ponto de atenção 2]

### 📊 Estimativa

**Complexidade**: [Baixa/Média/Alta]
**Tempo estimado**: [X horas]

### 🔗 Dependências

- Depende de: [Card anterior ou recurso]
- Bloqueia: [Card posterior ou recurso]

```

## Diretrizes Específicas

### Para Backend (Laravel):
- Usar enums existentes do projeto
- Implementar FormRequests para validação
- Aplicar policies para autorização
- Usar transações para operações complexas
- Seguir padrão de nomenclatura do projeto

### Para Frontend (React + Inertia):
- Componentes funcionais com TypeScript
- useForm hook do Inertia para formulários
- daisyUI classes prioritárias sobre Tailwind customizado
- Tratamento de estados de loading/error/success
- Responsividade mobile-first

### Para Testes:
- Pest PHP com descrições em português
- Cobertura mínima: happy path + edge cases
- Testes de autorização obrigatórios
- Factory/Seeder para dados de teste

### Para Segurança:
- Validação server-side obrigatória
- Autorização em todas as rotas
- Sanitização de inputs
- Rate limiting quando aplicável

## Prompt de Uso

**Para gerar os cards, use este formato:**

```

Gere os 6 cards de implementação para a feature: "[NOME DA FEATURE]"

Descrição da feature: [DESCRIÇÃO DETALHADA]

User stories principais:

- Como [persona], eu quero [ação] para [benefício]
- Como [persona], eu quero [ação] para [benefício]

Contexto adicional: [INFORMAÇÕES ESPECÍFICAS DO DOMÍNIO]

```

**Exemplo de uso:**
```

Gere os 6 cards de implementação para a feature: "Gestão de Folgas dos Colaboradores"

Descrição da feature: Sistema para registro e aprovação de folgas (férias, licenças, faltas) dos colaboradores do laboratório, com calendário visual e relatórios.

User stories principais:

- Como colaborador, eu quero solicitar folgas informando período e motivo para que sejam avaliadas pelos coordenadores
- Como coordenador, eu quero aprovar/rejeitar solicitações de folga para manter controle da equipe
- Como docente, eu quero visualizar calendário de folgas da equipe para planejamento de projetos

Contexto adicional: Integração com sistema de projetos existente, notificações por email, diferentes tipos de folga (remunerada, não-remunerada, etc.)

```

## Stack Tecnológica do Projeto

- **Backend**: Laravel 11 + PHP 8.4
- **Frontend**: React 18 + Inertia.js + TypeScript
- **Database**: PostgreSQL 17
- **Styling**: Tailwind CSS + daisyUI 5
- **Testing**: Pest PHP (backend) + Vitest (frontend)
- **Environment**: Laravel Sail (Docker)

## Padrões do Projeto

### Nomenclatura:
- **Controllers**: `{Entidade}Controller` (ex: `ProjetosController`)
- **Models**: Singular em PascalCase (ex: `User`, `Projeto`)
- **Routes**: Usar resource routes quando possível
- **Components**: PascalCase (ex: `UserProfile.tsx`)
- **Testes**: Descrições em português, usar `test()` ou `it()`

### Regras Importantes:
- ✅ **SEMPRE** usar enums existentes (StatusCadastro, TipoVinculo, etc.)
- ✅ **SEMPRE** validar permissões antes de operações sensíveis
- ✅ **SEMPRE** usar transações DB para operações complexas
- ✅ **SEMPRE** incluir testes para novas funcionalidades
- ❌ **NUNCA** quebrar backward compatibility sem discussão
- ❌ **NUNCA** expor dados sensíveis em logs ou respostas
- ❌ **NUNCA** hardcodar valores que devem ser configuráveis

## Contexto Acadêmico

Este é um sistema interno para o **Laboratório de Computação Inteligente Aplicada (LaCInA) da UFCG**, focado em:
- Gestão de colaboradores (discentes, externos, docentes)
- Administração de projetos e vínculos
- Processos de RH digitalizados
- Relatórios para comprovação acadêmica
- Autorização baseada em tipos de vínculo e status
```
