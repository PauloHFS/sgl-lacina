import { Table, ColumnDefinition } from '@/Components/Table';
import { Ausencia } from '@/types';
import { router } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import React from 'react';

interface AusenciasTabProps {
    ausencias: Ausencia[];
}

const AusenciasTab: React.FC<AusenciasTabProps> = ({ ausencias }) => {
    const handleRowClick = (ausencia: Ausencia) => {
        router.visit(route('ausencias.show', ausencia.id));
    };

    const columns: ColumnDefinition<Ausencia>[] = [
        {
            header: 'Colaborador',
            accessor: 'usuario',
            render: (ausencia) => ausencia.usuario?.name || 'N/A',
        },
        {
            header: 'Data de Início',
            accessor: 'data_inicio',
            render: (ausencia) =>
                format(new Date(ausencia.data_inicio), 'dd/MM/yyyy', {
                    locale: ptBR,
                }),
        },
        {
            header: 'Data de Fim',
            accessor: 'data_fim',
            render: (ausencia) =>
                format(new Date(ausencia.data_fim), 'dd/MM/yyyy', {
                    locale: ptBR,
                }),
        },
        {
            header: 'Status',
            accessor: 'status',
            render: (ausencia) => (
                <span
                    className={`badge badge-${
                        ausencia.status === 'APROVADO'
                            ? 'success'
                            : ausencia.status === 'REJEITADO'
                            ? 'error'
                            : 'warning'
                    }`}
                >
                    {ausencia.status}
                </span>
            ),
        },
    ];

    // Wrap the array in a Paginated structure
    const paginatedAusencias = {
        data: ausencias,
        links: [],
        meta: {
            current_page: 1,
            from: 1,
            last_page: 1,
            links: [],
            path: '',
            per_page: ausencias.length,
            to: ausencias.length,
            total: ausencias.length,
        },
    };

    return (
        <div>
            <h3 className="card-title mb-4 text-xl">Ausências do Projeto</h3>
            <Table
                data={paginatedAusencias}
                columns={columns}
                onRowClick={handleRowClick}
                emptyMessage="Nenhuma ausência encontrada para este projeto."
            />
        </div>
    );
};

export default AusenciasTab;
