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
            <input id="drawer-toggle" type="checkbox" className="drawer-toggle" />
            
            <div className="drawer-content flex flex-col">
                {/* Navbar */}
                <div className="navbar bg-base-100 shadow-sm lg:hidden">
                    <div className="navbar-start">
                        <label htmlFor="drawer-toggle" className="btn btn-square btn-ghost">
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
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
                            <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
                                {user.foto_url ? (
                                    <div className="w-10 rounded-full">
                                        <img src={`/storage/${user.foto_url}`} alt={user.name} />
                                    </div>
                                ) : (
                                    <div className="avatar placeholder">
                                        <div className="bg-neutral text-neutral-content w-10 rounded-full">
                                            <span className="text-sm font-medium">
                                                {user.name.charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                )}
                            </div>
                            <ul tabIndex={0} className="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow-lg border border-base-300">
                                <li className="menu-title px-4 py-2">
                                    <div>
                                        <div className="font-semibold text-base-content">{user.name}</div>
                                        <div className="text-sm text-base-content/70">{user.email}</div>
                                    </div>
                                </li>
                                <div className="divider my-1"></div>
                                <li>
                                    <Link href={route('profile.edit')} className="flex items-center gap-3">
                                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Perfil
                                    </Link>
                                </li>
                                <li>
                                    <Link href={route('logout')} method="post" as="button" className="flex items-center gap-3 text-error hover:bg-error/10">
                                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
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
                    <header className="bg-base-100 shadow-sm border-b border-base-200">
                        <div className="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
                            {header}
                        </div>
                    </header>
                )}

                {/* Main content */}
                <main className="flex-1 bg-base-200">
                    <div className="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>

            {/* Sidebar */}
            <div className="drawer-side">
                <label htmlFor="drawer-toggle" aria-label="close sidebar" className="drawer-overlay"></label>
                <aside className="bg-base-100 min-h-full w-80 flex flex-col shadow-xl border-r border-base-200">
                    {/* Logo Section */}
                    <div className="p-6 border-b border-base-200">
                        <Link href="/" className="flex items-center gap-3">
                            <ApplicationLogo className="h-10 w-auto" />
                            <div>
                                <h1 className="text-xl font-bold text-base-content">LACINA</h1>
                                <p className="text-sm text-base-content/70">Sistema de RH</p>
                            </div>
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
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6l-3-3-3 3V5z" />
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
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    Projetos
                                </NavLink>
                            </li>

                            {!isCoordenador && (
                                <li>
                                    <NavLink
                                        href={route('horarios.meus')}
                                        active={route().current('horarios.meus')}
                                        className={`flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors ${
                                            route().current('horarios.meus')
                                                ? 'bg-primary text-primary-content'
                                                : 'text-base-content hover:bg-base-200'
                                        }`}
                                    >
                                        <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Meus Horários
                                    </NavLink>
                                </li>
                            )}

                            {isCoordenador && (
                                <li>
                                    <NavLink
                                        href={route('colaboradores.index', {
                                            status: 'vinculo_pendente',
                                        })}
                                        active={route().current('colaboradores.*')}
                                        className={`flex items-center gap-3 rounded-lg px-4 py-3 text-base font-medium transition-colors ${
                                            route().current('colaboradores.*')
                                                ? 'bg-primary text-primary-content'
                                                : 'text-base-content hover:bg-base-200'
                                        }`}
                                    >
                                        <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                        </svg>
                                        Colaboradores
                                    </NavLink>
                                </li>
                            )}
                        </ul>
                    </nav>

                    {/* User Section */}
                    <div className="border-t border-base-200 p-4">
                        <div className="flex items-center gap-3 mb-4">
                            {user.foto_url ? (
                                <div className="avatar">
                                    <div className="w-12 rounded-full">
                                        <img src={`/storage/${user.foto_url}`} alt={user.name} />
                                    </div>
                                </div>
                            ) : (
                                <div className="avatar placeholder">
                                    <div className="bg-neutral text-neutral-content w-12 rounded-full">
                                        <span className="text-lg font-semibold">
                                            {user.name.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                            )}
                            <div className="flex-1 min-w-0">
                                <p className="text-sm font-semibold text-base-content truncate">{user.name}</p>
                                <p className="text-xs text-base-content/70 truncate">{user.email}</p>
                                {isCoordenador && (
                                    <div className="badge badge-primary badge-xs mt-1">Coordenador</div>
                                )}
                            </div>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-2">
                            <Link 
                                href={route('profile.edit')} 
                                className="btn btn-ghost btn-sm flex items-center gap-2"
                            >
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Perfil
                            </Link>
                            <Link 
                                href={route('logout')} 
                                method="post" 
                                as="button" 
                                className="btn btn-ghost btn-sm text-error hover:bg-error/10 flex items-center gap-2"
                            >
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
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
