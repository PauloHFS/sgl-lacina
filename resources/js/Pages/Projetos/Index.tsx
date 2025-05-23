import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps, TipoProjeto } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useDebounce } from 'use-debounce';

type Projeto = {
    id: string; // Changed from number to string to match controller and database schema
    nome: string;
    cliente: string;
    tipo: TipoProjeto;
};

type QueryParams = {
    search?: string;
    tab: 'todos' | 'colaborador' | 'coordenador';
};

interface ProjetosPageProps extends PageProps {
    projetos: Projeto[];
    queryparams: QueryParams;
}

export default function Projetos({
    projetos,
    auth,
    queryparams,
}: ProjetosPageProps) {
    const [searchValue, setSearchValue] = useState(queryparams.search || '');
    const [debouncedSearchValue] = useDebounce(searchValue, 300);
    const [activeTab, setActiveTab] = useState<QueryParams['tab']>(
        queryparams.tab || 'todos',
    );

    useEffect(() => {
        // Only make a request if debouncedSearchValue or activeTab has actually changed
        // and is different from the initial queryparams.
        if (
            debouncedSearchValue !== (queryparams.search || '') ||
            activeTab !== (queryparams.tab || 'todos')
        ) {
            router.get(
                route('projetos.index'),
                { search: debouncedSearchValue, tab: activeTab },
                { preserveState: true, replace: true, preserveScroll: true },
            );
        }
    }, [debouncedSearchValue, activeTab, queryparams.search, queryparams.tab]);

    // Effect to sync local state if queryparams change from external navigation/reload
    useEffect(() => {
        setSearchValue(queryparams.search || '');
        setActiveTab(queryparams.tab || 'todos');
    }, [queryparams.search, queryparams.tab]);

    const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearchValue(e.target.value);
    };

    const handleTabChange = (tab: QueryParams['tab']) => {
        setActiveTab(tab);
    };

    // filteredProjetos is directly from props, controller handles filtering
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
                                        value={searchValue}
                                        onChange={handleSearchChange}
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

                            <div
                                role="tablist"
                                className="tabs tabs-bordered mb-6"
                            >
                                <button
                                    role="tab"
                                    className={`tab ${
                                        activeTab === 'todos'
                                            ? 'tab-active'
                                            : ''
                                    }`}
                                    onClick={() => handleTabChange('todos')}
                                >
                                    Todos
                                </button>
                                <button
                                    role="tab"
                                    className={`tab ${
                                        activeTab === 'colaborador'
                                            ? 'tab-active'
                                            : ''
                                    }`}
                                    onClick={() =>
                                        handleTabChange('colaborador')
                                    }
                                >
                                    Meus Projetos (Colaborador)
                                </button>
                                {auth.isCoordenador && (
                                    <button
                                        role="tab"
                                        className={`tab ${
                                            activeTab === 'coordenador'
                                                ? 'tab-active'
                                                : ''
                                        }`}
                                        onClick={() =>
                                            handleTabChange('coordenador')
                                        }
                                    >
                                        Meus Projetos (Coordenador)
                                    </button>
                                )}
                            </div>

                            {projetos && projetos.length > 0 ? (
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                    {projetos.map((projeto) => (
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
                                                    {/* Placeholder for future actions like edit/delete icons if needed */}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center text-gray-500">
                                    Nenhum projeto encontrado para esta seleção.
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
