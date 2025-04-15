import PrimaryButton from '@/Components/PrimaryButton';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function AvaliacaoInicial({
    user,
    status,
    message,
}: {
    user: {
        id: number;
        name: string;
        email: string;
    };
    status?: string;
    message?: string;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({});

    const handleOnSubmit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('colaboradores.store', { preCandidatoUserId: user.id }), {
            onFinish: () => reset(),
        });
    };

    return (
        <Authenticated
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Avaliacao Inicial
                </h2>
            }
        >
            <form onSubmit={handleOnSubmit} className="space-y-6">
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
                        <PrimaryButton>Aceitar Pré Candidato</PrimaryButton>
                    </>
                )}
                {status === 'error' && (
                    <div className="text-red-600">
                        <h3 className="text-lg font-semibold">Erro</h3>
                        <p className="text-sm">{message}</p>
                    </div>
                )}
            </form>
        </Authenticated>
    );
}
