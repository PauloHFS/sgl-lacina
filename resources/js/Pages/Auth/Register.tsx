import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        lab_password: '',
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <div className="bg-base-200 flex min-h-screen flex-col items-center justify-center">
            <Link className="mb-8" href="/">
                <ApplicationLogo className="text-primary h-auto w-50" />
            </Link>
            <Head title="Register" />
            <div className="card bg-base-100 w-full max-w-md shadow-xl">
                <div className="card-body">
                    <h2 className="card-title mb-4">Cadastro</h2>
                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label className="label" htmlFor="lab_password">
                                <span className="label-text">
                                    Senha do Laboratório
                                </span>
                            </label>
                            <input
                                id="lab_password"
                                type="password"
                                name="lab_password"
                                value={data.lab_password}
                                className={`input input-bordered w-full ${errors.lab_password ? 'input-error' : ''}`}
                                autoComplete="off"
                                autoFocus
                                onChange={(e) =>
                                    setData('lab_password', e.target.value)
                                }
                                required
                            />
                            {errors.lab_password && (
                                <span className="text-error mt-1 text-xs">
                                    {errors.lab_password}
                                </span>
                            )}
                        </div>
                        <div>
                            <label className="label" htmlFor="name">
                                <span className="label-text">
                                    Nome Completo
                                </span>
                            </label>
                            <input
                                id="name"
                                name="name"
                                value={data.name}
                                className={`input input-bordered w-full ${errors.name ? 'input-error' : ''}`}
                                autoComplete="name"
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                required
                            />
                            {errors.name && (
                                <span className="text-error mt-1 text-xs">
                                    {errors.name}
                                </span>
                            )}
                        </div>
                        <div>
                            <label className="label" htmlFor="email">
                                <span className="label-text">Email</span>
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                className={`input input-bordered w-full ${errors.email ? 'input-error' : ''}`}
                                autoComplete="username"
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                                required
                            />
                            {errors.email && (
                                <span className="text-error mt-1 text-xs">
                                    {errors.email}
                                </span>
                            )}
                        </div>
                        <div>
                            <label className="label" htmlFor="password">
                                <span className="label-text">Senha</span>
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                className={`input input-bordered w-full ${errors.password ? 'input-error' : ''}`}
                                autoComplete="new-password"
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                required
                            />
                            {errors.password && (
                                <span className="text-error mt-1 text-xs">
                                    {errors.password}
                                </span>
                            )}
                        </div>
                        <div>
                            <label
                                className="label"
                                htmlFor="password_confirmation"
                            >
                                <span className="label-text">
                                    Confirme a Senha
                                </span>
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                className={`input input-bordered w-full ${errors.password_confirmation ? 'input-error' : ''}`}
                                autoComplete="new-password"
                                onChange={(e) =>
                                    setData(
                                        'password_confirmation',
                                        e.target.value,
                                    )
                                }
                                required
                            />
                            {errors.password_confirmation && (
                                <span className="text-error mt-1 text-xs">
                                    {errors.password_confirmation}
                                </span>
                            )}
                        </div>
                        <div className="mt-4 flex items-center justify-between">
                            <Link
                                href={route('login')}
                                className="link link-hover text-sm"
                            >
                                Já possui cadastro?
                            </Link>
                            <button
                                type="submit"
                                className="btn btn-primary"
                                disabled={processing}
                            >
                                Cadastrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
