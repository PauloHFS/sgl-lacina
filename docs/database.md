# Database

Quando lidando com as migrations e com as tabelas, levem em consideração o seguinte DBML:

```dbml
table usuarios {
  id bigint [pk]
  nome varchar(255)
  linkedin_url varchar
  github_url varchar
  figma_url varchar
  foto_url varchar
  curriculo text
  area_atuacao varchar
  tecnologias text
  cpf varchar
  conta_bancaria varchar
  agencia varchar
  codigo_banco varchar
  rg varchar
  uf_rg varchar
  orgao_emissor_rg varchar
  telefone varchar
  email varchar(255)
  email_verified_at timestamp(0)
  password varchar(255)
  remember_token varchar(100)
  created_at timestamp(0)
  updated_at timestamp(0)
}

enum TipoVinculo {
  COORDENADOR
  COLABORADOR
}

enum Funcao {
  COODERNADOR
  PESQUISADOR
  DESENVOLVEDOR
  TECNICO
  ALUNO
}

table usuario_vinculo {
  projeto_id bigint [ref: > projetos.id]
  usuario_id bigint [ref: > usuarios.id]
  tipo_vinculo TipoVinculo [not null]
  funcao Funcao [not null]
  data_inicio datetime [not null]
  data_fim datetime

  indexes {
    (projeto_id, usuario_id, data_fim) [pk]
  }
}

enum TipoProjeto {
  PDI
  TCC
  MESTRADO
  DOUTORADO
  SUPORTE
}

table projetos {
  id bigint [pk, increment]
  nome varchar [not null]
  data_inicio date [not null]
  data_termino date
  cliente varchar [not null]
  descricao text
  slack_url varchar
  discord_url varchar
  board_url varchar
  git_url varchar
  tipo TipoProjeto [not null]
  created_at datetime
  updated_at datetime
}

enum StatusParticipacaoProjeto {
  APROVADO
  PENDENTE
  REJEITADO
}

table solicitacoes_projeto {
  id bigint [pk, increment]
  usuario_id bigint [ref: > usuarios.id]
  projeto_id bigint [ref: > projetos.id]
  status StatusParticipacaoProjeto [not null]
  created_at datetime
  updated_at datetime
}

enum StatusSolicitacaoTrocaProjeto {
  PENDENTE
  APROVADO
  REJEITADO
}

table solicitacoes_troca_projeto {
  usuario_id bigint [Ref: > usuarios.id]
  projeto_atual_id bigint [Ref: > projetos.id]
  projeto_novo_id bigint [Ref: > projetos.id]
  motivo text [not null]
  resposta text
  status StatusSolicitacaoTrocaProjeto [not null]
  data_solicitacao date [not null]
  data_resposta date

  indexes {
    (usuario_id, projeto_atual_id, projeto_novo_id) [pk]
  }
}

enum WeekDay {
  SEGUNDA
  TERCA
  QUARTA
  QUINTA
  SEXTA
  SABADO
  DOMINGO
}

enum TipoHorario {
  AULA
  TRABALHO
  AUSENTE
}

table horarios {
  id bigint [pk, increment]
  colaborador_id bigint [ref: > usuarios.id]
  dia_semana WeekDay [not null]
  horario_inicio time [not null]
  horario_termino time [not null]
  tipo TipoHorario [not null]
  created_at datetime
  updated_at datetime
}

table folgas {
  id int [pk, increment]
  usuario_id bigint [ref: > usuarios.id, note: "Colaborador que solicitou a folga"]
  tipo varchar [not null, note: "Pode ser 'coletiva' ou 'pessoal'"]
  data_inicio date [not null]
  data_fim date [not null]
  justificativa text
  status varchar [not null, note: "Pode ser 'aprovada', 'pendente' ou 'rejeitada'"]
  created_at datetime
  updated_at datetime
}

table salas {
  id bigint [pk, increment]
  nome varchar [not null]
  senha_porta varchar [not null]
  created_at datetime
  updated_at datetime
}

table baias {
  id bigint [pk, increment]
  sala_id bigint [not null, ref: > salas.id]
  nome varchar [not null]
  created_at datetime
  updated_at datetime
}

table horarios_baia {
  horario_id bigint [not null, ref: > horarios.id]
  sala_id bigint [not null, ref: > salas.id]
  created_at datetime
  updated_at datetime

  indexes {
    (horario_id, sala_id) [pk]
  }
}

// >>>>>>>>>>>>>>> Tabelas do LARAVEL
table sessions {
  id varchar(255) [pk]
  user_id bigint [ref: > usuarios.id]
  ip_address varchar(45)
  user_agent text
  payload text
  last_activity integer
}

table password_reset_tokens {
  email varchar(255) [pk]
  token varchar(255)
  created_at timestamp(0)
}

table migrations {
  id integer [pk]
  migration varchar(255)
  batch integer
}

table cache {
  key varchar(255) [pk]
  value text
  expiration integer
}

table cache_locks {
  key varchar(255) [pk]
  owner varchar(255)
  expiration integer
}

table job_batches {
  id varchar(255) [pk]
  name varchar(255)
  total_jobs integer
  pending_jobs integer
  failed_jobs integer
  failed_jobs_ids text
  options text
  cancelled_at integer
  created_at integer
  finished_at integer
}

table failed_jobs {
  id bigint [pk]
  uuid varchar(255)
  connection text
  queue text
  payload text
  exception text
  failed_at timestamp
}
// <<<<<<<<<<<<< Tabelas do Laravel
```
