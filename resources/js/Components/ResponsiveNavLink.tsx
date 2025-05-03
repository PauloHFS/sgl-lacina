import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active?: boolean }) {
    return (
        <Link
            {...props}
            className={`block w-full px-3 py-2 ${
                active
                    ? 'bg-primary/10 text-primary font-medium'
                    : 'text-base-content hover:bg-base-200'
            } transition duration-150 ease-in-out ${className}`}
        >
            {children}
        </Link>
    );
}
