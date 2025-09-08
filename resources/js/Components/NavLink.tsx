import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active?: boolean }) {
    return (
        <Link
            {...props}
            className={`rounded-md px-2 py-1 transition-colors duration-200 ${
                active
                    ? 'text-primary font-bold'
                    : 'text-base-content hover:text-primary hover:underline hover:underline-offset-4'
            } ${className}`}
        >
            {children}
        </Link>
    );
}
