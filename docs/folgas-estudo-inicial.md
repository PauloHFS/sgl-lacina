---

### **Card 1: ANÁLISE & DESIGN** 🎯

#### 📋 Descrição

Análise e desenho da arquitetura para a funcionalidade de calendário do laboratório e gestão de folgas. Esta fase define as regras de negócio, fluxos de usuário, permissões e o modelo de dados necessário para suportar eventos globais (feriados, folgas coletivas) e solicitações de folga individuais com detalhamento de compensação de horas.

#### 🎯 Objetivos

-   [ ] Detalhar as user stories e critérios de aceitação.
-   [ ] Definir as regras de negócio para criação e aprovação de folgas e eventos.
-   [ ] Mapear o fluxo de dados e os estados da aplicação (ex: folga pendente, aprovada, rejeitada).
-   [ ] Especificar as permissões: quem pode criar eventos globais (coordenador) vs. solicitar folgas (colaborador).
-   [ ] Desenhar wireframes/mockups de baixo nível para a visualização do calendário e os formulários de solicitação/criação.

#### 📦 Entregáveis

-   [ ] Documento de regras de negócio.
-   [ ] Diagrama de fluxo de usuário para solicitação e aprovação de folgas.
-   [ ] Matriz de permissões (Role-Based Access Control).
-   [ ] Wireframes para:
    -   Página do Calendário (visualização mensal/semanal).
    -   Modal de Solicitação de Folga Individual (com campo de compensação).
    -   Modal de Criação de Evento Global (para coordenadores).

#### ✅ Critérios de Aceitação

-   [ ] Coordenadores podem criar, editar e remover eventos que afetam todo o laboratório (Feriado, Folga Coletiva, Laboratório Fechado).
-   [ ] Colaboradores podem solicitar folgas individuais, especificando o período, motivo e um plano de como as horas serão compensadas.
-   [ ] Coordenadores de projeto podem visualizar as solicitações de folga dos membros de sua equipe, aprovando ou rejeitando-as.
-   [ ] Todos os usuários autenticados podem visualizar o calendário consolidado com todos os tipos de eventos e folgas.
-   [ ] Notificações por e-mail são enviadas para coordenadores (nova solicitação) e colaboradores (status da solicitação alterado).

#### 🚨 Pontos de Atenção

-   Diferenciar claramente `Folgas` (individuais, com fluxo de aprovação) de `Eventos` (globais, sem fluxo de aprovação).
-   A "compensação de horas" pode ser um campo de texto descritivo inicialmente, evitando complexidade de um sistema de banco de horas.
-   O calendário deve ser performático ao carregar dados de múltiplas fontes (eventos, folgas individuais, folgas coletivas).

#### 📊 Estimativa

-   **Complexidade**: Média
-   **Tempo estimado**: 8 horas

#### 🔗 Dependências

-   N/A (Card inicial)

---

### **Card 2: DATABASE & MIGRATIONS** 🗄️

#### 📋 Descrição

Criação e modificação da estrutura do banco de dados para suportar o calendário e o sistema de folgas aprimorado. Isso envolve uma nova tabela para eventos do calendário e a adição de campos na tabela de folgas existente.

#### 🎯 Objetivos

- [ ] Criar uma nova migration para a tabela `eventos_calendario`.
- [ ] Modificar a migration da tabela `folgas` para incluir o campo de compensação.
- [ ] Estabelecer relacionamentos e índices necessários para performance.
- [ ] Criar seeders para popular o calendário com dados de teste (feriados, etc.).

#### 📦 Entregáveis

- [ ] Arquivo de migration para a tabela `eventos_calendario`.
- [ ] Arquivo de migration para modificar a tabela `folgas`.
- [ ] Seeders para `EventoCalendarioSeeder`.
- [ ] Model Factories atualizadas.

#### 🔧 Implementação

##### Arquivos a Criar/Modificar:

- `database/migrations/YYYY_MM_DD_HHMMSS_create_eventos_calendario_table.php` - Nova tabela para eventos.
- `database/migrations/YYYY_MM_DD_HHMMSS_add_compensacao_to_folgas_table.php` - Adicionar novo campo à tabela `folgas`.
- `database/seeders/EventoCalendarioSeeder.php` - Seeder para dados iniciais.
- `database/factories/FolgasFactory.php` - Atualizar factory.
- `database/factories/EventoCalendarioFactory.php` - Nova factory.

##### Código Chave:

```php
Schema::create('eventos_calendario', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('titulo');
    $table->text('descricao')->nullable();
    $table->date('data_inicio');
    $table->date('data_fim');
    $table->string('tipo'); // Ex: 'FERIADO', 'COLETIVA', 'LAB_FECHADO'
    $table->foreignUuid('criado_por_id')->nullable()->constrained('users');
    $table->timestamps();
});

// filepath: database/migrations/YYYY_MM_DD_HHMMSS_add_compensacao_to_folgas_table.php
Schema::table('folgas', function (Blueprint $table) {
    $table->text('compensacao_detalhes')->nullable()->after('justificativa');
});
```

#### ✅ Critérios de Aceitação

- [ ] A migration para `eventos_calendario` é executada sem erros.
- [ ] A migration para `folgas` adiciona o novo campo sem perda de dados.
- [ ] Os seeders populam a tabela `eventos_calendario` corretamente.
- [ ] Índices são aplicados nas colunas `data_inicio`, `data_fim` e `tipo` da nova tabela.

#### 🚨 Pontos de Atenção

- Garantir que os tipos de eventos (`tipo`) sejam padronizados, possivelmente usando um Enum (`TipoEventoCalendario`).
- A coluna `criado_por_id` em `eventos_calendario` deve ser nullable para eventos importados (ex: feriados nacionais).

#### 📊 Estimativa

- **Complexidade**: Baixa
- **Tempo estimado**: 4 horas

#### 🔗 Dependências

- Card 1: ANÁLISE & DESIGN

---

### **Card 3: BACKEND (Models & Controllers)** ⚙️

#### 📋 Descrição

Implementação da lógica de negócio no backend. Isso inclui a criação de models, controllers, form requests e policies para gerenciar eventos do calendário e o fluxo de solicitação/aprovação de folgas.

#### 🎯 Objetivos

- [ ] Criar o Model `EventoCalendario` e atualizar o Model `Folgas`.
- [ ] Implementar `CalendarioController` para CRUD de eventos globais.
- [ ] Atualizar `FolgasController` para lidar com a solicitação e aprovação, incluindo o campo de compensação.
- [ ] Criar Form Requests para validação de dados.
- [ ] Implementar Policies para garantir que apenas usuários autorizados possam executar ações.
- [ ] Desenvolver Jobs e Mails para notificações.

#### 📦 Entregáveis

- [ ] `app/Models/EventoCalendario.php`
- [ ] `app/Http/Controllers/CalendarioController.php`
- [ ] `app/Http/Controllers/FolgasController.php` (modificado)
- [ ] `app/Http/Requests/StoreEventoCalendarioRequest.php`
- [ ] `app/Http/Requests/StoreFolgaRequest.php` (modificado)
- [ ] `app/Policies/EventoCalendarioPolicy.php`
- [ ] `app/Policies/FolgaPolicy.php` (modificado)
- [ ] `app/Mail/NotificaAprovacaoFolga.php` e `app/Mail/NotificaSolicitacaoFolga.php`

#### 🔧 Implementação

##### Arquivos a Criar/Modificar:

- `app/Models/EventoCalendario.php` - Novo model.
- `app/Models/Folgas.php` - Adicionar `compensacao_detalhes` ao `$fillable`.
- `app/Http/Controllers/CalendarioController.php` - Controller para gerenciar eventos.
- `app/Http/Controllers/FolgasController.php` - Atualizar métodos `store` e `update`.
- `app/Policies/EventoCalendarioPolicy.php` - Definir permissões (ex: `create` apenas para coordenador).
- web.php - Adicionar rotas para `calendario` e `folgas`.

##### Código Chave:

```php
class CalendarioController extends Controller
{
    public function index()
    {
        // Lógica para buscar eventos e folgas aprovadas
        $eventos = EventoCalendario::all();
        $folgas = Folgas::where('status', StatusFolga::APROVADO)->get();

        return inertia('Calendario/Index', [
            'eventos' => $eventos,
            'folgas' => $folgas,
        ]);
    }

    public function store(StoreEventoCalendarioRequest $request)
    {
        $this->authorize('create', EventoCalendario::class);
        // Lógica para criar evento
    }
}

// filepath: app/Policies/EventoCalendarioPolicy.php
class EventoCalendarioPolicy
{
    public function create(User $user): bool
    {
        // Apenas coordenadores podem criar eventos globais
        return $user->isCoordenador();
    }
}
```

#### ✅ Critérios de Aceitação

- [ ] Endpoints do `CalendarioController` estão protegidos por policy.
- [ ] A solicitação de folga salva corretamente os detalhes de compensação.
- [ ] A aprovação/rejeição de folgas dispara as notificações por e-mail corretas.
- [ ] A validação via Form Requests impede a inserção de dados inválidos.

#### 🚨 Pontos de Atenção

- A query no `CalendarioController@index` deve ser otimizada para buscar apenas os dados necessários para o período de visualização do calendário.
- Usar transações de banco de dados onde for apropriado, especialmente em lógicas de aprovação complexas.

#### 📊 Estimativa

- **Complexidade**: Média
- **Tempo estimado**: 12 horas

#### 🔗 Dependências

- Card 2: DATABASE & MIGRATIONS

---

### **Card 4: FRONTEND (Components & Pages)** 🎨

#### 📋 Descrição

Desenvolvimento da interface do usuário para o calendário e gestão de folgas, utilizando React, Inertia.js e daisyUI. O foco é criar uma experiência de usuário clara e responsiva.

#### 🎯 Objetivos

- [ ] Criar a página principal do calendário que renderiza eventos e folgas.
- [ ] Desenvolver um componente de modal para solicitar folga individual.
- [ ] Desenvolver um componente de modal para coordenadores criarem eventos globais.
- [ ] Implementar a visualização de detalhes de eventos e folgas ao clicar no calendário.
- [ ] Garantir que todos os componentes sejam responsivos e sigam a identidade visual do daisyUI.

#### 📦 Entregáveis

- [ ] `resources/js/Pages/Calendario/Index.tsx`
- [ ] `resources/js/Components/Calendario/SolicitarFolgaModal.tsx`
- [ ] `resources/js/Components/Calendario/CriarEventoModal.tsx`
- [ ] `resources/js/Components/Calendario/CalendarioView.tsx` (componente principal do calendário)
- [ ] Tipos TypeScript para os novos dados (`EventoCalendario`, `Folga`).

#### 🔧 Implementação

##### Arquivos a Criar/Modificar:

- `resources/js/Pages/Calendario/Index.tsx` - Página que orquestra os componentes do calendário.
- `resources/js/Components/Calendario/CalendarioView.tsx` - Componente que pode usar uma biblioteca como `FullCalendar` ou ser uma implementação customizada com grid do Tailwind.
- `resources/js/Components/Calendario/SolicitarFolgaModal.tsx` - Formulário com `useForm` do Inertia para solicitação.

##### Código Chave:

```tsx
import { useForm } from '@inertiajs/react';
import React from 'react';

export default function SolicitarFolgaModal({ closeModal }) {
    const { data, setData, post, processing, errors } = useForm({
        data_inicio: '',
        data_fim: '',
        justificativa: '',
        compensacao_detalhes: '', // Novo campo
    });

    function submit(e) {
        e.preventDefault();
        post(route('folgas.store'), { onSuccess: () => closeModal() });
    }

    return (
        // JSX com form, inputs, textarea para justificativa e compensacao_detalhes
        // e botões de submit e cancel. Usar componentes daisyUI (input, textarea, btn, modal).
    );
}
```

#### ✅ Critérios de Aceitação

- [ ] O calendário exibe corretamente feriados, folgas coletivas e folgas individuais aprovadas.
- [ ] O formulário de solicitação de folga inclui o campo "Plano de Compensação" e o envia para o backend.
- [ ] Coordenadores veem um botão para "Adicionar Evento", que abre o modal correspondente.
- [ ] A interface trata adequadamente os estados de loading e exibe erros de validação retornados pelo servidor.

#### 🚨 Pontos de Atenção

- Avaliar uma biblioteca de calendário robusta para React (como FullCalendar) para economizar tempo de desenvolvimento e garantir acessibilidade.
- A diferenciação visual entre os tipos de evento no calendário é crucial (ex: cores diferentes para feriado, folga, etc.).

#### 📊 Estimativa

- **Complexidade**: Alta
- **Tempo estimado**: 16 horas

#### 🔗 Dependências

- Card 3: BACKEND (Models & Controllers)

---

### **Card 5: TESTES & QUALIDADE** 🧪

#### 📋 Descrição

Garantir a qualidade e a robustez da nova funcionalidade através da escrita de testes automatizados para o backend e, se necessário, para o frontend.

#### 🎯 Objetivos

- [ ] Escrever testes de feature (Pest) para o fluxo completo de criação de eventos e solicitação/aprovação de folgas.
- [ ] Testar as policies de autorização para garantir que as permissões estão sendo aplicadas corretamente.
- [ ] Validar os edge cases, como datas sobrepostas ou dados inválidos.
- [ ] Escrever testes unitários para qualquer lógica de negócio complexa que possa ser isolada em Services ou Actions.

#### 📦 Entregáveis

- [ ] `tests/Feature/CalendarioTest.php`
- [ ] FolgasTest.php (modificado)

#### 🔧 Implementação

##### Arquivos a Criar/Modificar:

- `tests/Feature/CalendarioTest.php` - Testes para CRUD de eventos do calendário.
- FolgasTest.php - Adicionar testes para o fluxo com compensação e aprovação.

##### Código Chave:

```php
use App\Models\User;
use App\Enums\TipoVinculo;

test('coordenador pode criar um evento de folga coletiva no calendário', function () {
    $coordenador = User::factory()->coordenador()->create();

    $this->actingAs($coordenador)
        ->post(route('calendario.store'), [
            'titulo' => 'Recesso de Fim de Ano',
            'data_inicio' => '2025-12-24',
            'data_fim' => '2026-01-02',
            'tipo' => 'COLETIVA',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('eventos_calendario', ['titulo' => 'Recesso de Fim de Ano']);
});

test('colaborador não pode criar um evento no calendário', function () {
    $colaborador = User::factory()->create();

    $this->actingAs($colaborador)
        ->post(route('calendario.store'), [/*...dados...*/])
        ->assertStatus(403);
});

// filepath: tests/Feature/FolgasTest.php
test('colaborador pode solicitar folga com detalhes de compensação', function () {
    $colaborador = User::factory()->create();

    $this->actingAs($colaborador)
        ->post(route('folgas.store'), [
            'data_inicio' => now()->addMonth(),
            'data_fim' => now()->addMonth()->addDay(),
            'justificativa' => 'Consulta médica',
            'compensacao_detalhes' => 'Trabalharei duas horas a mais na semana seguinte.',
        ]);

    $this->assertDatabaseHas('folgas', [
        'justificativa' => 'Consulta médica',
        'compensacao_detalhes' => 'Trabalharei duas horas a mais na semana seguinte.',
    ]);
});
```

#### ✅ Critérios de Aceitação

- [ ] Testes cobrem o "happy path" para todos os fluxos.
- [ ] Testes de autorização falham para usuários sem permissão e passam para usuários com permissão.
- [ ] Testes de validação verificam todos os campos obrigatórios e formatos de dados.
- [ ] A suíte de testes passa completamente.

#### 🚨 Pontos de Atenção

- Lembrar de usar `Mail::fake()` para testar o envio de notificações sem enviar e-mails reais.
- Usar factories para criar o estado inicial necessário para cada teste de forma limpa e isolada.

#### 📊 Estimativa

- **Complexidade**: Média
- **Tempo estimado**: 10 horas

#### 🔗 Dependências

- Card 3: BACKEND (Models & Controllers)
- Card 4: FRONTEND (Components & Pages)

---

### **Card 6: DOCUMENTAÇÃO & DEPLOY** 📚

#### 📋 Descrição

Documentação da nova funcionalidade para usuários finais e desenvolvedores, e preparação para o deploy em produção.

#### 🎯 Objetivos

- [ ] Criar um guia de usuário explicando como solicitar folgas e como coordenadores podem gerenciar o calendário.
- [ ] Documentar as novas rotas de API, models e fluxos no repositório do projeto.
- [ ] Verificar se as variáveis de ambiente necessárias estão documentadas.
- [ ] Criar um checklist de deploy para garantir uma implantação suave.

#### 📦 Entregáveis

- [ ] Seção na documentação do usuário (`docs/user-guide/calendario-folgas.md`).
- [ ] Atualização da documentação técnica (README.md ou `docs/tech/`).
- [ ] Itens no checklist de deploy.

#### ✅ Critérios de Aceitação

- [ ] A documentação do usuário é clara e cobre todos os casos de uso da feature.
- [ ] A documentação técnica está atualizada com os novos componentes do sistema.
- [ ] O processo de deploy é testado em um ambiente de staging.

#### 🚨 Pontos de Atenção

- A documentação deve usar uma linguagem acessível para o público-alvo (colaboradores e docentes do laboratório).
- Incluir screenshots da interface na documentação do usuário para facilitar o entendimento.

#### 📊 Estimativa

- **Complexidade**: Baixa
- **Tempo estimado**: 4 horas

#### 🔗 Dependências

- Card 5: TESTES & QUALIDADE
