import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Funcao,
    StatusVinculoProjeto,
    TipoProjeto,
    TipoVinculo,
} from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useMemo } from 'react';
import { ColaboradorDetalhes } from './Partials/ColaboradorDetalhes';
import { ColaboradorHeader } from './Partials/ColaboradorHeader';
import { ColaboradorStatus } from './Partials/ColaboradorStatus';
import { InfoItem } from './Partials/InfoItem';

export interface ShowProps {
    colaborador: {
        id: string;
        name: string;
        email: string;
        curriculo_lattes_url?: string | null;
        linkedin_url?: string | null;
        github_url?: string | null;
        figma_url?: string | null;
        foto_url?: string | null;
        area_atuacao?: string | null;
        tecnologias?: string | null;
        cpf?: string | null;
        banco?: {
            id: string;
            codigo: string;
            nome: string;
        } | null;
        conta_bancaria?: string | null;
        agencia?: string | null;
        rg?: string | null;
        uf_rg?: string | null;
        telefone?: string | null;
        created_at: string;
        updated_at: string;
        status_cadastro: StatusVinculoProjeto;
        vinculo?: {
            id: string;
            usuario_id: string;
            projeto_id: string;
            tipo_vinculo: TipoVinculo;
            funcao: Funcao;
            status: StatusVinculoProjeto;
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
    const { toast } = useToast();

    // TODO: Concertar essas rotas aqui:
    const handleAceitarCadastro = useCallback(() => {
        router.post(route('colaboradores.aceitar', colaborador.id));
    }, [colaborador.id]);

    const handleRecusarCadastro = useCallback(() => {
        router.post(route('colaboradores.recusar', colaborador.id));
    }, [colaborador.id]);

    const { data, setData, errors, put, processing } = useForm<{
        status?: StatusVinculoProjeto;
        funcao?: Funcao;
        tipo_vinculo?: TipoVinculo;
        carga_horaria_semanal?: number;
        data_inicio?: string;
    }>({
        status: colaborador.vinculo?.status,
        funcao: colaborador.vinculo?.funcao,
        tipo_vinculo: colaborador.vinculo?.tipo_vinculo,
        carga_horaria_semanal: colaborador.vinculo?.carga_horaria_semanal,
        data_inicio: colaborador.vinculo?.data_inicio
            ? colaborador.vinculo.data_inicio.substring(0, 10)
            : undefined,
    });

    const handleAtualizarStatusVinculo = useCallback(() => {
        if (!colaborador.vinculo) {
            toast('Vínculo não encontrado', 'error');
            return;
        }

        put(route('vinculos.update', colaborador.vinculo.id), {
            preserveScroll: true,
            onSuccess: () => {
                toast(`Status do vínculo atualizado.`, 'success');
            },
            onError: (err: Record<string, string>) => {
                toast('Erro ao atualizar status do vínculo.', 'error');
                console.error('Erro ao atualizar vínculo:', err);
            },
        });
    }, [colaborador.vinculo, put, toast]);

    const handleAceitarVinculo = useCallback(() => {
        setData('status', 'APROVADO');
    }, [setData]);

    const handleRecusarVinculo = useCallback(() => {
        setData('status', 'RECUSADO');
    }, [setData]);

    useEffect(() => {
        if (data.status === 'APROVADO' || data.status === 'RECUSADO') {
            handleAtualizarStatusVinculo();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.status]);

    const originalVinculoDisplayValues = useMemo(() => {
        return {
            funcao: colaborador.vinculo?.funcao,
            tipo_vinculo: colaborador.vinculo?.tipo_vinculo,
            carga_horaria_semanal: colaborador.vinculo?.carga_horaria_semanal,
            data_inicio: colaborador.vinculo?.data_inicio
                ? colaborador.vinculo.data_inicio.substring(0, 10)
                : undefined,
        };
    }, [colaborador.vinculo]);

    const areVinculoFieldsDirty =
        data.funcao !== originalVinculoDisplayValues.funcao ||
        data.tipo_vinculo !== originalVinculoDisplayValues.tipo_vinculo ||
        data.carga_horaria_semanal !==
            originalVinculoDisplayValues.carga_horaria_semanal ||
        data.data_inicio !== originalVinculoDisplayValues.data_inicio;

    const handleResetVinculoFields = useCallback(() => {
        setData({
            funcao: originalVinculoDisplayValues.funcao,
            tipo_vinculo: originalVinculoDisplayValues.tipo_vinculo,
            carga_horaria_semanal:
                originalVinculoDisplayValues.carga_horaria_semanal,
            data_inicio: originalVinculoDisplayValues.data_inicio,
        });
    }, [originalVinculoDisplayValues, setData]);

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

                            <div className="divider">Projeto(s)</div>
                            <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                                {colaborador.projetos_atuais.length > 0 ? (
                                    colaborador.projetos_atuais.map(
                                        (projeto) => (
                                            <InfoItem
                                                key={projeto.id}
                                                label="Projeto"
                                            >
                                                <Link
                                                    href={route(
                                                        'projetos.show',
                                                        projeto.id,
                                                    )}
                                                    className="input input-bordered hover:bg-base-200 flex h-auto min-h-10 items-center py-2 break-words whitespace-normal"
                                                >
                                                    {projeto.nome}
                                                </Link>
                                            </InfoItem>
                                        ),
                                    )
                                ) : (
                                    <p className="text-base-content/70">
                                        Nenhum projeto.
                                    </p>
                                )}
                            </div>

                            <div className="divider">
                                Solicitação de Vinculo
                            </div>
                            <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                                {colaborador.vinculo ? (
                                    <>
                                        <InfoItem
                                            label="Projeto"
                                            value={
                                                colaborador.vinculo.projeto.nome
                                            }
                                        />

                                        <div>
                                            <label
                                                className="label"
                                                htmlFor="funcao"
                                            >
                                                <span className="label-text font-semibold">
                                                    Função:
                                                </span>
                                            </label>
                                            <select
                                                id="funcao"
                                                className={`select select-bordered w-full ${
                                                    colaborador.vinculo
                                                        ?.funcao !==
                                                        data.funcao &&
                                                    data.funcao !== undefined
                                                        ? 'select-warning'
                                                        : ''
                                                } ${errors.funcao ? 'select-error' : ''}`}
                                                value={data.funcao || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'funcao',
                                                        e.target
                                                            .value as Funcao,
                                                    )
                                                }
                                                disabled={processing}
                                            >
                                                {(
                                                    [
                                                        'COORDENADOR',
                                                        'PESQUISADOR',
                                                        'DESENVOLVEDOR',
                                                        'TECNICO',
                                                        'ALUNO',
                                                    ] as Array<Funcao>
                                                ).map((funcao) => (
                                                    <option
                                                        key={funcao}
                                                        value={funcao}
                                                    >
                                                        {funcao}
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.funcao && (
                                                <p className="text-error mt-1 text-xs">
                                                    {errors.funcao}
                                                </p>
                                            )}
                                        </div>

                                        <div>
                                            <label
                                                className="label"
                                                htmlFor="tipovinculo"
                                            >
                                                <span className="label-text font-semibold">
                                                    Tipo de Vínculo:
                                                </span>
                                            </label>
                                            <select
                                                id="tipovinculo"
                                                className={`select select-bordered w-full ${
                                                    colaborador.vinculo
                                                        ?.tipo_vinculo !==
                                                        data.tipo_vinculo &&
                                                    data.tipo_vinculo !==
                                                        undefined
                                                        ? 'select-warning'
                                                        : ''
                                                } ${errors.tipo_vinculo ? 'select-error' : ''}`}
                                                value={data.tipo_vinculo || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'tipo_vinculo',
                                                        e.target
                                                            .value as TipoVinculo,
                                                    )
                                                }
                                                disabled={processing}
                                            >
                                                {(
                                                    [
                                                        'COLABORADOR',
                                                        'COORDENADOR',
                                                    ] as Array<TipoVinculo>
                                                ).map((tipo) => (
                                                    <option
                                                        key={tipo}
                                                        value={tipo}
                                                    >
                                                        {tipo}
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.tipo_vinculo && (
                                                <p className="text-error mt-1 text-xs">
                                                    {errors.tipo_vinculo}
                                                </p>
                                            )}
                                        </div>

                                        <div>
                                            <label
                                                className="label"
                                                htmlFor="cargahoraria"
                                            >
                                                <span className="label-text font-semibold">
                                                    Carga Horária Semanal
                                                    (horas):
                                                </span>
                                            </label>
                                            <input
                                                id="cargahoraria"
                                                type="number"
                                                className={`input input-bordered w-full ${
                                                    colaborador.vinculo
                                                        ?.carga_horaria_semanal !==
                                                        data.carga_horaria_semanal &&
                                                    data.carga_horaria_semanal !==
                                                        undefined
                                                        ? 'input-warning'
                                                        : ''
                                                } ${errors.carga_horaria_semanal ? 'input-error' : ''}`}
                                                value={
                                                    data.carga_horaria_semanal ===
                                                    undefined
                                                        ? ''
                                                        : data.carga_horaria_semanal
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'carga_horaria_semanal',
                                                        e.target.value === ''
                                                            ? undefined
                                                            : parseInt(
                                                                  e.target
                                                                      .value,
                                                                  10,
                                                              ),
                                                    )
                                                }
                                                disabled={processing}
                                            />
                                            {errors.carga_horaria_semanal && (
                                                <p className="text-error mt-1 text-xs">
                                                    {
                                                        errors.carga_horaria_semanal
                                                    }
                                                </p>
                                            )}
                                        </div>

                                        <div>
                                            <label
                                                className="label"
                                                htmlFor="datainicio"
                                            >
                                                <span className="label-text font-semibold">
                                                    Data de Início:
                                                </span>
                                            </label>
                                            <input
                                                id="datainicio"
                                                type="date"
                                                className={`input input-bordered w-full ${
                                                    colaborador.vinculo?.data_inicio?.substring(
                                                        0,
                                                        10,
                                                    ) !== data.data_inicio &&
                                                    data.data_inicio !==
                                                        undefined
                                                        ? 'input-warning'
                                                        : ''
                                                } ${errors.data_inicio ? 'input-error' : ''}`}
                                                value={data.data_inicio || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'data_inicio',
                                                        e.target.value,
                                                    )
                                                }
                                                disabled={processing}
                                            />
                                            {errors.data_inicio && (
                                                <p className="text-error mt-1 text-xs">
                                                    {errors.data_inicio}
                                                </p>
                                            )}
                                        </div>

                                        <div className="mt-4 flex justify-end space-x-2 md:col-span-2">
                                            {areVinculoFieldsDirty && (
                                                <button
                                                    type="button"
                                                    className="btn btn-outline btn-sm"
                                                    onClick={
                                                        handleResetVinculoFields
                                                    }
                                                    disabled={processing}
                                                >
                                                    Resetar
                                                </button>
                                            )}
                                        </div>
                                    </>
                                ) : (
                                    <p className="text-base-content/70">
                                        Nenhuma solicitação de vínculo
                                        encontrada.
                                    </p>
                                )}
                            </div>

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
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
