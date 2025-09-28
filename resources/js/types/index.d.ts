export const STATUS_CADASTRO = ['IMCOMPLETO', 'PENDENTE', 'ACEITO', 'RECUSADO'] as const;
export type StatusCadastro = (typeof STATUS_CADASTRO)[number];

export const FUNCOES = [
    'COORDENADOR',
    'PESQUISADOR',
    'DESENVOLVEDOR',
    'TECNICO',
    'ALUNO',
] as const;
export type Funcao = (typeof FUNCOES)[number];

export const GENEROS = ['MASCULINO', 'FEMININO', 'OUTRO'] as const;
export type Genero = (typeof GENEROS)[number];

export const TIPOS_VINCULO = [
    'COORDENADOR',
    'COLABORADOR'
] as const;
export type TipoVinculo = (typeof TIPOS_VINCULO)[number];

export const DIA_DA_SEMANA = [
    'DOMINGO',
    'SEGUNDA',
    'TERCA',
    'QUARTA',
    'QUINTA',
    'SEXTA',
    'SABADO',
] as const;
export type DiaDaSemana = (typeof DIA_DA_SEMANA)[number];

export const STATUS_AUSENCIA = ['PENDENTE', 'APROVADO', 'REJEITADO'] as const;
export type StatusAusencia = (typeof STATUS_AUSENCIA)[number];

export const STATUS_VINCULO_PROJETO = [
    'APROVADO',
    'PENDENTE',
    'ENCERRADO',
    'RECUSADO',
] as const;
export type StatusVinculoProjeto = (typeof STATUS_VINCULO_PROJETO)[number];

export const TIPOS_FOLGA = ['COLETIVA', 'INDIVIDUAL'] as const;
export type TipoFolga = (typeof TIPOS_FOLGA)[number];

export const TIPOS_HORARIO = [
    'EM_AULA',
    'TRABALHO_PRESENCIAL',
    'TRABALHO_REMOTO',
    'AUSENTE',
] as const;
export type TipoHorario = (typeof TIPOS_HORARIO)[number];

export const TIPOS_PROJETO = [
    'PDI',
    'TCC',
    'MESTRADO',
    'DOUTORADO',
    'SUPORTE',
] as const;
export type TipoProjeto = (typeof TIPOS_PROJETO)[number];

export interface Timestamps {
    created_at?: string | null;
    updated_at?: string | null;
}

export interface SoftDeletes extends Timestamps {
    deleted_at?: string | null;
}

export const ROLES = ['coordenador_master', 'coordenador', 'colaborador'] as const;
export type Role = (typeof ROLES)[number];

export interface User extends SoftDeletes {
    id: string;
    name: string;
    email: string;
    role: Role;
    email_verified_at?: string | null;
    password?: string;
    remember_token?: string | null;
    status_cadastro: StatusCadastro;
    campos_extras: Record<string, string>;
    curriculo_lattes_url?: string | null;
    linkedin_url?: string | null;
    github_url?: string | null;
    website_url?: string | null;
    area_atuacao?: string[] | null;
    tecnologias?: string[] | null;
    cpf?: string | null;
    rg?: string | null;
    uf_rg?: string | null;
    orgao_emissor_rg?: string | null;
    telefone?: string | null;
    banco_id?: string | null;
    conta_bancaria?: string | null;
    agencia?: string | null;
    foto_url?: string | null;
    genero?: Genero | null;
    data_nascimento?: string | null;
    cep?: string | null;
    endereco?: string | null;
    numero?: string | null;
    complemento?: string | null;
    bairro?: string | null;
    cidade?: string | null;
    uf?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        isVinculoProjetoPendente: boolean;
        isCoordenador: boolean;
    };
};

export interface Banco {
    id: string;
    codigo: string;
    nome: string;
}

export interface Projeto extends SoftDeletes {
    id: string;
    nome: string;
    descricao?: string | null;
    valor_total?: number;
    meses_execucao?: number;
    campos_extras?: Record<string, string>;
    data_inicio: string; // YYYY-MM-DD
    data_termino?: string | null; // YYYY-MM-DD
    cliente: string;
    slack_url?: string | null;
    discord_url?: string | null;
    board_url?: string | null;
    git_url?: string | null;
    tipo: TipoProjeto;
    numero_convenio?: string | null;
    interveniente_financeiro_id?: string | null; // precisa de load eagerly
    interveniente_financeiro?: IntervenienteFinanceiro | null; // precisa de load eagerly 
}

export interface Coordenador {
    id: string;
    name: string;
    foto_url?: string | null;
}

export interface UsuarioProjeto extends SoftDeletes {
    id: string;
    usuario_id: string;
    usuario?: User; // precisa de load eagerly
    projeto_id: string;
    projeto?: Projeto; // precisa de load eagerly
    trocar?: boolean;
    tipo_vinculo: TipoVinculo;
    funcao: Funcao;
    status: StatusVinculoProjeto;
    carga_horaria: number;
    valor_bolsa: number;
    data_inicio: string; // YYYY-MM-DD HH:MM:SS
    data_fim?: string | null; // YYYY-MM-DD HH:MM:SS
}

export interface Sala extends SoftDeletes {
    id: string;
    nome: string;
    descricao?: string | null;
    ativa: boolean;
    baias?: Baia[]; // precisa de load eagerly
}

export interface Baia extends SoftDeletes {
    id: string;
    nome: string;
    descricao?: string | null;
    ativa: boolean;
    sala_id: string;
    sala?: Sala; // precisa de load eagerly
}

export interface Horario extends SoftDeletes {
    id: string;
    horario: number; // 0 - 23
    dia_da_semana: DiaDaSemana;
    tipo: TipoHorario;
    usuario_id: string;
    usuario?: User; // precisa de load eagerly
    usuario_projeto_id?: string | null;
    usuario_projeto?: UsuarioProjeto | null; // precisa de load eagerly
    baia_id?: string | null;
    baia?: Baia | null; // precisa de load eagerly
}

export interface ProjetoAtivo {
    id: string; // ID do vínculo usuario_projeto
    projeto_id: string;
    projeto_nome: string;
    carga_horaria: number; // Carga horária semanal do projeto
}

export interface SalaDisponivel {
    id: string;
    nome: string;
    baias: BaiaDisponivel[];
}

export interface BaiaDisponivel {
    id: string;
    nome: string;
    updated_at: string;
}

export interface PessoaHorario {
    id: string;
    name: string;
    email: string;
    foto_url?: string;
    baia?: {
        id: string;
        nome: string;
    } | null;
    projeto?: {
        id: string;
        nome: string;
    } | null;
}

export interface HorarioSlot {
    count: number;
    pessoas: PessoaHorario[];
}

export interface HorariosSala {
    [dia: string]: {
        [hora: number]: HorarioSlot;
    };
}
export interface IntervenienteFinanceiro extends Timestamps {
    id: string;
    nome: string;
}

export interface CompensacaoHorario {
    data: string;
    horario: number[];
}

export interface Ausencia extends SoftDeletes {
    id: string;
    usuario_id: string;
    usuario?: User; // precisa de load eagerly
    projeto_id: string;
    projeto?: Projeto; // precisa de load eagerly
    titulo: string;
    data_inicio: string; // YYYY-MM-DD
    data_fim: string; // YYYY-MM-DD
    justificativa: string;
    status: StatusAusencia;
    horas_a_compensar: number;
    compensacao_data_inicio: string; // YYYY-MM-DD
    compensacao_data_fim: string; // YYYY-MM-DD
    compensacao_horarios: CompensacaoHorario[];
}

export interface DailyReport {
    id: string;
    data: string;
    horas_trabalhadas: number;
    o_que_fez_ontem: string | null;
    o_que_vai_fazer_hoje: string | null;
    observacoes: string | null;
    usuario_id: string;
    projeto_id: string;
    created_at: string;
    updated_at: string;
    usuario?: User; // precisa de load eagerly
    projeto?: Projeto; // precisa de load eagerly
}
