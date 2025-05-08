import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="bg-base-200 flex min-h-screen flex-col items-center justify-center">
            <Link className="mb-8" href="/">
                <ApplicationLogo className="text-primary h-auto w-50" />
            </Link>
            <Head title="Log in" />
            <div className="card bg-base-100 w-full max-w-md shadow-xl">
                <div className="card-body">
                    <h2 className="card-title mb-4 justify-center">Entrar</h2>
                    {status && (
                        <div className="alert alert-success mb-4 px-3 py-2 text-sm">
                            {status}
                        </div>
                    )}
                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label htmlFor="email" className="label">
                                <span className="label-text">Email</span>
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className={`input input-bordered w-full ${errors.email ? 'input-error' : ''}`}
                                autoComplete="username"
                                autoFocus
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                            />
                            {errors.email && (
                                <span className="text-error mt-1 text-xs">
                                    {errors.email}
                                </span>
                            )}
                        </div>
                        <div>
                            <label htmlFor="password" className="label">
                                <span className="label-text">Senha</span>
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className={`input input-bordered w-full ${errors.password ? 'input-error' : ''}`}
                                autoComplete="current-password"
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                            />
                            {errors.password && (
                                <span className="text-error mt-1 text-xs">
                                    {errors.password}
                                </span>
                            )}
                        </div>
                        <div className="flex items-center">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                className="checkbox checkbox-primary"
                                checked={data.remember}
                                onChange={(e) =>
                                    setData('remember', e.target.checked)
                                }
                            />
                            <label
                                htmlFor="remember"
                                className="ms-2 text-sm text-gray-600 dark:text-gray-400"
                            >
                                Lembrar-me
                            </label>
                        </div>
                        <div className="flex items-center justify-between">
                            {canResetPassword && (
                                <Link
                                    href={route('password.request')}
                                    className="link link-primary text-sm"
                                >
                                    Esqueceu sua Senha?
                                </Link>
                            )}
                            <button
                                type="submit"
                                className="btn btn-primary ms-4"
                                disabled={processing}
                            >
                                Entrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
