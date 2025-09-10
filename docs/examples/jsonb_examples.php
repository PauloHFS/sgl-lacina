<?php

/**
 * Exemplos práticos de como manipular campos JSONB no Eloquent
 * 
 * Este arquivo demonstra as melhores práticas para trabalhar com dados JSON
 * no PostgreSQL usando o Laravel Eloquent ORM.
 */

namespace App\Examples;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class JsonbExamples
{
  /**
   * 1. OPERAÇÕES BÁSICAS COM JSONB
   */

  public function exemploOperacoesBasicas()
  {
    $user = User::find('uuid-aqui');

    // ✅ Acessar dados JSON usando cast 'array'
    $preferencias = $user->campos_extra; // Retorna array PHP

    // ✅ Definir dados JSON
    $user->campos_extra = [
      'preferencias' => [
        'tema' => 'dark',
        'idioma' => 'pt-BR',
        'notificacoes' => true
      ],
      'configuracoes_profile' => [
        'publico' => false,
        'mostrar_email' => true
      ]
    ];
    $user->save();

    // ✅ Usando métodos auxiliares customizados
    $user->setCampoExtra('preferencias.tema', 'light');
    $tema = $user->getCampoExtra('preferencias.tema'); // 'light'
    $user->save();
  }

  /**
   * 2. CONSULTAS JSONB - OPERADORES POSTGRESQL
   */

  public function exemploConsultasJsonb()
  {
    // ✅ Operador -> : Acessar chave JSON (retorna JSON)
    $users = User::whereRaw("campos_extra->'preferencias'->>'tema' = ?", ['dark'])->get();

    // ✅ Operador ->> : Acessar chave JSON (retorna text)
    $users = User::whereRaw("campos_extra->>'status' = ?", ['ativo'])->get();

    // ✅ Operador @> : Contém JSON (verificação de inclusão)
    $users = User::whereRaw("campos_extra @> ?", [json_encode(['preferencias' => ['tema' => 'dark']])])->get();

    // ✅ Operador ? : JSON contém chave
    $users = User::whereRaw("campos_extra ? ?", ['configuracoes_profile'])->get();

    // ✅ Operador ?& : JSON contém todas as chaves
    $users = User::whereRaw("campos_extra ?& ?", ['{preferencias,configuracoes_profile}'])->get();

    // ✅ Operador ?| : JSON contém qualquer uma das chaves
    $users = User::whereRaw("campos_extra ?| ?", ['{tema_antigo,configuracoes_profile}'])->get();
  }

  /**
   * 3. CONSULTAS USANDO MÉTODOS ELOQUENT
   */

  public function exemploConsultasEloquent()
  {
    // ✅ Usando whereJsonContains (Laravel 5.8+)
    $users = User::whereJsonContains('campos_extra->preferencias->tema', 'dark')->get();

    // ✅ Usando whereJsonLength
    $users = User::whereJsonLength('campos_extra->tecnologias', 3)->get();

    // ✅ Consultas mais complexas
    $users = User::where('campos_extra->preferencias->notificacoes', true)
      ->whereJsonContains('campos_extra->tecnologias', 'Laravel')
      ->get();

    // ✅ Ordenação por campos JSON
    $users = User::orderByRaw("campos_extra->>'created_profile_at'")->get();
  }

  /**
   * 4. ATUALIZAÇÕES JSONB
   */

  public function exemploAtualizacoesJsonb()
  {
    // ✅ Atualizar campo específico mantendo outros dados
    DB::table('users')
      ->where('id', 'uuid-aqui')
      ->update([
        'campos_extra' => DB::raw("jsonb_set(campos_extra, '{preferencias,tema}', '\"light\"')")
      ]);

    // ✅ Adicionar novo campo ao JSON existente
    DB::table('users')
      ->where('id', 'uuid-aqui')
      ->update([
        'campos_extra' => DB::raw("campos_extra || '{\"nova_config\": true}'::jsonb")
      ]);

    // ✅ Remover campo do JSON
    DB::table('users')
      ->where('id', 'uuid-aqui')
      ->update([
        'campos_extra' => DB::raw("campos_extra - 'campo_obsoleto'")
      ]);

    // ✅ Atualização usando Eloquent (substitui todo o JSON)
    $user = User::find('uuid-aqui');
    $user->mergeCamposExtra(['nova_preferencia' => 'valor']);
    $user->save();
  }

  /**
   * 5. ÍNDICES JSONB PARA PERFORMANCE
   */

  public function exemploIndices()
  {
    // Em migrations, você pode criar índices específicos:

    /*
        // Índice GIN genérico (bom para consultas @>, ?, ?&, ?|)
        $table->index(DB::raw('(campos_extra)'), 'idx_users_campos_extra_gin', 'gin');
        
        // Índice em campo específico
        $table->index(DB::raw("(campos_extra->'preferencias'->>'tema')"), 'idx_users_tema');
        
        // Índice GIN em path específico
        $table->index(DB::raw("(campos_extra->'tecnologias')"), 'idx_users_tecnologias_gin', 'gin');
        */
  }

  /**
   * 6. VALIDAÇÃO DE DADOS JSONB
   */

  public function exemploValidacao()
  {
    // ✅ Em Form Requests
    /*
        'campos_extra' => 'json',
        'campos_extra.preferencias.tema' => 'required|in:light,dark',
        'campos_extra.preferencias.idioma' => 'required|string|max:5',
        'campos_extra.tecnologias' => 'array',
        'campos_extra.tecnologias.*' => 'string|max:50',
        */

    // ✅ Validação customizada
    $user = new User();
    $user->campos_extra = [
      'preferencias' => [
        'tema' => 'dark',
        'idioma' => 'pt-BR'
      ]
    ];

    // Validar estrutura antes de salvar
    if (!$this->validarEstruturaCamposExtra($user->campos_extra)) {
      throw new \InvalidArgumentException('Estrutura de campos_extra inválida');
    }
  }

  /**
   * 7. AGGREGATIONS E FUNÇÕES JSONB
   */

  public function exemploAggregations()
  {
    // ✅ Contar usuários por preferência de tema
    $stats = DB::table('users')
      ->select(DB::raw("campos_extra->'preferencias'->>'tema' as tema, COUNT(*) as total"))
      ->whereNotNull('campos_extra')
      ->groupBy(DB::raw("campos_extra->'preferencias'->>'tema'"))
      ->get();

    // ✅ Extrair todas as tecnologias únicas
    $tecnologias = DB::table('users')
      ->select(DB::raw("DISTINCT jsonb_array_elements_text(campos_extra->'tecnologias') as tecnologia"))
      ->whereRaw("jsonb_typeof(campos_extra->'tecnologias') = 'array'")
      ->pluck('tecnologia');

    // ✅ Estatísticas avançadas
    $stats = DB::table('users')
      ->select([
        DB::raw("AVG(jsonb_array_length(campos_extra->'tecnologias')) as media_tecnologias"),
        DB::raw("COUNT(CASE WHEN campos_extra->'preferencias'->>'tema' = 'dark' THEN 1 END) as usuarios_tema_dark")
      ])
      ->whereRaw("jsonb_typeof(campos_extra->'tecnologias') = 'array'")
      ->first();
  }

  /**
   * 8. SCOPE CUSTOMIZADO PARA CONSULTAS JSONB
   */

  public function exemploScopes()
  {
    // Adicione ao modelo User:
    /*
        public function scopeComTema($query, $tema)
        {
            return $query->whereRaw("campos_extra->'preferencias'->>'tema' = ?", [$tema]);
        }

        public function scopeComTecnologia($query, $tecnologia)
        {
            return $query->whereJsonContains('campos_extra->tecnologias', $tecnologia);
        }

        public function scopeConfiguracao($query, $chave, $valor)
        {
            return $query->whereRaw("campos_extra->>'$chave' = ?", [$valor]);
        }
        */

    // Uso:
    // $users = User::comTema('dark')->get();
    // $users = User::comTecnologia('Laravel')->get();
  }

  /**
   * 9. MUTATORS E ACCESSORS PARA JSONB
   */

  public function exemploMutatorsAccessors()
  {
    // Adicione ao modelo User:
    /*
        // Accessor para facilitar acesso a preferências
        public function getPreferenciasAttribute()
        {
            return $this->getCampoExtra('preferencias', []);
        }

        // Mutator para garantir estrutura de preferências
        public function setPreferenciasAttribute($value)
        {
            $this->setCampoExtra('preferencias', $value);
        }

        // Accessor para lista de tecnologias
        public function getTecnologiasListaAttribute()
        {
            return $this->getCampoExtra('tecnologias', []);
        }
        */
  }

  /**
   * 10. EXEMPLO PRÁTICO: SISTEMA DE CONFIGURAÇÕES
   */

  public function exemploSistemaConfiguracoes()
  {
    $user = User::find('uuid-aqui');

    // Configurar perfil do usuário
    $user->mergeCamposExtra([
      'profile_settings' => [
        'theme' => 'dark',
        'language' => 'pt-BR',
        'timezone' => 'America/Sao_Paulo',
        'notifications' => [
          'email' => true,
          'push' => false,
          'sms' => false
        ]
      ],
      'permissions' => [
        'can_edit_projects' => true,
        'can_manage_users' => false,
        'can_export_data' => true
      ],
      'last_activity' => [
        'login_at' => now()->toISOString(),
        'page_visited' => '/dashboard',
        'device' => 'desktop'
      ]
    ]);

    $user->save();

    // Buscar usuários com notificações email ativadas
    $usersWithEmail = User::whereRaw("campos_extra->'profile_settings'->'notifications'->>'email' = 'true'")->get();

    // Buscar usuários que podem gerenciar projetos
    $projectManagers = User::whereRaw("campos_extra->'permissions'->>'can_edit_projects' = 'true'")->get();
  }

  /**
   * Função auxiliar para validar estrutura
   */
  private function validarEstruturaCamposExtra(array $data): bool
  {
    // Implementar validação customizada da estrutura JSON
    return isset($data['preferencias']) && is_array($data['preferencias']);
  }
}

/**
 * RESUMO DAS MELHORES PRÁTICAS:
 * 
 * 1. ✅ Sempre use cast 'array' no modelo
 * 2. ✅ Crie métodos auxiliares para manipulação (getCampoExtra, setCampoExtra)
 * 3. ✅ Use operadores PostgreSQL nativos para consultas complexas
 * 4. ✅ Crie índices GIN para campos JSONB consultados frequentemente
 * 5. ✅ Valide a estrutura JSON antes de salvar
 * 6. ✅ Use scopes para consultas JSONB reutilizáveis
 * 7. ✅ Prefira whereJsonContains() para consultas simples
 * 8. ✅ Use DB::raw() para operações PostgreSQL específicas
 * 9. ✅ Considere usar accessors/mutators para campos JSON frequentes
 * 10. ✅ Monitore performance das consultas JSONB
 */
