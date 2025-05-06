import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="bg-base-200 flex min-h-screen flex-col items-center justify-center">
            <div className="mb-4">
                <Link href="/">
                    <ApplicationLogo className="text-primary h-20 w-20" />
                </Link>
            </div>

            <div className="card bg-base-100 w-full max-w-md shadow-xl">
                <div className="card-body">{children}</div>
            </div>
        </div>
    );
}
