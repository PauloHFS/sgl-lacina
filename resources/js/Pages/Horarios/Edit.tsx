import HorarioTrabalhoModal from '@/Components/HorarioTrabalhoModal';
import { useToast } from '@/Context/ToastProvider';
import { useProjetoPreferences } from '@/hooks/useProjetoPreferences';
import { useSalaBaiaPreferences } from '@/hooks/useSalaBaiaPreferences';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    DiaDaSemana,
    Horario,
    PageProps,
    ProjetoAtivo,
    Sala,
    SalaDisponivel,
    TipoHorario,
} from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import {
    ChangeEvent,
    FormEventHandler,
    useEffect,
    useMemo,
    useState,
} from 'react';

const DIAS_SEMANA_HORARIO = [
    { id: 'SEGUNDA', nome: 'Segunda' },
    { id: 'TERCA', nome: 'Terça' },
    { id: 'QUARTA', nome: 'Quarta' },
    { id: 'QUINTA', nome: 'Quinta' },
    { id: 'SEXTA', nome: 'Sexta' },
    { id: 'SABADO', nome: 'Sábado' },
] as const;

const TIME_SLOTS_HORARIO = [
    0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
    21, 22, 23,
];

const STATUS_OPTIONS_HORARIO: TipoHorario[] = [
    'AUSENTE',
    'EM_AULA',
    'TRABALHO_PRESENCIAL',
    'TRABALHO_REMOTO',
];

interface HorarioSlotState {
    tipo: TipoHorario;
    salaId?: string;
    baiaId?: string;
    usuarioProjetoId?: string;
}

interface HorariosTableState {
    [dayId: string]: {
        [timeSlot: number]: HorarioSlotState;
    };
}

interface HorarioChangeData {
    id: string;
    tipo?: TipoHorario;
    baia_id?: string | null;
    usuario_projeto_id?: string | null;
    baia_updated_at?: string;
}

interface HorarioUpdatePayload {
    _method: string;
    horarios: HorarioChangeData[];
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
            tableState[dia.id][slot] = {
                tipo: 'AUSENTE',
                salaId: undefined,
                baiaId: undefined,
                usuarioProjetoId: undefined,
            };
        });
    });

    // Preenche com os dados do backend
    Object.entries(horariosFromBackend).forEach(([dia, horarios]) => {
        if (tableState[dia]) {
            horarios.forEach((horario) => {
                if (TIME_SLOTS_HORARIO.includes(horario.horario)) {
                    tableState[dia][horario.horario] = {
                        tipo: horario.tipo,
                        salaId: horario.baia?.sala?.id,
                        baiaId: horario.baia?.id,
                        usuarioProjetoId:
                            horario.usuario_projeto_id || undefined,
                    };
                }
            });
        }
    });

    return tableState;
};

export default function EditarHorario({
    horarios,
}: PageProps<{
    horarios: Record<DiaDaSemana, Array<Horario>>;
    salas: Sala[];
}>) {
    const { toast } = useToast();
    const { preferences, updateTrabalhoPresencial } = useSalaBaiaPreferences();
    const { updateUltimoProjetoSelecionado, getUltimoProjetoSelecionado } =
        useProjetoPreferences();

    const [modalState, setModalState] = useState<{
        isOpen: boolean;
        tipoTrabalho: 'TRABALHO_PRESENCIAL' | 'TRABALHO_REMOTO' | null;
        diaId: string;
        timeSlot: number;
    }>({
        isOpen: false,
        tipoTrabalho: null,
        diaId: '',
        timeSlot: 0,
    });

    const [salasDisponiveis, setSalasDisponiveis] = useState<SalaDisponivel[]>(
        [],
    );
    const [projetosAtivos, setProjetosAtivos] = useState<ProjetoAtivo[]>([]);
    const [isLoadingModal, setIsLoadingModal] = useState(false);
    const [isLoadingProjetos, setIsLoadingProjetos] = useState(false);

    const initialHorarios = useMemo(() => {
        return convertHorariosToTableState(horarios);
    }, [horarios]);

    const { data, setData, processing, errors } = useForm<{
        horarios: HorariosTableState;
    }>({
        horarios: initialHorarios,
    });

    // Carrega projetos ativos ao montar o componente
    useEffect(() => {
        const carregarProjetosAtivos = async () => {
            setIsLoadingProjetos(true);
            try {
                const response = await fetch(route('horarios.projetos-ativos'));
                const data = await response.json();
                setProjetosAtivos(data.projetos || []);
            } catch (error) {
                console.error('Erro ao carregar projetos ativos:', error);
                toast(
                    'Erro ao carregar projetos. Recarregue a página.',
                    'error',
                );
            } finally {
                setIsLoadingProjetos(false);
            }
        };

        carregarProjetosAtivos();
    }, [toast]);

    // Calcula as horas semanais por projeto com base nos horários selecionados
    const horasProjetoSemanal = useMemo(() => {
        const contadorHoras: Record<string, number> = {};

        Object.values(data.horarios).forEach((diasSlots) => {
            Object.values(diasSlots).forEach((slot) => {
                if (
                    slot.usuarioProjetoId &&
                    (slot.tipo === 'TRABALHO_PRESENCIAL' ||
                        slot.tipo === 'TRABALHO_REMOTO')
                ) {
                    contadorHoras[slot.usuarioProjetoId] =
                        (contadorHoras[slot.usuarioProjetoId] || 0) + 1;
                }
            });
        });

        return contadorHoras;
    }, [data.horarios]);

    const handleStatusChange = (
        diaId: string,
        timeSlot: number,
        event: ChangeEvent<HTMLSelectElement>,
    ) => {
        const newStatus = event.target.value as TipoHorario;

        if (
            newStatus === 'TRABALHO_PRESENCIAL' ||
            newStatus === 'TRABALHO_REMOTO'
        ) {
            setModalState({
                isOpen: true,
                tipoTrabalho: newStatus,
                diaId,
                timeSlot,
            });
            fetchModalData(diaId, timeSlot, newStatus);
        } else {
            // Para outros tipos, atualizar diretamente
            const currentDaySchedule = data.horarios[diaId] || {};
            setData('horarios', {
                ...data.horarios,
                [diaId]: {
                    ...currentDaySchedule,
                    [timeSlot]: {
                        tipo: newStatus,
                        salaId: undefined,
                        baiaId: undefined,
                        usuarioProjetoId: undefined,
                    },
                },
            });
        }
    };

    const fetchModalData = async (
        diaId: string,
        timeSlot: number,
        tipoTrabalho: 'TRABALHO_PRESENCIAL' | 'TRABALHO_REMOTO',
    ) => {
        setIsLoadingModal(true);

        try {
            // Busca projetos ativos sempre
            const projetosResponse = await fetch(
                route('horarios.projetos-ativos'),
            );
            const projetosData = await projetosResponse.json();
            setProjetosAtivos(projetosData.projetos || []);

            // Busca salas disponíveis apenas para trabalho presencial
            if (tipoTrabalho === 'TRABALHO_PRESENCIAL') {
                const salasResponse = await fetch(
                    route('horarios.salas-disponiveis', {
                        dia_da_semana: diaId,
                        horario: timeSlot,
                    }),
                );
                const salasData = await salasResponse.json();
                setSalasDisponiveis(salasData.salas || []);
            } else {
                setSalasDisponiveis([]);
            }
        } catch (error) {
            console.error('Erro ao buscar dados do modal:', error);
            toast('Erro ao carregar dados. Tente novamente.', 'error');
            setModalState({
                isOpen: false,
                tipoTrabalho: null,
                diaId: '',
                timeSlot: 0,
            });
        } finally {
            setIsLoadingModal(false);
        }
    };

    const handleModalConfirm = (
        salaId?: string,
        baiaId?: string,
        projetoId?: string,
    ) => {
        const { tipoTrabalho, diaId, timeSlot } = modalState;

        if (!tipoTrabalho) return;

        // Salva as preferências
        if (tipoTrabalho === 'TRABALHO_PRESENCIAL' && salaId && baiaId) {
            updateTrabalhoPresencial(salaId, baiaId);
        }

        if (projetoId) {
            updateUltimoProjetoSelecionado(projetoId);
        }

        const currentDaySchedule = data.horarios[diaId] || {};
        setData('horarios', {
            ...data.horarios,
            [diaId]: {
                ...currentDaySchedule,
                [timeSlot]: {
                    tipo: tipoTrabalho,
                    salaId:
                        tipoTrabalho === 'TRABALHO_PRESENCIAL'
                            ? salaId
                            : undefined,
                    baiaId:
                        tipoTrabalho === 'TRABALHO_PRESENCIAL'
                            ? baiaId
                            : undefined,
                    usuarioProjetoId: projetoId,
                },
            },
        });

        setModalState({
            isOpen: false,
            tipoTrabalho: null,
            diaId: '',
            timeSlot: 0,
        });
    };

    const handleModalClose = () => {
        setModalState({
            isOpen: false,
            tipoTrabalho: null,
            diaId: '',
            timeSlot: 0,
        });
    };

    // Função para converter dados do frontend para o formato do backend
    const convertToBackendFormat = (
        frontendData: HorariosTableState,
        originalHorarios: Record<DiaDaSemana, Array<Horario>>,
    ) => {
        const changedHorarios: Array<HorarioChangeData> = [];

        // Criar um mapa dos horários originais para facilitar a comparação
        const originalHorariosMap = new Map<string, Horario>();
        Object.values(originalHorarios)
            .flat()
            .forEach((horario) => {
                const key = `${horario.dia_da_semana}-${horario.horario}`;
                originalHorariosMap.set(key, horario);
            });

        // Comparar dados do frontend com os originais
        Object.entries(frontendData).forEach(([dia, slots]) => {
            Object.entries(slots).forEach(([slot, slotData]) => {
                const key = `${dia}-${parseInt(slot)}`;
                const originalHorario = originalHorariosMap.get(key);

                if (originalHorario) {
                    const originalTipo = originalHorario.tipo;
                    const originalBaiaId = originalHorario.baia?.id;
                    const originalUsuarioProjetoId =
                        originalHorario.usuario_projeto_id;

                    const newTipo = slotData.tipo;
                    const newBaiaId = slotData.baiaId;
                    const newUsuarioProjetoId = slotData.usuarioProjetoId;

                    // Verificar se houve mudanças
                    const tipoChanged = originalTipo !== newTipo;
                    const baiaChanged = originalBaiaId !== newBaiaId;
                    const projetoChanged =
                        originalUsuarioProjetoId !== newUsuarioProjetoId;

                    if (tipoChanged || baiaChanged || projetoChanged) {
                        const changeData: HorarioChangeData = {
                            id: originalHorario.id,
                        };

                        if (tipoChanged) {
                            changeData.tipo = newTipo;
                        }

                        if (
                            baiaChanged &&
                            (newTipo === 'TRABALHO_PRESENCIAL' ||
                                originalTipo === 'TRABALHO_PRESENCIAL')
                        ) {
                            changeData.baia_id =
                                newTipo === 'TRABALHO_PRESENCIAL'
                                    ? newBaiaId || null
                                    : null;

                            // Adiciona o timestamp da baia para locking otimista
                            if (newBaiaId && originalHorario.baia?.updated_at) {
                                changeData.baia_updated_at =
                                    originalHorario.baia.updated_at;
                            }
                        }

                        if (
                            projetoChanged &&
                            (newTipo === 'TRABALHO_PRESENCIAL' ||
                                newTipo === 'TRABALHO_REMOTO' ||
                                originalTipo === 'TRABALHO_PRESENCIAL' ||
                                originalTipo === 'TRABALHO_REMOTO')
                        ) {
                            changeData.usuario_projeto_id =
                                newTipo === 'TRABALHO_PRESENCIAL' ||
                                newTipo === 'TRABALHO_REMOTO'
                                    ? newUsuarioProjetoId || null
                                    : null;
                        }

                        changedHorarios.push(changeData);
                    }
                }
            });
        });

        return changedHorarios;
    };

    const handleSubmit: FormEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();

        const horariosChanged = convertToBackendFormat(data.horarios, horarios);
        if (horariosChanged.length === 0) {
            toast('Nenhuma alteração foi feita nos horários.', 'info');
            return;
        }

        // TODO: Concertar isso aqui
        const payload: HorarioUpdatePayload = {
            _method: 'patch',
            horarios: horariosChanged,
        };

        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        router.post(route('horarios.update'), payload as any, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                toast('Horários atualizados com sucesso!', 'success');
            },
            onError: (errors: Record<string, string>) => {
                toast(
                    'Erro ao atualizar horários. Verifique os dados informados.',
                    'error',
                );
                console.error('Erro ao atualizar horários:', errors);
            },
        });
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
                const slotData = data.horarios[dia.id]?.[slot];
                const status = slotData?.tipo || 'AUSENTE';
                if (status in counts) {
                    counts[status]++;
                }
            });
        });
        return counts;
    }, [data.horarios]);

    return (
        <AuthenticatedLayout>
            <Head title="Editar Horários" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 dark:bg-base-100 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="text-base-content dark:text-base-content p-6">
                            <div className="mb-6 flex items-center justify-between">
                                <h1 className="text-2xl font-bold">
                                    Editar Meus Horários
                                </h1>
                                <div className="breadcrumbs text-sm">
                                    <ul>
                                        <li>
                                            <a
                                                href={route('horarios.index')}
                                                className="link"
                                            >
                                                Horários
                                            </a>
                                        </li>
                                        <li>Editar</li>
                                    </ul>
                                </div>
                            </div>

                            {/* Exibição de erros de validação */}
                            {Object.keys(errors).length > 0 && (
                                <div className="alert alert-error mb-6">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="h-6 w-6 shrink-0 stroke-current"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <div>
                                        <h3 className="font-bold">
                                            Erro ao salvar horários!
                                        </h3>
                                        <div className="text-xs">
                                            {Object.entries(errors).map(
                                                ([key, error]) => (
                                                    <div
                                                        key={key}
                                                        className="mt-1"
                                                    >
                                                        <strong>{key}:</strong>{' '}
                                                        {Array.isArray(error)
                                                            ? error.join(', ')
                                                            : error}
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Card de Controle de Carga Horária por Projeto */}
                            <div className="card card-bordered bg-base-200 mt-6 shadow">
                                <div className="card-body">
                                    <h4 className="card-title mb-3 text-lg font-semibold">
                                        Controle de Carga Horária Semanal
                                    </h4>
                                    {isLoadingProjetos ? (
                                        <div className="flex items-center justify-center p-6">
                                            <span className="loading loading-spinner loading-md"></span>
                                            <span className="ml-2">
                                                Carregando projetos...
                                            </span>
                                        </div>
                                    ) : projetosAtivos.length === 0 ? (
                                        <div className="bg-base-300/50 border-base-300 rounded-lg border p-6 text-center">
                                            <p className="text-base-content font-medium">
                                                Você não possui projetos ativos.
                                            </p>
                                            <p className="mt-1 text-sm opacity-70">
                                                Os projetos aparecerão aqui após
                                                serem aprovados.
                                            </p>
                                        </div>
                                    ) : (
                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                                            {projetosAtivos.map((projeto) => {
                                                const horasAlocadas =
                                                    horasProjetoSemanal[
                                                        projeto.id
                                                    ] || 0;
                                                const horasDefinidas =
                                                    projeto.carga_horaria / 4;
                                                const percentual =
                                                    horasDefinidas > 0
                                                        ? Math.round(
                                                              (horasAlocadas /
                                                                  horasDefinidas) *
                                                                  100,
                                                          )
                                                        : 0;

                                                const isExcesso =
                                                    horasAlocadas >
                                                    horasDefinidas;
                                                const isCompleto =
                                                    horasAlocadas ===
                                                    horasDefinidas;
                                                const isParcial =
                                                    horasAlocadas > 0 &&
                                                    horasAlocadas <
                                                        horasDefinidas;

                                                let statusClass =
                                                    'border-neutral bg-base-300 text-base-content';
                                                if (isExcesso) {
                                                    statusClass =
                                                        'border-error bg-error/20 text-error-content shadow-md';
                                                } else if (isCompleto) {
                                                    statusClass =
                                                        'border-success bg-success/20 text-success-content shadow-md';
                                                } else if (isParcial) {
                                                    statusClass =
                                                        'border-warning bg-warning/20 text-warning-content shadow-md';
                                                }

                                                return (
                                                    <div
                                                        key={projeto.id}
                                                        className={`rounded-lg border-2 p-4 transition-colors ${statusClass}`}
                                                    >
                                                        <div className="mb-2">
                                                            <h5
                                                                className="truncate font-semibold"
                                                                title={
                                                                    projeto.projeto_nome
                                                                }
                                                            >
                                                                {
                                                                    projeto.projeto_nome
                                                                }
                                                            </h5>
                                                        </div>

                                                        <div className="mb-2 flex items-center justify-between">
                                                            <span className="text-sm font-medium opacity-80">
                                                                Horas:
                                                            </span>
                                                            <span
                                                                className={`text-base font-bold ${
                                                                    isExcesso
                                                                        ? 'text-error'
                                                                        : isCompleto
                                                                          ? 'text-success'
                                                                          : isParcial
                                                                            ? 'text-warning'
                                                                            : ''
                                                                }`}
                                                            >
                                                                {horasAlocadas}h
                                                                /{' '}
                                                                {horasDefinidas}
                                                                h
                                                            </span>
                                                        </div>

                                                        <div className="mb-2">
                                                            <div className="mb-1 flex items-center justify-between">
                                                                <span className="text-xs font-medium opacity-80">
                                                                    Progresso
                                                                </span>
                                                                <span
                                                                    className={`text-xs font-bold ${
                                                                        isExcesso
                                                                            ? 'text-error'
                                                                            : isCompleto
                                                                              ? 'text-success'
                                                                              : isParcial
                                                                                ? 'text-warning'
                                                                                : 'opacity-75'
                                                                    }`}
                                                                >
                                                                    {percentual}
                                                                    %
                                                                </span>
                                                            </div>
                                                            <progress
                                                                className={`progress w-full ${
                                                                    isExcesso
                                                                        ? 'progress-error'
                                                                        : isCompleto
                                                                          ? 'progress-success'
                                                                          : isParcial
                                                                            ? 'progress-warning'
                                                                            : 'progress-neutral'
                                                                }`}
                                                                value={Math.min(
                                                                    percentual,
                                                                    100,
                                                                )}
                                                                max="100"
                                                            ></progress>
                                                        </div>

                                                        {isExcesso && (
                                                            <div className="mt-2 text-xs">
                                                                <span className="text-error bg-error/10 rounded px-2 py-1 font-bold">
                                                                    Excesso: +
                                                                    {horasAlocadas -
                                                                        horasDefinidas}
                                                                    h
                                                                </span>
                                                            </div>
                                                        )}

                                                        {horasAlocadas ===
                                                            0 && (
                                                            <div className="mt-2 text-xs opacity-60">
                                                                Nenhuma hora
                                                                alocada
                                                            </div>
                                                        )}
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    )}

                                    <div className="bg-info/10 border-info/20 mt-4 rounded-lg border p-3 text-xs">
                                        <p className="text-info-content">
                                            💡 <strong>Dica:</strong> As horas
                                            são contabilizadas apenas para tipos
                                            "Trabalho Presencial" e "Trabalho
                                            Remoto" vinculados a projetos.
                                        </p>
                                    </div>
                                </div>
                            </div>

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
                                                                    className={`border-base-300 border p-1 ${getStatusColorClass(data.horarios[dia.id]?.[slot]?.tipo)}`}
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
                                                                            ]
                                                                                ?.tipo ||
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

                                <div className="mt-8 flex justify-between">
                                    <a
                                        href={route('horarios.index')}
                                        className="btn btn-outline"
                                    >
                                        Cancelar
                                    </a>
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

                            {/* Modal para seleção de sala/baia/projeto */}
                            <HorarioTrabalhoModal
                                isOpen={modalState.isOpen}
                                onClose={handleModalClose}
                                onConfirm={handleModalConfirm}
                                tipoTrabalho={
                                    modalState.tipoTrabalho ||
                                    'TRABALHO_PRESENCIAL'
                                }
                                initialSalaId={
                                    modalState.tipoTrabalho ===
                                    'TRABALHO_PRESENCIAL'
                                        ? preferences.trabalhoPresencial?.salaId
                                        : undefined
                                }
                                initialBaiaId={
                                    modalState.tipoTrabalho ===
                                    'TRABALHO_PRESENCIAL'
                                        ? preferences.trabalhoPresencial?.baiaId
                                        : undefined
                                }
                                initialProjetoId={getUltimoProjetoSelecionado()}
                                salasDisponiveis={salasDisponiveis}
                                projetosAtivos={projetosAtivos}
                                isLoading={isLoadingModal}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
