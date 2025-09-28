import Paggination, { Paginated } from '@/Components/Paggination';
import { Table, ColumnDefinition } from '@/Components/Table';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { PageProps, User, UsuarioProjeto } from '@/types';
import { router, usePage } from '@inertiajs/react';

interface ShowPageProps extends PageProps {
    colaborador: User;
    historico: Paginated<UsuarioProjeto>;
}

export default function HistoricoPage({
    colaborador,
    historico,
}: ShowPageProps) {
    const { url } = usePage();

    const columns: ColumnDefinition<UsuarioProjeto>[] = [
        {
            header: 'Projeto',
            accessor: 'projeto',
            render: (item) => item.projeto?.nome ?? '-',
        },
        {
            header: 'Função',
            accessor: 'funcao',
        },
        {
            header: 'Tipo de Vínculo',
            accessor: 'tipo_vinculo',
        },
        {
            header: 'Status',
            accessor: 'status',
            render: (item) => (
                <span className="badge badge-outline">{item.status}</span>
            ),
        },
        {
            header: 'Bolsa',
            accessor: 'valor_bolsa',
            render: (item) =>
                item.valor_bolsa ? (
                    <span className="badge badge-success">
                        {(item.valor_bolsa / 100).toLocaleString('pt-BR', {
                            style: 'currency',
                            currency: 'BRL',
                        })}
                    </span>
                ) : (
                    <span className="badge badge-secondary">---</span>
                ),
        },
        {
            header: 'Carga Horária',
            accessor: 'carga_horaria',
            render: (item) => `${item.carga_horaria}h`,
        },
        {
            header: 'Data Início',
            accessor: 'data_inicio',
            render: (item) =>
                item.data_inicio
                    ? new Date(item.data_inicio).toLocaleDateString('pt-BR')
                    : '-',
        },
        {
            header: 'Data Fim',
            accessor: 'data_fim',
            render: (item) =>
                item.data_fim
                    ? new Date(item.data_fim).toLocaleDateString('pt-BR')
                    : '-',
        },
    ];

    return (
        <Authenticated
            header={
                <div className="flex items-center justify-between">
                    <h1 className="text-base-content text-xl leading-tight font-semibold">
                        Histórico de Participação - {colaborador.name}
                    </h1>
                    <button className="btn" onClick={() => history.back()}>
                        Voltar
                    </button>
                </div>
            }
        >
            <div className="container mx-auto p-6">
                <div className="mb-6">
                    <div className="flex flex-col gap-2">
                        <div className="font-semibold">{colaborador.name}</div>
                        <div className="text-base-content/70 text-sm">
                            {colaborador.email}
                        </div>
                        <div className="flex gap-2">
                            {colaborador.area_atuacao?.map((area) => (
                                <span
                                    key={area}
                                    className="badge badge-soft badge-info"
                                >
                                    {area}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
                <Table
                    data={historico}
                    columns={columns}
                    emptyMessage="Nenhum histórico encontrado."
                />
                <div className="mt-6 flex justify-center">
                    <Paggination
                        paginated={historico}
                        onPageChange={(page) => {
                            router.get(url, { page }, { preserveState: true });
                        }}
                    />
                </div>
            </div>
        </Authenticated>
    );
}
