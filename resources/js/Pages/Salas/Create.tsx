import { useToast } from '@/Context/ToastProvider';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface BaiaFormData {
    nome: string;
    descricao?: string;
    ativa: boolean;
}

interface FormData {
    nome: string;
    descricao: string;
    ativa: boolean;
    baias: BaiaFormData[];
}

export default function Create() {
    const { toast } = useToast();
    const [baias, setBaias] = useState<BaiaFormData[]>([]);

    const { data, setData, processing, errors } = useForm<FormData>({
        nome: '',
        descricao: '',
        ativa: true,
        baias: [],
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
        };

        const novasBaias = [...baias, novaBaia];
        setBaias(novasBaias);
        setData('baias', novasBaias);
    };

    const removerBaia = (index: number) => {
        const novasBaias = baias.filter((_, i) => i !== index);
        setBaias(novasBaias);
        setData('baias', novasBaias);
    };

    const atualizarBaia = (
        index: number,
        field: keyof BaiaFormData,
        value: string | boolean,
    ) => {
        const novasBaias = baias.map((baia, i) =>
            i === index ? { ...baia, [field]: value } : baia,
        );
        setBaias(novasBaias);
        setData('baias', novasBaias);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        // Preparar dados para envio com as baias atuais
        const formDataToSend = {
            nome: data.nome,
            descricao: data.descricao,
            ativa: data.ativa,
            baias: baias.map((baia) => ({
                nome: baia.nome,
                descricao: baia.descricao || '',
                ativa: baia.ativa,
            })),
        };

        // Usar router.post para enviar dados específicos
        router.post(route('salas.store'), formDataToSend, {
            onSuccess: () => {
                toast('Sala criada com sucesso!', 'success');
            },
            onError: (formErrors: Record<string, string>) => {
                console.error('Erro ao criar sala:', formErrors);
                toast('Erro ao criar sala. Verifique os campos.', 'error');
            },
        });
    };

    return (
        <Authenticated>
            <Head title="Criar Nova Sala" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-base-100 shadow sm:rounded-lg">
                        <div className="p-6 sm:p-8">
                            <div className="mb-6">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h1 className="text-base-content text-2xl font-bold">
                                            Criar Nova Sala
                                        </h1>
                                        <p className="text-base-content/70 mt-1 text-sm">
                                            Cadastre uma nova sala e suas baias.
                                        </p>
                                    </div>
                                    <Link
                                        href={route('salas.index')}
                                        className="btn btn-ghost"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                            />
                                        </svg>
                                        Voltar
                                    </Link>
                                </div>
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
                                            placeholder="Digite uma descrição para a sala (opcional)"
                                            className={`textarea textarea-bordered w-full ${
                                                errors.descricao
                                                    ? 'textarea-error'
                                                    : ''
                                            }`}
                                            rows={3}
                                            value={data.descricao}
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
                                    <div className="flex items-center justify-between">
                                        <h2 className="text-base-content border-base-300 border-b pb-2 text-xl font-semibold">
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

                                    {baias.length === 0 ? (
                                        <div className="text-base-content/60 py-8 text-center">
                                            <div className="flex flex-col items-center gap-4">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    className="text-base-content/30 h-16 w-16"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                                    />
                                                </svg>
                                                <div>
                                                    <p className="font-medium">
                                                        Nenhuma baia cadastrada
                                                    </p>
                                                    <p className="mt-1 text-sm">
                                                        Clique em "Adicionar
                                                        Baia" para começar a
                                                        cadastrar as baias desta
                                                        sala.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            {baias.map((baia, index) => (
                                                <div
                                                    key={`baia-${index}`}
                                                    className="border-base-300 bg-base-50 rounded-lg border p-4"
                                                >
                                                    <div className="mb-4 flex items-start justify-between">
                                                        <h3 className="text-base-content text-lg font-medium">
                                                            Nova Baia #
                                                            {index + 1}
                                                        </h3>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                removerBaia(
                                                                    index,
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
                                                                    Nome da Baia
                                                                    *
                                                                </span>
                                                            </label>
                                                            <input
                                                                type="text"
                                                                placeholder="Ex: Baia 01"
                                                                className={`input input-bordered w-full ${
                                                                    getNestedError(
                                                                        `baias.${index}.nome`,
                                                                    )
                                                                        ? 'input-error'
                                                                        : ''
                                                                }`}
                                                                value={
                                                                    baia.nome
                                                                }
                                                                onChange={(e) =>
                                                                    atualizarBaia(
                                                                        index,
                                                                        'nome',
                                                                        e.target
                                                                            .value,
                                                                    )
                                                                }
                                                                required
                                                            />
                                                            {getNestedError(
                                                                `baias.${index}.nome`,
                                                            ) && (
                                                                <div className="label">
                                                                    <span className="label-text-alt text-error">
                                                                        {getNestedError(
                                                                            `baias.${index}.nome`,
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
                                                                        `baias.${index}.ativa`,
                                                                    )
                                                                        ? 'select-error'
                                                                        : ''
                                                                }`}
                                                                value={
                                                                    baia.ativa
                                                                        ? 'true'
                                                                        : 'false'
                                                                }
                                                                onChange={(e) =>
                                                                    atualizarBaia(
                                                                        index,
                                                                        'ativa',
                                                                        e.target
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
                                                                `baias.${index}.ativa`,
                                                            ) && (
                                                                <div className="label">
                                                                    <span className="label-text-alt text-error">
                                                                        {getNestedError(
                                                                            `baias.${index}.ativa`,
                                                                        )}
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>

                                                        {/* Espaço para terceira coluna ou pode ser usado para expandir outras */}
                                                        <div className="form-control md:col-span-1">
                                                            <label className="label">
                                                                <span className="label-text font-medium">
                                                                    &nbsp;
                                                                </span>
                                                            </label>
                                                            <div className="flex h-12 items-center">
                                                                <div
                                                                    className={`badge ${
                                                                        baia.ativa
                                                                            ? 'badge-success'
                                                                            : 'badge-error'
                                                                    }`}
                                                                >
                                                                    {baia.ativa
                                                                        ? 'Ativa'
                                                                        : 'Inativa'}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {/* Descrição da Baia - Linha completa */}
                                                    <div className="form-control mt-4">
                                                        <label className="label">
                                                            <span className="label-text font-medium">
                                                                Descrição
                                                            </span>
                                                        </label>
                                                        <textarea
                                                            placeholder="Descrição da baia (opcional)"
                                                            className={`textarea textarea-bordered w-full ${
                                                                getNestedError(
                                                                    `baias.${index}.descricao`,
                                                                )
                                                                    ? 'textarea-error'
                                                                    : ''
                                                            }`}
                                                            rows={2}
                                                            value={
                                                                baia.descricao ||
                                                                ''
                                                            }
                                                            onChange={(e) =>
                                                                atualizarBaia(
                                                                    index,
                                                                    'descricao',
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                        />
                                                        {getNestedError(
                                                            `baias.${index}.descricao`,
                                                        ) && (
                                                            <div className="label">
                                                                <span className="label-text-alt text-error">
                                                                    {getNestedError(
                                                                        `baias.${index}.descricao`,
                                                                    )}
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                {/* Botões de Ação */}
                                <div className="border-base-300 flex flex-col justify-end gap-4 border-t pt-6 sm:flex-row">
                                    <Link
                                        href={route('salas.index')}
                                        className="btn btn-ghost"
                                    >
                                        Cancelar
                                    </Link>
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        {processing ? (
                                            <>
                                                <span className="loading loading-spinner loading-sm"></span>
                                                Criando...
                                            </>
                                        ) : (
                                            'Criar Sala'
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
