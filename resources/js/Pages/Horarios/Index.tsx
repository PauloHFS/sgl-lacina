import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { DiaDaSemana, Horario, PageProps, TipoHorario } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useMemo } from 'react';

const DIAS_SEMANA_HORARIO = [
    { id: 'SEGUNDA', nome: 'Segunda' },
    { id: 'TERCA', nome: 'Terça' },
    { id: 'QUARTA', nome: 'Quarta' },
    { id: 'QUINTA', nome: 'Quinta' },
    { id: 'SEXTA', nome: 'Sexta' },
    { id: 'SABADO', nome: 'Sábado' },
] as const;

const TIME_SLOTS_HORARIO = [
    7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
];

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

export default function MeuHorario({
    horarios,
}: PageProps<{
    horarios: Record<DiaDaSemana, Array<Horario>>;
}>) {
    const statusCounts = useMemo(() => {
        const counts: Record<TipoHorario, number> = {
            AUSENTE: 0,
            EM_AULA: 0,
            TRABALHO_PRESENCIAL: 0,
            TRABALHO_REMOTO: 0,
        };

        // Contar status de todos os horários
        Object.entries(horarios).forEach(([, horariosArray]) => {
            horariosArray.forEach((horario) => {
                if (horario.tipo in counts) {
                    counts[horario.tipo]++;
                }
            });
        });

        // Contar slots ausentes (não preenchidos)
        const totalSlots =
            DIAS_SEMANA_HORARIO.length * TIME_SLOTS_HORARIO.length;
        const preenchidos = Object.values(horarios).reduce(
            (acc, arr) => acc + arr.length,
            0,
        );
        counts.AUSENTE = totalSlots - preenchidos;

        return counts;
    }, [horarios]);

    const getHorarioForSlot = (
        dia: DiaDaSemana,
        slot: number,
    ): Horario | undefined => {
        return horarios[dia]?.find((h) => h.horario === slot);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Meus Horários" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 dark:bg-base-100 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="text-base-content dark:text-base-content p-6">
                            <div className="mb-6 flex items-center justify-between">
                                <h1 className="text-2xl font-bold">
                                    Meus Horários
                                </h1>
                                <Link
                                    href={route('horarios.edit')}
                                    className="btn btn-primary"
                                >
                                    Editar Horários
                                </Link>
                            </div>

                            <div className="mb-6 overflow-x-auto">
                                <table className="table w-full text-center">
                                    <thead>
                                        <tr className="bg-base-300">
                                            <th className="border-base-300 w-32 border p-2">
                                                Horário
                                            </th>
                                            {DIAS_SEMANA_HORARIO.map((dia) => (
                                                <th
                                                    key={dia.id}
                                                    className="border-base-300 border p-2"
                                                >
                                                    {dia.nome}
                                                </th>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {TIME_SLOTS_HORARIO.map((slot) => {
                                            const nextHour = slot + 1;
                                            const slotFormatted =
                                                slot
                                                    .toString()
                                                    .padStart(2, '0') + ':00';
                                            const nextHourFormatted =
                                                nextHour
                                                    .toString()
                                                    .padStart(2, '0') + ':00';

                                            return (
                                                <tr key={slot}>
                                                    <td className="border-base-300 border p-2 font-semibold">
                                                        {`${slotFormatted} - ${nextHourFormatted}`}
                                                    </td>
                                                    {DIAS_SEMANA_HORARIO.map(
                                                        (dia) => {
                                                            const horario =
                                                                getHorarioForSlot(
                                                                    dia.id,
                                                                    slot,
                                                                );
                                                            const status =
                                                                horario?.tipo ||
                                                                'AUSENTE';

                                                            return (
                                                                <td
                                                                    key={`${dia.id}-${slot}`}
                                                                    className={`border-base-300 border p-3 text-sm font-medium ${getStatusColorClass(status)}`}
                                                                >
                                                                    <div className="text-center">
                                                                        <div className="font-semibold">
                                                                            {getStatusDisplayName(
                                                                                status,
                                                                            )}
                                                                        </div>
                                                                        {horario?.baia && (
                                                                            <div className="mt-1 text-xs">
                                                                                {horario
                                                                                    .baia
                                                                                    .sala
                                                                                    ?.nome &&
                                                                                    !horario.baia.sala.nome
                                                                                        .toLowerCase()
                                                                                        .includes(
                                                                                            'sala',
                                                                                        ) && (
                                                                                        <div className="flex items-center gap-1">
                                                                                            <span className="opacity-75">
                                                                                                Sala:
                                                                                            </span>
                                                                                            <span className="font-medium">
                                                                                                {
                                                                                                    horario
                                                                                                        .baia
                                                                                                        .sala
                                                                                                        .nome
                                                                                                }
                                                                                            </span>
                                                                                        </div>
                                                                                    )}
                                                                                {!horario.baia.nome
                                                                                    .toLowerCase()
                                                                                    .includes(
                                                                                        'baia',
                                                                                    ) && (
                                                                                    <div className="flex items-center gap-1">
                                                                                        <span className="opacity-75">
                                                                                            Baia:
                                                                                        </span>
                                                                                        <span className="font-medium">
                                                                                            {
                                                                                                horario
                                                                                                    .baia
                                                                                                    .nome
                                                                                            }
                                                                                        </span>
                                                                                    </div>
                                                                                )}
                                                                                {horario
                                                                                    .baia
                                                                                    .sala
                                                                                    ?.nome &&
                                                                                    horario.baia.sala.nome
                                                                                        .toLowerCase()
                                                                                        .includes(
                                                                                            'sala',
                                                                                        ) && (
                                                                                        <div className="font-medium">
                                                                                            {
                                                                                                horario
                                                                                                    .baia
                                                                                                    .sala
                                                                                                    .nome
                                                                                            }
                                                                                        </div>
                                                                                    )}
                                                                                {horario.baia.nome
                                                                                    .toLowerCase()
                                                                                    .includes(
                                                                                        'baia',
                                                                                    ) && (
                                                                                    <div className="font-medium">
                                                                                        {
                                                                                            horario
                                                                                                .baia
                                                                                                .nome
                                                                                        }
                                                                                    </div>
                                                                                )}
                                                                            </div>
                                                                        )}
                                                                    </div>
                                                                </td>
                                                            );
                                                        },
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
                                        Resumo dos Horários
                                    </h4>
                                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4">
                                        {Object.entries(statusCounts).map(
                                            ([status, count]) => (
                                                <div
                                                    key={status}
                                                    className={`flex items-center space-x-2 rounded-lg p-3 shadow ${getStatusColorClass(status as TipoHorario)}`}
                                                >
                                                    <span
                                                        className={`border-base-content/20 h-5 w-5 rounded-full border ${getStatusColorClass(status as TipoHorario)}`}
                                                    ></span>
                                                    <span className="font-medium">
                                                        {getStatusDisplayName(
                                                            status as TipoHorario,
                                                        )}
                                                        :
                                                    </span>
                                                    <span className="text-lg font-bold">
                                                        {count}
                                                    </span>
                                                </div>
                                            ),
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
