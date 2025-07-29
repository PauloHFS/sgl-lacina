import { Paginated } from '@/Components/Paggination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Ausencia, PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

interface IndexPageProps extends PageProps {
    ausencias: Paginated<Ausencia>;
}

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
                                        Nenhuma ausências encontrada.
                                    </p>
                                    <Link
                                        href={route('ausencias.create')}
                                        className="btn btn-primary"
                                    >
                                        Registrar Primeira Ausencia
                                    </Link>
                                </div>
                            ) : (
                                <>
                                    <div className="overflow-x-auto">
                                        <table className="table">
                                            <thead>
                                                <tr>
                                                    <th>Colaborador</th>
                                                    <th>Projeto</th>
                                                    <th>Titulo</th>
                                                    <th>Status</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {ausencias.data.map(
                                                    (ausencias) => (
                                                        <tr key={ausencias.id}>
                                                            <td>
                                                                <div className="flex items-center gap-3">
                                                                    <div className="avatar">
                                                                        <div className="mask mask-squircle h-12 w-12">
                                                                            <img
                                                                                src={
                                                                                    ausencias
                                                                                        .usuario
                                                                                        ?.foto_url
                                                                                        ? ausencias
                                                                                              .usuario
                                                                                              ?.foto_url
                                                                                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(ausencias.usuario?.name ?? 'User')}&background=random&color=fff`
                                                                                }
                                                                                alt={`Foto de ${ausencias.usuario?.name}`}
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div className="font-bold">
                                                                            {ausencias
                                                                                .usuario
                                                                                ?.name ||
                                                                                '-'}
                                                                        </div>
                                                                        <div className="text-sm opacity-50">
                                                                            {ausencias
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
                                                                        {ausencias
                                                                            .projeto
                                                                            ?.nome ||
                                                                            '-'}
                                                                    </div>
                                                                    <div className="text-sm opacity-50">
                                                                        {ausencias
                                                                            .projeto
                                                                            ?.cliente ||
                                                                            ''}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div className="font-bold">
                                                                        {
                                                                            ausencias.titulo
                                                                        }
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span
                                                                        className={`badge ${
                                                                            ausencias.status ===
                                                                            'PENDENTE'
                                                                                ? 'badge-warning'
                                                                                : ausencias.status ===
                                                                                    'APROVADO'
                                                                                  ? 'badge-success'
                                                                                  : 'badge-error'
                                                                        }`}
                                                                    >
                                                                        {
                                                                            ausencias.status
                                                                        }
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div className="flex gap-2">
                                                                    <Link
                                                                        href={route(
                                                                            'ausencias.show',
                                                                            ausencias.id,
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
