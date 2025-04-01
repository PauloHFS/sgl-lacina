import GuestLayout from '@/Layouts/GuestLayout';
import { Head } from '@inertiajs/react';

// TODO: FIX DARK MODE
export default function WaitingApproval() {
    return (
        <GuestLayout>
            <Head title="Aguardando Aprovação" />
            <div className="flex flex-col items-center justify-center bg-gray-100">
                <div className="rounded-lg bg-white px-6 py-8 shadow-md">
                    <div className="text-center">
                        <svg
                            className="mx-auto h-16 w-16 text-yellow-500"
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

                        <h2 className="mt-6 text-2xl font-bold text-gray-900">
                            Cadastro em Análise
                        </h2>

                        <p className="mt-2 text-gray-600">
                            Seu cadastro foi recebido e está aguardando
                            aprovação do docente responsável.
                        </p>

                        <div className="mt-6 border-t border-gray-200 pt-6">
                            <h3 className="text-lg font-medium text-gray-900">
                                O que acontece agora?
                            </h3>
                            <ul className="mt-4 space-y-2 text-left text-gray-600">
                                <li className="flex items-start">
                                    <span
                                        className="h-5 w-5 flex-shrink-0 text-blue-600"
                                        aria-hidden="true"
                                    >
                                        •
                                    </span>
                                    <span className="ml-2">
                                        O docente que você indicou receberá uma
                                        notificação sobre seu cadastro.
                                    </span>
                                </li>
                                <li className="flex items-start">
                                    <span
                                        className="h-5 w-5 flex-shrink-0 text-blue-600"
                                        aria-hidden="true"
                                    >
                                        •
                                    </span>
                                    <span className="ml-2">
                                        Após a análise e aprovação, você
                                        receberá um e-mail com as instruções de
                                        acesso.
                                    </span>
                                </li>
                                <li className="flex items-start">
                                    <span
                                        className="h-5 w-5 flex-shrink-0 text-blue-600"
                                        aria-hidden="true"
                                    >
                                        •
                                    </span>
                                    <span className="ml-2">
                                        Caso haja qualquer necessidade de ajuste
                                        nos dados, você será notificado.
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div className="mt-6 border-t border-gray-200 pt-6">
                            <h3 className="text-lg font-medium text-gray-900">
                                O que você precisa fazer?
                            </h3>
                            <ul className="mt-4 space-y-2 text-left text-gray-600">
                                <li className="flex items-start">
                                    <span
                                        className="h-5 w-5 flex-shrink-0 text-blue-600"
                                        aria-hidden="true"
                                    >
                                        •
                                    </span>
                                    <span className="ml-2">
                                        Verificar seu email no sistema pelo link
                                        recebido. (Verifique também a caixa de
                                        spam)
                                    </span>
                                </li>
                                <li className="flex items-start">
                                    <span
                                        className="h-5 w-5 flex-shrink-0 text-blue-600"
                                        aria-hidden="true"
                                    >
                                        •
                                    </span>
                                    <span className="ml-2">
                                        Verificar regularmente seu e-mail para
                                        atualizações sobre o status do seu
                                        cadastro.
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div className="mt-6">
                            <a
                                href="/login"
                                className="font-medium text-blue-600 hover:text-blue-800"
                            >
                                Voltar para login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
