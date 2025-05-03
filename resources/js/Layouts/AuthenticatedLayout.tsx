import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage().props.auth.user;
    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="bg-base-100 min-h-screen">
            <nav className="navbar border-base-300 bg-base-200 border-b">
                <div className="navbar-start">
                    <Link href="/" className="flex items-center">
                        <ApplicationLogo className="h-9 w-auto" />
                    </Link>
                </div>
                <div className="navbar-center hidden sm:flex">
                    <ul className="menu menu-horizontal px-1">
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
                                    route().current('colaboradores.index')
                                        ? 'active'
                                        : ''
                                }
                            >
                                Colaboradores
                            </Link>
                        </li>
                    </ul>
                </div>
                <div className="navbar-end">
                    {/* Desktop Dropdown */}
                    <div className="hidden sm:flex">
                        <div className="dropdown dropdown-end">
                            <div
                                tabIndex={0}
                                role="button"
                                className="btn btn-ghost btn-sm"
                            >
                                <span>{user.name}</span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    className="ms-2 h-4 w-4"
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
                                className="dropdown-content menu menu-sm rounded-box bg-base-200 z-20 mt-2 w-52 shadow"
                            >
                                <li>
                                    <Link href={route('profile.edit')}>
                                        Perfil
                                    </Link>
                                </li>
                                <li>
                                    <form
                                        method="post"
                                        action={route('logout')}
                                    >
                                        <button
                                            type="submit"
                                            className="w-full text-left"
                                        >
                                            Sair
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    {/* Mobile Hamburger */}
                    <div className="sm:hidden">
                        <button
                            className="btn btn-ghost btn-square"
                            onClick={() =>
                                setShowingNavigationDropdown((prev) => !prev)
                            }
                        >
                            <svg
                                className="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                {showingNavigationDropdown ? (
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                ) : (
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                )}
                            </svg>
                        </button>
                    </div>
                </div>
            </nav>

            {/* Mobile Dropdown */}
            {showingNavigationDropdown && (
                <div className="border-base-300 bg-base-200 border-b sm:hidden">
                    <ul className="menu menu-vertical px-2 py-2">
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
                                    route().current('colaboradores.index')
                                        ? 'active'
                                        : ''
                                }
                            >
                                Colaboradores
                            </Link>
                        </li>
                    </ul>
                    <div className="border-base-300 border-t px-4 py-3">
                        <div className="font-bold">{user.name}</div>
                        <div className="text-base-content/70 text-sm">
                            {user.email}
                        </div>
                        <ul className="menu menu-vertical mt-2">
                            <li>
                                <Link href={route('profile.edit')}>Perfil</Link>
                            </li>
                            <li>
                                <form method="post" action={route('logout')}>
                                    <input
                                        type="hidden"
                                        name="_token"
                                        value={usePage().props.csrf_token}
                                    />
                                    <button
                                        type="submit"
                                        className="w-full text-left"
                                    >
                                        Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            )}

            {header && (
                <header className="bg-base-100 shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
