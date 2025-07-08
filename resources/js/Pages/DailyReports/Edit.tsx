import RichTextEditor from '@/Components/RichTextEditor';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';

interface DailyReport {
    id: string;
    data: string;
    horas_trabalhadas: number;
    o_que_fez_ontem: string | null;
    o_que_vai_fazer_hoje: string | null;
    observacoes: string | null;
    projeto_id: string;
    projeto: {
        id: string;
        nome: string;
        cliente: string;
    };
}

interface Projeto {
    id: string;
    nome: string;
    cliente: string;
}

interface EditPageProps extends PageProps {
    dailyReport: DailyReport;
    projetosAtivos: Projeto[];
    horasPorProjetoPorDia: Record<
        string,
        {
            SEGUNDA: number;
            TERCA: number;
            QUARTA: number;
            QUINTA: number;
            SEXTA: number;
            SABADO: number;
            DOMINGO: number;
        }
    >;
}

interface DailyReportForm {
    data: string;
    projeto_id: string;
    horas_trabalhadas: number | null;
    o_que_fez_ontem: string;
    o_que_vai_fazer_hoje: string;
    observacoes: string;
}

const Edit = ({
    dailyReport,
    projetosAtivos,
    horasPorProjetoPorDia,
}: EditPageProps) => {
    console.log({
        horasPorProjetoPorDia,
    });
    const [horasCalculadas, setHorasCalculadas] = useState<number | null>(null);

    const { data, setData, put, processing, errors } = useForm<DailyReportForm>(
        {
            data: dailyReport.data.substring(0, 10),
            projeto_id: dailyReport.projeto_id,
            horas_trabalhadas: dailyReport.horas_trabalhadas,
            o_que_fez_ontem: dailyReport.o_que_fez_ontem || '',
            o_que_vai_fazer_hoje: dailyReport.o_que_vai_fazer_hoje || '',
            observacoes: dailyReport.observacoes || '',
        },
    );

    // Mapear dias da semana em português para inglês
    const mapearDiaDaSemana = (
        data: string,
    ):
        | 'SEGUNDA'
        | 'TERCA'
        | 'QUARTA'
        | 'QUINTA'
        | 'SEXTA'
        | 'SABADO'
        | 'DOMINGO' => {
        const dataObj = new Date(data + 'T00:00:00');
        const diaDaSemanaIngles = dataObj.toLocaleDateString('en-US', {
            weekday: 'long',
        });

        const mapeamento: Record<
            string,
            | 'SEGUNDA'
            | 'TERCA'
            | 'QUARTA'
            | 'QUINTA'
            | 'SEXTA'
            | 'SABADO'
            | 'DOMINGO'
        > = {
            Monday: 'SEGUNDA',
            Tuesday: 'TERCA',
            Wednesday: 'QUARTA',
            Thursday: 'QUINTA',
            Friday: 'SEXTA',
            Saturday: 'SABADO',
            Sunday: 'DOMINGO',
        };

        return mapeamento[diaDaSemanaIngles] || 'SEGUNDA';
    };

    const calcularHorasAutomaticamente = () => {
        if (!data.data || !data.projeto_id) {
            setHorasCalculadas(null);
            return;
        }

        try {
            const diaDaSemana = mapearDiaDaSemana(data.data);
            const horasProjeto = horasPorProjetoPorDia[data.projeto_id];

            if (horasProjeto) {
                const horas = horasProjeto[diaDaSemana] || 0;
                setHorasCalculadas(horas);
                // Só atualiza se for diferente da data original (usuário mudou a data)
                if (data.data !== dailyReport.data.substring(0, 10)) {
                    setData('horas_trabalhadas', horas);
                }
            } else {
                setHorasCalculadas(0);
            }
        } catch (error) {
            console.error('Erro ao calcular horas:', error);
            setHorasCalculadas(null);
        }
    };

    // Calcular horas automaticamente quando a data ou projeto mudarem
    useEffect(() => {
        calcularHorasAutomaticamente();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.data, data.projeto_id]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('daily-reports.update', dailyReport.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-base-content text-xl leading-tight font-semibold">
                        Editar Daily Report
                    </h2>
                    <Link
                        href={route('daily-reports.index')}
                        className="btn btn-ghost"
                    >
                        Voltar
                    </Link>
                </div>
            }
        >
            <Head title="Editar Daily Report" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow-xl">
                        <div className="card-body">
                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    {/* Data */}
                                    <div className="form-control">
                                        <label className="label" htmlFor="data">
                                            <span className="label-text">
                                                Data *
                                            </span>
                                        </label>
                                        <input
                                            id="data"
                                            type="date"
                                            className={`input input-bordered w-full ${
                                                errors.data ? 'input-error' : ''
                                            }`}
                                            value={data.data}
                                            onChange={(e) =>
                                                setData('data', e.target.value)
                                            }
                                            required
                                        />
                                        {errors.data && (
                                            <div className="label">
                                                <span className="label-text-alt text-error">
                                                    {errors.data}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Projeto */}
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
                                            className={`select select-bordered w-full ${
                                                errors.projeto_id
                                                    ? 'select-error'
                                                    : ''
                                            }`}
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
                                            {projetosAtivos.map((projeto) => (
                                                <option
                                                    key={projeto.id}
                                                    value={projeto.id}
                                                >
                                                    {projeto.nome} -{' '}
                                                    {projeto.cliente}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.projeto_id && (
                                            <div className="label">
                                                <span className="label-text-alt text-error">
                                                    {errors.projeto_id}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    {/* Horas Trabalhadas */}
                                    <div className="form-control">
                                        <label
                                            className="label"
                                            htmlFor="horas_trabalhadas"
                                        >
                                            <span className="label-text">
                                                Horas Trabalhadas
                                            </span>
                                        </label>
                                        <input
                                            id="horas_trabalhadas"
                                            type="number"
                                            min="0"
                                            max="24"
                                            step="0.5"
                                            className={`input input-bordered w-full ${
                                                errors.horas_trabalhadas
                                                    ? 'input-error'
                                                    : ''
                                            }`}
                                            value={data.horas_trabalhadas || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'horas_trabalhadas',
                                                    e.target.value
                                                        ? Number(e.target.value)
                                                        : null,
                                                )
                                            }
                                            placeholder="Ex: 8"
                                        />
                                        <div className="label justify-start gap-4">
                                            <span className="label-text-alt">
                                                Valor sugerido automaticamente
                                                baseado no seu horário
                                                cadastrado e projeto
                                                selecionado.
                                            </span>
                                            {horasCalculadas !== null && (
                                                <span className="badge badge-info gap-2">
                                                    Sugerido: {horasCalculadas}h
                                                </span>
                                            )}
                                        </div>
                                        {errors.horas_trabalhadas && (
                                            <div className="label">
                                                <span className="label-text-alt text-error">
                                                    {errors.horas_trabalhadas}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* O que fez ontem */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">
                                            O que fez ontem
                                        </span>
                                    </label>
                                    <RichTextEditor
                                        content={data.o_que_fez_ontem}
                                        onChange={(content: string) =>
                                            setData('o_que_fez_ontem', content)
                                        }
                                        placeholder="Descreva o que fez no dia anterior..."
                                    />
                                    {errors.o_que_fez_ontem && (
                                        <label className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.o_que_fez_ontem}
                                            </span>
                                        </label>
                                    )}
                                </div>

                                {/* O que vai fazer hoje */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">
                                            O que vai fazer hoje
                                        </span>
                                    </label>
                                    <RichTextEditor
                                        content={data.o_que_vai_fazer_hoje}
                                        onChange={(content: string) =>
                                            setData(
                                                'o_que_vai_fazer_hoje',
                                                content,
                                            )
                                        }
                                        placeholder="Descreva o que vai fazer hoje..."
                                    />
                                    {errors.o_que_vai_fazer_hoje && (
                                        <label className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.o_que_vai_fazer_hoje}
                                            </span>
                                        </label>
                                    )}
                                </div>

                                {/* Observações */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text">
                                            Observações
                                        </span>
                                    </label>
                                    <RichTextEditor
                                        content={data.observacoes}
                                        onChange={(content: string) =>
                                            setData('observacoes', content)
                                        }
                                        placeholder="Observações adicionais sobre o trabalho..."
                                    />
                                    {errors.observacoes && (
                                        <label className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.observacoes}
                                            </span>
                                        </label>
                                    )}
                                </div>

                                {/* Botões */}
                                <div className="card-actions justify-end">
                                    <Link
                                        href={route('daily-reports.index')}
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
                                            : 'Atualizar Daily Report'}
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
