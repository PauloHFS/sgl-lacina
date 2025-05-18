import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps, TipoProjeto } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

type Projeto = {
    id: number;
    nome: string;
    cliente: string;
    tipo: TipoProjeto;
};

export default function Projetos({
    projetos,
    auth,
}: PageProps<{ projetos: Projeto[] }>) {
    const [searchTerm, setSearchTerm] = useState('');

    const filteredProjetos = projetos.filter(
        (projeto) =>
            projeto.nome.toLowerCase().includes(searchTerm.toLowerCase()) ||
            projeto.cliente.toLowerCase().includes(searchTerm.toLowerCase()) ||
            projeto.tipo.toLowerCase().includes(searchTerm.toLowerCase()),
    );

    const getBadgeColor = (tipo: TipoProjeto) => {
        const badgeColors: { [key in TipoProjeto]: string } = {
            PDI: 'badge-primary',
            TCC: 'badge-secondary',
            MESTRADO: 'badge-accent',
            DOUTORADO: 'badge-info',
            SUPORTE: 'badge-warning',
        };
        return badgeColors[tipo] || 'badge-info';
    };

    const getBarColor = (tipo: TipoProjeto) => {
        const barColors: { [key in TipoProjeto]: string } = {
            PDI: 'bg-primary',
            TCC: 'bg-secondary',
            MESTRADO: 'bg-accent',
            DOUTORADO: 'bg-info',
            SUPORTE: 'bg-warning',
        };
        return barColors[tipo] || 'bg-info';
    };

    return (
        <AuthenticatedLayout>
            <Head title="Projetos" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            <div className="mb-6 flex flex-col items-center justify-between gap-4 sm:flex-row">
                                <div className="form-control w-full max-w-xs">
                                    <input
                                        type="text"
                                        placeholder="Buscar projetos..."
                                        className="input input-bordered w-full"
                                        value={searchTerm}
                                        onChange={(e) =>
                                            setSearchTerm(e.target.value)
                                        }
                                    />
                                </div>
                                {auth.isCoordenador && (
                                    <a
                                        href={route('projetos.create')}
                                        className="btn btn-primary w-full sm:w-auto"
                                    >
                                        Cadastrar Novo Projeto
                                    </a>
                                )}
                            </div>

                            {filteredProjetos && filteredProjetos.length > 0 ? (
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                    {filteredProjetos.map((projeto) => (
                                        <div
                                            key={projeto.id}
                                            className="group card card-bordered bg-base-100 cursor-pointer shadow-lg transition-all duration-300 ease-in-out hover:scale-105 hover:shadow-2xl"
                                            onClick={() =>
                                                router.get(
                                                    route('projetos.show', {
                                                        projeto: projeto.id,
                                                    }),
                                                )
                                            }
                                        >
                                            <div
                                                className={`h-3 w-full ${getBarColor(
                                                    projeto.tipo,
                                                )}`}
                                            ></div>
                                            <div className="card-body p-5">
                                                <div className="mb-2 flex items-start justify-between">
                                                    <h2 className="card-title group-hover:text-primary text-xl font-bold transition-colors">
                                                        {projeto.nome}
                                                    </h2>
                                                    <span
                                                        className={`badge badge-lg ${getBadgeColor(
                                                            projeto.tipo,
                                                        )}`}
                                                    >
                                                        {projeto.tipo}
                                                    </span>
                                                </div>
                                                <p className="text-base-content/80 mb-4 text-sm">
                                                    <span className="font-semibold">
                                                        Cliente:
                                                    </span>{' '}
                                                    {projeto.cliente}
                                                </p>
                                                <div className="card-actions border-base-300/50 mt-auto items-center justify-start border-t pt-4">
                                                    <div className="avatar-group -space-x-4 rtl:space-x-reverse">
                                                        <div className="avatar border-base-100 border-2">
                                                            <div className="h-10 w-10">
                                                                <img src="https://picsum.photos/seed/user1/80/80" />
                                                            </div>
                                                        </div>
                                                        <div className="avatar border-base-100 border-2">
                                                            <div className="h-10 w-10">
                                                                <img src="https://picsum.photos/seed/user2/80/80" />
                                                            </div>
                                                        </div>
                                                        <div className="avatar border-base-100 border-2">
                                                            <div className="h-10 w-10">
                                                                <img src="https://picsum.photos/seed/user3/80/80" />
                                                            </div>
                                                        </div>
                                                        <div className="avatar placeholder border-base-100 border-2">
                                                            <div className="bg-neutral text-neutral-content h-10 w-10">
                                                                <span>+5</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span className="text-base-content/60 group-hover:text-primary ml-auto text-xs transition-colors">
                                                        Ver detalhes &rarr;
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="alert alert-info">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        className="h-6 w-6 shrink-0 stroke-current"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 9 0118 0z"
                                        ></path>
                                    </svg>
                                    <span>
                                        {searchTerm
                                            ? 'Nenhum projeto encontrado para essa busca.'
                                            : 'Nenhum projeto cadastrado.'}
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
