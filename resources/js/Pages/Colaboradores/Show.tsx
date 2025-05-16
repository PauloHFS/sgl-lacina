import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

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
                    <div className="card card-bordered bg-base-100 shadow-xl">
                        <div className="card-body">
                            {/* Header Section */}
                            <div className="flex flex-col items-center gap-6 sm:flex-row">
                                {colaborador.foto_url ? (
                                    <div className="avatar">
                                        <div className="ring-primary ring-offset-base-100 w-24 rounded-full ring ring-offset-2">
                                            <img
                                                src={`/storage/${colaborador.foto_url}`}
                                                alt={`Foto de ${colaborador.name}`}
                                            />
                                        </div>
                                    </div>
                                ) : (
                                    <div className="avatar avatar-placeholder">
                                        <div className="bg-neutral text-neutral-content ring-primary ring-offset-base-100 w-24 rounded-full ring ring-offset-2">
                                            <span className="text-3xl">
                                                {colaborador.name
                                                    .charAt(0)
                                                    .toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                )}
                                <div className="text-center sm:text-left">
                                    <h2 className="card-title text-2xl">
                                        {colaborador.name}
                                    </h2>
                                    <p className="text-base-content/70">
                                        {colaborador.email}
                                    </p>
                                    <div className="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
                                        {colaborador.linkedin_url && (
                                            <a
                                                href={colaborador.linkedin_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="btn btn-sm btn-outline btn-primary"
                                            >
                                                LinkedIn
                                            </a>
                                        )}
                                        {colaborador.github_url && (
                                            <a
                                                href={colaborador.github_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="btn btn-sm btn-outline btn-neutral"
                                            >
                                                GitHub
                                            </a>
                                        )}
                                        {colaborador.figma_url && (
                                            <a
                                                href={colaborador.figma_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="btn btn-sm btn-outline btn-accent"
                                            >
                                                Figma
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="divider">Detalhes</div>

                            {/* Details Section */}
                            <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            Área de Atuação:
                                        </span>
                                    </label>
                                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                        {colaborador.area_atuacao || '-'}
                                    </span>
                                </div>
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            Tecnologias:
                                        </span>
                                    </label>
                                    <div className="input input-bordered flex h-auto min-h-10 flex-wrap items-center gap-1 py-2 break-words whitespace-normal">
                                        {colaborador.tecnologias
                                            ? colaborador.tecnologias
                                                  .split(',')
                                                  .map((tech, idx) => (
                                                      <span
                                                          key={idx}
                                                          className="badge badge-info badge-outline"
                                                      >
                                                          {tech.trim()}
                                                      </span>
                                                  ))
                                            : '-'}
                                    </div>
                                </div>
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            Telefone:
                                        </span>
                                    </label>
                                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                        {colaborador.telefone || '-'}
                                    </span>
                                </div>
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            CPF:
                                        </span>
                                    </label>
                                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                        {colaborador.cpf || '-'}
                                    </span>
                                </div>
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            RG:
                                        </span>
                                    </label>
                                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                        {colaborador.rg || '-'}
                                    </span>
                                </div>
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            UF RG:
                                        </span>
                                    </label>
                                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                        {colaborador.uf_rg || '-'}
                                    </span>
                                </div>
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            Conta Bancária:
                                        </span>
                                    </label>
                                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                        {colaborador.conta_bancaria || '-'}
                                    </span>
                                </div>
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            Agência:
                                        </span>
                                    </label>
                                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                        {colaborador.agencia || '-'}
                                    </span>
                                </div>
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            Código do Banco:
                                        </span>
                                    </label>
                                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                                        {colaborador.codigo_banco || '-'}
                                    </span>
                                </div>
                                <div className="form-control md:col-span-2">
                                    <label className="label">
                                        <span className="label-text font-semibold">
                                            Currículo:
                                        </span>
                                    </label>
                                    <span className="textarea textarea-bordered flex h-auto min-h-24 py-2 break-words whitespace-normal">
                                        {colaborador.curriculo || '-'}
                                    </span>
                                </div>
                            </div>

                            {/* STATUS DO COLABORADOR */}
                            <div className="divider">Status</div>
                            <div className="my-6">
                                {colaborador.status_cadastro ===
                                    'VINCULO_PENDENTE' && (
                                    <div className="alert alert-warning shadow-lg">
                                        <div>
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-6 w-6 flex-shrink-0 stroke-current"
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
                                                <h3 className="font-bold">
                                                    Vínculo pendente de
                                                    aprovação.
                                                </h3>
                                                <div className="text-xs">
                                                    Este colaborador aguarda
                                                    aprovação de cadastro.
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex-none">
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
                                                className="btn btn-sm btn-success"
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
                                                className="btn btn-sm btn-error ml-2"
                                            >
                                                Recusar
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {colaborador.status_cadastro ===
                                    'APROVACAO_PENDENTE' && (
                                    <div className="alert alert-info shadow-lg">
                                        <div>
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                className="h-6 w-6 flex-shrink-0 stroke-current"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                ></path>
                                            </svg>
                                            <div>
                                                <h3 className="font-bold">
                                                    Aprovação pendente de
                                                    vínculo em projeto.
                                                </h3>
                                                <div className="text-xs">
                                                    Projeto solicitado:{' '}
                                                    <span className="font-semibold">
                                                        {colaborador
                                                            .projeto_solicitado
                                                            ?.nome || '-'}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex-none">
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
                                                className="btn btn-sm btn-success"
                                            >
                                                Aceitar Vínculo
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
                                                className="btn btn-sm btn-error ml-2"
                                            >
                                                Recusar Vínculo
                                            </button>
                                        </div>
                                    </div>
                                )}

                                {colaborador.status_cadastro === 'ATIVO' && (
                                    <div className="alert alert-success shadow-lg">
                                        <div>
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-6 w-6 flex-shrink-0 stroke-current"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            <div>
                                                <h3 className="font-bold">
                                                    Colaborador ativo
                                                </h3>
                                                <div className="flex flex-wrap items-center gap-1 text-xs">
                                                    <span>
                                                        Projeto(s) atual(is):
                                                    </span>
                                                    {colaborador.projetos_atuais &&
                                                    colaborador.projetos_atuais
                                                        .length > 0 ? (
                                                        colaborador.projetos_atuais.map(
                                                            (proj) => (
                                                                <span
                                                                    key={
                                                                        proj.id
                                                                    }
                                                                    className="badge badge-ghost badge-sm"
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
                                        </div>
                                    </div>
                                )}

                                {colaborador.status_cadastro === 'INATIVO' && (
                                    <div className="alert alert-outline shadow-lg">
                                        <div>
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                className="h-6 w-6 flex-shrink-0 stroke-current"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                ></path>
                                            </svg>
                                            <div>
                                                <h3 className="font-bold">
                                                    Colaborador inativo
                                                </h3>
                                                <span className="text-xs">
                                                    Este colaborador não possui
                                                    vínculo ativo no momento.
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                            <div className="card-actions mt-6 justify-end">
                                <button
                                    type="button"
                                    onClick={() =>
                                        window.history.length > 1
                                            ? window.history.back()
                                            : router.visit(
                                                  route('colaboradores.index', {
                                                      status: 'aprovacao_pendente',
                                                  }),
                                              )
                                    }
                                    className="btn btn-outline btn-primary"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="mr-2 h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M15 19l-7-7 7-7"
                                        />
                                    </svg>
                                    Voltar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
