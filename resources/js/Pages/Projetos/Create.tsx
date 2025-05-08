import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';

interface CreateProjetoProps {
    tiposProjeto: string[];
}

export default function CreateProjeto({ tiposProjeto }: CreateProjetoProps) {
    const { data, setData, post, errors, processing } = useForm({
        nome: '',
        descricao: '',
        data_inicio: '',
        data_termino: '',
        cliente: '',
        slack_url: '',
        discord_url: '',
        board_url: '',
        git_url: '',
        tipo: tiposProjeto.length > 0 ? tiposProjeto[0] : '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('projetos.store'), {
            onError: (formErrors) => {
                console.error('Erro ao cadastrar projeto:', formErrors);
                // You might want to display these errors to the user
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-base-content text-xl leading-tight font-semibold">
                    Cadastrar Novo Projeto
                </h2>
            }
        >
            <Head title="Cadastrar Projeto" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 overflow-hidden shadow-sm sm:rounded-lg">
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
                                                Cliente*
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
                                            required
                                        />
                                        {errors.cliente && (
                                            <span className="text-error text-xs">
                                                {errors.cliente}
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
                                            value={data.data_termino}
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
                                                setData('tipo', e.target.value)
                                            }
                                            className="select select-bordered w-full"
                                            required
                                        >
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
                                            value={data.descricao}
                                            onChange={(e) =>
                                                setData(
                                                    'descricao',
                                                    e.target.value,
                                                )
                                            }
                                            className="textarea textarea-bordered w-full"
                                            rows={3}
                                        ></textarea>
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
                                            value={data.slack_url}
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
                                            value={data.discord_url}
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
                                            value={data.board_url}
                                            onChange={(e) =>
                                                setData(
                                                    'board_url',
                                                    e.target.value,
                                                )
                                            }
                                            className="input input-bordered w-full"
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
                                            value={data.git_url}
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
                                </div>

                                {/* Botão */}
                                <div className="mt-6">
                                    <button
                                        type="submit"
                                        className="btn btn-primary w-full sm:w-auto"
                                        disabled={processing}
                                    >
                                        {processing
                                            ? 'Salvando...'
                                            : 'Salvar Projeto'}
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
