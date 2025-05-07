import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

type Projeto = {
    id: number;
    nome: string;
    cliente: string;
    tipo: string;
};

type DashboardProps = {
    projetos: Projeto[];
};

export default function Projetos({ projetos }: DashboardProps) {
    const [solicitando, setSolicitando] = useState<{ [id: number]: boolean }>(
        {},
    );
    const [searchTerm, setSearchTerm] = useState('');

    const solicitarVinculo = (projetoId: number) => {
        setSolicitando((prev) => ({ ...prev, [projetoId]: true }));
        router.post(
            route('projetos.solicitar-vinculo', { projeto: projetoId }),
            {},
            {
                onFinish: () =>
                    setSolicitando((prev) => ({ ...prev, [projetoId]: false })),
            },
        );
    };

    const filteredProjetos = projetos?.filter(
        (projeto) =>
            projeto.nome.toLowerCase().includes(searchTerm.toLowerCase()) ||
            projeto.cliente.toLowerCase().includes(searchTerm.toLowerCase()) ||
            projeto.tipo.toLowerCase().includes(searchTerm.toLowerCase()),
    );

    const getBadgeColor = (tipo: string) => {
        switch (tipo.toLowerCase()) {
            case 'web':
                return 'badge-primary';
            case 'mobile':
                return 'badge-secondary';
            case 'desktop':
                return 'badge-accent';
            default:
                return 'badge-info';
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Projetos" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            <div className="mb-6 flex flex-col items-center justify-between sm:flex-row">
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
                            </div>

                            {filteredProjetos && filteredProjetos.length > 0 ? (
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    {filteredProjetos.map((projeto) => (
                                        <div
                                            key={projeto.id}
                                            className="card bg-base-200 transition-all hover:shadow-md"
                                        >
                                            <div className="card-body p-4">
                                                <div className="flex items-start justify-between">
                                                    <h4 className="card-title text-lg">
                                                        {projeto.nome}
                                                    </h4>
                                                    <span
                                                        className={`badge ${getBadgeColor(projeto.tipo)}`}
                                                    >
                                                        {projeto.tipo}
                                                    </span>
                                                </div>
                                                <p className="text-base-content/70 mt-1">
                                                    <span className="font-medium">
                                                        Cliente:
                                                    </span>{' '}
                                                    {projeto.cliente}
                                                </p>
                                                <div className="card-actions mt-3 justify-end">
                                                    <button
                                                        className="btn btn-primary btn-sm"
                                                        onClick={() =>
                                                            solicitarVinculo(
                                                                projeto.id,
                                                            )
                                                        }
                                                        disabled={
                                                            !!solicitando[
                                                                projeto.id
                                                            ]
                                                        }
                                                    >
                                                        {solicitando[projeto.id]
                                                            ? 'Solicitando...'
                                                            : 'Solicitar Vínculo'}
                                                    </button>
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
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
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
