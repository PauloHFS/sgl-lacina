import { Paginated } from '@/Components/Paggination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Ausencia, PageProps, Projeto } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Filtros {
    projeto_id?: string;
}

interface IndexPageProps extends PageProps {
    ausencias: Paginated<Ausencia>;
    projetosAtivos: Projeto[];
    filtros: Filtros;
}

const formatarData = (data: string): string => {
    return new Date(data).toLocaleDateString('pt-BR');
};

const Index = ({ ausencias, projetosAtivos, filtros }: IndexPageProps) => {
    const [filtroLocal, setFiltroLocal] = useState<Filtros>(filtros);

    const aplicarFiltros: FormEventHandler = (e) => {
        e.preventDefault();

        const params = new URLSearchParams();

        if (filtroLocal.projeto_id)
            params.append('projeto_id', filtroLocal.projeto_id);

        router.get(route('ausencias.index'), Object.fromEntries(params), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const limparFiltros = () => {
        setFiltroLocal({});
        router.get(route('ausencias.index'));
    };

    const excluirAusencias = (id: string) => {
        if (confirm('Tem certeza que deseja excluir esta ausencias?')) {
            router.delete(route('ausencias.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-base-content text-xl leading-tight font-semibold">
                        Ausências
                    </h2>
                    <Link
                        href={route('ausencias.create')}
                        className="btn btn-primary"
                    >
                        Nova Ausência
                    </Link>
                </div>
            }
        >
            <Head title="Ausências" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Filtros */}
                    <div className="card bg-base-200/50 dark:bg-base-200/20 mb-8 shadow-md">
                        <div className="card-body">
                            <h3 className="card-title text-base-content/80 mb-2">
                                Filtros
                            </h3>
                            <form
                                onSubmit={aplicarFiltros}
                                className="grid grid-cols-1 items-end gap-x-4 gap-y-2 sm:grid-cols-2 lg:grid-cols-5"
                            >
                                <div className="form-control w-full">
                                    <label className="label">
                                        <span className="label-text">
                                            Projeto
                                        </span>
                                    </label>
                                    <select
                                        className="select select-bordered w-full"
                                        value={filtroLocal.projeto_id || ''}
                                        onChange={(e) =>
                                            setFiltroLocal((prev) => ({
                                                ...prev,
                                                projeto_id: e.target.value,
                                            }))
                                        }
                                    >
                                        <option value="">
                                            Todos os projetos
                                        </option>
                                        {projetosAtivos.map((projeto) => (
                                            <option
                                                key={projeto.id}
                                                value={projeto.id}
                                            >
                                                {projeto.nome} -{' '}
                                                {projeto.cliente}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="form-control w-full">
                                    <div className="flex gap-2 pt-4 sm:pt-0">
                                        <button
                                            type="submit"
                                            className="btn btn-primary flex-grow"
                                        >
                                            Filtrar
                                        </button>
                                        <button
                                            type="button"
                                            onClick={limparFiltros}
                                            className="btn btn-ghost"
                                        >
                                            Limpar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {/* Lista de Ausências */}
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            {ausencias.data.length === 0 ? (
                                <div className="py-8 text-center">
                                    <p className="mb-4 text-gray-500">
                                        Nenhuma ausência encontrada.
                                    </p>
                                    <Link
                                        href={route('ausencias.create')}
                                        className="btn btn-primary"
                                    >
                                        Registrar Primeira Ausência
                                    </Link>
                                </div>
                            ) : (
                                <>
                                    <div className="overflow-x-auto">
                                        <table className="table">
                                            <thead>
                                                <tr>
                                                    <th>Titulo</th>
                                                    <th>Projeto</th>
                                                    <th>Perido de Ausência</th>
                                                    <th>Status</th>
                                                    <th>Horas a Compensar</th>
                                                    <th>
                                                        Perido De Compensação
                                                    </th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {ausencias.data.map(
                                                    (ausencia) => (
                                                        <tr key={ausencia.id}>
                                                            <td>
                                                                <div>
                                                                    <div className="font-bold">
                                                                        {
                                                                            ausencia.titulo
                                                                        }
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div className="font-bold">
                                                                        {ausencia
                                                                            .projeto
                                                                            ?.nome ||
                                                                            '-'}
                                                                    </div>
                                                                    <div className="text-sm opacity-50">
                                                                        {ausencia
                                                                            .projeto
                                                                            ?.cliente ||
                                                                            ''}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div className="font-bold">
                                                                        {formatarData(
                                                                            ausencia.data_inicio,
                                                                        )}
                                                                        {' - '}
                                                                        {formatarData(
                                                                            ausencia.data_fim,
                                                                        )}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    className={`badge ${
                                                                        {
                                                                            PENDENTE:
                                                                                'badge-warning',
                                                                            APROVADO:
                                                                                'badge-success',
                                                                            REJEITADO:
                                                                                'badge-error',
                                                                        }[ausencia.status]
                                                                    }`}
                                                                >
                                                                    {ausencia.status}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div className="font-bold">
                                                                    {ausencia.horas_a_compensar ||
                                                                        '0'}{' '}
                                                                    horas
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div className="font-bold">
                                                                    {ausencia.compensacao_data_inicio
                                                                        ? formatarData(
                                                                              ausencia.compensacao_data_inicio,
                                                                          )
                                                                        : 'N/A'}
                                                                    {' - '}
                                                                    {ausencia.compensacao_data_fim
                                                                        ? formatarData(
                                                                              ausencia.compensacao_data_fim,
                                                                          )
                                                                        : 'N/A'}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div className="flex gap-2">
                                                                    <Link
                                                                        href={route(
                                                                            'ausencias.show',
                                                                            ausencia.id,
                                                                        )}
                                                                        className="btn btn-info btn-xs"
                                                                    >
                                                                        Ver
                                                                    </Link>
                                                                    <Link
                                                                        href={route(
                                                                            'ausencias.edit',
                                                                            ausencia.id,
                                                                        )}
                                                                        className="btn btn-warning btn-xs"
                                                                    >
                                                                        Editar
                                                                    </Link>
                                                                    <button
                                                                        onClick={() =>
                                                                            excluirAusencias(
                                                                                ausencia.id,
                                                                            )
                                                                        }
                                                                        className="btn btn-error btn-xs"
                                                                    >
                                                                        Excluir
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    ),
                                                )}
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* Paginação */}
                                    {ausencias.last_page > 1 && (
                                        <div className="mt-6 flex justify-center">
                                            <div className="join">
                                                {ausencias.links.map(
                                                    (link, index) => (
                                                        <button
                                                            key={index}
                                                            onClick={() => {
                                                                if (link.url) {
                                                                    router.get(
                                                                        link.url,
                                                                    );
                                                                }
                                                            }}
                                                            className={`join-item btn ${link.active ? 'btn-active' : ''}`}
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
            </div>
        </AuthenticatedLayout>
    );
};

export default Index;
