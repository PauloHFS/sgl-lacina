import { Baia } from '@/types';

interface HorarioModalProps {
    isOpen: boolean;
    onClose: () => void;
    dia: string;
    horario: string;
    usuarios: Array<{
        id: string;
        name: string;
        email: string;
        foto_url?: string | null;
        baia?: Baia | null;
    }>;
}

export default function HorarioModal({
    isOpen,
    onClose,
    dia,
    horario,
    usuarios,
}: HorarioModalProps) {
    if (!isOpen) return null;

    return (
        <dialog open className="modal modal-open">
            <div className="modal-box w-11/12 max-w-2xl">
                <form method="dialog">
                    <button
                        className="btn btn-sm btn-circle btn-ghost absolute top-2 right-2"
                        onClick={onClose}
                        type="button"
                    >
                        ✕
                    </button>
                </form>

                <h3 className="mb-4 text-lg font-bold">
                    Usuários em {dia} - {horario}
                </h3>

                {usuarios.length === 0 ? (
                    <div className="py-8 text-center">
                        <div className="text-base-content/60">
                            Nenhum usuário trabalhando neste horário
                        </div>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {usuarios.map((usuario) => (
                            <div
                                key={usuario.id}
                                className="card bg-base-200 shadow-sm"
                            >
                                <div className="card-body p-4">
                                    <div className="flex items-center gap-3">
                                        <div className="avatar">
                                            <div className="h-12 w-12 rounded-full">
                                                <img
                                                    src={
                                                        usuario.foto_url ||
                                                        `https://ui-avatars.com/api/?name=${encodeURIComponent(usuario.name)}&background=random&color=fff`
                                                    }
                                                    alt={`Foto de ${usuario.name}`}
                                                />
                                            </div>
                                        </div>
                                        <div className="flex-1">
                                            <div className="font-semibold">
                                                {usuario.name}
                                            </div>
                                            <div className="text-base-content/70 text-sm">
                                                {usuario.email}
                                            </div>
                                            {usuario.baia && (
                                                <div className="mt-1 text-xs">
                                                    {usuario.baia.sala
                                                        ?.nome && (
                                                        <span className="badge badge-outline badge-xs mr-1">
                                                            Sala:{' '}
                                                            {
                                                                usuario.baia
                                                                    .sala.nome
                                                            }
                                                        </span>
                                                    )}
                                                    <span className="badge badge-outline badge-xs">
                                                        Baia:{' '}
                                                        {usuario.baia.nome}
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
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
}
