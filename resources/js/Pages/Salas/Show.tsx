import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

interface Baia {
    id: string;
    nome: string;
    descricao?: string;
    ativa: boolean;
    created_at: string;
    updated_at: string;
}

interface Sala {
    id: string;
    nome: string;
    descricao?: string;
    ativa: boolean;
    baias: Baia[];
    created_at: string;
    updated_at: string;
}

interface ShowProps {
    sala: Sala;
    canEdit: boolean;
    canDelete: boolean;
}

export default function Show({ sala, canEdit }: ShowProps) {
    const baiasAtivas = sala.baias.filter((baia) => baia.ativa);
    const baiasInativas = sala.baias.filter((baia) => !baia.ativa);

    return (
        <Authenticated>
            <Head title={`Sala: ${sala.nome}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 rounded-lg shadow-sm">
                        {/* Header */}
                        <div className="border-base-200 border-b p-6">
                            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div className="mb-2 flex items-center gap-3">
                                        <h1 className="text-base-content text-2xl font-bold">
                                            {sala.nome}
                                        </h1>
                                        <div
                                            className={`badge ${
                                                sala.ativa
                                                    ? 'badge-success'
                                                    : 'badge-error'
                                            }`}
                                        >
                                            {sala.ativa ? 'Ativa' : 'Inativa'}
                                        </div>
                                    </div>
                                    <p className="text-base-content/70 text-sm">
                                        Detalhes da sala e suas baias
                                    </p>
                                </div>

                                <div className="flex items-center gap-2">
                                    {canEdit && (
                                        <Link
                                            href={route('salas.edit', sala.id)}
                                            className="btn btn-outline btn-warning"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
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
                                    <Link
                                        href={route('salas.index')}
                                        className="btn btn-outline"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
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
                            </div>

                            {/* Informações da Sala */}
                            <div className="grid gap-6 lg:grid-cols-2">
                                {/* Detalhes da Sala */}
                                <div className="card bg-base-100 border-base-300 border">
                                    <div className="card-body">
                                        <h2 className="card-title text-base-content flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="text-primary h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={2}
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                />
                                            </svg>
                                            Informações da Sala
                                        </h2>

                                        <div className="space-y-3">
                                            <div>
                                                <span className="text-base-content/70 text-sm font-medium">
                                                    Nome:
                                                </span>
                                                <div className="text-base-content font-medium">
                                                    {sala.nome}
                                                </div>
                                            </div>

                                            {sala.descricao && (
                                                <div>
                                                    <span className="text-base-content/70 text-sm font-medium">
                                                        Descrição:
                                                    </span>
                                                    <div className="text-base-content">
                                                        {sala.descricao}
                                                    </div>
                                                </div>
                                            )}

                                            <div>
                                                <span className="text-base-content/70 text-sm font-medium">
                                                    Status:
                                                </span>
                                                <div className="mt-1">
                                                    <div
                                                        className={`badge ${
                                                            sala.ativa
                                                                ? 'badge-success'
                                                                : 'badge-error'
                                                        }`}
                                                    >
                                                        {sala.ativa
                                                            ? 'Ativa'
                                                            : 'Inativa'}
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <span className="text-base-content/70 text-sm font-medium">
                                                    Criada em:
                                                </span>
                                                <div className="text-base-content">
                                                    {new Date(
                                                        sala.created_at,
                                                    ).toLocaleDateString(
                                                        'pt-BR',
                                                        {
                                                            day: '2-digit',
                                                            month: '2-digit',
                                                            year: 'numeric',
                                                            hour: '2-digit',
                                                            minute: '2-digit',
                                                        },
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Estatísticas das Baias */}
                                <div className="card bg-base-100 border-base-300 border">
                                    <div className="card-body">
                                        <h2 className="card-title text-base-content flex items-center gap-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="text-secondary h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={2}
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                                />
                                            </svg>
                                            Estatísticas das Baias
                                        </h2>

                                        <div className="stats stats-vertical">
                                            <div className="stat">
                                                <div className="stat-title">
                                                    Total de Baias
                                                </div>
                                                <div className="stat-value text-primary">
                                                    {sala.baias.length}
                                                </div>
                                                <div className="stat-desc">
                                                    Número total de baias
                                                    cadastradas
                                                </div>
                                            </div>

                                            <div className="stat">
                                                <div className="stat-title">
                                                    Baias Ativas
                                                </div>
                                                <div className="stat-value text-success">
                                                    {baiasAtivas.length}
                                                </div>
                                                <div className="stat-desc">
                                                    Baias disponíveis para uso
                                                </div>
                                            </div>

                                            <div className="stat">
                                                <div className="stat-title">
                                                    Baias Inativas
                                                </div>
                                                <div className="stat-value text-error">
                                                    {baiasInativas.length}
                                                </div>
                                                <div className="stat-desc">
                                                    Baias fora de uso
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Lista de Baias */}
                        <div className="p-6">
                            <div className="mb-4 flex items-center gap-3">
                                <h2 className="text-base-content text-xl font-semibold">
                                    Baias da Sala
                                </h2>
                                <div className="badge badge-outline">
                                    {sala.baias.length}{' '}
                                    {sala.baias.length === 1 ? 'baia' : 'baias'}
                                </div>
                            </div>

                            {sala.baias.length > 0 ? (
                                <div className="space-y-4">
                                    {/* Baias Ativas */}
                                    {baiasAtivas.length > 0 && (
                                        <div>
                                            <h3 className="text-success mb-3 flex items-center gap-2 text-lg font-medium">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    className="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>
                                                Baias Ativas (
                                                {baiasAtivas.length})
                                            </h3>
                                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                                {baiasAtivas.map((baia) => (
                                                    <div
                                                        key={baia.id}
                                                        className="card bg-base-100 border-success/20 border shadow-sm transition-shadow hover:shadow-md"
                                                    >
                                                        <div className="card-body p-4">
                                                            <div className="flex items-center justify-between">
                                                                <h4 className="text-base-content font-medium">
                                                                    {baia.nome}
                                                                </h4>
                                                                <div className="badge badge-success badge-sm">
                                                                    Ativa
                                                                </div>
                                                            </div>

                                                            {baia.descricao && (
                                                                <p className="text-base-content/70 mt-2 text-sm">
                                                                    {
                                                                        baia.descricao
                                                                    }
                                                                </p>
                                                            )}

                                                            <div className="text-base-content/60 mt-2 text-xs">
                                                                Criada em:{' '}
                                                                {new Date(
                                                                    baia.created_at,
                                                                ).toLocaleDateString(
                                                                    'pt-BR',
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    {/* Baias Inativas */}
                                    {baiasInativas.length > 0 && (
                                        <div>
                                            <h3 className="text-error mb-3 flex items-center gap-2 text-lg font-medium">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    className="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M6 18L18 6M6 6l12 12"
                                                    />
                                                </svg>
                                                Baias Inativas (
                                                {baiasInativas.length})
                                            </h3>
                                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                                {baiasInativas.map((baia) => (
                                                    <div
                                                        key={baia.id}
                                                        className="card bg-base-100 border-error/20 border opacity-75 shadow-sm"
                                                    >
                                                        <div className="card-body p-4">
                                                            <div className="flex items-center justify-between">
                                                                <h4 className="text-base-content font-medium">
                                                                    {baia.nome}
                                                                </h4>
                                                                <div className="badge badge-error badge-sm">
                                                                    Inativa
                                                                </div>
                                                            </div>

                                                            {baia.descricao && (
                                                                <p className="text-base-content/70 mt-2 text-sm">
                                                                    {
                                                                        baia.descricao
                                                                    }
                                                                </p>
                                                            )}

                                                            <div className="text-base-content/60 mt-2 text-xs">
                                                                Criada em:{' '}
                                                                {new Date(
                                                                    baia.created_at,
                                                                ).toLocaleDateString(
                                                                    'pt-BR',
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="py-12 text-center">
                                    <div className="flex flex-col items-center gap-4">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="text-base-content/30 h-16 w-16"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                            />
                                        </svg>
                                        <div>
                                            <h3 className="text-base-content text-lg font-medium">
                                                Nenhuma baia cadastrada
                                            </h3>
                                            <p className="text-base-content/70 mt-1 text-sm">
                                                Esta sala ainda não possui baias
                                                cadastradas.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
