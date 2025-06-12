# Cards de Implementação: Gestão de Horários, Salas e Baias

## Card 1: ANÁLISE & DESIGN 🎯

### 📋 Descrição

Definir arquitetura completa do sistema de gestão de infraestrutura física e horários, incluindo regras de negócio, fluxos de dados, permissões e interface de usuário para gerenciamento de salas, baias, horários e alertas de conflitos.

### 🎯 Objetivos

- [ ] Mapear todos os casos de uso e fluxos de interação
- [ ] Definir modelo de dados e relacionamentos entre entidades
- [ ] Estabelecer regras de autorização e visibilidade
- [ ] Projetar interface responsiva com daisyUI
- [ ] Definir sistema de notificações e alertas

### 📦 Entregáveis

- [ ] User stories detalhadas com critérios de aceitação
- [ ] Diagrama de entidades e relacionamentos (ERD)
- [ ] Fluxogramas de processos principais
- [ ] Wireframes das telas principais
- [ ] Matriz de permissões por tipo de usuário
- [ ] Especificação de API endpoints
- [ ] Regras de validação e business logic

### 🔧 Implementação

#### Estrutura de Dados:

**Salas:**
- id, nome, capacidade, descrição, ativa
- Relacionamento: hasMany(Baias)

**Baias:**
- id, sala_id, numero, disponivel, descricao
- Relacionamentos: belongsTo(Sala), belongsToMany(User), hasMany(Horarios)

**Horários:**
- id, usuario_id, usuario_projeto_id (nullable), dia_semana, hora_inicio, hora_fim, tipo (EM_AULA/REMOTO/NA_BAIA/AUSENTE), observacoes
- Relacionamentos: belongsTo(User), belongsTo(UsuarioProjeto, nullable), belongsToMany(Baias)
- **Regras de Vinculação:**
  - `EM_AULA`: Apenas usuario_id (sem projeto vinculado)
  - `REMOTO`: usuario_id + usuario_projeto_id obrigatório  
  - `NA_BAIA`: usuario_id + usuario_projeto_id obrigatório + baias obrigatório
  - `AUSENTE`: Apenas usuario_id (pode ser pessoal ou relacionado a projeto)

**Alertas:**
- id, usuario_id, docente_id, tipo, mensagem, resolvido, created_at
- Relacionamentos: belongsTo(User), belongsTo(User as docente)

#### Regras de Negócio:

```typescript
// Tipos de horário - onde/como o colaborador está trabalhando
enum TipoHorario {
    EM_AULA = 'EM_AULA',        // Assistindo aulas da graduação/pós
    REMOTO = 'REMOTO',          // Trabalhando remotamente no projeto
    NA_BAIA = 'NA_BAIA',        // Trabalhando presencialmente na baia
    AUSENTE = 'AUSENTE'         // Não disponível (folga, compromisso, etc.)
}

// Status de alerta
enum StatusAlerta {
    PENDENTE = 'PENDENTE',
    RESOLVIDO = 'RESOLVIDO',
    IGNORADO = 'IGNORADO'
}
```

### ✅ Critérios de Aceitação

- [ ] Docentes podem gerenciar salas/baias apenas de seus projetos
- [ ] Discentes registram horários com tipo específico (Em Aula, Remoto, Na Baia, Ausente)
- [ ] Sistema detecta conflitos automaticamente
- [ ] Apenas horários 'NA_BAIA' podem ser vinculados a baias físicas
- [ ] Dashboard mostra ocupação física vs remota do laboratório
- [ ] Alertas são enviados em tempo real
- [ ] Interface é responsiva em mobile/desktop
- [ ] Integração com sistema de projetos existente

### 🚨 Pontos de Atenção

- Verificar permissões baseadas em vínculos de projeto
- Considerar fuso horário e horário de verão
- Otimizar queries para grandes volumes de horários
- Implementar debounce em buscas em tempo real

### 📊 Estimativa

**Complexidade**: Alta
**Tempo estimado**: 8 horas

### 🔗 Dependências

- Depende de: Sistema de usuários e projetos existente
- Bloqueia: Todos os cards seguintes

---

## Card 2: DATABASE & MIGRATIONS 🗄️

### 📋 Descrição

Implementar estrutura de banco de dados completa para salas, baias, horários e alertas, incluindo migrações, seeders, índices de performance e constraints de integridade.

### 🎯 Objetivos

- [ ] Criar migrações para todas as tabelas necessárias
- [ ] Implementar relacionamentos com integridade referencial
- [ ] Adicionar índices para otimização de consultas
- [ ] Criar seeders para dados de desenvolvimento
- [ ] Definir constraints e validações de banco

### 📦 Entregáveis

- [ ] Migration para tabela `salas`
- [ ] Migration para tabela `baias`
- [ ] Migration para tabela `horarios` (vinculada a usuario_projeto)
- [ ] Migration para tabela `horario_baia` (pivot)
- [ ] Migration para tabela `alertas_horario`
- [ ] Seeders com dados de exemplo
- [ ] Índices de performance otimizados
- [ ] Views/queries para relatórios por projeto

### 🔧 Implementação

#### Arquivos a Criar:

- `database/migrations/create_salas_table.php` - Estrutura de salas
- `database/migrations/create_baias_table.php` - Estrutura de baias
- `database/migrations/create_horarios_table.php` - Estrutura de horários
- `database/migrations/create_horario_baia_table.php` - Tabela pivot
- `database/migrations/create_alertas_horario_table.php` - Sistema de alertas
- `database/seeders/EspacosSeeder.php` - Dados de exemplo

#### Código Chave:

```php
// Migration: create_salas_table.php
Schema::create('salas', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('nome')->unique();
    $table->integer('capacidade')->default(1);
    $table->text('descricao')->nullable();
    $table->boolean('ativa')->default(true);
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['ativa', 'created_at']);
});

// Migration: create_horarios_table.php
Schema::create('horarios', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('usuario_id')->constrained('users')->onDelete('cascade');
    $table->foreignUuid('usuario_projeto_id')->nullable()->constrained('usuario_projeto')->onDelete('cascade');
    $table->tinyInteger('dia_semana'); // 1-7 (Segunda-Domingo)
    $table->time('hora_inicio');
    $table->time('hora_fim');
    $table->enum('tipo', ['EM_AULA', 'REMOTO', 'NA_BAIA', 'AUSENTE']);
    $table->text('observacoes')->nullable();
    $table->timestamps();
    
    $table->index(['usuario_id', 'dia_semana', 'hora_inicio']);
    $table->index(['usuario_projeto_id', 'dia_semana', 'hora_inicio']);
    $table->index(['tipo', 'dia_semana']);
    
    // Constraint: horários EM_AULA não podem ter projeto vinculado
    $table->index(['tipo', 'usuario_projeto_id'], 'idx_tipo_projeto_validation');
});
    $table->timestamps();
    
    $table->index(['usuario_projeto_id', 'dia_semana', 'hora_inicio']);
    $table->index(['tipo', 'dia_semana']);
    $table->unique(['usuario_projeto_id', 'dia_semana', 'hora_inicio', 'hora_fim'], 'unique_horario_por_vinculo');
});
```

### ✅ Critérios de Aceitação

- [ ] Todas as migrações executam sem erro
- [ ] Relacionamentos funcionam corretamente
- [ ] Seeders populam dados consistentes
- [ ] Índices melhoram performance de consultas
- [ ] Constraints impedem dados inválidos
- [ ] Soft deletes preservam histórico

### 🚨 Pontos de Atenção

- Usar UUIDs para compatibilidade com tabelas existentes
- Validar sobreposição de horários no nível de banco
- Considerar particionamento para tabela de horários
- Implementar cascata apropriada para exclusões

### 📊 Estimativa

**Complexidade**: Média
**Tempo estimado**: 6 horas

### 🔗 Dependências

- Depende de: Card 1 (Análise & Design)
- Bloqueia: Card 3 (Backend)

---

## Card 3: BACKEND (Models & Controllers) ⚙️

### 📋 Descrição

Implementar Models Eloquent com relacionamentos, Controllers para APIs, FormRequests para validação, Services para lógica complexa e Jobs para processamento de alertas automáticos.

### 🎯 Objetivos

- [ ] Criar Models com relacionamentos e scopes
- [ ] Implementar Controllers com actions CRUD + específicas
- [ ] Desenvolver FormRequests para validação robusta
- [ ] Criar Services para lógica de negócio complexa
- [ ] Implementar Jobs para alertas automáticos
- [ ] Adicionar Middleware de autorização

### 📦 Entregáveis

- [ ] Models: Sala, Baia, Horario, AlertaHorario
- [ ] Controllers: SalasController, BaiasController, HorariosController
- [ ] FormRequests para validação de dados
- [ ] ConflictDetectionService para detecção de conflitos
- [ ] Jobs para processamento de alertas
- [ ] Policies para autorização granular

### 🔧 Implementação

#### Arquivos a Criar:

- `app/Models/Sala.php` - Model de sala
- `app/Models/Baia.php` - Model de baia
- `app/Models/Horario.php` - Model de horário
- `app/Models/AlertaHorario.php` - Model de alerta
- `app/Http/Controllers/SalasController.php` - Controller de salas
- `app/Http/Controllers/HorariosController.php` - Controller de horários
- `app/Services/ConflictDetectionService.php` - Detecção de conflitos
- `app/Jobs/ProcessarAlertasHorario.php` - Job de alertas

#### Código Chave:

```php
// app/Models/Horario.php
class Horario extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usuario_id', 'usuario_projeto_id', 'dia_semana', 'hora_inicio', 
        'hora_fim', 'tipo', 'observacoes'
    ];

    protected $casts = [
        'dia_semana' => WeekDay::class,
        'tipo' => TipoHorario::class,
        'hora_inicio' => 'datetime:H:i',
        'hora_fim' => 'datetime:H:i',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usuarioProjeto(): BelongsTo
    {
        return $this->belongsTo(UsuarioProjeto::class);
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class)->through('usuarioProjeto');
    }

    public function baias(): BelongsToMany
    {
        return $this->belongsToMany(Baia::class, 'horario_baia');
    }

    // Scopes para filtrar por tipo de trabalho
    public function scopePresencial($query)
    {
        return $query->where('tipo', TipoHorario::NA_BAIA);
    }

    public function scopeRemoto($query)
    {
        return $query->where('tipo', TipoHorario::REMOTO);
    }

    public function scopeDisponivel($query)
    {
        return $query->whereIn('tipo', [TipoHorario::NA_BAIA, TipoHorario::REMOTO]);
    }

    public function scopeIndisponivel($query)
    {
        return $query->whereIn('tipo', [TipoHorario::EM_AULA, TipoHorario::AUSENTE]);
    }

    public function scopeConflitoCom($query, Horario $horario)
    {
        return $query->where('usuario_id', $horario->usuario_id)
            ->where('dia_semana', $horario->dia_semana)
            ->where('id', '!=', $horario->id)
            ->where(function ($q) use ($horario) {
                $q->whereBetween('hora_inicio', [$horario->hora_inicio, $horario->hora_fim])
                  ->orWhereBetween('hora_fim', [$horario->hora_inicio, $horario->hora_fim])
                  ->orWhere(function ($q2) use ($horario) {
                      $q2->where('hora_inicio', '<=', $horario->hora_inicio)
                         ->where('hora_fim', '>=', $horario->hora_fim);
                  });
            });
    }

    public function scopePorProjeto($query, $projetoId)
    {
        return $query->whereHas('usuarioProjeto', function ($q) use ($projetoId) {
            $q->where('projeto_id', $projetoId);
        });
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeComProjeto($query)
    {
        return $query->whereNotNull('usuario_projeto_id');
    }

    public function scopeSemProjeto($query)
    {
        return $query->whereNull('usuario_projeto_id');
    }

    // Verifica se o horário requer vinculação com projeto
    public function requerVinculoProjeto(): bool
    {
        return in_array($this->tipo, [TipoHorario::REMOTO, TipoHorario::NA_BAIA]);
    }

    // Verifica se o horário é pessoal (não relacionado a projeto)
    public function ehHorarioPessoal(): bool
    {
        return in_array($this->tipo, [TipoHorario::EM_AULA, TipoHorario::AUSENTE]);
    }

    // Boot method para validações automáticas
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($horario) {
            // Regra 1: EM_AULA não pode ter projeto vinculado
            if ($horario->tipo === TipoHorario::EM_AULA && $horario->usuario_projeto_id) {
                throw new \InvalidArgumentException('Horários EM_AULA não podem ter projeto vinculado');
            }

            // Regra 2: REMOTO e NA_BAIA devem ter projeto vinculado
            if (in_array($horario->tipo, [TipoHorario::REMOTO, TipoHorario::NA_BAIA]) && !$horario->usuario_projeto_id) {
                throw new \InvalidArgumentException('Horários de trabalho devem ter projeto vinculado');
            }

            // Regra 3: Se não for NA_BAIA, remove vínculos com baias
            if ($horario->tipo !== TipoHorario::NA_BAIA) {
                $horario->baias()->detach();
            }
        });
    }
}

// app/Services/ConflictDetectionService.php
class ConflictDetectionService
{
    public function detectarConflitos(User $usuario, ?Projeto $projeto = null): Collection
    {
        $query = Horario::with(['usuarioProjeto.usuario', 'usuarioProjeto.projeto'])
            ->whereHas('usuarioProjeto', function ($q) use ($usuario) {
                $q->where('usuario_id', $usuario->id);
            });

        if ($projeto) {
            $query->porProjeto($projeto->id);
        }

        $horarios = $query->get();
        $conflitos = collect();

        foreach ($horarios as $horario) {
            $conflitantes = Horario::conflitoCom($horario)->get();

            if ($conflitantes->isNotEmpty()) {
                $conflitos->push([
                    'horario_principal' => $horario,
                    'conflitantes' => $conflitantes,
                    'projeto' => $horario->projeto,
                    'usuario' => $horario->usuario,
                    'tipo_conflito' => $this->determinarTipoConflito($horario, $conflitantes),
                ]);
            }
        }

        return $conflitos;
    }

    public function detectarConflitosGlobais(Projeto $projeto): Collection
    {
        $horarios = Horario::porProjeto($projeto->id)
            ->with(['usuarioProjeto.usuario'])
            ->get();

        $conflitos = collect();

        foreach ($horarios as $horario) {
            // Detecta conflitos de baia (duas pessoas na mesma baia ao mesmo tempo)
            if ($horario->tipo === TipoHorario::NA_BAIA) {
                $conflitosPresenciais = $this->detectarConflitosPresenciais($horario);
                if ($conflitosPresenciais->isNotEmpty()) {
                    $conflitos = $conflitos->merge($conflitosPresenciais);
                }
            }
        }

        return $conflitos;
    }

    private function detectarConflitosPresenciais(Horario $horario): Collection
    {
        $conflitos = collect();

        // Verifica conflitos de baia
        foreach ($horario->baias as $baia) {
            $horariosMesmaBaia = Horario::whereHas('baias', function ($q) use ($baia) {
                    $q->where('baia_id', $baia->id);
                })
                ->where('id', '!=', $horario->id)
                ->where('dia_semana', $horario->dia_semana)
                ->where('tipo', TipoHorario::NA_BAIA)
                ->where(function ($q) use ($horario) {
                    $q->whereBetween('hora_inicio', [$horario->hora_inicio, $horario->hora_fim])
                      ->orWhereBetween('hora_fim', [$horario->hora_inicio, $horario->hora_fim]);
                })
                ->get();

            if ($horariosMesmaBaia->isNotEmpty()) {
                $conflitos->push([
                    'tipo' => 'conflito_baia',
                    'baia' => $baia,
                    'horario_principal' => $horario,
                    'conflitantes' => $horariosMesmaBaia,
                    'mensagem' => "Conflito de ocupação da baia {$baia->numero}",
                ]);
            }
        }

        return $conflitos;
    }

    private function determinarTipoConflito(Horario $principal, Collection $conflitantes): string
    {
        $tiposPrincipais = [$principal->tipo->value];
        $tiposConflitantes = $conflitantes->pluck('tipo')->map(fn($t) => $t->value)->toArray();

        if (in_array('EM_AULA', $tiposPrincipais) || in_array('EM_AULA', $tiposConflitantes)) {
            return 'conflito_aula_trabalho';
        }

        if (in_array('NA_BAIA', $tiposPrincipais) && in_array('NA_BAIA', $tiposConflitantes)) {
            return 'conflito_presencial';
        }

        if (in_array('AUSENTE', $tiposPrincipais) || in_array('AUSENTE', $tiposConflitantes)) {
            return 'conflito_disponibilidade';
        }

        return 'conflito_geral';
    }

    public function gerarRelatorioOcupacao(Projeto $projeto, array $periodo): array
    {
        $horarios = Horario::porProjeto($projeto->id)
            ->disponivel() // Apenas NA_BAIA e REMOTO
            ->get();

        $estatisticas = [
            'total_horas' => $horarios->sum(function ($h) {
                return $h->hora_fim->diffInHours($h->hora_inicio);
            }),
            'horas_presenciais' => $horarios->where('tipo', TipoHorario::NA_BAIA)->sum(function ($h) {
                return $h->hora_fim->diffInHours($h->hora_inicio);
            }),
            'horas_remotas' => $horarios->where('tipo', TipoHorario::REMOTO)->sum(function ($h) {
                return $h->hora_fim->diffInHours($h->hora_inicio);
            }),
            'taxa_presencial' => 0,
            'baias_utilizadas' => $horarios->where('tipo', TipoHorario::NA_BAIA)
                ->flatMap(fn($h) => $h->baias)
                ->unique('id')
                ->count(),
        ];

        if ($estatisticas['total_horas'] > 0) {
            $estatisticas['taxa_presencial'] = round(
                ($estatisticas['horas_presenciais'] / $estatisticas['total_horas']) * 100, 
                2
            );
        }

        return $estatisticas;
    }
}
```

### ✅ Critérios de Aceitação

- [ ] CRUD completo para salas, baias e horários
- [ ] Validação robusta em FormRequests
- [ ] Autorização baseada em vínculos de projeto
- [ ] Detecção automática de conflitos
- [ ] APIs retornam dados paginados
- [ ] Logs detalhados para auditoria

### 🚨 Pontos de Atenção

- Otimizar queries N+1 com eager loading
- Validar permissões em todos os endpoints
- Implementar rate limiting para APIs
- Cachear consultas frequentes

### 📊 Estimativa

**Complexidade**: Alta
**Tempo estimado**: 12 horas

### 🔗 Dependências

- Depende de: Card 2 (Database & Migrations)
- Bloqueia: Card 4 (Frontend)

---

## Card 4: FRONTEND (Components & Pages) 🎨

### 📋 Descrição

Desenvolver interface completa responsiva com React + Inertia.js + daisyUI para gestão de salas, baias, horários e visualização de conflitos, incluindo formulários dinâmicos e dashboards informativos.

### 🎯 Objetivos

- [ ] Criar páginas de gerenciamento de salas e baias
- [ ] Implementar interface de cadastro de horários
- [ ] Desenvolver dashboard de visualização de horários
- [ ] Criar sistema de alertas em tempo real
- [ ] Implementar componentes reutilizáveis
- [ ] Garantir responsividade mobile-first

### 📦 Entregáveis

- [ ] Páginas: Salas/Index, Salas/Form, Horarios/MeuHorario
- [ ] Componentes: HorarioGrid, ConflictAlert, BaiaCard
- [ ] Forms com validação em tempo real
- [ ] Dashboard de visualização de conflitos
- [ ] Sistema de notificações toast
- [ ] Modais para edição rápida

### 🔧 Implementação

#### Arquivos a Criar:

- `resources/js/Pages/Salas/Index.tsx` - Listagem de salas
- `resources/js/Pages/Salas/Form.tsx` - Formulário de sala
- `resources/js/Pages/Horarios/Dashboard.tsx` - Dashboard de horários
- `resources/js/Components/HorarioGrid.tsx` - Grade de horários
- `resources/js/Components/ConflictAlert.tsx` - Alertas de conflito
- `resources/js/Components/BaiaSelector.tsx` - Seletor de baias

#### Código Chave:

```tsx
// resources/js/Pages/Horarios/Form.tsx
interface HorarioFormProps {
    horario?: Horario;
    projetos: Projeto[];
    baias: Baia[];
}

export default function HorarioForm({ horario, projetos, baias }: HorarioFormProps) {
    const { data, setData, post, put, processing, errors } = useForm({
        usuario_projeto_id: horario?.usuario_projeto_id || '',
        dia_semana: horario?.dia_semana || 1,
        hora_inicio: horario?.hora_inicio || '08:00',
        hora_fim: horario?.hora_fim || '12:00',
        tipo: horario?.tipo || TipoHorario.REMOTO,
        observacoes: horario?.observacoes || '',
        baias: horario?.baias?.map(b => b.id) || [],
    });

    const tiposHorario = [
        { value: 'EM_AULA', label: '📚 Em Aula', color: 'text-info', descricao: 'Horários de disciplinas (sem projeto)' },
        { value: 'REMOTO', label: '🏠 Remoto', color: 'text-primary', descricao: 'Trabalho home office (projeto obrigatório)' },
        { value: 'NA_BAIA', label: '🏢 Na Baia', color: 'text-success', descricao: 'Trabalho presencial (projeto + baia obrigatórios)' },
        { value: 'AUSENTE', label: '❌ Ausente', color: 'text-neutral', descricao: 'Indisponível (projeto opcional)' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        
        if (horario) {
            put(route('horarios.update', horario.id));
        } else {
            post(route('horarios.store'));
        }
    };

    // Lógica para determinar se projeto é obrigatório
    const requerProjeto = data.tipo === 'REMOTO' || data.tipo === 'NA_BAIA';
    const permiteProjetoOpcional = data.tipo === 'AUSENTE';
    const proibeProjeto = data.tipo === 'EM_AULA';
    
    // Lógica para determinar se baia é necessária
    const podeVincularBaia = data.tipo === 'NA_BAIA';

    // Limpa projeto quando não é permitido
    useEffect(() => {
        if (proibeProjeto && data.usuario_projeto_id) {
            setData('usuario_projeto_id', '');
        }
    }, [data.tipo, proibeProjeto]);

    // Limpa baias quando não é permitido
    useEffect(() => {
        if (!podeVincularBaia && data.baias.length > 0) {
            setData('baias', []);
        }
    }, [data.tipo, podeVincularBaia]);

    return (
        <AuthenticatedLayout>
            <Head title={horario ? 'Editar Horário' : 'Novo Horário'} />
            
            <div className="container mx-auto p-6">
                <div className="card bg-base-100 shadow-xl">
                    <div className="card-body">
                        <h2 className="card-title">
                            {horario ? 'Editar Horário' : 'Cadastrar Novo Horário'}
                        </h2>

                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Seleção de Projeto - Condicional baseada no tipo */}
                            {!proibeProjeto && (
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">
                                            Projeto {requerProjeto && <span className="text-error">*</span>}
                                        </span>
                                        <span className="label-text-alt text-sm opacity-70">
                                            {requerProjeto && 'Obrigatório para trabalho'}
                                            {permiteProjetoOpcional && 'Opcional - apenas se relacionado ao projeto'}
                                        </span>
                                    </label>
                                    <select 
                                        className="select select-bordered"
                                        value={data.usuario_projeto_id}
                                        onChange={e => setData('usuario_projeto_id', e.target.value)}
                                        disabled={proibeProjeto}
                                        required={requerProjeto}
                                    >
                                        <option value="">
                                            {requerProjeto ? 'Selecione um projeto' : 'Nenhum projeto (pessoal)'}
                                        </option>
                                        {projetos.map(projeto => (
                                            <option key={projeto.id} value={projeto.vinculo_id}>
                                                {projeto.nome}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.usuario_projeto_id && (
                                        <label className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.usuario_projeto_id}
                                            </span>
                                        </label>
                                    )}
                                </div>
                            )}

                            {/* Aviso para horários EM_AULA */}
                            {proibeProjeto && (
                                <div className="alert alert-info">
                                    <svg className="stroke-current shrink-0 h-6 w-6">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" 
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>
                                        Horários <strong>Em Aula</strong> são pessoais e não são vinculados a projetos do laboratório.
                                    </span>
                                </div>
                            )}

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Dia da Semana */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">Dia da Semana</span>
                                    </label>
                                    <select 
                                        className="select select-bordered"
                                        value={data.dia_semana}
                                        onChange={e => setData('dia_semana', parseInt(e.target.value))}
                                    >
                                        <option value={1}>Segunda-feira</option>
                                        <option value={2}>Terça-feira</option>
                                        <option value={3}>Quarta-feira</option>
                                        <option value={4}>Quinta-feira</option>
                                        <option value={5}>Sexta-feira</option>
                                        <option value={6}>Sábado</option>
                                        <option value={7}>Domingo</option>
                                    </select>
                                </div>

                                {/* Tipo de Horário */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">Tipo de Horário</span>
                                    </label>
                                    <select 
                                        className="select select-bordered"
                                        value={data.tipo}
                                        onChange={e => setData('tipo', e.target.value)}
                                    >
                                        {tiposHorario.map(tipo => (
                                            <option key={tipo.value} value={tipo.value}>
                                                {tipo.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Horário Início */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">Horário de Início</span>
                                    </label>
                                    <input 
                                        type="time" 
                                        className="input input-bordered"
                                        value={data.hora_inicio}
                                        onChange={e => setData('hora_inicio', e.target.value)}
                                    />
                                </div>

                                {/* Horário Fim */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">Horário de Término</span>
                                    </label>
                                    <input 
                                        type="time" 
                                        className="input input-bordered"
                                        value={data.hora_fim}
                                        onChange={e => setData('hora_fim', e.target.value)}
                                    />
                                </div>
                            </div>

                            {/* Seleção de Baias - apenas para tipo NA_BAIA */}
                            {podeVincularBaia && (
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">
                                            Baias (obrigatório para trabalho presencial)
                                        </span>
                                    </label>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-2">
                                        {baias.map(baia => (
                                            <label key={baia.id} className="label cursor-pointer">
                                                <input 
                                                    type="checkbox"
                                                    className="checkbox checkbox-primary"
                                                    checked={data.baias.includes(baia.id)}
                                                    onChange={e => {
                                                        if (e.target.checked) {
                                                            setData('baias', [...data.baias, baia.id]);
                                                        } else {
                                                            setData('baias', data.baias.filter(id => id !== baia.id));
                                                        }
                                                    }}
                                                />
                                                <span className="label-text">
                                                    Baia {baia.numero} ({baia.sala.nome})
                                                </span>
                                            </label>
                                        ))}
                                    </div>
                                    {errors.baias && (
                                        <label className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.baias}
                                            </span>
                                        </label>
                                    )}
                                </div>
                            )}

                            {/* Observações */}
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">Observações</span>
                                </label>
                                <textarea 
                                    className="textarea textarea-bordered"
                                    placeholder="Informações adicionais sobre este horário..."
                                    value={data.observacoes}
                                    onChange={e => setData('observacoes', e.target.value)}
                                />
                            </div>

                            <div className="card-actions justify-end">
                                <button 
                                    type="button"
                                    className="btn btn-ghost"
                                    onClick={() => window.history.back()}
                                >
                                    Cancelar
                                </button>
                                <button 
                                    type="submit" 
                                    className="btn btn-primary"
                                    disabled={processing}
                                >
                                    {processing && <span className="loading loading-spinner loading-sm"></span>}
                                    {horario ? 'Atualizar' : 'Cadastrar'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
```

// resources/js/Pages/Horarios/Dashboard.tsx
interface HorariosDashboardProps {
    horarios: PaginatedData<Horario>;
    conflitos: ConflictoHorario[];
    salas: Sala[];
    filtros: HorarioFiltros;
    estatisticas: {
        total_colaboradores: number;
        presenciais_agora: number;
        remotos_agora: number;
        em_aula_agora: number;
        ausentes_agora: number;
    };
}

export default function HorariosDashboard({ 
    horarios, conflitos, salas, filtros, estatisticas 
}: HorariosDashboardProps) {
    const [selectedUser, setSelectedUser] = useState<User | null>(null);
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard de Horários" />
            
            <div className="container mx-auto p-6">
                {/* Estatísticas em tempo real */}
                <div className="stats shadow mb-6 w-full">
                    <div className="stat">
                        <div className="stat-figure text-success">
                            <svg className="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"/>
                            </svg>
                        </div>
                        <div className="stat-title">Presencial</div>
                        <div className="stat-value text-success">{estatisticas.presenciais_agora}</div>
                    </div>
                    
                    <div className="stat">
                        <div className="stat-figure text-primary">
                            <svg className="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                        </div>
                        <div className="stat-title">Remoto</div>
                        <div className="stat-value text-primary">{estatisticas.remotos_agora}</div>
                    </div>
                    
                    <div className="stat">
                        <div className="stat-figure text-info">
                            <svg className="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div className="stat-title">Em Aula</div>
                        <div className="stat-value text-info">{estatisticas.em_aula_agora}</div>
                    </div>
                    
                    <div className="stat">
                        <div className="stat-figure text-neutral">
                            <svg className="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                            </svg>
                        </div>
                        <div className="stat-title">Ausente</div>
                        <div className="stat-value text-neutral">{estatisticas.ausentes_agora}</div>
                    </div>
                </div>

                {/* Header com filtros */}
                <div className="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
                    <h1 className="text-3xl font-bold">Dashboard de Horários</h1>
                    
                    <div className="flex flex-wrap gap-2">
                        <select className="select select-bordered select-sm">
                            <option value="">Todos os tipos</option>
                            <option value="EM_AULA">📚 Em Aula</option>
                            <option value="REMOTO">🏠 Remoto</option>
                            <option value="NA_BAIA">🏢 Na Baia</option>
                            <option value="AUSENTE">❌ Ausente</option>
                        </select>
                        
                        <div className="btn-group">
                            <button 
                                className={`btn btn-sm ${viewMode === 'grid' ? 'btn-active' : ''}`}
                                onClick={() => setViewMode('grid')}
                            >
                                Grade
                            </button>
                            <button 
                                className={`btn btn-sm ${viewMode === 'list' ? 'btn-active' : ''}`}
                                onClick={() => setViewMode('list')}
                            >
                                Lista
                            </button>
                        </div>
                    </div>
                </div>

                {/* Alertas de conflito */}
                {conflitos.length > 0 && (
                    <div className="alert alert-warning mb-6">
                        <svg className="stroke-current shrink-0 h-6 w-6">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" 
                                  d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            {conflitos.length} conflito(s) de horário detectado(s)
                        </span>
                        <button className="btn btn-sm btn-outline">
                            Ver detalhes
                        </button>
                    </div>
                )}

                {/* Grid de horários */}
                {viewMode === 'grid' ? (
                    <HorarioGrid 
                        horarios={horarios.data}
                        onUserSelect={setSelectedUser}
                        selectedUser={selectedUser}
                    />
                ) : (
                    <HorarioList horarios={horarios.data} />
                )}
            </div>
        </AuthenticatedLayout>
    );
}
```

// resources/js/Components/HorarioGrid.tsx
interface HorarioGridProps {
    horarios: Horario[];
    onUserSelect: (user: User) => void;
    selectedUser: User | null;
}

const DIAS_SEMANA = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];
const HORARIOS = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

export const HorarioGrid = memo<HorarioGridProps>(({ 
    horarios, onUserSelect, selectedUser 
}) => {
    return (
        <div className="overflow-x-auto">
            <table className="table table-zebra w-full">
                <thead>
                    <tr>
                        <th className="w-20">Horário</th>
                        {DIAS_SEMANA.map(dia => (
                            <th key={dia} className="text-center">{dia}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {HORARIOS.map(hora => (
                        <tr key={hora}>
                            <td className="font-semibold">{hora}</td>
                            {DIAS_SEMANA.map((dia, diaIndex) => {
                                const horariosDoDia = horarios.filter(h => 
                                    h.dia_semana === diaIndex + 1 && 
                                    h.hora_inicio <= hora && 
                                    h.hora_fim > hora
                                );
                                
                                return (
                                    <td key={`${hora}-${dia}`} className="p-1">
                                        <div className="flex flex-col gap-1">
                                            {horariosDoDia.map(horario => (
                                                <div
                                                    key={horario.id}
                                                    className={`badge badge-sm cursor-pointer ${
                                                        horario.tipo === 'EM_AULA' 
                                                            ? 'badge-info' 
                                                            : horario.tipo === 'NA_BAIA'
                                                            ? 'badge-success'
                                                            : horario.tipo === 'REMOTO'
                                                            ? 'badge-primary'
                                                            : 'badge-neutral' // AUSENTE
                                                    }`}
                                                    onClick={() => onUserSelect(horario.usuario)}
                                                    title={`${horario.usuario.name} - ${horario.tipo} ${horario.baias?.length ? `(Baia ${horario.baias[0].numero})` : ''}`}
                                                >
                                                    {horario.usuario.name}
                                                    {horario.tipo === 'NA_BAIA' && (
                                                        <span className="ml-1 text-xs">🏢</span>
                                                    )}
                                                    {horario.tipo === 'REMOTO' && (
                                                        <span className="ml-1 text-xs">🏠</span>
                                                    )}
                                                    {horario.tipo === 'EM_AULA' && (
                                                        <span className="ml-1 text-xs">📚</span>
                                                    )}
                                                    {horario.tipo === 'AUSENTE' && (
                                                        <span className="ml-1 text-xs">❌</span>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    </td>
                                );
                            })}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
});
```

### ✅ Critérios de Aceitação

- [ ] Interface responsiva funciona em mobile/tablet/desktop
- [ ] Formulários validam em tempo real
- [ ] Dashboard mostra conflitos claramente
- [ ] Navegação intuitiva entre seções
- [ ] Loading states durante operações
- [ ] Mensagens de erro/sucesso apropriadas

### 🚨 Pontos de Atenção

- Otimizar re-renders com React.memo
- Implementar debounce em buscas
- Garantir acessibilidade (ARIA labels)
- Testar em diferentes tamanhos de tela

### 📊 Estimativa

**Complexidade**: Alta
**Tempo estimado**: 16 horas

### 🔗 Dependências

- Depende de: Card 3 (Backend)
- Bloqueia: Card 5 (Testes)

---

## Card 5: TESTES & QUALIDADE 🧪

### 📋 Descrição

Implementar cobertura completa de testes para backend (Pest PHP) e frontend (Vitest), incluindo testes unitários, de integração, autorização e edge cases para garantir qualidade e confiabilidade do sistema.

### 🎯 Objetivos

- [ ] Criar testes Pest para Models e relacionamentos
- [ ] Implementar testes de Controllers e APIs
- [ ] Desenvolver testes de autorização e permissões
- [ ] Criar testes Vitest para componentes React
- [ ] Implementar testes de edge cases
- [ ] Configurar coverage reports

### 📦 Entregáveis

- [ ] Testes unitários para Models (Sala, Baia, Horario)
- [ ] Testes de feature para Controllers
- [ ] Testes de autorização e policies
- [ ] Testes de validação e business rules
- [ ] Testes de componentes React críticos
- [ ] Testes de performance para queries
- [ ] Code coverage reports

### 🔧 Implementação

#### Arquivos a Criar:

- `tests/Unit/Models/SalaTest.php` - Testes do model Sala
- `tests/Unit/Models/HorarioTest.php` - Testes do model Horario
- `tests/Feature/SalasManagementTest.php` - Testes de gestão de salas
- `tests/Feature/HorariosManagementTest.php` - Testes de horários
- `tests/Feature/ConflictDetectionTest.php` - Testes de detecção de conflitos
- `resources/js/__tests__/HorarioGrid.test.tsx` - Testes do componente

#### Código Chave:

```php
// tests/Feature/HorariosManagementTest.php
describe('Gestão de Horários', function () {
    beforeEach(function () {
        $this->user = User::factory()->cadastroCompleto()->create([
            'status_cadastro' => StatusCadastro::ACEITO,
            'email_verified_at' => now(),
        ]);
        
        $this->docente = User::factory()->cadastroCompleto()->create([
            'status_cadastro' => StatusCadastro::ACEITO,
            'email_verified_at' => now(),
        ]);
        
        $this->projeto = Projeto::factory()->create();
        
        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->docente->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);
    });

    test('discente pode cadastrar horário presencial na baia', function () {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $sala = Sala::factory()->create();
        $baia = Baia::factory()->create(['sala_id' => $sala->id]);

        $dados = [
            'usuario_projeto_id' => $vinculo->id,
            'dia_semana' => WeekDay::Monday->value,
            'hora_inicio' => '08:00',
            'hora_fim' => '10:00',
            'tipo' => TipoHorario::NA_BAIA->value,
            'observacoes' => 'Desenvolvimento presencial',
            'baias' => [$baia->id],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('horarios', [
            'usuario_projeto_id' => $vinculo->id,
            'dia_semana' => WeekDay::Monday->value,
            'tipo' => TipoHorario::NA_BAIA->value,
        ]);

        // Verifica se foi vinculado à baia
        $horario = Horario::where('usuario_projeto_id', $vinculo->id)->first();
        expect($horario->baias)->toContain($baia);
    });

    test('discente pode cadastrar horário remoto sem baia', function () {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $dados = [
            'usuario_projeto_id' => $vinculo->id,
            'dia_semana' => WeekDay::Tuesday->value,
            'hora_inicio' => '14:00',
            'hora_fim' => '18:00',
            'tipo' => TipoHorario::REMOTO->value,
            'observacoes' => 'Trabalho home office',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $horario = Horario::where('usuario_projeto_id', $vinculo->id)
            ->where('tipo', TipoHorario::REMOTO)
            ->first();

        expect($horario->baias)->toBeEmpty();
    });

    test('horário em aula não permite vinculo com projeto nem baia', function () {
        $dados = [
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => null, // EM_AULA não pode ter projeto
            'dia_semana' => WeekDay::Wednesday->value,
            'hora_inicio' => '08:00',
            'hora_fim' => '10:00',
            'tipo' => TipoHorario::EM_AULA->value,
            'observacoes' => 'Aula de Algoritmos Avançados',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $horario = Horario::where('usuario_id', $this->user->id)
            ->where('tipo', TipoHorario::EM_AULA)
            ->first();

        expect($horario->usuario_projeto_id)->toBeNull();
        expect($horario->baias)->toBeEmpty();
    });

    test('horário em aula não pode ter projeto vinculado', function () {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $dados = [
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => $vinculo->id, // Tentativa inválida
            'dia_semana' => WeekDay::Wednesday->value,
            'hora_inicio' => '08:00',
            'hora_fim' => '10:00',
            'tipo' => TipoHorario::EM_AULA->value,
            'observacoes' => 'Aula de Algoritmos',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertSessionHasErrors(['usuario_projeto_id']);
    });

    test('horário ausente pode ser pessoal ou relacionado a projeto', function () {
        // Teste 1: Ausente pessoal (sem projeto)
        $dados = [
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => null,
            'dia_semana' => WeekDay::Friday->value,
            'hora_inicio' => '14:00',
            'hora_fim' => '16:00',
            'tipo' => TipoHorario::AUSENTE->value,
            'observacoes' => 'Consulta médica',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertRedirect()
            ->assertSessionHas('success');

        // Teste 2: Ausente relacionado a projeto (férias do projeto)
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $dados2 = [
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => $vinculo->id,
            'dia_semana' => WeekDay::Monday->value,
            'hora_inicio' => '08:00',
            'hora_fim' => '18:00',
            'tipo' => TipoHorario::AUSENTE->value,
            'observacoes' => 'Férias do projeto',
        ];

        $response2 = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados2);

        $response2->assertRedirect()
            ->assertSessionHas('success');
    });

    test('horário remoto deve ter projeto obrigatório', function () {
        $dados = [
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => null, // Tentativa inválida
            'dia_semana' => WeekDay::Tuesday->value,
            'hora_inicio' => '14:00',
            'hora_fim' => '18:00',
            'tipo' => TipoHorario::REMOTO->value,
            'observacoes' => 'Trabalho remoto sem projeto',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertSessionHasErrors(['usuario_projeto_id']);
    });

    test('horário na baia deve ter projeto obrigatório', function () {
        $dados = [
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => null, // Tentativa inválida
            'dia_semana' => WeekDay::Tuesday->value,
            'hora_inicio' => '14:00',
            'hora_fim' => '18:00',
            'tipo' => TipoHorario::NA_BAIA->value,
            'observacoes' => 'Trabalho presencial sem projeto',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertSessionHasErrors(['usuario_projeto_id']);
    });

        $response->assertSessionHasErrors(['baias']);
    });

    test('sistema detecta conflito entre aula e trabalho no mesmo usuário', function () {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Criar horário existente (em aula - sem projeto vinculado)
        Horario::factory()->create([
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => null, // EM_AULA não tem projeto
            'dia_semana' => WeekDay::Monday,
            'hora_inicio' => '08:00',
            'hora_fim' => '10:00',
            'tipo' => TipoHorario::EM_AULA,
        ]);

        // Tentar criar horário conflitante (trabalho na baia)
        $dados = [
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => $vinculo->id,
            'dia_semana' => WeekDay::Monday->value,
            'hora_inicio' => '09:00',
            'hora_fim' => '11:00',
            'tipo' => TipoHorario::NA_BAIA->value,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertSessionHasErrors(['conflito']);
    });

    test('sistema permite horários em projetos diferentes sem conflito', function () {
        $projeto2 = Projeto::factory()->create();
        
        $vinculo1 = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $vinculo2 = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $projeto2->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        // Horário no projeto 1
        Horario::factory()->create([
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => $vinculo1->id,
            'dia_semana' => WeekDay::Monday,
            'hora_inicio' => '08:00',
            'hora_fim' => '12:00',
            'tipo' => TipoHorario::REMOTO,
        ]);

        // Horário no projeto 2 (horário diferente - sem conflito)
        $dados = [
            'usuario_id' => $this->user->id,
            'usuario_projeto_id' => $vinculo2->id,
            'dia_semana' => WeekDay::Monday->value,
            'hora_inicio' => '14:00',
            'hora_fim' => '18:00',
            'tipo' => TipoHorario::NA_BAIA->value,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('horarios.store'), $dados);

        $response->assertRedirect()
            ->assertSessionHas('success');
    });

    test('sistema detecta conflito de baia entre usuários diferentes', function () {
        $vinculo1 = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $outroUsuario = User::factory()->create();
        $vinculo2 = UsuarioProjeto::factory()->create([
            'usuario_id' => $outroUsuario->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $sala = Sala::factory()->create();
        $baia = Baia::factory()->create(['sala_id' => $sala->id]);

        // Primeiro usuário ocupa a baia
        $horario1 = Horario::factory()->create([
            'usuario_projeto_id' => $vinculo1->id,
            'dia_semana' => WeekDay::Monday,
            'hora_inicio' => '08:00',
            'hora_fim' => '12:00',
            'tipo' => TipoHorario::NA_BAIA,
        ]);
        $horario1->baias()->attach($baia);

        // Segundo usuário tenta usar a mesma baia
        $dados = [
            'usuario_projeto_id' => $vinculo2->id,
            'dia_semana' => WeekDay::Monday->value,
            'hora_inicio' => '10:00',
            'hora_fim' => '14:00',
            'tipo' => TipoHorario::NA_BAIA->value,
            'baias' => [$baia->id],
        ];

        $response = $this->actingAs($outroUsuario)
            ->post(route('horarios.store'), $dados);

        $response->assertSessionHasErrors(['conflito_baia']);
    });

    test('docente pode visualizar horários de discentes do projeto', function () {
        $vinculo = UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $this->projeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        Horario::factory()->create([
            'usuario_projeto_id' => $vinculo->id,
        ]);

        $response = $this->actingAs($this->docente)
            ->get(route('horarios.dashboard', ['projeto' => $this->projeto->id]));

        $response->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Horarios/Dashboard')
                    ->has('horarios.data')
                    ->where('projeto.id', $this->projeto->id)
            );
    });

    test('docente não pode visualizar horários de discentes de outros projetos', function () {
        $outroDocente = User::factory()->create();
        $outroProjeto = Projeto::factory()->create();
        
        UsuarioProjeto::factory()->create([
            'usuario_id' => $outroDocente->id,
            'projeto_id' => $outroProjeto->id,
            'tipo_vinculo' => TipoVinculo::COORDENADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        UsuarioProjeto::factory()->create([
            'usuario_id' => $this->user->id,
            'projeto_id' => $outroProjeto->id,
            'tipo_vinculo' => TipoVinculo::COLABORADOR,
            'status' => StatusVinculoProjeto::APROVADO,
        ]);

        $response = $this->actingAs($this->docente)
            ->get(route('horarios.show', $this->user));

        $response->assertForbidden();
    });
});

// tests/Unit/Services/ConflictDetectionServiceTest.php
test('detecta conflitos sobrepostos no mesmo vínculo de projeto', function () {
    $user = User::factory()->create();
    $projeto = Projeto::factory()->create();
    
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
        'tipo_vinculo' => TipoVinculo::COLABORADOR,
        'status' => StatusVinculoProjeto::APROVADO,
    ]);
    
    $horario1 = Horario::factory()->create([
        'usuario_projeto_id' => $vinculo->id,
        'dia_semana' => WeekDay::Monday,
        'hora_inicio' => '08:00',
        'hora_fim' => '10:00',
        'tipo' => TipoHorario::EM_AULA,
    ]);
    
    $horario2 = Horario::factory()->create([
        'usuario_projeto_id' => $vinculo->id,
        'dia_semana' => WeekDay::Monday,
        'hora_inicio' => '09:00',
        'hora_fim' => '11:00',
        'tipo' => TipoHorario::REMOTO,
    ]);

    $service = new ConflictDetectionService();
    $conflitos = $service->detectarConflitos($user, $projeto);

    expect($conflitos)->toHaveCount(1);
    expect($conflitos->first()['conflitantes'])->toContain($horario2);
    expect($conflitos->first()['projeto']->id)->toBe($projeto->id);
    expect($conflitos->first()['tipo_conflito'])->toBe('conflito_aula_trabalho');
});

test('não detecta conflitos entre projetos diferentes', function () {
    $user = User::factory()->create();
    $projeto1 = Projeto::factory()->create();
    $projeto2 = Projeto::factory()->create();
    
    $vinculo1 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto1->id,
    ]);
    
    $vinculo2 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto2->id,
    ]);
    
    Horario::factory()->create([
        'usuario_projeto_id' => $vinculo1->id,
        'dia_semana' => WeekDay::Monday,
        'hora_inicio' => '08:00',
        'hora_fim' => '10:00',
        'tipo' => TipoHorario::NA_BAIA,
    ]);
    
    Horario::factory()->create([
        'usuario_projeto_id' => $vinculo2->id,
        'dia_semana' => WeekDay::Monday,
        'hora_inicio' => '09:00',
        'hora_fim' => '11:00',
        'tipo' => TipoHorario::REMOTO,
    ]);

    $service = new ConflictDetectionService();
    $conflitos = $service->detectarConflitos($user, $projeto1);

    expect($conflitos)->toHaveCount(0);
});

test('detecta conflitos de baia entre usuários diferentes', function () {
    $projeto = Projeto::factory()->create();
    $sala = Sala::factory()->create();
    $baia = Baia::factory()->create(['sala_id' => $sala->id]);
    
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $vinculo1 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user1->id,
        'projeto_id' => $projeto->id,
    ]);
    
    $vinculo2 = UsuarioProjeto::factory()->create([
        'usuario_id' => $user2->id,
        'projeto_id' => $projeto->id,
    ]);
    
    $horario1 = Horario::factory()->create([
        'usuario_projeto_id' => $vinculo1->id,
        'dia_semana' => WeekDay::Monday,
        'hora_inicio' => '08:00',
        'hora_fim' => '12:00',
        'tipo' => TipoHorario::NA_BAIA,
    ]);
    $horario1->baias()->attach($baia);
    
    $horario2 = Horario::factory()->create([
        'usuario_projeto_id' => $vinculo2->id,
        'dia_semana' => WeekDay::Monday,
        'hora_inicio' => '10:00',
        'hora_fim' => '14:00',
        'tipo' => TipoHorario::NA_BAIA,
    ]);
    $horario2->baias()->attach($baia);

    $service = new ConflictDetectionService();
    $conflitos = $service->detectarConflitosGlobais($projeto);

    expect($conflitos)->toHaveCount(1);
    expect($conflitos->first()['tipo'])->toBe('conflito_baia');
    expect($conflitos->first()['baia']->id)->toBe($baia->id);
});

test('gera relatório de ocupação com estatísticas corretas', function () {
    $projeto = Projeto::factory()->create();
    $user = User::factory()->create();
    
    $vinculo = UsuarioProjeto::factory()->create([
        'usuario_id' => $user->id,
        'projeto_id' => $projeto->id,
    ]);
    
    // 4 horas presenciais
    Horario::factory()->create([
        'usuario_projeto_id' => $vinculo->id,
        'hora_inicio' => '08:00',
        'hora_fim' => '12:00',
        'tipo' => TipoHorario::NA_BAIA,
    ]);
    
    // 2 horas remotas
    Horario::factory()->create([
        'usuario_projeto_id' => $vinculo->id,
        'hora_inicio' => '14:00',
        'hora_fim' => '16:00',
        'tipo' => TipoHorario::REMOTO,
    ]);
    
    // 2 horas em aula (não conta como produtivo)
    Horario::factory()->create([
        'usuario_projeto_id' => $vinculo->id,
        'hora_inicio' => '16:00',
        'hora_fim' => '18:00',
        'tipo' => TipoHorario::EM_AULA,
    ]);

    $service = new ConflictDetectionService();
    $relatorio = $service->gerarRelatorioOcupacao($projeto, []);

    expect($relatorio['total_horas'])->toBe(6); // 4 + 2 (não conta EM_AULA)
    expect($relatorio['horas_presenciais'])->toBe(4);
    expect($relatorio['horas_remotas'])->toBe(2);
    expect($relatorio['taxa_presencial'])->toBe(66.67); // 4/6 * 100
});
```

### ✅ Critérios de Aceitação

- [ ] Cobertura de testes > 90% para lógica crítica
- [ ] Todos os endpoints têm testes de autorização
- [ ] Edge cases estão cobertos
- [ ] Testes passam consistentemente
- [ ] Performance queries está testada
- [ ] Componentes React principais testados

### 🚨 Pontos de Atenção

- Usar factories para dados consistentes
- Testar tanto happy path quanto error cases
- Mockar dependências externas
- Verificar memory leaks em testes longos

### 📊 Estimativa

**Complexidade**: Alta
**Tempo estimado**: 14 horas

### 🔗 Dependências

- Depende de: Card 4 (Frontend)
- Bloqueia: Card 6 (Documentação)

---

## Card 6: DOCUMENTAÇÃO & DEPLOY 📚

### 📋 Descrição

Criar documentação técnica completa, guias de usuário, verificações de performance e segurança, além de preparar checklist de deploy e plano de rollback para produção.

### 🎯 Objetivos

- [ ] Documentar arquitetura e decisões técnicas
- [ ] Criar guias de uso para usuários finais
- [ ] Verificar performance e otimizações
- [ ] Realizar auditoria de segurança
- [ ] Preparar deploy e rollback procedures
- [ ] Configurar monitoramento

### 📦 Entregáveis

- [ ] Documentação técnica da arquitetura
- [ ] Guia do usuário com screenshots
- [ ] Relatório de performance e otimizações
- [ ] Checklist de segurança
- [ ] Scripts de deploy e rollback
- [ ] Configuração de monitoramento
- [ ] Documentação de APIs

### 🔧 Implementação

#### Arquivos a Criar:

- `docs/espacos-horarios/README.md` - Documentação principal
- `docs/espacos-horarios/user-guide.md` - Guia do usuário
- `docs/espacos-horarios/api-docs.md` - Documentação da API
- `docs/espacos-horarios/performance-report.md` - Relatório de performance
- `scripts/deploy-espacos.sh` - Script de deploy
- `docs/espacos-horarios/security-checklist.md` - Checklist de segurança

#### Código Chave:

```markdown
# Gestão de Horários, Salas e Baias - Documentação Técnica

## Visão Geral

O módulo de Gestão de Horários, Salas e Baias permite:

- **Gestão de Infraestrutura**: Cadastro e organização de salas e baias
- **Horários Contextualizados**: Registro detalhado de onde/como cada colaborador trabalha
  - 📚 **Em Aula**: Horários de graduação/pós-graduação
  - 🏠 **Remoto**: Trabalho home office no projeto
  - 🏢 **Na Baia**: Trabalho presencial com baia específica
  - ❌ **Ausente**: Indisponibilidade (folga, compromisso, etc.)
- **Detecção de Conflitos**: Identificação automática de sobreposições e conflitos de baia
- **Dashboard Inteligente**: Visualização em tempo real de ocupação física vs remota
- **Sistema de Alertas**: Notificações para resolução de conflitos
- **Relatórios de Ocupação**: Estatísticas de utilização do laboratório

## Arquitetura

### Models Principais

```php
Sala -> hasMany(Baia) -> belongsToMany(User) -> hasMany(Horario)
```

### Fluxo de Dados

1. **Cadastro de Sala** → Docente cria sala com capacidade
2. **Criação de Baias** → Baias são vinculadas à sala
3. **Registro de Horários** → Discente cadastra disponibilidade
4. **Detecção de Conflitos** → Sistema valida sobreposições
5. **Alertas Automáticos** → Notificações são enviadas
6. **Resolução** → Usuário ajusta horários conforme necessário

## Endpoints da API

### Salas
- `GET /api/salas` - Listar salas
- `POST /api/salas` - Criar sala
- `PUT /api/salas/{id}` - Atualizar sala
- `DELETE /api/salas/{id}` - Remover sala

### Horários
- `GET /api/horarios` - Listar horários (com filtro por projeto)
- `POST /api/horarios` - Criar horário vinculado a projeto
- `GET /api/horarios/conflitos` - Verificar conflitos por projeto
- `GET /api/horarios/dashboard/{projeto}` - Dashboard de visualização por projeto
- `GET /api/horarios/relatorio/{projeto}` - Relatório de horas por projeto
- `GET /api/projetos/{projeto}/horarios/utilizacao` - Taxa de utilização do projeto

## Permissões

| Ação | Discente | Docente | Admin |
|------|----------|---------|-------|
| Ver próprios horários | ✓ | ✓ | ✓ |
| Ver horários do projeto | ✗ | ✓ | ✓ |
| Criar/editar horários | ✓ | ✓ | ✓ |
| Gerenciar salas | ✗ | ✓ | ✓ |
| Vincular baias (NA_BAIA) | ✓ | ✓ | ✓ |
| Ver estatísticas de ocupação | ✗ | ✓ | ✓ |
| Enviar alertas | ✗ | ✓ | ✓ |

## Regras de Negócio dos Tipos de Horário

### 📚 EM_AULA
- **Quando usar**: Horários de disciplinas da graduação/pós-graduação
- **Características**: Indisponível para projeto, não vincula baia
- **Conflitos**: Pode gerar conflito com outros tipos

### 🏠 REMOTO  
- **Quando usar**: Trabalho home office no projeto
- **Características**: Produtivo para projeto, não requer baia física
- **Benefícios**: Flexibilidade, economia de espaço físico

### 🏢 NA_BAIA
- **Quando usar**: Trabalho presencial no laboratório
- **Características**: Produtivo para projeto, requer baia específica
- **Validações**: Obrigatório vincular pelo menos uma baia
- **Conflitos**: Detecta conflitos de ocupação de baia

### ❌ AUSENTE
- **Quando usar**: Folgas, compromissos pessoais, férias
- **Características**: Indisponível para projeto, não vincula baia
- **Propósito**: Transparência na disponibilidade da equipe

## Exemplos Práticos de Uso

### Cenário 1: Discente de Mestrado
```
Segunda-feira:
08:00-10:00 📚 EM_AULA (Aula de Algoritmos Avançados)
10:00-12:00 🏢 NA_BAIA (Desenvolvimento presencial - Baia 15)
14:00-18:00 🏠 REMOTO (Revisão de código e documentação)

Terça-feira:
08:00-12:00 🏢 NA_BAIA (Reunião com orientador + desenvolvimento - Baia 15)
14:00-16:00 📚 EM_AULA (Seminário de Pesquisa)
16:00-18:00 ❌ AUSENTE (Consulta médica)
```

### Cenário 2: Colaborador Externo
```
Segunda a Sexta:
09:00-12:00 🏠 REMOTO (Desenvolvimento back-end)
14:00-17:00 🏠 REMOTO (Code review e testes)

Quarta-feira:
14:00-17:00 🏢 NA_BAIA (Reunião semanal presencial - Baia 12)
```

### Cenário 3: Discente de Graduação
```
Segunda-feira:
07:00-12:00 📚 EM_AULA (Aulas da graduação)
14:00-18:00 🏢 NA_BAIA (Desenvolvimento do projeto - Baia 8)

Sábado:
08:00-12:00 🏠 REMOTO (Estudos e desenvolvimento)
```

### Benefícios dos Tipos Contextualizados

#### Para Coordenadores de Projeto:
- **Visibilidade real**: Saber quando o colaborador está disponível vs indisponível
- **Planejamento**: Otimizar reuniões baseado na presença física
- **Gestão de recursos**: Distribuir baias com base na demanda real
- **Conflitos inteligentes**: Detectar sobreposições de compromissos

#### Para Colaboradores:
- **Flexibilidade**: Alternar entre trabalho presencial e remoto
- **Transparência**: Comunicar disponibilidade de forma clara
- **Organização**: Separar compromissos acadêmicos de projeto
- **Autonomia**: Gerenciar próprio tempo e local de trabalho

#### Para o Laboratório:
- **Ocupação otimizada**: Dados reais de uso do espaço físico
- **Relatórios precisos**: Estatísticas de produtividade por modalidade
- **Planejamento espacial**: Decisões baseadas em dados de ocupação
- **Compliance acadêmico**: Respeitar horários de aula dos discentes

## Lógica de Detecção de Conflitos

### Tipos de Conflitos por Categoria

#### 1. Conflitos de Disponibilidade (Mesmo Vínculo de Projeto)
```
EM_AULA ⚠️ QUALQUER_OUTRO_TIPO
- Pessoa não pode estar em aula e trabalhando ao mesmo tempo
- Criticidade: ALTA - Viola obrigações acadêmicas

AUSENTE ⚠️ QUALQUER_OUTRO_TIPO  
- Pessoa não pode estar ausente e disponível simultaneamente
- Criticidade: MÉDIA - Indica problema de planejamento

NA_BAIA ⚠️ REMOTO
- Pessoa não pode estar presencialmente e remotamente ao mesmo tempo
- Criticidade: ALTA - Conflito físico impossível
```

#### 2. Conflitos de Espaço Físico (Entre Usuários)
```
NA_BAIA (User A) ⚠️ NA_BAIA (User B) + MESMA_BAIA
- Duas pessoas não podem ocupar a mesma baia simultaneamente
- Criticidade: ALTA - Conflito de recurso físico

NA_BAIA (User A) ✅ REMOTO (User B) + MESMA_BAIA
- Uma pessoa presencial, outra remota = SEM CONFLITO
- A baia fica disponível para o usuário presencial
```

#### 3. Conflitos de Projeto (Entre Projetos Diferentes)
```
PROJETO_A (User X) ⚠️ PROJETO_B (User X) + MESMO_HORÁRIO
- Pessoa não pode trabalhar em dois projetos simultaneamente
- Criticidade: MÉDIA - Indica sobrecarga ou erro de planejamento
- Exceção: EM_AULA não conflita entre projetos (prioridade acadêmica)
```

### Algoritmo de Detecção

```typescript
interface ConflictDetection {
    // Passo 1: Verificar conflitos dentro do mesmo vínculo de projeto
    detectarConflitosIntraVinculo(usuarioProjetoId: string): ConflictResult[];
    
    // Passo 2: Verificar conflitos de baia entre usuários diferentes  
    detectarConflitosEspacoFisico(baiaId: string, diaHora: TimeSlot): ConflictResult[];
    
    // Passo 3: Verificar conflitos entre projetos diferentes do mesmo usuário
    detectarConflitosInterProjetos(usuarioId: string): ConflictResult[];
}

// Matriz de compatibilidade entre tipos
const COMPATIBILIDADE_TIPOS = {
    'EM_AULA': {
        'EM_AULA': 'CONFLITO',     // Duas aulas simultaneamente
        'REMOTO': 'CONFLITO',      // Aula vs trabalho remoto  
        'NA_BAIA': 'CONFLITO',     // Aula vs trabalho presencial
        'AUSENTE': 'CONFLITO'      // Aula vs ausência
    },
    'REMOTO': {
        'REMOTO': 'PERMITIDO',     // Múltiplos trabalhos remotos (se projetos diferentes)
        'NA_BAIA': 'CONFLITO',     // Remoto vs presencial
        'AUSENTE': 'CONFLITO'      // Trabalho vs ausência
    },
    'NA_BAIA': {
        'NA_BAIA': 'VERIFICAR_BAIA', // Depende da baia física
        'AUSENTE': 'CONFLITO'        // Presencial vs ausência
    },
    'AUSENTE': {
        'AUSENTE': 'PERMITIDO'     // Múltiplas ausências OK
    }
};
```

## Otimizações de Performance

### Queries Principais
```sql
-- Dashboard de horários otimizado por projeto
SELECT h.*, u.name, s.nome as sala_nome, p.nome as projeto_nome
FROM horarios h
JOIN usuario_projeto up ON h.usuario_projeto_id = up.id
JOIN users u ON up.usuario_id = u.id
JOIN projetos p ON up.projeto_id = p.id
LEFT JOIN horario_baia hb ON h.id = hb.horario_id
LEFT JOIN baias b ON hb.baia_id = b.id
LEFT JOIN salas s ON b.sala_id = s.id
WHERE up.projeto_id = ?
ORDER BY h.dia_semana, h.hora_inicio;

-- Detecção de conflitos de baia
SELECT h1.*, h2.*, b.numero as baia_numero, s.nome as sala_nome
FROM horarios h1
JOIN horario_baia hb1 ON h1.id = hb1.horario_id
JOIN baias b ON hb1.baia_id = b.id
JOIN salas s ON b.sala_id = s.id
JOIN horario_baia hb2 ON b.id = hb2.baia_id
JOIN horarios h2 ON hb2.horario_id = h2.id
WHERE h1.id != h2.id 
  AND h1.dia_semana = h2.dia_semana
  AND h1.tipo = 'NA_BAIA' 
  AND h2.tipo = 'NA_BAIA'
  AND (
    (h1.hora_inicio < h2.hora_fim AND h1.hora_fim > h2.hora_inicio)
  );

-- Relatório de ocupação por tipo de horário
SELECT 
    p.nome as projeto,
    h.tipo,
    COUNT(*) as total_horarios,
    SUM(EXTRACT(EPOCH FROM (h.hora_fim - h.hora_inicio))/3600) as total_horas,
    COUNT(DISTINCT up.usuario_id) as usuarios_unicos
FROM horarios h
JOIN usuario_projeto up ON h.usuario_projeto_id = up.id
JOIN projetos p ON up.projeto_id = p.id
WHERE h.tipo IN ('NA_BAIA', 'REMOTO')
GROUP BY p.id, p.nome, h.tipo
ORDER BY p.nome, h.tipo;
```

### Cache Strategy
- **Dashboard por projeto**: Cache por 5 minutos
- **Conflitos por projeto**: Cache por 1 minuto
- **Salas/Baias**: Cache por 1 hora
- **Relatórios de utilização**: Cache por 30 minutos

## Monitoramento

### Métricas Principais
- Tempo de resposta do dashboard
- Taxa de conflitos detectados
- Utilização de salas/baias
- Performance de queries de horários

### Alertas Críticos
- Query lenta (> 2s)
- Alto número de conflitos (> 10/dia)
- Falha na detecção automática
- Erro na sincronização de dados

## FAQ e Troubleshooting

### Perguntas Frequentes

#### Q: Posso cadastrar o mesmo horário para trabalho remoto e presencial?
**R:** Não. O sistema detectará conflito pois uma pessoa não pode estar em dois locais ao mesmo tempo. Escolha o tipo mais apropriado para cada horário.

#### Q: Como registrar um horário de reunião híbrida (alguns presenciais, outros remotos)?
**R:** Cada pessoa deve registrar individualmente:
- Participantes presenciais: `NA_BAIA` + vincular baia da reunião
- Participantes remotos: `REMOTO` (sem baia vinculada)

#### Q: Posso alterar um horário de `NA_BAIA` para `REMOTO` depois?
**R:** Sim. O sistema automaticamente removerá os vínculos com baias quando o tipo for alterado para `REMOTO`, `EM_AULA` ou `AUSENTE`.

#### Q: O que acontece se eu tiver aula e um compromisso de projeto no mesmo horário?
**R:** Registre como `EM_AULA` (prioridade acadêmica). Coordene com seu orientador para reagendar atividades do projeto.

#### Q: Como marcar férias ou folgas?
**R:** Use o tipo `AUSENTE` para indicar indisponibilidade. Isso ajuda a equipe a saber quando você não estará disponível.

### Problemas Comuns e Soluções

#### ❌ "Erro: Conflito de horário detectado"
```
Problema: Tentativa de sobrepor horários incompatíveis
Soluções:
1. Verificar horários já cadastrados no mesmo dia
2. Ajustar horários para evitar sobreposição
3. Considerar alterar tipo de horário (ex: remoto em vez de presencial)
4. Para aulas obrigatórias, manter EM_AULA e ajustar horários de projeto
```

#### ❌ "Erro: Baia obrigatória para trabalho presencial"
```
Problema: Tentativa de cadastrar NA_BAIA sem selecionar baia
Soluções:
1. Selecionar pelo menos uma baia disponível
2. Verificar se a baia não está ocupada no mesmo horário
3. Considerar alterar para REMOTO se baia não for essencial
```

#### ❌ "Erro: Permissão negada para visualizar horários"
```
Problema: Tentativa de acessar horários sem permissão adequada
Soluções:
1. Verificar se é coordenador do projeto
2. Confirmar vínculo ativo com o projeto
3. Contactar administrador se problema persistir
```

#### ⚠️ "Aviso: Alta ocupação de baias detectada"
```
Problema: Poucas baias disponíveis para demanda
Soluções:
1. Coordenadores podem redistribuir horários presenciais
2. Incentivar trabalho remoto quando possível
3. Avaliar aquisição de mais equipamentos/espaço
```

### Monitoramento e Alertas

#### Métricas de Saúde do Sistema
- **Taxa de Conflitos**: < 5% dos horários cadastrados
- **Ocupação de Baias**: < 80% na capacidade máxima
- **Tempo de Resposta**: < 2s para dashboard principal
- **Disponibilidade**: 99.5% uptime

#### Alertas Automáticos
```php
// Exemplo de configuração de alertas
AlertaHorario::create([
    'tipo' => 'CONFLITO_BAIA',
    'usuario_id' => $usuarioAfetado->id,
    'docente_id' => $coordenadorProjeto->id,
    'mensagem' => "Conflito de baia detectado: {$detalhes}",
    'metadata' => [
        'horario_id' => $horario->id,
        'baia_id' => $baia->id,
        'projeto_id' => $projeto->id,
        'prioridade' => 'ALTA'
    ]
]);
```
```

### ✅ Critérios de Aceitação

- [ ] Documentação técnica completa e atualizada
- [ ] Guia do usuário com exemplos práticos
- [ ] Performance verificada e otimizada
- [ ] Segurança auditada e aprovada
- [ ] Deploy testado em staging
- [ ] Rollback procedures documentados
- [ ] Monitoramento configurado

### 🚨 Pontos de Atenção

- Validar permissões em produção
- Testar backup e restore procedures
- Verificar compatibilidade com dados existentes
- Configurar alertas de monitoramento

### 📊 Estimativa

**Complexidade**: Média
**Tempo estimado**: 8 horas

### 🔗 Dependências

- Depende de: Card 5 (Testes & Qualidade)
- Bloqueia: Nenhum (feature completa)

---

## Resumo de Implementação

**Total Estimado**: 64 horas
**Complexidade Geral**: Alta
**Riscos Principais**: Performance de queries, complexidade de autorização, UX de conflitos

**Ordem de Implementação**:
1. Análise & Design (8h)
2. Database & Migrations (6h) 
3. Backend (12h)
4. Frontend (16h)
5. Testes & Qualidade (14h)
6. Documentação & Deploy (8h)

**Entregas Principais**:
- Sistema completo de gestão de infraestrutura física do laboratório
- Interface intuitiva para registro de horários contextualizados (Em Aula/Remoto/Na Baia/Ausente)
- Dashboard inteligente com visualização em tempo real de ocupação física vs remota
- Sistema automático de detecção de conflitos por tipo de horário e ocupação de baia
- Relatórios detalhados de produtividade e utilização de espaço por projeto
- Sistema de alertas proativos para resolução de conflitos
- Cobertura de testes robusta com cenários específicos para cada tipo de horário
- Documentação completa com exemplos práticos e troubleshooting

**Principais Inovações**:
- **Tipos de Horário Contextualizados**: Diferenciação clara entre compromissos acadêmicos e trabalho de projeto
- **Gestão Híbrida**: Suporte nativo para trabalho presencial e remoto
- **Detecção Inteligente**: Algoritmos específicos para cada tipo de conflito
- **Vinculação por Projeto**: Horários associados ao vínculo projeto-usuário para melhor rastreabilidade
- **Dashboard em Tempo Real**: Estatísticas instantâneas de ocupação do laboratório
- **Flexibilidade Total**: Colaboradores podem alternar entre modalidades conforme necessidade
