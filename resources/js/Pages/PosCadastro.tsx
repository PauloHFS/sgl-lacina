import { BANCOS, ESTADOS } from '@/constants';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';
import { IMaskInput } from 'react-imask';
import Select from 'react-select';

interface ColaboradorData {
    // documentos
    cpf: string;
    rg: string;
    uf_rg: string;
    orgao_emissor_rg: string;

    // endereco
    cep: string;
    logradouro: string;
    numero: string;
    complemento: string;
    bairro: string;
    cidade: string;
    estado: string;

    // dados de contato
    telefone: string;

    // dados bancarios
    conta_bancaria: string;
    agencia: string;
    codigo_banco: string;

    // dados profissionais
    linkedin_url: string;
    github_url: string;
    figma_url: string;
    curriculo: string;
    area_atuacao: string;
    tecnologias: string;

    // // dados academicos
    // matricula: string;
    // periodo_entrada: string;
    // periodo_conclusao: string;
}

export default function PosCadastro() {
    const { data, setData, post, processing, errors } =
        useForm<ColaboradorData>({
            // documentos
            rg: '',
            uf_rg: '',
            orgao_emissor_rg: '',
            cpf: '',

            // endereco
            cep: '',
            logradouro: '',
            numero: '',
            complemento: '',
            bairro: '',
            cidade: '',
            estado: '',

            // dados de contato
            telefone: '',

            // dados bancarios
            conta_bancaria: '',
            agencia: '',
            codigo_banco: '',

            // dados profissionais
            curriculo: '',
            linkedin_url: '',
            github_url: '',
            figma_url: '',
            area_atuacao: '',
            tecnologias: '',

            // // dados academicos
            // matricula: '',
            // periodo_entrada: '',
            // periodo_conclusao: '',
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
                    setData('estado', res.estado);
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
            onSuccess: () => {
                alert('Cadastro realizado com sucesso!');
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
            }
        >
            <div className="py-12">
                <Head title="Cadastro de Discente" />

                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 bg-white p-6">
                            <h1 className="mb-6 text-2xl font-bold">
                                Cadastro de Discente
                            </h1>

                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    {/* Documentos */}
                                    <div className="col-span-2">
                                        <h2 className="mb-4 border-b pb-2 text-lg font-semibold">
                                            Documentos
                                        </h2>
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="cpf"
                                        >
                                            CPF*
                                        </label>
                                        <IMaskInput
                                            id="cpf"
                                            mask="000.000.000-00"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.cpf ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.cpf}
                                            onAccept={(value: string) =>
                                                setData('cpf', value)
                                            }
                                            required
                                        />
                                        {errors.cpf && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.cpf}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="rg"
                                        >
                                            RG*
                                        </label>
                                        <input
                                            id="rg"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.rg ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.rg}
                                            minLength={7}
                                            maxLength={9}
                                            onChange={(e) =>
                                                setData('rg', e.target.value)
                                            }
                                            required
                                        />
                                        {errors.rg && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.rg}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="rg"
                                        >
                                            Orgão Emissor*
                                        </label>
                                        <input
                                            id="orgao_emissor_rg"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.orgao_emissor_rg ? 'border-red-500' : 'border-gray-300'}`}
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
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.orgao_emissor_rg}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="uf_rg"
                                        >
                                            UF do RG*
                                        </label>
                                        <Select
                                            id="uf_rg"
                                            options={ESTADOS.map((uf) => ({
                                                value: uf.sigla,
                                                label: uf.nome,
                                            }))}
                                            onChange={(e) =>
                                                setData('uf_rg', e?.value || '')
                                            }
                                            placeholder="Selecione uma UF..."
                                            isSearchable
                                            required
                                        />
                                        {errors.uf_rg && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.uf_rg}
                                            </p>
                                        )}
                                    </div>

                                    {/* Endereço */}
                                    <div className="col-span-2">
                                        <h2 className="mb-4 mt-4 border-b pb-2 text-lg font-semibold">
                                            Endereço
                                        </h2>
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="cep"
                                        >
                                            CEP
                                        </label>
                                        <IMaskInput
                                            id="cep"
                                            mask="00000-000"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.cep ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.cep}
                                            onAccept={(value: string) =>
                                                setData('cep', value)
                                            }
                                            onChange={handleCepChange}
                                            placeholder="00000-000"
                                        />
                                        {errors.cep && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.cep}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="logradouro"
                                        >
                                            Logradouro
                                        </label>
                                        <input
                                            id="logradouro"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.logradouro ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.logradouro}
                                            onChange={(e) =>
                                                setData(
                                                    'logradouro',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.logradouro && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.logradouro}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="numero"
                                        >
                                            Número
                                        </label>
                                        <input
                                            id="numero"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.numero ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.numero}
                                            onChange={(e) =>
                                                setData(
                                                    'numero',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.numero && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.numero}
                                            </p>
                                        )}
                                    </div>
                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="complemento"
                                        >
                                            Complemento
                                        </label>
                                        <input
                                            id="complemento"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.complemento ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.complemento}
                                            onChange={(e) =>
                                                setData(
                                                    'complemento',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.complemento && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.complemento}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="bairro"
                                        >
                                            Bairro
                                        </label>
                                        <input
                                            id="bairro"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.bairro ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.bairro}
                                            onChange={(e) =>
                                                setData(
                                                    'bairro',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.bairro && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.bairro}
                                            </p>
                                        )}
                                    </div>
                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="estado"
                                        >
                                            Estado
                                        </label>
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
                                            required
                                        />
                                        {errors.estado && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.estado}
                                            </p>
                                        )}
                                    </div>
                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="cidade"
                                        >
                                            Cidade
                                        </label>
                                        <input
                                            id="cidade"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.cidade ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.cidade}
                                            onChange={(e) =>
                                                setData(
                                                    'cidade',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.cidade && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.cidade}
                                            </p>
                                        )}
                                    </div>

                                    {/* Dados Contato */}
                                    <div className="col-span-2">
                                        <h2 className="mb-4 mt-4 border-b pb-2 text-lg font-semibold">
                                            Dados de Contato
                                        </h2>
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="telefone"
                                        >
                                            Telefone
                                        </label>
                                        <input
                                            id="telefone"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.telefone ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.telefone}
                                            onChange={(e) =>
                                                setData(
                                                    'telefone',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.telefone && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.telefone}
                                            </p>
                                        )}
                                    </div>

                                    {/* Dados Bancários */}
                                    <div className="col-span-2">
                                        <h2 className="mb-4 mt-4 border-b pb-2 text-lg font-semibold">
                                            Dados Bancários
                                        </h2>
                                    </div>

                                    <div className="w-full max-w-md">
                                        <label className="mb-2 block text-sm font-medium text-gray-700">
                                            Banco
                                        </label>
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
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="conta_bancaria"
                                        >
                                            Conta Bancária
                                        </label>
                                        <IMaskInput
                                            id="conta_bancaria"
                                            mask="00000-0"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.conta_bancaria ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.conta_bancaria}
                                            onAccept={(value: string) =>
                                                setData('conta_bancaria', value)
                                            }
                                            placeholder="00000-0"
                                        />
                                        {errors.conta_bancaria && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.conta_bancaria}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="agencia"
                                        >
                                            Agência
                                        </label>
                                        <IMaskInput
                                            id="agencia"
                                            mask="0000-0"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.agencia ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.agencia}
                                            onAccept={(value: string) =>
                                                setData('agencia', value)
                                            }
                                            placeholder="0000-0"
                                        />
                                        {errors.agencia && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.agencia}
                                            </p>
                                        )}
                                    </div>

                                    {/* Dados Profissionais */}
                                    <div className="col-span-2">
                                        <h2 className="mb-4 mt-4 border-b pb-2 text-lg font-semibold">
                                            Dados Profissionais
                                        </h2>
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="curriculo"
                                        >
                                            Currículo Lattes
                                        </label>
                                        <input
                                            id="curriculo"
                                            type="url"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.curriculo ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.curriculo}
                                            onChange={(e) =>
                                                setData(
                                                    'curriculo',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.curriculo && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.curriculo}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="linkedin"
                                        >
                                            LinkedIn
                                        </label>
                                        <input
                                            id="linkedin"
                                            type="url"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.linkedin_url ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.linkedin_url}
                                            onChange={(e) =>
                                                setData(
                                                    'linkedin_url',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.linkedin_url && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.linkedin_url}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="github"
                                        >
                                            GitHub
                                        </label>
                                        <input
                                            id="github"
                                            type="url"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.github_url ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.github_url}
                                            onChange={(e) =>
                                                setData(
                                                    'github_url',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.github_url && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.github_url}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="figma"
                                        >
                                            Figma
                                        </label>
                                        <input
                                            id="figma"
                                            type="url"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.figma_url ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.figma_url}
                                            onChange={(e) =>
                                                setData(
                                                    'figma_url',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.figma_url && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.figma_url}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="area_atuacao"
                                        >
                                            Área de Atuação
                                        </label>
                                        <input
                                            id="area_atuacao"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.area_atuacao ? 'border-red-500' : 'border-gray-300'}`}
                                            value={data.area_atuacao}
                                            onChange={(e) =>
                                                setData(
                                                    'area_atuacao',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {errors.area_atuacao && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.area_atuacao}
                                            </p>
                                        )}
                                    </div>

                                    <div className="mb-4">
                                        <label
                                            className="mb-2 block text-sm font-bold text-gray-700"
                                            htmlFor="tecnologias"
                                        >
                                            Tecnologias
                                        </label>
                                        <input
                                            id="tecnologias"
                                            type="text"
                                            className={`w-full rounded-md border px-3 py-2 ${errors.tecnologias ? 'border-red-500' : 'border-gray-300'}`}
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
                                            <p className="mt-1 text-xs text-red-500">
                                                {errors.tecnologias}
                                            </p>
                                        )}
                                    </div>

                                    {/* Dados Acadêmicos */}
                                    {/* <div className="col-span-2">
                                    <h2 className="mb-4 mt-4 border-b pb-2 text-lg font-semibold">
                                        Dados Acadêmicos
                                    </h2>
                                </div>

                                <div className="mb-4">
                                    <label
                                        className="mb-2 block text-sm font-bold text-gray-700"
                                        htmlFor="matricula"
                                    >
                                        Matrícula*
                                    </label>
                                    <input
                                        id="matricula"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.matricula ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.matricula}
                                        onChange={(e) =>
                                            setData('matricula', e.target.value)
                                        }
                                        required
                                    />
                                    {errors.matricula && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {errors.matricula}
                                        </p>
                                    )}
                                </div>

                                <div className="mb-4">
                                    <label
                                        className="mb-2 block text-sm font-bold text-gray-700"
                                        htmlFor="periodo_entrada"
                                    >
                                        Período de Entrada
                                    </label>
                                    <input
                                        id="periodo_entrada"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.periodo_entrada ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.periodo_entrada}
                                        onChange={(e) =>
                                            setData(
                                                'periodo_entrada',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ex: 2023.1"
                                    />
                                    {errors.periodo_entrada && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {errors.periodo_entrada}
                                        </p>
                                    )}
                                </div>

                                <div className="mb-4">
                                    <label
                                        className="mb-2 block text-sm font-bold text-gray-700"
                                        htmlFor="periodo_conclusao"
                                    >
                                        Período de Conclusão (previsto)
                                    </label>
                                    <input
                                        id="periodo_conclusao"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.periodo_conclusao ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.periodo_conclusao}
                                        onChange={(e) =>
                                            setData(
                                                'periodo_conclusao',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ex: 2027.2"
                                    />
                                    {errors.periodo_conclusao && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {errors.periodo_conclusao}
                                        </p>
                                    )}
                                </div> */}

                                    <div className="col-span-2 mt-6">
                                        <button
                                            type="submit"
                                            className="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
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
