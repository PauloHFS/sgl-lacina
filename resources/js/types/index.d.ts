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

export const STATUS_FOLGA = ['PENDENTE', 'APROVADO', 'REJEITADO'] as const;
export type StatusFolga = (typeof STATUS_FOLGA)[number];

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

export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at?: string | null;
    password?: string;
    remember_token?: string | null;
    status_cadastro: StatusCadastro;
    campos_extras: Record<string, string>;
    curriculo_lattes_url?: string | null;
    linkedin_url?: string | null;
    github_url?: string | null;
    website_url?: string | null;
    area_atuacao?: string | null;
    tecnologias?: string | null;
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
    created_at?: string | null;
    updated_at?: string | null;
    deleted_at?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        isCoordenador: boolean;
        isColaborador: boolean;
        isVinculoProjetoPendente: boolean;
    };
};

export interface Banco {
    id: string;
    codigo: string;
    nome: string;
}

export interface Projeto {
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
    created_at?: string | null; // YYYY-MM-DD HH:MM:SS
    updated_at?: string | null; // YYYY-MM-DD HH:MM:SS
    deleted_at?: string | null; // YYYY-MM-DD HH:MM:SS
}

export interface Coordenador {
    id: string;
    name: string;
}

export interface UsuarioProjeto {
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
    data_inicio: string; // YYYY-MM-DD HH:MM:SS
    data_fim?: string | null; // YYYY-MM-DD HH:MM:SS
    created_at?: string | null; // YYYY-MM-DD HH:MM:SS
    updated_at?: string | null; // YYYY-MM-DD HH:MM:SS
    deleted_at?: string | null; // YYYY-MM-DD HH:MM:SS
}

export interface Sala {
    id: string;
    nome: string;
    descricao?: string | null;
    ativa: boolean;
    baias?: Baia[]; // precisa de load eagerly
    created_at?: string | null; // YYYY-MM-DD HH:MM:SS
    updated_at?: string | null; // YYYY-MM-DD HH:MM:SS
    deleted_at?: string | null; // YYYY-MM-DD HH:MM:SS
}

export interface Baia {
    id: string;
    nome: string;
    descricao?: string | null;
    ativa: boolean;
    sala_id: string;
    sala?: Sala; // precisa de load eagerly
    created_at?: string | null; // YYYY-MM-DD HH:MM:SS
    updated_at?: string | null; // YYYY-MM-DD HH:MM:SS
    deleted_at?: string | null; // YYYY-MM-DD HH:MM:SS
}

export interface Horario {
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
    created_at?: string | null; // YYYY-MM-DD HH:MM:SS
    updated_at?: string | null; // YYYY-MM-DD HH:MM:SS
    deleted_at?: string | null; // YYYY-MM-DD HH:MM:SS
}