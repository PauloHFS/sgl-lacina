import DangerButton from '@/Components/DangerButton';
import Authenticated from '@/Layouts/AuthenticatedLayout';

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
    };
    status: 'success' | 'error';
    message?: string;
}

export default function AvaliacaoInicial({ user, status, message }: Props) {
    return (
        <Authenticated
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Avaliacao Inicial
                </h2>
            }
        >
            {status === 'success' && (
                <>
                    <div>
                        <h3 className="text-lg font-semibold">
                            Nome: {user.name}
                        </h3>
                        <p className="text-sm text-gray-600">
                            Email: {user.email}
                        </p>
                    </div>
                    <DangerButton onClick={() => alert(user.id)}>
                        Aceitar Pré Candidato
                    </DangerButton>
                </>
            )}
            {status === 'error' && (
                <div className="text-red-600">
                    <h3 className="text-lg font-semibold">Erro</h3>
                    <p className="text-sm">{message}</p>
                </div>
            )}
        </Authenticated>
    );
}
