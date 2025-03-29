import { ClassAttributes, ImgHTMLAttributes } from 'react';
import { JSX } from 'react/jsx-runtime';

export default function ApplicationLogo(
    props: JSX.IntrinsicAttributes &
        ClassAttributes<HTMLImageElement> &
        ImgHTMLAttributes<HTMLImageElement>,
) {
    return <img src="/images/logo.png" alt="Logo" {...props} />;
}
