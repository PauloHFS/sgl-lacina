import { PessoaHorario } from '@/types';
import { forwardRef } from 'react';

interface HorarioPessoasModalProps {
    onClose: () => void;
    pessoas: PessoaHorario[];
    horario: { dia: string; hora: number } | null;
    salaNome: string;
}

const HorarioPessoasModal = forwardRef<
    HTMLDialogElement,
    HorarioPessoasModalProps
>(({ onClose, pessoas, horario, salaNome }, ref) => {
    return (
        <dialog ref={ref} className="modal">
            <div className="modal-box max-w-4xl">
                <form method="dialog">
                    <button
                        className="btn btn-sm btn-circle btn-ghost absolute top-2 right-2"
                        onClick={onClose}
                    >
                        ✕
                    </button>
                </form>

                {horario && (
                    <div>
                        <h3 className="mb-4 text-lg font-bold">
                            Colaboradores Trabalhando
                        </h3>

                        <div className="mb-4">
                            <div className="text-base-content/70 flex items-center gap-2 text-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    className="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                <span>
                                    {horario.dia} -{' '}
                                    {String(horario.hora).padStart(2, '0')}:00
                                </span>
                                <span className="mx-2">•</span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    className="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                                <span>{salaNome}</span>
                                <span className="mx-2">•</span>
                                <span className="font-medium">
                                    {pessoas.length} pessoa(s)
                                </span>
                            </div>
                        </div>

                        {pessoas.length > 0 ? (
                            <div className="max-h-96 space-y-4 overflow-y-auto">
                                {pessoas.map((pessoa, index) => (
                                    <div
                                        key={pessoa.id}
                                        className="card bg-base-200 border-base-300 border transition-shadow hover:shadow-md"
                                    >
                                        <div className="card-body p-4">
                                            <div className="flex items-start gap-4">
                                                {/* Avatar */}
                                                <div className="flex-shrink-0">
                                                    {pessoa.foto_url ? (
                                                        <div className="avatar">
                                                            <div className="w-12 rounded-full">
                                                                <img
                                                                    src={
                                                                        pessoa.foto_url
                                                                    }
                                                                    alt={
                                                                        pessoa.name
                                                                    }
                                                                />
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <div className="avatar placeholder">
                                                            <div className="bg-neutral text-neutral-content w-12 rounded-full">
                                                                <span className="text-sm">
                                                                    {pessoa.name
                                                                        .split(
                                                                            ' ',
                                                                        )
                                                                        .map(
                                                                            (
                                                                                n,
                                                                            ) =>
                                                                                n.charAt(
                                                                                    0,
                                                                                ),
                                                                        )
                                                                        .slice(
                                                                            0,
                                                                            2,
                                                                        )
                                                                        .join(
                                                                            '',
                                                                        )
                                                                        .toUpperCase()}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Informações da pessoa */}
                                                <div className="min-w-0 flex-1">
                                                    <div className="flex items-start justify-between">
                                                        <div>
                                                            <h4 className="text-base-content text-base font-semibold">
                                                                {pessoa.name}
                                                            </h4>
                                                            <p className="text-base-content/70 text-sm">
                                                                {pessoa.email}
                                                            </p>
                                                        </div>
                                                        <div className="text-base-content/60 text-right text-xs">
                                                            #{index + 1}
                                                        </div>
                                                    </div>

                                                    {/* Informações de trabalho */}
                                                    <div className="mt-3 space-y-2">
                                                        {pessoa.baia && (
                                                            <div className="flex items-center gap-2">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    className="text-base-content/60 h-4 w-4"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke="currentColor"
                                                                >
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        strokeWidth={
                                                                            2
                                                                        }
                                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                                                    />
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        strokeWidth={
                                                                            2
                                                                        }
                                                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                                                    />
                                                                </svg>
                                                                <span className="text-base-content/80 text-sm">
                                                                    <span className="font-medium">
                                                                        Baia:
                                                                    </span>{' '}
                                                                    {
                                                                        pessoa
                                                                            .baia
                                                                            .nome
                                                                    }
                                                                </span>
                                                            </div>
                                                        )}

                                                        {pessoa.projeto && (
                                                            <div className="flex items-center gap-2">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    className="text-base-content/60 h-4 w-4"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke="currentColor"
                                                                >
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        strokeWidth={
                                                                            2
                                                                        }
                                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                                    />
                                                                </svg>
                                                                <span className="text-base-content/80 text-sm">
                                                                    <span className="font-medium">
                                                                        Projeto:
                                                                    </span>{' '}
                                                                    {
                                                                        pessoa
                                                                            .projeto
                                                                            .nome
                                                                    }
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="py-8 text-center">
                                <div className="flex flex-col items-center gap-3">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="text-base-content/30 h-12 w-12"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                        />
                                    </svg>
                                    <div className="text-base-content/60">
                                        Nenhuma pessoa encontrada para este
                                        horário
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                <div className="modal-action">
                    <button className="btn" onClick={onClose}>
                        Fechar
                    </button>
                </div>
            </div>
            <form method="dialog" className="modal-backdrop">
                <button onClick={onClose}>close</button>
            </form>
        </dialog>
    );
});

HorarioPessoasModal.displayName = 'HorarioPessoasModal';

export default HorarioPessoasModal;
