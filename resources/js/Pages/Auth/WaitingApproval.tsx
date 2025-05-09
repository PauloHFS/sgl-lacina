import GuestLayout from '@/Layouts/GuestLayout';
import { Head } from '@inertiajs/react';

export default function WaitingApproval() {
    return (
        <GuestLayout>
            <Head title="Aguardando Aprovação" />
            <div className="card-body items-center text-center">
                <div className="avatar placeholder mb-6">
                    <div className="bg-warning/20 text-warning flex h-20 w-20 items-center justify-center rounded-full">
                        <svg
                            className="h-12 w-12"
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
                    </div>
                </div>
                <h2 className="card-title text-primary text-2xl font-bold">
                    Cadastro em Análise
                </h2>
                <p className="text-base-content/70 mt-3">
                    Seu cadastro foi recebido e está aguardando aprovação do
                    docente responsável.
                </p>

                <div className="divider my-6" />

                <div className="w-full text-left">
                    <h3 className="text-primary mb-3 text-lg font-semibold">
                        O que acontece agora?
                    </h3>
                    <ul className="text-base-content/70 list-inside list-disc space-y-2">
                        <li>
                            O docente que você indicou receberá uma notificação
                            sobre seu cadastro.
                        </li>
                        <li>
                            Após a análise e aprovação, você receberá um e-mail
                            com as instruções de acesso.
                        </li>
                        <li>
                            Caso haja qualquer necessidade de ajuste nos dados,
                            você será notificado.
                        </li>
                    </ul>
                </div>

                <div className="divider my-6" />

                <div className="w-full text-left">
                    <h3 className="text-primary mb-3 text-lg font-semibold">
                        O que você precisa fazer?
                    </h3>
                    <ul className="text-base-content/70 list-inside list-disc space-y-2">
                        <li>
                            Verificar seu email no sistema pelo link recebido.
                            (Verifique também a caixa de spam)
                        </li>
                        <li>
                            Verificar regularmente seu e-mail para atualizações
                            sobre o status do seu cadastro.
                        </li>
                    </ul>
                </div>

                <div className="card-actions mt-8">
                    <a href="/login" className="btn btn-primary btn-block">
                        Voltar para login
                    </a>
                </div>
            </div>
        </GuestLayout>
    );
}
