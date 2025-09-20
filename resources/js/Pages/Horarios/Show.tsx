import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface Baia {
    id: string;
    nome: string;
    sala: {
        id: string;
        nome: string;
    } | null;
}

interface HorarioItem {
    id: string;
    horario: number;
    tipo: string;
    baia: Baia | null;
}

interface DiaHorario {
    nome: string;
    horarios: HorarioItem[];
}

interface Colaborador {
    id: string;
    name: string;
    email: string;
}

interface Projeto {
    id: string;
    nome: string;
    cliente: string;
}

interface Vinculo {
    id: string;
    funcao: string;
    carga_horaria: number;
    data_inicio: string;
}

interface ShowPageProps extends PageProps {
    colaborador: Colaborador;
    projeto: Projeto;
    vinculo: Vinculo;
    horarios: Record<string, DiaHorario>;
    can_edit: boolean;
    horarioLastUpdatedAt: string | null;
}

const formatarHorario = (horario: number): string => {
    return `${horario.toString().padStart(2, '0')}:00`;
};

const getTipoLabel = (tipo: string): string => {
    const tipos: Record<string, string> = {
        TRABALHO_PRESENCIAL: 'Trabalho Presencial',
        TRABALHO_REMOTO: 'Trabalho Remoto',
        EM_AULA: 'Em Aula',
        AUSENTE: 'Ausente',
    };
    return tipos[tipo] || tipo;
};

const getTipoBadgeClass = (tipo: string): string => {
    const classes: Record<string, string> = {
        TRABALHO_PRESENCIAL: 'badge-success',
        TRABALHO_REMOTO: 'badge-primary',
        EM_AULA: 'badge-info',
        AUSENTE: 'badge-error',
    };
    return classes[tipo] || 'badge-neutral';
};

export default function Show({
    colaborador,
    projeto,
    vinculo,
    horarios,
    horarioLastUpdatedAt,
}: ShowPageProps) {
    console.log({
        colaborador,
        projeto,
        vinculo,
        horarios,
        horarioLastUpdatedAt,
    });

    const diasOrdem = [
        'SEGUNDA',
        'TERCA',
        'QUARTA',
        'QUINTA',
        'SEXTA',
        'SABADO',
        'DOMINGO',
    ];

    return (
        <AuthenticatedLayout>
            <Head title={`Horários - ${colaborador.name} - ${projeto.nome}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="card card-bordered bg-base-100 shadow-xl">
                        <div className="card-body">
                            {/* Header */}
                            <div className="mb-6">
                                <div className="mb-4 flex items-center justify-between">
                                    <div>
                                        <h1 className="text-base-content text-3xl font-bold">
                                            Horários do Colaborador
                                        </h1>
                                        <div className="breadcrumbs text-sm">
                                            <ul>
                                                <li>
                                                    <Link
                                                        href={route(
                                                            'colaboradores.show',
                                                            colaborador.id,
                                                        )}
                                                        className="link link-hover"
                                                    >
                                                        {colaborador.name}
                                                    </Link>
                                                </li>
                                                <li>
                                                    <Link
                                                        href={route(
                                                            'projetos.show',
                                                            projeto.id,
                                                        )}
                                                        className="link link-hover"
                                                    >
                                                        {projeto.nome}
                                                    </Link>
                                                </li>
                                                <li>Horários</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {/* Informações do Projeto e Vínculo */}
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div className="stats shadow">
                                        <div className="stat">
                                            <div className="stat-title">
                                                Projeto
                                            </div>
                                            <div className="stat-value text-lg">
                                                {projeto.nome}
                                            </div>
                                            <div className="stat-desc">
                                                {projeto.cliente}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="stats shadow">
                                        <div className="stat">
                                            <div className="stat-title">
                                                Função
                                            </div>
                                            <div className="stat-value text-lg">
                                                {vinculo.funcao}
                                            </div>
                                            <div className="stat-desc">
                                                Colaborador
                                            </div>
                                        </div>
                                    </div>

                                    <div className="stats shadow">
                                        <div className="stat">
                                            <div className="stat-title">
                                                Carga Horária
                                            </div>
                                            <div className="stat-value text-lg">
                                                {vinculo.carga_horaria}h/mês
                                            </div>
                                            <div className="stat-desc">
                                                {Math.round(
                                                    vinculo.carga_horaria / 4,
                                                )}
                                                h/semana
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Resumo de Horas */}
                            <div className="divider">Resumo Semanal</div>

                            <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                                {Object.entries({
                                    TRABALHO_PRESENCIAL: 'success',
                                    TRABALHO_REMOTO: 'primary',
                                    EM_AULA: 'info',
                                    AUSENTE: 'error',
                                }).map(([tipo, color]) => {
                                    const totalHoras = diasOrdem.reduce(
                                        (total, dia) => {
                                            const diaHorarios = horarios[dia];
                                            if (!diaHorarios) return total;
                                            return (
                                                total +
                                                diaHorarios.horarios.filter(
                                                    (h) => h.tipo === tipo,
                                                ).length
                                            );
                                        },
                                        0,
                                    );

                                    return (
                                        <div
                                            key={tipo}
                                            className={`stats stats-vertical shadow bg-${color}/10`}
                                        >
                                            <div className="stat">
                                                <div className="stat-title">
                                                    {getTipoLabel(tipo)}
                                                </div>
                                                <div
                                                    className={`stat-value text-2xl text-${color}`}
                                                >
                                                    {totalHoras}h
                                                </div>
                                                <div className="stat-desc">
                                                    na semana
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            <div className="divider">Horários da Semana</div>

                            {/* Grade de Horários */}
                            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                                {diasOrdem.map((dia) => {
                                    const diaHorarios = horarios[dia];
                                    if (!diaHorarios) return null;

                                    return (
                                        <div
                                            key={dia}
                                            className="card card-bordered bg-base-50"
                                        >
                                            <div className="card-header">
                                                <h3 className="card-title p-4 pb-2 text-lg font-semibold">
                                                    {diaHorarios.nome}
                                                </h3>
                                            </div>
                                            <div className="card-body pt-2">
                                                {diaHorarios.horarios.length >
                                                0 ? (
                                                    <div className="space-y-2">
                                                        {diaHorarios.horarios.map(
                                                            (horario) => (
                                                                <div
                                                                    key={
                                                                        horario.id
                                                                    }
                                                                    className="bg-base-100 flex items-center justify-between rounded-lg border p-3"
                                                                >
                                                                    <div className="flex items-center space-x-3">
                                                                        <div className="font-mono text-sm">
                                                                            {formatarHorario(
                                                                                horario.horario,
                                                                            )}
                                                                        </div>
                                                                        <div
                                                                            className={`badge badge-sm ${getTipoBadgeClass(horario.tipo)}`}
                                                                        >
                                                                            {getTipoLabel(
                                                                                horario.tipo,
                                                                            )}
                                                                        </div>
                                                                    </div>

                                                                    {horario.baia && (
                                                                        <div className="text-base-content/70 text-xs">
                                                                            <div className="font-medium">
                                                                                {
                                                                                    horario
                                                                                        .baia
                                                                                        .nome
                                                                                }
                                                                            </div>
                                                                            {horario
                                                                                .baia
                                                                                .sala && (
                                                                                <div>
                                                                                    {
                                                                                        horario
                                                                                            .baia
                                                                                            .sala
                                                                                            .nome
                                                                                    }
                                                                                </div>
                                                                            )}
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                ) : (
                                                    <div className="text-base-content/50 py-6 text-center">
                                                        <svg
                                                            className="mx-auto mb-2 h-8 w-8"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth={1}
                                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                            />
                                                        </svg>
                                                        <p className="text-sm">
                                                            Sem horários
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                            <div>
                                {horarioLastUpdatedAt && (
                                    <p className="text-base-content/50 mt-6 text-sm">
                                        Última atualização dos horários:{' '}
                                        {new Date(
                                            horarioLastUpdatedAt.replace(
                                                ' ',
                                                'T',
                                            ) + 'Z',
                                        ).toLocaleString('pt-BR', {
                                            dateStyle: 'short',
                                            timeStyle: 'short',
                                        })}
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
