import React from 'react';
import { StatusIcon } from '../Pages/Colaboradores/Partials/StatusIcon';

interface StatusAlertProps {
    type: 'warning' | 'info' | 'success' | 'error' | 'outline';
    title: string;
    message?: string | React.ReactNode;
    actions?: React.ReactNode;
}

export const StatusAlert: React.FC<StatusAlertProps> = React.memo(
    ({ type, title, message, actions }) => {
        const alertClass =
            type === 'outline' ? 'alert-outline' : `alert-${type}`;
        return (
            <div role="alert" className={`alert ${alertClass} p-4 shadow-lg`}>
                <div className="flex flex-col gap-2">
                    <div className="flex items-start">
                        {type !== 'outline' && (
                            <div className="mr-3 flex-shrink-0 pt-1">
                                <StatusIcon type={type} />
                            </div>
                        )}
                        <div className="flex-grow">
                            <h3 className="text-md mb-1 font-bold">{title}</h3>
                            {message && (
                                <div className="text-sm">{message}</div>
                            )}
                        </div>
                    </div>
                    {actions && (
                        <div className="flex justify-end space-x-2">
                            {actions}
                        </div>
                    )}
                </div>
            </div>
        );
    },
);
StatusAlert.displayName = 'StatusAlert';
