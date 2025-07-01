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
                        <div className="mb-6">
                            <h3 className="text-base-content mb-2 text-xl font-bold">
                                Colaboradores Presentes
                            </h3>

                            <div className="bg-base-200 rounded-lg p-4">
                                <div className="flex items-center justify-between">
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
                                            {String(horario.hora).padStart(
                                                2,
                                                '0',
                                            )}
                                            :00
                                        </span>
                                    </div>

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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                        <span>{salaNome}</span>
                                    </div>

                                    <div className="badge badge-primary">
                                        {pessoas.length}{' '}
                                        {pessoas.length === 1
                                            ? 'pessoa'
                                            : 'pessoas'}
                                    </div>
                                </div>
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
                                                {/* Avatar com sistema de fallback melhorado */}
                                                <div className="relative flex-shrink-0">
                                                    <div className="avatar">
                                                        <div className="w-12 rounded-full">
                                                            {pessoa.foto_url ? (
                                                                <img
                                                                    src={
                                                                        pessoa.foto_url
                                                                    }
                                                                    alt={`Foto de ${pessoa.name}`}
                                                                    className="h-12 w-12 rounded-full object-cover"
                                                                    onError={(
                                                                        e,
                                                                    ) => {
                                                                        // Remove a imagem e mostra o placeholder
                                                                        const img =
                                                                            e.target as HTMLImageElement;
                                                                        img.style.display =
                                                                            'none';

                                                                        // Encontra o placeholder e o mostra
                                                                        const container =
                                                                            img.closest(
                                                                                '.avatar',
                                                                            );
                                                                        const placeholder =
                                                                            container?.querySelector(
                                                                                '.avatar-placeholder',
                                                                            );
                                                                        if (
                                                                            placeholder
                                                                        ) {
                                                                            (
                                                                                placeholder as HTMLElement
                                                                            ).style.display =
                                                                                'flex';
                                                                        }
                                                                    }}
                                                                />
                                                            ) : null}

                                                            {/* Placeholder sempre presente */}
                                                            <div
                                                                className="avatar-placeholder bg-neutral text-neutral-content absolute inset-0 flex h-12 w-12 items-center justify-center rounded-full"
                                                                style={{
                                                                    display:
                                                                        pessoa.foto_url
                                                                            ? 'none'
                                                                            : 'flex',
                                                                }}
                                                            >
                                                                <span className="text-sm font-medium">
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
                                                    </div>
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
                                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
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

                                                        {/* Badge de status se não tiver baia nem projeto */}
                                                        {!pessoa.baia &&
                                                            !pessoa.projeto && (
                                                                <div className="flex items-center gap-2">
                                                                    <div className="badge badge-ghost badge-sm">
                                                                        Sem
                                                                        local ou
                                                                        projeto
                                                                        definido
                                                                    </div>
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
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                                        />
                                    </svg>
                                    <div>
                                        <h4 className="text-base-content text-lg font-medium">
                                            Nenhuma pessoa encontrada
                                        </h4>
                                        <p className="text-base-content/70 mt-1 text-sm">
                                            Não há colaboradores trabalhando
                                            neste horário.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Backdrop para fechar o modal */}
            <form method="dialog" className="modal-backdrop">
                <button onClick={onClose}>close</button>
            </form>
        </dialog>
    );
});

HorarioPessoasModal.displayName = 'HorarioPessoasModal';

export default HorarioPessoasModal;
