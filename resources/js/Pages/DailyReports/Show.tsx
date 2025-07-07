import WysiwygViewer from '@/Components/WysiwygViewerPrism';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

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
    usuario: {
        id: string;
        name: string;
        email: string;
    };
    created_at: string;
    updated_at: string;
}

interface ShowPageProps extends PageProps {
    dailyReport: DailyReport;
}

const formatarData = (data: string): string => {
    const date = new Date(data);
    // Adiciona o fuso horário para evitar problemas de data "um dia antes"
    const userTimezoneOffset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() + userTimezoneOffset).toLocaleDateString(
        'pt-BR',
    );
};

const formatarDataHora = (data: string): string => {
    return new Date(data).toLocaleString('pt-BR');
};

const Show = ({ dailyReport }: ShowPageProps) => {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-base-content text-xl leading-tight font-semibold">
                        Daily Report - {formatarData(dailyReport.data)}
                    </h2>
                    <div className="flex gap-2">
                        <Link
                            href={route('daily-reports.edit', dailyReport.id)}
                            className="btn btn-primary"
                        >
                            Editar
                        </Link>
                        <Link
                            href={route('daily-reports.index')}
                            className="btn btn-ghost"
                        >
                            Voltar à Lista
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title={`Daily Report - ${formatarData(dailyReport.data)}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            {/* Informações básicas */}
                            <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div className="stats shadow">
                                    <div className="stat">
                                        <div className="stat-title">Data</div>
                                        <div className="stat-value text-2xl">
                                            {formatarData(dailyReport.data)}
                                        </div>
                                        <div className="stat-desc">
                                            {new Date(
                                                dailyReport.data,
                                            ).toLocaleDateString('pt-BR', {
                                                weekday: 'long',
                                                timeZone: 'UTC',
                                            })}
                                        </div>
                                    </div>
                                </div>

                                <div className="stats shadow">
                                    <div className="stat">
                                        <div className="stat-title">
                                            Horas Trabalhadas
                                        </div>
                                        <div className="stat-value text-primary text-2xl">
                                            {dailyReport.horas_trabalhadas}h
                                        </div>
                                        <div className="stat-desc">
                                            Tempo dedicado ao projeto
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Informações do projeto */}
                            <div className="alert alert-info mb-6">
                                <div>
                                    <h3 className="font-bold">Projeto</h3>
                                    <div className="text-sm">
                                        <strong>
                                            {dailyReport.projeto.nome}
                                        </strong>{' '}
                                        - {dailyReport.projeto.cliente}
                                    </div>
                                </div>
                            </div>

                            {/* Conteúdo do daily report */}
                            <div className="space-y-6">
                                <div className="collapse-arrow bg-base-200 collapse">
                                    <input type="checkbox" defaultChecked />
                                    <div className="collapse-title text-xl font-medium">
                                        O que fez ontem
                                    </div>
                                    <div className="collapse-content">
                                        <WysiwygViewer
                                            content={
                                                dailyReport.o_que_fez_ontem
                                            }
                                            className="min-h-[60px]"
                                        />
                                    </div>
                                </div>

                                <div className="collapse-arrow bg-base-200 collapse">
                                    <input type="checkbox" defaultChecked />
                                    <div className="collapse-title text-xl font-medium">
                                        O que vai fazer hoje
                                    </div>
                                    <div className="collapse-content">
                                        <WysiwygViewer
                                            content={
                                                dailyReport.o_que_vai_fazer_hoje
                                            }
                                            className="min-h-[60px]"
                                        />
                                    </div>
                                </div>

                                {dailyReport.observacoes && (
                                    <div className="collapse-arrow bg-base-200 collapse">
                                        <input type="checkbox" defaultChecked />
                                        <div className="collapse-title text-xl font-medium">
                                            Observações
                                        </div>
                                        <div className="collapse-content">
                                            <WysiwygViewer
                                                content={
                                                    dailyReport.observacoes
                                                }
                                                className="min-h-[60px]"
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Informações de auditoria */}
                            <div className="divider my-8"></div>

                            <div className="text-base-content/70 space-y-1 text-sm">
                                <div>
                                    <strong>Criado em:</strong>{' '}
                                    {formatarDataHora(dailyReport.created_at)}
                                </div>
                                {dailyReport.updated_at !==
                                    dailyReport.created_at && (
                                    <div>
                                        <strong>Última atualização:</strong>{' '}
                                        {formatarDataHora(
                                            dailyReport.updated_at,
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Show;
