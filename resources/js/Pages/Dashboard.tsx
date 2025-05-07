import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

type DashboardProps = {
    projetosCount: number;
    usuariosCount: number;
    solicitacoesPendentes: number;
    ultimosProjetos: { id: number; nome: string; cliente: string }[];
};

export default function Dashboard({
    projetosCount = 0,
    usuariosCount = 0,
    solicitacoesPendentes = 0,
    ultimosProjetos = [],
}: DashboardProps) {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div className="card bg-base-100 shadow">
                            <div className="card-body items-center text-center">
                                <span className="text-4xl font-bold">
                                    {projetosCount}
                                </span>
                                <span className="text-base-content/70">
                                    Projetos ativos
                                </span>
                            </div>
                        </div>
                        <div className="card bg-base-100 shadow">
                            <div className="card-body items-center text-center">
                                <span className="text-4xl font-bold">
                                    {usuariosCount}
                                </span>
                                <span className="text-base-content/70">
                                    Usuários cadastrados
                                </span>
                            </div>
                        </div>
                        <div className="card bg-base-100 shadow">
                            <div className="card-body items-center text-center">
                                <span className="text-4xl font-bold">
                                    {solicitacoesPendentes}
                                </span>
                                <span className="text-base-content/70">
                                    Solicitações pendentes
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="card bg-base-100 shadow">
                        <div className="card-body">
                            <h3 className="card-title mb-4">
                                Últimos projetos cadastrados
                            </h3>
                            {ultimosProjetos.length > 0 ? (
                                <ul className="list">
                                    {ultimosProjetos.map((projeto) => (
                                        <li
                                            key={projeto.id}
                                            className="list-row flex justify-between"
                                        >
                                            <span>{projeto.nome}</span>
                                            <span className="text-base-content/60">
                                                {projeto.cliente}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <span className="text-base-content/60">
                                    Nenhum projeto cadastrado recentemente.
                                </span>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
