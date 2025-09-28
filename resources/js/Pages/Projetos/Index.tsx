import Paggination, { Paginated } from '@/Components/Paggination';
import { Table, ColumnDefinition } from '@/Components/Table';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { useTable } from '@/hooks/useTable';
import { Head } from '@inertiajs/react';
import React from 'react';

interface Projeto {
    id: string;
    nome: string;
    cliente: string;
    tipo: string;
    usuarios: { pivot: { status: string } }[];
}

interface IndexProps {
    projetos: Paginated<Projeto>;
}

export default function Index({ projetos }: IndexProps) {
    const { queryParams, updateQuery } = useTable({
        routeName: 'projetos.index',
        initialState: {
            tab: 'todos',
        },
    });

    const columns: ColumnDefinition<Projeto>[] = [
        {
            header: 'Nome',
            accessor: 'nome',
        },
        {
            header: 'Cliente',
            accessor: 'cliente',
        },
        {
            header: 'Tipo',
            accessor: 'tipo',
        },
        {
            header: 'Meu Status',
            accessor: 'id', // dummy accessor
            render: (projeto) => {
                const userPivot = projeto.usuarios[0]?.pivot;
                return userPivot ? (
                    <span className={`badge badge-${userPivot.status === 'APROVADO' ? 'success' : 'warning'}`}>
                        {userPivot.status}
                    </span>
                ) : (
                    <span className="badge badge-ghost">N/A</span>
                );
            },
        },
        {
            header: 'Ações',
            accessor: 'id',
            render: (projeto) => (
                <a
                    href={route('projetos.show', projeto.id)}
                    className="btn btn-sm btn-outline btn-primary"
                >
                    Ver
                </a>
            ),
        },
    ];

    return (
        <Authenticated>
            <Head title="Projetos" />
            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 rounded-lg shadow-sm">
                        <div className="border-base-200 border-b p-6">
                            {/* Header with search */}
                            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <form
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        updateQuery({ search: queryParams.search });
                                    }}
                                    className="flex items-center gap-2"
                                >
                                    <label className="input input-bordered flex items-center gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="h-4 w-4 opacity-70"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z"
                                            />
                                        </svg>
                                        <input
                                            type="text"
                                            className="grow"
                                            placeholder="Buscar projeto..."
                                            value={queryParams.search || ''}
                                            onChange={(e) =>
                                                updateQuery({ search: e.target.value })
                                            }
                                        />
                                    </label>
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                    >
                                        Buscar
                                    </button>
                                </form>
                            </div>

                            {/* Tabs */}
                            <div role="tablist" className="tabs tabs-box mb-6">
                                {['todos', 'colaborador', 'coordenador'].map((tab) => (
                                    <button
                                        key={tab}
                                        role="tab"
                                        className={`tab ${
                                            queryParams.tab === tab
                                                ? 'tab-active'
                                                : ''
                                        }`}
                                        onClick={() =>
                                            updateQuery({ tab })
                                        }
                                    >
                                        {tab.charAt(0).toUpperCase() + tab.slice(1)}
                                    </button>
                                ))}
                            </div>

                            <Table
                                data={projetos}
                                columns={columns}
                                emptyMessage="Nenhum projeto encontrado."
                            />

                            {/* Pagination */}
                            {projetos && (
                                <div className="mt-6 flex justify-center">
                                    <Paggination
                                        paginated={projetos}
                                        onPageChange={(page) =>
                                            updateQuery({ page })
                                        }
                                    />
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}