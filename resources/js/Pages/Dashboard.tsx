import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Funcao, PageProps, StatusVinculoProjeto, TipoVinculo } from '@/types';
import { Head } from '@inertiajs/react';

export default function Dashboard({
    projetos,
    projetosCount,
}: PageProps<{
    projetos: {
        id: string;
        usuario_id: string;
        projeto_id: string;
        tipo_vinculo: TipoVinculo;
        funcao: Funcao;
        status: StatusVinculoProjeto;
        carga_horaria_semanal: number;
        data_inicio: string;
        data_fim: string | null;
        created_at: string;
        updated_at: string;
        deleted_at: string | null;
        projeto_nome: string;
        projeto_cliente: string;
        data_termino: string;
    }[];
    projetosCount: number;
}>) {
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Key Metrics */}
                    <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div
                            className="tooltip"
                            data-tip="Número de projetos em que você está atualmente ativo."
                        >
                            <div className="card bg-base-100 shadow">
                                <div className="card-body items-center text-center">
                                    <span className="text-4xl font-bold">
                                        {
                                            projetos.filter(
                                                (p) => p.status === 'APROVADO',
                                            ).length
                                        }
                                    </span>
                                    <span className="text-base-content/70">
                                        Projetos Ativos
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div
                            className="tooltip"
                            data-tip="Número total de projetos em que você já esteve envolvido."
                        >
                            <div className="card bg-base-100 shadow">
                                <div className="card-body items-center text-center">
                                    <span className="text-4xl font-bold">
                                        {projetosCount}
                                    </span>
                                    <span className="text-base-content/70">
                                        Total de Projetos
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div
                            className="tooltip"
                            data-tip="Número de projetos que você concluiu ou não está mais ativo."
                        >
                            <div className="card bg-base-100 shadow">
                                <div className="card-body items-center text-center">
                                    <span className="text-4xl font-bold">
                                        {
                                            projetos.filter(
                                                (p) => p.status === 'ENCERRADO',
                                            ).length
                                        }
                                    </span>
                                    <span className="text-base-content/70">
                                        Projetos Finalizados
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Recent Projects */}
                    <div className="card bg-base-100 shadow">
                        <div className="card-body">
                            <div className="mb-4 flex items-center justify-between">
                                <h3 className="card-title">
                                    Histórico de Projetos
                                </h3>
                                {/* <form
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        router.post(
                                            route(
                                                'relatorio.participacao.enviar',
                                            ),
                                        );
                                    }}
                                >
                                    <button
                                        type="submit"
                                        className="btn btn-primary btn-sm"
                                    >
                                        Gerar Relatório por Email
                                    </button>
                                </form> */}
                            </div>
                            <div className="overflow-x-auto">
                                <table className="table-zebra table">
                                    <thead>
                                        <tr>
                                            <th>Projeto</th>
                                            <th>Cliente</th>
                                            <th>Tipo</th>
                                            <th>Período</th>
                                            <th>Carga Horária</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {projetos.map((p, i) => (
                                            <tr key={i}>
                                                <td>{p.projeto_nome}</td>
                                                <td>{p.projeto_cliente}</td>
                                                <td>
                                                    <span className="badge badge-info">
                                                        {p.tipo_vinculo}
                                                    </span>
                                                </td>
                                                <td>
                                                    {new Date(
                                                        p.data_inicio,
                                                    ).toLocaleDateString()}{' '}
                                                    -{' '}
                                                    {p.data_fim
                                                        ? new Date(
                                                              p.data_fim,
                                                          ).toLocaleDateString()
                                                        : 'Atual'}
                                                </td>
                                                <td>
                                                    <span className="badge badge-outline">
                                                        {
                                                            p.carga_horaria_semanal
                                                        }
                                                        h/sem
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        className={
                                                            p.status ===
                                                            'APROVADO'
                                                                ? 'badge badge-success'
                                                                : p.status ===
                                                                    'ENCERRADO'
                                                                  ? 'badge badge-neutral'
                                                                  : 'badge'
                                                        }
                                                    >
                                                        {p.status}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
