import React from 'react';

interface SocialLinksProps {
    linkedinUrl?: string | null;
    githubUrl?: string | null;
    figmaUrl?: string | null;
    colaboradorName: string;
}

export const SocialLinks: React.FC<SocialLinksProps> = React.memo(
    ({ linkedinUrl, githubUrl, figmaUrl, colaboradorName }) => (
        <div className="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
            {linkedinUrl && (
                <a
                    href={linkedinUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-sm btn-outline btn-primary"
                    aria-label={`LinkedIn de ${colaboradorName}`}
                >
                    LinkedIn
                </a>
            )}
            {githubUrl && (
                <a
                    href={githubUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-sm btn-outline btn-neutral"
                    aria-label={`GitHub de ${colaboradorName}`}
                >
                    GitHub
                </a>
            )}
            {figmaUrl && (
                <a
                    href={figmaUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-sm btn-outline btn-accent"
                    aria-label={`Figma de ${colaboradorName}`}
                >
                    Figma
                </a>
            )}
        </div>
    ),
);
SocialLinks.displayName = 'SocialLinks';
