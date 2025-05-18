import React from 'react';
import { ShowProps } from '../Show';
import { StatusAlert } from './StatusAlert';

interface ColaboradorStatusProps {
    colaborador: ShowProps['colaborador'];
    onAceitarCadastro: () => void;
    onRecusarCadastro: () => void;
    onAceitarVinculo: () => void;
    onRecusarVinculo: () => void;
}

export const ColaboradorStatus: React.FC<ColaboradorStatusProps> = React.memo(
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
