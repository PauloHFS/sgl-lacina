import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps as InertiaPageProps } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import React, { useState } from 'react';

interface Projeto {
    id: string;
    nome: string;
    descricao: string | null;
    data_inicio: string;
    data_termino: string | null;
    cliente: string;
    slack_url: string | null;
    discord_url: string | null;
    board_url: string | null;
    git_url: string | null;
    tipo: string;
}

interface ShowPageProps extends InertiaPageProps {
    projeto: Projeto;
    tiposVinculo: string[];
    funcoes: string[];
}

type Toast = {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info';
};

export default function Show({
    projeto,
    tiposVinculo,
    funcoes,
}: ShowPageProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        projeto_id: projeto.id,
        data_inicio: '',
        carga_horaria_semanal: '',
        tipo_vinculo: '',
        funcao: '',
    });

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post(route('vinculo.create'), {
            onSuccess: () => {
                reset();
                addToast('Vínculo criado com sucesso!', 'success');
            },
            onError: () => {
                addToast('Erro ao criar vínculo.', 'error');
            },
        });
    };

    // TODO migrar isso para o root do REACT e acessar via hooks
    const [toasts, setToasts] = useState<Toast[]>([]);

    const addToast = (message: string, type: Toast['type'] = 'info') => {
        const id = Date.now();
        setToasts((prev) => [...prev, { id, message, type }]);
        setTimeout(() => {
            setToasts((prev) => prev.filter((t) => t.id !== id));
        }, 3000);
    };

    return (
        <AuthenticatedLayout>
            <Head title={projeto.nome} />

            <div className="toast toast-top toast-end z-[100] flex flex-col gap-2 p-4">
                {toasts.map((t) => (
                    <div key={t.id} className={`alert alert-${t.type}`}>
                        <span>{t.message}</span>
                    </div>
                ))}
            </div>

            <section className="py-12">
                <div className="mx-auto max-w-4xl px-4">
                    <div className="card bg-base-100 border-base-200 border shadow-2xl">
                        <div className="card-body">
                            <div className="mb-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h2 className="card-title mb-2 text-3xl font-bold">
                                        {projeto.nome}
                                    </h2>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="badge badge-primary badge-lg">
                                            {projeto.tipo}
                                        </span>
                                        <span className="badge badge-outline badge-lg">
                                            Cliente: {projeto.cliente}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex flex-col gap-2 text-right">
                                    <span className="text-base-content/70 text-sm">
                                        Início:{' '}
                                        <span className="font-semibold">
                                            {new Date(
                                                projeto.data_inicio,
                                            ).toLocaleDateString()}
                                        </span>
                                    </span>
                                    {projeto.data_termino && (
                                        <span className="text-base-content/70 text-sm">
                                            Término:{' '}
                                            <span className="font-semibold">
                                                {new Date(
                                                    projeto.data_termino,
                                                ).toLocaleDateString()}
                                            </span>
                                        </span>
                                    )}
                                </div>
                            </div>
                            {projeto.descricao && (
                                <div className="mb-6">
                                    <h3 className="mb-1 text-lg font-semibold">
                                        Descrição
                                    </h3>
                                    <p className="text-base-content/90 whitespace-pre-line">
                                        {projeto.descricao}
                                    </p>
                                </div>
                            )}
                            <div className="mb-6">
                                <h3 className="mb-2 text-lg font-semibold">
                                    Links Úteis
                                </h3>
                                <div className="flex flex-wrap gap-3">
                                    {projeto.slack_url && (
                                        <a
                                            href={projeto.slack_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="btn btn-sm btn-info btn-outline"
                                        >
                                            <svg
                                                className="mr-1 h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M7.5 2a1.5 1.5 0 0 1 1.5 1.5V7a1.5 1.5 0 1 1-3 0V3.5A1.5 1.5 0 0 1 7.5 2Zm0 5.5H3.5a1.5 1.5 0 1 0 0 3H7a1.5 1.5 0 1 0 .5-3Zm9-5.5A1.5 1.5 0 0 1 18.5 3.5V7a1.5 1.5 0 1 1-3 0V3.5A1.5 1.5 0 0 1 16.5 2Zm0 5.5H20.5a1.5 1.5 0 1 1 0 3H17a1.5 1.5 0 1 1-.5-3ZM7.5 16.5A1.5 1.5 0 0 1 9 18v3.5a1.5 1.5 0 1 1-3 0V18a1.5 1.5 0 0 1 1.5-1.5Zm0-5.5H3.5a1.5 1.5 0 1 0 0 3H7a1.5 1.5 0 1 0 .5-3Zm9 5.5A1.5 1.5 0 0 1 18.5 18v3.5a1.5 1.5 0 1 1-3 0V18a1.5 1.5 0 0 1 1.5-1.5Zm0-5.5H20.5a1.5 1.5 0 1 1 0 3H17a1.5 1.5 0 1 1-.5-3Z"
                                                />
                                            </svg>
                                            Slack
                                        </a>
                                    )}
                                    {projeto.discord_url && (
                                        <a
                                            href={projeto.discord_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="btn btn-sm btn-accent btn-outline"
                                        >
                                            <svg
                                                className="mr-1 h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M20.317 4.369A19.791 19.791 0 0 0 16.885 3.1a.074.074 0 0 0-.079.037c-.34.607-.719 1.398-.984 2.025a18.524 18.524 0 0 0-5.59 0 12.51 12.51 0 0 0-.997-2.025.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.684 4.369a.07.07 0 0 0-.032.027C1.533 7.59.366 10.68.093 13.726a.08.08 0 0 0 .028.067c2.426 1.78 4.78 2.86 7.09 3.563a.077.077 0 0 0 .084-.027c.547-.75 1.035-1.542 1.44-2.377a.076.076 0 0 0-.041-.104c-.779-.297-1.517-.66-2.222-1.08a.077.077 0 0 1-.008-.127c.15-.113.3-.23.442-.347a.074.074 0 0 1 .077-.01c4.664 2.13 9.72 0 9.72 0a.075.075 0 0 1 .078.009c.143.117.292.234.443.347a.077.077 0 0 1-.007.127c-.705.42-1.444.783-2.223 1.08a.076.076 0 0 0-.04.105c.42.835.908 1.627 1.44 2.377a.076.076 0 0 0 .084.027c2.31-.703 4.664-1.783 7.09-3.563a.077.077 0 0 0 .028-.067c-.3-3.046-1.467-6.136-3.559-9.33a.07.07 0 0 0-.032-.027ZM8.02 14.331c-.696 0-1.272-.64-1.272-1.426 0-.787.563-1.427 1.272-1.427.717 0 1.281.64 1.273 1.427 0 .786-.563 1.426-1.273 1.426Zm7.96 0c-.696 0-1.272-.64-1.272-1.426 0-.787.563-1.427 1.272-1.427.717 0 1.281.64 1.273 1.427 0 .786-.556 1.426-1.273 1.426Z"
                                                />
                                            </svg>
                                            Discord
                                        </a>
                                    )}
                                    {projeto.board_url && (
                                        <a
                                            href={projeto.board_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="btn btn-sm btn-secondary btn-outline"
                                        >
                                            <svg
                                                className="mr-1 h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M4 4h16v16H4V4Zm2 2v12h12V6H6Zm2 2h2v8H8V8Zm4 0h4v2h-4V8Zm0 4h4v4h-4v-4Z"
                                                />
                                            </svg>
                                            Board/Kanban
                                        </a>
                                    )}
                                    {projeto.git_url && (
                                        <a
                                            href={projeto.git_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="btn btn-sm btn-neutral btn-outline"
                                        >
                                            <svg
                                                className="mr-1 h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.483 0-.237-.009-.868-.014-1.703-2.782.604-3.369-1.342-3.369-1.342-.454-1.154-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.004.07 1.532 1.032 1.532 1.032.892 1.528 2.341 1.087 2.91.832.092-.647.35-1.087.636-1.338-2.22-.253-4.555-1.112-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.025A9.564 9.564 0 0 1 12 6.844c.85.004 1.705.115 2.504.337 1.909-1.295 2.748-1.025 2.748-1.025.546 1.378.202 2.397.1 2.65.64.7 1.028 1.595 1.028 2.688 0 3.848-2.338 4.695-4.566 4.944.36.31.68.921.68 1.857 0 1.34-.012 2.421-.012 2.751 0 .268.18.579.688.481C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10Z"
                                                />
                                            </svg>
                                            Repositório Git
                                        </a>
                                    )}
                                    {!projeto.slack_url &&
                                        !projeto.discord_url &&
                                        !projeto.board_url &&
                                        !projeto.git_url && (
                                            <span className="text-base-content/60">
                                                Nenhum link disponível.
                                            </span>
                                        )}
                                </div>
                            </div>
                            <div className="flex justify-end">
                                <label
                                    htmlFor="solicitar-vinculo-drawer"
                                    className="btn btn-primary btn-lg"
                                >
                                    <svg
                                        className="mr-2 h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            fill="currentColor"
                                            d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2Z"
                                        />
                                    </svg>
                                    Solicitar Vínculo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div className="drawer drawer-end">
                <input
                    id="solicitar-vinculo-drawer"
                    type="checkbox"
                    className="drawer-toggle"
                />
                <div className="drawer-content">{/* Page content here */}</div>
                <div className="drawer-side">
                    <label
                        htmlFor="solicitar-vinculo-drawer"
                        aria-label="close sidebar"
                        className="drawer-overlay"
                    ></label>
                    <div className="menu bg-base-200 text-base-content min-h-full w-80 p-4">
                        <h3 className="mb-4 text-xl font-bold">
                            Solicitar Vínculo ao Projeto
                        </h3>
                        <form onSubmit={submit}>
                            <div className="form-control mb-4">
                                <label className="label">
                                    <span className="label-text">
                                        Data de Início
                                    </span>
                                </label>
                                <input
                                    type="date"
                                    className="input input-bordered w-full"
                                    value={data.data_inicio}
                                    onChange={(e) =>
                                        setData('data_inicio', e.target.value)
                                    }
                                    required
                                />
                                {errors.data_inicio && (
                                    <p className="text-error mt-1 text-xs">
                                        {errors.data_inicio}
                                    </p>
                                )}
                            </div>

                            <div className="form-control mb-4">
                                <label className="label">
                                    <span className="label-text">
                                        Carga Horária Semanal (horas)
                                    </span>
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    max="40"
                                    className="input input-bordered w-full"
                                    value={data.carga_horaria_semanal}
                                    onChange={(e) =>
                                        setData(
                                            'carga_horaria_semanal',
                                            e.target.value,
                                        )
                                    }
                                    required
                                />
                                {errors.carga_horaria_semanal && (
                                    <p className="text-error mt-1 text-xs">
                                        {errors.carga_horaria_semanal}
                                    </p>
                                )}
                            </div>

                            <div className="form-control mb-4">
                                <label className="label">
                                    <span className="label-text">
                                        Tipo de Vínculo
                                    </span>
                                </label>
                                <select
                                    className="select select-bordered w-full"
                                    value={data.tipo_vinculo}
                                    onChange={(e) =>
                                        setData('tipo_vinculo', e.target.value)
                                    }
                                    required
                                >
                                    <option value="" disabled>
                                        Selecione
                                    </option>
                                    {tiposVinculo.map((tipo) => (
                                        <option key={tipo} value={tipo}>
                                            {tipo}
                                        </option>
                                    ))}
                                </select>
                                {errors.tipo_vinculo && (
                                    <p className="text-error mt-1 text-xs">
                                        {errors.tipo_vinculo}
                                    </p>
                                )}
                            </div>

                            <div className="form-control mb-4">
                                <label className="label">
                                    <span className="label-text">Função</span>
                                </label>
                                <select
                                    className="select select-bordered w-full"
                                    value={data.funcao}
                                    onChange={(e) =>
                                        setData('funcao', e.target.value)
                                    }
                                    required
                                >
                                    <option value="" disabled>
                                        Selecione
                                    </option>
                                    {funcoes.map((funcao) => (
                                        <option key={funcao} value={funcao}>
                                            {funcao}
                                        </option>
                                    ))}
                                </select>
                                {errors.funcao && (
                                    <p className="text-error mt-1 text-xs">
                                        {errors.funcao}
                                    </p>
                                )}
                            </div>

                            <div className="modal-action mt-6">
                                <button
                                    type="submit"
                                    className="btn btn-primary"
                                    disabled={processing}
                                >
                                    {processing
                                        ? 'Enviando...'
                                        : 'Enviar Solicitação'}
                                </button>
                                <label
                                    htmlFor="solicitar-vinculo-drawer"
                                    className="btn"
                                >
                                    Cancelar
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
