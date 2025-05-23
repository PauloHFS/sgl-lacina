export interface User {
    id: number | string;
    name: string;
    email: string;
    email_verified_at?: string;
    foto_url?: string;
    genero?: string;
    data_nascimento?: string;
    cpf?: string;
    rg?: string;
    uf_rg?: string;
    orgao_emissor_rg?: string;
    cep?: string;
    endereco?: string;
    numero?: string;
    complemento?: string;
    bairro?: string;
    cidade?: string;
    estado?: string;
    telefone?: string;
    conta_bancaria?: string;
    agencia?: string;
    banco_id?: string;
    curriculo?: string;
    linkedin_url?: string;
    github_url?: string;
    figma_url?: string;
    area_atuacao?: string;
    tecnologias?: string;
    status_cadastro?: string;
    created_at?: string;
    updated_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        isCoordenador: boolean;
        isColaborador: boolean;
    };
};

// Enums do Laravel

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

// export type StatusSolicitacaoTrocaProjeto = 'ACEITO' | 'REJEITADO' | 'PENDENTE';

export type StatusVinculoProjeto =
    | 'APROVADO'
    | 'PENDENTE'
    | 'ENCERRADO'
    | 'RECUSADO';

export type TipoFolga = 'COLETIVA' | 'INDIVIDUAL';

export type TipoHorario = 'AULA' | 'TRABALHO' | 'AUSENTE';

export type TipoProjeto = 'PDI' | 'TCC' | 'MESTRADO' | 'DOUTORADO' | 'SUPORTE';

// Added Projeto and UsuarioProjeto interfaces
export interface Projeto {
    id: string;
    nome: string;
    descricao?: string | null;
    data_inicio: string;
    data_termino?: string | null;
    cliente: string;
    slack_url?: string | null;
    discord_url?: string | null;
    board_url?: string | null;
    git_url?: string | null;
    tipo: TipoProjeto;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
}

export interface UsuarioProjeto {
    id: string;
    usuario_id: string;
    projeto_id: string;
    projeto_antigo_id?: string | null;
    tipo_vinculo: TipoVinculo;
    funcao: Funcao;
    status: StatusVinculoProjeto;
    carga_horaria_semanal: number;
    data_inicio: string;
    data_fim?: string | null;
    descricao_atividades?: string | null;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string | null;
}
