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
                        title="Vínculo pendente de aprovação."
                        message="Este Vinculo aguarda aprovação de cadastro."
                        actions={
                            <>
                                <button
                                    type="button"
                                    onClick={onAceitarCadastro}
                                    className="btn btn-sm btn-success"
                                    aria-label="Aceitar cadastro do Vinculo"
                                    disabled={processing}
                                >
                                    Aceitar
                                </button>
                                <button
                                    type="button"
                                    onClick={onRecusarCadastro}
                                    className="btn btn-sm btn-error ml-2"
                                    aria-label="Recusar cadastro do Vinculo"
                                    disabled={processing}
                                >
                                    Recusar
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
                        title="Vinculo encerrado."
                        message="Este Vinculo está atualmente encerrado."
                    />
                );
            default:
                // eslint-disable-next-line no-case-declarations, @typescript-eslint/no-unused-vars
                return null;
        }
    },
);
ColaboradorStatus.displayName = 'ColaboradorStatus';
