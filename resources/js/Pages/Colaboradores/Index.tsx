import Paggination, { Paginated } from '@/Components/Paggination';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import React, { useState } from 'react';

interface Colaborador {
    id: number;
    name: string;
    email: string;
    linkedin_url?: string;
    github_url?: string;
    figma_url?: string;
    foto_url?: string;
    area_atuacao?: string;
    tecnologias?: string;
    created_at: string;
    tem_projeto: boolean;
}

interface IndexProps {
    colaboradores?: Paginated<Colaborador>;
}

type Tabs = 'vinculo_pendente' | 'aprovacao_pendente' | 'ativos' | 'inativos';

export default function Index({ colaboradores }: IndexProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [activeTab, setActiveTab] = useState<Tabs | null>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('colaboradores.index'),
            { search: searchTerm },
            { preserveState: true },
        );
    };

    const handleTabChange = (tab: Tabs) => {
        setActiveTab(tab);
        router.get(
            route('colaboradores.index'),
            { status: tab },
            { preserveState: true },
        );
    };

    return (
        <Authenticated header="Colaboradores">
            <Head title="Colaboradores" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 rounded-lg shadow-sm">
                        <div className="border-base-200 border-b p-6">
                            {/* Header with search */}
                            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <form
                                    onSubmit={handleSearch}
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
                                            value={searchTerm}
                                            onChange={(e) =>
                                                setSearchTerm(e.target.value)
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
                                <button
                                    role="tab"
                                    className={`tab ${activeTab === 'vinculo_pendente' ? 'tab-active' : ''}`}
                                    onClick={() =>
                                        handleTabChange('vinculo_pendente')
                                    }
                                >
                                    Vínculo Pendente
                                </button>
                                <button
                                    role="tab"
                                    className={`tab ${activeTab === 'aprovacao_pendente' ? 'tab-active' : ''}`}
                                    onClick={() =>
                                        handleTabChange('aprovacao_pendente')
                                    }
                                >
                                    Aprovação Pendente
                                </button>
                                <button
                                    role="tab"
                                    className={`tab ${activeTab === 'ativos' ? 'tab-active' : ''}`}
                                    onClick={() => handleTabChange('ativos')}
                                >
                                    Ativos
                                </button>
                                <button
                                    role="tab"
                                    className={`tab ${activeTab === 'inativos' ? 'tab-active' : ''}`}
                                    onClick={() => handleTabChange('inativos')}
                                >
                                    Inativos
                                </button>
                            </div>

                            {activeTab === null ? (
                                <div className="text-base-content/60 mb-4 text-sm">
                                    Selecione uma aba para filtrar os
                                    colaboradores.
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="table-zebra table">
                                        <thead>
                                            <tr>
                                                <th>Foto</th>
                                                <th>Nome</th>
                                                <th>Email</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {colaboradores &&
                                            colaboradores.data.length > 0 ? (
                                                colaboradores.data.map(
                                                    (colaborador) => (
                                                        <tr
                                                            key={colaborador.id}
                                                        >
                                                            <td>
                                                                <div className="avatar">
                                                                    <div className="h-12 w-12 rounded-full">
                                                                        <img
                                                                            src={
                                                                                colaborador.foto_url
                                                                                    ? colaborador.foto_url
                                                                                    : `https://robohash.org/set1/${colaborador.name}.png`
                                                                            }
                                                                            alt={`Foto de ${colaborador.name}`}
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span className="text-base-content font-medium">
                                                                    {
                                                                        colaborador.name
                                                                    }
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span className="text-base-content/70">
                                                                    {
                                                                        colaborador.email
                                                                    }
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a
                                                                    href={route(
                                                                        'colaboradores.show',
                                                                        colaborador.id,
                                                                    )}
                                                                    className="btn btn-sm btn-outline btn-primary"
                                                                >
                                                                    Ver
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    ),
                                                )
                                            ) : (
                                                <tr>
                                                    <td
                                                        colSpan={4}
                                                        className="text-base-content/60 text-center"
                                                    >
                                                        Nenhum colaborador
                                                        encontrado.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            )}

                            {/* Pagination */}
                            {colaboradores && (
                                <div className="mt-6 flex justify-center">
                                    <Paggination
                                        paginated={colaboradores}
                                        onPageChange={(page) =>
                                            router.get(
                                                route('colaboradores.index'),
                                                { page },
                                                { preserveState: true },
                                            )
                                        }
                                        preserveScroll={true}
                                        preserveState={true}
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
