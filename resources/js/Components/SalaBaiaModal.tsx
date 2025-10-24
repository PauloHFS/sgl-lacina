import { Baia, Sala } from '@/types';
import { useEffect, useMemo, useState } from 'react';

interface SalaBaiaModalProps {
    isOpen: boolean;
    onClose: () => void;
    onConfirm: (salaId: string, baiaId: string) => void;
    tipoTrabalho: 'TRABALHO_PRESENCIAL' | 'TRABALHO_REMOTO';
    initialSalaId?: string;
    initialBaiaId?: string;
    salas: Sala[];
}

export default function SalaBaiaModal({
    isOpen,
    onClose,
    onConfirm,
    tipoTrabalho,
    initialSalaId,
    initialBaiaId,
    salas,
}: SalaBaiaModalProps) {
    const [selectedSalaId, setSelectedSalaId] = useState<string>(
        initialSalaId || '',
    );
    const [selectedBaiaId, setSelectedBaiaId] = useState<string>(
        initialBaiaId || '',
    );
    const [error, setError] = useState<string | null>(null);

    const selectedSala = useMemo(
        () => salas.find((sala) => sala.id === selectedSalaId),
        [salas, selectedSalaId],
    );

    const baias = useMemo(() => selectedSala?.baias || [], [selectedSala]);

    useEffect(() => {
        if (initialSalaId) {
            setSelectedSalaId(initialSalaId);
        }
        if (initialBaiaId) {
            setSelectedBaiaId(initialBaiaId);
        }
    }, [initialSalaId, initialBaiaId]);

    // Reset baia selection when sala changes
    useEffect(() => {
        if (
            selectedSalaId &&
            !baias.find((baia: Baia) => baia.id === selectedBaiaId)
        ) {
            setSelectedBaiaId('');
        }
    }, [selectedSalaId, baias, selectedBaiaId]);

    const handleConfirm = () => {
        if (
            tipoTrabalho === 'TRABALHO_PRESENCIAL' &&
            (!selectedSalaId || !selectedBaiaId)
        ) {
            setError('Selecione uma sala e uma baia para trabalho presencial');
            return;
        }
        if (tipoTrabalho === 'TRABALHO_REMOTO' && !selectedSalaId) {
            setError('Selecione uma sala para trabalho remoto');
            return;
        }

        onConfirm(selectedSalaId, selectedBaiaId);
        onClose();
    };

    const handleClose = () => {
        setError(null);
        onClose();
    };

    if (!isOpen) return null;

    return (
        <dialog className="modal modal-open">
            <div className="modal-box">
                <h3 className="mb-4 text-lg font-bold">
                    Selecionar{' '}
                    {tipoTrabalho === 'TRABALHO_PRESENCIAL'
                        ? 'Sala e Baia'
                        : 'Sala'}
                </h3>

                {error && (
                    <div className="alert alert-error mb-4">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-6 w-6 shrink-0 stroke-current"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <span>{error}</span>
                    </div>
                )}

                <div className="space-y-4">
                    {/* Seleção de Sala */}
                    <div>
                        <label className="label">
                            <span className="label-text font-medium">
                                Sala *
                            </span>
                        </label>
                        <select
                            className="select select-bordered w-full"
                            value={selectedSalaId}
                            onChange={(e) => setSelectedSalaId(e.target.value)}
                        >
                            <option value="">Selecione uma sala</option>
                            {salas.map((sala) => (
                                <option key={sala.id} value={sala.id}>
                                    {sala.nome}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Seleção de Baia (apenas para trabalho presencial) */}
                    {tipoTrabalho === 'TRABALHO_PRESENCIAL' && (
                        <div>
                            <label className="label">
                                <span className="label-text font-medium">
                                    Baia *
                                </span>
                            </label>
                            <select
                                className="select select-bordered w-full"
                                value={selectedBaiaId}
                                onChange={(e) =>
                                    setSelectedBaiaId(e.target.value)
                                }
                                disabled={!selectedSalaId}
                            >
                                <option value="">
                                    {!selectedSalaId
                                        ? 'Selecione uma sala primeiro'
                                        : 'Selecione uma baia'}
                                </option>
                                {baias.map((baia: Baia) => (
                                    <option key={baia.id} value={baia.id}>
                                        {baia.nome}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>

                <div className="modal-action">
                    <button
                        type="button"
                        className="btn btn-outline"
                        onClick={handleClose}
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        className="btn btn-primary"
                        onClick={handleConfirm}
                    >
                        Confirmar
                    </button>
                </div>
            </div>
            <form method="dialog" className="modal-backdrop">
                <button type="button" onClick={handleClose}>
                    close
                </button>
            </form>
        </dialog>
    );
}
