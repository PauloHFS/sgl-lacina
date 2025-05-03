import { ButtonHTMLAttributes } from 'react';

export default function DangerButton({
    className = '',
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={`btn btn-error ${disabled ? 'btn-disabled' : ''} ${className}`}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
