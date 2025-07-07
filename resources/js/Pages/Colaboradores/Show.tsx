import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Banco,
    Funcao,
    PageProps,
    StatusCadastro,
    StatusVinculoProjeto,
    TipoProjeto,
    TipoVinculo,
    UsuarioProjeto,
} from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { ColaboradorDetalhes } from './Partials/ColaboradorDetalhes';
import { ColaboradorHeader } from './Partials/ColaboradorHeader';
import { ColaboradorStatus } from './Partials/ColaboradorStatus';
import { InfoItem } from './Partials/InfoItem';

export interface ColaboradorData {
    id: string;
    name: string;
    email: string;
    status_cadastro: StatusCadastro;
    campos_extras?: Record<string, string>;
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
    genero?: string | null;
    data_nascimento?: string | null;
    cep?: string | null;
    endereco?: string | null;
    numero?: string | null;
    complemento?: string | null;
    bairro?: string | null;
    cidade?: string | null;
    uf?: string | null;
    created_at: string;
    updated_at: string;
    banco?: Banco | null;
    projetos: Array<{
        id: string;
        nome: string;
        descricao: string;
        data_inicio: string;
        data_termino: string | null;
        cliente: string;
        slack_url?: string | null;
        discord_url?: string | null;
        board_url?: string | null;
        git_url?: string | null;
        tipo: TipoProjeto;
        created_at: string;
        updated_at: string;
        deleted_at?: string | null;
        vinculo: {
            id: string; // Added this line
            usuario_id: string;
            projeto_id: string;
            tipo_vinculo: TipoVinculo;
            funcao: Funcao;
            status: StatusVinculoProjeto;
            carga_horaria: number;
            valor_bolsa: number;
            data_inicio: string;
            data_fim?: string | null;
            created_at: string;
            updated_at: string;
        };
    }>;
}

export interface ShowPageProps extends PageProps {
    colaborador: ColaboradorData;
    bancos: Array<{ id: string; nome: string; codigo: string }>;
    can_update_colaborador: boolean;
    status_colaborador:
        | 'VINCULO_PENDENTE'
        | 'APROVACAO_PENDENTE'
        | 'ATIVO'
        | 'ENCERRADO';
    ultimo_vinculo: UsuarioProjeto | null;
}

interface VinculoEditFormData {
    funcao: Funcao | '';
    carga_horaria: number;
    valor_bolsa: number;
    data_inicio: string | null | undefined;
    data_fim: string | null | undefined;
}

export default function Show({
    colaborador,
    bancos,
    can_update_colaborador,
    status_colaborador,
    ultimo_vinculo,
}: ShowPageProps) {
    const { toast } = useToast();

    const [isEditingDetalhes, setIsEditingDetalhes] = useState(false);

    const {
        data: vinculoData,
        setData: setVinculoData,
        errors: vinculoErrors,
        put: putVinculo,
        processing: processingVinculo,
    } = useForm<{
        status?: StatusVinculoProjeto;
        funcao?: Funcao;
        tipo_vinculo?: TipoVinculo;
        carga_horaria?: number;
        valor_bolsa?: number;
        data_inicio?: string;
    }>({
        status: ultimo_vinculo?.status,
        funcao: ultimo_vinculo?.funcao,
        tipo_vinculo: ultimo_vinculo?.tipo_vinculo,
        carga_horaria: ultimo_vinculo?.carga_horaria,
        valor_bolsa: ultimo_vinculo?.valor_bolsa,
        data_inicio: ultimo_vinculo?.data_inicio
            ? ultimo_vinculo.data_inicio.substring(0, 10)
            : undefined,
    });

    const {
        data: detalhesData,
        setData: setDetalhesData,
        errors: detalhesErrors,
        put: putDetalhes,
        processing: processingDetalhes,
        reset: resetDetalhesForm,
        clearErrors: clearDetalhesErrors,
    } = useForm<ColaboradorData>({
        id: colaborador.id,
        email: colaborador.email,
        status_cadastro: colaborador.status_cadastro,
        projetos: colaborador.projetos || [],
        created_at: colaborador.created_at,
        updated_at: colaborador.updated_at,
        name: colaborador.name,
        campos_extras: colaborador.campos_extras,
        curriculo_lattes_url: colaborador.curriculo_lattes_url,
        linkedin_url: colaborador.linkedin_url,
        github_url: colaborador.github_url,
        website_url: colaborador.website_url,
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

    const [editingVinculo, setEditingVinculo] = useState<UsuarioProjeto | null>(
        null,
    );

    const vinculoEditForm = useForm<VinculoEditFormData>({
        funcao: '',
        carga_horaria: 0,
        valor_bolsa: 0,
        data_inicio: null,
        data_fim: null,
    });

    const handleEditVinculoClick = (vinculo: UsuarioProjeto) => {
        setEditingVinculo(vinculo);
        vinculoEditForm.setData({
            funcao: vinculo.funcao,
            carga_horaria: vinculo.carga_horaria,
            valor_bolsa: vinculo.valor_bolsa || 0,
            data_inicio: vinculo.data_inicio
                ? new Date(vinculo.data_inicio).toISOString().split('T')[0]
                : null,
            data_fim: vinculo.data_fim
                ? new Date(vinculo.data_fim).toISOString().split('T')[0]
                : null,
        });
    };

    const handleCancelEditVinculo = useCallback(() => {
        setEditingVinculo(null);
        vinculoEditForm.reset();
        vinculoEditForm.clearErrors();
    }, [vinculoEditForm]);

    const handleSubmitEditVinculo = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (!editingVinculo) return;

        vinculoEditForm.patch(`/vinculo/${editingVinculo.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast('Sucesso! Vínculo com projeto atualizado.');
                handleCancelEditVinculo();
            },
            onError: (errors) => {
                console.error(errors);
                toast(
                    'Erro! Não foi possível atualizar o vínculo com o projeto.',
                    'error',
                );
            },
        });
    };

    const handleAceitarCadastro = useCallback(
        (observacao?: string) => {
            router.post(
                route('colaboradores.aceitar', colaborador.id),
                {
                    observacao: observacao || null,
                },
                {
                    onSuccess: () => {
                        router.get(
                            route('colaboradores.index', {
                                status: 'cadastro_pendente',
                            }),
                        );
                        toast('Cadastro aceito com sucesso.', 'success');
                    },
                    onError: () => toast('Erro ao aceitar cadastro.', 'error'),
                },
            );
        },
        [colaborador.id, toast],
    );

    const handleRecusarCadastro = useCallback(
        (observacao?: string) => {
            router.post(
                route('colaboradores.recusar', colaborador.id),
                {
                    observacao: observacao || null,
                },
                {
                    onSuccess: () => {
                        router.get(
                            route('colaboradores.index', {
                                status: 'cadastro_pendente',
                            }),
                        );
                        toast('Cadastro recusado com sucesso.', 'success');
                    },
                    onError: () => toast('Erro ao recusar cadastro.', 'error'),
                },
            );
        },
        [colaborador.id, toast],
    );

    const handleAtualizarStatusVinculo = useCallback(() => {
        if (!ultimo_vinculo) {
            toast('Vínculo não encontrado', 'error');
            return;
        }
        const dataToUpdate = {
            status: vinculoData.status,
            funcao: vinculoData.funcao,
            tipo_vinculo: vinculoData.tipo_vinculo,
            carga_horaria: vinculoData.carga_horaria,
            valor_bolsa: vinculoData.valor_bolsa || 0,
            data_inicio: vinculoData.data_inicio,
        };

        putVinculo(route('vinculos.update', ultimo_vinculo.id), {
            data: dataToUpdate,
            preserveScroll: true,
            onSuccess: () => {
                router.get(
                    route('colaboradores.index', {
                        status: 'vinculo_pendente',
                    }),
                );
                toast('Status do vínculo atualizado.', 'success');
            },
            onError: (err: Record<string, string>) => {
                toast('Erro ao atualizar status do vínculo.', 'error');
                console.error('Erro ao atualizar vínculo:', err);
            },
        });
    }, [
        ultimo_vinculo,
        vinculoData.status,
        vinculoData.funcao,
        vinculoData.tipo_vinculo,
        vinculoData.carga_horaria,
        vinculoData.valor_bolsa,
        vinculoData.data_inicio,
        putVinculo,
        toast,
    ]);

    const handleAceitarVinculo = useCallback(() => {
        setVinculoData('status', 'APROVADO');
    }, [setVinculoData]);

    const handleRecusarVinculo = useCallback(() => {
        setVinculoData('status', 'RECUSADO');
    }, [setVinculoData]);

    // Estados para o modal de encerramento de vínculo
    const [showEncerrarModal, setShowEncerrarModal] = useState(false);
    const [dataFimVinculo, setDataFimVinculo] = useState('');
    const [processingEncerrar, setProcessingEncerrar] = useState(false);

    // Handlers para encerrar vínculo
    const handleEncerrarVinculo = useCallback(() => {
        if (!editingVinculo) return;

        // Definir data atual como padrão
        const today = new Date().toISOString().split('T')[0];
        setDataFimVinculo(today);
        setShowEncerrarModal(true);
    }, [editingVinculo]);

    const handleConfirmarEncerramento = useCallback(() => {
        if (!editingVinculo || !dataFimVinculo) return;

        setProcessingEncerrar(true);

        router.patch(
            `/vinculo/${editingVinculo.id}`,
            {
                data_fim: dataFimVinculo,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setShowEncerrarModal(false);
                    setDataFimVinculo('');
                    setProcessingEncerrar(false);
                    handleCancelEditVinculo();
                    toast('Vínculo encerrado com sucesso!', 'success');
                },
                onError: (errors) => {
                    setProcessingEncerrar(false);
                    console.error(errors);
                    toast('Erro ao encerrar vínculo.', 'error');
                },
            },
        );
    }, [editingVinculo, dataFimVinculo, toast, handleCancelEditVinculo]);

    const handleDesfazerEncerramento = useCallback(() => {
        if (!editingVinculo) return;

        setProcessingEncerrar(true);

        router.patch(
            `/vinculo/${editingVinculo.id}`,
            {
                data_fim: null,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setProcessingEncerrar(false);
                    handleCancelEditVinculo();
                    toast('Encerramento desfeito com sucesso!', 'success');
                },
                onError: (errors) => {
                    setProcessingEncerrar(false);
                    console.error(errors);
                    toast('Erro ao desfazer encerramento.', 'error');
                },
            },
        );
    }, [editingVinculo, toast, handleCancelEditVinculo]);

    const handleCancelarEncerramento = useCallback(() => {
        setShowEncerrarModal(false);
        setDataFimVinculo('');
    }, []);

    useEffect(() => {
        if (
            vinculoData.status === 'APROVADO' ||
            vinculoData.status === 'RECUSADO'
        ) {
            if (
                ultimo_vinculo &&
                vinculoData.status !== ultimo_vinculo.status
            ) {
                handleAtualizarStatusVinculo();
            }
        }
    }, [vinculoData.status, ultimo_vinculo, handleAtualizarStatusVinculo]);

    const originalVinculoDisplayValues = useMemo(() => {
        return {
            funcao: ultimo_vinculo?.funcao,
            tipo_vinculo: ultimo_vinculo?.tipo_vinculo,
            carga_horaria: ultimo_vinculo?.carga_horaria,
            valor_bolsa: ultimo_vinculo?.valor_bolsa,
            data_inicio: ultimo_vinculo?.data_inicio
                ? ultimo_vinculo.data_inicio.substring(0, 10)
                : undefined,
        };
    }, [ultimo_vinculo]);

    const areVinculoFieldsDirty = useMemo(
        () =>
            vinculoData.funcao !== originalVinculoDisplayValues.funcao ||
            vinculoData.tipo_vinculo !==
                originalVinculoDisplayValues.tipo_vinculo ||
            vinculoData.carga_horaria !==
                originalVinculoDisplayValues.carga_horaria ||
            vinculoData.valor_bolsa !==
                originalVinculoDisplayValues.valor_bolsa ||
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
            status: ultimo_vinculo?.status,
            funcao: originalVinculoDisplayValues.funcao,
            tipo_vinculo: originalVinculoDisplayValues.tipo_vinculo,
            carga_horaria: originalVinculoDisplayValues.carga_horaria,
            valor_bolsa: originalVinculoDisplayValues.valor_bolsa,
            data_inicio: originalVinculoDisplayValues.data_inicio,
        });
    }, [setVinculoData, ultimo_vinculo, originalVinculoDisplayValues]);

    return (
        <AuthenticatedLayout>
            <Head title={`Colaborador: ${colaborador.name}`} />
            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="card card-bordered bg-base-100 shadow-xl">
                        <div className="card-body">
                            <ColaboradorHeader colaborador={colaborador} />

                            {/* Sistema de Abas */}
                            <div className="tabs tabs-border tabs-box mt-6 w-full">
                                <input
                                    type="radio"
                                    name="colaborador_tabs"
                                    className="tab"
                                    aria-label="Status e Vínculo"
                                    defaultChecked
                                />
                                <div className="tab-content p-6">
                                    <div className="divider">
                                        Status do Cadastro e Vínculo
                                    </div>
                                    <ColaboradorStatus
                                        onAceitarCadastro={
                                            handleAceitarCadastro
                                        }
                                        onRecusarCadastro={
                                            handleRecusarCadastro
                                        }
                                        onAceitarVinculo={handleAceitarVinculo}
                                        onRecusarVinculo={handleRecusarVinculo}
                                        processing={
                                            processingVinculo ||
                                            processingDetalhes
                                        }
                                        status_colaborador={status_colaborador}
                                    />

                                    {ultimo_vinculo &&
                                        ultimo_vinculo?.status ===
                                            'PENDENTE' && (
                                            <>
                                                <div className="divider">
                                                    Detalhes do Vínculo com o
                                                    Projeto
                                                </div>
                                                <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                                                    <InfoItem
                                                        label="Projeto"
                                                        value={
                                                            ultimo_vinculo
                                                                .projeto?.nome
                                                        }
                                                    />

                                                    <InfoItem
                                                        label="Cliente"
                                                        value={
                                                            ultimo_vinculo
                                                                .projeto
                                                                ?.cliente
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
                                                                ultimo_vinculo?.funcao !==
                                                                    vinculoData.funcao &&
                                                                vinculoData.funcao !==
                                                                    undefined
                                                                    ? 'select-warning'
                                                                    : ''
                                                            } ${vinculoErrors.funcao ? 'select-error' : ''}`}
                                                            value={
                                                                vinculoData.funcao ||
                                                                ''
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
                                                                ultimo_vinculo?.status !==
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
                                                                    value={
                                                                        funcao
                                                                    }
                                                                >
                                                                    {funcao}
                                                                </option>
                                                            ))}
                                                        </select>
                                                        {vinculoErrors.funcao && (
                                                            <p className="text-error mt-1 text-xs">
                                                                {
                                                                    vinculoErrors.funcao
                                                                }
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
                                                                ultimo_vinculo?.tipo_vinculo !==
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
                                                                ultimo_vinculo?.status !==
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
                                                                            (
                                                                                char,
                                                                            ) =>
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
                                                            htmlFor="carga_horaria"
                                                        >
                                                            <span className="label-text font-semibold">
                                                                Carga Horária
                                                                Mensal:
                                                            </span>
                                                        </label>
                                                        <input
                                                            id="carga_horaria"
                                                            type="number"
                                                            className={`input input-bordered w-full ${
                                                                ultimo_vinculo?.carga_horaria !==
                                                                    vinculoData.carga_horaria &&
                                                                vinculoData.carga_horaria !==
                                                                    undefined
                                                                    ? 'input-warning'
                                                                    : ''
                                                            } ${vinculoErrors.carga_horaria ? 'input-error' : ''}`}
                                                            value={
                                                                vinculoData.carga_horaria ||
                                                                ''
                                                            }
                                                            onChange={(e) =>
                                                                setVinculoData(
                                                                    'carga_horaria',
                                                                    parseInt(
                                                                        e.target
                                                                            .value,
                                                                        10,
                                                                    ),
                                                                )
                                                            }
                                                            disabled={
                                                                processingVinculo ||
                                                                processingDetalhes ||
                                                                ultimo_vinculo?.status !==
                                                                    'PENDENTE'
                                                            }
                                                        />
                                                        <span className="label-text-alt">
                                                            {`${vinculoData.carga_horaria ? vinculoData.carga_horaria / 4 : 0} horas/semana`}
                                                        </span>
                                                        {vinculoErrors.carga_horaria && (
                                                            <p className="text-error mt-1 text-xs">
                                                                {
                                                                    vinculoErrors.carga_horaria
                                                                }
                                                            </p>
                                                        )}
                                                    </div>

                                                    <div>
                                                        <label
                                                            className="label"
                                                            htmlFor="valor_bolsa"
                                                        >
                                                            <span className="label-text font-semibold">
                                                                Valor da Bolsa:
                                                            </span>
                                                        </label>
                                                        <input
                                                            id="valor_bolsa"
                                                            type="text"
                                                            className={`input input-bordered w-full ${
                                                                ultimo_vinculo?.valor_bolsa !==
                                                                    vinculoData.valor_bolsa &&
                                                                vinculoData.valor_bolsa !==
                                                                    undefined
                                                                    ? 'input-warning'
                                                                    : ''
                                                            } ${vinculoErrors.valor_bolsa ? 'input-error' : ''}`}
                                                            value={(
                                                                (vinculoData.valor_bolsa ||
                                                                    0) / 100
                                                            ).toLocaleString(
                                                                'pt-BR',
                                                                {
                                                                    minimumFractionDigits: 2,
                                                                    maximumFractionDigits: 2,
                                                                },
                                                            )}
                                                            onChange={(e) => {
                                                                const apenasNumeros =
                                                                    e.target.value.replace(
                                                                        /\D/g,
                                                                        '',
                                                                    );
                                                                const centavos =
                                                                    parseInt(
                                                                        apenasNumeros ||
                                                                            '0',
                                                                    );
                                                                setVinculoData(
                                                                    'valor_bolsa',
                                                                    centavos,
                                                                );
                                                            }}
                                                            onFocus={(e) => {
                                                                e.target.value =
                                                                    (
                                                                        (vinculoData.valor_bolsa ||
                                                                            0) /
                                                                        100
                                                                    ).toFixed(
                                                                        2,
                                                                    );
                                                            }}
                                                            onBlur={(e) => {
                                                                e.target.value =
                                                                    (
                                                                        (vinculoData.valor_bolsa ||
                                                                            0) /
                                                                        100
                                                                    ).toLocaleString(
                                                                        'pt-BR',
                                                                        {
                                                                            minimumFractionDigits: 2,
                                                                            maximumFractionDigits: 2,
                                                                        },
                                                                    );
                                                            }}
                                                            disabled={
                                                                processingVinculo ||
                                                                processingDetalhes ||
                                                                ultimo_vinculo?.status !==
                                                                    'PENDENTE'
                                                            }
                                                            placeholder="0,00"
                                                        />
                                                        {vinculoErrors.valor_bolsa && (
                                                            <p className="text-error mt-1 text-xs">
                                                                {
                                                                    vinculoErrors.valor_bolsa
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
                                                                ultimo_vinculo?.data_inicio?.substring(
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
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            disabled={
                                                                processingVinculo ||
                                                                processingDetalhes ||
                                                                ultimo_vinculo?.status !==
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

                                                    {ultimo_vinculo?.status ===
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

                                <input
                                    type="radio"
                                    name="colaborador_tabs"
                                    className="tab"
                                    aria-label="Dados Pessoais"
                                />
                                <div className="tab-content p-6">
                                    <div className="divider">
                                        Dados Pessoais
                                    </div>
                                    {can_update_colaborador &&
                                        !isEditingDetalhes && (
                                            <div className="card-actions mb-4 justify-end">
                                                <button
                                                    className="btn btn-sm btn-outline btn-primary"
                                                    onClick={() =>
                                                        setIsEditingDetalhes(
                                                            true,
                                                        )
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
                                        onSubmit={
                                            handleUpdateDetalhesColaborador
                                        }
                                        bancos={bancos}
                                        canEdit={can_update_colaborador}
                                    />
                                </div>

                                <input
                                    type="radio"
                                    name="colaborador_tabs"
                                    className="tab"
                                    aria-label="Projetos"
                                />
                                <div className="tab-content p-6">
                                    <div className="divider">
                                        Projeto(s) Ativos
                                    </div>

                                    {colaborador.projetos.filter(
                                        (p) => p.vinculo.status === 'APROVADO',
                                    ).length > 0 ? (
                                        <div className="overflow-x-auto">
                                            <table className="table-zebra table">
                                                <thead>
                                                    <tr>
                                                        <th>Projeto</th>
                                                        <th>Cliente</th>
                                                        <th>Função</th>
                                                        <th>Carga Horária</th>
                                                        <th>Valor da Bolsa</th>
                                                        <th>Data Início</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {colaborador.projetos
                                                        .filter(
                                                            (p) =>
                                                                p.vinculo
                                                                    .status ===
                                                                'APROVADO',
                                                        )
                                                        .map((projeto) => (
                                                            <tr
                                                                key={projeto.id}
                                                            >
                                                                <td>
                                                                    <div className="font-medium">
                                                                        {
                                                                            projeto.nome
                                                                        }
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div className="text-sm opacity-70">
                                                                        {
                                                                            projeto.cliente
                                                                        }
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div className="badge badge-outline">
                                                                        {
                                                                            projeto
                                                                                .vinculo
                                                                                .funcao
                                                                        }
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div className="text-sm">
                                                                        {
                                                                            projeto
                                                                                .vinculo
                                                                                .carga_horaria
                                                                        }
                                                                        h/mês
                                                                        <div className="text-xs opacity-60">
                                                                            (
                                                                            {Math.round(
                                                                                projeto
                                                                                    .vinculo
                                                                                    .carga_horaria /
                                                                                    4,
                                                                            )}
                                                                            h/semana)
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div className="text-sm font-medium">
                                                                        {projeto
                                                                            .vinculo
                                                                            .valor_bolsa
                                                                            ? `R$ ${(projeto.vinculo.valor_bolsa / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                                                                            : 'R$ 0,00'}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div className="text-sm">
                                                                        {new Date(
                                                                            projeto.vinculo.data_inicio,
                                                                        ).toLocaleDateString(
                                                                            'pt-BR',
                                                                        )}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div className="join">
                                                                        <Link
                                                                            href={route(
                                                                                'projetos.show',
                                                                                projeto.id,
                                                                            )}
                                                                            className="btn btn-ghost btn-xs join-item"
                                                                            title="Ver Projeto"
                                                                        >
                                                                            <svg
                                                                                className="h-4 w-4"
                                                                                fill="none"
                                                                                stroke="currentColor"
                                                                                viewBox="0 0 24 24"
                                                                            >
                                                                                <path
                                                                                    strokeLinecap="round"
                                                                                    strokeLinejoin="round"
                                                                                    strokeWidth={
                                                                                        2
                                                                                    }
                                                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                                                />
                                                                                <path
                                                                                    strokeLinecap="round"
                                                                                    strokeLinejoin="round"
                                                                                    strokeWidth={
                                                                                        2
                                                                                    }
                                                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                                                />
                                                                            </svg>
                                                                        </Link>

                                                                        <Link
                                                                            href={route(
                                                                                'horarios.show',
                                                                                {
                                                                                    colaborador:
                                                                                        colaborador.id,
                                                                                    projeto:
                                                                                        projeto.id,
                                                                                },
                                                                            )}
                                                                            className="btn btn-ghost btn-xs join-item"
                                                                            title="Ver Horários"
                                                                        >
                                                                            <svg
                                                                                className="h-4 w-4"
                                                                                fill="none"
                                                                                stroke="currentColor"
                                                                                viewBox="0 0 24 24"
                                                                            >
                                                                                <path
                                                                                    strokeLinecap="round"
                                                                                    strokeLinejoin="round"
                                                                                    strokeWidth={
                                                                                        2
                                                                                    }
                                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                                                />
                                                                            </svg>
                                                                        </Link>

                                                                        {can_update_colaborador && (
                                                                            <button
                                                                                onClick={() =>
                                                                                    handleEditVinculoClick(
                                                                                        projeto.vinculo,
                                                                                    )
                                                                                }
                                                                                className="btn btn-ghost btn-xs join-item"
                                                                                title="Editar Vínculo"
                                                                            >
                                                                                <svg
                                                                                    className="h-4 w-4"
                                                                                    fill="none"
                                                                                    stroke="currentColor"
                                                                                    viewBox="0 0 24 24"
                                                                                >
                                                                                    <path
                                                                                        strokeLinecap="round"
                                                                                        strokeLinejoin="round"
                                                                                        strokeWidth={
                                                                                            2
                                                                                        }
                                                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                                                    />
                                                                                </svg>
                                                                            </button>
                                                                        )}
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    ) : (
                                        <div className="py-12 text-center">
                                            <div className="text-base-content/50 mb-2">
                                                <svg
                                                    className="mx-auto mb-4 h-12 w-12"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={1}
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                    />
                                                </svg>
                                            </div>
                                            <p className="text-base-content/70 text-lg font-medium">
                                                Nenhum projeto ativo
                                            </p>
                                            <p className="text-base-content/50 text-sm">
                                                Este colaborador não está
                                                vinculado a nenhum projeto
                                                aprovado no momento.
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {editingVinculo && (
                <Modal
                    show={!!editingVinculo}
                    onClose={handleCancelEditVinculo}
                >
                    <form onSubmit={handleSubmitEditVinculo} className="p-6">
                        <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Editar Vínculo com Projeto
                        </h2>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Altere os detalhes do vínculo do colaborador com o
                            projeto.
                        </p>

                        <div className="mt-6">
                            <label htmlFor="funcao_modal" className="label">
                                <span className="label-text">Função</span>
                            </label>
                            <select
                                id="funcao_modal"
                                name="funcao"
                                className="select select-bordered mt-1 block w-full"
                                value={vinculoEditForm.data.funcao}
                                onChange={(e) =>
                                    vinculoEditForm.setData(
                                        'funcao',
                                        e.target.value as Funcao,
                                    )
                                }
                                required
                            >
                                <option value="" disabled>
                                    Selecione uma função
                                </option>
                                {(
                                    [
                                        'ALUNO',
                                        'COORDENADOR',
                                        'DESENVOLVEDOR',
                                        'PESQUISADOR',
                                        'TECNICO',
                                    ] as Array<Funcao>
                                ).map((funcao) => (
                                    <option key={funcao} value={funcao}>
                                        {funcao
                                            .replace('_', ' ')
                                            .toLocaleLowerCase()
                                            .replace(/\b\w/g, (char) =>
                                                char.toUpperCase(),
                                            )}
                                    </option>
                                ))}
                            </select>
                            <InputError
                                message={vinculoEditForm.errors.funcao}
                                className="mt-2"
                            />
                        </div>

                        <div className="mt-4">
                            <label htmlFor="carga_horaria" className="label">
                                <span className="label-text">
                                    Carga Horária
                                </span>
                            </label>
                            <TextInput
                                id="carga_horaria"
                                name="carga_horaria"
                                type="number"
                                className="mt-1 block w-full"
                                value={vinculoEditForm.data.carga_horaria}
                                onChange={(e) =>
                                    vinculoEditForm.setData(
                                        'carga_horaria',
                                        parseInt(e.target.value) || 0,
                                    )
                                }
                                min="1"
                                max="40"
                                required
                            />
                            <span className="label-text-alt">
                                {`${vinculoEditForm.data.carga_horaria ? Number(vinculoEditForm.data.carga_horaria) / 4 : 0} horas/semana`}
                            </span>
                            <InputError
                                message={vinculoEditForm.errors.carga_horaria}
                                className="mt-2"
                            />
                        </div>

                        <div className="mt-4">
                            <label
                                htmlFor="valor_bolsa_modal"
                                className="label"
                            >
                                <span className="label-text">
                                    Valor da Bolsa
                                </span>
                            </label>
                            <TextInput
                                id="valor_bolsa_modal"
                                name="valor_bolsa"
                                type="text"
                                className="mt-1 block w-full"
                                value={(
                                    (vinculoEditForm.data.valor_bolsa || 0) /
                                    100
                                ).toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2,
                                })}
                                onChange={(e) => {
                                    const apenasNumeros =
                                        e.target.value.replace(/\D/g, '');
                                    const centavos = parseInt(
                                        apenasNumeros || '0',
                                    );
                                    vinculoEditForm.setData(
                                        'valor_bolsa',
                                        centavos,
                                    );
                                }}
                                onFocus={(e) => {
                                    e.target.value = (
                                        (vinculoEditForm.data.valor_bolsa ||
                                            0) / 100
                                    ).toFixed(2);
                                }}
                                onBlur={(e) => {
                                    e.target.value = (
                                        (vinculoEditForm.data.valor_bolsa ||
                                            0) / 100
                                    ).toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    });
                                }}
                                placeholder="0,00"
                            />
                            <InputError
                                message={vinculoEditForm.errors.valor_bolsa}
                                className="mt-2"
                            />
                        </div>

                        <div className="mt-4">
                            <label htmlFor="data_inicio" className="label">
                                <span className="label-text">
                                    Data de Inicio
                                </span>
                            </label>
                            <TextInput
                                id="data_inicio"
                                name="data_inicio"
                                type="date"
                                className="mt-1 block w-full"
                                value={vinculoEditForm.data.data_inicio || ''}
                                onChange={(e) =>
                                    vinculoEditForm.setData(
                                        'data_inicio',
                                        e.target.value,
                                    )
                                }
                            />
                            <InputError
                                message={vinculoEditForm.errors.data_inicio}
                                className="mt-2"
                            />
                        </div>

                        <div className="mt-6 flex justify-end">
                            {editingVinculo?.data_fim ? (
                                // Se já tem data_fim, mostra botão para desfazer
                                <DangerButton
                                    type="button"
                                    onClick={handleDesfazerEncerramento}
                                    disabled={processingEncerrar}
                                    className="mr-3"
                                >
                                    {processingEncerrar
                                        ? 'Processando...'
                                        : 'Desfazer Encerramento'}
                                </DangerButton>
                            ) : (
                                // Se não tem data_fim, mostra botão para encerrar
                                <DangerButton
                                    type="button"
                                    onClick={handleEncerrarVinculo}
                                    disabled={vinculoEditForm.processing}
                                    className="mr-3"
                                >
                                    Encerrar Vínculo
                                </DangerButton>
                            )}
                            <SecondaryButton
                                type="button"
                                onClick={handleCancelEditVinculo}
                            >
                                Cancelar
                            </SecondaryButton>
                            <PrimaryButton
                                className="ml-3"
                                disabled={vinculoEditForm.processing}
                            >
                                Salvar Alterações
                            </PrimaryButton>
                        </div>
                    </form>
                </Modal>
            )}

            {/* Modal de confirmação de encerramento */}
            {showEncerrarModal && (
                <Modal
                    show={showEncerrarModal}
                    onClose={handleCancelarEncerramento}
                >
                    <div className="p-6">
                        <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Encerrar Vínculo
                        </h2>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Defina a data de encerramento do vínculo. O status
                            será atualizado automaticamente pelo sistema.
                        </p>

                        <div className="mt-6">
                            <label
                                htmlFor="data_fim_encerramento"
                                className="label"
                            >
                                <span className="label-text">
                                    Data de Encerramento
                                </span>
                            </label>
                            <TextInput
                                id="data_fim_encerramento"
                                type="date"
                                className="mt-1 block w-full"
                                value={dataFimVinculo}
                                onChange={(e) =>
                                    setDataFimVinculo(e.target.value)
                                }
                                required
                            />
                        </div>

                        <div className="mt-6 flex justify-end">
                            <SecondaryButton
                                type="button"
                                onClick={handleCancelarEncerramento}
                                disabled={processingEncerrar}
                            >
                                Cancelar
                            </SecondaryButton>
                            <DangerButton
                                className="ml-3"
                                onClick={handleConfirmarEncerramento}
                                disabled={processingEncerrar || !dataFimVinculo}
                            >
                                {processingEncerrar
                                    ? 'Processando...'
                                    : 'Confirmar Encerramento'}
                            </DangerButton>
                        </div>
                    </div>
                </Modal>
            )}
        </AuthenticatedLayout>
    );
}
