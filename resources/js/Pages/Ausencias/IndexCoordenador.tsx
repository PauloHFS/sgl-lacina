import { Paginated } from '@/Components/Paggination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Ausencia, PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

interface IndexPageProps extends PageProps {
    ausencias: Paginated<Ausencia>;
}

const formatarData = (data: string): string => {
    return new Date(data).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

const Index = ({ ausencias }: IndexPageProps) => {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-base-content text-xl leading-tight font-semibold">
                        Ausências
                    </h2>
                </div>
            }
        >
            <Head title="Ausências" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Lista de Ausencias */}
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            {ausencias.data.length === 0 ? (
                                <div className="py-8 text-center">
                                    <p className="mb-4 text-gray-500">
                                        Nenhuma ausência encontrada.
                                    </p>
                                </div>
                            ) : (
                                <>
                                    <div className="overflow-x-auto">
                                        <table className="table">
                                            <thead>
                                                <tr>
                                                    <th>Colaborador</th>
                                                    <th>Projeto</th>
                                                    <th>Período de Ausência</th>
                                                    <th>
                                                        Período de Compensação
                                                    </th>
                                                    <th>Status</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {ausencias.data.map(
                                                    (ausencia) => (
                                                        <tr key={ausencia.id}>
                                                            <td>
                                                                <div className="flex items-center gap-3">
                                                                    <div className="avatar">
                                                                        <div className="mask mask-squircle h-12 w-12">
                                                                            <img
                                                                                src={
                                                                                    ausencia
                                                                                        .usuario
                                                                                        ?.foto_url ??
                                                                                    `https://ui-avatars.com/api/?name=${encodeURIComponent(ausencia.usuario?.name ?? 'U')}&background=random&color=fff`
                                                                                }
                                                                                alt={`Foto de ${ausencia.usuario?.name}`}
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div className="font-bold">
                                                                            {ausencia
                                                                                .usuario
                                                                                ?.name ||
                                                                                '-'}
                                                                        </div>
                                                                        <div className="text-sm opacity-50">
                                                                            {ausencia
                                                                                .usuario
                                                                                ?.email ||
                                                                                ''}
                                                                        </div>
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
                                                                    <div className="text-sm opacity-50">
                                                                        {
                                                                            ausencia.titulo
                                                                        }
                                                                    </div>
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
                                                                <span
                                                                    className={`badge ${
                                                                        {
                                                                            PENDENTE:
                                                                                'badge-warning',
                                                                            APROVADO:
                                                                                'badge-success',
                                                                            REJEITADO:
                                                                                'badge-error',
                                                                        }[
                                                                            ausencia
                                                                                .status
                                                                        ]
                                                                    }`}
                                                                >
                                                                    {
                                                                        ausencia.status
                                                                    }
                                                                </span>
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
