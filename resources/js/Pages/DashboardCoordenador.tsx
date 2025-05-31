import Pagination, { Paginated } from '@/Components/Paggination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Funcao, StatusVinculoProjeto, TipoVinculo } from '@/types';
import { Head, Link } from '@inertiajs/react';

type ProjetoAtivoType = {
    id: string;
    nome: string;
    descricao: string | null;
    data_inicio: string;
    data_termino: string | null;
    cliente: string;
    slack_url: string | null;
    discord_url: string | null;
    board_url: string | null;
    git_url: string | null;
    tipo: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    vinculo: {
        usuario_id: string;
        projeto_id: string;
        id: string;
        tipo_vinculo: TipoVinculo;
        funcao: Funcao;
        status: StatusVinculoProjeto;
        carga_horaria_semanal: number;
        data_inicio: string;
        data_fim: string | null;
        created_at: string;
        updated_at: string;
    };
};

type DashboardProps = {
    projetosCount: number;
    usuariosCount: number;
    solicitacoesPendentes: number;
    ultimosProjetos: { id: string; nome: string; cliente: string }[];
    projetosAtivos: Paginated<ProjetoAtivoType>;
};

export default function Dashboard({
    projetosCount = 0,
    usuariosCount = 0,
    solicitacoesPendentes = 0,
    ultimosProjetos = [],
    projetosAtivos = {
        data: [],
        current_page: 1,
        first_page_url: '',
        from: 0,
        last_page: 1,
        last_page_url: '',
        links: [],
        next_page_url: null,
        path: '',
        per_page: 10,
        prev_page_url: null,
        to: 0,
        total: 0,
    },
}: DashboardProps) {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div
                            className="tooltip"
                            data-tip="Número total de projetos atualmente ativos no sistema."
                        >
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
                        </div>
                        <div
                            className="tooltip"
                            data-tip="Número total de usuários registrados na plataforma."
                        >
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
                        </div>
                        <div
                            className="tooltip"
                            data-tip="Número de solicitações de cadastro que aguardam aprovação."
                        >
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
                    </div>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div className="card bg-base-100 shadow md:col-span-2">
                            <div className="card-body">
                                <h3 className="card-title mb-4">
                                    Seus projetos ativos
                                </h3>
                                {projetosAtivos.data.length > 0 ? (
                                    <>
                                        <ul className="list">
                                            {projetosAtivos.data.map(
                                                (projeto: ProjetoAtivoType) => (
                                                    <Link
                                                        as="li"
                                                        key={projeto.id}
                                                        className="list-row hover:bg-base-200 flex cursor-pointer items-center justify-between rounded p-3 transition-colors"
                                                        href={`/projeto/${projeto.id}`}
                                                        data-testid={`projeto-${projeto.id}`}
                                                        title={`Acessar projeto: ${projeto.nome}`}
                                                        aria-label={`Acessar projeto: ${projeto.nome}`}
                                                        tabIndex={0}
                                                        role="link"
                                                    >
                                                        <span>
                                                            {projeto.nome}
                                                        </span>
                                                        <span className="text-base-content/60">
                                                            {projeto.cliente}
                                                        </span>
                                                    </Link>
                                                ),
                                            )}
                                        </ul>

                                        {/* Paginação dos projetos ativos */}
                                        {projetosAtivos.last_page > 1 && (
                                            <div className="mt-4">
                                                <Pagination
                                                    paginated={projetosAtivos}
                                                />
                                            </div>
                                        )}
                                    </>
                                ) : (
                                    <span className="text-base-content/60">
                                        Nenhum projeto ativo no momento.
                                    </span>
                                )}
                            </div>
                        </div>

                        <div className="card bg-base-100 shadow">
                            <div className="card-body flex-1">
                                <h3 className="card-title mb-4">
                                    Últimos projetos cadastrados
                                </h3>
                                {ultimosProjetos.length > 0 ? (
                                    <ul className="list">
                                        {ultimosProjetos.map((projeto) => (
                                            <Link
                                                as="li"
                                                href={`/projeto/${projeto.id}`}
                                                data-testid={`ultimo-projeto-${projeto.id}`}
                                                aria-label={`Acessar projeto: ${projeto.nome}`}
                                                tabIndex={0}
                                                role="link"
                                                title={`Acessar projeto: ${projeto.nome}`}
                                                className="list-row hover:bg-base-200 flex cursor-pointer items-center justify-between rounded p-3 transition-colors"
                                                key={projeto.id}
                                            >
                                                <span>{projeto.nome}</span>
                                                <span className="text-base-content/60">
                                                    {projeto.cliente}
                                                </span>
                                            </Link>
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
            </div>
        </AuthenticatedLayout>
    );
}
