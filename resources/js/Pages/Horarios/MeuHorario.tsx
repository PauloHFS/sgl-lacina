import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { ChangeEvent, FormEventHandler, useMemo } from 'react';

const DIAS_SEMANA_HORARIO = [
    { id: 'seg', nome: 'Segunda' },
    { id: 'ter', nome: 'Terça' },
    { id: 'qua', nome: 'Quarta' },
    { id: 'qui', nome: 'Quinta' },
    { id: 'sex', nome: 'Sexta' },
];

const TIME_SLOTS_HORARIO = [
    '07:00',
    '08:00',
    '09:00',
    '10:00',
    '11:00',
    '12:00',
    '13:00',
    '14:00',
    '15:00',
    '16:00',
    '17:00',
    '18:00',
];

type StatusHorario = 'Ausente' | 'Presente' | 'Em Aula';

const STATUS_OPTIONS_HORARIO: StatusHorario[] = [
    'Ausente',
    'Presente',
    'Em Aula',
];

interface HorarioSlotState {
    [timeSlot: string]: StatusHorario;
}

interface HorariosTableState {
    [dayId: string]: HorarioSlotState;
}

const getStatusColorClass = (status: StatusHorario | undefined): string => {
    if (!status) return 'bg-base-100';
    switch (status) {
        case 'Ausente':
            return 'bg-red-200 !text-red-800';
        case 'Presente':
            return 'bg-green-200 !text-green-800';
        case 'Em Aula':
            return 'bg-yellow-200 !text-yellow-800';
        default:
            return 'bg-base-100';
    }
};

const initialHorarios = DIAS_SEMANA_HORARIO.reduce((acc, dia) => {
    acc[dia.id] = TIME_SLOTS_HORARIO.reduce((dayAcc, slot) => {
        dayAcc[slot] = 'Ausente';

        if (slot === '08:00' || slot === '09:00') {
            dayAcc[slot] = 'Em Aula';
        } else if (slot === '10:00' || slot === '11:00') {
            dayAcc[slot] = 'Presente';
        } else if (slot === '14:00' || slot === '15:00') {
            dayAcc[slot] = 'Em Aula';
        } else if (slot === '16:00' || slot === '17:00') {
            dayAcc[slot] = 'Presente';
        }
        return dayAcc;
    }, {} as HorarioSlotState);
    return acc;
}, {} as HorariosTableState);

export default function MeuHorario() {
    const { data, setData, processing } = useForm<{
        horarios: HorariosTableState;
    }>({
        horarios: initialHorarios,
    });

    const handleStatusChange = (
        diaId: string,
        timeSlot: string,
        event: ChangeEvent<HTMLSelectElement>,
    ) => {
        const newStatus = event.target.value as StatusHorario;
        const currentDaySchedule = data.horarios[diaId] || {};
        setData('horarios', {
            ...data.horarios,
            [diaId]: {
                ...currentDaySchedule,
                [timeSlot]: newStatus,
            },
        });
    };

    const handleSubmit: FormEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();
        console.log('Dados do formulário (nova estrutura):', data.horarios);
        alert('Horários salvos (mock)! Verifique o console.');
        // post(route('horarios.store'), { data: { horarios: data.horarios } });
    };

    const statusCounts = useMemo(() => {
        const counts: Record<StatusHorario, number> = {
            Ausente: 0,
            Presente: 0,
            'Em Aula': 0,
        };
        DIAS_SEMANA_HORARIO.forEach((dia) => {
            TIME_SLOTS_HORARIO.forEach((slot) => {
                const status = data.horarios[dia.id]?.[slot];
                if (status && status in counts) {
                    counts[status]++;
                } else if (!status) {
                    counts.Ausente++;
                }
            });
        });
        return counts;
    }, [data.horarios]);

    return (
        <AuthenticatedLayout>
            <Head title="Meus Horários" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 dark:bg-base-100 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="text-base-content dark:text-base-content p-6">
                            <form onSubmit={handleSubmit}>
                                <div className="mb-6 overflow-x-auto">
                                    <table className="table-sm table w-full text-center">
                                        <thead>
                                            <tr className="bg-base-300">
                                                <th className="border-base-300 w-32 border p-2">
                                                    Horário
                                                </th>
                                                {DIAS_SEMANA_HORARIO.map(
                                                    (dia) => (
                                                        <th
                                                            key={dia.id}
                                                            className="border-base-300 border p-2"
                                                        >
                                                            {dia.nome}
                                                        </th>
                                                    ),
                                                )}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {TIME_SLOTS_HORARIO.map((slot) => {
                                                const slotHour = parseInt(
                                                    slot.split(':')[0],
                                                );
                                                const nextHour =
                                                    (slotHour + 1)
                                                        .toString()
                                                        .padStart(2, '0') +
                                                    ':00';
                                                return (
                                                    <tr key={slot}>
                                                        <td className="border-base-300 border p-2 font-semibold">
                                                            {`${slot} - ${nextHour}`}
                                                        </td>
                                                        {DIAS_SEMANA_HORARIO.map(
                                                            (dia) => (
                                                                <td
                                                                    key={`${dia.id}-${slot}`}
                                                                    className={`border-base-300 border p-1 ${getStatusColorClass(data.horarios[dia.id]?.[slot])}`}
                                                                >
                                                                    <select
                                                                        name={`${dia.id}-${slot}-status`}
                                                                        value={
                                                                            data
                                                                                .horarios[
                                                                                dia
                                                                                    .id
                                                                            ]?.[
                                                                                slot
                                                                            ] ||
                                                                            'Ausente'
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            handleStatusChange(
                                                                                dia.id,
                                                                                slot,
                                                                                e,
                                                                            )
                                                                        }
                                                                        className="select select-bordered bg-base-100 text-base-content focus:border-primary w-full focus:outline-none"
                                                                    >
                                                                        {STATUS_OPTIONS_HORARIO.map(
                                                                            (
                                                                                opt,
                                                                            ) => (
                                                                                <option
                                                                                    key={
                                                                                        opt
                                                                                    }
                                                                                    value={
                                                                                        opt
                                                                                    }
                                                                                >
                                                                                    {
                                                                                        opt
                                                                                    }
                                                                                </option>
                                                                            ),
                                                                        )}
                                                                    </select>
                                                                </td>
                                                            ),
                                                        )}
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="card card-bordered bg-base-200 mt-6 shadow">
                                    <div className="card-body">
                                        <h4 className="card-title mb-3 text-lg font-semibold">
                                            Legenda & Contagem
                                        </h4>
                                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                                            {STATUS_OPTIONS_HORARIO.map(
                                                (status) => (
                                                    <div
                                                        key={status}
                                                        className={`flex items-center space-x-2 rounded-lg p-3 shadow ${getStatusColorClass(status)}`}
                                                    >
                                                        <span
                                                            className={`border-base-content/20 h-5 w-5 rounded-full border ${getStatusColorClass(status)}`}
                                                        ></span>
                                                        <span className="font-medium">
                                                            {status}:
                                                        </span>
                                                        <span className="text-lg font-bold">
                                                            {
                                                                statusCounts[
                                                                    status
                                                                ]
                                                            }
                                                        </span>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-8 flex justify-end">
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing
                                            ? 'Salvando...'
                                            : 'Salvar Horários'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
