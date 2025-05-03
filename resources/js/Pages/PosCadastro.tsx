import { BANCOS, ESTADOS } from '@/constants';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';
import { IMaskInput } from 'react-imask';
import Select from 'react-select';

interface ColaboradorData {
    foto_url: File | null;
    genero: string;
    data_nascimento: string;
    cpf: string;
    rg: string;
    uf_rg: string;
    orgao_emissor_rg: string;
    cep: string;
    logradouro: string;
    numero: string;
    complemento: string;
    bairro: string;
    cidade: string;
    estado: string;
    telefone: string;
    conta_bancaria: string;
    agencia: string;
    codigo_banco: string;
    linkedin_url: string;
    github_url: string;
    figma_url: string;
    curriculo: string;
    area_atuacao: string;
    tecnologias: string;
}

export default function PosCadastro() {
    const { data, setData, post, processing, errors } =
        useForm<ColaboradorData>({
            foto_url: null,
            genero: '',
            data_nascimento: '',
            rg: '',
            uf_rg: '',
            orgao_emissor_rg: '',
            cpf: '',
            cep: '',
            logradouro: '',
            numero: '',
            complemento: '',
            bairro: '',
            cidade: '',
            estado: '',
            telefone: '',
            conta_bancaria: '',
            agencia: '',
            codigo_banco: '',
            curriculo: '',
            linkedin_url: '',
            github_url: '',
            figma_url: '',
            area_atuacao: '',
            tecnologias: '',
        });

    const handleCepChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const cep = e.target.value.replace(/\D/g, '');
        setData('cep', cep);

        if (cep.length === 8) {
            try {
                const response = await fetch(
                    `https://viacep.com.br/ws/${cep}/json/`,
                );
                const res = await response.json();
                if (!res.erro) {
                    setData('logradouro', res.logradouro);
                    setData('bairro', res.bairro);
                    setData('cidade', res.localidade);
                    setData('estado', res.uf);
                } else {
                    alert('CEP não encontrado');
                }
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
            }
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/profile/update', {
            forceFormData: true,
            onSuccess: () => {
                alert('Cadastro realizado com sucesso!');
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl leading-tight font-semibold">
                    Completar Cadastro
                </h2>
            }
        >
            <div className="py-12">
                <Head title="Cadastro de Discente" />

                <div className="mx-auto max-w-4xl px-2">
                    <div className="card bg-base-100 shadow-lg">
                        <div className="card-body">
                            <h2 className="card-title mb-6">
                                Cadastro de Discente
                            </h2>

                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    {/* Foto de Perfil */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Foto de Perfil
                                            </span>
                                            <input
                                                id="foto_url"
                                                type="file"
                                                accept="image/*"
                                                className={`file-input file-input-bordered w-full ${errors.foto_url ? 'file-input-error' : ''}`}
                                                onChange={(e) => {
                                                    if (
                                                        e.target.files &&
                                                        e.target.files[0]
                                                    ) {
                                                        setData(
                                                            'foto_url',
                                                            e.target.files[0],
                                                        );
                                                    }
                                                }}
                                            />
                                            {errors.foto_url && (
                                                <span className="label-text-alt text-error">
                                                    {errors.foto_url}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Gênero */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Gênero
                                            </span>
                                            <Select
                                                id="genero"
                                                options={[
                                                    {
                                                        value: 'MASCULINO',
                                                        label: 'Masculino',
                                                    },
                                                    {
                                                        value: 'FEMININO',
                                                        label: 'Feminino',
                                                    },
                                                    {
                                                        value: 'OUTRO',
                                                        label: 'Outro',
                                                    },
                                                    {
                                                        value: 'NAO_INFORMAR',
                                                        label: 'Prefiro não informar',
                                                    },
                                                ]}
                                                onChange={(e) =>
                                                    setData(
                                                        'genero',
                                                        e?.value || '',
                                                    )
                                                }
                                                placeholder="Selecione o gênero..."
                                                classNamePrefix={
                                                    errors.genero
                                                        ? 'select-error'
                                                        : ''
                                                }
                                                isSearchable
                                            />
                                            {errors.genero && (
                                                <span className="label-text-alt text-error">
                                                    {errors.genero}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Data de Nascimento */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Data de Nascimento
                                            </span>
                                            <input
                                                id="data_nascimento"
                                                type="date"
                                                className={`input input-bordered w-full ${errors.data_nascimento ? 'input-error' : ''}`}
                                                value={
                                                    data.data_nascimento || ''
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'data_nascimento',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.data_nascimento && (
                                                <span className="label-text-alt text-error">
                                                    {errors.data_nascimento}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Documentos */}
                                    <div className="col-span-2">
                                        <div className="divider">
                                            Documentos
                                        </div>
                                    </div>

                                    {/* CPF */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                CPF*
                                            </span>
                                            <IMaskInput
                                                id="cpf"
                                                mask="000.000.000-00"
                                                className={`input input-bordered w-full ${errors.cpf ? 'input-error' : ''}`}
                                                value={data.cpf}
                                                onAccept={(value: string) =>
                                                    setData('cpf', value)
                                                }
                                                required
                                            />
                                            {errors.cpf && (
                                                <span className="label-text-alt text-error">
                                                    {errors.cpf}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* RG */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                RG*
                                            </span>
                                            <input
                                                id="rg"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.rg ? 'input-error' : ''}`}
                                                value={data.rg}
                                                minLength={7}
                                                maxLength={9}
                                                onChange={(e) =>
                                                    setData(
                                                        'rg',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                            {errors.rg && (
                                                <span className="label-text-alt text-error">
                                                    {errors.rg}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Orgão Emissor */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Orgão Emissor*
                                            </span>
                                            <input
                                                id="orgao_emissor_rg"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.orgao_emissor_rg ? 'input-error' : ''}`}
                                                value={data.orgao_emissor_rg}
                                                onChange={(e) =>
                                                    setData(
                                                        'orgao_emissor_rg',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                            {errors.orgao_emissor_rg && (
                                                <span className="label-text-alt text-error">
                                                    {errors.orgao_emissor_rg}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* UF do RG */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                UF do RG*
                                            </span>
                                            <Select
                                                id="uf_rg"
                                                options={ESTADOS.map((uf) => ({
                                                    value: uf.sigla,
                                                    label: uf.nome,
                                                }))}
                                                onChange={(e) =>
                                                    setData(
                                                        'uf_rg',
                                                        e?.value || '',
                                                    )
                                                }
                                                placeholder="Selecione uma UF..."
                                                isSearchable
                                            />
                                            {errors.uf_rg && (
                                                <span className="label-text-alt text-error">
                                                    {errors.uf_rg}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Endereço */}
                                    <div className="col-span-2">
                                        <div className="divider">Endereço</div>
                                    </div>

                                    {/* CEP */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                CEP
                                            </span>
                                            <IMaskInput
                                                id="cep"
                                                mask="00000-000"
                                                className={`input input-bordered w-full ${errors.cep ? 'input-error' : ''}`}
                                                value={data.cep}
                                                onAccept={(value: string) =>
                                                    setData('cep', value)
                                                }
                                                onChange={handleCepChange}
                                                placeholder="00000-000"
                                            />
                                            {errors.cep && (
                                                <span className="label-text-alt text-error">
                                                    {errors.cep}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Logradouro */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Logradouro
                                            </span>
                                            <input
                                                id="logradouro"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.logradouro ? 'input-error' : ''}`}
                                                value={data.logradouro}
                                                onChange={(e) =>
                                                    setData(
                                                        'logradouro',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.logradouro && (
                                                <span className="label-text-alt text-error">
                                                    {errors.logradouro}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Número */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Número
                                            </span>
                                            <input
                                                id="numero"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.numero ? 'input-error' : ''}`}
                                                value={data.numero}
                                                onChange={(e) =>
                                                    setData(
                                                        'numero',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.numero && (
                                                <span className="label-text-alt text-error">
                                                    {errors.numero}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Complemento */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Complemento
                                            </span>
                                            <input
                                                id="complemento"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.complemento ? 'input-error' : ''}`}
                                                value={data.complemento}
                                                onChange={(e) =>
                                                    setData(
                                                        'complemento',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.complemento && (
                                                <span className="label-text-alt text-error">
                                                    {errors.complemento}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Bairro */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Bairro
                                            </span>
                                            <input
                                                id="bairro"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.bairro ? 'input-error' : ''}`}
                                                value={data.bairro}
                                                onChange={(e) =>
                                                    setData(
                                                        'bairro',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.bairro && (
                                                <span className="label-text-alt text-error">
                                                    {errors.bairro}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Estado */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Estado
                                            </span>
                                            <Select
                                                id="estado"
                                                options={ESTADOS.map((uf) => ({
                                                    value: uf.sigla,
                                                    label: uf.nome,
                                                }))}
                                                onChange={(e) =>
                                                    setData(
                                                        'estado',
                                                        e?.value || '',
                                                    )
                                                }
                                                placeholder="Selecione um estado..."
                                                isSearchable
                                            />
                                            {errors.estado && (
                                                <span className="label-text-alt text-error">
                                                    {errors.estado}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Cidade */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Cidade
                                            </span>
                                            <input
                                                id="cidade"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.cidade ? 'input-error' : ''}`}
                                                value={data.cidade}
                                                onChange={(e) =>
                                                    setData(
                                                        'cidade',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.cidade && (
                                                <span className="label-text-alt text-error">
                                                    {errors.cidade}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Dados de Contato */}
                                    <div className="col-span-2">
                                        <div className="divider">
                                            Dados de Contato
                                        </div>
                                    </div>

                                    {/* Telefone */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Telefone
                                            </span>
                                            <input
                                                id="telefone"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.telefone ? 'input-error' : ''}`}
                                                value={data.telefone}
                                                onChange={(e) =>
                                                    setData(
                                                        'telefone',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.telefone && (
                                                <span className="label-text-alt text-error">
                                                    {errors.telefone}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Dados Bancários */}
                                    <div className="col-span-2">
                                        <div className="divider">
                                            Dados Bancários
                                        </div>
                                    </div>

                                    {/* Banco */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Banco
                                            </span>
                                            <Select
                                                options={BANCOS}
                                                onChange={(e) =>
                                                    setData(
                                                        'codigo_banco',
                                                        e?.value || '',
                                                    )
                                                }
                                                placeholder="Selecione um banco..."
                                                isSearchable
                                            />
                                        </label>
                                    </div>

                                    {/* Conta Bancária */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Conta Bancária
                                            </span>
                                            <IMaskInput
                                                id="conta_bancaria"
                                                mask="00000-0"
                                                className={`input input-bordered w-full ${errors.conta_bancaria ? 'input-error' : ''}`}
                                                value={data.conta_bancaria}
                                                onAccept={(value: string) =>
                                                    setData(
                                                        'conta_bancaria',
                                                        value,
                                                    )
                                                }
                                                placeholder="00000-0"
                                            />
                                            {errors.conta_bancaria && (
                                                <span className="label-text-alt text-error">
                                                    {errors.conta_bancaria}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Agência */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Agência
                                            </span>
                                            <IMaskInput
                                                id="agencia"
                                                mask="0000-0"
                                                className={`input input-bordered w-full ${errors.agencia ? 'input-error' : ''}`}
                                                value={data.agencia}
                                                onAccept={(value: string) =>
                                                    setData('agencia', value)
                                                }
                                                placeholder="0000-0"
                                            />
                                            {errors.agencia && (
                                                <span className="label-text-alt text-error">
                                                    {errors.agencia}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Dados Profissionais */}
                                    <div className="col-span-2">
                                        <div className="divider">
                                            Dados Profissionais
                                        </div>
                                    </div>

                                    {/* Currículo Lattes */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Currículo Lattes
                                            </span>
                                            <input
                                                id="curriculo"
                                                type="url"
                                                className={`input input-bordered w-full ${errors.curriculo ? 'input-error' : ''}`}
                                                value={data.curriculo}
                                                onChange={(e) =>
                                                    setData(
                                                        'curriculo',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.curriculo && (
                                                <span className="label-text-alt text-error">
                                                    {errors.curriculo}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* LinkedIn */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                LinkedIn
                                            </span>
                                            <input
                                                id="linkedin"
                                                type="url"
                                                className={`input input-bordered w-full ${errors.linkedin_url ? 'input-error' : ''}`}
                                                value={data.linkedin_url}
                                                onChange={(e) =>
                                                    setData(
                                                        'linkedin_url',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.linkedin_url && (
                                                <span className="label-text-alt text-error">
                                                    {errors.linkedin_url}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* GitHub */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                GitHub
                                            </span>
                                            <input
                                                id="github"
                                                type="url"
                                                className={`input input-bordered w-full ${errors.github_url ? 'input-error' : ''}`}
                                                value={data.github_url}
                                                onChange={(e) =>
                                                    setData(
                                                        'github_url',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.github_url && (
                                                <span className="label-text-alt text-error">
                                                    {errors.github_url}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Figma */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Figma
                                            </span>
                                            <input
                                                id="figma"
                                                type="url"
                                                className={`input input-bordered w-full ${errors.figma_url ? 'input-error' : ''}`}
                                                value={data.figma_url}
                                                onChange={(e) =>
                                                    setData(
                                                        'figma_url',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.figma_url && (
                                                <span className="label-text-alt text-error">
                                                    {errors.figma_url}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Área de Atuação */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Área de Atuação
                                            </span>
                                            <input
                                                id="area_atuacao"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.area_atuacao ? 'input-error' : ''}`}
                                                value={data.area_atuacao}
                                                onChange={(e) =>
                                                    setData(
                                                        'area_atuacao',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                            {errors.area_atuacao && (
                                                <span className="label-text-alt text-error">
                                                    {errors.area_atuacao}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Tecnologias */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Tecnologias
                                            </span>
                                            <input
                                                id="tecnologias"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.tecnologias ? 'input-error' : ''}`}
                                                value={data.tecnologias}
                                                onChange={(e) =>
                                                    setData(
                                                        'tecnologias',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Separadas por vírgula"
                                            />
                                            {errors.tecnologias && (
                                                <span className="label-text-alt text-error">
                                                    {errors.tecnologias}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Botão */}
                                    <div className="col-span-2 mt-6">
                                        <button
                                            type="submit"
                                            className="btn btn-primary btn-block"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Enviando...'
                                                : 'Salvar Cadastro'}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
