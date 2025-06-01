import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface Projeto {
    id: string;
    nome: string;
    descricao?: string;
    data_inicio: string;
    data_termino?: string;
    cliente: string;
    slack_url?: string;
    discord_url?: string;
    board_url?: string;
    git_url?: string;
    tipo: string;
}

interface EditPageProps {
    projeto: Projeto;
    tiposProjeto: string[];
}

export default function Edit({ projeto, tiposProjeto }: EditPageProps) {
    const { data, setData, patch, processing, errors } = useForm({
        nome: projeto.nome,
        descricao: projeto.descricao || '',
        data_inicio: projeto.data_inicio
            ? projeto.data_inicio.substring(0, 10)
            : '',
        data_termino: projeto.data_termino
            ? projeto.data_termino.substring(0, 10)
            : '',
        cliente: projeto.cliente,
        slack_url: projeto.slack_url || '',
        discord_url: projeto.discord_url || '',
        board_url: projeto.board_url || '',
        git_url: projeto.git_url || '',
        tipo: projeto.tipo,
    });

    const { toast } = useToast();

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('projetos.update', projeto.id), {
            onSuccess: () => {
                toast('Projeto atualizado com sucesso.', 'success');
            },
            onError: (errors) => {
                console.error(errors);
                toast('Não foi possível atualizar o projeto.', 'error');
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
                                                Cliente *
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

                                    {/* Tipo do Projeto */}
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text font-medium">
                                                Tipo do Projeto *
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
                                                setData('tipo', e.target.value)
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

                                    {/* Data de Início */}
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text font-medium">
                                                Data de Início *
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
                                            value={data.data_termino}
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
                                        value={data.descricao}
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
                                                value={data.slack_url}
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
                                                value={data.discord_url}
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
                                                value={data.board_url}
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
                                                value={data.git_url}
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
