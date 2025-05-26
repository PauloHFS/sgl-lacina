export type StatusCadastro = 'IMCOMPLETO' | 'PENDENTE' | 'ACEITO' | 'RECUSADO';

export type Funcao =
    | 'COORDENADOR'
    | 'PESQUISADOR'
    | 'DESENVOLVEDOR'
    | 'TECNICO'
    | 'ALUNO';

export type Genero = 'MASCULINO' | 'FEMININO' | 'OUTRO';

export type TipoVinculo = 'COORDENADOR' | 'COLABORADOR';

export type DiaDaSemana =
    | 'DOMINGO'
    | 'SEGUNDA'
    | 'TERCA'
    | 'QUARTA'
    | 'QUINTA'
    | 'SEXTA'
    | 'SABADO';

export type StatusFolga = 'PENDENTE' | 'APROVADO' | 'REJEITADO';

export type StatusVinculoProjeto =
    | 'APROVADO'
    | 'PENDENTE'
    | 'ENCERRADO'
    | 'RECUSADO';

export type TipoFolga = 'COLETIVA' | 'INDIVIDUAL';

export type TipoHorario = 'AULA' | 'TRABALHO' | 'AUSENTE';

export type TipoProjeto = 'PDI' | 'TCC' | 'MESTRADO' | 'DOUTORADO' | 'SUPORTE';

export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at?: string | null;
    password?: string;
    remember_token?: string | null;
    status_cadastro: StatusCadastro;
    curriculo_lattes_url?: string | null;
    linkedin_url?: string | null;
    github_url?: string | null;
    figma_url?: string | null;
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
    data_inicio: string; // date format: YYYY-MM-DD
    data_termino?: string | null; // date format: YYYY-MM-DD
    cliente: string;
    slack_url?: string | null;
    discord_url?: string | null;
    board_url?: string | null;
    git_url?: string | null;
    tipo: TipoProjeto;
    created_at?: string | null; // timestamp format: YYYY-MM-DD HH:MM:SS
    updated_at?: string | null; // timestamp format: YYYY-MM-DD HH:MM:SS
    deleted_at?: string | null; // timestamp format: YYYY-MM-DD HH:MM:SS
}

export interface Coordenador {
    id: string;
    name: string;
}

export interface UsuarioProjeto {
    id: string;
    usuario_id: string;
    usuario?: User;
    projeto_id: string;
    projeto?: Projeto;
    tipo_vinculo: TipoVinculo;
    funcao: Funcao;
    status: StatusVinculoProjeto;
    carga_horaria_semanal: number;
    data_inicio: string; // timestamp format: YYYY-MM-DD HH:MM:SS
    data_fim?: string | null; // timestamp format: YYYY-MM-DD HH:MM:SS
    created_at?: string | null; // timestamp format: YYYY-MM-DD HH:MM:SS
    updated_at?: string | null; // timestamp format: YYYY-MM-DD HH:MM:SS
    deleted_at?: string | null; // timestamp format: YYYY-MM-DD HH:MM:SS
}

export interface ShowPageProps extends PageProps {
    projeto: Projeto;
    tiposVinculo: TipoVinculo[];
    funcoes: Funcao[];
    usuarioVinculo: UsuarioProjeto | null;
    vinculosDoUsuarioLogadoNoProjeto: UsuarioProjeto[];
    participantesProjeto: {
        data: ParticipanteProjeto[];
        // Add other pagination properties if needed
    } | null;
    temVinculosPendentes: boolean;
    coordenadoresDoProjeto: Coordenador[];
}

export interface VinculoCreateForm {
    // ...existing fields...
}
