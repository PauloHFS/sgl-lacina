import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface Daily {
    id: string;
    data: string;
    ontem: string;
    observacoes: string | null;
    hoje: string;
    carga_horaria: number;
    created_at: string;
    usuario_projeto: {
        projeto: {
            nome: string;
        };
        funcao: string;
    };
}

interface PaginatedData {
    data: Daily[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props extends PageProps {
    dailies: PaginatedData;
    filters: {
        projeto_id?: string;
        data_inicio?: string;
        data_fim?: string;
    };
}

export default function Index({ dailies, filters }: Props) {
    const [searchFilters, setSearchFilters] = useState(filters);

    const handleFilter = () => {
        router.get(route('daily.index'), searchFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setSearchFilters({});
        router.get(route('daily.index'));
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR');
    };

    const truncateText = (text: string, maxLength: number = 100) => {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h2 className="text-base-content text-xl font-semibold">
                            Dailies
                        </h2>
                        <p className="text-base-content/60 mt-0.5 text-sm">
                            Acompanhe seu progresso diário
                        </p>
                    </div>
                    <Link
                        href={route('daily.create')}
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
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        Novo Daily
                    </Link>
                </div>
            }
        >
            <Head title="Meus Dailies" />

            <div className="py-4">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Filtros */}
                    <div className="card bg-base-100 mb-6 shadow-sm">
                        <div className="card-body p-4">
                            <div className="mb-3 flex items-center gap-2">
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
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                                    />
                                </svg>
                                <span className="text-base-content text-sm font-medium">
                                    Filtros
                                </span>
                            </div>

                            <div className="grid grid-cols-1 gap-3 md:grid-cols-4">
                                <div className="form-control">
                                    <input
                                        type="date"
                                        className="input input-bordered input-sm"
                                        placeholder="Data início"
                                        value={searchFilters.data_inicio || ''}
                                        onChange={(e) =>
                                            setSearchFilters((prev) => ({
                                                ...prev,
                                                data_inicio: e.target.value,
                                            }))
                                        }
                                    />
                                </div>

                                <div className="form-control">
                                    <input
                                        type="date"
                                        className="input input-bordered input-sm"
                                        placeholder="Data fim"
                                        value={searchFilters.data_fim || ''}
                                        onChange={(e) =>
                                            setSearchFilters((prev) => ({
                                                ...prev,
                                                data_fim: e.target.value,
                                            }))
                                        }
                                    />
                                </div>

                                <div className="form-control md:col-span-2">
                                    <div className="flex gap-2">
                                        <button
                                            className="btn btn-primary btn-sm flex-1"
                                            onClick={handleFilter}
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
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                                />
                                            </svg>
                                            Filtrar
                                        </button>
                                        <button
                                            className="btn btn-ghost btn-sm"
                                            onClick={clearFilters}
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                            Limpar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Lista de Dailies */}
                    <div className="space-y-4">
                        {dailies.data.length === 0 ? (
                            <div className="card bg-base-100 shadow-sm">
                                <div className="card-body py-12 text-center">
                                    <div className="bg-base-200 mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full">
                                        <svg
                                            className="text-base-content/50 h-8 w-8"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1.5}
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                    </div>
                                    <h3 className="text-base-content mb-2 text-lg font-semibold">
                                        Nenhum daily encontrado
                                    </h3>
                                    <p className="text-base-content/60 mx-auto mb-6 max-w-md">
                                        Comece registrando seu primeiro daily
                                        para acompanhar seu progresso.
                                    </p>
                                    <Link
                                        href={route('daily.create')}
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
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                        Registrar Primeiro Daily
                                    </Link>
                                </div>
                            </div>
                        ) : (
                            <>
                                <div className="mb-4 flex items-center justify-between">
                                    <span className="text-base-content/60 text-sm">
                                        {dailies.total}{' '}
                                        {dailies.total === 1
                                            ? 'daily'
                                            : 'dailies'}{' '}
                                        encontrado
                                        {dailies.total === 1 ? '' : 's'}
                                    </span>
                                    <span className="text-base-content/60 text-sm">
                                        Página {dailies.current_page} de{' '}
                                        {dailies.last_page}
                                    </span>
                                </div>

                                {dailies.data.map((daily) => (
                                    <div
                                        key={daily.id}
                                        className="card bg-base-100 border-base-200 border shadow-sm transition-shadow hover:shadow-md"
                                    >
                                        <div className="card-body p-4">
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1">
                                                    {/* Header */}
                                                    <div className="mb-3 flex flex-wrap items-center gap-2">
                                                        <div className="badge badge-primary badge-sm">
                                                            {formatDate(
                                                                daily.data,
                                                            )}
                                                        </div>
                                                        <div className="badge badge-outline badge-sm">
                                                            {
                                                                daily
                                                                    .usuario_projeto
                                                                    .projeto
                                                                    .nome
                                                            }
                                                        </div>
                                                        <div className="badge badge-ghost badge-sm">
                                                            {
                                                                daily.carga_horaria
                                                            }
                                                            h
                                                        </div>
                                                    </div>

                                                    {/* Conteúdo */}
                                                    <div className="grid gap-4 text-sm lg:grid-cols-2">
                                                        <div>
                                                            <div className="mb-1 flex items-center gap-1">
                                                                <div className="bg-warning h-2 w-2 rounded-full"></div>
                                                                <span className="text-base-content/80 font-medium">
                                                                    Ontem
                                                                </span>
                                                            </div>
                                                            <p className="text-base-content/70 leading-relaxed">
                                                                {truncateText(
                                                                    daily.ontem,
                                                                    120,
                                                                )}
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <div className="mb-1 flex items-center gap-1">
                                                                <div className="bg-success h-2 w-2 rounded-full"></div>
                                                                <span className="text-base-content/80 font-medium">
                                                                    Hoje
                                                                </span>
                                                            </div>
                                                            <p className="text-base-content/70 leading-relaxed">
                                                                {truncateText(
                                                                    daily.hoje,
                                                                    120,
                                                                )}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    {/* Observações */}
                                                    {daily.observacoes && (
                                                        <div className="border-base-200 mt-3 border-t pt-3">
                                                            <div className="mb-1 flex items-center gap-1">
                                                                <div className="bg-info h-2 w-2 rounded-full"></div>
                                                                <span className="text-base-content/80 text-sm font-medium">
                                                                    Observações
                                                                </span>
                                                            </div>
                                                            <p className="text-base-content/70 text-sm leading-relaxed">
                                                                {truncateText(
                                                                    daily.observacoes,
                                                                    150,
                                                                )}
                                                            </p>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Menu de ações */}
                                                <div className="dropdown dropdown-end">
                                                    <label
                                                        tabIndex={0}
                                                        className="btn btn-ghost btn-xs btn-circle"
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
                                                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                                                            />
                                                        </svg>
                                                    </label>
                                                    <ul
                                                        tabIndex={0}
                                                        className="dropdown-content menu bg-base-100 rounded-box border-base-200 z-[1] w-48 border p-2 shadow-lg"
                                                    >
                                                        <li>
                                                            <Link
                                                                href={route(
                                                                    'daily.show',
                                                                    daily.id,
                                                                )}
                                                                className="text-sm"
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
                                                                Visualizar
                                                            </Link>
                                                        </li>
                                                        {new Date(
                                                            daily.data,
                                                        ).toDateString() ===
                                                            new Date().toDateString() && (
                                                            <>
                                                                <li>
                                                                    <Link
                                                                        href={route(
                                                                            'daily.edit',
                                                                            daily.id,
                                                                        )}
                                                                        className="text-sm"
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
                                                                        Editar
                                                                    </Link>
                                                                </li>
                                                                <div className="divider my-1"></div>
                                                                <li>
                                                                    <button
                                                                        className="text-error text-sm"
                                                                        onClick={() => {
                                                                            if (
                                                                                confirm(
                                                                                    'Tem certeza que deseja excluir este daily?',
                                                                                )
                                                                            ) {
                                                                                router.delete(
                                                                                    route(
                                                                                        'daily.destroy',
                                                                                        daily.id,
                                                                                    ),
                                                                                );
                                                                            }
                                                                        }}
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
                                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                                            />
                                                                        </svg>
                                                                        Excluir
                                                                    </button>
                                                                </li>
                                                            </>
                                                        )}
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}

                                {/* Paginação */}
                                {dailies.last_page > 1 && (
                                    <div className="flex flex-col items-center justify-between gap-4 pt-4 sm:flex-row">
                                        <div className="text-base-content/60 text-sm">
                                            Mostrando{' '}
                                            {(dailies.current_page - 1) *
                                                dailies.per_page +
                                                1}{' '}
                                            -{' '}
                                            {Math.min(
                                                dailies.current_page *
                                                    dailies.per_page,
                                                dailies.total,
                                            )}{' '}
                                            de {dailies.total}
                                        </div>
                                        <div className="join">
                                            {dailies.links.map(
                                                (link, index) => (
                                                    <button
                                                        key={index}
                                                        className={`join-item btn btn-sm ${
                                                            link.active
                                                                ? 'btn-primary'
                                                                : 'btn-ghost'
                                                        } ${!link.url ? 'btn-disabled' : ''}`}
                                                        onClick={() =>
                                                            link.url &&
                                                            router.get(link.url)
                                                        }
                                                        disabled={!link.url}
                                                        dangerouslySetInnerHTML={{
                                                            __html: link.label,
                                                        }}
                                                    />
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
