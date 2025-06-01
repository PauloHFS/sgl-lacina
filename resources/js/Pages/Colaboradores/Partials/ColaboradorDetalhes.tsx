import { ESTADOS, GENEROS } from '@/constants';
import React, { useCallback } from 'react';
import { IMaskInput } from 'react-imask';
import { ColaboradorData, ShowPageProps } from '../Show';
import { CamposExtrasSection } from './CamposExtrasSection';
import { InfoItem } from './InfoItem';

// Define types for setData and errors more generically, aligned with Inertia's useForm
interface CustomSetData<TForm extends object> {
    // Changed Record<string, unknown> to object
    (data: TForm): void;
    (data: (previousData: TForm) => TForm): void;
    <K extends keyof TForm>(key: K, value: TForm[K]): void;
}
type FormErrors<TForm> = Partial<Record<keyof TForm, string>>;

interface ColaboradorDetalhesProps {
    colaborador: ColaboradorData;
    isEditing: boolean;
    data: ColaboradorData;
    setData: CustomSetData<ColaboradorData>;
    errors: FormErrors<ColaboradorData>;
    processing: boolean;
    onCancel: () => void;
    onSubmit: () => void;
    bancos: ShowPageProps['bancos']; // Use ShowPageProps
    canEdit: boolean;
}

export const ColaboradorDetalhes: React.FC<ColaboradorDetalhesProps> = ({
    colaborador,
    isEditing,
    data,
    setData,
    errors,
    processing,
    onCancel,
    onSubmit,
    bancos,
    canEdit,
}) => {
    // Memoizar a função onCamposChange para evitar re-renders desnecessários
    const handleCamposChange = useCallback(
        (campos: Record<string, string>) => {
            setData('campos_extras', campos);
        },
        [setData],
    );

    if (!isEditing) {
        return (
            <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                <InfoItem label="Nome Completo" value={colaborador.name} />
                <InfoItem label="Email" value={colaborador.email} />
                <InfoItem label="CPF" value={colaborador.cpf} />
                <InfoItem label="RG" value={colaborador.rg} />
                <InfoItem
                    label="Órgão Emissor RG"
                    value={colaborador.orgao_emissor_rg}
                />
                <InfoItem label="UF RG" value={colaborador.uf_rg} />
                <InfoItem label="Telefone" value={colaborador.telefone} />
                <InfoItem label="Gênero" value={colaborador.genero} />
                <InfoItem
                    label="Data de Nascimento"
                    value={
                        colaborador.data_nascimento
                            ? new Date(
                                  colaborador.data_nascimento,
                              ).toLocaleDateString('pt-BR', { timeZone: 'UTC' })
                            : '-'
                    }
                />
                <InfoItem
                    label="Banco"
                    value={
                        colaborador.banco
                            ? `${colaborador.banco.codigo} - ${colaborador.banco.nome}`
                            : '-'
                    }
                />
                <InfoItem label="Agência" value={colaborador.agencia} />
                <InfoItem
                    label="Conta Bancária"
                    value={colaborador.conta_bancaria}
                />

                <InfoItem label="CEP" value={colaborador.cep} />
                <InfoItem label="Endereço" value={colaborador.endereco} />
                <InfoItem label="Número" value={colaborador.numero} />
                <InfoItem label="Complemento" value={colaborador.complemento} />
                <InfoItem label="Bairro" value={colaborador.bairro} />
                <InfoItem label="Cidade" value={colaborador.cidade} />
                <InfoItem label="UF" value={colaborador.uf} />

                {/* Campos Extras */}
                {colaborador.campos_extras &&
                    Object.keys(colaborador.campos_extras).length > 0 && (
                        <>
                            <div className="md:col-span-2">
                                <h3 className="mb-2 text-lg font-semibold">
                                    Campos Extras
                                </h3>
                            </div>
                            {Object.entries(colaborador.campos_extras).map(
                                ([key, value]) => (
                                    <InfoItem
                                        key={key}
                                        label={key}
                                        value={value || '-'}
                                    />
                                ),
                            )}
                        </>
                    )}

                {/*
                <InfoItem
                    label="Área de Atuação"
                    value={colaborador.area_atuacao}
                />
                 {colaborador.curriculo_lattes_url ? (
                    <InfoItem label="Currículo Lattes">
                        <a
                            href={colaborador.curriculo_lattes_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="link link-primary break-all"
                        >
                            {colaborador.curriculo_lattes_url}
                        </a>
                    </InfoItem>
                ) : (
                    <InfoItem label="Currículo Lattes" value="-" />
                )}
                {colaborador.linkedin_url ? (
                    <InfoItem label="LinkedIn">
                        <a
                            href={colaborador.linkedin_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="link link-primary break-all"
                        >
                            {colaborador.linkedin_url}
                        </a>
                    </InfoItem>
                ) : (
                    <InfoItem label="LinkedIn" value="-" />
                )}
                {colaborador.github_url ? (
                    <InfoItem label="GitHub">
                        <a
                            href={colaborador.github_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="link link-primary break-all"
                        >
                            {colaborador.github_url}
                        </a>
                    </InfoItem>
                ) : (
                    <InfoItem label="GitHub" value="-" />
                )}
                {colaborador.website_url ? (
                    <InfoItem label="Figma">
                        <a
                            href={colaborador.website_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="link link-primary break-all"
                        >
                            {colaborador.website_url}
                        </a>
                    </InfoItem>
                ) : (
                    <InfoItem label="Figma" value="-" />
                )} */}
                {/* <InfoItem label="Tecnologias">
                    <div className="input input-bordered flex h-auto min-h-10 flex-wrap items-center gap-1 py-2 break-words whitespace-normal">
                        {colaborador.tecnologias
                            ? colaborador.tecnologias
                                  .split(',')
                                  .map((tech: string, idx: number) => (
                                      <span
                                          key={idx}
                                          className="badge badge-info badge-outline"
                                      >
                                          {tech.trim()}
                                      </span>
                                  ))
                            : '-'}
                    </div>
                </InfoItem> */}
            </div>
        );
    }

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                onSubmit();
            }}
            className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2"
        >
            {/* Nome */}
            <div>
                <label className="label" htmlFor="name">
                    <span className="label-text font-semibold">
                        Nome Completo:
                    </span>
                </label>
                <input
                    id="name"
                    type="text"
                    className={`input input-bordered w-full ${errors.name ? 'input-error' : ''}`}
                    value={data.name || ''}
                    onChange={(e) => setData('name', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.name && (
                    <p className="text-error mt-1 text-xs">{errors.name}</p>
                )}
            </div>

            {/* Email */}
            {/* <div>
                <label className="label" htmlFor="email">
                    <span className="label-text font-semibold">Email:</span>
                </label>
                <input
                    id="email"
                    type="email"
                    className={`input input-bordered w-full ${errors.email ? 'input-error' : ''}`}
                    value={data.email || ''}
                    onChange={(e) => setData('email', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.email && (
                    <p className="text-error mt-1 text-xs">{errors.email}</p>
                )}
            </div> */}

            <InfoItem label="Email" value={colaborador.email} />

            {/* CPF */}
            <div>
                <label className="label" htmlFor="cpf">
                    <span className="label-text font-semibold">CPF:</span>
                </label>
                <input
                    id="cpf"
                    type="text"
                    className={`input input-bordered w-full ${errors.cpf ? 'input-error' : ''}`}
                    value={data.cpf || ''}
                    onChange={(e) => setData('cpf', e.target.value)}
                    disabled={processing || !canEdit}
                    // TODO: Add mask if desired
                />
                {errors.cpf && (
                    <p className="text-error mt-1 text-xs">{errors.cpf}</p>
                )}
            </div>

            {/* RG */}
            <div>
                <label className="label" htmlFor="rg">
                    <span className="label-text font-semibold">RG:</span>
                </label>
                <input
                    id="rg"
                    type="text"
                    className={`input input-bordered w-full ${errors.rg ? 'input-error' : ''}`}
                    value={data.rg || ''}
                    onChange={(e) => setData('rg', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.rg && (
                    <p className="text-error mt-1 text-xs">{errors.rg}</p>
                )}
            </div>

            {/* Órgão Emissor RG */}
            <div>
                <label className="label" htmlFor="orgao_emissor_rg">
                    <span className="label-text font-semibold">
                        Órgão Emissor RG:
                    </span>
                </label>
                <input
                    id="orgao_emissor_rg"
                    type="text"
                    className={`input input-bordered w-full ${errors.orgao_emissor_rg ? 'input-error' : ''}`}
                    value={data.orgao_emissor_rg || ''}
                    onChange={(e) =>
                        setData('orgao_emissor_rg', e.target.value)
                    }
                    disabled={processing || !canEdit}
                />
                {errors.orgao_emissor_rg && (
                    <p className="text-error mt-1 text-xs">
                        {errors.orgao_emissor_rg}
                    </p>
                )}
            </div>

            {/* UF RG */}
            <div>
                <label className="label" htmlFor="uf_rg">
                    <span className="label-text font-semibold">UF do RG:</span>
                </label>
                <select
                    id="uf_rg"
                    className={`select select-bordered w-full ${errors.uf_rg ? 'select-error' : ''}`}
                    value={data.uf_rg || ''}
                    onChange={(e) => setData('uf_rg', e.target.value)}
                    disabled={processing || !canEdit}
                >
                    <option value="">Selecione</option>
                    {ESTADOS.map((uf) => (
                        <option key={uf.sigla} value={uf.sigla}>
                            {uf.nome}
                        </option>
                    ))}
                </select>
                {errors.uf_rg && (
                    <p className="text-error mt-1 text-xs">{errors.uf_rg}</p>
                )}
            </div>

            {/* Telefone */}
            <div>
                <label className="label" htmlFor="telefone">
                    <span className="label-text font-semibold">Telefone:</span>
                </label>
                <input
                    id="telefone"
                    type="tel"
                    className={`input input-bordered w-full ${errors.telefone ? 'input-error' : ''}`}
                    value={data.telefone || ''}
                    onChange={(e) => setData('telefone', e.target.value)}
                    disabled={processing || !canEdit}
                    // TODO: Add mask if desired
                />
                {errors.telefone && (
                    <p className="text-error mt-1 text-xs">{errors.telefone}</p>
                )}
            </div>

            {/* Gênero */}
            <div>
                <label className="label" htmlFor="genero">
                    <span className="label-text font-semibold">Gênero:</span>
                </label>
                <select
                    id="genero"
                    className={`select select-bordered w-full ${errors.genero ? 'select-error' : ''}`}
                    value={data.genero || ''}
                    onChange={(e) => setData('genero', e.target.value)}
                    disabled={processing || !canEdit}
                >
                    <option value="">Selecione</option>
                    {GENEROS.map((g: { value: string; label: string }) => (
                        <option key={g.value} value={g.value}>
                            {g.label}
                        </option>
                    ))}
                </select>
                {errors.genero && (
                    <p className="text-error mt-1 text-xs">{errors.genero}</p>
                )}
            </div>

            {/* Data de Nascimento */}
            <div>
                <label className="label" htmlFor="data_nascimento">
                    <span className="label-text font-semibold">
                        Data de Nascimento:
                    </span>
                </label>
                <input
                    id="data_nascimento"
                    type="date"
                    className={`input input-bordered w-full ${errors.data_nascimento ? 'input-error' : ''}`}
                    value={data.data_nascimento || ''}
                    onChange={(e) => setData('data_nascimento', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.data_nascimento && (
                    <p className="text-error mt-1 text-xs">
                        {errors.data_nascimento}
                    </p>
                )}
            </div>

            {/* Banco */}
            <div>
                <label className="label" htmlFor="banco_id">
                    <span className="label-text font-semibold">Banco:</span>
                </label>
                <select
                    id="banco_id"
                    className={`select select-bordered w-full ${errors.banco_id ? 'select-error' : ''}`}
                    value={data.banco_id || ''}
                    onChange={(e) => setData('banco_id', e.target.value)}
                    disabled={processing || !canEdit}
                >
                    <option value="">Selecione um banco</option>
                    {bancos.map(
                        (banco: {
                            id: string;
                            codigo: string;
                            nome: string;
                        }) => (
                            <option key={banco.id} value={banco.id}>
                                {banco.codigo} - {banco.nome}
                            </option>
                        ),
                    )}
                </select>
                {errors.banco_id && (
                    <p className="text-error mt-1 text-xs">{errors.banco_id}</p>
                )}
            </div>

            {/* Agência */}
            <div>
                <label className="label" htmlFor="agencia">
                    <span className="label-text font-semibold">Agência:</span>
                </label>
                <input
                    id="agencia"
                    type="text"
                    className={`input input-bordered w-full ${errors.agencia ? 'input-error' : ''}`}
                    value={data.agencia || ''}
                    onChange={(e) => setData('agencia', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.agencia && (
                    <p className="text-error mt-1 text-xs">{errors.agencia}</p>
                )}
            </div>

            {/* Conta Bancária */}
            <div>
                <label className="label" htmlFor="conta_bancaria">
                    <span className="label-text font-semibold">
                        Conta Bancária:
                    </span>
                </label>
                <IMaskInput
                    id="conta_bancaria"
                    mask={[
                        { mask: '0-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '00-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '000-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '0000-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '00000-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '000000-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '0000000-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '00000000-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '000000000-S', definitions: { S: /[0-9xX]/ } },
                        { mask: '0000000000-S', definitions: { S: /[0-9xX]/ } },
                        {
                            mask: '00000000000-S',
                            definitions: { S: /[0-9xX]/ },
                        },
                        {
                            mask: '000000000000-S',
                            definitions: { S: /[0-9xX]/ },
                        },
                    ]}
                    prepare={(str) => str.toUpperCase()}
                    className={`input input-bordered w-full ${errors.conta_bancaria ? 'input-error' : ''}`}
                    value={data.conta_bancaria || ''}
                    onAccept={(value: string) =>
                        setData('conta_bancaria', value)
                    }
                    disabled={processing || !canEdit}
                    placeholder="Ex: 12345-X ou 1234567-0"
                />
                {errors.conta_bancaria && (
                    <p className="text-error mt-1 text-xs">
                        {errors.conta_bancaria}
                    </p>
                )}
            </div>

            {/* CEP */}
            <div>
                <label className="label" htmlFor="cep">
                    <span className="label-text font-semibold">CEP:</span>
                </label>
                <input
                    id="cep"
                    type="text"
                    className={`input input-bordered w-full ${errors.cep ? 'input-error' : ''}`}
                    value={data.cep || ''}
                    onChange={(e) => setData('cep', e.target.value)}
                    disabled={processing || !canEdit}
                    // TODO: Add mask and auto-fill address if desired
                />
                {errors.cep && (
                    <p className="text-error mt-1 text-xs">{errors.cep}</p>
                )}
            </div>

            {/* Endereço */}
            <div>
                <label className="label" htmlFor="endereco">
                    <span className="label-text font-semibold">Endereço:</span>
                </label>
                <input
                    id="endereco"
                    type="text"
                    className={`input input-bordered w-full ${errors.endereco ? 'input-error' : ''}`}
                    value={data.endereco || ''}
                    onChange={(e) => setData('endereco', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.endereco && (
                    <p className="text-error mt-1 text-xs">{errors.endereco}</p>
                )}
            </div>

            {/* Número */}
            <div>
                <label className="label" htmlFor="numero">
                    <span className="label-text font-semibold">Número:</span>
                </label>
                <input
                    id="numero"
                    type="text"
                    className={`input input-bordered w-full ${errors.numero ? 'input-error' : ''}`}
                    value={data.numero || ''}
                    onChange={(e) => setData('numero', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.numero && (
                    <p className="text-error mt-1 text-xs">{errors.numero}</p>
                )}
            </div>

            {/* Complemento */}
            <div>
                <label className="label" htmlFor="complemento">
                    <span className="label-text font-semibold">
                        Complemento:
                    </span>
                </label>
                <input
                    id="complemento"
                    type="text"
                    className={`input input-bordered w-full ${errors.complemento ? 'input-error' : ''}`}
                    value={data.complemento || ''}
                    onChange={(e) => setData('complemento', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.complemento && (
                    <p className="text-error mt-1 text-xs">
                        {errors.complemento}
                    </p>
                )}
            </div>

            {/* Bairro */}
            <div>
                <label className="label" htmlFor="bairro">
                    <span className="label-text font-semibold">Bairro:</span>
                </label>
                <input
                    id="bairro"
                    type="text"
                    className={`input input-bordered w-full ${errors.bairro ? 'input-error' : ''}`}
                    value={data.bairro || ''}
                    onChange={(e) => setData('bairro', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.bairro && (
                    <p className="text-error mt-1 text-xs">{errors.bairro}</p>
                )}
            </div>

            {/* Cidade */}
            <div>
                <label className="label" htmlFor="cidade">
                    <span className="label-text font-semibold">Cidade:</span>
                </label>
                <input
                    id="cidade"
                    type="text"
                    className={`input input-bordered w-full ${errors.cidade ? 'input-error' : ''}`}
                    value={data.cidade || ''}
                    onChange={(e) => setData('cidade', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.cidade && (
                    <p className="text-error mt-1 text-xs">{errors.cidade}</p>
                )}
            </div>

            {/* Estado */}
            <div>
                <label className="label" htmlFor="estado">
                    <span className="label-text font-semibold">Estado:</span>
                </label>
                <select
                    id="estado"
                    className={`select select-bordered w-full ${errors.uf ? 'select-error' : ''}`}
                    value={data.uf || ''}
                    onChange={(e) => setData('uf', e.target.value)}
                    disabled={processing || !canEdit}
                >
                    <option value="">Selecione</option>
                    {ESTADOS.map((uf) => (
                        <option key={uf.sigla} value={uf.sigla}>
                            {uf.nome}
                        </option>
                    ))}
                </select>
                {errors.uf && (
                    <p className="text-error mt-1 text-xs">{errors.uf}</p>
                )}
            </div>

            {/* Área de Atuação */}
            {/* <div className="md:col-span-2">
                <label className="label" htmlFor="area_atuacao">
                    <span className="label-text font-semibold">
                        Área de Atuação:
                    </span>
                </label>
                <textarea
                    id="area_atuacao"
                    className={`textarea textarea-bordered w-full ${errors.area_atuacao ? 'textarea-error' : ''}`}
                    value={data.area_atuacao || ''}
                    onChange={(e) => setData('area_atuacao', e.target.value)}
                    disabled={processing || !canEdit}
                    rows={3}
                />
                {errors.area_atuacao && (
                    <p className="text-error mt-1 text-xs">
                        {errors.area_atuacao}
                    </p>
                )}
            </div> */}

            {/* Tecnologias */}
            {/* <div className="md:col-span-2">
                <label className="label" htmlFor="tecnologias">
                    <span className="label-text font-semibold">
                        Tecnologias (separadas por vírgula):
                    </span>
                </label>
                <input
                    id="tecnologias"
                    type="text"
                    className={`input input-bordered w-full ${errors.tecnologias ? 'input-error' : ''}`}
                    value={data.tecnologias || ''}
                    onChange={(e) => setData('tecnologias', e.target.value)}
                    disabled={processing || !canEdit}
                />
                {errors.tecnologias && (
                    <p className="text-error mt-1 text-xs">
                        {errors.tecnologias}
                    </p>
                )}
            </div> */}

            {/* Links
            <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:col-span-2 md:grid-cols-2">
                <div>
                    <label className="label" htmlFor="curriculo_lattes_url">
                        <span className="label-text font-semibold">
                            Currículo Lattes URL:
                        </span>
                    </label>
                    <input
                        id="curriculo_lattes_url"
                        type="url"
                        className={`input input-bordered w-full ${errors.curriculo_lattes_url ? 'input-error' : ''}`}
                        value={data.curriculo_lattes_url || ''}
                        onChange={(e) =>
                            setData('curriculo_lattes_url', e.target.value)
                        }
                        disabled={processing || !canEdit}
                        placeholder="https://..."
                    />
                    {errors.curriculo_lattes_url && (
                        <p className="text-error mt-1 text-xs">
                            {errors.curriculo_lattes_url}
                        </p>
                    )}
                </div>
                <div>
                    <label className="label" htmlFor="linkedin_url">
                        <span className="label-text font-semibold">
                            LinkedIn URL:
                        </span>
                    </label>
                    <input
                        id="linkedin_url"
                        type="url"
                        className={`input input-bordered w-full ${errors.linkedin_url ? 'input-error' : ''}`}
                        value={data.linkedin_url || ''}
                        onChange={(e) =>
                            setData('linkedin_url', e.target.value)
                        }
                        disabled={processing || !canEdit}
                        placeholder="https://linkedin.com/in/..."
                    />
                    {errors.linkedin_url && (
                        <p className="text-error mt-1 text-xs">
                            {errors.linkedin_url}
                        </p>
                    )}
                </div>
                <div>
                    <label className="label" htmlFor="github_url">
                        <span className="label-text font-semibold">
                            GitHub URL:
                        </span>
                    </label>
                    <input
                        id="github_url"
                        type="url"
                        className={`input input-bordered w-full ${errors.github_url ? 'input-error' : ''}`}
                        value={data.github_url || ''}
                        onChange={(e) => setData('github_url', e.target.value)}
                        disabled={processing || !canEdit}
                        placeholder="https://github.com/..."
                    />
                    {errors.github_url && (
                        <p className="text-error mt-1 text-xs">
                            {errors.github_url}
                        </p>
                    )}
                </div>
                <div>
                    <label className="label" htmlFor="website_url">
                        <span className="label-text font-semibold">
                            Website URL:
                        </span>
                    </label>
                    <input
                        id="website_url"
                        type="url"
                        className={`input input-bordered w-full ${errors.website_url ? 'input-error' : ''}`}
                        value={data.website_url || ''}
                        onChange={(e) => setData('website_url', e.target.value)}
                        disabled={processing || !canEdit}
                        placeholder="https://example.com"
                    />
                    {errors.website_url && (
                        <p className="text-error mt-1 text-xs">
                            {errors.website_url}
                        </p>
                    )}
                </div>
            </div> */}

            {/* Campos Extras */}
            <div className="md:col-span-2">
                <CamposExtrasSection
                    campos_extras={data.campos_extras}
                    onCamposChange={handleCamposChange}
                    errors={
                        typeof errors.campos_extras === 'object'
                            ? errors.campos_extras
                            : undefined
                    }
                    processing={processing}
                    canEdit={canEdit}
                />
            </div>

            {/* Actions */}
            {canEdit && (
                <div className="mt-6 flex justify-end space-x-3 md:col-span-2">
                    <button
                        type="button"
                        onClick={onCancel}
                        className="btn btn-ghost"
                        disabled={processing}
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        className="btn btn-primary"
                        disabled={processing}
                    >
                        {processing ? (
                            <span className="loading loading-spinner loading-sm"></span>
                        ) : (
                            'Salvar Alterações'
                        )}
                    </button>
                </div>
            )}
        </form>
    );
};

ColaboradorDetalhes.displayName = 'ColaboradorDetalhes';
