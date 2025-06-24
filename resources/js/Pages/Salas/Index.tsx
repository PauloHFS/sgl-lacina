import { Paginated } from '@/Components/Paggination';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Sala } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import React, { useRef, useState } from 'react';

interface IndexProps {
    salas: Paginated<Sala>;
    canCreate: boolean;
    canEdit: boolean;
    canDelete: boolean;
    filters: {
        search?: string;
    };
}

export default function Index({
    salas,
    canCreate,
    canEdit,
    canDelete,
    filters,
}: IndexProps) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [salaToDelete, setSalaToDelete] = useState<Sala | null>(null);
    const deleteModalRef = useRef<HTMLDialogElement>(null);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route('salas.index'),
            { search: searchTerm },
            { preserveState: true },
        );
    };

    const openDeleteModal = (sala: Sala) => {
        setSalaToDelete(sala);
        deleteModalRef.current?.showModal();
    };

    const handleDelete = () => {
        if (salaToDelete) {
            router.delete(route('salas.destroy', salaToDelete.id), {
                preserveScroll: true,
                onSuccess: () => {
                    deleteModalRef.current?.close();
                    setSalaToDelete(null);
                },
            });
        }
    };

    return (
        <Authenticated>
            <Head title="Salas" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 rounded-lg shadow-sm">
                        <div className="border-base-200 border-b p-6">
                            {/* Header */}
                            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h1 className="text-base-content text-2xl font-bold">
                                        Salas
                                    </h1>
                                    <p className="text-base-content/70 text-sm">
                                        Gerencie as salas do laboratório
                                    </p>
                                </div>

                                {canCreate && (
                                    <Link
                                        href={route('salas.create')}
                                        className="btn btn-primary"
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
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                        Nova Sala
                                    </Link>
                                )}
                            </div>

                            {/* Search */}
                            <div className="mb-6">
                                <form
                                    onSubmit={handleSearch}
                                    className="flex max-w-md items-center gap-2"
                                >
                                    <label className="input input-bordered flex flex-1 items-center gap-2">
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
                                            placeholder="Buscar salas..."
                                            value={searchTerm}
                                            onChange={(e) =>
                                                setSearchTerm(e.target.value)
                                            }
                                        />
                                    </label>
                                    <button
                                        type="submit"
                                        className="btn btn-outline"
                                    >
                                        Buscar
                                    </button>
                                </form>
                            </div>

                            {/* Stats */}
                            <div className="stats mb-6 shadow">
                                <div className="stat">
                                    <div className="stat-title">
                                        Total de Salas
                                    </div>
                                    <div className="stat-value text-primary">
                                        {salas.total}
                                    </div>
                                </div>
                                <div className="stat">
                                    <div className="stat-title">
                                        Salas na Página
                                    </div>
                                    <div className="stat-value text-secondary">
                                        {salas.data.length}
                                    </div>
                                </div>
                            </div>

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <table className="table-zebra table">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Descrição</th>
                                            <th>Status</th>
                                            <th>Baias</th>
                                            <th>Data de Criação</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {salas.data.length > 0 ? (
                                            salas.data.map((sala) => (
                                                <tr key={sala.id}>
                                                    <td>
                                                        <div className="text-base-content font-medium">
                                                            {sala.nome}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div className="text-base-content/70 max-w-xs truncate">
                                                            {sala.descricao ||
                                                                '-'}
                                                        </div>
                                                    </td>
                                                    <td>
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
                                                    </td>
                                                    <td>
                                                        <div className="flex items-center gap-2">
                                                            <span className="badge badge-outline">
                                                                {sala.baias
                                                                    ?.length ??
                                                                    0}{' '}
                                                                baias
                                                            </span>
                                                            {(sala.baias
                                                                ?.length ?? 0) >
                                                                0 && (
                                                                <div className="text-base-content/60 text-xs">
                                                                    (
                                                                    {sala.baias?.filter(
                                                                        (b) =>
                                                                            b.ativa,
                                                                    ).length ??
                                                                        0}{' '}
                                                                    ativas)
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div className="text-base-content/70 text-sm">
                                                            {sala.created_at
                                                                ? new Date(
                                                                      sala.created_at,
                                                                  ).toLocaleDateString(
                                                                      'pt-BR',
                                                                  )
                                                                : '-'}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div className="flex items-center gap-2">
                                                            <Link
                                                                href={route(
                                                                    'salas.show',
                                                                    sala.id,
                                                                )}
                                                                className="btn btn-sm btn-outline btn-info"
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
                                                                Ver
                                                            </Link>

                                                            {canEdit && (
                                                                <Link
                                                                    href={route(
                                                                        'salas.edit',
                                                                        sala.id,
                                                                    )}
                                                                    className="btn btn-sm btn-outline btn-warning"
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
                                                                            strokeWidth={
                                                                                2
                                                                            }
                                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                                        />
                                                                    </svg>
                                                                    Editar
                                                                </Link>
                                                            )}
                                                            {canDelete && (
                                                                <button
                                                                    onClick={() =>
                                                                        openDeleteModal(
                                                                            sala,
                                                                        )
                                                                    }
                                                                    className="btn btn-ghost btn-xs text-error"
                                                                >
                                                                    Excluir
                                                                </button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td
                                                    colSpan={6}
                                                    className="text-base-content/60 py-8 text-center"
                                                >
                                                    <div className="flex flex-col items-center gap-2">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-12 w-12 opacity-30"
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
                                                        <div>
                                                            <p className="font-medium">
                                                                Nenhuma sala
                                                                encontrada
                                                            </p>
                                                            <p className="text-sm">
                                                                {canCreate ? (
                                                                    <>
                                                                        Comece
                                                                        criando
                                                                        uma nova
                                                                        sala{' '}
                                                                        <Link
                                                                            href={route(
                                                                                'salas.create',
                                                                            )}
                                                                            className="link link-primary"
                                                                        >
                                                                            clique
                                                                            aqui
                                                                        </Link>
                                                                    </>
                                                                ) : (
                                                                    'Não há salas cadastradas no momento.'
                                                                )}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <dialog ref={deleteModalRef} id="delete_modal" className="modal">
                <div className="modal-box">
                    <h3 className="text-lg font-bold">
                        Confirmar Exclusão de Sala
                    </h3>
                    <p className="py-4">
                        Você tem certeza que deseja excluir a sala{' '}
                        <span className="text-error font-bold">
                            {salaToDelete?.nome}
                        </span>
                        ?
                    </p>
                    <div className="prose prose-sm max-w-none">
                        <p>
                            Esta ação é <strong>irreversível</strong> e
                            resultará no seguinte:
                        </p>
                        <ul>
                            <li>
                                Todas as baias associadas a esta sala serão
                                permanentemente removidas.
                            </li>
                            <li>
                                Vínculos de equipamentos com as baias desta sala
                                serão perdidos.
                            </li>
                            <li>
                                O histórico de alocação de usuários a estas
                                baias será perdido.
                            </li>
                        </ul>
                    </div>
                    <div className="modal-action">
                        <form method="dialog">
                            <button className="btn">Cancelar</button>
                        </form>
                        <button
                            className="btn btn-error"
                            onClick={handleDelete}
                        >
                            Excluir
                        </button>
                    </div>
                </div>
                <form method="dialog" className="modal-backdrop">
                    <button />
                </form>
            </dialog>
        </Authenticated>
    );
}
