import RichTextEditor from '@/Components/RichTextEditor';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Ausencia, PageProps, Projeto } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { eachDayOfInterval, format, getDay, isValid, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { FormEventHandler, useEffect, useMemo, useState } from 'react';

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
    compensacao_horarios?: string; // JSON string
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

const Edit = ({
    ausencia,
    projetosAtivos,
    horasPorProjetoPorDia,
}: EditPageProps) => {
    const { data, setData, put, processing, errors } = useForm<AusenciaForm>({
        titulo: ausencia.titulo,
        projeto_id: ausencia.projeto_id,
        data_inicio: ausencia.data_inicio
            ? format(parseISO(ausencia.data_inicio), 'yyyy-MM-dd')
            : format(new Date(), 'yyyy-MM-dd'),
        data_fim: ausencia.data_fim
            ? format(parseISO(ausencia.data_fim), 'yyyy-MM-dd')
            : format(new Date(), 'yyyy-MM-dd'),
        justificativa: ausencia.justificativa || '',
        horas_a_compensar:
            typeof ausencia.horas_a_compensar === 'number'
                ? ausencia.horas_a_compensar
                : 0,
        compensacao_data_inicio: ausencia.compensacao_data_inicio
            ? format(parseISO(ausencia.compensacao_data_inicio), 'yyyy-MM-dd')
            : format(new Date(), 'yyyy-MM-dd'),
        compensacao_data_fim: ausencia.compensacao_data_fim
            ? format(parseISO(ausencia.compensacao_data_fim), 'yyyy-MM-dd')
            : format(new Date(), 'yyyy-MM-dd'),
        compensacao_horarios:
            typeof ausencia.compensacao_horarios === 'string'
                ? ausencia.compensacao_horarios
                : Array.isArray(ausencia.compensacao_horarios)
                  ? JSON.stringify(ausencia.compensacao_horarios)
                  : '[]',
    });

    const [diasCompensacao, setDiasCompensacao] = useState<CompensacaoDia[]>(
        [],
    );

    // Inicializa os dias de compensação vindos do backend
    useEffect(() => {
        const horarios = Array.isArray(ausencia.compensacao_horarios)
            ? ausencia.compensacao_horarios
            : [];

        const diasIniciais = horarios.map((dia) => ({
            data: dia.data,
            horario: dia.horario || [],
        }));
        setDiasCompensacao(diasIniciais);
    }, [ausencia.compensacao_horarios]);

    // Calcula horas a compensar automaticamente
    useEffect(() => {
        if (!data.projeto_id || !data.data_inicio || !data.data_fim) {
            setData('horas_a_compensar', 0);
            return;
        }
        try {
            const projetoHorarios = horasPorProjetoPorDia[data.projeto_id];
            if (!projetoHorarios) {
                setData('horas_a_compensar', 0);
                return;
            }
            const inicio = parseISO(data.data_inicio);
            const fim = parseISO(data.data_fim);
            if (!isValid(inicio) || !isValid(fim) || inicio > fim) {
                setData('horas_a_compensar', 0);
                return;
            }
            const diasNoIntervalo = eachDayOfInterval({
                start: inicio,
                end: fim,
            });
            const totalHoras = diasNoIntervalo.reduce((acc, dia) => {
                const diaDaSemana = DIAS_DA_SEMANA_MAP[getDay(dia)];
                return acc + (projetoHorarios[diaDaSemana] || 0);
            }, 0);
            setData('horas_a_compensar', totalHoras);
        } catch {
            setData('horas_a_compensar', 0);
        }
    }, [data.projeto_id, data.data_inicio, data.data_fim]);

    // Atualiza os dias de compensação conforme intervalo, preservando horas do backend
    useEffect(() => {
        const inicio = parseISO(data.compensacao_data_inicio || '');
        const fim = parseISO(data.compensacao_data_fim || '');
        if (isValid(inicio) && isValid(fim) && inicio <= fim) {
            const diasNoIntervalo = eachDayOfInterval({
                start: inicio,
                end: fim,
            });
            setDiasCompensacao((diasAtuais) => {
                return diasNoIntervalo.map((diaDate) => {
                    const diaFormatado = format(diaDate, 'yyyy-MM-dd');
                    const diaExistente = diasAtuais.find(
                        (d) => d.data === diaFormatado,
                    );
                    return diaExistente
                        ? diaExistente
                        : { data: diaFormatado, horario: [] };
                });
            });
        } else {
            setDiasCompensacao([]);
        }
    }, [data.compensacao_data_inicio, data.compensacao_data_fim]);

    // Sincroniza os dias de compensação com o form
    useEffect(() => {
        const compensacaoFormatada = diasCompensacao
            .filter((dia) => dia.horario.length > 0)
            .map(({ data, horario }) => ({ data, horario }));
        setData('compensacao_horarios', JSON.stringify(compensacaoFormatada));
    }, [diasCompensacao, setData]);

    const totalHorasCompensadas = useMemo(() => {
        return diasCompensacao.reduce(
            (acc, dia) => acc + dia.horario.length,
            0,
        );
    }, [diasCompensacao]);

    const handleHoraChange = (
        diaData: string,
        hora: number,
        checked: boolean,
    ) => {
        setDiasCompensacao((diasAtuais) =>
            diasAtuais.map((dia) => {
                if (dia.data === diaData) {
                    const novosHorarios = checked
                        ? [...dia.horario, hora].sort((a, b) => a - b)
                        : dia.horario.filter((h) => h !== hora);
                    return { ...dia, horario: novosHorarios };
                }
                return dia;
            }),
        );
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('ausencias.update', ausencia.id));
    };

    const HORAS_DO_DIA = Array.from({ length: 15 }, (_, i) => i + 7); // 7h às 21h

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
                                        {/* Título e Projeto */}
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
                                        {/* Data Início e Fim */}
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
                                    {/* Justificativa */}
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
                                        {/* Horas a Compensar */}
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
                                            {errors.horas_a_compensar && (
                                                <p className="text-error mt-2 text-sm">
                                                    {errors.horas_a_compensar}
                                                </p>
                                            )}
                                        </div>
                                        {/* Compensação Início */}
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
                                        {/* Compensação Fim */}
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
                                    {/* Lista de Dias de Compensação */}
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
                                    {/* Resumo */}
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
                                        href={route('Ausencias.index')}
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
