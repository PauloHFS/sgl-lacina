import ApplicationLogo from '@/Components/ApplicationLogo';
import NavLink from '@/Components/NavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode } from 'react';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { user, isCoordenador } = usePage().props.auth;

    return (
        <div className="drawer lg:drawer-open">
            <input
                id="drawer-toggle"
                type="checkbox"
                className="drawer-toggle"
            />

            <div className="drawer-content flex flex-col">
                {/* Navbar */}
                <div className="navbar bg-base-100 shadow-sm lg:hidden">
                    <div className="navbar-start">
                        <label
                            htmlFor="drawer-toggle"
                            className="btn btn-square btn-ghost"
                        >
                            <svg
                                className="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </label>
                    </div>

                    <div className="navbar-center">
                        <Link href="/" className="btn btn-ghost">
                            <ApplicationLogo className="h-8 w-auto" />
                        </Link>
                    </div>

                    <div className="navbar-end">
                        <div className="dropdown dropdown-end">
                            <div
                                tabIndex={0}
                                role="button"
                                className="btn btn-ghost btn-circle avatar"
                            >
                                <div className="avatar">
                                    <div className="mask mask-squircle h-10 w-10">
                                        <img
                                            src={
                                                user.foto_url
                                                    ? user.foto_url
                                                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff`
                                            }
                                            alt={`Foto de ${user.name}`}
                                        />
                                    </div>
                                </div>
                            </div>
                            <ul
                                tabIndex={0}
                                className="dropdown-content menu bg-base-100 rounded-box border-base-300 z-[1] w-52 border p-2 shadow-lg"
                            >
                                <li className="menu-title px-4 py-2">
                                    <div>
                                        <div className="text-base-content font-semibold">
                                            {user.name}
                                        </div>
                                        <div className="text-base-content/70 text-sm">
                                            {user.email}
                                        </div>
                                    </div>
                                </li>
                                <div className="divider my-1"></div>
                                <li>
                                    <Link
                                        href={route('profile.edit')}
                                        className="flex items-center gap-3"
                                    >
                                        <svg
                                            className="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                        Perfil
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href={route('logout')}
                                        method="post"
                                        as="button"
                                        className="text-error hover:bg-error/10 flex items-center gap-3"
                                    >
                                        <svg
                                            className="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                            />
                                        </svg>
                                        Sair
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {/* Header */}
                {header && (
                    <header className="bg-base-100 border-base-200 border-b shadow-sm">
                        <div className="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                            {header}
                        </div>
                    </header>
                )}

                {/* Main content */}
                <main className="bg-base-200 flex-1">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>

            {/* Sidebar */}
            <div className="drawer-side">
                <label
                    htmlFor="drawer-toggle"
                    aria-label="close sidebar"
                    className="drawer-overlay"
                ></label>
                <aside className="bg-base-100 border-base-200 flex min-h-full w-80 flex-col border-r shadow-xl">
                    {/* Logo Section */}
                    <div className="border-base-200 border-b p-6">
                        <Link
                            href="/"
                            className="flex items-center justify-center gap-3"
                        >
                            <ApplicationLogo className="h-13 w-auto" />
                        </Link>
                    </div>

                    {/* Navigation */}
                    <nav className="flex-1 p-4">
                        <ul className="menu menu-vertical w-full gap-2">
                            <li>
                                <NavLink
                                    href={route('dashboard')}
                                    active={route().current('dashboard')}
                                    className={`flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors ${
                                        route().current('dashboard')
                                            ? 'bg-primary text-primary-content'
                                            : 'text-base-content hover:bg-base-200'
                                    }`}
                                >
                                    <svg
                                        className="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                                        />
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6l-3-3-3 3V5z"
                                        />
                                    </svg>
                                    Dashboard
                                </NavLink>
                            </li>

                            <li>
                                <NavLink
                                    href={route('projetos.index')}
                                    active={route().current('projetos.*')}
                                    className={`flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors ${
                                        route().current('projetos.*')
                                            ? 'bg-primary text-primary-content'
                                            : 'text-base-content hover:bg-base-200'
                                    }`}
                                >
                                    <svg
                                        className="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                        />
                                    </svg>
                                    Projetos
                                </NavLink>
                            </li>

                            {!isCoordenador && (
                                <li>
                                    <NavLink
                                        href={route('horarios.index')}
                                        active={route().current(
                                            'horarios.index',
                                        )}
                                        className={`flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors ${
                                            route().current('horarios.index')
                                                ? 'bg-primary text-primary-content'
                                                : 'text-base-content hover:bg-base-200'
                                        }`}
                                    >
                                        <svg
                                            className="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        Meus Horários
                                    </NavLink>
                                </li>
                            )}

                            {isCoordenador && (
                                <>
                                    <li>
                                        <NavLink
                                            href={route('colaboradores.index', {
                                                status: 'vinculo_pendente',
                                            })}
                                            active={route().current(
                                                'colaboradores.*',
                                            )}
                                            className={`flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors ${
                                                route().current(
                                                    'colaboradores.*',
                                                )
                                                    ? 'bg-primary text-primary-content'
                                                    : 'text-base-content hover:bg-base-200'
                                            }`}
                                        >
                                            <svg
                                                className="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                                                />
                                            </svg>
                                            Colaboradores
                                        </NavLink>
                                    </li>
                                    <li>
                                        <NavLink
                                            href={route('salas.index')}
                                            active={route().current('salas.*')}
                                            className={`flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors ${
                                                route().current('salas.*')
                                                    ? 'bg-primary text-primary-content'
                                                    : 'text-base-content hover:bg-base-200'
                                            }`}
                                        >
                                            <svg
                                                className="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0h3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                />
                                            </svg>
                                            Salas
                                        </NavLink>
                                    </li>
                                    <li>
                                        <NavLink
                                            href={route('configuracoes.index')}
                                            active={route().current(
                                                'configuracoes.*',
                                            )}
                                            className={`flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors ${
                                                route().current(
                                                    'configuracoes.*',
                                                )
                                                    ? 'bg-primary text-primary-content'
                                                    : 'text-base-content hover:bg-base-200'
                                            }`}
                                        >
                                            <svg
                                                className="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                                />
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                            </svg>
                                            Configurações
                                        </NavLink>
                                    </li>
                                </>
                            )}
                        </ul>
                    </nav>

                    {/* User Section */}
                    <div className="border-base-200 border-t p-4">
                        <div className="mb-4 flex items-center gap-3">
                            {' '}
                            <div className="avatar">
                                <div className="mask mask-squircle h-12 w-12">
                                    <img
                                        src={
                                            user.foto_url
                                                ? user.foto_url
                                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff`
                                        }
                                        alt={`Foto de ${user.name}`}
                                    />
                                </div>
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="text-base-content truncate text-sm font-semibold">
                                    {user.name}
                                </p>
                                <p className="text-base-content/70 truncate text-xs">
                                    {user.email}
                                </p>
                                {isCoordenador && (
                                    <div className="badge badge-primary badge-xs mt-1">
                                        Coordenador
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            <Link
                                href={route('profile.edit')}
                                className="btn btn-ghost btn-sm flex items-center gap-2"
                            >
                                <svg
                                    className="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                                Perfil
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="btn btn-ghost btn-sm text-error hover:bg-error/10 flex items-center gap-2"
                            >
                                <svg
                                    className="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                    />
                                </svg>
                                Sair
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    );
}
