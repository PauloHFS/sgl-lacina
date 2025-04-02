export interface Colaborador {
    id: number;
    linkedin?: string;
    github?: string;
    figma?: string;
    foto?: string;
    curriculo?: string;
    area_atuacao?: string;
    tecnologias?: string;
    cpf?: string;
    conta_bancaria?: string;
    agencia?: string;
    codigo_banco?: string;
    rg?: string;
    uf_rg?: string;
    telefone?: string;
    created_at?: string;
    updated_at?: string;
}

export interface Docente {
    id: number;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    colaborador?: Colaborador;
    docente?: Docente;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
