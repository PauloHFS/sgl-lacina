import React from 'react';
import { ShowProps } from '../Show';
import { SocialLinks } from './SocialLinks';

interface ColaboradorHeaderProps {
    colaborador: Pick<
        ShowProps['colaborador'],
        | 'name'
        | 'email'
        | 'foto_url'
        | 'linkedin_url'
        | 'github_url'
        | 'figma_url'
    >;
}

export const ColaboradorHeader: React.FC<ColaboradorHeaderProps> = React.memo(
    ({ colaborador }) => (
        <div className="flex flex-col items-center gap-6 sm:flex-row">
            {colaborador.foto_url ? (
                <div className="avatar">
                    <div className="ring-primary ring-offset-base-100 w-24 rounded-full ring ring-offset-2">
                        <img
                            src={`/storage/${colaborador.foto_url}`}
                            alt={`Foto de ${colaborador.name}`}
                        />
                    </div>
                </div>
            ) : (
                <div className="avatar avatar-placeholder">
                    <div className="bg-neutral text-neutral-content ring-primary ring-offset-base-100 w-24 rounded-full ring ring-offset-2">
                        <span className="text-3xl" aria-hidden="true">
                            {colaborador.name.charAt(0).toUpperCase()}
                        </span>
                    </div>
                </div>
            )}
            <div className="text-center sm:text-left">
                <h2 className="card-title text-2xl">{colaborador.name}</h2>
                <p className="text-base-content/70">{colaborador.email}</p>
                <SocialLinks
                    linkedinUrl={colaborador.linkedin_url}
                    githubUrl={colaborador.github_url}
                    figmaUrl={colaborador.figma_url}
                    colaboradorName={colaborador.name} // Pass the name here
                />
            </div>
        </div>
    ),
);
ColaboradorHeader.displayName = 'ColaboradorHeader';
