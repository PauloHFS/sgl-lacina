import { Head, useForm } from '@inertiajs/react';
import React from 'react';

interface ColaboradorData {
    // documentos
    cpf: string;
    rg: string;
    uf_rg: string;
    orgao_emissor_rg: string;

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

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/profile/update', {
            onSuccess: () => {
                alert('Cadastro realizado com sucesso!');
            },
        });
    };

    return (
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
                                        htmlFor="rg"
                                    >
                                        RG*
                                    </label>
                                    <input
                                        id="rg"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.rg ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.rg}
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
                                        htmlFor="uf_rg"
                                    >
                                        UF do RG*
                                    </label>
                                    <input
                                        id="uf_rg"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.uf_rg ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.uf_rg}
                                        onChange={(e) =>
                                            setData('uf_rg', e.target.value)
                                        }
                                        required
                                    />
                                    {errors.uf_rg && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {errors.uf_rg}
                                        </p>
                                    )}
                                </div>

                                <div className="mb-4">
                                    <label
                                        className="mb-2 block text-sm font-bold text-gray-700"
                                        htmlFor="cpf"
                                    >
                                        CPF*
                                    </label>
                                    <input
                                        id="cpf"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.cpf ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.cpf}
                                        onChange={(e) =>
                                            setData('cpf', e.target.value)
                                        }
                                        required
                                        placeholder="000.000.000-00"
                                    />
                                    {errors.cpf && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {errors.cpf}
                                        </p>
                                    )}
                                </div>

                                {/* Dados Pessoais */}
                                <div className="col-span-2">
                                    <h2 className="mb-4 mt-4 border-b pb-2 text-lg font-semibold">
                                        Dados de Contato
                                    </h2>
                                </div>

                                {/* Dados Bancários */}
                                <div className="col-span-2">
                                    <h2 className="mb-4 mt-4 border-b pb-2 text-lg font-semibold">
                                        Dados Bancários
                                    </h2>
                                </div>

                                <div className="mb-4">
                                    <label
                                        className="mb-2 block text-sm font-bold text-gray-700"
                                        htmlFor="conta_bancaria"
                                    >
                                        Conta Bancária
                                    </label>
                                    <input
                                        id="conta_bancaria"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.conta_bancaria ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.conta_bancaria}
                                        onChange={(e) =>
                                            setData(
                                                'conta_bancaria',
                                                e.target.value,
                                            )
                                        }
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
                                    <input
                                        id="agencia"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.agencia ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.agencia}
                                        onChange={(e) =>
                                            setData('agencia', e.target.value)
                                        }
                                    />
                                    {errors.agencia && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {errors.agencia}
                                        </p>
                                    )}
                                </div>

                                <div className="mb-4">
                                    <label
                                        className="mb-2 block text-sm font-bold text-gray-700"
                                        htmlFor="codigo_banco"
                                    >
                                        Código do Banco
                                    </label>
                                    <input
                                        id="codigo_banco"
                                        type="text"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.codigo_banco ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.codigo_banco}
                                        onChange={(e) =>
                                            setData(
                                                'codigo_banco',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    {errors.codigo_banco && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {errors.codigo_banco}
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
                                        Link para Currículo
                                    </label>
                                    <input
                                        id="curriculo"
                                        type="url"
                                        className={`w-full rounded-md border px-3 py-2 ${errors.curriculo ? 'border-red-500' : 'border-gray-300'}`}
                                        value={data.curriculo}
                                        onChange={(e) =>
                                            setData('curriculo', e.target.value)
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
                                            setData('figma_url', e.target.value)
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
    );
}
