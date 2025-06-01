import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import React, { useState } from 'react';

interface ConfiguracaoIndexProps {
    configuracoes: {
        senha_laboratorio_existe: boolean;
        token_laboratorio?: string;
    };
}

export default function Index({ configuracoes }: ConfiguracaoIndexProps) {
    const [showForm, setShowForm] = useState(false);
    const [copied, setCopied] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        novo_token: '',
    });

    const copyToClipboard = async (text: string) => {
        try {
            await navigator.clipboard.writeText(text);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (err) {
            console.error('Erro ao copiar:', err);
        }
    };

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
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body p-8">
                            {/* Seção de Senha do Laboratório */}
                            <div className="mb-10">
                                <div className="mb-8">
                                    <h3 className="text-base-content mb-3 text-xl font-semibold">
                                        Token do Laboratório
                                    </h3>
                                    <p className="text-base-content/70 text-base leading-relaxed">
                                        Gerencie o token utilizado para validar
                                        novos cadastros no laboratório.
                                    </p>
                                </div>

                                {!showForm ? (
                                    <div className="bg-base-200/30 border-base-300/50 rounded-lg border p-6">
                                        <div className="mb-6 flex items-center justify-between">
                                            <div className="flex items-center gap-4">
                                                <div
                                                    className={`badge gap-2 ${configuracoes.senha_laboratorio_existe ? 'badge-success' : 'badge-warning'} badge-lg`}
                                                >
                                                    {configuracoes.senha_laboratorio_existe ? (
                                                        <svg
                                                            className="h-4 w-4"
                                                            fill="currentColor"
                                                            viewBox="0 0 20 20"
                                                        >
                                                            <path
                                                                fillRule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clipRule="evenodd"
                                                            />
                                                        </svg>
                                                    ) : (
                                                        <svg
                                                            className="h-4 w-4"
                                                            fill="currentColor"
                                                            viewBox="0 0 20 20"
                                                        >
                                                            <path
                                                                fillRule="evenodd"
                                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                clipRule="evenodd"
                                                            />
                                                        </svg>
                                                    )}
                                                    {configuracoes.senha_laboratorio_existe
                                                        ? 'Configurado'
                                                        : 'Não configurado'}
                                                </div>
                                                <span className="text-base-content/60 text-sm">
                                                    {configuracoes.senha_laboratorio_existe
                                                        ? 'O token do laboratório está ativo'
                                                        : 'Configure um token para validar novos cadastros'}
                                                </span>
                                            </div>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setShowForm(true)
                                                }
                                                className="btn btn-primary"
                                            >
                                                {configuracoes.senha_laboratorio_existe
                                                    ? 'Alterar Token'
                                                    : 'Configurar Token'}
                                            </button>
                                        </div>

                                        {/* Seção de exibição do token atual */}
                                        {configuracoes.senha_laboratorio_existe &&
                                            configuracoes.token_laboratorio && (
                                                <div className="bg-base-100 border-base-300/50 rounded-lg border p-4">
                                                    <label className="label pb-2">
                                                        <span className="label-text text-base-content/70 text-sm font-medium">
                                                            Token Atual
                                                        </span>
                                                    </label>
                                                    <div className="flex items-center gap-3">
                                                        <div className="bg-base-200/50 border-base-300/30 flex-1 rounded-lg border p-3 font-mono text-sm">
                                                            {
                                                                configuracoes.token_laboratorio
                                                            }
                                                        </div>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                copyToClipboard(
                                                                    configuracoes.token_laboratorio!,
                                                                )
                                                            }
                                                            className={`btn btn-square btn-sm ${copied ? 'btn-success' : 'btn-ghost'}`}
                                                            title="Copiar token"
                                                        >
                                                            {copied ? (
                                                                <svg
                                                                    className="h-4 w-4"
                                                                    fill="currentColor"
                                                                    viewBox="0 0 20 20"
                                                                >
                                                                    <path
                                                                        fillRule="evenodd"
                                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                        clipRule="evenodd"
                                                                    />
                                                                </svg>
                                                            ) : (
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
                                                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                                                    />
                                                                </svg>
                                                            )}
                                                        </button>
                                                    </div>
                                                </div>
                                            )}
                                    </div>
                                ) : (
                                    <div className="card bg-base-200/50 border-base-300/80 border shadow-sm">
                                        <div className="card-body p-8">
                                            <form onSubmit={handleSubmit}>
                                                <div className="mb-8">
                                                    <h4 className="text-base-content mb-2 text-lg font-medium">
                                                        {configuracoes.senha_laboratorio_existe
                                                            ? 'Alterar Token do Laboratório'
                                                            : 'Configurar Token do Laboratório'}
                                                    </h4>
                                                    <p className="text-base-content/60 text-sm">
                                                        {configuracoes.senha_laboratorio_existe
                                                            ? 'Digite o novo token desejado'
                                                            : 'Defina um token para validar novos cadastros'}
                                                    </p>
                                                </div>

                                                <div className="space-y-8">
                                                    <div className="form-control w-full">
                                                        <label className="label pb-3">
                                                            <span className="label-text text-base font-medium">
                                                                {configuracoes.senha_laboratorio_existe
                                                                    ? 'Novo Token'
                                                                    : 'Token do Laboratório'}
                                                            </span>
                                                        </label>
                                                        <input
                                                            type="text"
                                                            value={
                                                                data.novo_token
                                                            }
                                                            onChange={(e) =>
                                                                setData(
                                                                    'novo_token',
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            className={`input input-bordered bg-base-100 h-12 w-full font-mono text-base ${errors.novo_token ? 'input-error' : 'focus:input-primary'}`}
                                                            placeholder={
                                                                configuracoes.senha_laboratorio_existe
                                                                    ? 'Digite o novo token'
                                                                    : 'Digite o token do laboratório'
                                                            }
                                                            required
                                                        />
                                                        {errors.novo_token && (
                                                            <label className="label pt-2">
                                                                <span className="label-text-alt text-error font-medium">
                                                                    {
                                                                        errors.novo_token
                                                                    }
                                                                </span>
                                                            </label>
                                                        )}
                                                        <label className="label pt-2">
                                                            <span className="label-text-alt text-base-content/50 text-xs">
                                                                Mínimo de 4
                                                                caracteres. Use
                                                                um token fácil
                                                                de compartilhar
                                                                com novos
                                                                colaboradores.
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div className="border-base-300/50 mt-10 flex justify-end gap-4 border-t pt-10">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setShowForm(false);
                                                            reset();
                                                        }}
                                                        className="btn btn-ghost btn-lg"
                                                        disabled={processing}
                                                    >
                                                        Cancelar
                                                    </button>
                                                    <button
                                                        type="submit"
                                                        disabled={processing}
                                                        className="btn btn-primary btn-lg min-w-48"
                                                    >
                                                        {processing ? (
                                                            <>
                                                                <span className="loading loading-spinner loading-sm"></span>
                                                                Salvando...
                                                            </>
                                                        ) : configuracoes.senha_laboratorio_existe ? (
                                                            'Salvar Novo Token'
                                                        ) : (
                                                            'Configurar Token'
                                                        )}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Divider melhorado */}
                            <div className="divider text-base-content/20 my-12"></div>

                            {/* Alert redesenhado */}
                            <div className="alert bg-info/5 border-info/20 rounded-lg border p-6">
                                <div className="flex gap-4">
                                    <div className="flex-shrink-0">
                                        <svg
                                            className="text-info h-6 w-6"
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
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="text-info mb-2 text-base font-semibold">
                                            Informação importante
                                        </h4>
                                        <p className="text-base-content/70 text-sm leading-relaxed">
                                            O token do laboratório é utilizado
                                            durante o processo de cadastro de
                                            novos colaboradores. Certifique-se
                                            de comunicar o novo token para os
                                            responsáveis pelo recrutamento.
                                        </p>
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
