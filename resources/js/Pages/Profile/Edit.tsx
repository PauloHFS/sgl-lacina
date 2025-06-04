import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Banco, PageProps, User } from '@/types';
import { Head } from '@inertiajs/react';
import CamposExtrasForm from './Partials/CamposExtrasForm';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
    mustVerifyEmail,
    status,
    bancos,
    user,
}: PageProps<{ 
    mustVerifyEmail: boolean; 
    status?: string; 
    bancos: Banco[];
    user: User;
}>) {
    return (
        <AuthenticatedLayout>
            <Head title="Perfil" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="card bg-base-100 shadow">
                        <div className="card-body">
                            <UpdateProfileInformationForm
                                mustVerifyEmail={mustVerifyEmail}
                                status={status}
                                bancos={bancos}
                                user={user}
                                className="max-w-xl"
                            />
                        </div>
                    </div>

                    <div className="card bg-base-100 shadow">
                        <div className="card-body">
                            <CamposExtrasForm className="max-w-xl" />
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
