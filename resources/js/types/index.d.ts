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
