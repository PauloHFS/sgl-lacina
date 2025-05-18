import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Funcao, StatusCadastro, TipoProjeto, TipoVinculo } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useCallback } from 'react';
import { ColaboradorDetalhes } from './Partials/ColaboradorDetalhes';
import { ColaboradorHeader } from './Partials/ColaboradorHeader';
import { ColaboradorStatus } from './Partials/ColaboradorStatus';
import { InfoItem } from './Partials/InfoItem';

export interface ShowProps {
    colaborador: {
        id: string;
        name: string;
        email: string;
        linkedin_url?: string | null;
        github_url?: string | null;
        figma_url?: string | null;
        foto_url?: string | null;
        area_atuacao?: string | null;
        tecnologias?: string | null;
        curriculo?: string | null;
        cpf?: string | null;
        conta_bancaria?: string | null;
        agencia?: string | null;
        codigo_banco?: string | null;
        rg?: string | null;
        uf_rg?: string | null;
        telefone?: string | null;
        created_at: string;
        updated_at: string;
        status_cadastro:
            | 'VINCULO_PENDENTE'
            | 'APROVACAO_PENDENTE'
            | 'ATIVO'
            | 'INATIVO';
        vinculo?: {
            id: string;
            usuario_id: string;
            projeto_id: string;
            tipo_vinculo: TipoVinculo;
            funcao: Funcao;
            status: StatusCadastro;
            carga_horaria_semanal: number;
            data_inicio: string;
            data_fim?: string | null;
            created_at: string;
            updated_at: string;
            deleted_at: string | null;
            projeto: {
                id: string;
                nome: string;
                descricao: string;
                data_inicio: string;
                data_termino: string | null;
                cliente: string;
                slack_url: string | null;
                discord_url: string | null;
                board_url: string | null;
                git_url: string | null;
                tipo: TipoProjeto;
                created_at: string;
                updated_at: string;
                deleted_at: string | null;
            };
        } | null;
        projetos_atuais: Array<{
            id: string;
            nome: string;
            // descricao: string;
            // data_inicio: string;
            // data_termino: string | null;
            // cliente: string;
            // slack_url: string | null;
            // discord_url: string | null;
            // board_url: string | null;
            // git_url: string | null;
            // tipo: TipoProjeto;
            // created_at: string;
            // updated_at: string;
            // deleted_at: string | null;
        }>;
    };
}

export default function Show({ colaborador }: ShowProps) {
    const handleAceitarCadastro = useCallback(() => {
        router.post(route('colaboradores.aceitar', colaborador.id));
    }, [colaborador.id]);

    const handleRecusarCadastro = useCallback(() => {
        router.post(route('colaboradores.recusar', colaborador.id));
    }, [colaborador.id]);

    const handleAceitarVinculo = useCallback(() => {
        router.post(route('vinculos.aceitar', colaborador.id));
    }, [colaborador.id]);

    const handleRecusarVinculo = useCallback(() => {
        router.post(route('vinculos.recusar', colaborador.id));
    }, [colaborador.id]);

    return (
        <AuthenticatedLayout header="Detalhes do Colaborador">
            <Head title={`Colaborador: ${colaborador.name}`} />
            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="card card-bordered bg-base-100 shadow-xl">
                        <div className="card-body">
                            <ColaboradorHeader colaborador={colaborador} />

                            <div className="divider">Detalhes</div>
                            <ColaboradorDetalhes colaborador={colaborador} />

                            <div className="divider">Status</div>
                            <div className="my-2">
                                <ColaboradorStatus
                                    colaborador={colaborador}
                                    onAceitarCadastro={handleAceitarCadastro}
                                    onRecusarCadastro={handleRecusarCadastro}
                                    onAceitarVinculo={handleAceitarVinculo}
                                    onRecusarVinculo={handleRecusarVinculo}
                                />
                            </div>

                            <div className="divider">Projeto(s)</div>
                            <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                                {colaborador.projetos_atuais.length > 0 ? (
                                    colaborador.projetos_atuais.map(
                                        (projeto) => (
                                            <InfoItem
                                                key={projeto.id}
                                                label="Projeto"
                                                value={projeto.nome}
                                            >
                                                <div className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                                    {projeto.nome}
                                                </div>
                                            </InfoItem>
                                        ),
                                    )
                                ) : (
                                    <p className="text-base-content/70">
                                        Nenhum projeto atual.
                                    </p>
                                )}
                            </div>

                            <div className="divider">Vinculo</div>
                            <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                                {colaborador.vinculo ? (
                                    <>
                                        <InfoItem
                                            label="Projeto"
                                            value={
                                                colaborador.vinculo.projeto.nome
                                            }
                                        />
                                        <InfoItem
                                            label="Função"
                                            value={colaborador.vinculo.funcao}
                                        />
                                        <InfoItem
                                            label="Tipo de Vínculo"
                                            value={
                                                colaborador.vinculo.tipo_vinculo
                                            }
                                        />
                                        <InfoItem
                                            label="Carga Horária Semanal"
                                            value={`${colaborador.vinculo.carga_horaria_semanal} horas`}
                                        />
                                        <InfoItem
                                            label="Data de Início"
                                            value={
                                                colaborador.vinculo.data_inicio
                                            }
                                        />
                                    </>
                                ) : (
                                    <p className="text-base-content/70">
                                        Nenhum vínculo atual.
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
