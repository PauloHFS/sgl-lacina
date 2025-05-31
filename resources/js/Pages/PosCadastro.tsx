import { ESTADOS, AREAS_ATUACAO, TECNOLOGIAS } from '@/constants';
import { useToast } from '@/Context/ToastProvider';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Banco, PageProps } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import React, { FormEventHandler } from 'react';
import { IMaskInput } from 'react-imask';
import MultiSelect from '@/Components/MultiSelect';

interface PosCadastroProps extends PageProps {
    bancos: Array<Banco>; // TODO Refatorar isso para buscar paginado no select (isso pode crescer muito)
}

const ORGAOS_EMISSORES = [
    {
        sigla: 'SSP',
        nome: 'Secretaria de Segurança Pública',
    },
    { sigla: 'DPF', nome: 'Departamento de Polícia Federal' },
    { sigla: 'MRE', nome: 'Ministério das Relações Exteriores' },
    { sigla: 'COMAER', nome: 'Comando da Aeronáutica' },
    { sigla: 'COLOG', nome: 'Comando Logístico do Exército' },
    { sigla: 'DGePM', nome: 'Diretoria-Geral do Pessoal da Marinha' },
    { sigla: 'OUTROS', nome: 'Outros/Conselhos Profissionais' },
];

export default function PosCadastro({ bancos }: PosCadastroProps) {
    const { data, setData, post, errors, processing } = useForm<{
        foto_url: File | null;
        genero: string;
        data_nascimento: string;
        rg: string;
        uf_rg: string;
        orgao_emissor_rg: string;
        cpf: string;
        cep: string;
        endereco: string;
        numero: string;
        complemento: string;
        bairro: string;
        cidade: string;
        estado: string;
        telefone: string;
        conta_bancaria: string;
        agencia: string;
        banco_id: string;
        curriculo_lattes_url: string;
        linkedin_url: string;
        github_url: string;
        website_url: string;
        area_atuacao: string[];
        tecnologias: string[];
    }>({
        foto_url: null,
        genero: '',
        data_nascimento: '',
        rg: '',
        uf_rg: '',
        orgao_emissor_rg: '',
        cpf: '',
        cep: '',
        endereco: '',
        numero: '',
        complemento: '',
        bairro: '',
        cidade: '',
        estado: '',
        telefone: '',
        conta_bancaria: '',
        agencia: '',
        banco_id: '',
        curriculo_lattes_url: '',
        linkedin_url: '',
        github_url: '',
        website_url: '',
        area_atuacao: [],
        tecnologias: [],
    });

    const { toast } = useToast();

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();

        // Remover formatação do CPF e telefone antes de enviar
        const cleanedData = {
            ...data,
            cpf: data.cpf.replace(/\\D/g, ''),
            telefone: data.telefone.replace(/\\D/g, ''),
            rg: data.rg.replace(/\\D/g, ''),
            area_atuacao: data.area_atuacao.join(', '),
            tecnologias: data.tecnologias.join(', '),
        };

        post(route('profile.completarCadastro'), {
            data: cleanedData,
            forceFormData: true,
            onSuccess: () => {
                toast('Cadastro realizado com sucesso!', 'success');
            },
            onError: () => {
                toast('Erro ao realizar cadastro. Tente novamente.', 'error');
            },
        });
    };

    // TODO: travar os dados de endereço até fazer a request, depois libera achando ou não o cep
    const viaCEP = (cep: string) => {
        if (cep.length !== 8) return;

        axios
            .get(`https://viacep.com.br/ws/${cep}/json/`)
            .then((res) => {
                if (res.data.erro) {
                    toast('CEP não encontrado', 'info');
                    return;
                }

                setData((prev) => ({
                    ...prev,
                    endereco: res.data.logradouro || '',
                    bairro: res.data.bairro || '',
                    cidade: res.data.localidade || '',
                    estado: res.data.uf || '',
                    complemento: res.data.complemento || '',
                }));
            })
            .catch((err) => {
                console.error('Erro ao buscar CEP:', err);
                toast(
                    'Erro ao buscar CEP. Tente novamente mais tarde.',
                    'info',
                );
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
                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    {/* Foto de Perfil */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-2">
                                                Foto de Perfil*
                                            </span>
                                            <div className="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                                                {/* Avatar Preview or Placeholder */}
                                                {data.foto_url ? (
                                                    <div className="avatar">
                                                        <div className="ring-primary ring-offset-base-100 h-24 w-24 rounded-lg ring ring-offset-2">
                                                            <img
                                                                src={URL.createObjectURL(
                                                                    data.foto_url,
                                                                )}
                                                                alt="Preview da Foto de Perfil"
                                                                className="h-full w-full rounded-lg object-cover"
                                                            />
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="bg-base-200 ring-base-300 flex aspect-square h-24 w-24 items-center justify-center rounded-lg ring-1">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            strokeWidth={1.5}
                                                            stroke="currentColor"
                                                            className="text-base-content flex h-12 w-12 opacity-30"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                                            />
                                                        </svg>
                                                    </div>
                                                )}

                                                {/* File Input and messages */}
                                                <div className="w-full flex-grow sm:w-auto">
                                                    <input
                                                        id="foto_url"
                                                        type="file"
                                                        accept="image/png, image/jpeg, image/gif, image/webp, .heic, .heif"
                                                        className={`file-input file-input-bordered w-full ${errors.foto_url ? 'file-input-error' : ''}`}
                                                        onChange={(e) => {
                                                            if (
                                                                e.target
                                                                    .files &&
                                                                e.target
                                                                    .files[0]
                                                            ) {
                                                                const file =
                                                                    e.target
                                                                        .files[0];
                                                                if (
                                                                    file.size >
                                                                    2048 * 1024
                                                                ) {
                                                                    // 2MB
                                                                    toast(
                                                                        'A foto não pode ser maior que 2MB.',
                                                                        'error',
                                                                    );
                                                                    setData(
                                                                        'foto_url',
                                                                        null,
                                                                    );
                                                                    e.target.value =
                                                                        ''; // Limpa o campo de input
                                                                } else {
                                                                    setData(
                                                                        'foto_url',
                                                                        file,
                                                                    );
                                                                }
                                                            } else {
                                                                setData(
                                                                    'foto_url',
                                                                    null,
                                                                ); // Limpa se nenhum arquivo for selecionado
                                                                e.target.value =
                                                                    '';
                                                            }
                                                        }}
                                                    />
                                                    {errors.foto_url ? (
                                                        <span className="label-text-alt text-error mt-1">
                                                            {errors.foto_url}
                                                        </span>
                                                    ) : (
                                                        <span className="label-text-alt mt-1">
                                                            Formatos aceitos:
                                                            PNG, JPG, GIF, WEBP,
                                                            HEIC.
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {/* Gênero */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Gênero*
                                            </span>
                                            <select
                                                id="genero"
                                                className={`select select-bordered w-full ${errors.genero ? 'select-error' : ''}`}
                                                value={data.genero || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'genero',
                                                        e.target.value,
                                                    )
                                                }
                                            >
                                                <option value="" disabled>
                                                    Selecione o gênero...
                                                </option>
                                                <option value="MASCULINO">
                                                    Masculino
                                                </option>
                                                <option value="FEMININO">
                                                    Feminino
                                                </option>
                                                <option value="OUTRO">
                                                    Outro
                                                </option>
                                                <option value="NAO_INFORMAR">
                                                    Prefiro não informar
                                                </option>
                                            </select>
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
                                                Data de Nascimento*
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

                                    {/* Telefone */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Telefone*
                                            </span>

                                            <IMaskInput
                                                id="telefone"
                                                mask="+55 (00) 00000-0000"
                                                className={`input input-bordered w-full ${errors.telefone ? 'input-error' : ''}`}
                                                value={data.telefone}
                                                onAccept={(value: string) =>
                                                    setData('telefone', value)
                                                }
                                                placeholder="+55 (00) 00000-0000"
                                            />
                                            {errors.telefone && (
                                                <span className="label-text-alt text-error">
                                                    {errors.telefone}
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
                                                minLength={6}
                                                maxLength={16} // Alterado de 15 para 16
                                                onChange={(e) =>
                                                    setData(
                                                        'rg',
                                                        e.target.value.toUpperCase(),
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
                                                Orgão Emissor RG*
                                            </span>
                                            <select
                                                id="orgao_emissor_rg"
                                                className={`select select-bordered w-full ${errors.orgao_emissor_rg ? 'select-error' : ''}`}
                                                value={
                                                    data.orgao_emissor_rg || ''
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'orgao_emissor_rg',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            >
                                                <option value="" disabled>
                                                    Selecione o órgão emissor...
                                                </option>
                                                {ORGAOS_EMISSORES.map(
                                                    (orgao) => (
                                                        <option
                                                            key={orgao.sigla}
                                                            value={orgao.sigla}
                                                        >
                                                            {orgao.sigla} - {orgao.nome}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
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
                                            <select
                                                id="uf_rg"
                                                className={`select select-bordered w-full ${errors.uf_rg ? 'select-error' : ''}`}
                                                value={data.uf_rg || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'uf_rg',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            >
                                                <option value="" disabled>
                                                    Selecione uma UF...
                                                </option>
                                                {ESTADOS.map((uf) => (
                                                    <option
                                                        key={uf.sigla}
                                                        value={uf.sigla}
                                                    >
                                                        {uf.nome}
                                                    </option>
                                                ))}
                                            </select>
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
                                                CEP*
                                            </span>
                                            <IMaskInput
                                                id="cep"
                                                mask="00000-000"
                                                className={`input input-bordered w-full ${errors.cep ? 'input-error' : ''}`}
                                                value={data.cep}
                                                onAccept={(value: string) =>
                                                    setData('cep', value)
                                                }
                                                onChange={(
                                                    e: React.ChangeEvent<HTMLInputElement>,
                                                ) => {
                                                    const rawCep =
                                                        e.target.value.replace(
                                                            /\D/g,
                                                            '',
                                                        );
                                                    setData(
                                                        'cep',
                                                        e.target.value,
                                                    );
                                                    viaCEP(rawCep);
                                                }}
                                                placeholder="00000-000"
                                                required
                                            />
                                            {errors.cep && (
                                                <span className="label-text-alt text-error">
                                                    {errors.cep}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Endereço */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Endereço*
                                            </span>
                                            <input
                                                id="endereco"
                                                type="text"
                                                className={`input input-bordered w-full ${errors.endereco ? 'input-error' : ''}`}
                                                value={data.endereco}
                                                onChange={(e) =>
                                                    setData(
                                                        'endereco',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            />
                                            {errors.endereco && (
                                                <span className="label-text-alt text-error">
                                                    {errors.endereco}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Número */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Número*
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
                                                required
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
                                                Bairro*
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
                                                required
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
                                                Estado*
                                            </span>
                                            <select
                                                id="estado"
                                                className={`select select-bordered w-full ${errors.estado ? 'select-error' : ''}`}
                                                value={data.estado || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'estado',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            >
                                                <option value="" disabled>
                                                    Selecione um estado...
                                                </option>
                                                {ESTADOS.map((uf) => (
                                                    <option
                                                        key={uf.sigla}
                                                        value={uf.sigla}
                                                    >
                                                        {uf.nome}
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.estado && (
                                                <span className="label-text-alt text-error">
                                                    {errors.estado}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Cidade */}
                                    {/* TODO: Fazer isso um select, baseado no estado que foi selecionado no select de estado */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Cidade*
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
                                                required
                                            />
                                            {errors.cidade && (
                                                <span className="label-text-alt text-error">
                                                    {errors.cidade}
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
                                                Banco*
                                            </span>
                                            <select
                                                id="banco_id"
                                                className={`select select-bordered w-full ${errors.banco_id ? 'select-error' : ''}`}
                                                value={data.banco_id || ''}
                                                onChange={(e) =>
                                                    setData(
                                                        'banco_id',
                                                        e.target.value,
                                                    )
                                                }
                                                required
                                            >
                                                <option value="" disabled>
                                                    Selecione um banco...
                                                </option>
                                                {bancos.map((banco) => (
                                                    <option
                                                        key={banco.id}
                                                        value={banco.id}
                                                    >
                                                        {banco.codigo} -{' '}
                                                        {banco.nome}
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.banco_id && (
                                                <span className="label-text-alt text-error">
                                                    {errors.banco_id}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Agência */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Agência*
                                            </span>
                                            <input
                                                id="agencia"
                                                type="text" // Pode ser IMaskInput se houver um padrão específico
                                                className={`input input-bordered w-full ${errors.agencia ? 'input-error' : ''}`}
                                                value={data.agencia}
                                                onChange={(e) =>
                                                    setData(
                                                        'agencia',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder='Ex: 1234 ou 12345'
                                                required
                                            />
                                            {errors.agencia && (
                                                <span className="label-text-alt text-error">
                                                    {errors.agencia}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Conta Bancária */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Conta Bancária*
                                            </span>
                                            <IMaskInput
                                                id="conta_bancaria"
                                                mask={[
                                                    {
                                                        mask: '0-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '00-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '0000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '00000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '000000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '0000000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '00000000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '000000000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '0000000000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '00000000000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                    {
                                                        mask: '000000000000-S',
                                                        definitions: {
                                                            S: /[0-9xX]/,
                                                        },
                                                    },
                                                ]}
                                                prepare={(str) =>
                                                    str.toUpperCase()
                                                }
                                                className={`input input-bordered w-full ${errors.conta_bancaria ? 'input-error' : ''}`}
                                                value={data.conta_bancaria}
                                                onAccept={(value: string) =>
                                                    setData(
                                                        'conta_bancaria',
                                                        value,
                                                    )
                                                }
                                                required
                                                placeholder="Ex: 12345-X ou 1234567-0" // Atualizado para refletir flexibilidade
                                            />
                                            {errors.conta_bancaria && (
                                                <span className="label-text-alt text-error">
                                                    {errors.conta_bancaria}
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
                                                Currículo Lattes*
                                            </span>
                                            <input
                                                id="curriculo_lattes_url"
                                                type="url"
                                                className={`input input-bordered w-full ${errors.curriculo_lattes_url ? 'input-error' : ''}`}
                                                value={
                                                    data.curriculo_lattes_url
                                                }
                                                onChange={(e) =>
                                                    setData(
                                                        'curriculo_lattes_url',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="https://lattes.cnpq.br/1234567890123456"
                                                required
                                            />
                                            {errors.curriculo_lattes_url && (
                                                <span className="label-text-alt text-error">
                                                    {
                                                        errors.curriculo_lattes_url
                                                    }
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
                                                placeholder="https://www.linkedin.com/in/..."
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
                                                placeholder="https://github.com/..."
                                            />
                                            {errors.github_url && (
                                                <span className="label-text-alt text-error">
                                                    {errors.github_url}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Website */}
                                    <div>
                                        <label className="form-control w-full">
                                            <span className="label-text mb-1">
                                                Website
                                            </span>
                                            <input
                                                id="website"
                                                type="url"
                                                className={`input input-bordered w-full ${errors.website_url ? 'input-error' : ''}`}
                                                value={data.website_url}
                                                onChange={(e) =>
                                                    setData(
                                                        'website_url',
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="https://www.seusite.com.br"
                                            />
                                            {errors.website_url && (
                                                <span className="label-text-alt text-error">
                                                    {errors.website_url}
                                                </span>
                                            )}
                                        </label>
                                    </div>

                                    {/* Área de Atuação */}
                                    <div>
                                        <MultiSelect
                                            id="area_atuacao"
                                            label="Área de Atuação"
                                            options={AREAS_ATUACAO}
                                            value={data.area_atuacao}
                                            onChange={(value) => setData('area_atuacao', value)}
                                            error={errors.area_atuacao}
                                            placeholder="Selecione suas áreas de atuação..."
                                            maxSelections={3}
                                        />
                                    </div>

                                    {/* Tecnologias */}
                                    <div>
                                        <MultiSelect
                                            id="tecnologias"
                                            label="Tecnologias"
                                            options={TECNOLOGIAS}
                                            value={data.tecnologias}
                                            onChange={(value) => setData('tecnologias', value)}
                                            error={errors.tecnologias}
                                            placeholder="Selecione as tecnologias que você domina..."
                                            maxSelections={5}
                                        />
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
