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
                                <div className="avatar">
                                    <div className="w-16 rounded">
                                        <svg
                                            className="text-primary h-full w-full"
                                            viewBox="0 0 62 65"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M61.8548 14.6253C61.8778 14.7102 61.8895 14.7978 61.8897 14.8858V28.5615C61.8898 28.737 61.8434 28.9095 61.7554 29.0614C61.6675 29.2132 61.5409 29.3392 61.3887 29.4265L49.9104 36.0351V49.1337C49.9104 49.4902 49.7209 49.8192 49.4118 49.9987L25.4519 63.7916C25.3971 63.8227 25.3372 63.8427 25.2774 63.863C25.2176 63.8844 25.1547 63.8946 25.0926 63.8937C24.8309 63.9099 24.5785 63.8166 24.3993 63.6462L0.8652 41.5868C0.6029 41.3525 0.4796 40.9989 0.5385 40.6387V7.6354C0.4796 7.28558 0.6155 6.93171 0.8652 6.67982L24.5876 0.341524C24.6103 0.334988 24.6344 0.32665 24.6567 0.317922C24.7188 0.300502 24.7825 0.289525 24.8461 0.284796C25.0086 0.264123 25.1604 0.2713 25.3141 0.305841C25.3615 0.318807 25.4375 0.335239 25.5021 0.355021C25.5313 0.36375 25.5513 0.375302 25.5856 0.385126L25.6121 0.389916C25.7431 0.416334 25.8658 0.462348 25.9741 0.525877L43.9184 11.0068L61.8731 14.5954C61.8679 14.5954 61.8627 14.5954 61.8574 14.5953C61.8574 14.5953 61.8627 14.6043 61.8548 14.6253ZM59.893 27.9937V16.6055C59.8992 16.5266 59.9015 16.4471 59.9 16.3675L42.0147 12.8882L59.893 27.9937ZM47.9149 35.4937L47.9123 35.4949V49.0377L26.038 36.1255L26.0341 36.1268L25.9804 36.0918C25.9643 36.0843 25.9531 36.0741 25.9376 36.0651L23.5166 34.6777L47.9149 35.4937ZM2.53857 7.80672V40.0083L24.8762 29.9795L2.53857 7.80672ZM25.0365 1.66251L3.48871 7.5665L25.7229 29.5711L46.0988 12.7673L25.0365 1.66251Z"
                                                fill="currentColor"
                                            />
                                        </svg>
                                    </div>
                                </div>
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
                                            href={route('login')}
                                            className="btn btn-ghost btn-sm"
                                        >
                                            Log in
                                        </Link>
                                        <Link
                                            href={route('register')}
                                            className="btn btn-primary btn-sm"
                                        >
                                            Register
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>

                        {/* Main Card */}
                        <div className="card bg-base-200 w-full shadow-xl">
                            <div className="card-body items-center text-center">
                                <h2 className="card-title mb-2 text-2xl">
                                    SRH LACINA UFCG
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
