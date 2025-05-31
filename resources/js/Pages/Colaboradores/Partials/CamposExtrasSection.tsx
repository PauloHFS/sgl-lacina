import React, { useEffect, useState } from 'react';

interface CampoExtra {
    key: string;
    value: string;
}

interface CamposExtrasSectionProps {
    campos_extras?: Record<string, string>;
    onCamposChange: (campos: Record<string, string>) => void;
    errors?: Record<string, string>;
    processing?: boolean;
    canEdit?: boolean;
}

export const CamposExtrasSection: React.FC<CamposExtrasSectionProps> = ({
    campos_extras = {},
    onCamposChange,
    errors = {},
    processing = false,
    canEdit = true,
}) => {
    // Converter o objeto campos_extras em array para facilitar a edição
    const camposExtrasArray: CampoExtra[] = Object.entries(campos_extras).map(
        ([key, value]) => ({
            key,
            value: String(value),
        }),
    );

    const [campos, setCampos] = useState<CampoExtra[]>(
        camposExtrasArray.length > 0
            ? camposExtrasArray
            : [{ key: '', value: '' }],
    );

    useEffect(() => {
        // Atualizar campos quando props mudarem
        const newCamposArray = Object.entries(campos_extras).map(
            ([key, value]) => ({
                key,
                value: String(value),
            }),
        );

        if (newCamposArray.length === 0) {
            setCampos([{ key: '', value: '' }]);
        } else {
            setCampos(newCamposArray);
        }
    }, [campos_extras]);

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

        onCamposChange(camposObj);
    };

    return (
        <div className="md:col-span-2">
            <div className="mb-4">
                <h3 className="label-text text-lg font-semibold">
                    Campos Extras
                </h3>
                <p className="text-base-content/70 text-sm">
                    Informações adicionais personalizadas do colaborador
                </p>
            </div>

            <div className="space-y-4">
                {campos.map((campo, index) => (
                    <div
                        key={index}
                        className="border-base-300 grid grid-cols-1 items-end gap-4 rounded-lg border p-4 md:grid-cols-5"
                    >
                        <div className="md:col-span-2">
                            <label className="label" htmlFor={`key-${index}`}>
                                <span className="label-text font-semibold">
                                    Chave:
                                </span>
                            </label>
                            <input
                                id={`key-${index}`}
                                type="text"
                                className={`input input-bordered w-full ${
                                    errors[`campos_extras.${campo.key}`]
                                        ? 'input-error'
                                        : ''
                                }`}
                                value={campo.key}
                                onChange={(e) =>
                                    atualizarCampo(index, 'key', e.target.value)
                                }
                                disabled={processing || !canEdit}
                                placeholder="Ex: projeto_favorito"
                            />
                        </div>

                        <div className="md:col-span-2">
                            <label className="label" htmlFor={`value-${index}`}>
                                <span className="label-text font-semibold">
                                    Valor:
                                </span>
                            </label>
                            <input
                                id={`value-${index}`}
                                type="text"
                                className={`input input-bordered w-full ${
                                    errors[`campos_extras.${campo.key}`]
                                        ? 'input-error'
                                        : ''
                                }`}
                                value={campo.value}
                                onChange={(e) =>
                                    atualizarCampo(
                                        index,
                                        'value',
                                        e.target.value,
                                    )
                                }
                                disabled={processing || !canEdit}
                                placeholder="Ex: Sistema de RH"
                            />
                        </div>

                        <div className="md:col-span-1">
                            {campos.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() => removerCampo(index)}
                                    className="btn btn-error btn-sm w-full"
                                    title="Remover campo"
                                    disabled={processing || !canEdit}
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
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {canEdit && (
                <div className="mt-4">
                    <button
                        type="button"
                        onClick={adicionarCampo}
                        className="btn btn-outline btn-primary"
                        disabled={processing}
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
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                            />
                        </svg>
                        Adicionar Campo
                    </button>
                </div>
            )}

            {errors['campos_extras'] && (
                <p className="text-error mt-1 text-xs">
                    {errors['campos_extras']}
                </p>
            )}
        </div>
    );
};

CamposExtrasSection.displayName = 'CamposExtrasSection';
