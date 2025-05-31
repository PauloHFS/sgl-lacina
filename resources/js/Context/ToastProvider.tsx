import {
    createContext,
    ReactNode,
    useContext,
    useEffect,
    useRef,
    useState,
} from 'react';

interface ToastContextType {
    toast: (message: string, type?: 'success' | 'error' | 'info') => void;
    dismissBetaToast: () => void;
    showBetaToast: boolean;
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
    const [showBetaToast, setShowBetaToast] = useState(true);
    const timeoutRefs = useRef<Map<number, NodeJS.Timeout>>(new Map());

    const toast = (message: string, type: Toast['type'] = 'info') => {
        const id = Date.now();
        setToasts((prev) => [...prev, { id, message, type }]);

        const timeoutId = setTimeout(() => {
            setToasts((prev) => prev.filter((t) => t.id !== id));
            timeoutRefs.current.delete(id);
        }, 5000);

        timeoutRefs.current.set(id, timeoutId);
    };

    const dismissToast = (id: number) => {
        const timeoutId = timeoutRefs.current.get(id);
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutRefs.current.delete(id);
        }
        setToasts((prev) => prev.filter((t) => t.id !== id));
    };

    const dismissBetaToast = () => {
        setShowBetaToast(false);
    };

    // Cleanup timeouts on unmount
    useEffect(() => {
        const currentTimeoutRefs = timeoutRefs.current;
        return () => {
            currentTimeoutRefs.forEach((timeoutId) => {
                clearTimeout(timeoutId);
            });
            currentTimeoutRefs.clear();
        };
    }, []);

    const getAlertIcon = (type: Toast['type']) => {
        switch (type) {
            case 'success':
                return (
                    <svg
                        className="h-6 w-6 shrink-0 stroke-current"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                );
            case 'error':
                return (
                    <svg
                        className="h-6 w-6 shrink-0 stroke-current"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                );
            case 'info':
            default:
                return (
                    <svg
                        className="h-6 w-6 shrink-0 stroke-current"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                );
        }
    };

    return (
        <ToastContext.Provider
            value={{ toast, dismissBetaToast, showBetaToast }}
        >
            {children}

            {/* Beta Toast */}
            {showBetaToast && (
                <div className="toast toast-center toast-bottom z-[9999]">
                    <div className="alert alert-warning max-w-md shadow-lg">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-6 w-6 shrink-0 stroke-current"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                        <div className="flex-1">
                            <span>Esta é uma versão beta.</span>
                        </div>
                        <div className="flex gap-2">
                            <div
                                className="tooltip tooltip-top tooltip-primary"
                                data-tip="Esta é uma versão beta. Algumas funcionalidades podem estar incompletas ou apresentar instabilidade. Agradecemos o seu feedback!"
                            >
                                <button className="btn btn-circle btn-outline btn-xs">
                                    ?
                                </button>
                            </div>
                            <button
                                className="btn btn-sm btn-circle btn-ghost"
                                onClick={dismissBetaToast}
                                aria-label="Fechar aviso beta"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Dynamic Toasts */}
            {toasts.length > 0 && (
                <div className="toast toast-top toast-end z-[9998]">
                    {toasts.map((t) => (
                        <div
                            key={t.id}
                            className={`alert alert-${t.type} cursor-pointer shadow-lg transition-all duration-300 hover:shadow-xl`}
                            onClick={() => dismissToast(t.id)}
                        >
                            {getAlertIcon(t.type)}
                            <span>{t.message}</span>
                            <button
                                className="btn btn-circle btn-ghost btn-xs ml-2"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    dismissToast(t.id);
                                }}
                                aria-label="Fechar toast"
                            >
                                ✕
                            </button>
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
