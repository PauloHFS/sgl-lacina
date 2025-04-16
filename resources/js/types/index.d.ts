export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;

    linkedin_url?: string;
    github_url?: string;
    figma_url?: string;
    foto_url?: string;
    curriculo?: string;
    area_atuacao?: string;
    tecnologias?: string;

    cpf?: string;
    rg?: string;
    uf_rg?: string;
    orgao_emissor_rg?: string;
    conta_bancaria?: string;
    agencia?: string;
    codigo_banco?: string;

    created_at?: string;
    updated_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
