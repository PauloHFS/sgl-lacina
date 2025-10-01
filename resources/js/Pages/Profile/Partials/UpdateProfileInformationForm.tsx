import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import MultiSelect from '@/Components/MultiSelect';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { AREAS_ATUACAO, ESTADOS, TECNOLOGIAS } from '@/constants';
import { useToast } from '@/Context/ToastProvider';
import { Banco, Genero, User } from '@/types';
import { Transition } from '@headlessui/react';
import { Link, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import SearchableSelect from '@/Components/SearchableSelect';
import { useState } from 'react';
import { IMaskInput } from 'react-imask';

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    bancos = [],
    user,
    className = '',
}: {
    mustVerifyEmail: boolean;
    status?: string;
    bancos?: Banco[];
    user: User;
    className?: string;
}) {
    const { data, setData, errors, recentlySuccessful, processing } = useForm<
        Omit<typeof user, 'foto_url'> & { foto_url: string | File | null }
    >({
        ...user,
        foto_url: user.foto_url ?? null,
    });

    const [selectedOrgao, setSelectedOrgao] = useState(
        data.orgao_emissor_rg
            ? { id: data.orgao_emissor_rg, nome: data.orgao_emissor_rg, sigla: data.orgao_emissor_rg }
            : null
    );

    const { toast } = useToast();
    const [cpfValido, setCpfValido] = useState<boolean | null>(null);

    // Função para validar CPF
    const validarCPF = (cpf: string): boolean => {
        // Remove formatação
        const cpfLimpo = cpf.replace(/\D/g, '');

        // Verifica se tem 11 dígitos
        if (cpfLimpo.length !== 11) return false;

        // Verifica se todos os dígitos são iguais
        if (/^(\d)\1{10}$/.test(cpfLimpo)) return false;

        // Validação do primeiro dígito verificador
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpfLimpo.charAt(i)) * (10 - i);
        }
        let resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpfLimpo.charAt(9))) return false;

        // Validação do segundo dígito verificador
        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpfLimpo.charAt(i)) * (11 - i);
        }
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpfLimpo.charAt(10))) return false;

        return true;
    };

    // Função para buscar endereço via CEP
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
                    endereco: res.data.logradouro || prev.endereco || '',
                    bairro: res.data.bairro || prev.bairro || '',
                    cidade: res.data.localidade || prev.cidade || '',
                    uf: res.data.uf || prev.uf || '',
                    complemento: res.data.complemento || prev.complemento || '',
                }));
            })
            .catch((err) => {
                console.error('Erro ao buscar CEP:', err);
                toast(
                    'Erro ao buscar CEP. Tente novamente mais tarde.',
                    'error',
                );
            });
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        const formData = new FormData();

        Object.entries(data).forEach(([key, value]) => {
            // Lógica para arrays (area_atuacao, tecnologias)
            if (Array.isArray(value)) {
                value.forEach((item) => {
                    formData.append(`${key}[]`, String(item));
                });
                // Adiciona um campo vazio se o array for esvaziado, para garantir que a alteração seja registrada
                if (value.length === 0) {
                    formData.append(key, '');
                }
            }
            // Lógica para a foto
            else if (key === 'foto_url') {
                if (value instanceof File) {
                    formData.append(key, value);
                } else if (value === null) {
                    formData.append(key, ''); // Envia string vazia para remoção
                }
            } else if (value === null) {
                formData.append(key, '');
            } else if (typeof value === 'object' && value !== null) {
                formData.append(key, JSON.stringify(value));
            } else if (value !== undefined) {
                formData.append(key, String(value));
            }
        });

        formData.append('_method', 'PATCH');

        router.post(route('profile.update'), formData, {
            preserveScroll: true,
            onSuccess: () => {
                toast('Perfil atualizado com sucesso!', 'success');
            },
            onError: (errors) => {
                console.error('Erro ao atualizar perfil:', errors);
                toast('Verifique os erros no formulário.', 'error');
            },
        });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="card-title text-base-content">
                    Informações do Perfil
                </h2>
                <p className="text-base-content/70 mt-1 text-sm">
                    Atualize as informações do perfil da sua conta.
                </p>
            </header>
            <form onSubmit={submit} className="mt-6 space-y-6">
                {/* Grid de 2 colunas para campos básicos */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* Nome */}
                    <div>
                        <InputLabel htmlFor="name" value="Nome" />
                        <TextInput
                            id="name"
                            className="mt-1 block w-full"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            autoComplete="name"
                        />
                        <InputError className="mt-2" message={errors.name} />
                    </div>

                    {/* E-mail */}
                    <div>
                        <InputLabel htmlFor="email" value="E-mail" />
                        <TextInput
                            id="email"
                            type="email"
                            className="mt-1 block w-full"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            autoComplete="username"
                        />
                        <InputError className="mt-2" message={errors.email} />
                    </div>
                </div>

                {/* Foto de Perfil */}
                <div className="col-span-1 md:col-span-2">
                    <div className="card bg-base-200 p-6">
                        <h3 className="mb-4 text-lg font-semibold">
                            Foto de Perfil*
                        </h3>

                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            {/* Upload e Preview */}
                            <div>
                                <div className="flex flex-col items-center gap-4">
                                    {/* Avatar Preview */}
                                    {data.foto_url ? (
                                        <div className="avatar">
                                            <div className="ring-primary ring-offset-base-100 h-32 w-32 rounded-lg ring ring-offset-2">
                                                <img
                                                    src={
                                                        typeof data.foto_url ===
                                                        'string'
                                                            ? data.foto_url.startsWith(
                                                                  'http',
                                                              )
                                                                ? data.foto_url
                                                                : data.foto_url
                                                            : URL.createObjectURL(
                                                                  data.foto_url,
                                                              )
                                                    }
                                                    alt="Preview da Foto de Perfil"
                                                    className="h-full w-full rounded-lg object-cover"
                                                />
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="bg-base-100 ring-base-300 flex aspect-square h-32 w-32 items-center justify-center rounded-lg ring-1">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                strokeWidth={1.5}
                                                stroke="currentColor"
                                                className="text-base-content h-16 w-16 opacity-30"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                                />
                                            </svg>
                                        </div>
                                    )}

                                    {/* File Input */}
                                    <div className="w-full">
                                        <input
                                            id="foto_url"
                                            type="file"
                                            accept="image/png, image/jpeg, image/webp"
                                            className={`file-input file-input-bordered w-full ${errors.foto_url ? 'file-input-error' : ''}`}
                                            onChange={(e) => {
                                                if (
                                                    e.target.files &&
                                                    e.target.files[0]
                                                ) {
                                                    const file =
                                                        e.target.files[0];
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
                                                        e.target.value = ''; // Limpa o campo de input
                                                    } else {
                                                        setData(
                                                            'foto_url',
                                                            file,
                                                        );
                                                    }
                                                } else {
                                                    setData('foto_url', null); // Limpa se nenhum arquivo for selecionado
                                                    e.target.value = '';
                                                }
                                            }}
                                        />
                                        {errors.foto_url ? (
                                            <span className="label-text-alt text-error mt-1">
                                                {errors.foto_url}
                                            </span>
                                        ) : (
                                            <span className="label-text-alt mt-1">
                                                Formatos aceitos: PNG, JPG,
                                                WEBP. Máximo 2MB.
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Diretrizes da Foto */}
                            <div>
                                <h4 className="mb-3 text-base font-medium">
                                    Diretrizes para uma boa foto:
                                </h4>
                                <div className="space-y-3">
                                    <div className="flex items-center gap-3">
                                        <div className="badge badge-success badge-sm">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                className="h-3 w-3 stroke-current"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </div>
                                        <span className="text-sm">
                                            Foto com roupa adequada/profissional
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <div className="badge badge-success badge-sm">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                className="h-3 w-3 stroke-current"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </div>
                                        <span className="text-sm">
                                            Foto apenas do rosto e ombros
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <div className="badge badge-success badge-sm">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                className="h-3 w-3 stroke-current"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </div>
                                        <span className="text-sm">
                                            Fundo liso ou neutro
                                        </span>
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <div className="badge badge-success badge-sm">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                className="h-3 w-3 stroke-current"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </div>
                                        <span className="text-sm">
                                            Boa iluminação natural ou artificial
                                        </span>
                                    </div>
                                </div>

                                <div className="alert alert-warning mt-4">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="h-4 w-4 shrink-0 stroke-current"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.502 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"
                                        />
                                    </svg>
                                    <span className="text-xs">
                                        Esta foto será usada em documentos
                                        oficiais e no sistema.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Grid de 2 colunas para dados pessoais */}
                <div className="divider col-span-1 md:col-span-2">
                    Dados Pessoais
                </div>

                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {/* Gênero */}
                    <div>
                        <InputLabel htmlFor="genero" value="Gênero" />
                        <select
                            id="genero"
                            className={`select select-bordered w-full ${errors.genero ? 'select-error' : ''}`}
                            value={data.genero || ''}
                            onChange={(e) =>
                                setData('genero', e.target.value as Genero)
                            }
                        >
                            <option value="">Selecione o gênero...</option>
                            <option value="MASCULINO">Masculino</option>
                            <option value="FEMININO">Feminino</option>
                            <option value="OUTRO">Outro</option>
                            <option value="NAO_INFORMAR">
                                Prefiro não informar
                            </option>
                        </select>
                        <InputError className="mt-2" message={errors.genero} />
                    </div>

                    {/* Data de Nascimento */}
                    <div>
                        <InputLabel
                            htmlFor="data_nascimento"
                            value="Data de Nascimento"
                        />
                        <TextInput
                            id="data_nascimento"
                            type="date"
                            className={`input input-bordered w-full ${errors.data_nascimento ? 'input-error' : ''}`}
                            value={
                                data.data_nascimento
                                    ? new Date(data.data_nascimento)
                                          .toISOString()
                                          .split('T')[0]
                                    : ''
                            }
                            onChange={(e) =>
                                setData('data_nascimento', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.data_nascimento}
                        />
                    </div>

                    {/* Telefone - largura completa */}
                    <div className="col-span-1 md:col-span-2">
                        <InputLabel htmlFor="telefone" value="Telefone" />
                        <IMaskInput
                            id="telefone"
                            mask="+55 (00) 00000-0000"
                            className={`input input-bordered w-full ${errors.telefone ? 'input-error' : ''}`}
                            value={data.telefone || ''}
                            onAccept={(value) => setData('telefone', value)}
                            placeholder="+55 (00) 00000-0000"
                        />
                        <InputError
                            className="mt-2"
                            message={errors.telefone}
                        />
                    </div>
                </div>
                {/* Documentos */}
                <div className="divider col-span-1 md:col-span-2">
                    Documentos
                </div>
                <div className="col-span-1 grid grid-cols-1 gap-6 md:col-span-2 md:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="cpf" value="CPF" />
                        <IMaskInput
                            id="cpf"
                            mask="000.000.000-00"
                            className={`input input-bordered w-full ${
                                errors.cpf
                                    ? 'input-error'
                                    : cpfValido === true
                                      ? 'input-success'
                                      : cpfValido === false
                                        ? 'input-error'
                                        : ''
                            }`}
                            value={data.cpf || ''}
                            onAccept={(value) => {
                                setData('cpf', value);
                                const isValid = validarCPF(value);
                                setCpfValido(
                                    value.length === 14 ? isValid : null,
                                );
                            }}
                            required
                        />
                        {cpfValido === true && (
                            <div className="text-success mt-1 text-sm">
                                ✓ CPF válido
                            </div>
                        )}
                        {cpfValido === false && (
                            <div className="text-error mt-1 text-sm">
                                ✗ CPF inválido
                            </div>
                        )}
                        <InputError className="mt-2" message={errors.cpf} />
                    </div>
                    <div>
                        <InputLabel htmlFor="rg" value="RG" />
                        <TextInput
                            id="rg"
                            className={`input input-bordered w-full ${errors.rg ? 'input-error' : ''}`}
                            value={data.rg || ''}
                            minLength={7}
                            maxLength={9}
                            onChange={(e) => setData('rg', e.target.value)}
                            required
                        />
                        <InputError className="mt-2" message={errors.rg} />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="orgao_emissor_rg"
                            value="Órgão Emissor"
                        />
                        <SearchableSelect
                            apiUrl={route('api.orgaos-emissores.search')}
                            value={selectedOrgao}
                            onChange={(selected) => {
                                setSelectedOrgao(selected as any);
                                setData('orgao_emissor_rg', selected ? selected.sigla : '');
                            }}
                            placeholder="Selecione o órgão emissor..."
                        />
                        <InputError
                            className="mt-2"
                            message={errors.orgao_emissor_rg}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="uf_rg" value="UF do RG" />
                        <select
                            id="uf_rg"
                            className={`select select-bordered w-full ${errors.uf_rg ? 'select-error' : ''}`}
                            value={data.uf_rg || ''}
                            onChange={(e) => setData('uf_rg', e.target.value)}
                            required
                        >
                            <option value="">Selecione uma UF...</option>
                            {ESTADOS.map((uf) => (
                                <option key={uf.sigla} value={uf.sigla}>
                                    {uf.nome}
                                </option>
                            ))}
                        </select>
                        <InputError className="mt-2" message={errors.uf_rg} />
                    </div>
                </div>
                {/* Endereço */}
                <div className="divider col-span-1 md:col-span-2">Endereço</div>
                <div className="col-span-1 grid grid-cols-1 gap-6 md:col-span-2 md:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="cep" value="CEP" />
                        <IMaskInput
                            id="cep"
                            mask="00000-000"
                            className={`input input-bordered w-full ${errors.cep ? 'input-error' : ''}`}
                            value={data.cep || ''}
                            onAccept={(value) => {
                                setData('cep', value);
                                if (value.length === 9) {
                                    const cepLimpo = value.replace(/\D/g, '');
                                    viaCEP(cepLimpo);
                                }
                            }}
                            placeholder="00000-000"
                            required
                        />
                        <InputError className="mt-2" message={errors.cep} />
                    </div>
                    <div>
                        <InputLabel htmlFor="endereco" value="Endereço" />
                        <TextInput
                            id="endereco"
                            className={`input input-bordered w-full ${errors.endereco ? 'input-error' : ''}`}
                            value={data.endereco || ''}
                            onChange={(e) =>
                                setData('endereco', e.target.value)
                            }
                            required
                        />
                        <InputError
                            className="mt-2"
                            message={errors.endereco}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="numero" value="Número" />
                        <TextInput
                            id="numero"
                            className={`input input-bordered w-full ${errors.numero ? 'input-error' : ''}`}
                            value={data.numero || ''}
                            onChange={(e) => setData('numero', e.target.value)}
                            required
                        />
                        <InputError className="mt-2" message={errors.numero} />
                    </div>
                    <div>
                        <InputLabel htmlFor="complemento" value="Complemento" />
                        <TextInput
                            id="complemento"
                            className={`input input-bordered w-full ${errors.complemento ? 'input-error' : ''}`}
                            value={data.complemento || ''}
                            onChange={(e) =>
                                setData('complemento', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.complemento}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="bairro" value="Bairro" />
                        <TextInput
                            id="bairro"
                            className={`input input-bordered w-full ${errors.bairro ? 'input-error' : ''}`}
                            value={data.bairro || ''}
                            onChange={(e) => setData('bairro', e.target.value)}
                            required
                        />
                        <InputError className="mt-2" message={errors.bairro} />
                    </div>
                    <div>
                        <InputLabel htmlFor="uf" value="Estado" />
                        <select
                            id="uf"
                            className={`select select-bordered w-full ${errors.uf ? 'select-error' : ''}`}
                            value={data.uf || ''}
                            onChange={(e) => setData('uf', e.target.value)}
                            required
                        >
                            <option value="">Selecione um estado...</option>
                            {ESTADOS.map((uf_item) => (
                                <option
                                    key={uf_item.sigla}
                                    value={uf_item.sigla}
                                >
                                    {uf_item.nome}
                                </option>
                            ))}
                        </select>
                        <InputError className="mt-2" message={errors.uf} />
                    </div>
                    <div>
                        <InputLabel htmlFor="cidade" value="Cidade" />
                        <TextInput
                            id="cidade"
                            className={`input input-bordered w-full ${errors.cidade ? 'input-error' : ''}`}
                            value={data.cidade || ''}
                            onChange={(e) => setData('cidade', e.target.value)}
                            required
                        />
                        <InputError className="mt-2" message={errors.cidade} />
                    </div>
                </div>
                {/* Dados Bancários */}
                <div className="divider col-span-1 md:col-span-2">
                    Dados Bancários
                </div>
                <div className="col-span-1 grid grid-cols-1 gap-6 md:col-span-2 md:grid-cols-2">
                    <div className="col-span-1 md:col-span-2">
                        <InputLabel htmlFor="banco_id" value="Banco" />
                        <select
                            id="banco_id"
                            className={`select select-bordered w-full ${errors.banco_id ? 'select-error' : ''}`}
                            value={data.banco_id || ''}
                            onChange={(e) =>
                                setData('banco_id', e.target.value)
                            }
                        >
                            <option value="">Selecione um banco...</option>
                            {bancos.map((banco) => (
                                <option key={banco.id} value={banco.id}>
                                    {banco.codigo} - {banco.nome}
                                </option>
                            ))}
                        </select>
                        <InputError
                            className="mt-2"
                            message={errors.banco_id}
                        />
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="conta_bancaria"
                            value="Conta Bancária"
                        />
                        <IMaskInput
                            id="conta_bancaria"
                            mask="00000-0"
                            className={`input input-bordered w-full ${errors.conta_bancaria ? 'input-error' : ''}`}
                            value={data.conta_bancaria || ''}
                            onAccept={(value) =>
                                setData('conta_bancaria', value)
                            }
                            placeholder="00000-0"
                        />
                        <InputError
                            className="mt-2"
                            message={errors.conta_bancaria}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="agencia" value="Agência" />
                        <IMaskInput
                            id="agencia"
                            mask="0000-0"
                            className={`input input-bordered w-full ${errors.agencia ? 'input-error' : ''}`}
                            value={data.agencia || ''}
                            onAccept={(value) => setData('agencia', value)}
                            placeholder="0000-0"
                            required
                        />
                        <InputError className="mt-2" message={errors.agencia} />
                    </div>
                </div>
                {/* Dados Profissionais */}
                <div className="divider col-span-1 md:col-span-2">
                    Dados Profissionais
                </div>
                <div className="col-span-1 grid grid-cols-1 gap-6 md:col-span-2 md:grid-cols-2">
                    <div>
                        <InputLabel
                            htmlFor="curriculo_lattes_url"
                            value="Currículo Lattes"
                        />
                        <TextInput
                            id="curriculo_lattes_url"
                            type="url"
                            className={`input input-bordered w-full ${errors.curriculo_lattes_url ? 'input-error' : ''}`}
                            value={data.curriculo_lattes_url || ''}
                            onChange={(e) =>
                                setData('curriculo_lattes_url', e.target.value)
                            }
                            required
                        />
                        <InputError
                            className="mt-2"
                            message={errors.curriculo_lattes_url}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="linkedin_url" value="LinkedIn" />
                        <TextInput
                            id="linkedin_url"
                            type="url"
                            className={`input input-bordered w-full ${errors.linkedin_url ? 'input-error' : ''}`}
                            value={data.linkedin_url || ''}
                            onChange={(e) =>
                                setData('linkedin_url', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.linkedin_url}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="github_url" value="GitHub" />
                        <TextInput
                            id="github_url"
                            type="url"
                            className={`input input-bordered w-full ${errors.github_url ? 'input-error' : ''}`}
                            value={data.github_url || ''}
                            onChange={(e) =>
                                setData('github_url', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.github_url}
                        />
                    </div>
                    <div>
                        <InputLabel htmlFor="website_url" value="Website" />
                        <TextInput
                            id="website_url"
                            type="url"
                            className={`input input-bordered w-full ${errors.website_url ? 'input-error' : ''}`}
                            value={data.website_url || ''}
                            onChange={(e) =>
                                setData('website_url', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.website_url}
                        />
                    </div>

                    {/* Área de Atuação */}
                    <div>
                        <MultiSelect
                            id="area_atuacao"
                            label="Área de Atuação"
                            options={AREAS_ATUACAO}
                            value={data.area_atuacao ?? []}
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
                            value={data.tecnologias ?? []}
                            onChange={(value) => setData('tecnologias', value)}
                            error={errors.tecnologias}
                            placeholder="Selecione as tecnologias que você domina..."
                            maxSelections={5}
                        />
                    </div>
                </div>

                {/* Verificação de e-mail */}
                {mustVerifyEmail && user.email_verified_at === null && (
                    <div className="alert alert-warning">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-6 w-6 shrink-0 stroke-current"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                        <div>
                            <p>Seu endereço de e-mail não está verificado.</p>
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="link link-hover link-primary"
                            >
                                Clique aqui para reenviar o e-mail de
                                verificação.
                            </Link>
                            {status === 'verification-link-sent' && (
                                <div className="text-success mt-2 text-sm font-medium">
                                    Um novo link de verificação foi enviado para
                                    seu endereço de e-mail.
                                </div>
                            )}
                        </div>
                    </div>
                )}
                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Salvar</PrimaryButton>
                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-success text-sm">Salvo.</p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
