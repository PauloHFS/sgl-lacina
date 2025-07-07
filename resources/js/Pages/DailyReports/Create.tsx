import RichTextEditor from '@/Components/RichTextEditor';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { format } from 'date-fns';
import { FormEventHandler, useEffect, useState } from 'react';

interface Projeto {
    id: string;
    nome: string;
    cliente: string;
}

interface CreatePageProps extends PageProps {
    projetosAtivos: Projeto[];
}

interface DailyReportForm {
    data: string;
    projeto_id: string;
    horas_trabalhadas: number | null;
    o_que_fez_ontem: string;
    o_que_vai_fazer_hoje: string;
    observacoes: string;
}

const Create = ({ projetosAtivos }: CreatePageProps) => {
    const [calculandoHoras, setCalculandoHoras] = useState(false);
    const [horasCalculadas, setHorasCalculadas] = useState<number | null>(null);

    const { data, setData, post, processing, errors } =
        useForm<DailyReportForm>({
            data: format(new Date(), 'yyyy-MM-dd'),
            projeto_id: '',
            horas_trabalhadas: null,
            o_que_fez_ontem: '',
            o_que_vai_fazer_hoje: '',
            observacoes: '',
        });

    const calcularHoras = async () => {
        if (!data.data) return;

        setCalculandoHoras(true);
        try {
            const response = await fetch(
                route('daily-reports.calcular-horas'),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ data: data.data }),
                },
            );

            if (response.ok) {
                const result = await response.json();
                setHorasCalculadas(result.horas_trabalhadas);
                setData('horas_trabalhadas', result.horas_trabalhadas);
            }
        } catch (error) {
            console.error('Erro ao calcular horas:', error);
        } finally {
            setCalculandoHoras(false);
        }
    };

    // Calcular horas automaticamente quando a data mudar
    useEffect(() => {
        if (data.data) {
            calcularHoras();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data.data]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('daily-reports.store'));
    };

    const aplicarHorasCalculadas = () => {
        if (horasCalculadas !== null) {
            setData('horas_trabalhadas', horasCalculadas);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-base-content text-xl leading-tight font-semibold">
                        Novo Daily Report
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
            <Head title="Novo Daily Report" />

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
                                        <div className="join w-full">
                                            <input
                                                id="horas_trabalhadas"
                                                type="number"
                                                min="0"
                                                max="24"
                                                step="0.5"
                                                className={`input input-bordered join-item w-full ${
                                                    errors.horas_trabalhadas
                                                        ? 'input-error'
                                                        : ''
                                                }`}
                                                value={
                                                    data.horas_trabalhadas || ''
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'horas_trabalhadas',
                                                        e.target.value
                                                            ? Number(
                                                                  e.target
                                                                      .value,
                                                              )
                                                            : null,
                                                    )
                                                }
                                                placeholder="Ex: 8"
                                            />
                                            <button
                                                type="button"
                                                onClick={calcularHoras}
                                                className="btn btn-outline join-item"
                                                disabled={
                                                    calculandoHoras ||
                                                    !data.data
                                                }
                                            >
                                                {calculandoHoras ? (
                                                    <span className="loading loading-spinner"></span>
                                                ) : (
                                                    'Calcular'
                                                )}
                                            </button>
                                        </div>
                                        <div className="label justify-start gap-4">
                                            <span className="label-text-alt">
                                                Deixe em branco ou clique em
                                                "Calcular" para usar o horário
                                                cadastrado.
                                            </span>
                                            {horasCalculadas !== null && (
                                                <button
                                                    type="button"
                                                    onClick={
                                                        aplicarHorasCalculadas
                                                    }
                                                    className="badge badge-info cursor-pointer gap-2"
                                                >
                                                    Sugestão: {horasCalculadas}h
                                                </button>
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
                                        <div className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.o_que_fez_ontem}
                                            </span>
                                        </div>
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
                                        <div className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.o_que_vai_fazer_hoje}
                                            </span>
                                        </div>
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
                                        <div className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.observacoes}
                                            </span>
                                        </div>
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
                                            : 'Salvar Daily Report'}
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

export default Create;
