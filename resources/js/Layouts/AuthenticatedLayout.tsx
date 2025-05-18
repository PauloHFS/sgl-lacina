import ApplicationLogo from '@/Components/ApplicationLogo';
import NavLink from '@/Components/NavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const { user, isCoordenador } = usePage().props.auth;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="bg-base-200 min-h-screen">
            {/* Navbar */}
            <div className="navbar bg-base-100">
                <div className="navbar-start">
                    <div className="mr-4 flex-none">
                        <Link href="/">
                            <ApplicationLogo className="h-9 w-auto" />
                        </Link>
                    </div>
                    <div className="hidden sm:flex">
                        <ul className="menu menu-horizontal px-1">
                            <li>
                                <NavLink
                                    href={route('dashboard')}
                                    active={route().current('dashboard')}
                                    className="btn btn-ghost btn-sm rounded-btn"
                                >
                                    Dashboard
                                </NavLink>
                            </li>
                            <li>
                                <NavLink
                                    href={route('projetos.index')}
                                    active={route().current('projetos.*')}
                                    className="btn btn-ghost btn-sm rounded-btn"
                                >
                                    Projetos
                                </NavLink>
                            </li>
                            {isCoordenador && (
                                <li>
                                    <NavLink
                                        href={route('colaboradores.index', {
                                            status: 'vinculo_pendente',
                                        })}
                                        active={route().current(
                                            'colaboradores.*',
                                        )}
                                        className="btn btn-ghost btn-sm rounded-btn"
                                    >
                                        Colaboradores
                                    </NavLink>
                                </li>
                            )}
                        </ul>
                    </div>
                </div>

                <div className="navbar-end">
                    {/* Desktop user menu */}
                    <div className="hidden sm:block">
                        <div className="dropdown dropdown-end">
                            <div
                                tabIndex={0}
                                role="button"
                                className="btn btn-ghost"
                            >
                                {user.name}
                                {user.foto_url ? (
                                    <div className="avatar">
                                        <div className="w-12 rounded-full">
                                            <img
                                                src={`/storage/${user.foto_url}`}
                                            />
                                        </div>
                                    </div>
                                ) : (
                                    <div className="avatar avatar-placeholder">
                                        <div className="bg-neutral text-neutral-content w-12 rounded-full">
                                            <span className="text">
                                                {user.name
                                                    .charAt(0)
                                                    .toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                )}
                                <svg
                                    className="ml-2 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </div>
                            <ul
                                tabIndex={0}
                                className="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow"
                            >
                                <li>
                                    <Link href={route('profile.edit')}>
                                        Perfil
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href={route('logout')}
                                        method="post"
                                        as="button"
                                    >
                                        Sair
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {/* Mobile menu button */}
                    <div className="sm:hidden">
                        <button
                            onClick={() =>
                                setShowingNavigationDropdown(
                                    !showingNavigationDropdown,
                                )
                            }
                            className="btn btn-square btn-ghost"
                        >
                            <svg
                                className="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    className={
                                        !showingNavigationDropdown
                                            ? 'inline-flex'
                                            : 'hidden'
                                    }
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    className={
                                        showingNavigationDropdown
                                            ? 'inline-flex'
                                            : 'hidden'
                                    }
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {/* Mobile navigation dropdown */}
            <div
                className={`sm:hidden ${showingNavigationDropdown ? 'block' : 'hidden'}`}
            >
                <ul className="menu bg-base-100 rounded-box w-full p-2">
                    <li>
                        <Link
                            href={route('dashboard')}
                            className={
                                route().current('dashboard') ? 'active' : ''
                            }
                        >
                            Dashboard
                        </Link>
                    </li>
                    <li>
                        <Link
                            href={route('colaboradores.index')}
                            className={
                                route().current('colaboradores.*')
                                    ? 'active'
                                    : ''
                            }
                        >
                            Colaboradores
                        </Link>
                    </li>
                    <div className="divider"></div>
                    <div className="px-4">
                        <div className="font-medium">{user.name}</div>
                        <div className="text-sm opacity-75">{user.email}</div>
                    </div>
                    <li>
                        <Link href={route('profile.edit')}>Perfil</Link>
                    </li>
                    <li>
                        <Link href={route('logout')} method="post" as="button">
                            Sair
                        </Link>
                    </li>
                </ul>
            </div>

            {/* Header */}
            {header && (
                <header className="bg-base-100 shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            {/* Main content */}
            <main className="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
}
