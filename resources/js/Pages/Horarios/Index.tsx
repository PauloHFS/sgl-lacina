import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { DiaDaSemana, Horario, PageProps, TipoHorario } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { ChangeEvent, FormEventHandler, useMemo } from 'react';

const DIAS_SEMANA_HORARIO = [
    { id: 'SEGUNDA', nome: 'Segunda' },
    { id: 'TERCA', nome: 'Terça' },
    { id: 'QUARTA', nome: 'Quarta' },
    { id: 'QUINTA', nome: 'Quinta' },
    { id: 'SEXTA', nome: 'Sexta' },
] as const;

const TIME_SLOTS_HORARIO = [
    7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
];

const STATUS_OPTIONS_HORARIO: TipoHorario[] = [
    'AUSENTE',
    'EM_AULA',
    'TRABALHO_PRESENCIAL',
    'TRABALHO_REMOTO',
];

interface HorarioSlotState {
    [timeSlot: number]: TipoHorario;
}

interface HorariosTableState {
    [dayId: string]: HorarioSlotState;
}

const getStatusColorClass = (status: TipoHorario | undefined): string => {
    if (!status) return 'bg-base-100';
    switch (status) {
        case 'AUSENTE':
            return 'bg-red-200 !text-red-800';
        case 'TRABALHO_PRESENCIAL':
            return 'bg-green-200 !text-green-800';
        case 'TRABALHO_REMOTO':
            return 'bg-blue-200 !text-blue-800';
        case 'EM_AULA':
            return 'bg-yellow-200 !text-yellow-800';
        default:
            return 'bg-base-100';
    }
};

const getStatusDisplayName = (status: TipoHorario): string => {
    switch (status) {
        case 'AUSENTE':
            return 'Ausente';
        case 'EM_AULA':
            return 'Em Aula';
        case 'TRABALHO_PRESENCIAL':
            return 'Trabalho Presencial';
        case 'TRABALHO_REMOTO':
            return 'Trabalho Remoto';
        default:
            return status;
    }
};

// Função para converter os horários do backend para a estrutura do frontend
const convertHorariosToTableState = (
    horariosFromBackend: Record<DiaDaSemana, Array<Horario>>,
): HorariosTableState => {
    const tableState: HorariosTableState = {};

    // Inicializa todos os slots como AUSENTE
    DIAS_SEMANA_HORARIO.forEach((dia) => {
        tableState[dia.id] = {};
        TIME_SLOTS_HORARIO.forEach((slot) => {
            tableState[dia.id][slot] = 'AUSENTE';
        });
    });

    // Preenche com os dados do backend
    Object.entries(horariosFromBackend).forEach(([dia, horarios]) => {
        if (tableState[dia]) {
            horarios.forEach((horario) => {
                if (TIME_SLOTS_HORARIO.includes(horario.horario)) {
                    tableState[dia][horario.horario] = horario.tipo;
                }
            });
        }
    });

    console.log('Estado da tabela convertido:', tableState);

    return tableState;
};

export default function MeuHorario({
    horarios,
}: PageProps<{
    horarios: Record<DiaDaSemana, Array<Horario>>;
}>) {
    const initialHorarios = useMemo(() => {
        return convertHorariosToTableState(horarios);
    }, [horarios]);

    const { data, setData, processing } = useForm<{
        horarios: HorariosTableState;
    }>({
        horarios: initialHorarios,
    });

    const handleStatusChange = (
        diaId: string,
        timeSlot: number,
        event: ChangeEvent<HTMLSelectElement>,
    ) => {
        const newStatus = event.target.value as TipoHorario;
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
        const counts: Record<TipoHorario, number> = {
            AUSENTE: 0,
            EM_AULA: 0,
            TRABALHO_PRESENCIAL: 0,
            TRABALHO_REMOTO: 0,
        };
        DIAS_SEMANA_HORARIO.forEach((dia) => {
            TIME_SLOTS_HORARIO.forEach((slot) => {
                const status = data.horarios[dia.id]?.[slot];
                if (status && status in counts) {
                    counts[status]++;
                } else if (!status) {
                    counts.AUSENTE++;
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
                                                const nextHour = slot + 1;
                                                const slotFormatted =
                                                    slot
                                                        .toString()
                                                        .padStart(2, '0') +
                                                    ':00';
                                                const nextHourFormatted =
                                                    nextHour
                                                        .toString()
                                                        .padStart(2, '0') +
                                                    ':00';

                                                return (
                                                    <tr key={slot}>
                                                        <td className="border-base-300 border p-2 font-semibold">
                                                            {`${slotFormatted} - ${nextHourFormatted}`}
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
                                                                            'AUSENTE'
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
                                                                                    {getStatusDisplayName(
                                                                                        opt,
                                                                                    )}
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
                                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4">
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
                                                            {getStatusDisplayName(
                                                                status,
                                                            )}
                                                            :
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
