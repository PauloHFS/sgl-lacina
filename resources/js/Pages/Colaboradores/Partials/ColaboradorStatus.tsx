import React from 'react';
import { ColaboradorData } from '../Show'; // Import ColaboradorData
import { StatusAlert } from './StatusAlert';

interface ColaboradorStatusProps {
    colaborador: ColaboradorData; // Use ColaboradorData directly
    onAceitarCadastro: () => void;
    onRecusarCadastro: () => void;
    onAceitarVinculo: () => void;
    onRecusarVinculo: () => void;
    processing: boolean; // Added processing prop
}

export const ColaboradorStatus: React.FC<ColaboradorStatusProps> = React.memo(
    ({
        colaborador,
        onAceitarCadastro,
        onRecusarCadastro,
        onAceitarVinculo,
        onRecusarVinculo,
        processing, // Destructure processing prop
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
                                    disabled={processing} // Disable button when processing
                                >
                                    Aceitar
                                </button>
                                <button
                                    type="button"
                                    onClick={onRecusarCadastro}
                                    className="btn btn-sm btn-error ml-2"
                                    aria-label="Recusar cadastro do colaborador"
                                    disabled={processing} // Disable button when processing
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
                                    disabled={processing} // Disable button when processing
                                >
                                    Aceitar Vínculo
                                </button>
                                <button
                                    type="button"
                                    onClick={onRecusarVinculo}
                                    className="btn btn-sm btn-error ml-2"
                                    aria-label="Recusar vínculo do colaborador ao projeto"
                                    disabled={processing} // Disable button when processing
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
            case 'ENCERRADO':
                return (
                    <StatusAlert
                        type="outline"
                        title="Colaborador encerrado."
                        message="Este colaborador está atualmente encerrado."
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
