import Paggination, { Paginated } from '@/Components/Paggination';
import { Table, ColumnDefinition } from '@/Components/Table';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { useTable } from '@/hooks/useTable';
import { Head } from '@inertiajs/react';
import React from 'react';

interface Colaborador {
    id: number;
    name: string;
    email: string;
    linkedin_url?: string;
    github_url?: string;
    website_url?: string;
    foto_url?: string;
    area_atuacao?: string;
    tecnologias?: string;
    created_at: string;
    tem_projeto: boolean;
}

interface IndexProps {
    colaboradores: Paginated<Colaborador>;
}

export default function Index({ colaboradores }: IndexProps) {
    const { queryParams, updateQuery } = useTable({
        routeName: 'colaboradores.index',
        initialState: {
            status: 'cadastro_pendente',
        },
    });

    const columns: ColumnDefinition<Colaborador>[] = [
        {
            header: 'Foto',
            accessor: 'foto_url',
            render: (colaborador) => (
                <div className="avatar">
                    <div className="mask mask-squircle h-12 w-12">
                        <img
                            src={
                                colaborador.foto_url
                                    ? colaborador.foto_url
                                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                          colaborador.name,
                                      )}&background=random&color=fff`
                            }
                            alt={`Foto de ${colaborador.name}`}
                        />
                    </div>
                </div>
            ),
        },
        {
            header: 'Nome',
            accessor: 'name',
            render: (colaborador) => (
                <span className="text-base-content font-medium">
                    {colaborador.name}
                </span>
            ),
        },
        {
            header: 'Email',
            accessor: 'email',
            render: (colaborador) => (
                <span className="text-base-content/70">
                    {colaborador.email}
                </span>
            ),
        },
        {
            header: 'Ações',
            accessor: 'id',
            render: (colaborador) => (
                <a
                    href={route('colaboradores.show', colaborador.id)}
                    className="btn btn-sm btn-outline btn-primary"
                >
                    Ver
                </a>
            ),
        },
    ];

    return (
        <Authenticated>
            <Head title="Colaboradores" />
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
                                            placeholder="Buscar colaborador..."
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
                                {[
                                    'cadastro_pendente',
                                    'vinculo_pendente',
                                    'ativos',
                                    'encerrados',
                                ].map((tab) => (
                                    <button
                                        key={tab}
                                        role="tab"
                                        className={`tab ${
                                            queryParams.status === tab
                                                ? 'tab-active'
                                                : ''
                                        }`}
                                        onClick={() =>
                                            updateQuery({ status: tab })
                                        }
                                    >
                                        {tab.replace('_', ' ')}
                                    </button>
                                ))}
                            </div>

                            <Table
                                data={colaboradores}
                                columns={columns}
                                emptyMessage="Nenhum colaborador encontrado."
                            />

                            {/* Pagination */}
                            {colaboradores && (
                                <div className="mt-6 flex justify-center">
                                    <Paggination
                                        paginated={colaboradores}
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
