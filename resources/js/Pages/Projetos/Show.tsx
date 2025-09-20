import DailyReportTab from '@/Components/DailyReportTab';
import HorarioModal from '@/Components/HorarioModal';
import Pagination, { Paginated } from '@/Components/Paggination'; // Updated import
import { TIME_SLOTS_HORARIO } from '@/constants';
import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Baia,
    Coordenador,
    DailyReport,
    DiaDaSemana,
    Funcao,
    Horario,
    PageProps,
    Projeto,
    StatusVinculoProjeto,
    TipoVinculo,
    User,
    UsuarioProjeto,
} from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import React, { useState } from 'react';

const DIAS_SEMANA_HORARIO = [
    { id: 'SEGUNDA', nome: 'Segunda' },
    { id: 'TERCA', nome: 'Terça' },
    { id: 'QUARTA', nome: 'Quarta' },
    { id: 'QUINTA', nome: 'Quinta' },
    { id: 'SEXTA', nome: 'Sexta' },
    { id: 'SABADO', nome: 'Sábado' },
] as const;

type ParticipanteProjeto = Pick<User, 'id' | 'name' | 'email' | 'foto_url'> & {
    funcao: Funcao;
    tipo_vinculo: TipoVinculo;
    data_inicio: string;
    data_fim?: string | null;
    carga_horaria: number;
    valor_bolsa?: number;
};

interface ShowPageProps extends PageProps {
    projeto: Projeto;
    tiposVinculo: TipoVinculo[];
    funcoes: Funcao[];
    usuarioVinculo: UsuarioProjeto | null;
    vinculosDoUsuarioLogadoNoProjeto: UsuarioProjeto[];
    participantesProjeto?: Paginated<ParticipanteProjeto>;
    temVinculosPendentes: boolean;
    jaTemTrocaEmAndamento: boolean;
    coordenadoresDoProjeto: Coordenador[];
    horariosDosProjetos?: Record<DiaDaSemana, Array<Horario>>;
}

type VinculoCreateForm = {
    projeto_id: string;
    tipo_vinculo: TipoVinculo | '';
    funcao: Funcao | '';
    carga_horaria: number;
    data_inicio: string;
    trocar?: boolean;
    usuario_projeto_trocado_id?: string | null;
};

export default function Show({
    auth,
    projeto,
    funcoes,
    usuarioVinculo,
    vinculosDoUsuarioLogadoNoProjeto,
    participantesProjeto,
    temVinculosPendentes,
    coordenadoresDoProjeto,
    horariosDosProjetos,
    diaDaily: initialDiaDaily,
    dailyReports: initialDailyReports,
    totalParticipantes: initialTotalParticipantes,
}: ShowPageProps & {
    diaDaily?: string;
    dailyReports?: DailyReport[];
    totalParticipantes?: number;
}) {
    const { toast } = useToast();
    const [activeTab, setActiveTab] = useState<
        'colaboradores' | 'horarios' | 'dailys'
    >('colaboradores');

    const { url, props } = usePage();
    const [diaDaily, setDiaDaily] = useState<string>(
        initialDiaDaily || new Date().toISOString().slice(0, 10),
    );
    const [loadingDaily, setLoadingDaily] = useState(false);
    const [dailyReports, setDailyReports] = useState<DailyReport[]>(
        Array.isArray(initialDailyReports) ? initialDailyReports : [],
    );
    const [totalParticipantes, setTotalParticipantes] = useState<number>(
        typeof initialTotalParticipantes === 'number'
            ? initialTotalParticipantes
            : 0,
    );

    const handleChangeDiaDaily = (dia: string) => {
        setDiaDaily(dia);
        setLoadingDaily(true);
        const queryParams =
            props.queryparams && typeof props.queryparams === 'object'
                ? props.queryparams
                : {};
        router.get(
            url.split('?')[0],
            { ...queryParams, dia },
            {
                preserveState: true,
                preserveScroll: true,
                only: ['dailyReports', 'diaDaily', 'totalParticipantes'],
                onSuccess: (page) => {
                    setDailyReports(
                        Array.isArray(page.props.dailyReports)
                            ? page.props.dailyReports
                            : [],
                    );
                    setTotalParticipantes(
                        typeof page.props.totalParticipantes === 'number'
                            ? page.props.totalParticipantes
                            : 0,
                    );
                    setLoadingDaily(false);
                },
                onError: () => setLoadingDaily(false),
            },
        );
    };

    const [modalState, setModalState] = useState<{
        isOpen: boolean;
        dia: string;
        horario: string;
        usuarios: Array<{
            id: string;
            name: string;
            email: string;
            foto_url?: string | null;
            baia?: Baia | null;
        }>;
    }>({
        isOpen: false,
        dia: '',
        horario: '',
        usuarios: [],
    });

    // Função para contar quantas pessoas estão em cada slot de horário
    const countUsuariosInSlot = (dia: DiaDaSemana, slot: number): number => {
        if (!horariosDosProjetos || !horariosDosProjetos[dia]) return 0;
        return horariosDosProjetos[dia].filter(
            (h) =>
                h.horario === slot &&
                (h.tipo === 'TRABALHO_PRESENCIAL' ||
                    h.tipo === 'TRABALHO_REMOTO'),
        ).length;
    };

    // Função para abrir o modal com os usuários de um horário específico
    const openHorarioModal = (dia: DiaDaSemana, slot: number) => {
        if (!horariosDosProjetos || !horariosDosProjetos[dia]) return;

        const usuariosNoHorario = horariosDosProjetos[dia]
            .filter(
                (h) =>
                    h.horario === slot &&
                    (h.tipo === 'TRABALHO_PRESENCIAL' ||
                        h.tipo === 'TRABALHO_REMOTO'),
            )
            .map((h) => ({
                id: h.usuario?.id || h.usuario_id,
                name: h.usuario?.name || 'Nome não disponível',
                email: h.usuario?.email || 'Email não disponível',
                foto_url: h.usuario?.foto_url,
                baia: h.baia,
            }));

        const diaFormatted =
            DIAS_SEMANA_HORARIO.find((d) => d.id === dia)?.nome || dia;
        const slotFormatted = `${slot.toString().padStart(2, '0')}:00 - ${(slot + 1).toString().padStart(2, '0')}:00`;

        setModalState({
            isOpen: true,
            dia: diaFormatted,
            horario: slotFormatted,
            usuarios: usuariosNoHorario,
        });
    };

    const closeModal = () => {
        setModalState({
            isOpen: false,
            dia: '',
            horario: '',
            usuarios: [],
        });
    };

    const form = useForm<VinculoCreateForm>({
        projeto_id: projeto.id,
        tipo_vinculo: 'COLABORADOR' as TipoVinculo,
        funcao: '',
        carga_horaria: 80,
        data_inicio: '',
    });

    // TODO: migrar para o back end
    const isCoordenadorDoProjetoAtual =
        usuarioVinculo?.tipo_vinculo === ('COORDENADOR' as TipoVinculo) &&
        usuarioVinculo?.status === ('APROVADO' as StatusVinculoProjeto);

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        form.post(route('vinculo.create', projeto.id), {
            onSuccess: () => {
                toast('Solicitação de vínculo enviada com sucesso!', 'success');
            },
            onError: (e) => {
                toast('Erro ao solicitar vínculo!', 'error');
                console.error(e);
            },
        });
    };

    const dailyReportTabOnClick = () => {
        handleChangeDiaDaily(format(new Date(), 'yyyy-MM-dd'));
        setActiveTab('dailys');
    };

    const renderVinculoStatus = () => {
        if (usuarioVinculo) {
            let statusClass = 'badge-neutral';
            let statusText = '';

            switch (usuarioVinculo.status) {
                case 'APROVADO':
                    statusClass = 'badge-success';
                    statusText = 'Aprovado';
                    break;
                case 'PENDENTE':
                    statusClass = 'badge-warning';
                    statusText = 'Pendente';
                    break;
                case 'RECUSADO':
                    statusClass = 'badge-error';
                    statusText = 'Recusado';
                    break;
                default:
                    statusText = usuarioVinculo.status;
            }

            return (
                <div className="card bg-base-200 mt-6 shadow">
                    <div className="card-body">
                        <h3 className="text-base-content mb-4 text-lg font-medium">
                            Seu Vínculo Atual com Este Projeto
                        </h3>
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-base-content/70 text-sm font-medium">
                                    Status do vínculo:
                                </span>
                                <span
                                    className={`badge ${statusClass} badge-md`}
                                >
                                    {statusText}
                                </span>
                            </div>

                            {(usuarioVinculo.funcao ||
                                usuarioVinculo.tipo_vinculo) && (
                                <div className="divider my-1"></div>
                            )}

                            {usuarioVinculo.funcao && (
                                <div className="flex items-center justify-between">
                                    <span className="text-base-content/70 text-sm font-medium">
                                        Função:
                                    </span>
                                    <span className="badge badge-outline badge-md">
                                        {usuarioVinculo.funcao}
                                    </span>
                                </div>
                            )}
                            {usuarioVinculo.tipo_vinculo && (
                                <div className="flex items-center justify-between">
                                    <span className="text-base-content/70 text-sm font-medium">
                                        Tipo de Vínculo:
                                    </span>
                                    <span className="badge badge-primary badge-md">
                                        {usuarioVinculo.tipo_vinculo}
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            );
        }
        return null;
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Projeto: ${projeto.nome}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {/* Card for Project Details */}
                    <div className="bg-base-100 dark:bg-base-300 p-4 shadow sm:rounded-lg sm:p-8">
                        <div>
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {projeto.nome}
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Cliente: {projeto.cliente}
                                    </p>
                                </div>
                                {isCoordenadorDoProjetoAtual && (
                                    <Link
                                        href={route(
                                            'projetos.edit',
                                            projeto.id,
                                        )}
                                        className="btn btn-outline btn-sm"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            strokeWidth={1.5}
                                            stroke="currentColor"
                                            className="h-4 w-4"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13L2 21l.35-2.935a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"
                                            />
                                        </svg>
                                        Editar
                                    </Link>
                                )}
                            </div>

                            {auth.isCoordenador && temVinculosPendentes && (
                                <Link
                                    href={route('colaboradores.index', {
                                        status: 'vinculo_pendente',
                                        project_id: projeto.id,
                                    })}
                                >
                                    <div
                                        role="alert"
                                        className="alert alert-warning mt-4 cursor-pointer hover:shadow-lg"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            className="stroke-info h-6 w-6 shrink-0"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        <span>
                                            Há solicitações de vínculos
                                            pendentes! Clique para ver.
                                        </span>
                                    </div>
                                </Link>
                            )}

                            <div className="mt-4 space-y-2">
                                <p>
                                    <span className="font-semibold">
                                        Descrição:
                                    </span>{' '}
                                    {projeto.descricao || 'Não informada'}
                                </p>
                                <p>
                                    <span className="font-semibold">
                                        Data de Início:
                                    </span>{' '}
                                    {format(
                                        new Date(projeto.data_inicio),
                                        'dd/MM/yyyy',
                                        { locale: ptBR },
                                    )}
                                </p>
                                <p>
                                    <span className="font-semibold">
                                        Data de Término:
                                    </span>{' '}
                                    {projeto.data_termino
                                        ? format(
                                              new Date(projeto.data_termino),
                                              'dd/MM/yyyy',
                                              { locale: ptBR },
                                          )
                                        : 'Não definida'}
                                </p>
                                <p>
                                    <span className="font-semibold">Tipo:</span>{' '}
                                    {projeto.tipo}
                                </p>
                                {projeto.interveniente_financeiro && (
                                    <p>
                                        <span className="font-semibold">
                                            Interveniente Financeiro:
                                        </span>{' '}
                                        {projeto.interveniente_financeiro?.nome}
                                    </p>
                                )}
                                {projeto.numero_convenio && (
                                    <p>
                                        <span className="font-semibold">
                                            Número do Acordo/Contrato/Convênio:
                                        </span>{' '}
                                        {projeto.numero_convenio}
                                    </p>
                                )}
                                {projeto.valor_total &&
                                projeto.valor_total > 0 ? (
                                    <div className="flex items-center justify-between">
                                        <span className="font-semibold">
                                            Valor Total:
                                        </span>
                                        <span className="badge badge-success badge-lg">
                                            {(
                                                projeto.valor_total / 100
                                            ).toLocaleString('pt-BR', {
                                                style: 'currency',
                                                currency: 'BRL',
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
                                            })}
                                        </span>
                                    </div>
                                ) : null}
                                {projeto.meses_execucao &&
                                projeto.meses_execucao > 0 ? (
                                    <div className="flex items-center justify-between">
                                        <span className="font-semibold">
                                            Duração Execução (meses):
                                        </span>
                                        <span className="badge badge-info badge-lg">
                                            {projeto.meses_execucao}{' '}
                                            {projeto.meses_execucao === 1
                                                ? 'mês'
                                                : 'meses'}
                                        </span>
                                    </div>
                                ) : null}
                                {projeto.campos_extras &&
                                    Object.keys(projeto.campos_extras).length >
                                        0 && (
                                        <div className="mt-4">
                                            <h4 className="text-base-content mb-3 font-semibold">
                                                Campos Extras:
                                            </h4>
                                            <div className="bg-base-200 space-y-3 rounded-lg p-4">
                                                {Object.entries(
                                                    projeto.campos_extras,
                                                ).map(([key, value]) => (
                                                    <div
                                                        key={key}
                                                        className="bg-base-100 flex items-start justify-between gap-4 rounded-md p-3 shadow-sm"
                                                    >
                                                        <span className="text-base-content/80 min-w-0 flex-1 font-medium capitalize">
                                                            {key.replace(
                                                                /_/g,
                                                                ' ',
                                                            )}
                                                            :
                                                        </span>
                                                        <span className="text-base-content max-w-xs text-right break-words">
                                                            {value &&
                                                            typeof value ===
                                                                'object'
                                                                ? JSON.stringify(
                                                                      value,
                                                                  )
                                                                : String(
                                                                      value ||
                                                                          '',
                                                                  )}
                                                        </span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                {renderVinculoStatus()}
                            </div>
                        </div>
                    </div>

                    {!usuarioVinculo && (
                        <>
                            <div className="card bg-base-100 mb-6 shadow-xl">
                                <div className="card-body">
                                    <div className="mb-4 flex items-center gap-3">
                                        <div className="bg-primary/10 rounded-lg p-2">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                strokeWidth={1.5}
                                                stroke="currentColor"
                                                className="text-primary h-5 w-5"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"
                                                />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 className="card-title text-xl">
                                                Coordenadores do Projeto
                                            </h3>
                                            <p className="text-base-content/70 text-sm">
                                                Entre em contato com os
                                                coordenadores para
                                                esclarecimentos
                                            </p>
                                        </div>
                                    </div>
                                    {coordenadoresDoProjeto &&
                                    coordenadoresDoProjeto.length > 0 ? (
                                        <div className="grid gap-3">
                                            {coordenadoresDoProjeto.map(
                                                (coordenador: Coordenador) => (
                                                    <div
                                                        key={coordenador.id}
                                                        className="bg-base-200/50 hover:bg-base-200 flex items-center gap-3 rounded-lg p-3 transition-colors"
                                                    >
                                                        <div className="avatar">
                                                            <div className="h-10 w-10 rounded-full">
                                                                {coordenador.foto_url ? (
                                                                    <img
                                                                        src={
                                                                            coordenador.foto_url
                                                                        }
                                                                        alt={`Foto de ${coordenador.name}`}
                                                                        className="h-full w-full rounded-full object-cover"
                                                                    />
                                                                ) : (
                                                                    <div className="bg-neutral text-neutral-content flex h-full w-full items-center justify-center rounded-full">
                                                                        <span className="text-xs font-medium">
                                                                            {coordenador.name
                                                                                .charAt(
                                                                                    0,
                                                                                )
                                                                                .toUpperCase()}
                                                                        </span>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                        <div className="flex-1">
                                                            <div className="text-base-content font-medium">
                                                                {
                                                                    coordenador.name
                                                                }
                                                            </div>
                                                            <div className="text-base-content/70 text-sm">
                                                                Coordenador
                                                            </div>
                                                        </div>
                                                        <div className="badge badge-primary badge-sm">
                                                            Ativo
                                                        </div>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    ) : (
                                        <div className="py-8 text-center">
                                            <div className="bg-warning/10 mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    strokeWidth={1.5}
                                                    stroke="currentColor"
                                                    className="text-warning h-8 w-8"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12V15.75z"
                                                    />
                                                </svg>
                                            </div>
                                            <p className="text-base-content/70">
                                                Nenhum coordenador encontrado
                                                para este projeto.
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="card bg-base-100 shadow-xl">
                                <div className="card-body">
                                    <div className="mb-6 flex items-start gap-4">
                                        <div className="flex-1">
                                            <h3 className="card-title mb-2 text-xl">
                                                Solicitar Vínculo ao Projeto
                                            </h3>
                                            <p className="text-base-content/70">
                                                Preencha os dados abaixo para
                                                solicitar sua participação neste
                                                projeto. Sua solicitação será
                                                analisada pelos coordenadores.
                                            </p>
                                        </div>
                                    </div>

                                    <form
                                        onSubmit={submit}
                                        className="space-y-8"
                                    >
                                        <div className="mx-auto w-full max-w-5xl">
                                            {auth.isVinculoProjetoPendente ? (
                                                <div
                                                    role="alert"
                                                    className="alert alert-info"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        className="h-6 w-6 shrink-0 stroke-current"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                        ></path>
                                                    </svg>
                                                    <span>
                                                        Você já possui um
                                                        vinculo pendente, entre
                                                        em contato com um
                                                        Coordenador para avaliar
                                                        seu vinculo.
                                                    </span>
                                                </div>
                                            ) : (
                                                <div className="space-y-6">
                                                    {/* Seção de Troca de Projeto - Aparece apenas se o usuário tem vínculos ativos */}
                                                    {vinculosDoUsuarioLogadoNoProjeto &&
                                                        vinculosDoUsuarioLogadoNoProjeto.length >
                                                            0 && (
                                                            <div className="alert alert-warning">
                                                                <div className="w-full">
                                                                    <div className="mb-4 flex items-start gap-3">
                                                                        <svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            fill="none"
                                                                            viewBox="0 0 24 24"
                                                                            className="h-6 w-6 shrink-0 stroke-current"
                                                                        >
                                                                            <path
                                                                                strokeLinecap="round"
                                                                                strokeLinejoin="round"
                                                                                strokeWidth="2"
                                                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                                            />
                                                                        </svg>
                                                                        <div className="flex-1">
                                                                            <h3 className="mb-1 font-semibold">
                                                                                Você
                                                                                já
                                                                                possui
                                                                                vínculos
                                                                                ativos
                                                                            </h3>
                                                                            <p className="text-sm opacity-80">
                                                                                Para
                                                                                participar
                                                                                deste
                                                                                projeto,
                                                                                você
                                                                                pode
                                                                                optar
                                                                                por
                                                                                trocar
                                                                                um
                                                                                dos
                                                                                seus
                                                                                vínculos
                                                                                atuais
                                                                                ou
                                                                                solicitar
                                                                                participação
                                                                                em
                                                                                paralelo.
                                                                            </p>
                                                                        </div>
                                                                    </div>

                                                                    {/* Toggle para troca de projeto */}
                                                                    <div className="form-control">
                                                                        <label className="cursor-pointer">
                                                                            <div className="bg-base-100 border-warning/30 hover:border-warning/50 flex items-center gap-4 rounded-lg border p-4 transition-colors">
                                                                                <input
                                                                                    type="checkbox"
                                                                                    className="toggle toggle-warning"
                                                                                    checked={
                                                                                        form
                                                                                            .data
                                                                                            .trocar ||
                                                                                        false
                                                                                    }
                                                                                    onChange={(
                                                                                        e,
                                                                                    ) => {
                                                                                        form.setData(
                                                                                            'trocar',
                                                                                            e
                                                                                                .target
                                                                                                .checked,
                                                                                        );
                                                                                        if (
                                                                                            !e
                                                                                                .target
                                                                                                .checked
                                                                                        ) {
                                                                                            form.setData(
                                                                                                'usuario_projeto_trocado_id',
                                                                                                null,
                                                                                            );
                                                                                        }
                                                                                    }}
                                                                                />
                                                                                <div className="flex-1">
                                                                                    <div className="text-base-content font-medium">
                                                                                        Trocar
                                                                                        de
                                                                                        projeto
                                                                                    </div>
                                                                                    <div className="text-base-content/70 text-sm">
                                                                                        Encerrar
                                                                                        participação
                                                                                        em
                                                                                        um
                                                                                        projeto
                                                                                        atual
                                                                                        para
                                                                                        ingressar
                                                                                        neste
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </label>
                                                                        {form
                                                                            .errors
                                                                            .trocar && (
                                                                            <div className="label">
                                                                                <span className="label-text-alt text-error">
                                                                                    {
                                                                                        form
                                                                                            .errors
                                                                                            .trocar
                                                                                    }
                                                                                </span>
                                                                            </div>
                                                                        )}
                                                                    </div>

                                                                    {/* Seleção do projeto a ser trocado */}
                                                                    {form.data
                                                                        .trocar && (
                                                                        <div className="form-control bg-base-200 border-warning/20 mt-4 rounded-lg border p-4">
                                                                            <label className="label">
                                                                                <span className="label-text text-base-content font-medium">
                                                                                    Selecione
                                                                                    o
                                                                                    vínculo
                                                                                    a
                                                                                    ser
                                                                                    encerrado
                                                                                    <span className="text-error ml-1">
                                                                                        *
                                                                                    </span>
                                                                                </span>
                                                                            </label>
                                                                            <select
                                                                                className={`select select-bordered bg-base-100 text-base-content w-full ${
                                                                                    form
                                                                                        .errors
                                                                                        .usuario_projeto_trocado_id
                                                                                        ? 'select-error'
                                                                                        : 'border-base-300 focus:border-warning'
                                                                                }`}
                                                                                value={
                                                                                    form
                                                                                        .data
                                                                                        .usuario_projeto_trocado_id ||
                                                                                    ''
                                                                                }
                                                                                onChange={(
                                                                                    e,
                                                                                ) =>
                                                                                    form.setData(
                                                                                        'usuario_projeto_trocado_id',
                                                                                        e
                                                                                            .target
                                                                                            .value ||
                                                                                            null,
                                                                                    )
                                                                                }
                                                                            >
                                                                                <option
                                                                                    value=""
                                                                                    disabled
                                                                                >
                                                                                    Escolha
                                                                                    qual
                                                                                    vínculo
                                                                                    será
                                                                                    encerrado
                                                                                </option>
                                                                                {vinculosDoUsuarioLogadoNoProjeto
                                                                                    .filter(
                                                                                        (
                                                                                            vinculo,
                                                                                        ) =>
                                                                                            vinculo.status ===
                                                                                                'APROVADO' &&
                                                                                            !vinculo.data_fim,
                                                                                    )
                                                                                    .map(
                                                                                        (
                                                                                            vinculo,
                                                                                        ) => (
                                                                                            <option
                                                                                                key={
                                                                                                    vinculo.id
                                                                                                }
                                                                                                value={
                                                                                                    vinculo.id
                                                                                                }
                                                                                            >
                                                                                                {
                                                                                                    vinculo
                                                                                                        .projeto
                                                                                                        ?.nome
                                                                                                }{' '}
                                                                                                -{' '}
                                                                                                {
                                                                                                    vinculo.funcao
                                                                                                }
                                                                                            </option>
                                                                                        ),
                                                                                    )}
                                                                            </select>
                                                                            {form
                                                                                .errors
                                                                                .usuario_projeto_trocado_id && (
                                                                                <div className="label">
                                                                                    <span className="label-text-alt text-error">
                                                                                        {
                                                                                            form
                                                                                                .errors
                                                                                                .usuario_projeto_trocado_id
                                                                                        }
                                                                                    </span>
                                                                                </div>
                                                                            )}
                                                                            <div className="label">
                                                                                <span className="label-text-alt text-base-content/70">
                                                                                    Este
                                                                                    vínculo
                                                                                    será
                                                                                    encerrado
                                                                                    automaticamente
                                                                                    quando
                                                                                    sua
                                                                                    nova
                                                                                    solicitação
                                                                                    for
                                                                                    aprovada
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        )}

                                                    {/* Seção Principal do Formulário */}
                                                    <div className="card card-bordered bg-base-100">
                                                        <div className="card-body">
                                                            <div className="mb-6 flex items-center gap-3">
                                                                <div className="badge badge-primary badge-lg">
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none"
                                                                        viewBox="0 0 24 24"
                                                                        strokeWidth={
                                                                            2
                                                                        }
                                                                        stroke="currentColor"
                                                                        className="h-4 w-4"
                                                                    >
                                                                        <path
                                                                            strokeLinecap="round"
                                                                            strokeLinejoin="round"
                                                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                                                        />
                                                                    </svg>
                                                                </div>
                                                                <h3 className="text-base-content text-xl font-semibold">
                                                                    Dados da
                                                                    Solicitação
                                                                </h3>
                                                            </div>

                                                            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3 xl:grid-cols-3">
                                                                {/* Função */}
                                                                <div className="form-control">
                                                                    <label className="label">
                                                                        <span className="label-text font-medium">
                                                                            Função
                                                                            no
                                                                            Projeto
                                                                            <span className="text-error ml-1">
                                                                                *
                                                                            </span>
                                                                        </span>
                                                                    </label>
                                                                    <select
                                                                        className={`select select-bordered w-full ${
                                                                            form
                                                                                .errors
                                                                                .funcao
                                                                                ? 'select-error'
                                                                                : ''
                                                                        }`}
                                                                        value={
                                                                            form
                                                                                .data
                                                                                .funcao
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            form.setData(
                                                                                'funcao',
                                                                                e
                                                                                    .target
                                                                                    .value as Funcao,
                                                                            )
                                                                        }
                                                                    >
                                                                        <option
                                                                            value=""
                                                                            disabled
                                                                        >
                                                                            Selecione
                                                                            sua
                                                                            função
                                                                        </option>
                                                                        {funcoes.map(
                                                                            (
                                                                                funcao,
                                                                            ) => (
                                                                                <option
                                                                                    key={
                                                                                        funcao
                                                                                    }
                                                                                    value={
                                                                                        funcao
                                                                                    }
                                                                                >
                                                                                    {
                                                                                        funcao
                                                                                    }
                                                                                </option>
                                                                            ),
                                                                        )}
                                                                    </select>
                                                                    {form.errors
                                                                        .funcao && (
                                                                        <div className="label">
                                                                            <span className="label-text-alt text-error">
                                                                                {
                                                                                    form
                                                                                        .errors
                                                                                        .funcao
                                                                                }
                                                                            </span>
                                                                        </div>
                                                                    )}
                                                                </div>

                                                                {/* Carga Horária */}
                                                                <div className="form-control">
                                                                    <label className="label">
                                                                        <span className="label-text font-medium">
                                                                            Carga
                                                                            Horária
                                                                            Mensal
                                                                            <span className="text-error ml-1">
                                                                                *
                                                                            </span>
                                                                        </span>
                                                                        <span className="label-text-alt">
                                                                            horas/mês
                                                                        </span>
                                                                    </label>
                                                                    <div className="relative">
                                                                        <input
                                                                            type="number"
                                                                            className={`input input-bordered w-full pr-16 ${
                                                                                form
                                                                                    .errors
                                                                                    .carga_horaria
                                                                                    ? 'input-error'
                                                                                    : ''
                                                                            }`}
                                                                            placeholder="Ex: 80"
                                                                            value={
                                                                                form
                                                                                    .data
                                                                                    .carga_horaria
                                                                            }
                                                                            onChange={(
                                                                                e: React.ChangeEvent<HTMLInputElement>,
                                                                            ) =>
                                                                                form.setData(
                                                                                    'carga_horaria',
                                                                                    parseInt(
                                                                                        e
                                                                                            .target
                                                                                            .value,
                                                                                    ) ||
                                                                                        0,
                                                                                )
                                                                            }
                                                                            min="1"
                                                                            max="192"
                                                                        />
                                                                        <div className="absolute inset-y-0 right-0 flex items-center pr-3">
                                                                            <span className="text-base-content/60 text-xs font-medium">
                                                                                hrs
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div className="label">
                                                                        <span className="label-text-alt text-base-content/70">
                                                                            ≈{' '}
                                                                            {(
                                                                                form
                                                                                    .data
                                                                                    .carga_horaria /
                                                                                4
                                                                            ).toFixed(
                                                                                1,
                                                                            )}{' '}
                                                                            horas/semana
                                                                        </span>
                                                                    </div>
                                                                    {form.errors
                                                                        .carga_horaria && (
                                                                        <div className="label">
                                                                            <span className="label-text-alt text-error">
                                                                                {
                                                                                    form
                                                                                        .errors
                                                                                        .carga_horaria
                                                                                }
                                                                            </span>
                                                                        </div>
                                                                    )}
                                                                </div>

                                                                {/* Data de Início */}
                                                                <div className="form-control">
                                                                    <label className="label">
                                                                        <span className="label-text font-medium">
                                                                            Data
                                                                            de
                                                                            Início
                                                                            <span className="text-error ml-1">
                                                                                *
                                                                            </span>
                                                                        </span>
                                                                    </label>
                                                                    <input
                                                                        type="date"
                                                                        className={`input input-bordered w-full ${
                                                                            form
                                                                                .errors
                                                                                .data_inicio
                                                                                ? 'input-error'
                                                                                : ''
                                                                        }`}
                                                                        value={
                                                                            form
                                                                                .data
                                                                                .data_inicio
                                                                        }
                                                                        min={
                                                                            new Date(
                                                                                projeto.data_inicio,
                                                                            )
                                                                                .toISOString()
                                                                                .split(
                                                                                    'T',
                                                                                )[0]
                                                                        }
                                                                        max={(() => {
                                                                            if (
                                                                                !projeto.data_termino
                                                                            )
                                                                                return undefined;
                                                                            const dataAnterior =
                                                                                new Date(
                                                                                    projeto.data_termino,
                                                                                );
                                                                            dataAnterior.setDate(
                                                                                dataAnterior.getDate() -
                                                                                    1,
                                                                            );
                                                                            return dataAnterior
                                                                                .toISOString()
                                                                                .split(
                                                                                    'T',
                                                                                )[0];
                                                                        })()}
                                                                        onChange={(
                                                                            e: React.ChangeEvent<HTMLInputElement>,
                                                                        ) =>
                                                                            form.setData(
                                                                                'data_inicio',
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                    />
                                                                    {form.errors
                                                                        .data_inicio && (
                                                                        <div className="label">
                                                                            <span className="label-text-alt text-error">
                                                                                {
                                                                                    form
                                                                                        .errors
                                                                                        .data_inicio
                                                                                }
                                                                            </span>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {/* Seção dos Botões - dentro do mesmo card */}
                                                        <div className="card-body border-base-300 border-t pt-6">
                                                            <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                                                                <div className="text-base-content/70 flex items-center gap-2 text-sm">
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none"
                                                                        viewBox="0 0 24 24"
                                                                        strokeWidth={
                                                                            1.5
                                                                        }
                                                                        stroke="currentColor"
                                                                        className="h-4 w-4"
                                                                    >
                                                                        <path
                                                                            strokeLinecap="round"
                                                                            strokeLinejoin="round"
                                                                            d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"
                                                                        />
                                                                    </svg>
                                                                    Sua
                                                                    solicitação
                                                                    será
                                                                    analisada
                                                                    pelos
                                                                    coordenadores
                                                                    do projeto
                                                                </div>
                                                                <div className="flex gap-3">
                                                                    <button
                                                                        type="button"
                                                                        className="btn btn-ghost"
                                                                        onClick={() =>
                                                                            window.history.back()
                                                                        }
                                                                    >
                                                                        Cancelar
                                                                    </button>
                                                                    <button
                                                                        type="submit"
                                                                        className="btn btn-primary px-8"
                                                                        disabled={
                                                                            form.processing
                                                                        }
                                                                    >
                                                                        {form.processing ? (
                                                                            <>
                                                                                <span className="loading loading-spinner loading-sm"></span>
                                                                                Enviando...
                                                                            </>
                                                                        ) : (
                                                                            <>
                                                                                <svg
                                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                                    fill="none"
                                                                                    viewBox="0 0 20 20"
                                                                                    strokeWidth={
                                                                                        1.5
                                                                                    }
                                                                                    stroke="currentColor"
                                                                                    className="h-4 w-4"
                                                                                >
                                                                                    <path
                                                                                        strokeLinecap="round"
                                                                                        strokeLinejoin="round"
                                                                                        d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"
                                                                                    />
                                                                                </svg>
                                                                                Solicitar
                                                                                Vínculo
                                                                            </>
                                                                        )}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </>
                    )}

                    {/* Tabs Section */}
                    {(isCoordenadorDoProjetoAtual ||
                        usuarioVinculo?.status === 'APROVADO') && (
                        <div className="card bg-base-100 mt-8 shadow-xl">
                            <div className="card-body">
                                {/* Tabs Navigation */}
                                <div
                                    role="tablist"
                                    className="tabs tabs-bordered mb-6"
                                >
                                    <button
                                        role="tab"
                                        className={`tab ${activeTab === 'colaboradores' ? 'tab-active' : ''}`}
                                        onClick={() =>
                                            setActiveTab('colaboradores')
                                        }
                                    >
                                        Colaboradores
                                    </button>
                                    <button
                                        role="tab"
                                        className={`tab ${activeTab === 'horarios' ? 'tab-active' : ''}`}
                                        onClick={() => setActiveTab('horarios')}
                                    >
                                        Horários do Projeto
                                    </button>
                                    <button
                                        role="tab"
                                        className={`tab ${activeTab === 'dailys' ? 'tab-active' : ''}`}
                                        onClick={dailyReportTabOnClick}
                                    >
                                        Daily Reports
                                    </button>
                                </div>

                                {/* Tab Content */}
                                {activeTab === 'colaboradores' &&
                                    participantesProjeto &&
                                    participantesProjeto.data.length > 0 && (
                                        <div>
                                            <h3 className="card-title mb-4 text-xl">
                                                Participantes do Projeto
                                            </h3>
                                            <div className="overflow-x-auto">
                                                <table className="table-zebra table w-full">
                                                    <thead>
                                                        <tr>
                                                            <th>Nome</th>
                                                            <th>Email</th>
                                                            <th>Função</th>
                                                            {isCoordenadorDoProjetoAtual && (
                                                                <>
                                                                    <th>
                                                                        Carga
                                                                        Horária
                                                                        <br />
                                                                        (horas/mês)
                                                                    </th>
                                                                    <th>
                                                                        Valor da
                                                                        Bolsa
                                                                    </th>
                                                                    <th>
                                                                        Início
                                                                    </th>
                                                                    <th>Fim</th>
                                                                </>
                                                            )}
                                                            {isCoordenadorDoProjetoAtual && (
                                                                <th>Ações</th>
                                                            )}
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {participantesProjeto.data.map(
                                                            (
                                                                participante: ParticipanteProjeto,
                                                            ) => (
                                                                <tr
                                                                    key={
                                                                        participante.id
                                                                    }
                                                                >
                                                                    <td>
                                                                        <div className="flex items-center gap-3">
                                                                            <div className="avatar">
                                                                                <div className="mask mask-squircle h-12 w-12">
                                                                                    <img
                                                                                        src={
                                                                                            participante.foto_url ||
                                                                                            `https://ui-avatars.com/api/?name=${encodeURIComponent(participante.name)}&background=random&color=fff`
                                                                                        }
                                                                                        alt={`Foto de ${participante.name}`}
                                                                                    />
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <div className="font-bold">
                                                                                    {
                                                                                        participante.name
                                                                                    }
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        {
                                                                            participante.email
                                                                        }
                                                                    </td>
                                                                    <td>
                                                                        <span className="badge badge-primary badge-sm">
                                                                            {
                                                                                participante.funcao
                                                                            }
                                                                        </span>
                                                                    </td>
                                                                    {isCoordenadorDoProjetoAtual && (
                                                                        <>
                                                                            <td>
                                                                                {typeof participante.carga_horaria !==
                                                                                'undefined'
                                                                                    ? participante.carga_horaria
                                                                                    : '---'}
                                                                            </td>
                                                                            <td>
                                                                                {typeof participante.valor_bolsa !==
                                                                                'undefined'
                                                                                    ? `R$ ${(participante.valor_bolsa / 100).toFixed(2)}`
                                                                                    : '---'}
                                                                            </td>
                                                                            <td>
                                                                                {participante.data_inicio
                                                                                    ? format(
                                                                                          new Date(
                                                                                              participante.data_inicio,
                                                                                          ),
                                                                                          'dd/MM/yyyy',
                                                                                          {
                                                                                              locale: ptBR,
                                                                                          },
                                                                                      )
                                                                                    : '---'}
                                                                            </td>
                                                                            <td>
                                                                                {participante.data_fim
                                                                                    ? format(
                                                                                          new Date(
                                                                                              participante.data_fim,
                                                                                          ),
                                                                                          'dd/MM/yyyy',
                                                                                          {
                                                                                              locale: ptBR,
                                                                                          },
                                                                                      )
                                                                                    : '---'}
                                                                            </td>
                                                                            <td>
                                                                                <div className="join">
                                                                                    <Link
                                                                                        href={route(
                                                                                            'colaboradores.show',
                                                                                            participante.id,
                                                                                        )}
                                                                                        className="btn btn-ghost btn-xs join-item"
                                                                                        title="Ver Detalhes do Colaborador"
                                                                                    >
                                                                                        <svg
                                                                                            className="h-4 w-4"
                                                                                            fill="none"
                                                                                            stroke="currentColor"
                                                                                            viewBox="0 0 24 24"
                                                                                        >
                                                                                            <path
                                                                                                strokeLinecap="round"
                                                                                                strokeLinejoin="round"
                                                                                                strokeWidth={
                                                                                                    2
                                                                                                }
                                                                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                                                            />
                                                                                        </svg>
                                                                                    </Link>
                                                                                    <Link
                                                                                        href={route(
                                                                                            'horarios.show',
                                                                                            {
                                                                                                colaborador:
                                                                                                    participante.id,
                                                                                                projeto:
                                                                                                    projeto.id,
                                                                                            },
                                                                                        )}
                                                                                        className="btn btn-ghost btn-xs join-item"
                                                                                        title="Ver Horários do Colaborador"
                                                                                    >
                                                                                        <svg
                                                                                            className="h-4 w-4"
                                                                                            fill="none"
                                                                                            stroke="currentColor"
                                                                                            viewBox="0 0 24 24"
                                                                                        >
                                                                                            <path
                                                                                                strokeLinecap="round"
                                                                                                strokeLinejoin="round"
                                                                                                strokeWidth={
                                                                                                    2
                                                                                                }
                                                                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                                                            />
                                                                                        </svg>
                                                                                    </Link>
                                                                                </div>
                                                                            </td>
                                                                        </>
                                                                    )}
                                                                </tr>
                                                            ),
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                            {/* Pagination */}
                                            {participantesProjeto &&
                                                participantesProjeto.data
                                                    .length > 0 && (
                                                    <Pagination
                                                        paginated={
                                                            participantesProjeto
                                                        }
                                                    />
                                                )}
                                        </div>
                                    )}

                                {activeTab === 'horarios' && (
                                    <div>
                                        <h3 className="card-title mb-4 text-xl">
                                            Horários do Projeto
                                        </h3>
                                        {horariosDosProjetos ? (
                                            <div className="overflow-x-auto">
                                                <table className="table w-full text-center">
                                                    <thead>
                                                        <tr className="bg-base-300">
                                                            <th className="border-base-300 w-32 border p-2">
                                                                Horário
                                                            </th>
                                                            {DIAS_SEMANA_HORARIO.map(
                                                                (dia) => (
                                                                    <th
                                                                        key={
                                                                            dia.id
                                                                        }
                                                                        className="border-base-300 border p-2"
                                                                    >
                                                                        {
                                                                            dia.nome
                                                                        }
                                                                    </th>
                                                                ),
                                                            )}
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {TIME_SLOTS_HORARIO.map(
                                                            (slot) => {
                                                                const nextHour =
                                                                    slot + 1;
                                                                const slotFormatted =
                                                                    slot
                                                                        .toString()
                                                                        .padStart(
                                                                            2,
                                                                            '0',
                                                                        ) +
                                                                    ':00';
                                                                const nextHourFormatted =
                                                                    nextHour
                                                                        .toString()
                                                                        .padStart(
                                                                            2,
                                                                            '0',
                                                                        ) +
                                                                    ':00';

                                                                return (
                                                                    <tr
                                                                        key={
                                                                            slot
                                                                        }
                                                                    >
                                                                        <td className="border-base-300 border p-2 font-semibold">
                                                                            {`${slotFormatted} - ${nextHourFormatted}`}
                                                                        </td>
                                                                        {DIAS_SEMANA_HORARIO.map(
                                                                            (
                                                                                dia,
                                                                            ) => {
                                                                                const count =
                                                                                    countUsuariosInSlot(
                                                                                        dia.id as DiaDaSemana,
                                                                                        slot,
                                                                                    );

                                                                                return (
                                                                                    <td
                                                                                        key={`${dia.id}-${slot}`}
                                                                                        className="border-base-300 hover:bg-base-200 cursor-pointer border p-3 text-sm font-medium transition-colors"
                                                                                        onClick={() =>
                                                                                            openHorarioModal(
                                                                                                dia.id as DiaDaSemana,
                                                                                                slot,
                                                                                            )
                                                                                        }
                                                                                    >
                                                                                        {count >
                                                                                        0 ? (
                                                                                            <div className="text-center">
                                                                                                <div className="badge badge-primary badge-lg">
                                                                                                    {
                                                                                                        count
                                                                                                    }{' '}
                                                                                                    {count ===
                                                                                                    1
                                                                                                        ? 'pessoa'
                                                                                                        : 'pessoas'}
                                                                                                </div>
                                                                                                <div className="text-base-content/70 mt-1 text-xs">
                                                                                                    Clique
                                                                                                    para
                                                                                                    ver
                                                                                                    detalhes
                                                                                                </div>
                                                                                            </div>
                                                                                        ) : (
                                                                                            <div className="text-base-content/50 text-center">
                                                                                                Ninguém
                                                                                            </div>
                                                                                        )}
                                                                                    </td>
                                                                                );
                                                                            },
                                                                        )}
                                                                    </tr>
                                                                );
                                                            },
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        ) : (
                                            <div className="py-8 text-center">
                                                <div className="text-base-content/60">
                                                    Nenhum horário cadastrado
                                                    para este projeto
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                )}

                                {activeTab === 'dailys' && (
                                    <DailyReportTab
                                        dia={diaDaily}
                                        onChangeDia={handleChangeDiaDaily}
                                        loading={loadingDaily}
                                        dailyReports={dailyReports}
                                        totalParticipantes={totalParticipantes}
                                    />
                                )}

                                {activeTab === 'colaboradores' &&
                                    (!participantesProjeto ||
                                        participantesProjeto.data.length ===
                                            0) && (
                                        <div className="py-8 text-center">
                                            <div className="text-base-content/60">
                                                Nenhum participante encontrado
                                                para este projeto
                                            </div>
                                        </div>
                                    )}
                            </div>
                        </div>
                    )}

                    <HorarioModal
                        isOpen={modalState.isOpen}
                        onClose={closeModal}
                        dia={modalState.dia}
                        horario={modalState.horario}
                        usuarios={modalState.usuarios}
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
