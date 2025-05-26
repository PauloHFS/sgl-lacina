import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { ESTADOS } from '@/constants';
import { Transition } from '@headlessui/react';
import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { IMaskInput } from 'react-imask';

interface ProfileFormData {
    name: string;
    email: string;
    foto_url: File | null;
    genero: string;
    data_nascimento: string;
    cpf: string;
    rg: string;
    uf_rg: string;
    orgao_emissor_rg: string;
    cep: string;
    endereco: string;
    numero: string;
    complemento: string;
    bairro: string;
    cidade: string;
    uf: string;
    telefone: string;
    conta_bancaria: string;
    agencia: string;
    banco_id: string;
    curriculo_lattes_url: string;
    linkedin_url: string;
    github_url: string;
    figma_url: string;
    area_atuacao: string;
    tecnologias: string;
}

interface Banco {
    id: string;
    nome: string;
    codigo: string;
}

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    bancos = [],
    className = '',
}: {
    mustVerifyEmail: boolean;
    status?: string;
    bancos?: Banco[];
    className?: string;
}) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm<ProfileFormData>({
            name: user.name || '',
            email: user.email || '',
            foto_url: null,
            genero: user.genero ?? '',
            data_nascimento: user.data_nascimento ?? '',
            cpf: user.cpf ?? '',
            rg: user.rg ?? '',
            uf_rg: user.uf_rg ?? '',
            orgao_emissor_rg: user.orgao_emissor_rg ?? '',
            cep: user.cep ?? '',
            endereco: user.endereco ?? '',
            numero: user.numero ?? '',
            complemento: user.complemento ?? '',
            bairro: user.bairro ?? '',
            cidade: user.cidade ?? '',
            uf: user.uf ?? '',
            telefone: user.telefone ?? '',
            conta_bancaria: user.conta_bancaria ?? '',
            agencia: user.agencia ?? '',
            banco_id: user.banco_id ?? '',
            curriculo_lattes_url: user.curriculo_lattes_url ?? '',
            linkedin_url: user.linkedin_url ?? '',
            github_url: user.github_url ?? '',
            figma_url: user.figma_url ?? '',
            area_atuacao: user.area_atuacao ?? '',
            tecnologias: user.tecnologias ?? '',
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        // Remove null/undefined, converte tudo para string
        const cleanedData = Object.fromEntries(
            Object.entries(data).map(([k, v]) => [
                k,
                v === null || v === undefined ? '' : v,
            ]),
        );
        patch(route('profile.update'), {
            data: cleanedData,
            forceFormData: true,
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
                {/* Nome e E-mail */}
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
                {/* Foto de Perfil */}
                <div>
                    <InputLabel htmlFor="foto_url" value="Foto de Perfil" />
                    <input
                        id="foto_url"
                        type="file"
                        accept="image/*"
                        className={`file-input file-input-bordered w-full ${errors.foto_url ? 'file-input-error' : ''}`}
                        onChange={(e) => {
                            const file =
                                e.target.files && e.target.files[0]
                                    ? e.target.files[0]
                                    : null;
                            setData('foto_url', file);
                        }}
                    />
                    <InputError className="mt-2" message={errors.foto_url} />
                </div>
                {/* Gênero */}
                <div>
                    <InputLabel htmlFor="genero" value="Gênero" />
                    <select
                        id="genero"
                        className={`select select-bordered w-full ${errors.genero ? 'select-error' : ''}`}
                        value={data.genero || ''}
                        onChange={(e) => setData('genero', e.target.value)}
                    >
                        <option value="" disabled>
                            Selecione o gênero...
                        </option>
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
                        value={data.data_nascimento || ''}
                        onChange={(e) =>
                            setData('data_nascimento', e.target.value)
                        }
                    />
                    <InputError
                        className="mt-2"
                        message={errors.data_nascimento}
                    />
                </div>
                {/* Telefone */}
                <div>
                    <InputLabel htmlFor="telefone" value="Telefone" />
                    <IMaskInput
                        id="telefone"
                        mask="+55 (00) 00000-0000"
                        className={`input input-bordered w-full ${errors.telefone ? 'input-error' : ''}`}
                        value={data.telefone}
                        onAccept={(value) => setData('telefone', value)}
                        placeholder="+55 (00) 00000-0000"
                    />
                    <InputError className="mt-2" message={errors.telefone} />
                </div>
                {/* Documentos */}
                <div className="divider">Documentos</div>
                <div>
                    <InputLabel htmlFor="cpf" value="CPF" />
                    <IMaskInput
                        id="cpf"
                        mask="000.000.000-00"
                        className={`input input-bordered w-full ${errors.cpf ? 'input-error' : ''}`}
                        value={data.cpf}
                        onAccept={(value) => setData('cpf', value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.cpf} />
                </div>
                <div>
                    <InputLabel htmlFor="rg" value="RG" />
                    <TextInput
                        id="rg"
                        className={`input input-bordered w-full ${errors.rg ? 'input-error' : ''}`}
                        value={data.rg}
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
                        value="Orgão Emissor"
                    />
                    <TextInput
                        id="orgao_emissor_rg"
                        className={`input input-bordered w-full ${errors.orgao_emissor_rg ? 'input-error' : ''}`}
                        value={data.orgao_emissor_rg}
                        onChange={(e) =>
                            setData('orgao_emissor_rg', e.target.value)
                        }
                        required
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
                        <option value="" disabled>
                            Selecione uma UF...
                        </option>
                        {ESTADOS.map((uf) => (
                            <option key={uf.sigla} value={uf.sigla}>
                                {uf.nome}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.uf_rg} />
                </div>
                {/* Endereço */}
                <div className="divider">Endereço</div>
                <div>
                    <InputLabel htmlFor="cep" value="CEP" />
                    <IMaskInput
                        id="cep"
                        mask="00000-000"
                        className={`input input-bordered w-full ${errors.cep ? 'input-error' : ''}`}
                        value={data.cep}
                        onAccept={(value) => setData('cep', value)}
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
                        value={data.endereco}
                        onChange={(e) => setData('endereco', e.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.endereco} />
                </div>
                <div>
                    <InputLabel htmlFor="numero" value="Número" />
                    <TextInput
                        id="numero"
                        className={`input input-bordered w-full ${errors.numero ? 'input-error' : ''}`}
                        value={data.numero}
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
                        value={data.complemento}
                        onChange={(e) => setData('complemento', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.complemento} />
                </div>
                <div>
                    <InputLabel htmlFor="bairro" value="Bairro" />
                    <TextInput
                        id="bairro"
                        className={`input input-bordered w-full ${errors.bairro ? 'input-error' : ''}`}
                        value={data.bairro}
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
                        <option value="" disabled>
                            Selecione um estado...
                        </option>
                        {ESTADOS.map((uf_item) => (
                            <option key={uf_item.sigla} value={uf_item.sigla}>
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
                        value={data.cidade}
                        onChange={(e) => setData('cidade', e.target.value)}
                        required
                    />
                    <InputError className="mt-2" message={errors.cidade} />
                </div>
                {/* Dados Bancários */}
                <div className="divider">Dados Bancários</div>
                <div>
                    <InputLabel htmlFor="banco_id" value="Banco" />
                    <select
                        id="banco_id"
                        className={`select select-bordered w-full ${errors.banco_id ? 'select-error' : ''}`}
                        value={data.banco_id || ''}
                        onChange={(e) => setData('banco_id', e.target.value)}
                    >
                        <option value="" disabled>
                            Selecione um banco...
                        </option>
                        {bancos.map((banco) => (
                            <option key={banco.id} value={banco.id}>
                                {banco.codigo} - {banco.nome}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.banco_id} />
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
                        value={data.conta_bancaria}
                        onAccept={(value) => setData('conta_bancaria', value)}
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
                        value={data.agencia}
                        onAccept={(value) => setData('agencia', value)}
                        placeholder="0000-0"
                        required
                    />
                    <InputError className="mt-2" message={errors.agencia} />
                </div>
                {/* Dados Profissionais */}
                <div className="divider">Dados Profissionais</div>
                <div>
                    <InputLabel
                        htmlFor="curriculo_lattes_url"
                        value="Currículo Lattes"
                    />
                    <TextInput
                        id="curriculo_lattes_url"
                        type="url"
                        className={`input input-bordered w-full ${errors.curriculo_lattes_url ? 'input-error' : ''}`}
                        value={data.curriculo_lattes_url}
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
                        value={data.linkedin_url}
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
                        value={data.github_url}
                        onChange={(e) => setData('github_url', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.github_url} />
                </div>
                <div>
                    <InputLabel htmlFor="figma_url" value="Figma" />
                    <TextInput
                        id="figma_url"
                        type="url"
                        className={`input input-bordered w-full ${errors.figma_url ? 'input-error' : ''}`}
                        value={data.figma_url}
                        onChange={(e) => setData('figma_url', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.figma_url} />
                </div>
                <div>
                    <InputLabel
                        htmlFor="area_atuacao"
                        value="Área de Atuação"
                    />
                    <TextInput
                        id="area_atuacao"
                        className={`input input-bordered w-full ${errors.area_atuacao ? 'input-error' : ''}`}
                        value={data.area_atuacao}
                        onChange={(e) =>
                            setData('area_atuacao', e.target.value)
                        }
                        placeholder="Digite sua área de atuação..."
                    />
                    <InputError
                        className="mt-2"
                        message={errors.area_atuacao}
                    />
                </div>
                <div>
                    <InputLabel htmlFor="tecnologias" value="Tecnologias" />
                    <TextInput
                        id="tecnologias"
                        className={`input input-bordered w-full ${errors.tecnologias ? 'input-error' : ''}`}
                        value={data.tecnologias}
                        onChange={(e) => setData('tecnologias', e.target.value)}
                        placeholder="Digite as tecnologias que você domina..."
                    />
                    <InputError className="mt-2" message={errors.tecnologias} />
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
