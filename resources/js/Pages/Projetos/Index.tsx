import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    PageProps,
    StatusVinculoProjeto,
    TipoProjeto,
    TipoVinculo,
} from '@/types';
import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useDebounce } from 'use-debounce';

type Projeto = {
    id: string;
    nome: string;
    cliente: string;
    tipo: TipoProjeto;
    user_status: StatusVinculoProjeto | null;
    user_tipo_vinculo: TipoVinculo | null;
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

    const getBadgeColor = (tipo: TipoProjeto) => {
        const badgeColors: { [key in TipoProjeto]: string } = {
            PDI: 'badge-primary',
            TCC: 'badge-secondary',
            MESTRADO: 'badge-accent',
            DOUTORADO: 'badge-info',
            SUPORTE: 'badge-warning',
        };
        return badgeColors[tipo] || 'badge-neutral';
    };

    const getBarColor = (tipo: TipoProjeto) => {
        const barColors: { [key in TipoProjeto]: string } = {
            PDI: 'bg-primary',
            TCC: 'bg-secondary',
            MESTRADO: 'bg-accent',
            DOUTORADO: 'bg-info',
            SUPORTE: 'bg-warning',
        };
        return barColors[tipo] || 'bg-neutral';
    };

    // const getStatusBadgeColor = (status: StatusVinculoProjeto | null) => {
    //     if (!status) return 'badge-ghost';
    //     const statusColors: { [key in StatusVinculoProjeto]: string } = {
    //         APROVADO: 'badge-success',
    //         PENDENTE: 'badge-warning',
    //         RECUSADO: 'badge-error',
    //         ENCERRADO: 'badge-neutral',
    //     };
    //     return statusColors[status] || 'badge-ghost';
    // };

    // const getTipoVinculoBadgeColor = (tipoVinculo: TipoVinculo | null) => {
    //     if (!tipoVinculo) return 'badge-ghost';
    //     const tipoVinculoColors: { [key in TipoVinculo]: string } = {
    //         COLABORADOR: 'badge-primary',
    //         COORDENADOR: 'badge-accent',
    //     };
    //     return tipoVinculoColors[tipoVinculo] || 'badge-ghost';
    // };

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
                                                className={`rounded-t-box h-3 w-full ${getBarColor(
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
                                                <p className="text-base-content/80 mb-1 text-sm">
                                                    <span className="font-semibold">
                                                        Cliente:
                                                    </span>{' '}
                                                    {projeto.cliente}
                                                </p>

                                                {/* {projeto.user_status && (
                                                    <div className="mb-1">
                                                        <span className="text-sm font-semibold">
                                                            Meu Status:
                                                        </span>{' '}
                                                        <span
                                                            className={`badge ${getStatusBadgeColor(
                                                                projeto.user_status,
                                                            )}`}
                                                        >
                                                            {
                                                                projeto.user_status
                                                            }
                                                        </span>
                                                    </div>
                                                )}

                                                {projeto.user_tipo_vinculo && (
                                                    <div className="mb-4">
                                                        <span className="text-sm font-semibold">
                                                            Meu Vínculo:
                                                        </span>{' '}
                                                        <span
                                                            className={`badge ${getTipoVinculoBadgeColor(
                                                                projeto.user_tipo_vinculo,
                                                            )}`}
                                                        >
                                                            {
                                                                projeto.user_tipo_vinculo
                                                            }
                                                        </span>
                                                    </div>
                                                )} */}

                                                <div className="card-actions border-base-300/50 mt-auto items-center justify-start border-t pt-4">
                                                    {/* Placeholder for future actions */}
                                                    <span className="text-base-content/60 text-xs">
                                                        Clique para ver detalhes
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="py-10 text-center">
                                    <svg
                                        className="mx-auto h-12 w-12 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            vectorEffect="non-scaling-stroke"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                                        />
                                    </svg>
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">
                                        Nenhum projeto encontrado
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        {activeTab === 'todos' && !searchValue
                                            ? 'Cadastre um novo projeto para começar.'
                                            : 'Tente ajustar sua busca ou filtros.'}
                                    </p>
                                    {activeTab === 'todos' &&
                                        !searchValue &&
                                        auth.isCoordenador && (
                                            <div className="mt-6">
                                                <a
                                                    href={route(
                                                        'projetos.create',
                                                    )}
                                                    className="btn btn-primary"
                                                >
                                                    <svg
                                                        className="mr-2 -ml-1 h-5 w-5"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            fillRule="evenodd"
                                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                                            clipRule="evenodd"
                                                        />
                                                    </svg>
                                                    Cadastrar Novo Projeto
                                                </a>
                                            </div>
                                        )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
