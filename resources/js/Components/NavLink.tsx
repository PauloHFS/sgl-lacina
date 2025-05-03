import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active: boolean }) {
    return (
        <Link
            {...props}
            className={`link ${active ? 'text-primary font-bold' : 'text-base-content hover:text-primary'} ${className}`}
        >
            {children}
        </Link>
    );
}
