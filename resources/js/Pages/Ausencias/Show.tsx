import WysiwygViewer from '@/Components/WysiwygViewerPrism';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Ausencia, PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useMemo } from 'react';

interface ShowPageProps extends PageProps {
    ausencia: Ausencia;
}

const formatarData = (data: string | null | undefined): string => {
    if (!data) return 'N/A';
    const date = parseISO(data);
    return format(date, 'dd/MM/yyyy');
};

const formatarDataHora = (data: string | null | undefined): string => {
    if (!data) return 'N/A';
    const date = parseISO(data);
    return format(date, "dd/MM/yyyy 'às' HH:mm");
};

const Show = ({ ausencia, auth }: ShowPageProps) => {
    const compensacaoHorarios = useMemo(() => {
        if (typeof ausencia.compensacao_horarios === 'string') {
            try {
                return JSON.parse(ausencia.compensacao_horarios);
            } catch (e) {
                console.error("Erro ao fazer parse dos horários de compensação:", e);
                return [];
            }
        }
        return Array.isArray(ausencia.compensacao_horarios) ? ausencia.compensacao_horarios : [];
    }, [ausencia.compensacao_horarios]);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-base-content text-xl leading-tight font-semibold">
                        Detalhes da Ausência
                    </h2>
                    <div className="flex gap-2">
                        <Link
                            href={route('ausencias.edit', ausencia.id)}
                            className="btn btn-primary"
                        >
                            Editar
                        </Link>
                        <Link
                            href={route('ausencias.index')}
                            className="btn btn-ghost"
                        >
                            Voltar à Lista
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title={`Ausência - ${ausencia.titulo}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            <div className="space-y-6">
                                <h3 className="card-title text-2xl">
                                    {ausencia.titulo}
                                </h3>

                                {/* Informações básicas */}
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div className="stats shadow">
                                        <div className="stat">
                                            <div className="stat-title">
                                                Período da Ausência
                                            </div>
                                            <div className="stat-value text-lg">
                                                {formatarData(
                                                    ausencia.data_inicio,
                                                )}
                                                {' a '}
                                                {formatarData(
                                                    ausencia.data_fim,
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="stats shadow">
                                        <div className="stat">
                                            <div className="stat-title">
                                                Status
                                            </div>
                                            <div className="stat-value text-lg">
                                                <span
                                                    className={`badge ${
                                                        {
                                                            PENDENTE:
                                                                'badge-warning',
                                                            APROVADO:
                                                                'badge-success',
                                                            REJEITADO:
                                                                'badge-error',
                                                        }[ausencia.status]
                                                    }`}
                                                >
                                                    {ausencia.status}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Projeto */}
                                <div className="alert alert-info">
                                    <div>
                                        <h3 className="font-bold">Projeto</h3>
                                        <div className="text-sm">
                                            <strong>
                                                {ausencia.projeto?.nome}
                                            </strong>
                                            {ausencia.projeto?.cliente &&
                                                ` - ${ausencia.projeto.cliente}`}
                                        </div>
                                    </div>
                                </div>

                                {/* Justificativa */}
                                <div className="collapse-arrow bg-base-200 collapse">
                                    <input type="checkbox" defaultChecked />
                                    <div className="collapse-title text-xl font-medium">
                                        Justificativa
                                    </div>
                                    <div className="collapse-content">
                                        <WysiwygViewer
                                            content={ausencia.justificativa}
                                            className="min-h-[80px]"
                                        />
                                    </div>
                                </div>

                                {/* Plano de Compensação */}
                                {ausencia.horas_a_compensar > 0 && (
                                    <div className="collapse-arrow bg-base-200 collapse">
                                        <input type="checkbox" defaultChecked />
                                        <div className="collapse-title text-xl font-medium">
                                            Plano de Compensação
                                        </div>
                                        <div className="collapse-content space-y-4 p-4">
                                            <div className="stats w-full shadow">
                                                <div className="stat">
                                                    <div className="stat-title">
                                                        Horas a Compensar
                                                    </div>
                                                    <div className="stat-value text-primary">
                                                        {
                                                            ausencia.horas_a_compensar
                                                        }
                                                        h
                                                    </div>
                                                </div>
                                                <div className="stat">
                                                    <div className="stat-title">
                                                        Período
                                                    </div>
                                                    <div className="stat-value text-sm">
                                                        {formatarData(
                                                            ausencia.compensacao_data_inicio,
                                                        )}
                                                        {' a '}
                                                        {formatarData(
                                                            ausencia.compensacao_data_fim,
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            <h4 className="font-semibold">
                                                Horários Detalhados:
                                            </h4>
                                            <div className="max-h-60 space-y-2 overflow-y-auto">
                                                {compensacaoHorarios.map(
                                                    (
                                                        dia: {
                                                            data: string;
                                                            horario: number[];
                                                        },
                                                        index: number,
                                                    ) => (
                                                        <div
                                                            key={index}
                                                            className="bg-base-100 flex items-center gap-4 rounded-lg p-2"
                                                        >
                                                            <div className="font-bold">
                                                                {formatarData(
                                                                    dia.data,
                                                                )}
                                                            </div>
                                                            <div className="text-base-content/80 text-sm">
                                                                {format(
                                                                    parseISO(
                                                                        dia.data,
                                                                    ),
                                                                    'EEEE',
                                                                    {
                                                                        locale: ptBR,
                                                                    },
                                                                )}
                                                            </div>
                                                            <div className="flex flex-wrap gap-1">
                                                                {dia.horario.map(
                                                                    (
                                                                        h: number,
                                                                    ) => (
                                                                        <span
                                                                            key={
                                                                                h
                                                                            }
                                                                            className="badge badge-outline"
                                                                        >
                                                                            {h}h
                                                                        </span>
                                                                    ),
                                                                )}
                                                            </div>
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {/* Botões de Ação para Coordenador */}
                                {auth.isCoordenador &&
                                    ausencia.status === 'PENDENTE' && (
                                        <div className="border-base-300 mt-6 flex justify-end gap-2 border-t pt-6">
                                            <Link
                                                href={route(
                                                    'ausencias.updateStatus',
                                                    ausencia.id,
                                                )}
                                                method="patch"
                                                data={{ status: 'REJEITADO' }} // Envia o novo status
                                                as="button"
                                                className="btn btn-error"
                                                preserveScroll
                                            >
                                                Recusar
                                            </Link>
                                            <Link
                                                href={route(
                                                    'ausencias.updateStatus',
                                                    ausencia.id,
                                                )}
                                                method="patch"
                                                data={{ status: 'APROVADO' }} // Envia o novo status
                                                as="button"
                                                className="btn btn-success"
                                                preserveScroll
                                            >
                                                Aprovar
                                            </Link>
                                        </div>
                                    )}

                                {/* Informações de auditoria */}
                                <div className="divider my-4"></div>
                                <div className="text-base-content/70 space-y-1 text-sm">
                                    <div>
                                        <strong>Criado em:</strong>{' '}
                                        {formatarDataHora(ausencia.created_at)}
                                    </div>
                                    {ausencia.updated_at !==
                                        ausencia.created_at && (
                                        <div>
                                            <strong>Última atualização:</strong>{' '}
                                            {formatarDataHora(
                                                ausencia.updated_at,
                                            )}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Show;
