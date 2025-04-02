import Paggination, { Paginated } from '@/Components/Paggination';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import React, { useState } from 'react';

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

interface IndexProps {
    colaboradores: Paginated<Colaborador>;
}

// TODO ARRUMAR O DARK MODE
export default function Index({ colaboradores }: IndexProps) {
    const [searchTerm, setSearchTerm] = useState('');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('colaboradores.index'),
            { search: searchTerm },
            { preserveState: true },
        );
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
                                    </form>
                                </div>
                            </div>

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
                                                                        'https://robohash.org/set1/' +
                                                                        colaborador.name +
                                                                        '.png'
                                                                    }
                                                                    alt={`Foto de ${colaborador.name}`}
                                                                    className="h-15 w-15 rounded-full object-cover"
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
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
