import Pagination, { Paginated } from '@/Components/Paggination'; // Updated import
import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Coordenador,
    Funcao,
    PageProps,
    Projeto,
    StatusVinculoProjeto,
    TipoVinculo,
    User,
    UsuarioProjeto,
} from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import React from 'react';

type ParticipanteProjeto = Pick<User, 'id' | 'name' | 'email' | 'foto_url'> & {
    funcao: Funcao;
    tipo_vinculo: TipoVinculo;
    data_inicio: string;
};

interface ShowPageProps extends PageProps {
    projeto: Projeto;
    tiposVinculo: TipoVinculo[];
    funcoes: Funcao[];
    usuarioVinculo: UsuarioProjeto | null;
    vinculosDoUsuarioLogadoNoProjeto: UsuarioProjeto[];
    participantesProjeto?: Paginated<ParticipanteProjeto>;
    temVinculosPendentes: boolean;
    jaTemTrocaEmAndamento: boolean;
    coordenadoresDoProjeto: Coordenador[];
}

type VinculoCreateForm = {
    projeto_id: string;
    tipo_vinculo: TipoVinculo | '';
    funcao: Funcao | '';
    carga_horaria: number;
    data_inicio: string;
    trocar?: boolean;
    usuario_projeto_trocado_id?: string | null;
};

export default function Show({
    auth,
    projeto,
    tiposVinculo,
    funcoes,
    usuarioVinculo,
    vinculosDoUsuarioLogadoNoProjeto,
    participantesProjeto,
    temVinculosPendentes,
    coordenadoresDoProjeto,
}: ShowPageProps) {
    const { toast } = useToast();
    const form = useForm<VinculoCreateForm>({
        projeto_id: projeto.id,
        tipo_vinculo: '',
        funcao: '',
        carga_horaria: 20,
        data_inicio: '',
    });

    const isCoordenadorDoProjetoAtual =
        usuarioVinculo?.tipo_vinculo === ('COORDENADOR' as TipoVinculo) &&
        usuarioVinculo?.status === ('APROVADO' as StatusVinculoProjeto);

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        form.post(route('vinculo.create', projeto.id), {
            onSuccess: () => {
                toast('Solicitação de vínculo enviada com sucesso!', 'success');
            },
            onError: (e) => {
                toast('Erro ao solicitar vínculo!', 'error');
                console.error(e);
            },
        });
    };

    const renderVinculoStatus = () => {
        if (usuarioVinculo) {
            let statusClass = 'badge-neutral';
            let statusText = '';

            switch (usuarioVinculo.status) {
                case 'APROVADO':
                    statusClass = 'badge-success';
                    statusText = 'Aprovado';
                    break;
                case 'PENDENTE':
                    statusClass = 'badge-warning';
                    statusText = 'Pendente';
                    break;
                case 'RECUSADO':
                    statusClass = 'badge-error';
                    statusText = 'Recusado';
                    break;
                default:
                    statusText = usuarioVinculo.status;
            }

            return (
                <div className="card bg-base-200 mt-6 shadow">
                    <div className="card-body">
                        <h3 className="text-base-content mb-4 text-lg font-medium">
                            Seu Vínculo Atual com Este Projeto
                        </h3>
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-base-content/70 text-sm font-medium">
                                    Status do vínculo:
                                </span>
                                <span
                                    className={`badge ${statusClass} badge-md`}
                                >
                                    {statusText}
                                </span>
                            </div>

                            {(usuarioVinculo.funcao ||
                                usuarioVinculo.tipo_vinculo) && (
                                <div className="divider my-1"></div>
                            )}

                            {usuarioVinculo.funcao && (
                                <div className="flex items-center justify-between">
                                    <span className="text-base-content/70 text-sm font-medium">
                                        Função:
                                    </span>
                                    <span className="badge badge-outline badge-md">
                                        {usuarioVinculo.funcao}
                                    </span>
                                </div>
                            )}
                            {usuarioVinculo.tipo_vinculo && (
                                <div className="flex items-center justify-between">
                                    <span className="text-base-content/70 text-sm font-medium">
                                        Tipo de Vínculo:
                                    </span>
                                    <span className="badge badge-primary badge-md">
                                        {usuarioVinculo.tipo_vinculo}
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            );
        }
        return null;
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Projeto: ${projeto.nome}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {/* Card for Project Details */}
                    <div className="bg-base-100 dark:bg-base-300 p-4 shadow sm:rounded-lg sm:p-8">
                        <div>
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {projeto.nome}
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Cliente: {projeto.cliente}
                                    </p>
                                </div>
                                {isCoordenadorDoProjetoAtual && (
                                    <Link
                                        href={route(
                                            'projetos.edit',
                                            projeto.id,
                                        )}
                                        className="btn btn-outline btn-sm"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            strokeWidth={1.5}
                                            stroke="currentColor"
                                            className="h-4 w-4"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13L2 21l.35-2.935a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"
                                            />
                                        </svg>
                                        Editar
                                    </Link>
                                )}
                            </div>

                            {auth.isCoordenador && temVinculosPendentes && (
                                <Link
                                    href={route('colaboradores.index', {
                                        status: 'vinculo_pendente',
                                        project_id: projeto.id,
                                    })}
                                >
                                    <div
                                        role="alert"
                                        className="alert alert-warning mt-4 cursor-pointer hover:shadow-lg"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            className="stroke-info h-6 w-6 shrink-0"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        <span>
                                            Há solicitações de vínculos
                                            pendentes! Clique para ver.
                                        </span>
                                    </div>
                                </Link>
                            )}

                            <div className="mt-4 space-y-2">
                                <p>
                                    <span className="font-semibold">
                                        Descrição:
                                    </span>{' '}
                                    {projeto.descricao || 'Não informada'}
                                </p>
                                <p>
                                    <span className="font-semibold">
                                        Data de Início:
                                    </span>{' '}
                                    {format(
                                        new Date(projeto.data_inicio),
                                        'dd/MM/yyyy',
                                        { locale: ptBR },
                                    )}
                                </p>
                                <p>
                                    <span className="font-semibold">
                                        Data de Término:
                                    </span>{' '}
                                    {projeto.data_termino
                                        ? format(
                                              new Date(projeto.data_termino),
                                              'dd/MM/yyyy',
                                              { locale: ptBR },
                                          )
                                        : 'Não definida'}
                                </p>
                                <p>
                                    <span className="font-semibold">Tipo:</span>{' '}
                                    {projeto.tipo}
                                </p>
                                {renderVinculoStatus()}
                            </div>
                        </div>
                    </div>

                    {!usuarioVinculo && (
                        <>
                            <div className="card bg-base-100 mb-6 shadow-xl">
                                <div className="card-body">
                                    <h3 className="card-title text-xl">
                                        Coordenadores do Projeto
                                    </h3>
                                    {coordenadoresDoProjeto &&
                                    coordenadoresDoProjeto.length > 0 ? (
                                        <ul className="mt-2 list-disc pl-5">
                                            {coordenadoresDoProjeto.map(
                                                (coordenador: Coordenador) => (
                                                    <li
                                                        key={coordenador.id}
                                                        className="text-sm text-gray-700 dark:text-gray-300"
                                                    >
                                                        {coordenador.name}
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    ) : (
                                        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            Nenhum coordenador encontrado para
                                            este projeto.
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="card bg-base-100 shadow-xl">
                                <div className="card-body">
                                    <h3 className="card-title mb-2 text-xl">
                                        Solicitar Vínculo ao Projeto
                                    </h3>
                                    <p className="text-base-content/70 mb-6">
                                        Preencha os dados abaixo para solicitar
                                        sua participação neste projeto.
                                    </p>
                                    <form
                                        onSubmit={submit}
                                        className="space-y-6"
                                    >
                                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                            {auth.isVinculoProjetoPendente ? (
                                                <div
                                                    role="alert"
                                                    className="alert alert-info md:col-span-2"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        className="h-6 w-6 shrink-0 stroke-current"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                        ></path>
                                                    </svg>
                                                    <span>
                                                        Você já possui um
                                                        vinculo pendente, entre
                                                        em contato com um
                                                        Coordenador para avaliar
                                                        seu vinculo.
                                                    </span>
                                                </div>
                                            ) : (
                                                <div className="md:col-span-2">
                                                    <div className="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                                                        {vinculosDoUsuarioLogadoNoProjeto &&
                                                            vinculosDoUsuarioLogadoNoProjeto.length >
                                                                0 && (
                                                                <>
                                                                    {/* Checkbox for trocar */}
                                                                    <div className="form-control">
                                                                        <label className="label cursor-pointer justify-start gap-4">
                                                                            <span className="label-text font-medium">
                                                                                Trocar
                                                                                de
                                                                                projeto?
                                                                            </span>
                                                                            <input
                                                                                type="checkbox"
                                                                                className="toggle toggle-primary"
                                                                                checked={
                                                                                    form
                                                                                        .data
                                                                                        .trocar ||
                                                                                    false
                                                                                }
                                                                                onChange={(
                                                                                    e,
                                                                                ) =>
                                                                                    form.setData(
                                                                                        'trocar',
                                                                                        e
                                                                                            .target
                                                                                            .checked,
                                                                                    )
                                                                                }
                                                                            />
                                                                        </label>
                                                                        <div className="label pt-0">
                                                                            <span className="label-text-alt text-base-content/70">
                                                                                Marque
                                                                                se
                                                                                deseja
                                                                                trocar
                                                                                de
                                                                                um
                                                                                projeto
                                                                                atual
                                                                            </span>
                                                                        </div>
                                                                        {form
                                                                            .errors
                                                                            .trocar && (
                                                                            <div className="label">
                                                                                <span className="label-text-alt text-error">
                                                                                    {
                                                                                        form
                                                                                            .errors
                                                                                            .trocar
                                                                                    }
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                    </div>

                                                                    {/* Projeto a ser trocado */}
                                                                    <div className="form-control">
                                                                        <label className="label">
                                                                            <span className="label-text font-medium">
                                                                                Projeto
                                                                                a
                                                                                ser
                                                                                trocado
                                                                            </span>
                                                                        </label>
                                                                        <select
                                                                            className={`select select-bordered w-full ${
                                                                                !form
                                                                                    .data
                                                                                    .trocar
                                                                                    ? 'select-disabled'
                                                                                    : ''
                                                                            } ${form.errors.usuario_projeto_trocado_id ? 'select-error' : ''}`}
                                                                            disabled={
                                                                                !form
                                                                                    .data
                                                                                    .trocar
                                                                            }
                                                                            value={
                                                                                form
                                                                                    .data
                                                                                    .usuario_projeto_trocado_id ||
                                                                                ''
                                                                            }
                                                                            onChange={(
                                                                                e,
                                                                            ) =>
                                                                                form.setData(
                                                                                    'usuario_projeto_trocado_id',
                                                                                    e
                                                                                        .target
                                                                                        .value ||
                                                                                        null,
                                                                                )
                                                                            }
                                                                        >
                                                                            <option
                                                                                value=""
                                                                                disabled
                                                                            >
                                                                                Selecione
                                                                                o
                                                                                vínculo
                                                                                a
                                                                                ser
                                                                                encerrado
                                                                            </option>
                                                                            {vinculosDoUsuarioLogadoNoProjeto
                                                                                .filter(
                                                                                    (
                                                                                        vinculo,
                                                                                    ) =>
                                                                                        vinculo.status ===
                                                                                            'APROVADO' &&
                                                                                        !vinculo.data_fim,
                                                                                )
                                                                                .map(
                                                                                    (
                                                                                        vinculo,
                                                                                    ) => (
                                                                                        <option
                                                                                            key={
                                                                                                vinculo.id
                                                                                            }
                                                                                            value={
                                                                                                vinculo.id
                                                                                            }
                                                                                        >
                                                                                            {
                                                                                                vinculo
                                                                                                    .projeto
                                                                                                    ?.nome
                                                                                            }{' '}
                                                                                            (
                                                                                            {
                                                                                                vinculo.funcao
                                                                                            }

                                                                                            )
                                                                                        </option>
                                                                                    ),
                                                                                )}
                                                                        </select>
                                                                        <div className="label">
                                                                            {!form
                                                                                .data
                                                                                .trocar && (
                                                                                <span className="label-text-alt text-base-content/70">
                                                                                    Disponível
                                                                                    apenas
                                                                                    ao
                                                                                    trocar
                                                                                    de
                                                                                    projeto
                                                                                </span>
                                                                            )}
                                                                            {form
                                                                                .errors
                                                                                .usuario_projeto_trocado_id && (
                                                                                <span className="label-text-alt text-error">
                                                                                    {
                                                                                        form
                                                                                            .errors
                                                                                            .usuario_projeto_trocado_id
                                                                                    }
                                                                                </span>
                                                                            )}
                                                                        </div>
                                                                    </div>
                                                                </>
                                                            )}

                                                        {/* Tipo de Vínculo */}
                                                        <div className="form-control">
                                                            <label className="label">
                                                                <span className="label-text font-medium">
                                                                    Tipo de
                                                                    Vínculo
                                                                    <span className="text-error ml-1">
                                                                        *
                                                                    </span>
                                                                </span>
                                                            </label>
                                                            <select
                                                                className={`select select-bordered w-full ${
                                                                    form.errors
                                                                        .tipo_vinculo
                                                                        ? 'select-error'
                                                                        : ''
                                                                }`}
                                                                value={
                                                                    form.data
                                                                        .tipo_vinculo
                                                                }
                                                                onChange={(e) =>
                                                                    form.setData(
                                                                        'tipo_vinculo',
                                                                        e.target
                                                                            .value as TipoVinculo,
                                                                    )
                                                                }
                                                            >
                                                                <option
                                                                    value=""
                                                                    disabled
                                                                >
                                                                    Selecione o
                                                                    tipo de
                                                                    vínculo
                                                                </option>
                                                                {tiposVinculo.map(
                                                                    (tipo) => (
                                                                        <option
                                                                            key={
                                                                                tipo
                                                                            }
                                                                            value={
                                                                                tipo
                                                                            }
                                                                        >
                                                                            {
                                                                                tipo
                                                                            }
                                                                        </option>
                                                                    ),
                                                                )}
                                                            </select>
                                                            {form.errors
                                                                .tipo_vinculo && (
                                                                <div className="label">
                                                                    <span className="label-text-alt text-error">
                                                                        {
                                                                            form
                                                                                .errors
                                                                                .tipo_vinculo
                                                                        }
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>

                                                        {/* Função */}
                                                        <div className="form-control">
                                                            <label className="label">
                                                                <span className="label-text font-medium">
                                                                    Função
                                                                    <span className="text-error ml-1">
                                                                        *
                                                                    </span>
                                                                </span>
                                                            </label>
                                                            <select
                                                                className={`select select-bordered w-full ${
                                                                    form.errors
                                                                        .funcao
                                                                        ? 'select-error'
                                                                        : ''
                                                                }`}
                                                                value={
                                                                    form.data
                                                                        .funcao
                                                                }
                                                                onChange={(e) =>
                                                                    form.setData(
                                                                        'funcao',
                                                                        e.target
                                                                            .value as Funcao,
                                                                    )
                                                                }
                                                            >
                                                                <option
                                                                    value=""
                                                                    disabled
                                                                >
                                                                    Selecione a
                                                                    função
                                                                </option>
                                                                {funcoes.map(
                                                                    (
                                                                        funcao,
                                                                    ) => (
                                                                        <option
                                                                            key={
                                                                                funcao
                                                                            }
                                                                            value={
                                                                                funcao
                                                                            }
                                                                        >
                                                                            {
                                                                                funcao
                                                                            }
                                                                        </option>
                                                                    ),
                                                                )}
                                                            </select>
                                                            {form.errors
                                                                .funcao && (
                                                                <div className="label">
                                                                    <span className="label-text-alt text-error">
                                                                        {
                                                                            form
                                                                                .errors
                                                                                .funcao
                                                                        }
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>

                                                        {/* Carga Horária */}
                                                        <div className="form-control">
                                                            <label className="label">
                                                                <span className="label-text font-medium">
                                                                    Carga
                                                                    Horária
                                                                    Semanal
                                                                    <span className="text-error ml-1">
                                                                        *
                                                                    </span>
                                                                </span>
                                                                <span className="label-text-alt">
                                                                    horas
                                                                </span>
                                                            </label>
                                                            <input
                                                                type="number"
                                                                className={`input input-bordered w-full ${
                                                                    form.errors
                                                                        .carga_horaria
                                                                        ? 'input-error'
                                                                        : ''
                                                                }`}
                                                                placeholder="Ex: 20"
                                                                value={
                                                                    form.data
                                                                        .carga_horaria
                                                                }
                                                                onChange={(
                                                                    e: React.ChangeEvent<HTMLInputElement>,
                                                                ) =>
                                                                    form.setData(
                                                                        'carga_horaria',
                                                                        parseInt(
                                                                            e
                                                                                .target
                                                                                .value,
                                                                        ) || 0,
                                                                    )
                                                                }
                                                                min="1"
                                                                max="40"
                                                            />
                                                            {form.errors
                                                                .carga_horaria && (
                                                                <div className="label">
                                                                    <span className="label-text-alt text-error">
                                                                        {
                                                                            form
                                                                                .errors
                                                                                .carga_horaria
                                                                        }
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>

                                                        {/* Data de Inicio */}
                                                        <div className="form-control">
                                                            <label className="label">
                                                                <span className="label-text font-medium">
                                                                    Data de
                                                                    Início
                                                                    <span className="text-error ml-1">
                                                                        *
                                                                    </span>
                                                                </span>
                                                            </label>
                                                            <input
                                                                type="date"
                                                                className={`input input-bordered w-full ${
                                                                    form.errors
                                                                        .data_inicio
                                                                        ? 'input-error'
                                                                        : ''
                                                                }`}
                                                                value={
                                                                    form.data
                                                                        .data_inicio
                                                                }
                                                                onChange={(
                                                                    e: React.ChangeEvent<HTMLInputElement>,
                                                                ) =>
                                                                    form.setData(
                                                                        'data_inicio',
                                                                        e.target
                                                                            .value,
                                                                    )
                                                                }
                                                            />
                                                            {form.errors
                                                                .data_inicio && (
                                                                <div className="label">
                                                                    <span className="label-text-alt text-error">
                                                                        {
                                                                            form
                                                                                .errors
                                                                                .data_inicio
                                                                        }
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>

                                                    {/* Submit Button */}
                                                    <div className="card-actions justify-end pt-6">
                                                        <button
                                                            type="submit"
                                                            className="btn btn-primary btn-wide"
                                                            disabled={
                                                                form.processing
                                                            }
                                                        >
                                                            {form.processing && (
                                                                <span className="loading loading-spinner loading-sm"></span>
                                                            )}
                                                            {form.processing
                                                                ? 'Enviando...'
                                                                : 'Solicitar Vínculo'}
                                                        </button>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </>
                    )}

                    {/* Project Participants List (for coordinators) */}
                    {isCoordenadorDoProjetoAtual &&
                        participantesProjeto &&
                        participantesProjeto.data.length > 0 && (
                            <div className="card bg-base-100 shadow-xl">
                                <div className="card-body">
                                    <h3 className="card-title mb-4 text-xl">
                                        Participantes do Projeto
                                    </h3>
                                    <div className="overflow-x-auto">
                                        <table className="table-zebra table w-full">
                                            <thead>
                                                <tr>
                                                    <th>Nome</th>
                                                    <th>Email</th>
                                                    <th>Função</th>
                                                    {/* <th>Vínculo</th> */}
                                                    <th>Início</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {participantesProjeto.data.map(
                                                    (
                                                        participante: ParticipanteProjeto,
                                                    ) => (
                                                        <tr
                                                            key={
                                                                participante.id
                                                            }
                                                        >
                                                            <td>
                                                                <div className="flex items-center gap-3">
                                                                    <div className="avatar">
                                                                        <div className="mask mask-squircle h-12 w-12">
                                                                            <img
                                                                                src={
                                                                                    participante.foto_url ||
                                                                                    `https://ui-avatars.com/api/?name=${encodeURIComponent(participante.name)}&background=random&color=fff`
                                                                                }
                                                                                alt={`Foto de ${participante.name}`}
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div className="font-bold">
                                                                            {
                                                                                participante.name
                                                                            }
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                {
                                                                    participante.email
                                                                }
                                                            </td>
                                                            <td>
                                                                <span className="badge badge-primary badge-sm">
                                                                    {
                                                                        participante.funcao
                                                                    }
                                                                </span>
                                                            </td>
                                                            {/* <td>
                                                                <span className="badge badge-outline badge-sm">
                                                                    {
                                                                        participante.tipo_vinculo
                                                                    }
                                                                </span>
                                                            </td> */}
                                                            <td>
                                                                {format(
                                                                    new Date(
                                                                        participante.data_inicio,
                                                                    ),
                                                                    'dd/MM/yyyy',
                                                                    {
                                                                        locale: ptBR,
                                                                    },
                                                                )}
                                                            </td>
                                                            <td>
                                                                <Link
                                                                    href={route(
                                                                        'colaboradores.show',
                                                                        participante.id,
                                                                    )}
                                                                    className="btn btn-ghost btn-xs"
                                                                >
                                                                    Detalhes
                                                                </Link>
                                                            </td>
                                                        </tr>
                                                    ),
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                    {/* Pagination */}
                                    {participantesProjeto &&
                                        participantesProjeto.data.length >
                                            0 && (
                                            <Pagination
                                                paginated={participantesProjeto}
                                            />
                                        )}
                                </div>
                            </div>
                        )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
