import { Ausencia } from '@/types';
import { router } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import React from 'react';

interface AusenciasTabProps {
    ausencias: Ausencia[];
}

const AusenciasTab: React.FC<AusenciasTabProps> = ({ ausencias }) => {
    const handleRowClick = (ausenciaId: string) => {
        router.visit(route('ausencias.show', ausenciaId));
    };

    return (
        <div>
            <h3 className="card-title mb-4 text-xl">Ausências do Projeto</h3>
            {ausencias && ausencias.length > 0 ? (
                <div className="overflow-x-auto">
                    <table className="table-zebra table w-full">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Data de Início</th>
                                <th>Data de Fim</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {ausencias.map((ausencia) => (
                                <tr
                                    key={ausencia.id}
                                    onClick={() => handleRowClick(ausencia.id)}
                                    className="hover:bg-base-200 cursor-pointer"
                                >
                                    <td>{ausencia.usuario?.name || 'N/A'}</td>
                                    <td>
                                        {format(
                                            new Date(ausencia.data_inicio),
                                            'dd/MM/yyyy',
                                            { locale: ptBR },
                                        )}
                                    </td>
                                    <td>
                                        {format(
                                            new Date(ausencia.data_fim),
                                            'dd/MM/yyyy',
                                            { locale: ptBR },
                                        )}
                                    </td>
                                    <td>
                                        <span
                                            className={`badge badge-${ausencia.status === 'APROVADO' ? 'success' : ausencia.status === 'REJEITADO' ? 'error' : 'warning'}`}
                                        >
                                            {ausencia.status}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            ) : (
                <div className="py-8 text-center">
                    <div className="text-base-content/60">
                        Nenhuma ausência encontrada para este projeto.
                    </div>
                </div>
            )}
        </div>
    );
};

export default AusenciasTab;
