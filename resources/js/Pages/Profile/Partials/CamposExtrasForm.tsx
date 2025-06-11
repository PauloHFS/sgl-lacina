import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useToast } from '@/Context/ToastProvider';
import { Transition } from '@headlessui/react';
import { useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

interface CampoExtra {
    key: string;
    value: string;
}

interface CamposExtrasFormProps {
    className?: string;
}

export default function CamposExtrasForm({
    className = '',
}: CamposExtrasFormProps) {
    const user = usePage().props.auth.user;
    const { toast } = useToast();

    // Converter o objeto campos_extras em array de chave-valor para facilitar a edição
    const camposExtrasArray: CampoExtra[] = user.campos_extras
        ? Object.entries(user.campos_extras).map(([key, value]) => ({
              key,
              value: String(value),
          }))
        : [];

    const [campos, setCampos] = useState<CampoExtra[]>(camposExtrasArray);

    const { setData, patch, errors, processing, recentlySuccessful } = useForm({
        campos_extras: user.campos_extras || {},
    });

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

        // Validar se não há chaves duplicadas
        const keys = campos
            .map((campo) => campo.key.trim())
            .filter((key) => key !== '');
        const uniqueKeys = new Set(keys);

        if (keys.length !== uniqueKeys.size) {
            toast(
                'Erro: Existem chaves duplicadas nos campos extras.',
                'error',
            );
            return;
        }

        patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => {
                toast('Campos extras atualizados com sucesso!', 'success');
            },
            onError: () => {
                toast('Erro ao atualizar campos extras.', 'error');
            },
        });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="card-title text-base-content">Campos Extras</h2>
                <p className="text-base-content/70 mt-1 text-sm">
                    Adicione informações personalizadas ao seu perfil. Use
                    campos chave-valor para armazenar dados específicos.
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
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
                                    value={campo.key}
                                    onChange={(e) =>
                                        atualizarCampo(
                                            index,
                                            'key',
                                            e.target.value,
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
                                    value={campo.value}
                                    onChange={(e) =>
                                        atualizarCampo(
                                            index,
                                            'value',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Ex: Sistema de RH"
                                />
                            </div>

                            <div className="md:col-span-1">
                                <button
                                    type="button"
                                    onClick={() => removerCampo(index)}
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
                                            strokeWidth={2}
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                    Remover
                                </button>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="flex flex-col gap-4 sm:flex-row">
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

                    <PrimaryButton disabled={processing}>
                        Salvar Campos Extras
                    </PrimaryButton>
                </div>

                <InputError message={errors.campos_extras} className="mt-2" />

                <Transition
                    show={recentlySuccessful}
                    enter="transition ease-in-out"
                    enterFrom="opacity-0"
                    leave="transition ease-in-out"
                    leaveTo="opacity-0"
                >
                    <p className="text-success text-sm">
                        Campos extras salvos.
                    </p>
                </Transition>
            </form>

            {/* Seção informativa sobre exemplos de uso */}
            {/* <div className="bg-base-200 mt-8 rounded-lg p-4">
                <h3 className="text-base-content mb-2 font-semibold">
                    Exemplos de campos extras:
                </h3>
                <div className="text-base-content/70 space-y-1 text-sm">
                    <p>
                        <span className="font-medium">Tamanho da Camisa:</span>{' '}
                        M
                    </p>
                </div>
            </div> */}
        </section>
    );
}
