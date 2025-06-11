# Relatório Técnico de Refatoração: Laravel + Inertia.js

## Introdução

Este relatório detalha as áreas de foco para a refatoração da codebase, visando melhorias em segurança, performance, manutenibilidade, escalabilidade e confiabilidade. As recomendações seguem as melhores práticas para Laravel (PHP) e Inertia.js (React/TypeScript).

---

## 1. Segurança

### 1.1. Validação de Dados (Backend e Frontend)

- **Item:** Implementar/Revisar validação em todas as entradas de usuário.
- **Descrição:** Utilizar Form Requests no Laravel para validação no backend e bibliotecas como Zod ou Yup no frontend com `useForm` do Inertia.js.
- **Justificativa:** Prevenir dados malformados ou maliciosos de atingir a lógica de negócios ou o banco de dados. A validação no frontend melhora a UX, mas a do backend é crucial para segurança.
- **Prioridade:** Alta
- **Exemplo (Laravel Form Request):**
    ```php
    // app/Http/Requests/StoreProjetoRequest.php
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            // ... outras regras
        ];
    }
    ```
- **Exemplo (React com Zod e `useForm`):**

    ```typescript
    // resources/js/Pages/Projetos/Create.tsx
    import { useForm } from '@inertiajs/react';
    import { z } from 'zod';

    const schema = z.object({
        nome: z.string().min(1, 'Nome é obrigatório'),
        // ... outras regras
    });

    // ... dentro do componente
    const { data, setData, post, errors, processing } = useForm({
        nome: '',
        // ... outros campos
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        // Validação pode ser integrada aqui antes do post
        // ou confiar na validação do backend retornada pelo Inertia
        post(route('projetos.store'));
    };
    ```

### 1.2. Prevenção de XSS (Cross-Site Scripting)

- **Item:** Garantir que todas as saídas de dados no frontend sejam escapadas.
- **Descrição:** React já escapa dados por padrão em JSX. Para dados inseridos via `dangerouslySetInnerHTML`, garantir que o HTML seja sanitizado. No Laravel, Blade escapa por padrão; Inertia.js também lida bem com isso ao passar props.
- **Justificativa:** Evitar a execução de scripts maliciosos no navegador do usuário.
- **Prioridade:** Alta
- **Exemplo (React - Correto por padrão):**
    ```jsx
    // Não vulnerável
    <div>{userData.name}</div>
    ```
- **Exemplo (Laravel - Correto por padrão com Inertia):**
    ```php
    // Controller
    return Inertia::render('Profile/Show', ['user' => $user]);
    // React Page (props.user.name será escapado)
    ```

### 1.3. Prevenção de CSRF (Cross-Site Request Forgery)

- **Item:** Verificar se todas as rotas de `POST`, `PUT`, `PATCH`, `DELETE` estão protegidas por CSRF token.
- **Descrição:** Laravel já inclui middleware CSRF por padrão. Inertia.js, quando usado com Axios (configurado pelo Breeze/Laravel), envia o token `X-XSRF-TOKEN` automaticamente.
- **Justificativa:** Impedir que um site malicioso execute ações em nome de um usuário autenticado.
- **Prioridade:** Alta
- **Verificação:** Confirmar que o middleware `VerifyCsrfToken` está ativo e não há exclusões desnecessárias.

### 1.4. Prevenção de SQL Injection

- **Item:** Utilizar exclusivamente Eloquent ORM ou Query Builder com bindings.
- **Descrição:** Evitar o uso de queries SQL raw ou, se inevitável, garantir que todos os parâmetros sejam sanitizados e escapados corretamente usando `DB::raw` com bindings.
- **Justificativa:** Eloquent e Query Builder usam parameter binding, o que previne SQL Injection.
- **Prioridade:** Alta
- **Exemplo (Eloquent - Seguro):**
    ```php
    $users = User::where('email', $request->input('email'))->get();
    ```
- **Exemplo (Query Raw - Cuidado):**

    ```php
    // Inseguro se $searchTerm não for sanitizado
    // $results = DB::select(DB::raw("SELECT * FROM users WHERE name = '$searchTerm'"));

    // Seguro com bindings
    $results = DB::select(DB::raw("SELECT * FROM users WHERE name = :searchTerm"), ['searchTerm' => $searchTerm]);
    ```

### 1.5. Gerenciamento de Senhas

- **Item:** Utilizar hashing forte para senhas e políticas de senha adequadas.
- **Descrição:** Laravel usa Bcrypt por padrão, o que é bom. Considerar a implementação de políticas de senha (comprimento, complexidade) e mecanismos de recuperação de senha seguros.
- **Justificativa:** Proteger as credenciais dos usuários contra acesso não autorizado, mesmo em caso de vazamento do banco de dados.
- **Prioridade:** Alta
- **Verificação:** Confirmar uso de `Hash::make()` para senhas e `Hash::check()` para verificação.

### 1.6. Uso de Dependências Seguras

- **Item:** Manter dependências (Composer e npm) atualizadas e auditar vulnerabilidades.
- **Descrição:** Utilizar `composer outdated` e `npm outdated`. Ferramentas como `GitHub Dependabot` ou `npm audit` / `yarn audit` para identificar e corrigir vulnerabilidades conhecidas.
- **Justificativa:** Dependências desatualizadas são um vetor comum de ataque.
- **Prioridade:** Alta
- **Ação:** Executar `composer update` e `npm update` (ou `yarn upgrade`) regularmente após análise de impacto.

### 1.7. Controle de Acesso (Auth/ACL)

- **Item:** Implementar/Revisar Policies e Gates do Laravel para controle de acesso granular.
- **Descrição:** Definir Policies para modelos Eloquent e usar Gates para ações mais genéricas. Verificar se todas as rotas e ações sensíveis estão protegidas.
- **Justificativa:** Garantir que usuários só possam acessar recursos e executar ações para as quais têm permissão.
- **Prioridade:** Alta
- **Exemplo (Policy):**
    ```php
    // app/Policies/ProjetoPolicy.php
    public function update(User $user, Projeto $projeto): bool
    {
        // Lógica para verificar se o usuário pode atualizar o projeto
        return $user->id === $projeto->coordenador_id; // Exemplo simplificado
    }
    ```

### 1.8. Gerenciamento de Env/Secrets

- **Item:** Nunca commitar o arquivo `.env`. Utilizar variáveis de ambiente para todas as credenciais e configurações sensíveis.
- **Descrição:** O arquivo `.env.example` deve ser commitado como um template. Em produção, as variáveis de ambiente devem ser configuradas diretamente no servidor ou através de serviços de gerenciamento de segredos.
- **Justificativa:** Evitar a exposição de chaves de API, senhas de banco de dados e outras informações sensíveis no repositório de código.
- **Prioridade:** Alta

---

## 2. Performance

### 2.1. Otimização de Queries de Banco de Dados (Laravel Eloquent)

- **Item:** Identificar e otimizar queries lentas, especialmente o problema N+1.
- **Descrição:** Utilizar eager loading (`with()`, `load()`) para evitar múltiplas queries ao carregar relacionamentos. Usar `select()` para buscar apenas colunas necessárias. Analisar queries com Laravel Telescope, Debugbar ou logs de query do banco.
- **Justificativa:** Reduzir o tempo de resposta do backend e a carga no servidor de banco de dados.
- **Prioridade:** Alta
- **Exemplo (N+1 vs Eager Loading):**

    ```php
    // Problema N+1
    // $projetos = Projeto::all();
    // foreach ($projetos as $projeto) { echo $projeto->coordenador->name; }

    // Solução com Eager Loading
    $projetos = Projeto::with('coordenador')->get();
    foreach ($projetos as $projeto) { echo $projeto->coordenador->name; }
    ```

### 2.2. Cache

- **Item:** Implementar caching para dados frequentemente acessados e raramente modificados.
- **Descrição:** Utilizar o sistema de Cache do Laravel (Redis é uma boa opção, já na stack). Cachear resultados de queries complexas, configurações, etc. Considerar cache HTTP (ETags, Cache-Control).
- **Justificativa:** Reduzir a latência e a carga no banco de dados/aplicação.
- **Prioridade:** Média
- **Exemplo (Laravel Cache):**
    ```php
    $users = Cache::remember('all_users', now()->addHour(), function () {
        return User::all();
    });
    ```

### 2.3. Otimização de Assets (JS/CSS/Imagens)

- **Item:** Minificar JS/CSS, otimizar imagens.
- **Descrição:** Vite já lida com minificação de JS/CSS em builds de produção (`npm run build`). Para imagens, usar formatos otimizados (WebP), compressão (e.g., TinyPNG) e lazy loading.
- **Justificativa:** Reduzir o tamanho dos arquivos transferidos, melhorando o tempo de carregamento da página.
- **Prioridade:** Média
- **Ação:** Garantir que `npm run build` seja usado para produção. Implementar otimização de imagens no processo de upload ou via scripts.

### 2.4. Code Splitting (React)

- **Item:** Dividir o código JavaScript em chunks menores, carregados sob demanda.
- **Descrição:** Utilizar `React.lazy` e `Suspense` para carregar componentes de página ou componentes pesados apenas quando necessários. Vite suporta code splitting automaticamente para importações dinâmicas.
- **Justificativa:** Reduzir o tamanho inicial do bundle JavaScript, melhorando o Time to Interactive (TTI).
- **Prioridade:** Média
- **Exemplo (React.lazy):**
    ```typescript
    // resources/js/app.tsx (ou em rotas)
    import React, { lazy, Suspense } from 'react';
    // ...
    const AdminDashboard = lazy(() => import('@/Pages/Admin/Dashboard'));
    // ...
    // Em algum lugar no roteamento ou renderização condicional:
    // <Suspense fallback={<div>Loading...</div>}>
    //   <AdminDashboard />
    // </Suspense>
    ```

### 2.5. Lazy Loading (React/Imagens)

- **Item:** Carregar imagens e componentes apenas quando estiverem visíveis na tela.
- **Descrição:** Para imagens, usar o atributo `loading="lazy"`. Para componentes React, combinar com `React.lazy` ou usar bibliotecas como `react-intersection-observer`.
- **Justificativa:** Melhorar a performance percebida e economizar banda, especialmente em páginas longas com muitas imagens ou componentes complexos.
- **Prioridade:** Média
- **Exemplo (Imagem):**
    ```html
    <img src="image.jpg" alt="Descrição" loading="lazy" />
    ```

### 2.6. Web Vitals

- **Item:** Monitorar e otimizar Core Web Vitals (LCP, FID, CLS).
- **Descrição:** Utilizar ferramentas como Google PageSpeed Insights, Lighthouse, e o report de Core Web Vitals no Google Search Console. Focar em otimizar o carregamento do maior conteúdo visível (LCP), a interatividade (FID/INP), e a estabilidade visual (CLS).
- **Justificativa:** Melhorar a experiência do usuário e potencialmente o ranking SEO.
- **Prioridade:** Média

---

## 3. Manutenibilidade/Escalabilidade

### 3.1. Estrutura de Pastas (Laravel e React)

- **Item:** Revisar e padronizar a estrutura de pastas.
- **Descrição:** Seguir as convenções do Laravel (`app/Http/Controllers`, `app/Models`, etc.) e React (`resources/js/Pages`, `resources/js/Components`, `resources/js/Layouts`). Para componentes React, agrupar por funcionalidade ou tipo.
- **Justificativa:** Facilitar a navegação, localização de arquivos e entendimento do projeto por novos desenvolvedores.
- **Prioridade:** Baixa (se já estiver razoavelmente organizada) / Média (se confusa)

### 3.2. Padrões de Projeto (SRP, DRY, SOLID)

- **Item:** Aplicar princípios SOLID e padrões como SRP (Single Responsibility Principle) e DRY (Don't Repeat Yourself).
- **Descrição:** Refatorar classes e métodos longos e complexos. Extrair lógica de negócios dos controllers para Services, Actions ou Models. Criar componentes React reutilizáveis.
- **Justificativa:** Tornar o código mais modular, testável, fácil de entender e modificar.
- **Prioridade:** Média

### 3.3. Testes Unitários e de Integração

- **Item:** Aumentar a cobertura de testes.
- **Descrição:** Escrever testes para todas as funcionalidades críticas. No Laravel, usar Pest/PHPUnit para testes de unidade (Models, Services) e feature (Controllers, rotas). No React, usar Vitest e React Testing Library para testes de componentes. Considerar Laravel Dusk para E2E.
- **Justificativa:** Garantir a estabilidade do código, facilitar refatorações seguras e documentar o comportamento esperado.
- **Prioridade:** Alta
- **Exemplo (Pest - Laravel):**

    ```php
    // tests/Feature/ProjetoTest.php
    test('docente pode criar projeto', function () {
        $docente = User::factory()->docente()->create();
        $this->actingAs($docente);
        $dadosProjeto = Projeto::factory()->make()->toArray();

        $response = $this->post(route('projetos.store'), $dadosProjeto);

        $response->assertRedirect(route('projetos.index'));
        $this->assertDatabaseHas('projetos', ['nome' => $dadosProjeto['nome']]);
    });
    ```

### 3.4. Documentação de Código (PHPDoc, JSDoc)

- **Item:** Adicionar/Melhorar PHPDoc para classes e métodos PHP, e JSDoc/TSDoc para funções e componentes React/TypeScript.
- **Descrição:** Documentar o propósito, parâmetros e tipos de retorno de funções e métodos. Descrever props de componentes React.
- **Justificativa:** Facilitar o entendimento do código e a colaboração entre desenvolvedores.
- **Prioridade:** Média

### 3.5. Padronização de Código

- **Item:** Configurar e impor o uso de linters e formatadores.
- **Descrição:** Utilizar ESLint e Prettier para JavaScript/TypeScript (configurações já parecem existir: `.eslintrc.json`, `.prettierrc`). Para PHP, considerar PHP-CS-Fixer ou Pint (Laravel) para seguir PSR-12, e ferramentas de análise estática como PHPStan ou Psalm.
- **Justificativa:** Manter um estilo de código consistente, prevenir erros comuns e melhorar a legibilidade.
- **Prioridade:** Média
- **Ação:** Integrar aos hooks de pré-commit (e.g., Husky) e CI/CD.

### 3.6. Módulos Reutilizáveis

- **Item:** Identificar e criar componentes React reutilizáveis e Traits/Services no Laravel.
- **Descrição:** Abstrair lógica e UI comuns em componentes React genéricos. No Laravel, usar Traits para compartilhar métodos entre Models ou Controllers, e Services para encapsular lógica de negócios complexa.
- **Justificativa:** Reduzir duplicação de código (DRY) e promover a reutilização.
- **Prioridade:** Média

---

## 4. Confiabilidade

### 4.1. Tratamento de Erros

- **Item:** Implementar tratamento de exceções robusto no backend e Error Boundaries no frontend.
- **Descrição:** No Laravel, customizar o Handler de Exceções (`app/Exceptions/Handler.php`) para tratar exceções específicas e retornar respostas apropriadas (e.g., JSON para API, páginas de erro para web). No React, usar Error Boundaries para capturar erros em componentes e exibir uma UI de fallback.
- **Justificativa:** Prevenir quebras abruptas da aplicação e fornecer feedback útil ao usuário ou logs detalhados para desenvolvedores.
- **Prioridade:** Alta
- **Exemplo (React Error Boundary):**

    ```typescript
    // resources/js/Components/ErrorBoundary.tsx
    import React, { Component, ErrorInfo, ReactNode } from 'react';

    interface Props {
      children: ReactNode;
      fallbackUI?: ReactNode;
    }
    interface State {
      hasError: boolean;
    }

    class ErrorBoundary extends Component<Props, State> {
      public state: State = { hasError: false };

      public static getDerivedStateFromError(_: Error): State {
        return { hasError: true };
      }

      public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
        console.error("Uncaught error:", error, errorInfo);
        // Logar para Sentry ou similar aqui
      }

      public render() {
        if (this.state.hasError) {
          return this.props.fallbackUI || <h1>Algo deu errado.</h1>;
        }
        return this.props.children;
      }
    }
    export default ErrorBoundary;
    ```

### 4.2. Logging

- **Item:** Configurar logging detalhado e centralizado.
- **Descrição:** Utilizar os canais de log do Laravel para registrar eventos importantes, erros e informações de debug. Integrar com um serviço de logging centralizado como Sentry, Papertrail ou ELK stack para facilitar a análise e monitoramento.
- **Justificativa:** Essencial para diagnosticar problemas em produção e entender o comportamento da aplicação.
- **Prioridade:** Alta
- **Configuração:** Revisar `config/logging.php`. Considerar adicionar contexto aos logs (ID do usuário, request ID).

### 4.3. Monitoramento

- **Item:** Implementar monitoramento de performance da aplicação (APM) e saúde do servidor.
- **Descrição:** Utilizar ferramentas como Laravel Telescope (para desenvolvimento/staging), New Relic, Datadog ou Sentry APM. Monitorar métricas do servidor (CPU, memória, disco), taxas de erro, tempos de resposta.
- **Justificativa:** Identificar proativamente gargalos de performance e problemas antes que afetem os usuários.
- **Prioridade:** Média (para produção)

### 4.4. Resiliência

- **Item:** Projetar a aplicação para lidar com falhas parciais.
- **Descrição:** Utilizar Queues (com Redis, já na stack) para tarefas demoradas ou que podem falhar, permitindo retentativas. Implementar timeouts e circuit breakers para chamadas a serviços externos.
- **Justificativa:** Aumentar a disponibilidade da aplicação, mesmo quando componentes individuais falham.
- **Prioridade:** Média
- **Exemplo (Laravel Queue Job):**
    ```php
    // app/Jobs/ProcessarRelatorio.php
    class ProcessarRelatorio implements ShouldQueue {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
        // ...
        public function handle() { /* ... lógica demorada ... */ }
    }
    // Disparando o job:
    // ProcessarRelatorio::dispatch($dados);
    ```

---
