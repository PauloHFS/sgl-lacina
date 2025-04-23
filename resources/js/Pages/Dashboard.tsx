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
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h3 className="mb-4 text-lg font-bold">
                                Projetos cadastrados
                            </h3>
                            {projetos && projetos.length > 0 ? (
                                <ul className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {projetos.map((projeto) => (
                                        <li
                                            key={projeto.id}
                                            className="flex items-center justify-between py-2"
                                        >
                                            <div>
                                                <span className="font-semibold">
                                                    {projeto.nome}
                                                </span>
                                                <span className="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                                    ({projeto.tipo}) - Cliente:{' '}
                                                    {projeto.cliente}
                                                </span>
                                            </div>
                                            <button
                                                className="ml-4 rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 disabled:opacity-50"
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
                                <div>Nenhum projeto cadastrado.</div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
