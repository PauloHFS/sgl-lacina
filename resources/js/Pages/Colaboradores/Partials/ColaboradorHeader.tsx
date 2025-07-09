import { Link } from '@inertiajs/react';
import React from 'react';
import { ColaboradorData } from '../Show'; // Import ColaboradorData
import { SocialLinks } from './SocialLinks';

interface ColaboradorHeaderProps {
    colaborador: Pick<
        ColaboradorData,
        | 'id'
        | 'name'
        | 'email'
        | 'curriculo_lattes_url'
        | 'linkedin_url'
        | 'github_url'
        | 'website_url'
        | 'foto_url'
    >;
}

export const ColaboradorHeader: React.FC<ColaboradorHeaderProps> = React.memo(
    ({ colaborador }) => (
        <div className="flex flex-col items-center gap-6 sm:flex-row">
            <div className="avatar">
                <div className="mask mask-squircle ring-primary ring-offset-base-100 h-24 w-24 ring ring-offset-2">
                    <img
                        src={
                            colaborador.foto_url
                                ? colaborador.foto_url
                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(colaborador.name)}&background=random&color=fff`
                        }
                        alt={`Foto de ${colaborador.name}`}
                    />
                </div>
            </div>
            <div className="text-center sm:text-left">
                <h2 className="card-title text-2xl">{colaborador.name}</h2>
                <p className="text-base-content/70">{colaborador.email}</p>
                <SocialLinks
                    curriculoLattesUrl={colaborador.curriculo_lattes_url}
                    linkedinUrl={colaborador.linkedin_url}
                    githubUrl={colaborador.github_url}
                    websiteUrl={colaborador.website_url}
                    colaboradorName={colaborador.name}
                />
                <Link
                    href={`/colaboradores/${colaborador.id}/historico`}
                    className="btn btn-primary mt-2"
                    data-testid="historico-link"
                >
                    Histórico (beta)
                </Link>
            </div>
        </div>
    ),
);
ColaboradorHeader.displayName = 'ColaboradorHeader';
