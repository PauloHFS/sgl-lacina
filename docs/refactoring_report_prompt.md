# Prompt para Geração de Relatório Técnico de Refatoração

Você é um arquiteto de software sênior especializado em Laravel e React/Inertia.js. Sua tarefa é criar um relatório técnico completo de refatoração para o Sistema de Recursos Humanos do LaCInA-UFCG.

## Contexto do Projeto

- **Sistema**: Gestão de RH para laboratório acadêmico
- **Stack**: Laravel 11 + React 18 + Inertia.js + TypeScript + PostgreSQL 17 + Tailwind CSS + daisyUI
- **Funcionalidades**: Cadastro de colaboradores, gestão de projetos, solicitações, relatórios

## Estrutura Obrigatória do Relatório

### 1. SEGURANÇA (Prioridade: CRÍTICA)

- Validação de dados (Form Requests + Zod/Yup)
- Prevenção XSS, CSRF, SQL Injection
- Controle de acesso (Policies/Gates)
- Gerenciamento de senhas e secrets
- Auditoria de dependências

### 2. PERFORMANCE (Prioridade: ALTA)

- Otimização de queries (N+1, eager loading)
- Sistema de cache (Redis)
- Otimização de assets (code splitting, lazy loading)
- Monitoramento de Web Vitals

### 3. MANUTENIBILIDADE (Prioridade: MÉDIA)

- Estrutura de pastas e arquivos
- Aplicação de princípios SOLID/DRY
- Cobertura de testes (Pest + Vitest)
- Documentação de código (PHPDoc/TSDoc)
- Padronização (ESLint, Prettier, PHP-CS-Fixer)

### 4. ESCALABILIDADE (Prioridade: MÉDIA)

- Arquitetura modular
- Componentes reutilizáveis
- Patterns de design adequados
- Preparação para crescimento de dados/usuários

### 5. CONFIABILIDADE (Prioridade: ALTA)

- Tratamento de erros (Error Boundaries)
- Logging centralizado
- Monitoramento APM
- Resiliência (Queues, circuit breakers)

## Formato de Cada Seção

Para cada item identificado, incluir:

- **Item**: Nome claro da melhoria
- **Descrição**: Explicação técnica detalhada
- **Justificativa**: Por que é importante
- **Prioridade**: Alta/Média/Baixa
- **Exemplo**: Código antes/depois quando aplicável
- **Ferramentas**: Bibliotecas/packages recomendados

## Diretrizes Específicas

### Para Laravel (Backend):

- Seguir PSR-12 e convenções do Laravel
- Usar Eloquent ORM e evitar SQL raw
- Implementar Form Requests para validação
- Aplicar Policies para autorização
- Utilizar Queues para operações demoradas

### Para React/Inertia.js (Frontend):

- Componentes funcionais com TypeScript strict
- Usar hooks e Context API adequadamente
- Implementar Error Boundaries
- Otimizar com React.lazy e Suspense
- Validação com Zod ou similar

### Para Database (PostgreSQL):

- Índices otimizados
- Queries eficientes
- Migrations versionadas
- Backup e recovery strategies

## Critérios de Qualidade

1. **Exemplos de código**: Devem ser funcionais e seguir as melhores práticas
2. **Priorização**: Baseada em impacto de segurança, performance e manutenibilidade
3. **Viabilidade**: Considerar o contexto acadêmico e recursos disponíveis
4. **Compatibilidade**: Manter backward compatibility quando possível
5. **Documentação**: Cada recomendação deve ser auto-explicativa

## Resultado Esperado

Um relatório em Markdown contendo:

- Introdução com overview das melhorias
- Seções detalhadas por categoria
- Exemplos práticos de código
- Cronograma de implementação sugerido
- Métricas de sucesso para cada melhoria

O relatório deve servir como um guia prático para a equipe de desenvolvimento implementar as melhorias de forma incremental e segura.
