import { useToast } from '@/Context/ToastProvider';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface Baia {
    id: string;
    nome: string;
    descricao?: string;
    ativa: boolean;
    created_at: string;
    updated_at: string;
}

interface Sala {
    id: string;
    nome: string;
    descricao?: string;
    ativa: boolean;
    baias: Baia[];
    created_at: string;
    updated_at: string;
}

interface EditProps {
    sala: Sala;
}

interface BaiaFormData {
    id?: string;
    nome: string;
    descricao?: string;
    ativa: boolean;
    _action?: 'create' | 'update' | 'delete';
}

interface FormData {
    nome: string;
    descricao: string;
    ativa: boolean;
    baias: BaiaFormData[];
    baias_deletadas: string[];
}

export default function Edit({ sala }: EditProps) {
    const { toast } = useToast();
    const [baias, setBaias] = useState<BaiaFormData[]>(
        sala.baias.map((baia) => ({
            id: baia.id,
            nome: baia.nome,
            descricao: baia.descricao || '',
            ativa: baia.ativa,
            _action: 'update',
        })),
    );

    const { data, setData, patch, processing, errors } = useForm<FormData>({
        nome: sala.nome,
        descricao: sala.descricao || '',
        ativa: sala.ativa,
        baias: baias,
        baias_deletadas: [],
    });

    // Helper function to get nested errors safely
    const getNestedError = (path: string): string | undefined => {
        return (errors as Record<string, string>)?.[path];
    };

    const adicionarBaia = () => {
        const novaBaia: BaiaFormData = {
            nome: '',
            descricao: '',
            ativa: true,
            _action: 'create',
        };

        const novasBaias = [...baias, novaBaia];
        setBaias(novasBaias);
        setData('baias', novasBaias);
    };

    const removerBaia = (index: number) => {
        const baia = baias[index];

        if (baia.id) {
            // Se a baia já existe no banco, marca para deletar
            const novasBaias = baias.map((b, i) =>
                i === index ? { ...b, _action: 'delete' as const } : b,
            );
            setBaias(novasBaias);
            setData('baias', novasBaias);
        } else {
            // Se é uma nova baia, remove da lista
            const novasBaias = baias.filter((_, i) => i !== index);
            setBaias(novasBaias);
            setData('baias', novasBaias);
        }
    };

    const atualizarBaia = (
        index: number,
        field: keyof BaiaFormData,
        value: string | boolean,
    ) => {
        const novasBaias = baias.map((baia, i) => {
            if (i === index) {
                const baiaAtualizada = { ...baia, [field]: value };
                // Se não é uma nova baia e não está marcada para deletar, marca como update
                if (baia.id && baia._action !== 'delete') {
                    baiaAtualizada._action = 'update';
                }
                return baiaAtualizada;
            }
            return baia;
        });
        setBaias(novasBaias);
        setData('baias', novasBaias);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        // Filtrar baias que estão marcadas para deletar para não incluir nos dados do formulário
        const baiasParaEnvio = baias.filter(
            (baia) => baia._action !== 'delete',
        );
        const baiasParaDeletar = baias.filter(
            (baia) => baia._action === 'delete' && baia.id,
        );

        const dadosParaEnvio = {
            nome: data.nome,
            descricao: data.descricao,
            ativa: data.ativa,
            baias: baiasParaEnvio.map((baia) => ({
                id: baia.id,
                nome: baia.nome,
                descricao: baia.descricao || '',
                ativa: baia.ativa,
            })),
            baias_deletadas: baiasParaDeletar
                .map((baia) => baia.id)
                .filter(Boolean) as string[],
        };

        patch(route('salas.update', sala.id), {
            data: dadosParaEnvio,
            onSuccess: () => {
                toast('Sala atualizada com sucesso!', 'success');
            },
            onError: (formErrors) => {
                console.error('Erro ao atualizar sala:', formErrors);
                toast('Erro ao atualizar sala. Verifique os campos.', 'error');
            },
        });
    };

    const baiasVisiveis = baias.filter((baia) => baia._action !== 'delete');

    return (
        <Authenticated>
            <Head title={`Editar Sala: ${sala.nome}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 shadow sm:rounded-lg">
                        <div className="p-6 sm:p-8">
                            <div className="mb-6">
                                <h1 className="text-base-content text-2xl font-bold">
                                    Editar Sala
                                </h1>
                                <p className="text-base-content/70 mt-1 text-sm">
                                    Atualize as informações da sala e suas baias
                                    conforme necessário.
                                </p>
                            </div>

                            <form onSubmit={submit} className="space-y-8">
                                {/* Dados da Sala */}
                                <div className="space-y-6">
                                    <h2 className="text-base-content border-base-300 border-b pb-2 text-xl font-semibold">
                                        Informações da Sala
                                    </h2>

                                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        {/* Nome da Sala */}
                                        <div className="form-control">
                                            <label className="label">
                                                <span className="label-text font-medium">
                                                    Nome da Sala *
                                                </span>
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Digite o nome da sala"
                                                className={`input input-bordered w-full ${
                                                    errors.nome
                                                        ? 'input-error'
                                                        : ''
                                                }`}
                                                value={data.nome}
                                                onChange={(e) =>
                                                    setData(
                                                        'nome',
                                                        e.target.value,
                                                    )
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

                                        {/* Status da Sala */}
                                        <div className="form-control">
                                            <label className="label">
                                                <span className="label-text font-medium">
                                                    Status
                                                </span>
                                            </label>
                                            <select
                                                className={`select select-bordered w-full ${
                                                    errors.ativa
                                                        ? 'select-error'
                                                        : ''
                                                }`}
                                                value={
                                                    data.ativa
                                                        ? 'true'
                                                        : 'false'
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'ativa',
                                                        e.target.value ===
                                                            'true',
                                                    )
                                                }
                                            >
                                                <option value="true">
                                                    Ativa
                                                </option>
                                                <option value="false">
                                                    Inativa
                                                </option>
                                            </select>
                                            {errors.ativa && (
                                                <div className="label">
                                                    <span className="label-text-alt text-error">
                                                        {errors.ativa}
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {/* Descrição da Sala */}
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text font-medium">
                                                Descrição
                                            </span>
                                        </label>
                                        <textarea
                                            placeholder="Descreva a sala..."
                                            className={`textarea textarea-bordered h-24 w-full ${
                                                errors.descricao
                                                    ? 'textarea-error'
                                                    : ''
                                            }`}
                                            value={data.descricao || ''}
                                            onChange={(e) =>
                                                setData(
                                                    'descricao',
                                                    e.target.value,
                                                )
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
                                </div>

                                {/* Baias da Sala */}
                                <div className="space-y-6">
                                    <div className="border-base-300 flex items-center justify-between border-b pb-2">
                                        <h2 className="text-base-content text-xl font-semibold">
                                            Baias da Sala
                                        </h2>
                                        <button
                                            type="button"
                                            onClick={adicionarBaia}
                                            className="btn btn-outline btn-primary btn-sm"
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
                                            Adicionar Baia
                                        </button>
                                    </div>

                                    {baiasVisiveis.length === 0 ? (
                                        <div className="text-base-content/60 py-8 text-center">
                                            <p>
                                                Nenhuma baia cadastrada. Clique
                                                em "Adicionar Baia" para
                                                começar.
                                            </p>
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            {baiasVisiveis.map(
                                                (baia, index) => {
                                                    const realIndex =
                                                        baias.findIndex(
                                                            (b) => b === baia,
                                                        );
                                                    return (
                                                        <div
                                                            key={
                                                                baia.id ||
                                                                `new-${index}`
                                                            }
                                                            className="border-base-300 bg-base-50 rounded-lg border p-4"
                                                        >
                                                            <div className="mb-4 flex items-start justify-between">
                                                                <h3 className="text-base-content text-lg font-medium">
                                                                    {baia.id
                                                                        ? 'Editar Baia'
                                                                        : 'Nova Baia'}
                                                                </h3>
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        removerBaia(
                                                                            realIndex,
                                                                        )
                                                                    }
                                                                    className="btn btn-error btn-sm"
                                                                    title="Remover baia"
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
                                                                </button>
                                                            </div>

                                                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                                {/* Nome da Baia */}
                                                                <div className="form-control">
                                                                    <label className="label">
                                                                        <span className="label-text font-medium">
                                                                            Nome
                                                                            da
                                                                            Baia
                                                                            *
                                                                        </span>
                                                                    </label>
                                                                    <input
                                                                        type="text"
                                                                        placeholder="Ex: Baia 01"
                                                                        className={`input input-bordered w-full ${
                                                                            getNestedError(
                                                                                `baias.${realIndex}.nome`,
                                                                            )
                                                                                ? 'input-error'
                                                                                : ''
                                                                        }`}
                                                                        value={
                                                                            baia.nome
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            atualizarBaia(
                                                                                realIndex,
                                                                                'nome',
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                        required
                                                                    />
                                                                    {getNestedError(
                                                                        `baias.${realIndex}.nome`,
                                                                    ) && (
                                                                        <div className="label">
                                                                            <span className="label-text-alt text-error">
                                                                                {getNestedError(
                                                                                    `baias.${realIndex}.nome`,
                                                                                )}
                                                                            </span>
                                                                        </div>
                                                                    )}
                                                                </div>

                                                                {/* Status da Baia */}
                                                                <div className="form-control">
                                                                    <label className="label">
                                                                        <span className="label-text font-medium">
                                                                            Status
                                                                        </span>
                                                                    </label>
                                                                    <select
                                                                        className={`select select-bordered w-full ${
                                                                            getNestedError(
                                                                                `baias.${realIndex}.ativa`,
                                                                            )
                                                                                ? 'select-error'
                                                                                : ''
                                                                        }`}
                                                                        value={
                                                                            baia.ativa
                                                                                ? 'true'
                                                                                : 'false'
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            atualizarBaia(
                                                                                realIndex,
                                                                                'ativa',
                                                                                e
                                                                                    .target
                                                                                    .value ===
                                                                                    'true',
                                                                            )
                                                                        }
                                                                    >
                                                                        <option value="true">
                                                                            Ativa
                                                                        </option>
                                                                        <option value="false">
                                                                            Inativa
                                                                        </option>
                                                                    </select>
                                                                    {getNestedError(
                                                                        `baias.${realIndex}.ativa`,
                                                                    ) && (
                                                                        <div className="label">
                                                                            <span className="label-text-alt text-error">
                                                                                {getNestedError(
                                                                                    `baias.${realIndex}.ativa`,
                                                                                )}
                                                                            </span>
                                                                        </div>
                                                                    )}
                                                                </div>

                                                                {/* Descrição da Baia */}
                                                                <div className="form-control">
                                                                    <label className="label">
                                                                        <span className="label-text font-medium">
                                                                            Descrição
                                                                        </span>
                                                                    </label>
                                                                    <input
                                                                        type="text"
                                                                        placeholder="Descrição da baia"
                                                                        className={`input input-bordered w-full ${
                                                                            getNestedError(
                                                                                `baias.${realIndex}.descricao`,
                                                                            )
                                                                                ? 'input-error'
                                                                                : ''
                                                                        }`}
                                                                        value={
                                                                            baia.descricao ||
                                                                            ''
                                                                        }
                                                                        onChange={(
                                                                            e,
                                                                        ) =>
                                                                            atualizarBaia(
                                                                                realIndex,
                                                                                'descricao',
                                                                                e
                                                                                    .target
                                                                                    .value,
                                                                            )
                                                                        }
                                                                    />
                                                                    {getNestedError(
                                                                        `baias.${realIndex}.descricao`,
                                                                    ) && (
                                                                        <div className="label">
                                                                            <span className="label-text-alt text-error">
                                                                                {getNestedError(
                                                                                    `baias.${realIndex}.descricao`,
                                                                                )}
                                                                            </span>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    );
                                                },
                                            )}
                                        </div>
                                    )}
                                </div>

                                {/* Botões de Ação */}
                                <div className="border-base-300 flex flex-col justify-end gap-4 border-t pt-6 sm:flex-row">
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
                                            'Atualizar Sala'
                                        )}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
