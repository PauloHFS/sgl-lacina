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
}

interface ShowProps {
    colaborador: Colaborador;
}

export default function Show({ colaborador }: ShowProps) {
    return (
        <Authenticated header="Detalhes do Colaborador">
            <Head title={`Colaborador: ${colaborador.name}`} />
            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow">
                        <div className="card-body">
                            <div className="mb-6 flex items-center space-x-6">
                                <div className="avatar">
                                    <div className="ring-primary ring-offset-base-100 h-24 w-24 rounded-full ring ring-offset-2">
                                        <img
                                            src={
                                                colaborador.foto_url ||
                                                'https://robohash.org/set1/' +
                                                    colaborador.name +
                                                    '.png'
                                            }
                                            alt={`Foto de ${colaborador.name}`}
                                            className="object-cover"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <h2 className="card-title">
                                        {colaborador.name}
                                    </h2>
                                    <p className="text-base-content/70">
                                        {colaborador.email}
                                    </p>
                                    <div className="mt-2 flex space-x-2">
                                        {colaborador.linkedin_url && (
                                            <a
                                                href={colaborador.linkedin_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="btn btn-xs btn-outline btn-primary"
                                            >
                                                LinkedIn
                                            </a>
                                        )}
                                        {colaborador.github_url && (
                                            <a
                                                href={colaborador.github_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="btn btn-xs btn-outline btn-neutral"
                                            >
                                                GitHub
                                            </a>
                                        )}
                                        {colaborador.figma_url && (
                                            <a
                                                href={colaborador.figma_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="btn btn-xs btn-outline btn-accent"
                                            >
                                                Figma
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                            <div className="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                                      className="badge badge-info badge-outline mr-1"
                                                  >
                                                      {tech.trim()}
                                                  </span>
                                              ))
                                        : '-'}
                                </div>
                                <div>
                                    <span className="font-semibold">
                                        Telefone:
                                    </span>{' '}
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
                                    <span className="font-semibold">
                                        UF RG:
                                    </span>{' '}
                                    {colaborador.uf_rg || '-'}
                                </div>
                                <div>
                                    <span className="font-semibold">
                                        Conta Bancária:
                                    </span>{' '}
                                    {colaborador.conta_bancaria || '-'}
                                </div>
                                <div>
                                    <span className="font-semibold">
                                        Agência:
                                    </span>{' '}
                                    {colaborador.agencia || '-'}
                                </div>
                                <div>
                                    <span className="font-semibold">
                                        Código do Banco:
                                    </span>{' '}
                                    {colaborador.codigo_banco || '-'}
                                </div>
                                <div className="md:col-span-2">
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
                                    <div className="alert alert-warning flex-col items-start gap-2">
                                        <span className="text-warning-content font-semibold">
                                            Vínculo pendente de aprovação.
                                        </span>
                                        <div className="flex gap-2">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    router.post(
                                                        route(
                                                            'colaboradores.aceitar',
                                                            colaborador.id,
                                                        ),
                                                    )
                                                }
                                                className="btn btn-success btn-sm"
                                            >
                                                Aceitar
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    router.post(
                                                        route(
                                                            'colaboradores.recusar',
                                                            colaborador.id,
                                                        ),
                                                    )
                                                }
                                                className="btn btn-error btn-sm"
                                            >
                                                Recusar
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {colaborador.status_cadastro ===
                                    'APROVACAO_PENDENTE' && (
                                    <div className="alert alert-info flex-col items-start gap-2">
                                        <span className="text-info-content font-semibold">
                                            Aprovação pendente de vínculo em
                                            projeto.
                                        </span>
                                        <div>
                                            <span className="font-semibold">
                                                Projeto solicitado:
                                            </span>{' '}
                                            <span>
                                                {colaborador.projeto_solicitado
                                                    ?.nome || '-'}
                                            </span>
                                        </div>
                                        <div className="flex gap-2">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    router.post(
                                                        route(
                                                            'vinculos.aceitar',
                                                            colaborador.id,
                                                        ),
                                                    )
                                                }
                                                className="btn btn-success btn-sm"
                                            >
                                                Aceitar
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    router.post(
                                                        route(
                                                            'vinculos.recusar',
                                                            colaborador.id,
                                                        ),
                                                    )
                                                }
                                                className="btn btn-error btn-sm"
                                            >
                                                Recusar
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {colaborador.status_cadastro === 'ATIVO' && (
                                    <div className="alert alert-success flex-col items-start gap-2">
                                        <span className="text-success-content font-semibold">
                                            Colaborador ativo
                                        </span>
                                        <div>
                                            <span className="font-semibold">
                                                Projeto(s) atual(is):
                                            </span>{' '}
                                            {colaborador.projetos_atuais &&
                                            colaborador.projetos_atuais.length >
                                                0 ? (
                                                colaborador.projetos_atuais.map(
                                                    (proj) => (
                                                        <span
                                                            key={proj.id}
                                                            className="badge badge-success badge-outline mr-1"
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
                                    <div className="alert alert-outline flex-col items-start gap-2">
                                        <span className="text-base-content font-semibold">
                                            Colaborador inativo
                                        </span>
                                        <span className="text-base-content/70">
                                            Este colaborador não possui vínculo
                                            ativo no momento.
                                        </span>
                                    </div>
                                )}
                            </div>
                            <div className="mt-6">
                                <Link
                                    href={route('colaboradores.index')}
                                    className="link link-primary"
                                >
                                    Voltar para a lista
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
