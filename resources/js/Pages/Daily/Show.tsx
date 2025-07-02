import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

interface Daily {
    id: string;
    data: string;
    ontem: string;
    observacoes: string | null;
    hoje: string;
    carga_horaria: number;
    created_at: string;
    updated_at: string;
    usuario_projeto: {
        projeto: {
            nome: string;
        };
        funcao: string;
    };
}

interface Props extends PageProps {
    daily: Daily;
}

export default function Show({ daily }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const formatDateTime = (dateString: string) => {
        return new Date(dateString).toLocaleString('pt-BR');
    };

    const canEdit = () => {
        return (
            new Date(daily.data).toDateString() === new Date().toDateString()
        );
    };

    const handleDelete = () => {
        if (confirm('Tem certeza que deseja excluir este daily?')) {
            router.delete(route('daily.destroy', daily.id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h2 className="text-base-content text-xl font-semibold">
                            Daily
                        </h2>
                        <p className="text-base-content/60 mt-0.5 text-sm">
                            {formatDate(daily.data)} •{' '}
                            {daily.usuario_projeto.projeto.nome}
                        </p>
                    </div>
                    <div className="flex gap-2">
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
                        {canEdit() && (
                            <Link
                                href={route('daily.edit', daily.id)}
                                className="btn btn-primary btn-sm gap-2"
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
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                                Editar
                            </Link>
                        )}
                    </div>
                </div>
            }
        >
            <Head title={`Daily • ${formatDate(daily.data)}`} />

            <div className="py-4">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    {/* Header com informações */}
                    <div className="card bg-base-100 mb-6 shadow-sm">
                        <div className="card-body p-4">
                            <div className="flex flex-wrap items-center gap-2">
                                <div className="badge badge-primary">
                                    {formatDate(daily.data)}
                                </div>
                                <div className="badge badge-outline">
                                    {daily.usuario_projeto.projeto.nome}
                                </div>
                                <div className="badge badge-outline">
                                    {daily.usuario_projeto.funcao}
                                </div>
                                <div className="badge badge-ghost">
                                    {daily.carga_horaria}h
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Conteúdo principal */}
                    <div className="space-y-6">
                        <div className="grid gap-6 lg:grid-cols-2">
                            {/* Ontem */}
                            <div className="card bg-base-100 shadow-sm">
                                <div className="card-body p-4">
                                    <div className="mb-3 flex items-center gap-2">
                                        <div className="bg-warning h-3 w-3 rounded-full"></div>
                                        <h3 className="text-base-content font-medium">
                                            O que fiz ontem
                                        </h3>
                                    </div>
                                    <div className="bg-base-200 rounded-lg p-4">
                                        <p className="text-base-content text-sm leading-relaxed whitespace-pre-wrap">
                                            {daily.ontem}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Hoje */}
                            <div className="card bg-base-100 shadow-sm">
                                <div className="card-body p-4">
                                    <div className="mb-3 flex items-center gap-2">
                                        <div className="bg-success h-3 w-3 rounded-full"></div>
                                        <h3 className="text-base-content font-medium">
                                            O que farei hoje
                                        </h3>
                                    </div>
                                    <div className="bg-base-200 rounded-lg p-4">
                                        <p className="text-base-content text-sm leading-relaxed whitespace-pre-wrap">
                                            {daily.hoje}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Observações */}
                        {daily.observacoes && (
                            <div className="card bg-base-100 shadow-sm">
                                <div className="card-body p-4">
                                    <div className="mb-3 flex items-center gap-2">
                                        <div className="bg-info h-3 w-3 rounded-full"></div>
                                        <h3 className="text-base-content font-medium">
                                            Observações
                                        </h3>
                                    </div>
                                    <div className="bg-base-200 rounded-lg p-4">
                                        <p className="text-base-content text-sm leading-relaxed whitespace-pre-wrap">
                                            {daily.observacoes}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Metadados */}
                        <div className="card bg-base-100 shadow-sm">
                            <div className="card-body p-4">
                                <div className="text-base-content/60 flex items-center justify-between text-xs">
                                    <span>
                                        Criado em:{' '}
                                        {formatDateTime(daily.created_at)}
                                    </span>
                                    {daily.updated_at !== daily.created_at && (
                                        <span>
                                            Atualizado em:{' '}
                                            {formatDateTime(daily.updated_at)}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Ações */}
                        {canEdit() && (
                            <div className="flex justify-end gap-3">
                                <button
                                    onClick={handleDelete}
                                    className="btn btn-error btn-outline btn-sm gap-2"
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
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                    Excluir
                                </button>
                                <Link
                                    href={route('daily.edit', daily.id)}
                                    className="btn btn-primary btn-sm gap-2"
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
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                    Editar
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
