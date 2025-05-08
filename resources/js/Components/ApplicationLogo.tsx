import { ImgHTMLAttributes } from 'react';

interface ApplicationLogoProps extends ImgHTMLAttributes<HTMLImageElement> {
    lightSrc?: string;
    darkSrc?: string;
    lightAlt?: string;
    darkAlt?: string;
}

export default function ApplicationLogo({
    lightSrc = '/images/logo.png',
    darkSrc = '/images/logo_transp_branco.png',
    lightAlt = 'LACINA Logo Light',
    darkAlt = 'LACINA Logo Dark',
    className = '',
    ...props
}: ApplicationLogoProps) {
    return (
        <>
            <img
                src={lightSrc}
                alt={lightAlt}
                className={`mr-2 block h-10 dark:hidden ${className}`}
                {...props}
            />
            <img
                src={darkSrc}
                alt={darkAlt}
                className={`mr-2 hidden h-10 dark:block ${className}`}
                {...props}
            />
        </>
    );
}
