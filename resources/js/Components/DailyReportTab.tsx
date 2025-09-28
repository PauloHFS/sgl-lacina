import { DailyReport } from '@/types';
import { addDays, format, subDays } from 'date-fns';
import React from 'react';
import { Table, ColumnDefinition } from './Table';
import { router } from '@inertiajs/react';

interface DailyReportTabProps {
    dia: string; // yyyy-MM-dd
    onChangeDia: (dia: string) => void;
    loading: boolean;
    dailyReports: DailyReport[];
    totalParticipantes: number;
}

export default function DailyReportTab({
    dia,
    onChangeDia,
    loading,
    dailyReports,
    totalParticipantes,
}: DailyReportTabProps) {
    const today = format(new Date(), 'yyyy-MM-dd');

    const handlePrev = () => {
        const [year, month, day] = dia.split('-').map(Number);
        const currentDate = new Date(year, month - 1, day);
        onChangeDia(format(subDays(currentDate, 1), 'yyyy-MM-dd'));
    };
    const handleNext = () => {
        const [year, month, day] = dia.split('-').map(Number);
        const currentDate = new Date(year, month - 1, day);
        onChangeDia(format(addDays(currentDate, 1), 'yyyy-MM-dd'));
    };
    const handleDateChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        onChangeDia(e.target.value);
    };

    const handleRowClick = (dr: DailyReport) => {
        router.visit(route('daily-reports.show', dr.id));
    };

    const columns: ColumnDefinition<DailyReport>[] = [
        {
            header: 'Colaborador',
            accessor: 'usuario',
            render: (dr) => (
                <div className="flex items-center gap-3">
                    <div className="avatar">
                        <div className="mask mask-squircle h-12 w-12">
                            <img
                                src={
                                    dr.usuario?.foto_url
                                        ? dr.usuario?.foto_url
                                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                              dr.usuario?.name ?? 'Usuário Lacina',
                                          )}&background=random&color=fff`
                                }
                                alt={`Foto de ${dr.usuario?.name}`}
                            />
                        </div>
                    </div>
                    <div>
                        <div className="font-bold">
                            {dr.usuario?.name || 'Usuário Desconhecido'}
                        </div>
                    </div>
                </div>
            ),
        },
        {
            header: 'Horas Trabalhadas',
            accessor: 'horas_trabalhadas',
            render: (dr) => (
                <span className="badge badge-info">{dr.horas_trabalhadas}h</span>
            ),
        },
    ];

    const paginatedDailyReports = {
        data: dailyReports,
        links: [],
        meta: {
            current_page: 1,
            from: 1,
            last_page: 1,
            links: [],
            path: '',
            per_page: dailyReports.length,
            to: dailyReports.length,
            total: dailyReports.length,
        },
    };

    return (
        <div className="space-y-4">
            {/* Navegação de dias */}
            <div className="mb-4 flex items-center justify-center gap-2">
                <button className="btn btn-sm btn-outline" onClick={handlePrev}>
                    &#60;
                </button>
                <input
                    type="date"
                    className="input input-bordered input-sm w-40 text-center"
                    value={dia}
                    onChange={handleDateChange}
                    max={today}
                />
                <button
                    className="btn btn-sm btn-outline"
                    onClick={handleNext}
                    disabled={dia === today}
                >
                    &#62;
                </button>
            </div>
            <div className="mb-2 flex items-center justify-between">
                <span className="font-semibold">
                    Dailys feitas: {dailyReports.length} / {totalParticipantes}
                </span>
                {dailyReports.length < totalParticipantes && (
                    <span className="badge badge-warning">
                        {totalParticipantes - dailyReports.length} pendente(s)
                    </span>
                )}
            </div>
            <div className="min-h-96 space-y-4">
                {loading ? (
                    <div className="text-center">Carregando...</div>
                ) : (
                    <Table
                        data={paginatedDailyReports}
                        columns={columns}
                        onRowClick={handleRowClick}
                        emptyMessage="Nenhuma daily report encontrada para este dia."
                    />
                )}
            </div>
        </div>
    );
}
