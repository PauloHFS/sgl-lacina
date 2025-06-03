import ApplicationLogo from '@/Components/ApplicationLogo';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';

export default function Welcome({
    auth,
    laravelVersion,
    phpVersion,
}: PageProps<{ laravelVersion: string; phpVersion: string }>) {
    return (
        <>
            <Head title="Welcome" />
            <div className="bg-base-100 flex min-h-screen flex-col">
                <div className="hero flex-1">
                    <div className="hero-content w-full max-w-2xl flex-col lg:max-w-4xl">
                        {/* Navbar */}
                        <div className="navbar bg-base-200 rounded-box mb-8 shadow">
                            <div className="navbar-start" />
                            <div className="navbar-center">
                                <ApplicationLogo />
                            </div>
                            <div className="navbar-end gap-2">
                                {auth.user ? (
                                    <Link
                                        href={route('dashboard')}
                                        className="btn btn-primary btn-sm"
                                    >
                                        Dashboard
                                    </Link>
                                ) : (
                                    <>
                                        <Link
                                            href={route('register')}
                                            className="btn btn-ghost btn-sm"
                                        >
                                            Register
                                        </Link>
                                        <Link
                                            href={route('login')}
                                            className="btn btn-primary btn-sm"
                                        >
                                            Log in
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>

                        {/* Main Card */}
                        <div className="card bg-base-200 w-full shadow-xl">
                            <div className="card-body items-center text-center">
                                <h2 className="card-title mb-2 text-2xl">
                                    SGL LaCInA UFCG
                                </h2>
                                <p>
                                    Sistema de Recursos Humanos para o
                                    Laboratório de Computação Inteligente
                                    Aplicada (LACINA) da Universidade Federal de
                                    Campina Grande
                                </p>
                            </div>
                        </div>

                        {/* Footer */}
                        <footer className="mt-8">
                            <div className="text-base-content/70 text-sm">
                                Laravel v{laravelVersion} (PHP v{phpVersion})
                            </div>
                        </footer>
                    </div>
                </div>
            </div>
        </>
    );
}
