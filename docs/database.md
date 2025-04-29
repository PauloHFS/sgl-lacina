# Database

Quando lidando com as migrations e com as tabelas, levem em consideração o seguinte DBML:

```dbml
enum StatusCadastro {
  ACEITO
  RECUSADO
  PENDENTE
}

enum TipoVinculoProjeto {
  COORDENADOR
  COLABORADOR
}

enum Funcao {
  COORDENADOR
  PESQUISADOR
  DESENVOLVEDOR
  TECNICO
  ALUNO
}

enum StatusVinculoProjeto {
  APROVADO
  PENDENTE
  REJEITADO
  INATIVO
}

enum TipoProjeto {
  PDI
  TCC
  MESTRADO
  DOUTORADO
  SUPORTE
}

// TABELAS NORMALIZADAS

table usuarios {
  id uuid [pk, default: `gen_random_uuid()`]
  name varchar(255) [not null]
  email varchar(255) [not null, unique]
  email_verified_at timestamp(0)
  password varchar(255) [not null]
  remember_token varchar(100)
  status_cadastro StatusCadastro [not null]
  linkedin_url varchar
  github_url varchar
  figma_url varchar
  foto_url varchar
  curriculo text
  area_atuacao_id uuid [ref: > areas_atuacao.id]
  cpf char(11) [unique, not null]
  rg varchar
  uf_rg char(2)
  orgao_emissor_rg varchar
  telefone varchar
  banco_id uuid [ref: > bancos.id]
  conta_bancaria varchar
  agencia varchar
  cep char(8)
  endereco varchar
  numero varchar
  complemento varchar
  bairro varchar
  cidade varchar
  uf char(2)
  created_at timestamp(0)
  updated_at timestamp(0)
  deleted_at timestamp(0)
}

table areas_atuacao {
  id uuid [pk, default: `gen_random_uuid()`]
  nome varchar(100) [not null, unique]
}

table bancos {
  id uuid [pk, default: `gen_random_uuid()`]
  codigo char(3) [not null, unique]
  nome varchar(100) [not null]
  ispb char(8)
}

table tecnologias {
  id uuid [pk, default: `gen_random_uuid()`]
  nome varchar(100) [not null, unique]
}

table usuario_tecnologia {
  usuario_id uuid [ref: > usuarios.id]
  tecnologia_id uuid [ref: > tecnologias.id]
  primary key (usuario_id, tecnologia_id)
}

// PROJETOS

table projetos {
  id uuid [pk, increment]
  nome varchar [not null]
  descricao text
  data_inicio date [not null]
  data_termino date
  cliente varchar [not null]
  slack_url varchar
  discord_url varchar
  board_url varchar
  git_url varchar
  tipo TipoProjeto [not null]
  created_at datetime
  updated_at datetime
  deleted_at timestamp(0)
}

table usuario_vinculo {
  id uuid [pk, default: `gen_random_uuid()`]
  projeto_id uuid [not null, ref: > projetos.id]
  usuario_id uuid [not null, ref: > usuarios.id]
  tipo_vinculo TipoVinculoProjeto [not null]
  funcao Funcao [not null]
  status StatusVinculoProjeto [not null]
  carga_horaria_semanal int [not null]
  data_inicio datetime [not null]
  data_fim datetime
  created_at datetime
  updated_at datetime
  deleted_at timestamp(0)

  indexes {
    (projeto_id, usuario_id, data_inicio)
    (usuario_id, status)
  }
}

table historico_usuario_vinculo {
  id uuid [pk, default: `gen_random_uuid()`]
  usuario_vinculo_id uuid [ref: > usuario_vinculo.id]
  status_anterior StatusVinculoProjeto
  status_novo StatusVinculoProjeto
  carga_horaria_semanal_anterior int
  carga_horaria_semanal_nova int
  data_alteracao timestamp(0)
}

// >>>>>>>>>>>>>>> Tabelas do LARAVEL
table sessions {
  id varchar(255) [pk]
  user_id uuid [ref: > usuarios.id]
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
