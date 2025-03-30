import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import React, { useState } from 'react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Colaborador {
    id: number;
    name: string;
    email: string;
    linkedin?: string;
    github?: string;
    figma?: string;
    foto?: string;
    area_atuacao?: string;
    tecnologias?: string;
    created_at: string;
    tem_projeto: boolean;
}

interface PaginatedColaboradores {
    current_page: number;
    data: Colaborador[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface IndexProps {
    colaboradores: PaginatedColaboradores;
}

export default function Index({ colaboradores }: IndexProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('colaboradores.index'),
            { search: searchTerm },
            { preserveState: true },
        );
    };

    const handleFilterChange = (key: string, value: any) => {
        // Inertia.get(
        //     route('colaboradores.index'),
        //     {
        //         ...filters,
        //         [key]: value,
        //     },
        //     { preserveState: true },
        // );
    };

    return (
        <Authenticated header="Colaboradores">
            <Head title="Colaboradores" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 bg-white p-6">
                            {/* Header with search and actions */}
                            <div className="mb-6 sm:flex sm:items-center sm:justify-between">
                                <div className="flex items-center">
                                    <form
                                        onSubmit={handleSearch}
                                        className="flex items-center"
                                    >
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                {/* <SearchIcon className="h-5 w-5 text-gray-400" /> */}
                                            </div>
                                            <input
                                                type="text"
                                                placeholder="Buscar colaborador..."
                                                className="rounded-md border border-gray-300 py-2 pl-10 pr-4 focus:border-indigo-500 focus:ring-indigo-500"
                                                value={searchTerm}
                                                onChange={(e) =>
                                                    setSearchTerm(
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                        <button
                                            type="submit"
                                            className="ml-2 rounded-md bg-indigo-600 px-4 py-2 text-white"
                                        >
                                            Buscar
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowFilters(!showFilters)
                                            }
                                            className="ml-2 flex items-center rounded-md border border-gray-300 px-3 py-2"
                                        >
                                            {/* <FilterIcon className="mr-1 h-5 w-5 text-gray-500" /> */}
                                            Filtros
                                        </button>
                                    </form>
                                </div>

                                <div className="mt-4 sm:mt-0">
                                    <a
                                        // href={route('colaboradores.create')}
                                        className="focus:shadow-outline-indigo inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:border-indigo-900 focus:outline-none active:bg-indigo-900"
                                    >
                                        {/* <PlusIcon className="mr-1 h-4 w-4" /> */}
                                        Novo Colaborador
                                    </a>
                                </div>
                            </div>

                            {/* Filters */}
                            {showFilters && (
                                <div className="mb-6 rounded-md bg-gray-50 p-4">
                                    <h3 className="mb-3 text-sm font-medium text-gray-700">
                                        Filtros
                                    </h3>
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-700">
                                                Área de Atuação
                                            </label>
                                            <select
                                                className="w-full rounded-md border-gray-300 shadow-sm"
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        'area_atuacao',
                                                        e.target.value,
                                                    )
                                                }
                                            >
                                                <option value="">
                                                    Todas as áreas
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label className="mb-1 block text-sm font-medium text-gray-700">
                                                Status de Projeto
                                            </label>
                                            <select
                                                className="w-full rounded-md border-gray-300 shadow-sm"
                                                onChange={(e) =>
                                                    handleFilterChange(
                                                        'sem_projeto',
                                                        e.target.value ===
                                                            'sem',
                                                    )
                                                }
                                            >
                                                <option value="todos">
                                                    Todos os colaboradores
                                                </option>
                                                <option value="sem">
                                                    Sem projeto
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Foto
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Nome
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Área de Atuação
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Tecnologias
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Status
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Links
                                            </th>
                                            <th
                                                scope="col"
                                                className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Ações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200 bg-white">
                                        {colaboradores.data.length > 0 ? (
                                            colaboradores.data.map(
                                                (colaborador) => (
                                                    <tr key={colaborador.id}>
                                                        <td className="whitespace-nowrap px-6 py-4">
                                                            {colaborador.foto ? (
                                                                <img
                                                                    src={
                                                                        colaborador.foto
                                                                    }
                                                                    alt={`Foto de ${colaborador.name}`}
                                                                    className="h-10 w-10 rounded-full object-cover"
                                                                />
                                                            ) : (
                                                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200">
                                                                    <span className="font-medium text-gray-500">
                                                                        {colaborador.name
                                                                            .charAt(
                                                                                0,
                                                                            )
                                                                            .toUpperCase()}
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </td>
                                                        <td className="whitespace-nowrap px-6 py-4">
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {
                                                                    colaborador.name
                                                                }
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {
                                                                    colaborador.email
                                                                }
                                                            </div>
                                                        </td>
                                                        <td className="whitespace-nowrap px-6 py-4">
                                                            <div className="text-sm text-gray-900">
                                                                {colaborador.area_atuacao ||
                                                                    '-'}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4">
                                                            <div className="text-sm text-gray-900">
                                                                {colaborador.tecnologias
                                                                    ? colaborador.tecnologias
                                                                          .split(
                                                                              ',',
                                                                          )
                                                                          .map(
                                                                              (
                                                                                  tech,
                                                                                  index,
                                                                              ) => (
                                                                                  <span
                                                                                      key={
                                                                                          index
                                                                                      }
                                                                                      className="mb-1 mr-1 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800"
                                                                                  >
                                                                                      {tech.trim()}
                                                                                  </span>
                                                                              ),
                                                                          )
                                                                    : '-'}
                                                            </div>
                                                        </td>
                                                        <td className="whitespace-nowrap px-6 py-4">
                                                            {colaborador.tem_projeto ? (
                                                                <span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                                    Em projeto
                                                                </span>
                                                            ) : (
                                                                <span className="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                                    Sem projeto
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                            <div className="flex space-x-2">
                                                                {colaborador.linkedin && (
                                                                    <a
                                                                        href={
                                                                            colaborador.linkedin
                                                                        }
                                                                        target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        className="text-indigo-600 hover:text-indigo-900"
                                                                    >
                                                                        LinkedIn
                                                                    </a>
                                                                )}
                                                                {colaborador.github && (
                                                                    <a
                                                                        href={
                                                                            colaborador.github
                                                                        }
                                                                        target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        className="text-indigo-600 hover:text-indigo-900"
                                                                    >
                                                                        GitHub
                                                                    </a>
                                                                )}
                                                                {colaborador.figma && (
                                                                    <a
                                                                        href={
                                                                            colaborador.figma
                                                                        }
                                                                        target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        className="text-indigo-600 hover:text-indigo-900"
                                                                    >
                                                                        Figma
                                                                    </a>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                                            <a
                                                                // href={route(
                                                                //     'colaboradores.show',
                                                                //     colaborador.id,
                                                                // )}
                                                                className="mr-3 text-indigo-600 hover:text-indigo-900"
                                                            >
                                                                Ver
                                                            </a>
                                                            <a
                                                                // href={route(
                                                                //     'colaboradores.edit',
                                                                //     colaborador.id,
                                                                // )}
                                                                className="text-indigo-600 hover:text-indigo-900"
                                                            >
                                                                Editar
                                                            </a>
                                                        </td>
                                                    </tr>
                                                ),
                                            )
                                        ) : (
                                            <tr>
                                                <td
                                                    colSpan={7}
                                                    className="px-6 py-4 text-center text-gray-500"
                                                >
                                                    Nenhum colaborador
                                                    encontrado.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {/* {colaboradores.length > 0 && (
                                <div className="mt-6">
                                    <Pagination links={colaboradores.links} />
                                </div>
                            )} */}
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
