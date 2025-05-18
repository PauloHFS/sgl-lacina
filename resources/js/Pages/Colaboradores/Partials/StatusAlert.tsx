import React from 'react';
import { StatusIcon } from './StatusIcon';

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
            <div role="alert" className={`alert ${alertClass} shadow-lg`}>
                <div className="flex items-center">
                    {type !== 'outline' && <StatusIcon type={type} />}
                    <div>
                        <h3 className="font-bold">{title}</h3>
                        {message && <div className="text-xs">{message}</div>}
                    </div>
                </div>
                {actions && <div className="flex-none">{actions}</div>}
            </div>
        );
    },
);
StatusAlert.displayName = 'StatusAlert';
