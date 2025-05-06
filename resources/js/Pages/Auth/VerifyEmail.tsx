import PrimaryButton from '@/Components/PrimaryButton';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <GuestLayout>
            <Head title="Verificação de Email" />

            <div className="card bg-base-100 shadow-xl">
                <div className="card-body">
                    <div className="text-sm">
                        Obrigado por se cadastrar! Antes de começar, você poderia
                        verificar seu endereço de email clicando no link que acabamos de
                        enviar para você? Se você não recebeu o email, teremos prazer em
                        enviar outro.
                    </div>

                    {status === 'verification-link-sent' && (
                        <div className="alert alert-success text-sm">
                            Um novo link de verificação foi enviado para o endereço de
                            email que você forneceu durante o registro.
                        </div>
                    )}

                    <form onSubmit={submit} className="mt-4">
                        <div className="flex items-center justify-between">
                            <PrimaryButton disabled={processing} className="btn btn-primary">
                                Reenviar Email de Verificação
                            </PrimaryButton>

                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="btn btn-ghost btn-sm"
                            >
                                Log Out
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </GuestLayout>
    );
}
