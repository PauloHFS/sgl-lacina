import React from 'react';

interface InfoItemProps {
    label: string;
    value?: string | null;
    children?: React.ReactNode;
    className?: string;
    isTextArea?: boolean;
}

export const InfoItem: React.FC<InfoItemProps> = React.memo(
    ({ label, value, children, className = '', isTextArea = false }) => (
        <div className={`form-control ${className}`}>
            <label className="label">
                <span className="label-text font-semibold">{label}:</span>
            </label>
            {children ||
                (isTextArea ? (
                    <span className="textarea textarea-bordered flex h-auto min-h-24 py-2 break-words whitespace-normal">
                        {value || '-'}
                    </span>
                ) : (
                    <span className="input input-bordered flex h-auto min-h-10 items-center py-2 break-words whitespace-normal">
                        {value || '-'}
                    </span>
                ))}
        </div>
    ),
);
InfoItem.displayName = 'InfoItem';
