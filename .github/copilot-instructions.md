# Instruções para o GitHub Copilot

## Stack Tecnológica

- **Backend**: Laravel 11, PHP 8.4
- **Frontend**: React 18 com Inertia.js
- **Banco de Dados**: PostgreSQL 17
- **Ambiente de Desenvolvimento**: Laravel Sail (Docker)
- **Gerenciador de Pacotes**: Composer (PHP), npm (JavaScript)
- **Build Tool**: Vite
- **Serviços Adicionais**:
    - Redis
    - Meilisearch
    - Mailpit

## Convenções de Código

### PHP (Laravel)

- Seguir o padrão PSR-12.
- Utilizar Eloquent ORM para interações com o banco de dados.
- Nomear métodos de controladores com verbos HTTP (index, store, update, destroy).
- Utilizar rotas nomeadas (`Route::name('...')`) para facilitar a manutenção.

### JavaScript (React)

- Utilizar componentes funcionais com hooks.
- Organizar componentes em pastas por funcionalidade.
- Utilizar o `useForm` do Inertia.js para manipulação de formulários.
- Nomear arquivos de componentes com PascalCase (e.g., `UserProfile.tsx`).

### Estilização

- Utilizar Tailwind CSS para estilos utilitários.
- Integrar o daisyUI para componentes pré-estilizados e temas personalizáveis.
- Evitar estilos inline; preferir classes do Tailwind e componentes do daisyUI.

## Estrutura de Pastas

### Backend (Laravel)

- `app/Models`: Modelos Eloquent.
- `app/Http/Controllers`: Controladores HTTP.
- `routes/web.php`: Definições de rotas web.
- `database/migrations`: Arquivos de migração do banco de dados.

### Frontend (React com Inertia.js)

- `resources/js/Pages`: Páginas React renderizadas via Inertia.
- `resources/js/Components`: Componentes reutilizáveis.
- `resources/js/Layouts`: Layouts principais da aplicação.

## Boas Práticas

- Validar requisições no backend utilizando Form Requests.
- Utilizar políticas (`Policies`) para controle de acesso.
- Manter os controladores enxutos; delegar lógica para serviços ou modelos.
- Escrever testes automatizados para funcionalidades críticas:
    - **Testes Backend (Laravel)**:
        - Utilizar Pest PHP como framework de testes.
        - Organizar os testes em:
            - **Testes de Unidade**: Localizados em `tests/Unit`. Focam em testar pequenas partes isoladas do código, como métodos de uma classe.
            - **Testes de Funcionalidade (Feature Tests)**: Localizados em `tests/Feature`. Testam funcionalidades maiores da aplicação, simulando requisições HTTP e verificando as respostas.
        - Priorizar a cobertura de código para lógica de negócios e endpoints críticos.
        - Utilizar Mockery para criar mocks e stubs quando necessário.
    - **Testes Frontend (React)**:
        - **Testes de Componentes**:
            - Utilizar Vitest como test runner e React Testing Library para testar componentes React de forma isolada, focando no comportamento do usuário.
            - Organizar os testes de componentes próximos aos próprios componentes (e.g., em uma pasta `__tests__` dentro da pasta do componente ou com arquivos `*.test.jsx`/`*.test.tsx`).
        - **Testes End-to-End (E2E)**:
            - Utilizar Laravel Dusk para testar fluxos completos da aplicação, simulando interações reais do usuário no navegador.
            - Os testes Dusk geralmente ficam na pasta `tests/Browser`.
            - Focar em cobrir os caminhos críticos da aplicação.

## Observações

- Os importes e namespaces em php usam apenas `\` não `\\`.
- Evitar o uso de jQuery; preferir soluções nativas ou bibliotecas modernas.
- Manter dependências atualizadas regularmente.
- Documentar endpoints da API utilizando comentários no código ou ferramentas apropriadas.
