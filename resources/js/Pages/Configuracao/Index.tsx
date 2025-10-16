import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import React, { useState } from 'react';
import OrgaoEmissorManager from '@/Components/OrgaoEmissorManager'; // Import the new component
import { OrgaoEmissor } from '@/types'; // Assuming a type definition exists

interface ConfiguracaoIndexProps {

    configuracoes: {
        senha_laboratorio_existe: boolean;
        senha_laboratorio?: string;
    };
    orgaosEmissores: OrgaoEmissor[]; // Add the new prop
}

export default function Index({ configuracoes, orgaosEmissores }: ConfiguracaoIndexProps) { // Destructure the prop
    const [showForm, setShowForm] = useState(false);
    const [copied, setCopied] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        novo_senha: '',
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
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-base-100 shadow sm:rounded-lg">
                        <div className="mb-10">
                            <div className="mb-8">
                                <h3 className="text-base-content mb-3 text-xl font-semibold">
                                    Senha do Laboratório
                                </h3>
                                <p className="text-base-content/70 text-base leading-relaxed">
                                    Gerencie a senha utilizada para validar
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
                                                    ? 'A senha do laboratório está ativo'
                                                    : 'Configure uma senha para validar novos cadastros'}
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
                                                ? 'Alterar Senha'
                                                : 'Configurar Senha'}
                                        </button>
                                    </div>

                                    {configuracoes.senha_laboratorio_existe &&
                                        configuracoes.senha_laboratorio && (
                                            <div className="bg-base-100 border-base-300/50 rounded-lg border p-4">
                                                <label className="label pb-2">
                                                    <span className="label-text text-base-content/70 text-sm font-medium">
                                                        Senha Atual
                                                    </span>
                                                </label>
                                                <div className="flex items-center gap-3">
                                                    <div className="bg-base-200/50 border-base-300/30 flex-1 rounded-lg border p-3 font-mono text-sm">
                                                        {
                                                            configuracoes.senha_laboratorio
                                                        }
                                                    </div>
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            copyToClipboard(
                                                                configuracoes.senha_laboratorio!,
                                                            )
                                                        }
                                                        className={`btn btn-square btn-sm ${copied ? 'btn-success' : 'btn-ghost'}`}
                                                        title="Copiar Senha"
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
                                                        ? 'Alterar a senha do Laboratório'
                                                        : 'Configurar a senha do Laboratório'}
                                                </h4>
                                                <p className="text-base-content/60 text-sm">
                                                    {configuracoes.senha_laboratorio_existe
                                                        ? 'Digite a nova senha desejado'
                                                        : 'Defina uma senha para validar novos cadastros'}
                                                </p>
                                            </div>

                                            <div className="space-y-8">
                                                <div className="form-control w-full">
                                                    <label className="label pb-3">
                                                        <span className="label-text text-base font-medium">
                                                            {configuracoes.senha_laboratorio_existe
                                                                ? 'Novo Senha'
                                                                : 'Senha do Laboratório'}
                                                        </span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={
                                                            data.novo_senha
                                                        }
                                                        onChange={(e) =>
                                                            setData(
                                                                'novo_senha',
                                                                e.target
                                                                    .value,
                                                            )
                                                        }
                                                        className={`input input-bordered bg-base-100 h-12 w-full font-mono text-base ${errors.novo_senha ? 'input-error' : 'focus:input-primary'}`}
                                                        placeholder={
                                                            configuracoes.senha_laboratorio_existe
                                                                ? 'Digite a nova Senha'
                                                                : 'Digite a Senha do laboratório'
                                                        }
                                                        required
                                                    />
                                                    {errors.novo_senha && (
                                                        <label className="label pt-2">
                                                            <span className="label-text-alt text-error font-medium">
                                                                {
                                                                    errors.novo_senha
                                                                }
                                                            </span>
                                                        </label>
                                                    )}
                                                    <label className="label pt-2">
                                                        <span className="label-text-alt text-base-content/50 text-xs">
                                                            Mínimo de 4
                                                            caracteres. Use
                                                            uma senha fácil
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
                    </div>

                    {/* Render the new component here */}
                    <OrgaoEmissorManager orgaos={orgaosEmissores} />

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
