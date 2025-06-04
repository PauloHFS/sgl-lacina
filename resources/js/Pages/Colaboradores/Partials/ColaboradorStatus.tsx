import { StatusAlert } from '@/Components/StatusAlert';
import React from 'react';

interface ColaboradorStatusProps {
    onAceitarCadastro: () => void;
    onRecusarCadastro: () => void;
    onAceitarVinculo: () => void;
    onRecusarVinculo: () => void;
    processing: boolean;
    status_colaborador:
        | 'VINCULO_PENDENTE'
        | 'APROVACAO_PENDENTE'
        | 'ATIVO'
        | 'ENCERRADO';
}

export const ColaboradorStatus: React.FC<ColaboradorStatusProps> = React.memo(
    ({
        onAceitarCadastro,
        onRecusarCadastro,
        onAceitarVinculo,
        onRecusarVinculo,
        processing,
        status_colaborador,
    }) => {
        switch (status_colaborador) {
            case 'APROVACAO_PENDENTE':
                return (
                    <StatusAlert
                        type="warning"
                        title="Cadastro pendente de aprovação."
                        message="Este Usuário aguarda aprovação de cadastro. Revise os dados e aceite ou recuse o cadastro."
                        actions={
                            <>
                                <dialog id="my_modal_2" className="modal">
                                    <div className="modal-box mx-4 w-full max-w-md overflow-hidden">
                                        <form method="dialog">
                                            <button className="btn btn-sm btn-circle btn-ghost absolute top-2 right-2">
                                                ✕
                                            </button>
                                        </form>

                                        <div className="space-y-6 overflow-hidden">
                                            <div className="text-center">
                                                <h3 className="text-base-content text-lg font-bold break-words">
                                                    Avaliar Cadastro na
                                                    Plataforma
                                                </h3>
                                                <p className="text-base-content/70 mt-1 text-sm break-words">
                                                    Revise os dados do usuário
                                                    antes de tomar uma decisão
                                                </p>
                                            </div>

                                            <div className="alert alert-info">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    className="h-6 w-6 shrink-0 stroke-current"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    />
                                                </svg>
                                                <div className="overflow-hidden">
                                                    <div className="font-medium">
                                                        Atenção!
                                                    </div>
                                                    <div className="text-sm break-words opacity-80">
                                                        Esta ação não pode ser
                                                        desfeita. Verifique
                                                        todos os dados
                                                        cuidadosamente.
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="form-control w-full">
                                                <label className="label">
                                                    <span className="label-text text-base-content text-sm font-semibold">
                                                        Observações sobre a
                                                        avaliação
                                                    </span>
                                                    <span className="label-text-alt text-base-content/60 text-xs font-medium">
                                                        Opcional
                                                    </span>
                                                </label>
                                                <textarea
                                                    className="textarea textarea-bordered bg-base-100 text-base-content placeholder:text-base-content/40 focus:border-primary w-full resize-none transition-colors focus:outline-none"
                                                    rows={3}
                                                    placeholder="Adicione observações sobre a decisão tomada..."
                                                />
                                                <div className="mt-2">
                                                    <span className="text-base-content/70 block text-xs leading-relaxed">
                                                        Suas observações serão
                                                        enviadas por email para
                                                        o usuário junto com o
                                                        resultado da avaliação.
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="bg-base-200/60 border-base-300 rounded-lg border p-4">
                                                <div className="flex items-start gap-3">
                                                    <div className="text-primary text-lg">
                                                        💡
                                                    </div>
                                                    <div className="text-base-content/80 text-sm leading-relaxed break-words">
                                                        <strong className="text-base-content">
                                                            Dica:
                                                        </strong>{' '}
                                                        Pressione ESC ou clique
                                                        no X para fechar o modal
                                                        sem salvar alterações.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="modal-action border-base-300 mt-6 border-t pt-6">
                                            <div className="flex w-full gap-3">
                                                <button
                                                    type="button"
                                                    onClick={onRecusarCadastro}
                                                    className="btn btn-error flex-1"
                                                    aria-label="Recusar cadastro do usuário"
                                                    disabled={processing}
                                                >
                                                    {processing ? (
                                                        <span className="loading loading-spinner loading-sm" />
                                                    ) : (
                                                        <svg
                                                            className="h-4 w-4"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth={2}
                                                                d="M6 18L18 6M6 6l12 12"
                                                            />
                                                        </svg>
                                                    )}
                                                    Recusar
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={onAceitarCadastro}
                                                    className="btn btn-success flex-1"
                                                    aria-label="Aceitar cadastro do usuário"
                                                    disabled={processing}
                                                >
                                                    {processing ? (
                                                        <span className="loading loading-spinner loading-sm" />
                                                    ) : (
                                                        <svg
                                                            className="h-4 w-4"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth={2}
                                                                d="M5 13l4 4L19 7"
                                                            />
                                                        </svg>
                                                    )}
                                                    Aceitar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <form
                                        method="dialog"
                                        className="modal-backdrop"
                                    >
                                        <button>close</button>
                                    </form>
                                </dialog>
                                <button
                                    className="btn"
                                    onClick={() => {
                                        const modal = document.getElementById(
                                            'my_modal_2',
                                        ) as HTMLDialogElement;
                                        modal?.showModal();
                                    }}
                                >
                                    Avaliar Status
                                </button>
                            </>
                        }
                    />
                );
            case 'VINCULO_PENDENTE':
                return (
                    <StatusAlert
                        type="info"
                        title="Aprovação pendente de vínculo em projeto."
                        message="Este Colaborador aguarda aprovação de vínculo em um projeto. Revise os dados e aceite ou recuse o vínculo."
                        actions={
                            <>
                                <button
                                    type="button"
                                    onClick={onAceitarVinculo}
                                    className="btn btn-sm btn-success"
                                    aria-label="Aceitar vínculo do Colaborador ao projeto"
                                    disabled={processing}
                                >
                                    Aceitar Vínculo
                                </button>
                                <button
                                    type="button"
                                    onClick={onRecusarVinculo}
                                    className="btn btn-sm btn-error ml-2"
                                    aria-label="Recusar vínculo do Colaborador ao projeto"
                                    disabled={processing}
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
                        message="Este Colaborador está com vínculo ativo na plataforma."
                    />
                );
            case 'ENCERRADO':
                return (
                    <StatusAlert
                        type="outline"
                        title="Colaborabor Inativo."
                        message="Este colaborador nunca participou de projetos ou não está participando em um atualmente."
                    />
                );
            default:
                // eslint-disable-next-line no-case-declarations, @typescript-eslint/no-unused-vars
                return null;
        }
    },
);
ColaboradorStatus.displayName = 'ColaboradorStatus';
