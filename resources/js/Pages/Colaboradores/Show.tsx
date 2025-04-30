import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface Projeto {
    id: number;
    nome: string;
}

interface Colaborador {
    id: number;
    name: string;
    email: string;
    linkedin_url?: string;
    github_url?: string;
    figma_url?: string;
    foto_url?: string;
    area_atuacao?: string;
    tecnologias?: string;
    curriculo?: string;
    cpf?: string;
    conta_bancaria?: string;
    agencia?: string;
    codigo_banco?: string;
    rg?: string;
    uf_rg?: string;
    telefone?: string;
    created_at: string;
    updated_at: string;
    status_cadastro:
        | 'VINCULO_PENDENTE'
        | 'APROVACAO_PENDENTE'
        | 'ATIVO'
        | 'INATIVO';
    projeto_solicitado?: Projeto | null;
    projetos_atuais?: Projeto[];
    // Adicione outros campos conforme necessário
}

interface ShowProps {
    colaborador: Colaborador;
}

export default function Show({ colaborador }: ShowProps) {
    console.log(colaborador);
    return (
        <Authenticated header="Detalhes do Colaborador">
            <Head title={`Colaborador: ${colaborador.name}`} />
            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <div className="mb-6 flex items-center space-x-6">
                            <img
                                src={
                                    colaborador.foto_url ||
                                    'https://robohash.org/set1/' +
                                        colaborador.name +
                                        '.png'
                                }
                                alt={`Foto de ${colaborador.name}`}
                                className="h-24 w-24 rounded-full object-cover"
                            />
                            <div>
                                <h2 className="text-2xl font-bold">
                                    {colaborador.name}
                                </h2>
                                <p className="text-gray-600">
                                    {colaborador.email}
                                </p>
                                <div className="mt-2 flex space-x-2">
                                    {colaborador.linkedin_url && (
                                        <a
                                            href={colaborador.linkedin_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-indigo-600 hover:underline"
                                        >
                                            LinkedIn
                                        </a>
                                    )}
                                    {colaborador.github_url && (
                                        <a
                                            href={colaborador.github_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-indigo-600 hover:underline"
                                        >
                                            GitHub
                                        </a>
                                    )}
                                    {colaborador.figma_url && (
                                        <a
                                            href={colaborador.figma_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-indigo-600 hover:underline"
                                        >
                                            Figma
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <div>
                                <span className="font-semibold">
                                    Área de Atuação:
                                </span>{' '}
                                {colaborador.area_atuacao || '-'}
                            </div>
                            <div>
                                <span className="font-semibold">
                                    Tecnologias:
                                </span>{' '}
                                {colaborador.tecnologias
                                    ? colaborador.tecnologias
                                          .split(',')
                                          .map((tech, idx) => (
                                              <span
                                                  key={idx}
                                                  className="mr-1 inline-block rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800"
                                              >
                                                  {tech.trim()}
                                              </span>
                                          ))
                                    : '-'}
                            </div>
                            <div>
                                <span className="font-semibold">Telefone:</span>{' '}
                                {colaborador.telefone || '-'}
                            </div>
                            <div>
                                <span className="font-semibold">CPF:</span>{' '}
                                {colaborador.cpf || '-'}
                            </div>
                            <div>
                                <span className="font-semibold">RG:</span>{' '}
                                {colaborador.rg || '-'}
                            </div>
                            <div>
                                <span className="font-semibold">UF RG:</span>{' '}
                                {colaborador.uf_rg || '-'}
                            </div>
                            <div>
                                <span className="font-semibold">
                                    Conta Bancária:
                                </span>{' '}
                                {colaborador.conta_bancaria || '-'}
                            </div>
                            <div>
                                <span className="font-semibold">Agência:</span>{' '}
                                {colaborador.agencia || '-'}
                            </div>
                            <div>
                                <span className="font-semibold">
                                    Código do Banco:
                                </span>{' '}
                                {colaborador.codigo_banco || '-'}
                            </div>
                            <div>
                                <span className="font-semibold">
                                    Currículo:
                                </span>{' '}
                                {colaborador.curriculo || '-'}
                            </div>
                        </div>
                        {/* STATUS DO COLABORADOR */}
                        <div className="mb-6">
                            {colaborador.status_cadastro ===
                                'VINCULO_PENDENTE' && (
                                <div className="mb-2 rounded bg-yellow-50 p-4">
                                    <div className="mb-2 font-semibold text-yellow-800">
                                        Vínculo Pendente
                                    </div>
                                    <div className="flex space-x-2">
                                        <button
                                            onClick={() => {
                                                router.post(
                                                    route(
                                                        'colaboradores.aceitar',
                                                        colaborador.id,
                                                    ),
                                                );
                                            }}
                                            className="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                                        >
                                            Aceitar
                                        </button>
                                        <button
                                            onClick={() => {
                                                router.post(
                                                    route(
                                                        'colaboradores.recusar',
                                                        colaborador.id,
                                                    ),
                                                );
                                            }}
                                            className="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                                        >
                                            Recusar
                                        </button>
                                    </div>
                                </div>
                            )}
                            {colaborador.status_cadastro ===
                                'APROVACAO_PENDENTE' && (
                                <div className="mb-2 rounded bg-blue-50 p-4">
                                    <div className="mb-2 font-semibold text-blue-800">
                                        Aprovação Pendente
                                    </div>
                                    <div className="mb-2">
                                        Projeto solicitado:{' '}
                                        <span className="font-semibold">
                                            {colaborador.projeto_solicitado
                                                ? colaborador.projeto_solicitado
                                                      .nome
                                                : '-'}
                                        </span>
                                    </div>
                                    <div className="flex space-x-2">
                                        <button
                                            onClick={() => {
                                                router.post(
                                                    route(
                                                        'vinculos.aceitar',
                                                        colaborador.id,
                                                    ),
                                                );
                                            }}
                                            className="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                                        >
                                            Aceitar
                                        </button>
                                        <button
                                            onClick={() => {
                                                router.post(
                                                    route(
                                                        'vinculos.recusar',
                                                        colaborador.id,
                                                    ),
                                                );
                                            }}
                                            className="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                                        >
                                            Recusar
                                        </button>
                                    </div>
                                </div>
                            )}
                            {colaborador.status_cadastro === 'ATIVO' && (
                                <div className="mb-2 rounded bg-green-50 p-4">
                                    <div className="mb-2 font-semibold text-green-800">
                                        Ativo
                                    </div>
                                    <div>
                                        Projeto(s) atual(is):{' '}
                                        {colaborador.projetos_atuais &&
                                        colaborador.projetos_atuais.length >
                                            0 ? (
                                            colaborador.projetos_atuais.map(
                                                (proj) => (
                                                    <span
                                                        key={proj.id}
                                                        className="mr-1 inline-block rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800"
                                                    >
                                                        {proj.nome}
                                                    </span>
                                                ),
                                            )
                                        ) : (
                                            <span>-</span>
                                        )}
                                    </div>
                                </div>
                            )}
                            {colaborador.status_cadastro === 'INATIVO' && (
                                <div className="mb-2 rounded bg-gray-100 p-4">
                                    <div className="font-semibold text-gray-700">
                                        Inativo
                                    </div>
                                    <div>
                                        Este colaborador não possui vínculo
                                        ativo no momento.
                                    </div>
                                </div>
                            )}
                        </div>
                        <div className="mt-6">
                            <Link
                                href={route('colaboradores.index')}
                                className="text-indigo-600 hover:underline"
                            >
                                Voltar para a lista
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
