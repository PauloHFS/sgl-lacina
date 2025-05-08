import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
    mustVerifyEmail,
    status,
    bancos,
}: PageProps<{ mustVerifyEmail: boolean; status?: string; bancos: any[] }>) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl leading-tight font-semibold">Profile</h2>
            }
        >
            <Head title="Perfil" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow">
                        <div className="card-body">
                            <UpdateProfileInformationForm
                                mustVerifyEmail={mustVerifyEmail}
                                status={status}
                                bancos={bancos}
                                className="max-w-xl"
                            />
                        </div>
                    </div>

                    <div className="card bg-base-100 shadow">
                        <div className="card-body">
                            <UpdatePasswordForm className="max-w-xl" />
                        </div>
                    </div>

                    <div className="card bg-base-100 shadow">
                        <div className="card-body">
                            <DeleteUserForm className="max-w-xl" />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
