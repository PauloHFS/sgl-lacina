import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Funcao, StatusCadastro, TipoProjeto, TipoVinculo } from '@/types';
import { Head, router } from '@inertiajs/react';
import React, { useCallback } from 'react';

interface ShowProps {
    colaborador: {
        id: string;
        name: string;
        email: string;
        linkedin_url?: string | null;
        github_url?: string | null;
        figma_url?: string | null;
        foto_url?: string | null;
        area_atuacao?: string | null;
        tecnologias?: string | null;
        curriculo?: string | null;
        cpf?: string | null;
        conta_bancaria?: string | null;
        agencia?: string | null;
        codigo_banco?: string | null;
        rg?: string | null;
        uf_rg?: string | null;
        telefone?: string | null;
        created_at: string;
        updated_at: string;
        status_cadastro:
            | 'VINCULO_PENDENTE'
            | 'APROVACAO_PENDENTE'
            | 'ATIVO'
            | 'INATIVO';
        vinculo?: {
            id: string;
            usuario_id: string;
            projeto_id: string;
            tipo_vinculo: TipoVinculo;
            funcao: Funcao;
            status: StatusCadastro;
            carga_horaria_semanal: number;
            data_inicio: string;
            data_fim?: string | null;
            created_at: string;
            updated_at: string;
            deleted_at: string | null;
            projeto: {
                id: string;
                nome: string;
                descricao: string;
                data_inicio: string;
                data_termino: string | null;
                cliente: string;
                slack_url: string | null;
                discord_url: string | null;
                board_url: string | null;
                git_url: string | null;
                tipo: TipoProjeto;
                created_at: string;
                updated_at: string;
                deleted_at: string | null;
            };
        } | null;
        projetos_atuais: Array<{
            id: string;
            nome: string;
            descricao: string;
            data_inicio: string;
            data_termino: string | null;
            cliente: string;
            slack_url: string | null;
            discord_url: string | null;
            board_url: string | null;
            git_url: string | null;
            tipo: TipoProjeto;
            created_at: string;
            updated_at: string;
            deleted_at: string | null;
        }>;
    };
}

// --- Helper Components ---

interface InfoItemProps {
    label: string;
    value?: string | null;
    children?: React.ReactNode;
    className?: string;
    isTextArea?: boolean;
}

const InfoItem: React.FC<InfoItemProps> = React.memo(
    ({ label, value, children, className = '', isTextArea = false }) => (
        <div className={`form-control ${className}`}>
            <label className="label">
                <span className="label-text font-semibold">{label}:</span>
            </label>
            {children ||
                (isTextArea ? (
                    <span className="textarea textarea-bordered flex h-auto min-h-24 py-2 break-words whitespace-normal">
                        {value || '-'}
                    </span>
                ) : (
                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                        {value || '-'}
                    </span>
                ))}
        </div>
    ),
);
InfoItem.displayName = 'InfoItem';

interface SocialLinksProps {
    linkedinUrl?: string | null;
    githubUrl?: string | null;
    figmaUrl?: string | null;
    colaboradorName: string; // Added prop for accessibility
}

const SocialLinks: React.FC<SocialLinksProps> = React.memo(
    ({ linkedinUrl, githubUrl, figmaUrl, colaboradorName }) => (
        <div className="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
            {linkedinUrl && (
                <a
                    href={linkedinUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-sm btn-outline btn-primary"
                    aria-label={`LinkedIn de ${colaboradorName}`}
                >
                    LinkedIn
                </a>
            )}
            {githubUrl && (
                <a
                    href={githubUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-sm btn-outline btn-neutral"
                    aria-label={`GitHub de ${colaboradorName}`}
                >
                    GitHub
                </a>
            )}
            {figmaUrl && (
                <a
                    href={figmaUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-sm btn-outline btn-accent"
                    aria-label={`Figma de ${colaboradorName}`}
                >
                    Figma
                </a>
            )}
        </div>
    ),
);
SocialLinks.displayName = 'SocialLinks';

interface ColaboradorHeaderProps {
    colaborador: Pick<
        ShowProps['colaborador'],
        | 'name'
        | 'email'
        | 'foto_url'
        | 'linkedin_url'
        | 'github_url'
        | 'figma_url'
    >;
}

const ColaboradorHeader: React.FC<ColaboradorHeaderProps> = React.memo(
    ({ colaborador }) => (
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
                        <span className="text-3xl" aria-hidden="true">
                            {colaborador.name.charAt(0).toUpperCase()}
                        </span>
                    </div>
                </div>
            )}
            <div className="text-center sm:text-left">
                <h2 className="card-title text-2xl">{colaborador.name}</h2>
                <p className="text-base-content/70">{colaborador.email}</p>
                <SocialLinks
                    linkedinUrl={colaborador.linkedin_url}
                    githubUrl={colaborador.github_url}
                    figmaUrl={colaborador.figma_url}
                    colaboradorName={colaborador.name} // Pass the name here
                />
            </div>
        </div>
    ),
);
ColaboradorHeader.displayName = 'ColaboradorHeader';

interface ColaboradorDetalhesProps {
    colaborador: Pick<
        ShowProps['colaborador'],
        | 'area_atuacao'
        | 'tecnologias'
        | 'telefone'
        | 'cpf'
        | 'rg'
        | 'uf_rg'
        | 'conta_bancaria'
        | 'agencia'
        | 'codigo_banco'
        | 'curriculo'
    >;
}

const ColaboradorDetalhes: React.FC<ColaboradorDetalhesProps> = React.memo(
    ({ colaborador }) => (
        <div className="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
            <InfoItem
                label="Área de Atuação"
                value={colaborador.area_atuacao}
            />
            <InfoItem label="Tecnologias">
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
            </InfoItem>
            <InfoItem label="Telefone" value={colaborador.telefone} />
            <InfoItem label="CPF" value={colaborador.cpf} />
            <InfoItem label="RG" value={colaborador.rg} />
            <InfoItem label="UF RG" value={colaborador.uf_rg} />
            <InfoItem
                label="Conta Bancária"
                value={colaborador.conta_bancaria}
            />
            <InfoItem label="Agência" value={colaborador.agencia} />
            <InfoItem
                label="Código do Banco"
                value={colaborador.codigo_banco}
            />
            <InfoItem
                label="Currículo"
                value={colaborador.curriculo}
                className="md:col-span-2"
                isTextArea
            />
        </div>
    ),
);
ColaboradorDetalhes.displayName = 'ColaboradorDetalhes';

const StatusIcon: React.FC<{ type: 'warning' | 'info' | 'success' | 'error' }> =
    React.memo(({ type }) => {
        switch (type) {
            case 'warning':
                return (
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-6 w-6 flex-shrink-0 stroke-current"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                );
            case 'info':
                return (
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        className="h-6 w-6 flex-shrink-0 stroke-current"
                        aria-hidden="true"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                    </svg>
                );
            case 'success':
                return (
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-6 w-6 flex-shrink-0 stroke-current"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                );
            case 'error': // Added for completeness, though not used in original snippet for status
                return (
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-6 w-6 flex-shrink-0 stroke-current"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                );
            default:
                return null;
        }
    });
StatusIcon.displayName = 'StatusIcon';

interface StatusAlertProps {
    type: 'warning' | 'info' | 'success' | 'error' | 'outline';
    title: string;
    message?: string | React.ReactNode;
    actions?: React.ReactNode;
}

const StatusAlert: React.FC<StatusAlertProps> = React.memo(
    ({ type, title, message, actions }) => {
        const alertClass =
            type === 'outline' ? 'alert-outline' : `alert-${type}`;
        return (
            <div role="alert" className={`alert ${alertClass} shadow-lg`}>
                <div className="flex items-center">
                    {type !== 'outline' && <StatusIcon type={type} />}
                    <div>
                        <h3 className="font-bold">{title}</h3>
                        {message && <div className="text-xs">{message}</div>}
                    </div>
                </div>
                {actions && <div className="flex-none">{actions}</div>}
            </div>
        );
    },
);
StatusAlert.displayName = 'StatusAlert';

interface ColaboradorStatusProps {
    colaborador: ShowProps['colaborador'];
    onAceitarCadastro: () => void;
    onRecusarCadastro: () => void;
    onAceitarVinculo: () => void;
    onRecusarVinculo: () => void;
}

const ColaboradorStatus: React.FC<ColaboradorStatusProps> = React.memo(
    ({
        colaborador,
        onAceitarCadastro,
        onRecusarCadastro,
        onAceitarVinculo,
        onRecusarVinculo,
    }) => {
        switch (colaborador.status_cadastro) {
            case 'VINCULO_PENDENTE':
                return (
                    <StatusAlert
                        type="warning"
                        title="Vínculo pendente de aprovação."
                        message="Este colaborador aguarda aprovação de cadastro."
                        actions={
                            <>
                                <button
                                    type="button"
                                    onClick={onAceitarCadastro}
                                    className="btn btn-sm btn-success"
                                    aria-label="Aceitar cadastro do colaborador"
                                >
                                    Aceitar
                                </button>
                                <button
                                    type="button"
                                    onClick={onRecusarCadastro}
                                    className="btn btn-sm btn-error ml-2"
                                    aria-label="Recusar cadastro do colaborador"
                                >
                                    Recusar
                                </button>
                            </>
                        }
                    />
                );
            case 'APROVACAO_PENDENTE':
                return (
                    <StatusAlert
                        type="info"
                        title="Aprovação pendente de vínculo em projeto."
                        message={
                            <>
                                Projeto solicitado:{' '}
                                <span className="font-semibold">
                                    {colaborador.vinculo?.projeto.nome ||
                                        'Não especificado'}
                                </span>
                            </>
                        }
                        actions={
                            <>
                                <button
                                    type="button"
                                    onClick={onAceitarVinculo}
                                    className="btn btn-sm btn-success"
                                    aria-label="Aceitar vínculo do colaborador ao projeto"
                                >
                                    Aceitar Vínculo
                                </button>
                                <button
                                    type="button"
                                    onClick={onRecusarVinculo}
                                    className="btn btn-sm btn-error ml-2"
                                    aria-label="Recusar vínculo do colaborador ao projeto"
                                >
                                    Recusar Vínculo
                                </button>
                            </>
                        }
                    />
                );
            case 'ATIVO':
                return (
                    <StatusAlert
                        type="success"
                        title="Colaborador ativo."
                        message="Este colaborador está ativo na plataforma."
                    />
                );
            case 'INATIVO':
                return (
                    <StatusAlert
                        type="outline"
                        title="Colaborador inativo."
                        message="Este colaborador está atualmente inativo."
                    />
                );
            default:
                // eslint-disable-next-line no-case-declarations, @typescript-eslint/no-unused-vars
                const _exhaustiveCheck: never = colaborador.status_cadastro;
                return null;
        }
    },
);
ColaboradorStatus.displayName = 'ColaboradorStatus';

// --- Main Component ---

export default function Show({ colaborador }: ShowProps) {
    const handleAceitarCadastro = useCallback(() => {
        router.post(route('colaboradores.aceitar', colaborador.id));
    }, [colaborador.id]);

    const handleRecusarCadastro = useCallback(() => {
        router.post(route('colaboradores.recusar', colaborador.id));
    }, [colaborador.id]);

    const handleAceitarVinculo = useCallback(() => {
        router.post(route('vinculos.aceitar', colaborador.id));
    }, [colaborador.id]);

    const handleRecusarVinculo = useCallback(() => {
        router.post(route('vinculos.recusar', colaborador.id));
    }, [colaborador.id]);

    return (
        <AuthenticatedLayout header="Detalhes do Colaborador">
            <Head title={`Colaborador: ${colaborador.name}`} />
            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="card card-bordered bg-base-100 shadow-xl">
                        <div className="card-body">
                            <ColaboradorHeader colaborador={colaborador} />

                            <div className="divider">Detalhes</div>
                            <ColaboradorDetalhes colaborador={colaborador} />

                            <div className="divider">Status</div>
                            <div className="my-6">
                                <ColaboradorStatus
                                    colaborador={colaborador}
                                    onAceitarCadastro={handleAceitarCadastro}
                                    onRecusarCadastro={handleRecusarCadastro}
                                    onAceitarVinculo={handleAceitarVinculo}
                                    onRecusarVinculo={handleRecusarVinculo}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
