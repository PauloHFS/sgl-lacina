import { addDays, format, subDays } from 'date-fns';
import React from 'react';

export interface DailyReport {
    id: string;
    usuario_id: string;
    usuario_nome: string;
    data: string;
    horas_trabalhadas: number;
    o_que_fez_ontem: string | null;
    o_que_vai_fazer_hoje: string | null;
    observacoes: string | null;
}

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
                />
                <button className="btn btn-sm btn-outline" onClick={handleNext}>
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
                ) : dailyReports.length === 0 ? (
                    <div className="text-base-content/60 text-center">
                        Nenhuma daily report encontrada para este dia.
                    </div>
                ) : (
                    dailyReports.map((dr) => (
                        <div
                            key={dr.id}
                            className="card card-bordered bg-base-100"
                        >
                            <div className="card-body">
                                <div className="mb-2 flex items-center justify-between">
                                    <span className="font-bold">
                                        {dr.usuario_nome}
                                    </span>
                                    <span className="badge badge-info">
                                        {dr.horas_trabalhadas}h
                                    </span>
                                </div>
                                <div className="mb-2">
                                    <span className="font-semibold">
                                        O que fez ontem:
                                    </span>
                                    <div>
                                        {dr.o_que_fez_ontem || (
                                            <span className="text-base-content/50">
                                                Não informado
                                            </span>
                                        )}
                                    </div>
                                </div>
                                <div className="mb-2">
                                    <span className="font-semibold">
                                        O que vai fazer hoje:
                                    </span>
                                    <div>
                                        {dr.o_que_vai_fazer_hoje || (
                                            <span className="text-base-content/50">
                                                Não informado
                                            </span>
                                        )}
                                    </div>
                                </div>
                                {dr.observacoes && (
                                    <div className="mb-2">
                                        <span className="font-semibold">
                                            Observações:
                                        </span>
                                        <div>{dr.observacoes}</div>
                                    </div>
                                )}
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}
