import { createContext, ReactNode, useContext, useState } from 'react';

interface ToastContextType {
    toast: (message: string, type?: 'success' | 'error' | 'info') => void;
}

export const ToastContext = createContext<ToastContextType | undefined>(
    undefined,
);

type Toast = {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info';
};

export const ToastProvider = ({ children }: { children: ReactNode }) => {
    const [toasts, setToasts] = useState<Toast[]>([]);

    const toast = (message: string, type: Toast['type'] = 'info') => {
        const id = Date.now();
        setToasts((prev) => [...prev, { id, message, type }]);
        setTimeout(() => {
            setToasts((prev) => prev.filter((t) => t.id !== id));
        }, 3000);
    };

    return (
        <ToastContext.Provider value={{ toast }}>
            {children}
            {toasts.length > 0 && (
                <div className="toast toast-top toast-end z-[100]">
                    {toasts.map((t) => (
                        <div key={t.id} className={`alert alert-${t.type}`}>
                            <span>{t.message}</span>
                        </div>
                    ))}
                </div>
            )}
        </ToastContext.Provider>
    );
};

export const useToast = () => {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within a ToastProvider');
    }
    return context;
};
