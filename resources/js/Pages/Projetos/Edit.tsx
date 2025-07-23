import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { IntervenienteFinanceiro, Projeto, TipoProjeto } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface CampoExtra {
    key: string;
    value: string;
}

interface EditPageProps {
    projeto: Projeto;
    intervenientes_financeiros: Array<IntervenienteFinanceiro>;
}

const tiposProjeto: TipoProjeto[] = [
    'DOUTORADO',
    'MESTRADO',
    'PDI',
    'TCC',
    'SUPORTE',
];

export default function Edit({
    projeto,
    intervenientes_financeiros,
}: EditPageProps) {
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
        .toISOString()
        .split('T')[0];
    const lastDayOfMonth = new Date(
        today.getFullYear(),
        today.getMonth() + 1,
        0,
    )
        .toISOString()
        .split('T')[0];

    const { data, setData, patch, processing, errors } = useForm<
        Pick<
            Projeto,
            | 'nome'
            | 'descricao'
            | 'valor_total'
            | 'meses_execucao'
            | 'campos_extras'
            | 'data_inicio'
            | 'data_termino'
            | 'cliente'
            | 'slack_url'
            | 'discord_url'
            | 'board_url'
            | 'git_url'
            | 'tipo'
            | 'interveniente_financeiro_id'
            | 'numero_convenio'
        >
    >({
        nome: projeto.nome,
        descricao: projeto.descricao || '',
        valor_total: projeto.valor_total || 0,
        meses_execucao: projeto.meses_execucao || 0,
        campos_extras: projeto.campos_extras || {},
        data_inicio: projeto.data_inicio
            ? projeto.data_inicio.substring(0, 10)
            : firstDayOfMonth,
        data_termino: projeto.data_termino
            ? projeto.data_termino.substring(0, 10)
            : lastDayOfMonth,
        cliente: projeto.cliente,
        slack_url: projeto.slack_url || '',
        discord_url: projeto.discord_url || '',
        board_url: projeto.board_url || '',
        git_url: projeto.git_url || '',
        tipo: projeto.tipo,
        interveniente_financeiro_id: projeto.interveniente_financeiro_id || '',
        numero_convenio: projeto.numero_convenio || '',
    });

    const { toast } = useToast();

    const camposExtrasArray: CampoExtra[] = projeto.campos_extras
        ? Object.entries(projeto.campos_extras).map(([key, value]) => ({
              key,
              value: String(value),
          }))
        : [];

    const [campos, setCampos] = useState<CampoExtra[]>(camposExtrasArray);

    const adicionarCampo = () => {
        setCampos([...campos, { key: '', value: '' }]);
    };

    const removerCampo = (index: number) => {
        const novosCampos = campos.filter((_, i) => i !== index);
        setCampos(novosCampos);
        atualizarDados(novosCampos);
    };

    const atualizarCampo = (
        index: number,
        field: 'key' | 'value',
        newValue: string,
    ) => {
        const novosCampos = campos.map((campo, i) =>
            i === index ? { ...campo, [field]: newValue } : campo,
        );
        setCampos(novosCampos);
        atualizarDados(novosCampos);
    };

    const atualizarDados = (camposAtualizados: CampoExtra[]) => {
        // Filtrar campos vazios e converter para objeto
        const camposObj = camposAtualizados
            .filter(
                (campo) => campo.key.trim() !== '' && campo.value.trim() !== '',
            )
            .reduce(
                (acc, campo) => {
                    acc[campo.key.trim()] = campo.value.trim();
                    return acc;
                },
                {} as Record<string, string>,
            );

        setData('campos_extras', camposObj);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('projetos.update', projeto.id), {
            onSuccess: () => {
                toast('Projeto Atualizada com sucesso!', 'success');
            },
            onError: (formErrors) => {
                console.error('Erro ao atualizar projeto:', formErrors);
                toast(
                    'Erro ao atualizar projeto. Verifique os campos.',
                    'error',
                );
            },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Editar Projeto: ${projeto.nome}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 shadow sm:rounded-lg">
                        <div className="p-6 sm:p-8">
                            <div className="mb-6">
                                <h1 className="text-base-content text-2xl font-bold">
                                    Editar Projeto
                                </h1>
                                <p className="text-base-content/70 mt-1 text-sm">
                                    Atualize as informações do projeto conforme
                                    necessário.
                                </p>
                            </div>

                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    {/* Nome do Projeto */}
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text font-medium">
                                                Nome do Projeto *
                                            </span>
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Digite o nome do projeto"
                                            className={`input input-bordered w-full ${
                                                errors.nome ? 'input-error' : ''
                                            }`}
                                            value={data.nome}
                                            onChange={(e) =>
                                                setData('nome', e.target.value)
                                            }
                                            required
                                        />
                                        {errors.nome && (
                                            <div className="label">
                                                <span className="label-text-alt text-error">
                                                    {errors.nome}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Cliente */}
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text font-medium">
                                                Parceiro/Cliente*
                                            </span>
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="Digite o nome do cliente"
                                            className={`input input-bordered w-full ${
                                                errors.cliente
                                                    ? 'input-error'
                                                    : ''
                                            }`}
                                            value={data.cliente}
                                            onChange={(e) =>
                                                setData(
                                                    'cliente',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                        />
                                        {errors.cliente && (
                                            <div className="label">
                                                <span className="label-text-alt text-error">
                                                    {errors.cliente}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Número do Acordo / Contrato / Convênio  */}
                                    <div>
                                        <label
                                            htmlFor="numero_convenio"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Número do
                                                Acordo/Contrato/Convênio
                                            </span>
                                        </label>
                                        <input
                                            id="numero_convenio"
                                            type="text"
                                            value={data.numero_convenio || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'numero_convenio',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
                                            placeholder="Número do Acordo/Contrato/Convênio"
                                        />
                                        {errors.numero_convenio && (
                                            <span className="text-error text-xs">
                                                {errors.numero_convenio}
                                            </span>
                                        )}
                                    </div>

                                    {/* Data de Início */}
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text font-medium">
                                                Data de Início*
                                            </span>
                                        </label>
                                        <input
                                            type="date"
                                            className={`input input-bordered w-full ${
                                                errors.data_inicio
                                                    ? 'input-error'
                                                    : ''
                                            }`}
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
                                            <div className="label">
                                                <span className="label-text-alt text-error">
                                                    {errors.data_inicio}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Data de Término */}
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text font-medium">
                                                Data de Término
                                            </span>
                                        </label>
                                        <input
                                            type="date"
                                            className={`input input-bordered w-full ${
                                                errors.data_termino
                                                    ? 'input-error'
                                                    : ''
                                            }`}
                                            value={data.data_termino || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'data_termino',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.data_termino && (
                                            <div className="label">
                                                <span className="label-text-alt text-error">
                                                    {errors.data_termino}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Interveniente Financeiro */}
                                    <div>
                                        <label
                                            htmlFor="interveniente_financeiro_id"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Interveniente Financeiro
                                            </span>
                                        </label>
                                        <select
                                            id="interveniente_financeiro_id"
                                            value={
                                                data.interveniente_financeiro_id ||
                                                ''
                                            }
                                            onChange={(e) =>
                                                setData(
                                                    'interveniente_financeiro_id',
                                                    e.target.value,
                                                )
                                            }
                                            className="select select-bordered w-full"
                                        >
                                            <option value="" disabled>
                                                Selecione um interveniente
                                                financeiro
                                            </option>
                                            {intervenientes_financeiros.map(
                                                ({ id, nome }) => (
                                                    <option key={id} value={id}>
                                                        {nome}
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                        {errors.interveniente_financeiro_id && (
                                            <span className="text-error text-xs">
                                                {
                                                    errors.interveniente_financeiro_id
                                                }
                                            </span>
                                        )}
                                    </div>

                                    {/* Tipo do Projeto */}
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text font-medium">
                                                Tipo do Projeto*
                                            </span>
                                        </label>
                                        <select
                                            className={`select select-bordered w-full ${
                                                errors.tipo
                                                    ? 'select-error'
                                                    : ''
                                            }`}
                                            value={data.tipo}
                                            onChange={(e) =>
                                                setData(
                                                    'tipo',
                                                    e.target
                                                        .value as TipoProjeto,
                                                )
                                            }
                                            required
                                        >
                                            <option value="">
                                                Selecione o tipo
                                            </option>
                                            {tiposProjeto.map((tipo) => (
                                                <option key={tipo} value={tipo}>
                                                    {tipo}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.tipo && (
                                            <div className="label">
                                                <span className="label-text-alt text-error">
                                                    {errors.tipo}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Valor Total */}
                                    <div className="form-control">
                                        <label
                                            htmlFor="valor_total"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Valor Total* (R$)
                                            </span>
                                        </label>
                                        <input
                                            id="valor_total"
                                            type="text"
                                            value={(
                                                (data.valor_total || 0) / 100
                                            ).toLocaleString('pt-BR', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
                                            })}
                                            onChange={(e) => {
                                                const apenasNumeros =
                                                    e.target.value.replace(
                                                        /\D/g,
                                                        '',
                                                    );
                                                const centavos = parseInt(
                                                    apenasNumeros || '0',
                                                );
                                                setData(
                                                    'valor_total',
                                                    centavos,
                                                );
                                            }}
                                            onFocus={(e) => {
                                                e.target.value = (
                                                    (data.valor_total || 0) /
                                                    100
                                                ).toFixed(2);
                                            }}
                                            onBlur={(e) => {
                                                e.target.value = (
                                                    (data.valor_total || 0) /
                                                    100
                                                ).toLocaleString('pt-BR', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2,
                                                });
                                            }}
                                            className="input input-bordered w-full"
                                            required
                                            placeholder="0,00"
                                        />
                                        {errors.valor_total && (
                                            <span className="text-error text-xs">
                                                {errors.valor_total}
                                            </span>
                                        )}
                                    </div>
                                </div>

                                {/* Duração da execução */}
                                <div>
                                    <label
                                        htmlFor="meses_execucao"
                                        className="label"
                                    >
                                        <span className="label-text text-base-content">
                                            Duração da execução (meses)*
                                        </span>
                                    </label>
                                    <input
                                        id="meses_execucao"
                                        type="number"
                                        value={data.meses_execucao}
                                        onChange={(e) => {
                                            setData(
                                                'meses_execucao',
                                                parseFloat(e.target.value) || 0,
                                            );
                                        }}
                                        className="input input-bordered w-full"
                                        required
                                        placeholder="12"
                                    />
                                    {errors.meses_execucao && (
                                        <span className="text-error text-xs">
                                            {errors.meses_execucao}
                                        </span>
                                    )}
                                </div>

                                {/* Descrição */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-medium">
                                            Descrição
                                        </span>
                                    </label>
                                    <textarea
                                        placeholder="Descreva o projeto..."
                                        className={`textarea textarea-bordered h-24 w-full ${
                                            errors.descricao
                                                ? 'textarea-error'
                                                : ''
                                        }`}
                                        value={data.descricao || ''}
                                        onChange={(e) =>
                                            setData('descricao', e.target.value)
                                        }
                                    />
                                    {errors.descricao && (
                                        <div className="label">
                                            <span className="label-text-alt text-error">
                                                {errors.descricao}
                                            </span>
                                        </div>
                                    )}
                                </div>

                                {/* Links Úteis */}
                                <div className="space-y-4">
                                    <h3 className="text-base-content text-lg font-medium">
                                        Links Úteis
                                    </h3>

                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        {/* Slack URL */}
                                        <div className="form-control">
                                            <label className="label">
                                                <span className="label-text">
                                                    URL do Slack
                                                </span>
                                            </label>
                                            <input
                                                type="url"
                                                placeholder="https://workspace.slack.com"
                                                className={`input input-bordered w-full ${
                                                    errors.slack_url
                                                        ? 'input-error'
                                                        : ''
                                                }`}
                                                value={data.slack_url || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'slack_url',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.slack_url && (
                                                <div className="label">
                                                    <span className="label-text-alt text-error">
                                                        {errors.slack_url}
                                                    </span>
                                                </div>
                                            )}
                                        </div>

                                        {/* Discord URL */}
                                        <div className="form-control">
                                            <label className="label">
                                                <span className="label-text">
                                                    URL do Discord
                                                </span>
                                            </label>
                                            <input
                                                type="url"
                                                placeholder="https://discord.gg/..."
                                                className={`input input-bordered w-full ${
                                                    errors.discord_url
                                                        ? 'input-error'
                                                        : ''
                                                }`}
                                                value={data.discord_url || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'discord_url',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.discord_url && (
                                                <div className="label">
                                                    <span className="label-text-alt text-error">
                                                        {errors.discord_url}
                                                    </span>
                                                </div>
                                            )}
                                        </div>

                                        {/* Board URL */}
                                        <div className="form-control">
                                            <label className="label">
                                                <span className="label-text">
                                                    URL do Board/Kanban
                                                </span>
                                            </label>
                                            <input
                                                type="url"
                                                placeholder="https://trello.com/b/..."
                                                className={`input input-bordered w-full ${
                                                    errors.board_url
                                                        ? 'input-error'
                                                        : ''
                                                }`}
                                                value={data.board_url || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'board_url',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.board_url && (
                                                <div className="label">
                                                    <span className="label-text-alt text-error">
                                                        {errors.board_url}
                                                    </span>
                                                </div>
                                            )}
                                        </div>

                                        {/* Git URL */}
                                        <div className="form-control">
                                            <label className="label">
                                                <span className="label-text">
                                                    URL do Repositório Git
                                                </span>
                                            </label>
                                            <input
                                                type="url"
                                                placeholder="https://github.com/..."
                                                className={`input input-bordered w-full ${
                                                    errors.git_url
                                                        ? 'input-error'
                                                        : ''
                                                }`}
                                                value={data.git_url || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'git_url',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.git_url && (
                                                <div className="label">
                                                    <span className="label-text-alt text-error">
                                                        {errors.git_url}
                                                    </span>
                                                </div>
                                            )}
                                        </div>

                                        <div className="md:col-span-2">
                                            <label
                                                htmlFor="campos_extras"
                                                className="label"
                                            >
                                                <span className="label-text text-base-content">
                                                    Campos Extras
                                                </span>
                                            </label>
                                            <p className="text-base-content/70 mb-4 text-xs">
                                                Adicione informações
                                                personalizadas ao projeto. Use
                                                campos chave-valor para
                                                armazenar dados específicos como
                                                tags, categorias ou metadados.
                                            </p>
                                            <div>
                                                <div className="space-y-4">
                                                    {campos.map(
                                                        (campo, index) => (
                                                            <div
                                                                key={index}
                                                                className="border-base-300 grid grid-cols-1 items-end gap-4 rounded-lg border p-4 md:grid-cols-5"
                                                            >
                                                                <div className="md:col-span-2">
                                                                    <InputLabel
                                                                        htmlFor={`key-${index}`}
                                                                        value="Chave"
                                                                    />
                                                                    <TextInput
                                                                        id={`key-${index}`}
                                                                        type="text"
                                                                        className="mt-1 block w-full"
                                                                        value={
                                                                            campo.key
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            atualizarCampo(
                                                                                index,
                                                                                'key',
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                        placeholder="Ex: projeto_favorito"
                                                                    />
                                                                </div>

                                                                <div className="md:col-span-2">
                                                                    <InputLabel
                                                                        htmlFor={`value-${index}`}
                                                                        value="Valor"
                                                                    />
                                                                    <TextInput
                                                                        id={`value-${index}`}
                                                                        type="text"
                                                                        className="mt-1 block w-full"
                                                                        value={
                                                                            campo.value
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            atualizarCampo(
                                                                                index,
                                                                                'value',
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                        placeholder="Ex: Sistema de RH"
                                                                    />
                                                                </div>

                                                                <div className="md:col-span-1">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() =>
                                                                            removerCampo(
                                                                                index,
                                                                            )
                                                                        }
                                                                        className="btn btn-error btn-sm w-full"
                                                                        title="Remover campo"
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
                                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                                            />
                                                                        </svg>
                                                                        Remover
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        ),
                                                    )}
                                                </div>

                                                <div className="mt-4 flex flex-col gap-4 sm:flex-row">
                                                    <button
                                                        type="button"
                                                        onClick={adicionarCampo}
                                                        className="btn btn-outline btn-primary"
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
                                                                strokeWidth={2}
                                                                d="M12 4v16m8-8H4"
                                                            />
                                                        </svg>
                                                        Adicionar Campo
                                                    </button>
                                                </div>

                                                <InputError
                                                    message={
                                                        errors.campos_extras
                                                    }
                                                    className="mt-2"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Buttons */}
                                <div className="flex flex-col justify-end gap-4 pt-6 sm:flex-row">
                                    <button
                                        type="button"
                                        className="btn btn-ghost"
                                        onClick={() => window.history.back()}
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing ? (
                                            <>
                                                <span className="loading loading-spinner loading-sm"></span>
                                                Atualizando...
                                            </>
                                        ) : (
                                            'Atualizar Projeto'
                                        )}
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
