# Cards de Refatoração: Sistema de Autorização Baseado em Roles

## Visão Geral da Refatoração

**Objetivo**: Implementar um sistema de autorização simplificado baseado em roles (RBAC) para o Sistema de RH LaCInA-UFCG, substituindo verificações espalhadas por uma abordagem centralizada e escalável.

**Roles Definidos**:

- **Coordenador Master**: Acesso total ao sistema, sem necessidade de vínculos a projetos
- **Coordenador**: Pode gerenciar projetos que coordena e aprovar colaboradores
- **Colaborador**: Acesso básico, pode gerenciar próprio perfil e solicitar vínculos

---

## Card 1: ANÁLISE & DESIGN 🎯

### 📋 Descrição

Definir a arquitetura do sistema de autorização baseado em roles, mapeando permissões, fluxos de autorização e regras de negócio para substituir o sistema atual de verificações pontuais.

### 🎯 Objetivos

- [ ] Mapear todas as verificações de autorização existentes no sistema
- [ ] Definir hierarquia de roles e suas permissões específicas
- [ ] Projetar estratégia de migração sem quebrar funcionalidades existentes
- [ ] Estabelecer padrões para verificações futuras de autorização

### 📦 Entregáveis

- [ ] Documento de mapeamento de permissões por role
- [ ] Diagrama de hierarquia de roles e herança de permissões
- [ ] Especificação de middlewares e policies necessários
- [ ] Plano de migração gradual das verificações existentes
- [ ] Definição de convenções de nomenclatura para permissões

### 🔧 Implementação

#### Arquivos a Analisar:

- `app/Http/Middleware/VerificarCoordenadorMiddleware.php` - Middleware atual
- `app/Http/Middleware/ValidarTipoVinculoMiddleware.php` - Validações existentes
- `app/Http/Controllers/*Controller.php` - Verificações espalhadas
- `app/Policies/*` - Policies existentes

#### Mapeamento de Permissões:

```php
// Estrutura de permissões por role
COORDENADOR_MASTER: [
    '*' // Todas as permissões
]

COORDENADOR: [
    'projetos.create',
    'projetos.edit.own',
    'colaboradores.approve',
    'vinculos.manage.own',
    'relatorios.view.own'
]

COLABORADOR: [
    'perfil.edit.own',
    'projetos.view.own',
    'vinculos.apply',
    'relatorio.generate.own'
]
```

### ✅ Critérios de Aceitação

- [ ] Todas as verificações de autorização atuais foram catalogadas
- [ ] Hierarchy de roles definida com herança clara de permissões
- [ ] Estratégia de migração não quebra funcionalidades existentes
- [ ] Convenções de nomenclatura estabelecidas e documentadas
- [ ] Aprovação da arquitetura pela equipe de desenvolvimento

### 🚨 Pontos de Atenção

- Manter compatibilidade com sistema de vínculos existente
- Garantir que coordenadores master não precisem de vínculos de projeto
- Considerar performance das verificações de permissão

### 📊 Estimativa

**Complexidade**: Média
**Tempo estimado**: 8 horas

### 🔗 Dependências

- Depende de: Análise completa do sistema atual
- Bloqueia: Todos os outros cards desta refatoração

---

## Card 2: DATABASE & MIGRATIONS 🗄️

### 📋 Descrição

Criar as estruturas de dados necessárias para suportar o sistema de roles, incluindo flag para coordenadores master e índices para performance das consultas de autorização.

### 🎯 Objetivos

- [ ] Adicionar campo `is_coordenador_master` na tabela users
- [ ] Criar índices para otimizar consultas de autorização
- [ ] Implementar seeder para promover coordenadores master iniciais
- [ ] Garantir integridade referencial e constraints adequados

### 📦 Entregáveis

- [ ] Migration para adicionar campo `is_coordenador_master`
- [ ] Índices otimizados para consultas de autorização
- [ ] Seeder para coordenadores master iniciais
- [ ] Rollback plan para reversão da migration
- [ ] Testes de integridade dos dados

### 🔧 Implementação

#### Arquivos a Criar/Modificar:

- `database/migrations/2025_01_xx_add_coordenador_master_to_users.php` - Migration principal
- `database/seeders/CoordenadioresMasterSeeder.php` - Seeder inicial
- `database/seeders/DevelopmentSeeder.php` - Atualizar seeder de desenvolvimento

#### Código Chave:

```php
// Migration
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_coordenador_master')->default(false)->after('status_cadastro');
        $table->index(['is_coordenador_master']);
    });
}

// Seeder
class CoordenadioresMasterSeeder extends Seeder
{
    public function run(): void
    {
        $masterEmails = [
            'maxwell@computacao.ufcg.edu.br',
            'campelo@computacao.ufcg.edu.br',
        ];

        foreach ($masterEmails as $email) {
            User::where('email', $email)->update([
                'is_coordenador_master' => true
            ]);
        }
    }
}
```

### ✅ Critérios de Aceitação

- [ ] Campo `is_coordenador_master` adicionado corretamente
- [ ] Índices criados para otimizar consultas de autorização
- [ ] Seeder promove coordenadores master corretos
- [ ] Migration é reversível sem perda de dados
- [ ] Testes de integridade dos dados passando

### 🚨 Pontos de Atenção

- Backup dos dados antes da migration em produção
- Verificar performance das consultas após adicionar índices
- Garantir que rollback funciona corretamente

### 📊 Estimativa

**Complexidade**: Baixa
**Tempo estimado**: 3 horas

### 🔗 Dependências

- Depende de: Card 1 (Análise & Design)
- Bloqueia: Card 3 (Backend)

---

## Card 3: BACKEND (Models & Controllers) ⚙️

### 📋 Descrição

Implementar a lógica de negócio para o sistema de roles, incluindo enum Role, trait HasRole, middlewares e policies atualizados para suportar a nova estrutura de autorização.

### 🎯 Objetivos

- [ ] Criar enum Role com hierarquia e permissões
- [ ] Implementar trait HasRole para modelo User
- [ ] Criar middleware CheckRole para verificações de autorização
- [ ] Atualizar policies existentes para usar novo sistema
- [ ] Manter compatibilidade com verificações atuais

### 📦 Entregáveis

- [ ] Enum Role com métodos de permissões e hierarquia
- [ ] Trait HasRole com métodos de verificação
- [ ] Middleware CheckRole e CheckPermission
- [ ] Policies atualizadas para contexto específico
- [ ] Métodos helper para verificações comuns

### 🔧 Implementação

#### Arquivos a Criar/Modificar:

- `app/Enums/Role.php` - Enum principal de roles
- `app/Traits/HasRole.php` - Trait para modelo User
- `app/Http/Middleware/CheckRole.php` - Middleware de verificação
- `app/Http/Middleware/CheckPermission.php` - Middleware de permissões
- `app/Models/User.php` - Adicionar trait HasRole

#### Código Chave:

```php
// app/Enums/Role.php
enum Role: string
{
    case COORDENADOR_MASTER = 'coordenador_master';
    case COORDENADOR = 'coordenador';
    case COLABORADOR = 'colaborador';

    public function getPermissions(): array
    {
        return match($this) {
            self::COORDENADOR_MASTER => ['*'],
            self::COORDENADOR => [
                'projetos.create',
                'projetos.edit.own',
                'colaboradores.approve',
                'vinculos.manage.own',
            ],
            self::COLABORADOR => [
                'perfil.edit.own',
                'projetos.view.own',
                'vinculos.apply',
                'relatorio.generate.own',
            ],
        };
    }

    public function getLevel(): int
    {
        return match($this) {
            self::COORDENADOR_MASTER => 100,
            self::COORDENADOR => 50,
            self::COLABORADOR => 10,
        };
    }
}

// app/Traits/HasRole.php
trait HasRole
{
    public function getRole(): Role
    {
        if ($this->is_coordenador_master) {
            return Role::COORDENADOR_MASTER;
        }

        $temVinculoCoordenador = $this->usuarioProjetos()
            ->where('status', StatusVinculoProjeto::APROVADO)
            ->where('tipo_vinculo', TipoVinculo::COORDENADOR)
            ->exists();

        if ($temVinculoCoordenador) {
            return Role::COORDENADOR;
        }

        return Role::COLABORADOR;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->getRole()->getPermissions();
        return in_array('*', $permissions) || in_array($permission, $permissions);
    }

    public function hasPermissionForProject(string $permission, ?Projeto $projeto = null): bool
    {
        $role = $this->getRole();

        if ($role === Role::COORDENADOR_MASTER) {
            return true;
        }

        if ($role === Role::COORDENADOR && $projeto) {
            return $this->isCoordenador($projeto);
        }

        return $this->hasPermission($permission);
    }
}
```

### ✅ Critérios de Aceitação

- [ ] Enum Role implementado com todos os métodos necessários
- [ ] Trait HasRole funciona corretamente com modelo User
- [ ] Middlewares de autorização funcionando
- [ ] Policies atualizadas e testadas
- [ ] Compatibilidade mantida com sistema atual

### 🚨 Pontos de Atenção

- Garantir que verificações existentes continuem funcionando
- Otimizar consultas para verificação de roles
- Manter logs de autorização para auditoria

### 📊 Estimativa

**Complexidade**: Alta
**Tempo estimado**: 12 horas

### 🔗 Dependências

- Depende de: Card 2 (Database & Migrations)
- Bloqueia: Card 4 (Frontend)

---

## Card 4: FRONTEND (Components & Pages) 🎨

### 📋 Descrição

Atualizar os componentes React para exibir e gerenciar os novos roles, incluindo badges de identificação, formulários de promoção e interfaces adequadas para cada nível de autorização.

### 🎯 Objetivos

- [ ] Criar componente UserRoleBadge para exibição de roles
- [ ] Atualizar páginas para mostrar permissões contextuais
- [ ] Implementar formulários para promoção de coordenadores master
- [ ] Adicionar indicadores visuais de hierarquia de roles
- [ ] Garantir UX intuitiva para diferentes níveis de acesso

### 📦 Entregáveis

- [ ] Componente UserRoleBadge responsivo com estilos daisyUI
- [ ] Página de administração para coordenadores master
- [ ] Formulários de promoção com validação
- [ ] Indicadores visuais de permissões em tempo real
- [ ] Documentação de uso dos componentes

### 🔧 Implementação

#### Arquivos a Criar/Modificar:

- `resources/js/Components/UserRoleBadge.tsx` - Badge de role
- `resources/js/Pages/Admin/Dashboard.tsx` - Dashboard admin
- `resources/js/Components/RolePromotionForm.tsx` - Formulário promoção
- `resources/js/types/index.d.ts` - Adicionar tipos de Role

#### Código Chave:

```tsx
// resources/js/Components/UserRoleBadge.tsx
interface UserRoleBadgeProps {
    user: {
        is_coordenador_master?: boolean;
        role?: string;
    };
}

export default function UserRoleBadge({ user }: UserRoleBadgeProps) {
    if (user.is_coordenador_master) {
        return (
            <span className="badge badge-error badge-sm">
                Coordenador Master
            </span>
        );
    }

    const roleConfig = {
        coordenador: { label: 'Coordenador', style: 'badge-warning' },
        colaborador: { label: 'Colaborador', style: 'badge-info' },
    };

    const config =
        roleConfig[user.role as keyof typeof roleConfig] ||
        roleConfig.colaborador;

    return (
        <span className={`badge badge-sm ${config.style}`}>{config.label}</span>
    );
}

// resources/js/Pages/Admin/Dashboard.tsx
export default function AdminDashboard() {
    return (
        <AuthenticatedLayout>
            <Head title="Administração" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            <h2 className="card-title">
                                Dashboard de Administração
                            </h2>
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <AdminStatsCard />
                                <RoleManagementPanel />
                                <SystemHealthCard />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
```

### ✅ Critérios de Aceitação

- [ ] UserRoleBadge exibe corretamente todos os tipos de role
- [ ] Dashboard de administração funcional para coordenadores master
- [ ] Formulários de promoção validam dados corretamente
- [ ] Interface responsiva em dispositivos móveis
- [ ] Indicadores visuais claros para diferentes níveis de acesso

### 🚨 Pontos de Atenção

- Garantir acessibilidade nos componentes de role
- Validar permissões no frontend e backend
- Manter consistência visual com design system existente

### 📊 Estimativa

**Complexidade**: Média
**Tempo estimado**: 10 horas

### 🔗 Dependências

- Depende de: Card 3 (Backend)
- Bloqueia: Card 5 (Testes)

---

## Card 5: TESTES & QUALIDADE 🧪

### 📋 Descrição

Implementar cobertura completa de testes para o sistema de roles, incluindo testes unitários para lógica de permissões, testes de integração para fluxos de autorização e testes de edge cases.

### 🎯 Objetivos

- [ ] Criar testes unitários para enum Role e trait HasRole
- [ ] Implementar testes de feature para middlewares e policies
- [ ] Adicionar testes de autorização por contexto de projeto
- [ ] Criar testes de edge cases e cenários complexos
- [ ] Garantir cobertura mínima de 90% para código de autorização

### 📦 Entregáveis

- [ ] Testes unitários para Role enum e métodos de permissão
- [ ] Testes de feature para fluxos de autorização completos
- [ ] Testes de middlewares e policies
- [ ] Testes de edge cases e cenários extremos
- [ ] Relatório de cobertura de testes

### 🔧 Implementação

#### Arquivos a Criar/Modificar:

- `tests/Unit/RoleTest.php` - Testes unitários para enum Role
- `tests/Unit/HasRoleTraitTest.php` - Testes para trait HasRole
- `tests/Feature/AuthorizationRefactoredTest.php` - Testes de autorização
- `tests/Feature/RoleMiddlewareTest.php` - Testes de middleware

#### Código Chave:

```php
// tests/Unit/RoleTest.php
test('coordenador master tem todas as permissões', function () {
    $permissions = Role::COORDENADOR_MASTER->getPermissions();

    expect($permissions)->toContain('*');
});

test('hierarquia de roles funciona corretamente', function () {
    expect(Role::COORDENADOR_MASTER->getLevel())
        ->toBeGreaterThan(Role::COORDENADOR->getLevel());

    expect(Role::COORDENADOR->getLevel())
        ->toBeGreaterThan(Role::COLABORADOR->getLevel());
});

// tests/Feature/AuthorizationRefactoredTest.php
test('coordenador master pode acessar qualquer funcionalidade', function () {
    $user = User::factory()->create(['is_coordenador_master' => true]);

    expect($user->hasPermission('projetos.create'))->toBeTrue();
    expect($user->hasPermission('colaboradores.approve'))->toBeTrue();
    expect($user->hasPermission('qualquer.permissao'))->toBeTrue();
});

test('coordenador só pode gerenciar projetos que coordena', function () {
    $coordenador = User::factory()->create();
    $projeto1 = Projeto::factory()->create();
    $projeto2 = Projeto::factory()->create();

    // Coordenador do projeto1
    UsuarioProjeto::factory()->create([
        'usuario_id' => $coordenador->id,
        'projeto_id' => $projeto1->id,
        'tipo_vinculo' => TipoVinculo::COORDENADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);

    expect($coordenador->hasPermissionForProject('projetos.edit.own', $projeto1))
        ->toBeTrue();
    expect($coordenador->hasPermissionForProject('projetos.edit.own', $projeto2))
        ->toBeFalse();
});
```

### ✅ Critérios de Aceitação

- [ ] Todos os métodos de Role enum têm testes unitários
- [ ] Trait HasRole testado com diferentes cenários de usuário
- [ ] Middlewares de autorização testados com sucesso e falha
- [ ] Testes de integração cobrem fluxos completos de autorização
- [ ] Cobertura de testes >= 90% para código de autorização

### 🚨 Pontos de Atenção

- Testar edge cases como usuários sem vínculos
- Validar performance dos testes de autorização
- Garantir que testes não dependem de dados específicos

### 📊 Estimativa

**Complexidade**: Alta
**Tempo estimado**: 14 horas

### 🔗 Dependências

- Depende de: Card 4 (Frontend)
- Bloqueia: Card 6 (Documentação & Deploy)

---

## Card 6: DOCUMENTAÇÃO & DEPLOY 📚

### 📋 Descrição

Documentar o novo sistema de autorização, criar guias de uso, estabelecer checklist de segurança e preparar estratégia de deploy para produção com rollback plan.

### 🎯 Objetivos

- [ ] Documentar arquitetura e uso do sistema de roles
- [ ] Criar guia de migração das verificações antigas
- [ ] Estabelecer checklist de segurança para autorização
- [ ] Preparar estratégia de deploy gradual
- [ ] Criar guia de troubleshooting para problemas de autorização

### 📦 Entregáveis

- [ ] Documentação técnica completa do sistema de roles
- [ ] Guia de migração para desenvolvedores
- [ ] Checklist de segurança e auditoria
- [ ] Plano de deploy com rollback strategy
- [ ] Guia de troubleshooting e debugging

### 🔧 Implementação

#### Arquivos a Criar/Modificar:

- `docs/authorization-system.md` - Documentação principal
- `docs/migration-guide.md` - Guia de migração
- `docs/security-checklist.md` - Checklist de segurança
- `docs/troubleshooting-auth.md` - Guia de troubleshooting

#### Estrutura da Documentação:

````markdown
# Sistema de Autorização - LaCInA UFCG

## Visão Geral

O sistema de autorização baseado em roles simplifica e centraliza o controle de acesso...

## Roles Disponíveis

### Coordenador Master

- Acesso total ao sistema
- Não precisa de vínculos a projetos
- Pode promover outros coordenadores

### Coordenador

- Pode gerenciar projetos que coordena
- Pode aprovar colaboradores
- Limitado aos próprios projetos

### Colaborador

- Acesso básico ao sistema
- Pode gerenciar próprio perfil
- Pode solicitar vínculos

## Como Usar

### Verificar Permissões

```php
// Verificação simples
if ($user->hasPermission('projetos.create')) {
    // Usuário pode criar projetos
}

// Verificação contextual
if ($user->hasPermissionForProject('projetos.edit.own', $projeto)) {
    // Usuário pode editar este projeto específico
}
```
````

### Middlewares

```php
// Verificar role mínimo
Route::middleware(['role:coordenador'])->group(function () {
    // Rotas que exigem coordenador ou superior
});

// Verificar permissão específica
Route::middleware(['permission:projetos.create'])->group(function () {
    // Rotas que exigem permissão específica
});
```

## Migração

### Substituir Verificações Antigas

```php
// Antes
if (!auth()->user()->isCoordenador()) {
    abort(403);
}

// Depois
if (!auth()->user()->hasPermission('projetos.create')) {
    abort(403);
}
```

```

### ✅ Critérios de Aceitação

- [ ] Documentação técnica completa e atualizada
- [ ] Guia de migração testado com exemplos reais
- [ ] Checklist de segurança validado pela equipe
- [ ] Plano de deploy aprovado e testado em staging
- [ ] Guia de troubleshooting com cenários comuns

### 🚨 Pontos de Atenção

- Validar documentação com equipe de desenvolvimento
- Testar plano de rollback em ambiente de staging
- Manter documentação sincronizada com mudanças futuras

### 📊 Estimativa

**Complexidade**: Média
**Tempo estimado**: 8 horas

### 🔗 Dependências

- Depende de: Card 5 (Testes & Qualidade)
- Bloqueia: Nenhum (final do projeto)

---

## Resumo da Refatoração

### Benefícios Esperados
- **Simplicidade**: Sistema de 3 roles vs verificações espalhadas
- **Escalabilidade**: Fácil adição de novas permissões
- **Manutenibilidade**: Código centralizado e bem testado
- **Flexibilidade**: Coordenadores master sem vínculos de projeto
- **Auditoria**: Logs centralizados de autorização

### Impacto Estimado
- **Tempo Total**: ~55 horas
- **Arquivos Modificados**: ~25 arquivos
- **Cobertura de Testes**: 90%+ para código de autorização
- **Breaking Changes**: Nenhum (compatibilidade mantida)

### Próximos Passos
1. Aprovação da arquitetura proposta
2. Implementação sequencial dos cards
3. Testes em ambiente de staging
4. Deploy gradual em produção
5. Monitoramento e ajustes pós-deploy
```
