import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import React, { useState } from 'react';

interface ConfiguracaoIndexProps {
    configuracoes: {
        senha_laboratorio_existe: boolean;
    };
}

export default function Index({ configuracoes }: ConfiguracaoIndexProps) {
    const [showForm, setShowForm] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        senha_atual: '',
        nova_senha: '',
        nova_senha_confirmation: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        patch(route('configuracoes.senha-laboratorio.update'), {
            onSuccess: () => {
                reset();
                setShowForm(false);
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-base-content text-xl leading-tight font-semibold">
                    Configurações do Sistema
                </h2>
            }
        >
            <Head title="Configurações" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            {/* Seção de Senha do Laboratório */}
                            <div className="mb-8">
                                <div className="mb-6">
                                    <h3 className="text-base-content text-lg font-medium">
                                        Senha do Laboratório
                                    </h3>
                                    <p className="text-base-content/70 mt-1 text-sm">
                                        Altere a senha utilizada para novos
                                        cadastros no laboratório.
                                    </p>
                                </div>

                                {!showForm ? (
                                    <div className="flex items-center gap-4">
                                        <div
                                            className={`badge ${configuracoes.senha_laboratorio_existe ? 'badge-success' : 'badge-warning'} badge-outline`}
                                        >
                                            {configuracoes.senha_laboratorio_existe
                                                ? 'Configurada'
                                                : 'Não configurada'}
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => setShowForm(true)}
                                            className="btn btn-primary btn-sm"
                                        >
                                            {configuracoes.senha_laboratorio_existe
                                                ? 'Alterar Senha'
                                                : 'Configurar Senha'}
                                        </button>
                                    </div>
                                ) : (
                                    <div className="card bg-base-200/50 border-base-300 border">
                                        <div className="card-body">
                                            <form
                                                onSubmit={handleSubmit}
                                                className="space-y-6"
                                            >
                                                <h4 className="text-base-content text-lg font-medium">
                                                    {configuracoes.senha_laboratorio_existe
                                                        ? 'Alterar Senha do Laboratório'
                                                        : 'Configurar Senha do Laboratório'}
                                                </h4>

                                                <div className="space-y-4">
                                                    <label className="form-control w-full">
                                                        <div className="label">
                                                            <span className="label-text font-medium">
                                                                {configuracoes.senha_laboratorio_existe
                                                                    ? 'Senha Atual'
                                                                    : 'Senha do Laboratório'}
                                                            </span>
                                                        </div>
                                                        <input
                                                            type="password"
                                                            value={
                                                                data.senha_atual
                                                            }
                                                            onChange={(e) =>
                                                                setData(
                                                                    'senha_atual',
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            className={`input input-bordered bg-base-100 w-full ${errors.senha_atual ? 'input-error' : ''}`}
                                                            placeholder={
                                                                configuracoes.senha_laboratorio_existe
                                                                    ? 'Digite a senha atual'
                                                                    : 'Digite a senha do laboratório'
                                                            }
                                                            required
                                                        />
                                                        {errors.senha_atual && (
                                                            <div className="label">
                                                                <span className="label-text-alt text-error">
                                                                    {
                                                                        errors.senha_atual
                                                                    }
                                                                </span>
                                                            </div>
                                                        )}
                                                    </label>

                                                    <label className="form-control w-full">
                                                        <div className="label">
                                                            <span className="label-text font-medium">
                                                                Nova Senha
                                                            </span>
                                                        </div>
                                                        <input
                                                            type="password"
                                                            value={
                                                                data.nova_senha
                                                            }
                                                            onChange={(e) =>
                                                                setData(
                                                                    'nova_senha',
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            className={`input input-bordered bg-base-100 w-full ${errors.nova_senha ? 'input-error' : ''}`}
                                                            placeholder="Digite a nova senha"
                                                            required
                                                        />
                                                        {errors.nova_senha && (
                                                            <div className="label">
                                                                <span className="label-text-alt text-error">
                                                                    {
                                                                        errors.nova_senha
                                                                    }
                                                                </span>
                                                            </div>
                                                        )}
                                                    </label>

                                                    <label className="form-control w-full">
                                                        <div className="label">
                                                            <span className="label-text font-medium">
                                                                Confirmar Nova
                                                                Senha
                                                            </span>
                                                        </div>
                                                        <input
                                                            type="password"
                                                            value={
                                                                data.nova_senha_confirmation
                                                            }
                                                            onChange={(e) =>
                                                                setData(
                                                                    'nova_senha_confirmation',
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            className="input input-bordered bg-base-100 w-full"
                                                            placeholder="Confirme a nova senha"
                                                            required
                                                        />
                                                    </label>
                                                </div>

                                                <div className="flex justify-end gap-3 pt-4">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setShowForm(false);
                                                            reset();
                                                        }}
                                                        className="btn btn-ghost"
                                                        disabled={processing}
                                                    >
                                                        Cancelar
                                                    </button>
                                                    <button
                                                        type="submit"
                                                        disabled={processing}
                                                        className="btn btn-primary"
                                                    >
                                                        {processing ? (
                                                            <>
                                                                <span className="loading loading-spinner loading-sm"></span>
                                                                Salvando...
                                                            </>
                                                        ) : configuracoes.senha_laboratorio_existe ? (
                                                            'Salvar Nova Senha'
                                                        ) : (
                                                            'Configurar Senha'
                                                        )}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Outras configurações futuras podem ser adicionadas aqui */}
                            <div className="divider text-base-content/30"></div>

                            <div className="alert alert-info bg-info/10 border-info/20">
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
                                <div>
                                    <h4 className="text-info font-bold">
                                        Informação importante
                                    </h4>
                                    <div className="text-base-content/80 text-sm">
                                        A senha do laboratório é utilizada
                                        durante o processo de cadastro de novos
                                        colaboradores. Certifique-se de
                                        comunicar a nova senha para os
                                        responsáveis pelo recrutamento.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
