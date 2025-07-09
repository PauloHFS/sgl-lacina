import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { differenceInCalendarDays, parseISO } from 'date-fns';
import { FormEventHandler, useState } from 'react';

export function podeEditarDailyReport(data: string): boolean {
    const hoje = new Date();
    const dataReport = parseISO(data);
    return differenceInCalendarDays(hoje, dataReport) === 0;
}

export function podeExcluirDailyReport(data: string): boolean {
    const hoje = new Date();
    const dataReport = parseISO(data);
    const diff = differenceInCalendarDays(hoje, dataReport);
    return diff === 0 || diff === 1;
}

interface DailyReport {
    id: string;
    data: string;
    horas_trabalhadas: number;
    o_que_fez_ontem: string | null;
    o_que_vai_fazer_hoje: string | null;
    observacoes: string | null;
    projeto: {
        id: string;
        nome: string;
        cliente: string;
    };
    created_at: string;
    updated_at: string;
}

interface Projeto {
    id: string;
    nome: string;
    cliente: string;
}

interface Filtros {
    data_inicio?: string;
    data_fim?: string;
    projeto_id?: string;
}

interface IndexPageProps extends PageProps {
    dailyReports: {
        data: DailyReport[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    projetosAtivos: Projeto[];
    filtros: Filtros;
}

const formatarData = (data: string): string => {
    return new Date(data).toLocaleDateString('pt-BR');
};

const Index = ({ dailyReports, projetosAtivos, filtros }: IndexPageProps) => {
    const [filtroLocal, setFiltroLocal] = useState<Filtros>(filtros);

    const aplicarFiltros: FormEventHandler = (e) => {
        e.preventDefault();

        const params = new URLSearchParams();

        if (filtroLocal.data_inicio) {
            params.append('data_inicio', filtroLocal.data_inicio);
        }

        if (filtroLocal.data_fim) {
            params.append('data_fim', filtroLocal.data_fim);
        }

        if (filtroLocal.projeto_id) {
            params.append('projeto_id', filtroLocal.projeto_id);
        }

        router.get(route('daily-reports.index'), Object.fromEntries(params), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const limparFiltros = () => {
        setFiltroLocal({});
        router.get(route('daily-reports.index'));
    };

    const excluirReport = (id: string) => {
        if (confirm('Tem certeza que deseja excluir este daily report?')) {
            router.delete(route('daily-reports.destroy', id), {
                onSuccess: () => {
                    // Sucesso será mostrado via flash message
                },
            });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-base-content text-xl leading-tight font-semibold">
                        Daily Reports
                    </h2>
                    <Link
                        href={route('daily-reports.create')}
                        className="btn btn-primary"
                    >
                        Novo Daily Report
                    </Link>
                </div>
            }
        >
            <Head title="Daily Reports" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Filtros */}
                    <div className="card bg-base-200/50 dark:bg-base-200/20 mb-8 shadow-md">
                        <div className="card-body">
                            <h3 className="card-title text-base-content/80 mb-2">
                                Filtros
                            </h3>
                            <form
                                onSubmit={aplicarFiltros}
                                className="grid grid-cols-1 items-end gap-x-4 gap-y-2 sm:grid-cols-2 lg:grid-cols-4"
                            >
                                <div className="form-control w-full">
                                    <label className="label">
                                        <span className="label-text">
                                            Data Início
                                        </span>
                                    </label>
                                    <input
                                        type="date"
                                        className="input input-bordered w-full"
                                        value={filtroLocal.data_inicio || ''}
                                        onChange={(e) =>
                                            setFiltroLocal((prev) => ({
                                                ...prev,
                                                data_inicio: e.target.value,
                                            }))
                                        }
                                    />
                                </div>

                                <div className="form-control w-full">
                                    <label className="label">
                                        <span className="label-text">
                                            Data Fim
                                        </span>
                                    </label>
                                    <input
                                        type="date"
                                        className="input input-bordered w-full"
                                        value={filtroLocal.data_fim || ''}
                                        onChange={(e) =>
                                            setFiltroLocal((prev) => ({
                                                ...prev,
                                                data_fim: e.target.value,
                                            }))
                                        }
                                    />
                                </div>

                                <div className="form-control w-full">
                                    <label className="label">
                                        <span className="label-text">
                                            Projeto
                                        </span>
                                    </label>
                                    <select
                                        className="select select-bordered w-full"
                                        value={filtroLocal.projeto_id || ''}
                                        onChange={(e) =>
                                            setFiltroLocal((prev) => ({
                                                ...prev,
                                                projeto_id: e.target.value,
                                            }))
                                        }
                                    >
                                        <option value="">
                                            Todos os projetos
                                        </option>
                                        {projetosAtivos.map((projeto) => (
                                            <option
                                                key={projeto.id}
                                                value={projeto.id}
                                            >
                                                {projeto.nome} -{' '}
                                                {projeto.cliente}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="form-control w-full">
                                    <div className="flex gap-2 pt-4 sm:pt-0">
                                        <button
                                            type="submit"
                                            className="btn btn-primary flex-grow"
                                        >
                                            Filtrar
                                        </button>
                                        <button
                                            type="button"
                                            onClick={limparFiltros}
                                            className="btn btn-ghost"
                                        >
                                            Limpar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {/* Lista de Daily Reports */}
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            {dailyReports.data.length === 0 ? (
                                <div className="py-8 text-center">
                                    <p className="mb-4 text-gray-500">
                                        Nenhum daily report encontrado.
                                    </p>
                                    <Link
                                        href={route('daily-reports.create')}
                                        className="btn btn-primary"
                                    >
                                        Criar Primeiro Daily Report
                                    </Link>
                                </div>
                            ) : (
                                <>
                                    <div className="overflow-x-auto">
                                        <table className="table">
                                            <thead>
                                                <tr>
                                                    <th>Data</th>
                                                    <th>Projeto</th>
                                                    <th>Horas</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {dailyReports.data.map(
                                                    (report) => (
                                                        <tr key={report.id}>
                                                            <td>
                                                                <div>
                                                                    <div className="font-bold">
                                                                        {formatarData(
                                                                            report.data,
                                                                        )}
                                                                    </div>
                                                                    <div className="text-sm opacity-50">
                                                                        Criado
                                                                        em{' '}
                                                                        {formatarData(
                                                                            report.created_at,
                                                                        )}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <div className="font-bold">
                                                                        {
                                                                            report
                                                                                .projeto
                                                                                .nome
                                                                        }
                                                                    </div>
                                                                    <div className="text-sm opacity-50">
                                                                        {
                                                                            report
                                                                                .projeto
                                                                                .cliente
                                                                        }
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div className="badge badge-primary">
                                                                    {
                                                                        report.horas_trabalhadas
                                                                    }
                                                                    h
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div className="flex gap-2">
                                                                    <Link
                                                                        href={route(
                                                                            'daily-reports.show',
                                                                            report.id,
                                                                        )}
                                                                        className="btn btn-info btn-xs"
                                                                    >
                                                                        Ver
                                                                    </Link>
                                                                    {podeEditarDailyReport(
                                                                        report.created_at,
                                                                    ) && (
                                                                        <Link
                                                                            href={route(
                                                                                'daily-reports.edit',
                                                                                report.id,
                                                                            )}
                                                                            className="btn btn-warning btn-xs"
                                                                        >
                                                                            Editar
                                                                        </Link>
                                                                    )}
                                                                    {podeExcluirDailyReport(
                                                                        report.created_at,
                                                                    ) && (
                                                                        <button
                                                                            onClick={() =>
                                                                                excluirReport(
                                                                                    report.id,
                                                                                )
                                                                            }
                                                                            className="btn btn-error btn-xs"
                                                                        >
                                                                            Excluir
                                                                        </button>
                                                                    )}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    ),
                                                )}
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* Paginação */}
                                    {dailyReports.last_page > 1 && (
                                        <div className="mt-6 flex justify-center">
                                            <div className="join">
                                                {dailyReports.links.map(
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
                                                            className={`join-item btn ${
                                                                link.active
                                                                    ? 'btn-active'
                                                                    : ''
                                                            }`}
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
