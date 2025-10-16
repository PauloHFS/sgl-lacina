import RichTextEditor from '@/Components/RichTextEditor';
import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Ausencia, PageProps, Projeto } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { eachDayOfInterval, format, getDay, isValid, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import {
    FormEventHandler,
    useCallback,
    useEffect,
    useMemo,
    useState,
} from 'react';

// Tipos e Constantes
type DiaDaSemana =
    | 'DOMINGO'
    | 'SEGUNDA'
    | 'TERCA'
    | 'QUARTA'
    | 'QUINTA'
    | 'SEXTA'
    | 'SABADO';

interface EditPageProps extends PageProps {
    ausencia: Ausencia;
    projetosAtivos: Projeto[];
    horasPorProjetoPorDia: Record<
        string,
        Record<DiaDaSemana, number | undefined>
    >;
}

interface AusenciaForm {
    titulo: string;
    projeto_id: string;
    data_inicio: string;
    data_fim: string;
    justificativa: string;
    horas_a_compensar: number;
    compensacao_data_inicio?: string;
    compensacao_data_fim?: string;
    compensacao_horarios?: string;
}

interface CompensacaoDia {
    data: string; // yyyy-MM-dd
    horario: number[];
}

const DIAS_DA_SEMANA_MAP: DiaDaSemana[] = [
    'DOMINGO',
    'SEGUNDA',
    'TERCA',
    'QUARTA',
    'QUINTA',
    'SEXTA',
    'SABADO',
];

const HORAS_DO_DIA = Array.from({ length: 15 }, (_, i) => i + 7); // 7h às 21h

const safeJsonParse = <T,>(jsonString: unknown): T | null => {
    if (typeof jsonString !== 'string') {
        // Se já for um objeto/array, retorna, senão retorna null.
        return typeof jsonString === 'object' && jsonString !== null
            ? (jsonString as T)
            : null;
    }
    try {
        return JSON.parse(jsonString) as T;
    } catch {
        return null;
    }
};

/**
 * Hook customizado para encapsular a lógica do formulário de ausência.
 */
const useAusenciaForm = ({
    ausencia,
    horasPorProjetoPorDia,
}: EditPageProps) => {
    const { data, setData, put, processing, errors } = useForm<AusenciaForm>({
        titulo: ausencia.titulo,
        projeto_id: ausencia.projeto_id,
        data_inicio: ausencia.data_inicio.substring(0, 10),
        data_fim: ausencia.data_fim.substring(0, 10),
        justificativa: ausencia.justificativa || '',
        horas_a_compensar: ausencia.horas_a_compensar || 0,
        compensacao_data_inicio: ausencia.compensacao_data_inicio
            ? ausencia.compensacao_data_inicio.substring(0, 10)
            : undefined,
        compensacao_data_fim: ausencia.compensacao_data_fim
            ? ausencia.compensacao_data_fim.substring(0, 10)
            : undefined,
        compensacao_horarios: JSON.stringify(
            safeJsonParse<CompensacaoDia[]>(ausencia.compensacao_horarios) ??
                [],
        ),
    });

    const [diasCompensacao, setDiasCompensacao] = useState<CompensacaoDia[]>(
        () => safeJsonParse<CompensacaoDia[]>(data.compensacao_horarios) ?? [],
    );


    const totalHorasCalculadas = useMemo(() => {
        const { projeto_id, data_inicio, data_fim } = data;
        if (!projeto_id || !data_inicio || !data_fim) {
            return 0;
        }

        const projetoHorarios = horasPorProjetoPorDia[projeto_id];
        const inicio = parseISO(data_inicio);
        const fim = parseISO(data_fim);

        if (
            !projetoHorarios ||
            !isValid(inicio) ||
            !isValid(fim) ||
            inicio > fim
        ) {
            return 0;
        }

        return eachDayOfInterval({ start: inicio, end: fim }).reduce(
            (acc, dia) => {
                const diaSemana = DIAS_DA_SEMANA_MAP[getDay(dia)];
                return acc + (projetoHorarios[diaSemana] || 0);
            },
            0,
        );
    }, [data, horasPorProjetoPorDia]);

    useEffect(() => {
        setData('horas_a_compensar', totalHorasCalculadas);
    }, [totalHorasCalculadas, setData]);

    useEffect(() => {
        const { compensacao_data_inicio, compensacao_data_fim } = data;
        if (!compensacao_data_inicio || !compensacao_data_fim) {
            setDiasCompensacao([]);
            return;
        }
        const inicio = parseISO(compensacao_data_inicio);
        const fim = parseISO(compensacao_data_fim);

        if (!isValid(inicio) || !isValid(fim) || inicio > fim) {
            setDiasCompensacao([]);
            return;
        }
        const diasIntervalo = eachDayOfInterval({ start: inicio, end: fim });
        setDiasCompensacao((diasAtuais: CompensacaoDia[]) => {
            const diasAtuaisMap = new Map(diasAtuais.map((d) => [d.data, d]));
            return diasIntervalo.map((diaDate) => {
                const diaFormatado = format(diaDate, 'yyyy-MM-dd');
                return (
                    diasAtuaisMap.get(diaFormatado) || {
                        data: diaFormatado,
                        horario: [],
                    }
                );
            });
        });
    }, [data]);

    useEffect(() => {
        const compensacaoFormatada = diasCompensacao.filter(
            (dia) => dia.horario.length > 0,
        );
        setData('compensacao_horarios', JSON.stringify(compensacaoFormatada));
    }, [diasCompensacao, setData]);

    const totalHorasCompensadas = useMemo(
        () => diasCompensacao.reduce((acc, dia) => acc + dia.horario.length, 0),
        [diasCompensacao],
    );

    const handleHoraChange = useCallback(
        (diaData: string, hora: number, checked: boolean) => {
            setDiasCompensacao((diasAtuais: CompensacaoDia[]) =>
                diasAtuais.map((dia) =>
                    dia.data === diaData
                        ? {
                              ...dia,
                              horario: checked
                                  ? [...dia.horario, hora].sort((a, b) => a - b)
                                  : dia.horario.filter((h) => h !== hora),
                          }
                        : dia,
                ),
            );
        },
        [],
    );

    const { toast } = useToast();

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('ausencias.update', ausencia.id), {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Erro ao salvar a ausência:', errors);
                toast(
                    'Erro ao salvar a ausência. Verifique os campos.',
                    'error',
                );
            },
            onSuccess: () => {
                toast('Ausência atualizada com sucesso!', 'success');
            },
        });
    };

    return {
        data,
        setData,
        put,
        processing,
        errors,
        submit,
        diasCompensacao,
        totalHorasCompensadas,
        handleHoraChange,
        totalHorasCalculadas,
    };
};

const Edit = (props: EditPageProps) => {
    const { ausencia, projetosAtivos } = props;
    const {
        data,
        setData,
        processing,
        errors,
        submit,
        diasCompensacao,
        totalHorasCompensadas,
        handleHoraChange,
        totalHorasCalculadas,
    } = useAusenciaForm(props);

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-base-content text-xl leading-tight font-semibold">
                        Editar Ausência
                    </h2>
                    <Link
                        href={route('ausencias.index')}
                        className="btn btn-ghost"
                    >
                        Voltar
                    </Link>
                </div>
            }
        >
            <Head title={`Editar Ausência - ${ausencia.titulo}`} />
            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            <form onSubmit={submit} className="space-y-8">
                                {/* Seção 1: Informações da Ausencia */}
                                <div className="space-y-4">
                                    <h3 className="text-base-content text-lg font-medium">
                                        Detalhes da Ausência
                                    </h3>
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div className="form-control">
                                            <label
                                                className="label"
                                                htmlFor="titulo"
                                            >
                                                <span className="label-text">
                                                    Título *
                                                </span>
                                            </label>
                                            <input
                                                id="titulo"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.titulo ? 'input-error' : ''}`}
                                                value={data.titulo}
                                                onChange={(e) =>
                                                    setData(
                                                        'titulo',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                            {errors.titulo && (
                                                <p className="text-error mt-2 text-sm">
                                                    {errors.titulo}
                                                </p>
                                            )}
                                        </div>
                                        <div className="form-control">
                                            <label
                                                className="label"
                                                htmlFor="projeto_id"
                                            >
                                                <span className="label-text">
                                                    Projeto *
                                                </span>
                                            </label>
                                            <select
                                                id="projeto_id"
                                                className={`select select-bordered w-full ${errors.projeto_id ? 'select-error' : ''}`}
                                                value={data.projeto_id}
                                                onChange={(e) =>
                                                    setData(
                                                        'projeto_id',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            >
                                                <option disabled value="">
                                                    Selecione um projeto
                                                </option>
                                                {projetosAtivos.map(
                                                    (projeto: Projeto) => (
                                                        <option
                                                            key={projeto.id}
                                                            value={projeto.id}
                                                        >
                                                            {projeto.nome} -{' '}
                                                            {projeto.cliente}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                            {errors.projeto_id && (
                                                <p className="text-error mt-2 text-sm">
                                                    {errors.projeto_id}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        <div className="form-control">
                                            <label
                                                className="label"
                                                htmlFor="data_inicio"
                                            >
                                                <span className="label-text">
                                                    Data início *
                                                </span>
                                            </label>
                                            <input
                                                id="data_inicio"
                                                type="date"
                                                className={`input input-bordered w-full ${errors.data_inicio ? 'input-error' : ''}`}
                                                value={data.data_inicio}
                                                onChange={(e) =>
                                                    setData(
                                                        'data_inicio',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                            {errors.data_inicio && (
                                                <p className="text-error mt-2 text-sm">
                                                    {errors.data_inicio}
                                                </p>
                                            )}
                                        </div>
                                        <div className="form-control">
                                            <label
                                                className="label"
                                                htmlFor="data_fim"
                                            >
                                                <span className="label-text">
                                                    Data fim *
                                                </span>
                                            </label>
                                            <input
                                                id="data_fim"
                                                type="date"
                                                className={`input input-bordered w-full ${errors.data_fim ? 'input-error' : ''}`}
                                                value={data.data_fim}
                                                onChange={(e) =>
                                                    setData(
                                                        'data_fim',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                            {errors.data_fim && (
                                                <p className="text-error mt-2 text-sm">
                                                    {errors.data_fim}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text">
                                                Justificativa *
                                            </span>
                                        </label>
                                        <RichTextEditor
                                            content={data.justificativa}
                                            onChange={(content) =>
                                                setData(
                                                    'justificativa',
                                                    content,
                                                )
                                            }
                                            placeholder="Descreva o motivo da Ausencia..."
                                        />
                                        {errors.justificativa && (
                                            <p className="text-error mt-2 text-sm">
                                                {errors.justificativa}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="divider"></div>

                                {/* Seção 2: Plano de Compensação */}
                                <div className="space-y-6">
                                    <h3 className="text-base-content text-lg font-medium">
                                        Plano de Compensação
                                    </h3>
                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                                        <div className="form-control">
                                            <label
                                                className="label"
                                                htmlFor="horas_a_compensar"
                                            >
                                                <span className="label-text">
                                                    Horas a compensar
                                                </span>
                                            </label>
                                            <input
                                                id="horas_a_compensar"
                                                type="number"
                                                min="0"
                                                className={`input input-bordered w-full ${errors.horas_a_compensar ? 'input-error' : ''}`}
                                                value={data.horas_a_compensar}
                                                onChange={(e) =>
                                                    setData(
                                                        'horas_a_compensar',
                                                        Number(e.target.value),
                                                    )
                                                }
                                            />
                                            <div className="label">
                                                <span
                                                    className={`label-text-alt ${data.horas_a_compensar !== totalHorasCalculadas ? 'text-warning' : ''}`}
                                                >
                                                    {data.horas_a_compensar !==
                                                    totalHorasCalculadas
                                                        ? `Valor recomendado: ${totalHorasCalculadas}h. O valor inserido está diferente.`
                                                        : `Total de horas no período: ${totalHorasCalculadas}h`}
                                                </span>
                                            </div>
                                            {errors.horas_a_compensar && (
                                                <p className="text-error mt-2 text-sm">
                                                    {errors.horas_a_compensar}
                                                </p>
                                            )}
                                        </div>
                                        <div className="form-control">
                                            <label
                                                className="label"
                                                htmlFor="compensacao_data_inicio"
                                            >
                                                <span className="label-text">
                                                    Início da Compensação
                                                </span>
                                            </label>
                                            <input
                                                id="compensacao_data_inicio"
                                                type="date"
                                                className={`input input-bordered w-full ${errors.compensacao_data_inicio ? 'input-error' : ''}`}
                                                value={
                                                    data.compensacao_data_inicio ||
                                                    ''
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'compensacao_data_inicio',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                        <div className="form-control">
                                            <label
                                                className="label"
                                                htmlFor="compensacao_data_fim"
                                            >
                                                <span className="label-text">
                                                    Fim da Compensação
                                                </span>
                                            </label>
                                            <input
                                                id="compensacao_data_fim"
                                                type="date"
                                                className={`input input-bordered w-full ${errors.compensacao_data_fim ? 'input-error' : ''}`}
                                                value={
                                                    data.compensacao_data_fim ||
                                                    ''
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'compensacao_data_fim',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                    </div>
                                    <div className="space-y-4">
                                        {diasCompensacao.map((dia) => (
                                            <div
                                                key={dia.data}
                                                className="border-base-300 rounded-lg border p-4"
                                            >
                                                <div className="mb-4 flex items-center justify-between">
                                                    <h4 className="font-semibold">
                                                        {format(
                                                            parseISO(dia.data),
                                                            'dd/MM/yyyy',
                                                        )}
                                                        <span className="text-base-content/80 ml-2 font-normal">
                                                            {format(
                                                                parseISO(
                                                                    dia.data,
                                                                ),
                                                                'EEEE',
                                                                {
                                                                    locale: ptBR,
                                                                },
                                                            )}
                                                        </span>
                                                    </h4>
                                                    <span className="text-sm font-medium">
                                                        Horas selecionadas:{' '}
                                                        {dia.horario.length}
                                                    </span>
                                                </div>
                                                <div className="bg-base-200 grid grid-cols-4 gap-2 rounded-lg p-2 sm:grid-cols-5 md:grid-cols-8">
                                                    {HORAS_DO_DIA.map(
                                                        (hora) => (
                                                            <label
                                                                key={hora}
                                                                className="flex cursor-pointer flex-col items-center rounded-lg p-2 text-center"
                                                            >
                                                                <span className="text-sm font-medium">
                                                                    {hora}h
                                                                </span>
                                                                <input
                                                                    // CORREÇÃO: Uso de crases para template literals
                                                                    id={`compensacao-${dia.data}-${hora}`}
                                                                    name={`compensacao-${dia.data}-${hora}`}
                                                                    type="checkbox"
                                                                    className="checkbox checkbox-primary checkbox-sm mt-1"
                                                                    checked={dia.horario.includes(
                                                                        hora,
                                                                    )}
                                                                    onChange={(
                                                                        e,
                                                                    ) =>
                                                                        handleHoraChange(
                                                                            dia.data,
                                                                            hora,
                                                                            e
                                                                                .target
                                                                                .checked,
                                                                        )
                                                                    }
                                                                />
                                                            </label>
                                                        ),
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="text-right">
                                        <p className="font-semibold">
                                            Total de horas compensadas:{' '}
                                            {totalHorasCompensadas} /{' '}
                                            {data.horas_a_compensar}
                                        </p>
                                        {totalHorasCompensadas >
                                            data.horas_a_compensar && (
                                            <p className="text-warning text-sm">
                                                Você está compensando mais horas
                                                do que o necessário.
                                            </p>
                                        )}
                                    </div>
                                    {errors.compensacao_horarios && (
                                        <p className="text-error mt-2 text-sm">
                                            {errors.compensacao_horarios}
                                        </p>
                                    )}
                                </div>
                                {/* Botões de Ação */}
                                <div className="card-actions border-base-300 justify-end border-t pt-6">
                                    <Link
                                        href={route('ausencias.index')}
                                        className="btn btn-ghost"
                                    >
                                        Cancelar
                                    </Link>
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing
                                            ? 'Salvando...'
                                            : 'Salvar Alterações'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Edit;
