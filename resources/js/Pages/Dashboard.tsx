import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

// Tipagem básica para os projetos
type Projeto = {
    id: number;
    nome: string;
    cliente: string;
    tipo: string;
};

type DashboardProps = {
    projetos: Projeto[];
};

export default function Dashboard({ projetos }: DashboardProps) {
    // Estado para controlar botões desabilitados por projeto
    const [solicitando, setSolicitando] = useState<{ [id: number]: boolean }>(
        {},
    );

    // Função para solicitar vínculo
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

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl leading-tight font-semibold">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-sm">
                        <div className="card-body">
                            <h3 className="mb-4 text-lg font-bold">
                                Projetos cadastrados
                            </h3>
                            {projetos && projetos.length > 0 ? (
                                <ul className="divide-base-300 divide-y">
                                    {projetos.map((projeto) => (
                                        <li
                                            key={projeto.id}
                                            className="flex items-center justify-between py-2"
                                        >
                                            <div>
                                                <span className="font-semibold">
                                                    {projeto.nome}
                                                </span>
                                                <span className="text-base-content/70 ml-2 text-sm">
                                                    ({projeto.tipo}) - Cliente:{' '}
                                                    {projeto.cliente}
                                                </span>
                                            </div>
                                            <button
                                                className="btn btn-primary btn-sm"
                                                onClick={() =>
                                                    solicitarVinculo(projeto.id)
                                                }
                                                disabled={
                                                    !!solicitando[projeto.id]
                                                }
                                            >
                                                Solicitar Vínculo
                                            </button>
                                        </li>
                                    ))}
                                </ul>
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
                                    <span>Nenhum projeto cadastrado.</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
