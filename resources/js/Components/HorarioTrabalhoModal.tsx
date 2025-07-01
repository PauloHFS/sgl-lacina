import { ProjetoAtivo, SalaDisponivel } from '@/types';
import { useEffect, useMemo, useState } from 'react';

interface HorarioTrabalhoModalProps {
    isOpen: boolean;
    onClose: () => void;
    onConfirm: (salaId?: string, baiaId?: string, projetoId?: string) => void;
    tipoTrabalho: 'TRABALHO_PRESENCIAL' | 'TRABALHO_REMOTO';
    initialSalaId?: string;
    initialBaiaId?: string;
    initialProjetoId?: string;
    salasDisponiveis: SalaDisponivel[];
    projetosAtivos: ProjetoAtivo[];
    isLoading?: boolean;
}

export default function HorarioTrabalhoModal({
    isOpen,
    onClose,
    onConfirm,
    tipoTrabalho,
    initialSalaId,
    initialBaiaId,
    initialProjetoId,
    salasDisponiveis,
    projetosAtivos,
    isLoading = false,
}: HorarioTrabalhoModalProps) {
    const [selectedSalaId, setSelectedSalaId] = useState<string>(
        initialSalaId || '',
    );
    const [selectedBaiaId, setSelectedBaiaId] = useState<string>(
        initialBaiaId || '',
    );
    const [selectedProjetoId, setSelectedProjetoId] = useState<string>(
        initialProjetoId || '',
    );
    const [error, setError] = useState<string | null>(null);

    const selectedSala = useMemo(
        () => salasDisponiveis.find((sala) => sala.id === selectedSalaId),
        [salasDisponiveis, selectedSalaId],
    );

    const baias = useMemo(() => selectedSala?.baias || [], [selectedSala]);

    useEffect(() => {
        if (initialSalaId) {
            setSelectedSalaId(initialSalaId);
        }
        if (initialBaiaId) {
            setSelectedBaiaId(initialBaiaId);
        }
        if (initialProjetoId) {
            setSelectedProjetoId(initialProjetoId);
        }
    }, [initialSalaId, initialBaiaId, initialProjetoId]);

    // Reset baia selection when sala changes
    useEffect(() => {
        if (
            selectedSalaId &&
            selectedBaiaId &&
            !baias.find((baia) => baia.id === selectedBaiaId)
        ) {
            setSelectedBaiaId('');
        }
    }, [selectedSalaId, selectedBaiaId, baias]);

    // Reset form when modal opens
    useEffect(() => {
        if (isOpen) {
            setError(null);
            setSelectedSalaId(initialSalaId || '');
            setSelectedBaiaId(initialBaiaId || '');
            setSelectedProjetoId(initialProjetoId || '');
        }
    }, [isOpen, initialSalaId, initialBaiaId, initialProjetoId]);

    const handleConfirm = () => {
        setError(null);

        // Validação do projeto (obrigatório para ambos os tipos)
        if (!selectedProjetoId) {
            setError('Selecione um projeto ativo.');
            return;
        }

        // Validações para trabalho presencial
        if (tipoTrabalho === 'TRABALHO_PRESENCIAL') {
            if (!selectedSalaId) {
                setError('Selecione uma sala.');
                return;
            }

            if (!selectedBaiaId) {
                setError('Selecione uma baia.');
                return;
            }

            onConfirm(selectedSalaId, selectedBaiaId, selectedProjetoId);
        } else {
            // Para trabalho remoto
            onConfirm(undefined, undefined, selectedProjetoId);
        }
    };

    const handleModalClick = (e: React.MouseEvent) => {
        e.stopPropagation();
    };

    if (!isOpen) return null;

    return (
        <div className="modal modal-open" onClick={onClose}>
            <div
                className="modal-box w-11/12 max-w-2xl"
                onClick={handleModalClick}
            >
                <h3 className="mb-4 text-lg font-bold">
                    {tipoTrabalho === 'TRABALHO_PRESENCIAL'
                        ? 'Configurar Trabalho Presencial'
                        : 'Configurar Trabalho Remoto'}
                </h3>

                {isLoading ? (
                    <div className="flex items-center justify-center py-8">
                        <span className="loading loading-spinner loading-lg"></span>
                        <span className="ml-2">Carregando dados...</span>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {/* Seleção de Projeto */}
                        <div className="form-control">
                            <label className="label">
                                <span className="label-text font-medium">
                                    Projeto Ativo *
                                </span>
                            </label>
                            <select
                                className="select select-bordered w-full"
                                value={selectedProjetoId}
                                onChange={(e) =>
                                    setSelectedProjetoId(e.target.value)
                                }
                            >
                                <option value="">Selecione um projeto</option>
                                {projetosAtivos.map((projeto) => (
                                    <option key={projeto.id} value={projeto.id}>
                                        {projeto.projeto_nome}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Seleções específicas para trabalho presencial */}
                        {tipoTrabalho === 'TRABALHO_PRESENCIAL' && (
                            <>
                                {/* Seleção de Sala */}
                                <div className="form-control">
                                    <label className="label">
                                        <span className="label-text font-medium">
                                            Sala *
                                        </span>
                                    </label>
                                    <select
                                        className="select select-bordered w-full"
                                        value={selectedSalaId}
                                        onChange={(e) =>
                                            setSelectedSalaId(e.target.value)
                                        }
                                    >
                                        <option value="">
                                            Selecione uma sala
                                        </option>
                                        {salasDisponiveis.map((sala) => (
                                            <option
                                                key={sala.id}
                                                value={sala.id}
                                            >
                                                {sala.nome} ({sala.baias.length}{' '}
                                                baias disponíveis)
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Seleção de Baia */}
                                <div className="form-control">
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
                                            {selectedSalaId
                                                ? 'Selecione uma baia'
                                                : 'Primeiro selecione uma sala'}
                                        </option>
                                        {baias.map((baia) => (
                                            <option
                                                key={baia.id}
                                                value={baia.id}
                                            >
                                                {baia.nome}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </>
                        )}

                        {/* Informações adicionais */}
                        {tipoTrabalho === 'TRABALHO_REMOTO' && (
                            <div className="alert alert-info">
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
                                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                <span>
                                    Você estará trabalhando remotamente no
                                    projeto selecionado.
                                </span>
                            </div>
                        )}

                        {/* Exibição de erro */}
                        {error && (
                            <div className="alert alert-error">
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

                        {/* Botões de ação */}
                        <div className="modal-action">
                            <button
                                type="button"
                                className="btn btn-ghost"
                                onClick={onClose}
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                className="btn btn-primary"
                                onClick={handleConfirm}
                                disabled={isLoading}
                            >
                                Confirmar
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
