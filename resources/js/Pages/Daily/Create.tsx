import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import React from 'react';

interface UsuarioProjeto {
    id: string;
    projeto_nome: string;
    funcao: string;
}

interface Props extends PageProps {
    usuarioProjetos: UsuarioProjeto[];
}

interface FormData {
    usuario_projeto_id: string;
    data: string;
    ontem: string;
    observacoes: string;
    hoje: string;
    carga_horaria: number;
}

export default function Create({ usuarioProjetos }: Props) {
    const { data, setData, post, processing, errors, reset } =
        useForm<FormData>({
            usuario_projeto_id: '',
            data: new Date().toISOString().split('T')[0], // Data atual por padrão
            ontem: '',
            observacoes: '',
            hoje: '',
            carga_horaria: 8,
        });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('daily.store'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h2 className="text-base-content text-xl font-semibold">
                            Novo Daily
                        </h2>
                        <p className="text-base-content/60 mt-0.5 text-sm">
                            Registre seu progresso diário
                        </p>
                    </div>
                    <Link
                        href={route('daily.index')}
                        className="btn btn-ghost btn-sm gap-2"
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
                                strokeWidth={2}
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Voltar
                    </Link>
                </div>
            }
        >
            <Head title="Novo Daily" />

            <div className="py-4">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-6">
                        {/* Informações Básicas */}
                        <div className="card bg-base-100 shadow-sm">
                            <div className="card-body p-6">
                                <div className="mb-4 flex items-center gap-2">
                                    <svg
                                        className="text-base-content/70 h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <h3 className="text-base-content font-medium">
                                        Informações Básicas
                                    </h3>
                                </div>

                                <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
                                    <div className="form-control">
                                        <select
                                            className={`select select-bordered select-sm ${errors.usuario_projeto_id ? 'select-error' : ''}`}
                                            value={data.usuario_projeto_id}
                                            onChange={(e) =>
                                                setData(
                                                    'usuario_projeto_id',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                        >
                                            <option value="">
                                                Selecione um projeto
                                            </option>
                                            {usuarioProjetos.map((projeto) => (
                                                <option
                                                    key={projeto.id}
                                                    value={projeto.id}
                                                >
                                                    {projeto.projeto_nome} -{' '}
                                                    {projeto.funcao}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.usuario_projeto_id && (
                                            <div className="text-error mt-1 text-xs">
                                                {errors.usuario_projeto_id}
                                            </div>
                                        )}
                                    </div>

                                    <div className="form-control">
                                        <input
                                            type="date"
                                            className={`input input-bordered input-sm ${errors.data ? 'input-error' : ''}`}
                                            value={data.data}
                                            onChange={(e) =>
                                                setData('data', e.target.value)
                                            }
                                            max={
                                                new Date()
                                                    .toISOString()
                                                    .split('T')[0]
                                            }
                                            required
                                        />
                                        {errors.data && (
                                            <div className="text-error mt-1 text-xs">
                                                {errors.data}
                                            </div>
                                        )}
                                    </div>

                                    <div className="form-control">
                                        <div className="input-group input-group-sm">
                                            <input
                                                type="number"
                                                min="1"
                                                max="9"
                                                className={`input input-bordered input-sm flex-1 ${errors.carga_horaria ? 'input-error' : ''}`}
                                                value={data.carga_horaria}
                                                onChange={(e) =>
                                                    setData(
                                                        'carga_horaria',
                                                        parseInt(
                                                            e.target.value,
                                                        ) || 1,
                                                    )
                                                }
                                                required
                                            />
                                            <span className="bg-base-200 flex items-center px-3 text-sm">
                                                horas
                                            </span>
                                        </div>
                                        {errors.carga_horaria && (
                                            <div className="text-error mt-1 text-xs">
                                                {errors.carga_horaria}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Atividades */}
                        <div className="card bg-base-100 shadow-sm">
                            <div className="card-body p-6">
                                <div className="mb-4 flex items-center gap-2">
                                    <svg
                                        className="text-base-content/70 h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                    <h3 className="text-base-content font-medium">
                                        Atividades
                                    </h3>
                                </div>

                                <div className="space-y-4">
                                    <div className="form-control">
                                        <label className="label py-1">
                                            <span className="label-text flex items-center gap-2 text-sm font-medium">
                                                <div className="bg-warning h-2 w-2 rounded-full"></div>
                                                O que você fez ontem? *
                                            </span>
                                            <span className="label-text-alt text-base-content/60 text-xs">
                                                {data.ontem.length}/2000
                                            </span>
                                        </label>
                                        <textarea
                                            className={`textarea textarea-bordered textarea-sm h-24 resize-none ${errors.ontem ? 'textarea-error' : ''}`}
                                            placeholder="Descreva as atividades realizadas ontem..."
                                            value={data.ontem}
                                            onChange={(e) =>
                                                setData('ontem', e.target.value)
                                            }
                                            maxLength={2000}
                                            required
                                        />
                                        {errors.ontem && (
                                            <div className="text-error mt-1 text-xs">
                                                {errors.ontem}
                                            </div>
                                        )}
                                    </div>

                                    <div className="form-control">
                                        <label className="label py-1">
                                            <span className="label-text flex items-center gap-2 text-sm font-medium">
                                                <div className="bg-success h-2 w-2 rounded-full"></div>
                                                O que você fará hoje? *
                                            </span>
                                            <span className="label-text-alt text-base-content/60 text-xs">
                                                {data.hoje.length}/2000
                                            </span>
                                        </label>
                                        <textarea
                                            className={`textarea textarea-bordered textarea-sm h-24 resize-none ${errors.hoje ? 'textarea-error' : ''}`}
                                            placeholder="Descreva as atividades planejadas para hoje..."
                                            value={data.hoje}
                                            onChange={(e) =>
                                                setData('hoje', e.target.value)
                                            }
                                            maxLength={2000}
                                            required
                                        />
                                        {errors.hoje && (
                                            <div className="text-error mt-1 text-xs">
                                                {errors.hoje}
                                            </div>
                                        )}
                                    </div>

                                    <div className="form-control">
                                        <label className="label py-1">
                                            <span className="label-text flex items-center gap-2 text-sm font-medium">
                                                <div className="bg-info h-2 w-2 rounded-full"></div>
                                                Observações
                                            </span>
                                            <span className="label-text-alt text-base-content/60 text-xs">
                                                {data.observacoes.length}/2000
                                            </span>
                                        </label>
                                        <textarea
                                            className={`textarea textarea-bordered textarea-sm h-20 resize-none ${errors.observacoes ? 'textarea-error' : ''}`}
                                            placeholder="Dificuldades, bloqueios, comentários..."
                                            value={data.observacoes}
                                            onChange={(e) =>
                                                setData(
                                                    'observacoes',
                                                    e.target.value,
                                                )
                                            }
                                            maxLength={2000}
                                        />
                                        {errors.observacoes && (
                                            <div className="text-error mt-1 text-xs">
                                                {errors.observacoes}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Ações */}
                        <div className="flex flex-col justify-end gap-3 sm:flex-row">
                            <Link
                                href={route('daily.index')}
                                className="btn btn-ghost btn-sm"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                className="btn btn-primary btn-sm gap-2"
                                disabled={processing}
                            >
                                {processing && (
                                    <span className="loading loading-spinner loading-xs"></span>
                                )}
                                <svg
                                    className="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                {processing ? 'Salvando...' : 'Salvar Daily'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
