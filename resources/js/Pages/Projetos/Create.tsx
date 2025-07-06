import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { IntervenienteFinanceiro, Projeto, TipoProjeto } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import React, { useState } from 'react';

interface CampoExtra {
    key: string;
    value: string;
}

const tiposProjeto: TipoProjeto[] = [
    'DOUTORADO',
    'MESTRADO',
    'PDI',
    'TCC',
    'SUPORTE',
];

export default function CreateProjeto({
    intervenientes_financeiros,
}: {
    intervenientes_financeiros: Array<IntervenienteFinanceiro>;
}) {
    const { toast } = useToast();
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

    const { data, setData, post, errors, processing } = useForm<
        Pick<
            Projeto,
            | 'nome'
            | 'descricao'
            | 'data_inicio'
            | 'data_termino'
            | 'cliente'
            | 'slack_url'
            | 'discord_url'
            | 'board_url'
            | 'git_url'
            | 'interveniente_financeiro_id'
            | 'tipo'
            | 'valor_total'
            | 'meses_execucao'
            | 'campos_extras'
            | 'numero_convenio'
        >
    >('create-project', {
        nome: '',
        descricao: '',
        data_inicio: firstDayOfMonth,
        data_termino: lastDayOfMonth,
        cliente: '',
        slack_url: '',
        discord_url: '',
        board_url: '',
        git_url: '',
        interveniente_financeiro_id: null, // Inicialmente nulo
        numero_convenio: '',
        tipo: '' as TipoProjeto,
        valor_total: 0,
        meses_execucao: 0,
        campos_extras: {},
    });

    const [campos, setCampos] = useState<CampoExtra[]>([]);

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

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('projetos.store'), {
            onSuccess: () => {
                toast('Projeto cadastrado com sucesso!', 'success');
            },
            onError: (formErrors) => {
                console.error('Erro ao cadastrar projeto:', formErrors);
                toast(
                    'Erro ao cadastrar projeto. Verifique os campos.',
                    'error',
                );
            },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Cadastrar Projeto" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 overflow-hidden shadow-lg">
                        {/* Header */}
                        <div className="from-primary to-accent text-primary-content bg-gradient-to-r p-6">
                            <div className="flex items-center gap-4">
                                <div>
                                    <h1 className="text-2xl font-bold">
                                        Novo Projeto
                                    </h1>
                                    <p className="text-primary-content/80 mt-1">
                                        Preencha as informações abaixo para
                                        cadastrar um novo projeto no laboratório
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Form Content */}
                        <div className="p-6">
                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    {/* Nome */}
                                    <div>
                                        <label htmlFor="nome" className="label">
                                            <span className="label-text text-base-content">
                                                Nome do Projeto*
                                            </span>
                                        </label>
                                        <input
                                            id="nome"
                                            type="text"
                                            value={data.nome}
                                            onChange={(e) =>
                                                setData('nome', e.target.value)
                                            }
                                            className="input input-bordered w-full"
                                            placeholder="Digite o nome do projeto"
                                            required
                                        />
                                        {errors.nome && (
                                            <span className="text-error text-xs">
                                                {errors.nome}
                                            </span>
                                        )}
                                    </div>

                                    {/* Cliente */}
                                    <div>
                                        <label
                                            htmlFor="cliente"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Parceiro/Cliente*
                                            </span>
                                        </label>
                                        <input
                                            id="cliente"
                                            type="text"
                                            value={data.cliente}
                                            onChange={(e) =>
                                                setData(
                                                    'cliente',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
                                            placeholder="Digite o nome do cliente"
                                            required
                                        />
                                        {errors.cliente && (
                                            <span className="text-error text-xs">
                                                {errors.cliente}
                                            </span>
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
                                    <div>
                                        <label
                                            htmlFor="data_inicio"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Data de Início*
                                            </span>
                                        </label>
                                        <input
                                            id="data_inicio"
                                            type="date"
                                            value={data.data_inicio}
                                            onChange={(e) =>
                                                setData(
                                                    'data_inicio',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
                                            required
                                        />
                                        {errors.data_inicio && (
                                            <span className="text-error text-xs">
                                                {errors.data_inicio}
                                            </span>
                                        )}
                                    </div>

                                    {/* Data de Término */}
                                    <div>
                                        <label
                                            htmlFor="data_termino"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Data de Término
                                            </span>
                                        </label>
                                        <input
                                            id="data_termino"
                                            type="date"
                                            value={data.data_termino || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'data_termino',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
                                        />
                                        {errors.data_termino && (
                                            <span className="text-error text-xs">
                                                {errors.data_termino}
                                            </span>
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

                                    {/* Tipo de Projeto */}
                                    <div>
                                        <label htmlFor="tipo" className="label">
                                            <span className="label-text text-base-content">
                                                Tipo de Projeto*
                                            </span>
                                        </label>
                                        <select
                                            id="tipo"
                                            value={data.tipo}
                                            onChange={(e) =>
                                                setData(
                                                    'tipo',
                                                    e.target
                                                        .value as TipoProjeto,
                                                )
                                            }
                                            className="select select-bordered w-full"
                                            required
                                        >
                                            <option value="" disabled>
                                                Selecione um tipo
                                            </option>
                                            {tiposProjeto.map((tipo) => (
                                                <option key={tipo} value={tipo}>
                                                    {tipo}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.tipo && (
                                            <span className="text-error text-xs">
                                                {errors.tipo}
                                            </span>
                                        )}
                                    </div>

                                    {/* Valor Total */}
                                    <div>
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

                                    {/* Duração da execução */}
                                    <div>
                                        <label
                                            htmlFor="meses_execucao"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Duração da execução*
                                            </span>
                                        </label>
                                        <input
                                            id="meses_execucao"
                                            type="number"
                                            value={data.meses_execucao}
                                            onChange={(e) => {
                                                setData(
                                                    'meses_execucao',
                                                    parseFloat(
                                                        e.target.value,
                                                    ) || 0,
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
                                    <div className="md:col-span-2">
                                        <label
                                            htmlFor="descricao"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Descrição
                                            </span>
                                        </label>
                                        <textarea
                                            id="descricao"
                                            value={data.descricao || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'descricao',
                                                    e.target.value,
                                                )
                                            }
                                            className="textarea textarea-bordered w-full"
                                            rows={4}
                                            maxLength={2000}
                                            placeholder="Descreva os objetivos, metodologia e resultados esperados do projeto..."
                                        />
                                        <div className="text-base-content/70 mt-1 text-xs">
                                            {(data.descricao || '').length}/2000
                                            caracteres
                                        </div>
                                        {errors.descricao && (
                                            <span className="text-error text-xs">
                                                {errors.descricao}
                                            </span>
                                        )}
                                    </div>

                                    {/* Slack URL */}
                                    <div>
                                        <label
                                            htmlFor="slack_url"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Slack URL
                                            </span>
                                        </label>
                                        <input
                                            id="slack_url"
                                            type="url"
                                            value={data.slack_url || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'slack_url',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
                                            placeholder="https://seuworkspace.slack.com"
                                        />
                                        {errors.slack_url && (
                                            <span className="text-error text-xs">
                                                {errors.slack_url}
                                            </span>
                                        )}
                                    </div>

                                    {/* Discord URL */}
                                    <div>
                                        <label
                                            htmlFor="discord_url"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Discord URL
                                            </span>
                                        </label>
                                        <input
                                            id="discord_url"
                                            type="url"
                                            value={data.discord_url || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'discord_url',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
                                            placeholder="https://discord.gg/seuservidor"
                                        />
                                        {errors.discord_url && (
                                            <span className="text-error text-xs">
                                                {errors.discord_url}
                                            </span>
                                        )}
                                    </div>

                                    {/* Board URL */}
                                    <div>
                                        <label
                                            htmlFor="board_url"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Board URL (Trello, Jira, etc.)
                                            </span>
                                        </label>
                                        <input
                                            id="board_url"
                                            type="url"
                                            value={data.board_url || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'board_url',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
                                            placeholder="https://trello.com/seuprojeto"
                                        />
                                        {errors.board_url && (
                                            <span className="text-error text-xs">
                                                {errors.board_url}
                                            </span>
                                        )}
                                    </div>

                                    {/* Git URL */}
                                    <div>
                                        <label
                                            htmlFor="git_url"
                                            className="label"
                                        >
                                            <span className="label-text text-base-content">
                                                Repositório Git URL
                                            </span>
                                        </label>
                                        <input
                                            id="git_url"
                                            type="url"
                                            value={data.git_url || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'git_url',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
                                            placeholder="https://github.com/seuusuario/seuprojeto"
                                        />
                                        {errors.git_url && (
                                            <span className="text-error text-xs">
                                                {errors.git_url}
                                            </span>
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
                                            Adicione informações personalizadas
                                            ao projeto. Use campos chave-valor
                                            para armazenar dados específicos
                                            como tags, categorias ou metadados.
                                        </p>
                                        <div>
                                            <div className="space-y-4">
                                                {campos.map((campo, index) => (
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
                                                                onChange={(e) =>
                                                                    atualizarCampo(
                                                                        index,
                                                                        'key',
                                                                        e.target
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
                                                                onChange={(e) =>
                                                                    atualizarCampo(
                                                                        index,
                                                                        'value',
                                                                        e.target
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
                                                ))}
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
                                                message={errors.campos_extras}
                                                className="mt-2"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Botão */}
                                <div className="mt-6">
                                    <button
                                        type="submit"
                                        className="btn btn-accent w-full sm:w-auto"
                                        disabled={processing}
                                    >
                                        {processing ? 'Salvando...' : 'Criar'}
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
