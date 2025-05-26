import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Funcao,
    PageProps,
    StatusVinculoProjeto,
    TipoProjeto,
    TipoVinculo,
} from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { ColaboradorDetalhes } from './Partials/ColaboradorDetalhes';
import { ColaboradorHeader } from './Partials/ColaboradorHeader';
import { ColaboradorStatus } from './Partials/ColaboradorStatus';
import { InfoItem } from './Partials/InfoItem';

// Define a more specific type for the colaborador within ShowProps
export interface ColaboradorData {
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
    banco_id?: string | null;
    banco?: {
        id: string;
        codigo: string;
        nome: string;
    } | null;
    conta_bancaria?: string | null;
    agencia?: string | null;
    rg?: string | null;
    uf_rg?: string | null;
    orgao_emissor_rg?: string | null;
    telefone?: string | null;
    genero?: string | null;
    data_nascimento?: string | null; // Format YYYY-MM-DD
    cep?: string | null;
    endereco?: string | null;
    numero?: string | null;
    complemento?: string | null;
    bairro?: string | null;
    cidade?: string | null;
    uf?: string | null;
    created_at: string;
    updated_at: string;
    status_cadastro:
        | 'VINCULO_PENDENTE'
        | 'APROVACAO_PENDENTE'
        | 'ATIVO'
        | 'ENCERRADO';
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
    }>;
}

export interface ShowPageProps extends PageProps {
    // Renamed to ShowPageProps for clarity
    colaborador: ColaboradorData;
    bancos: Array<{ id: string; nome: string; codigo: string }>;
    ufs: Array<string>;
    generos: Array<{ value: string; label: string }>;
    can_update_colaborador: boolean;
}

export default function Show(props: ShowPageProps) {
    // Updated to use renamed type
    const {
        colaborador,
        bancos,
        ufs,
        generos,
        can_update_colaborador,
        // flash
    } = props;

    console.log(`${new Date().toISOString()} - [Colaboradores/show]`, {
        props,
    });

    const { toast } = useToast();

    // useEffect(() => {
    //     if (flash?.success) {
    //         toast(flash.success, 'success');
    //     }
    //     if (flash?.error) {
    //         toast(flash.error, 'error');
    //     }
    // }, [flash, toast]);

    const [isEditingDetalhes, setIsEditingDetalhes] = useState(false);

    // Form for Vinculo (existing)
    const {
        data: vinculoData,
        setData: setVinculoData,
        errors: vinculoErrors,
        put: putVinculo,
        processing: processingVinculo,
        // reset: resetVinculoForm,
    } = useForm<{
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

    // Form for ColaboradorDetalhes
    const {
        data: detalhesData,
        setData: setDetalhesData,
        errors: detalhesErrors,
        put: putDetalhes,
        processing: processingDetalhes,
        reset: resetDetalhesForm,
        clearErrors: clearDetalhesErrors,
    } = useForm<ColaboradorData>({
        id: colaborador.id, // Added
        email: colaborador.email, // Added
        status_cadastro: colaborador.status_cadastro, // Added
        projetos_atuais: colaborador.projetos_atuais || [], // Added
        created_at: colaborador.created_at, // Added
        updated_at: colaborador.updated_at, // Added
        name: colaborador.name,
        curriculo_lattes_url: colaborador.curriculo_lattes_url,
        linkedin_url: colaborador.linkedin_url,
        github_url: colaborador.github_url,
        figma_url: colaborador.figma_url,
        area_atuacao: colaborador.area_atuacao,
        tecnologias: colaborador.tecnologias,
        cpf: colaborador.cpf,
        banco_id: colaborador.banco_id,
        conta_bancaria: colaborador.conta_bancaria,
        agencia: colaborador.agencia,
        rg: colaborador.rg,
        uf_rg: colaborador.uf_rg,
        orgao_emissor_rg: colaborador.orgao_emissor_rg,
        telefone: colaborador.telefone,
        genero: colaborador.genero,
        data_nascimento: colaborador.data_nascimento
            ? colaborador.data_nascimento.substring(0, 10)
            : undefined,
        cep: colaborador.cep,
        endereco: colaborador.endereco,
        numero: colaborador.numero,
        complemento: colaborador.complemento,
        bairro: colaborador.bairro,
        cidade: colaborador.cidade,
        uf: colaborador.uf,
    });

    const handleAceitarCadastro = useCallback(() => {
        router.post(
            route('colaboradores.aceitar', colaborador.id),
            {},
            {
                onSuccess: () =>
                    toast('Cadastro aceito com sucesso.', 'success'),
                onError: () => toast('Erro ao aceitar cadastro.', 'error'),
            },
        );
    }, [colaborador.id, toast]);

    const handleRecusarCadastro = useCallback(() => {
        router.post(
            route('colaboradores.recusar', colaborador.id),
            {},
            {
                onSuccess: () =>
                    toast('Cadastro recusado com sucesso.', 'success'),
                onError: () => toast('Erro ao recusar cadastro.', 'error'),
            },
        );
    }, [colaborador.id, toast]);

    const handleAtualizarStatusVinculo = useCallback(() => {
        if (!colaborador.vinculo) {
            toast('Vínculo não encontrado', 'error');
            return;
        }
        const dataToUpdate = {
            status: vinculoData.status,
            funcao: vinculoData.funcao,
            tipo_vinculo: vinculoData.tipo_vinculo,
            carga_horaria_semanal: vinculoData.carga_horaria_semanal,
            data_inicio: vinculoData.data_inicio,
        };

        putVinculo(route('vinculos.update', colaborador.vinculo.id), {
            data: dataToUpdate,
            preserveScroll: true,
            onSuccess: () => {
                toast('Status do vínculo atualizado.', 'success');
            },
            onError: (err: Record<string, string>) => {
                toast('Erro ao atualizar status do vínculo.', 'error');
                console.error('Erro ao atualizar vínculo:', err);
            },
        });
    }, [colaborador.vinculo, putVinculo, vinculoData, toast]);

    const handleAceitarVinculo = useCallback(() => {
        setVinculoData('status', 'APROVADO');
    }, [setVinculoData]);

    const handleRecusarVinculo = useCallback(() => {
        setVinculoData('status', 'RECUSADO');
    }, [setVinculoData]);

    useEffect(() => {
        if (
            vinculoData.status === 'APROVADO' ||
            vinculoData.status === 'RECUSADO'
        ) {
            if (
                colaborador.vinculo &&
                vinculoData.status !== colaborador.vinculo.status
            ) {
                handleAtualizarStatusVinculo();
            }
        }
    }, [vinculoData.status, colaborador.vinculo, handleAtualizarStatusVinculo]);

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

    const areVinculoFieldsDirty = useMemo(
        () =>
            vinculoData.funcao !== originalVinculoDisplayValues.funcao ||
            vinculoData.tipo_vinculo !==
                originalVinculoDisplayValues.tipo_vinculo ||
            vinculoData.carga_horaria_semanal !==
                originalVinculoDisplayValues.carga_horaria_semanal ||
            vinculoData.data_inicio !==
                originalVinculoDisplayValues.data_inicio,
        [vinculoData, originalVinculoDisplayValues],
    );

    const handleUpdateDetalhesColaborador = () => {
        putDetalhes(route('colaboradores.update', colaborador.id), {
            preserveScroll: true,
            onSuccess: () => {
                toast(
                    'Detalhes do colaborador atualizados com sucesso.',
                    'success',
                );
                setIsEditingDetalhes(false);
            },
            onError: (err: Record<string, string>) => {
                toast('Erro ao atualizar detalhes do colaborador.', 'error');
                console.error('Erro ao atualizar detalhes:', err);
            },
        });
    };

    const handleCancelEditDetalhes = () => {
        resetDetalhesForm();
        clearDetalhesErrors();
        setIsEditingDetalhes(false);
    };

    const handleResetVinculoFields = useCallback(() => {
        setVinculoData({
            status: colaborador.vinculo?.status,
            funcao: originalVinculoDisplayValues.funcao,
            tipo_vinculo: originalVinculoDisplayValues.tipo_vinculo,
            carga_horaria_semanal:
                originalVinculoDisplayValues.carga_horaria_semanal,
            data_inicio: originalVinculoDisplayValues.data_inicio,
        });
    }, [setVinculoData, colaborador.vinculo, originalVinculoDisplayValues]);

    return (
        <AuthenticatedLayout header="Detalhes do Colaborador">
            <Head title={`Colaborador: ${colaborador.name}`} />

            {/* Toast Messages using daisyUI alert component
            {flash?.success && (
                <div className="toast toast-top toast-center">
                    <div role="alert" className="alert alert-success">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-6 w-6 shrink-0 stroke-current"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>{flash.success}</span>
                    </div>
                </div>
            )}
            {flash?.error && (
                <div className="toast toast-top toast-center">
                    <div role="alert" className="alert alert-error">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-6 w-6 shrink-0 stroke-current"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2 2m2-2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>{flash.error}</span>
                    </div>
                </div>
            ) */}

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="card card-bordered bg-base-100 shadow-xl">
                        <div className="card-body">
                            <ColaboradorHeader colaborador={colaborador} />

                            <div className="divider">Detalhes</div>
                            {can_update_colaborador && !isEditingDetalhes && (
                                <div className="card-actions mb-4 justify-end">
                                    <button
                                        className="btn btn-sm btn-outline btn-primary"
                                        onClick={() =>
                                            setIsEditingDetalhes(true)
                                        }
                                        disabled={
                                            processingDetalhes ||
                                            processingVinculo
                                        }
                                    >
                                        Editar Detalhes
                                    </button>
                                </div>
                            )}
                            <ColaboradorDetalhes
                                colaborador={colaborador}
                                isEditing={isEditingDetalhes}
                                data={detalhesData}
                                setData={setDetalhesData}
                                errors={detalhesErrors}
                                processing={processingDetalhes}
                                onCancel={handleCancelEditDetalhes}
                                onSubmit={handleUpdateDetalhesColaborador}
                                bancos={bancos}
                                ufs={ufs}
                                generos={generos}
                                canEdit={can_update_colaborador}
                            />

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
                                Status do Cadastro e Vínculo
                            </div>
                            <ColaboradorStatus
                                colaborador={colaborador}
                                onAceitarCadastro={handleAceitarCadastro}
                                onRecusarCadastro={handleRecusarCadastro}
                                onAceitarVinculo={handleAceitarVinculo}
                                onRecusarVinculo={handleRecusarVinculo}
                                processing={
                                    processingVinculo || processingDetalhes
                                }
                            />

                            {colaborador.vinculo &&
                                colaborador.status_cadastro === 'ATIVO' && (
                                    <>
                                        <div className="divider">
                                            Detalhes do Vínculo com o Projeto
                                        </div>
                                        <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                                            <InfoItem
                                                label="Projeto"
                                                value={
                                                    colaborador.vinculo.projeto
                                                        .nome
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
                                                            vinculoData.funcao &&
                                                        vinculoData.funcao !==
                                                            undefined
                                                            ? 'select-warning'
                                                            : ''
                                                    } ${vinculoErrors.funcao ? 'select-error' : ''}`}
                                                    value={
                                                        vinculoData.funcao || ''
                                                    }
                                                    onChange={(e) =>
                                                        setVinculoData(
                                                            'funcao',
                                                            e.target
                                                                .value as Funcao,
                                                        )
                                                    }
                                                    disabled={
                                                        processingVinculo ||
                                                        processingDetalhes ||
                                                        colaborador.vinculo
                                                            ?.status !==
                                                            'PENDENTE'
                                                    }
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
                                                {vinculoErrors.funcao && (
                                                    <p className="text-error mt-1 text-xs">
                                                        {vinculoErrors.funcao}
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
                                                            vinculoData.tipo_vinculo &&
                                                        vinculoData.tipo_vinculo !==
                                                            undefined
                                                            ? 'select-warning'
                                                            : ''
                                                    } ${vinculoErrors.tipo_vinculo ? 'select-error' : ''}`}
                                                    value={
                                                        vinculoData.tipo_vinculo ||
                                                        ''
                                                    }
                                                    onChange={(e) =>
                                                        setVinculoData(
                                                            'tipo_vinculo',
                                                            e.target
                                                                .value as TipoVinculo,
                                                        )
                                                    }
                                                    disabled={
                                                        processingVinculo ||
                                                        processingDetalhes ||
                                                        colaborador.vinculo
                                                            ?.status !==
                                                            'PENDENTE'
                                                    }
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
                                                            {tipo
                                                                .replace(
                                                                    /_/g,
                                                                    ' ',
                                                                )
                                                                .toLowerCase()
                                                                .replace(
                                                                    /\b\w/g,
                                                                    (char) =>
                                                                        char.toUpperCase(),
                                                                )}
                                                        </option>
                                                    ))}
                                                </select>
                                                {vinculoErrors.tipo_vinculo && (
                                                    <p className="text-error mt-1 text-xs">
                                                        {
                                                            vinculoErrors.tipo_vinculo
                                                        }
                                                    </p>
                                                )}
                                            </div>

                                            <div>
                                                <label
                                                    className="label"
                                                    htmlFor="carga_horaria_semanal"
                                                >
                                                    <span className="label-text font-semibold">
                                                        Carga Horária Semanal:
                                                    </span>
                                                </label>
                                                <input
                                                    id="carga_horaria_semanal"
                                                    type="number"
                                                    className={`input input-bordered w-full ${
                                                        colaborador.vinculo
                                                            ?.carga_horaria_semanal !==
                                                            vinculoData.carga_horaria_semanal &&
                                                        vinculoData.carga_horaria_semanal !==
                                                            undefined
                                                            ? 'input-warning'
                                                            : ''
                                                    } ${vinculoErrors.carga_horaria_semanal ? 'input-error' : ''}`}
                                                    value={
                                                        vinculoData.carga_horaria_semanal ||
                                                        ''
                                                    }
                                                    onChange={(e) =>
                                                        setVinculoData(
                                                            'carga_horaria_semanal',
                                                            parseInt(
                                                                e.target.value,
                                                                10,
                                                            ),
                                                        )
                                                    }
                                                    disabled={
                                                        processingVinculo ||
                                                        processingDetalhes ||
                                                        colaborador.vinculo
                                                            ?.status !==
                                                            'PENDENTE'
                                                    }
                                                />
                                                {vinculoErrors.carga_horaria_semanal && (
                                                    <p className="text-error mt-1 text-xs">
                                                        {
                                                            vinculoErrors.carga_horaria_semanal
                                                        }
                                                    </p>
                                                )}
                                            </div>

                                            <div>
                                                <label
                                                    className="label"
                                                    htmlFor="data_inicio"
                                                >
                                                    <span className="label-text font-semibold">
                                                        Data de Início:
                                                    </span>
                                                </label>
                                                <input
                                                    id="data_inicio"
                                                    type="date"
                                                    className={`input input-bordered w-full ${
                                                        colaborador.vinculo?.data_inicio?.substring(
                                                            0,
                                                            10,
                                                        ) !==
                                                            vinculoData.data_inicio &&
                                                        vinculoData.data_inicio !==
                                                            undefined
                                                            ? 'input-warning'
                                                            : ''
                                                    } ${vinculoErrors.data_inicio ? 'input-error' : ''}`}
                                                    value={
                                                        vinculoData.data_inicio ||
                                                        ''
                                                    }
                                                    onChange={(e) =>
                                                        setVinculoData(
                                                            'data_inicio',
                                                            e.target.value,
                                                        )
                                                    }
                                                    disabled={
                                                        processingVinculo ||
                                                        processingDetalhes ||
                                                        colaborador.vinculo
                                                            ?.status !==
                                                            'PENDENTE'
                                                    }
                                                />
                                                {vinculoErrors.data_inicio && (
                                                    <p className="text-error mt-1 text-xs">
                                                        {
                                                            vinculoErrors.data_inicio
                                                        }
                                                    </p>
                                                )}
                                            </div>

                                            {colaborador.vinculo?.status ===
                                                'PENDENTE' &&
                                                areVinculoFieldsDirty && (
                                                    <div className="mt-4 flex justify-end space-x-3 md:col-span-2">
                                                        <button
                                                            type="button"
                                                            onClick={
                                                                handleResetVinculoFields
                                                            }
                                                            className="btn btn-ghost"
                                                            disabled={
                                                                processingVinculo ||
                                                                processingDetalhes
                                                            }
                                                        >
                                                            Restaurar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={
                                                                handleAtualizarStatusVinculo
                                                            }
                                                            className="btn btn-primary"
                                                            disabled={
                                                                processingVinculo ||
                                                                processingDetalhes
                                                            }
                                                        >
                                                            {processingVinculo ? (
                                                                <span className="loading loading-spinner loading-sm"></span>
                                                            ) : (
                                                                'Salvar Alterações no Vínculo'
                                                            )}
                                                        </button>
                                                    </div>
                                                )}
                                        </div>
                                    </>
                                )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
