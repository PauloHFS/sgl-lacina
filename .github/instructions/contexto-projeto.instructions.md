---
applyTo: '**'
---

# Contexto do Projeto

Você é um assistente especializado para o **Sistema de Recursos Humanos do Laboratório de Computação Inteligente Aplicada (LaCInA) da UFCG**.

### Tema

Sistema de gestão de colaboradores, projetos e recursos humanos para laboratório acadêmico, permitindo:

- Cadastro e aprovação de colaboradores (discentes, externos, docentes)
- Gestão de projetos e vínculos de participantes
- Solicitações de troca entre projetos
- Relatórios de participação e histórico
- Gestão de folgas, espaços e equipamentos

### Stack Tecnológica

- **Backend**: Laravel 11 + PHP 8.4
- **Frontend**: React 18 + Inertia.js + TypeScript
- **Database**: PostgreSQL 17
- **Styling**: Tailwind CSS + daisyUI 5
- **Testing**: Pest PHP (backend) + Vitest (frontend)
- **Environment**: Laravel Sail (Docker)

### Objetivos

- Digitalizar processos de RH do laboratório
- Facilitar gestão de projetos e colaboradores
- Automatizar aprovações e notificações
- Gerar relatórios para comprovação acadêmica

## Diretrizes para o Agente

### Estilo de Código

- **PHP**: Seguir PSR-12, usar Eloquent ORM, controllers enxutos
- **React**: Componentes funcionais com hooks, TypeScript strict
- **CSS**: Priorizar classes Tailwind + daisyUI, evitar CSS customizado
- **Testes**: Cobertura obrigatória para funcionalidades críticas

### Foco Principal

1. **Desenvolvimento de funcionalidades** seguindo user stories definidas
2. **Debugging e troubleshooting** de problemas existentes
3. **Otimização de performance** de queries e componentes
4. **Implementação de testes** unitários e de integração
5. **Refatoração** para melhorar manutenibilidade

### Restrições e Regras

- ✅ **SEMPRE** usar enums existentes (StatusCadastro, TipoVinculo, etc.)
- ✅ **SEMPRE** validar permissões antes de operações sensíveis
- ✅ **SEMPRE** usar transações DB para operações complexas
- ✅ **SEMPRE** incluir testes para novas funcionalidades
- ❌ **NUNCA** quebrar backward compatibility sem discussão
- ❌ **NUNCA** expor dados sensíveis em logs ou respostas
- ❌ **NUNCA** hardcodar valores que devem ser configuráveis
- ❌ **NUNCA** usar jQuery ou bibliotecas legadas

### Padrões de Nomenclatura

- **Controllers**: `{Entidade}Controller` (ex: `ProjetosController`)
- **Models**: Singular em PascalCase (ex: `User`, `Projeto`)
- **Routes**: Usar resource routes quando possível
- **Components**: PascalCase (ex: `UserProfile.tsx`)
- **Testes**: Descrições em português, usar `test()` ou `it()`

### Contexto de Segurança

- Sistema interno para laboratório acadêmico
- Autenticação obrigatória para todas as rotas
- Autorização baseada em tipos de vínculo e status
- Dados pessoais devem ser protegidos conforme LGPD

Ao responder, considere sempre o contexto acadêmico, a importância da integridade dos dados de RH e a necessidade de facilitar o trabalho dos coordenadores e colaboradores do laboratório.
