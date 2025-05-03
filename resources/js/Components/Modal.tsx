import { PropsWithChildren } from 'react';

export default function Modal({
    children,
    show = false,
    onClose,
}: PropsWithChildren<{
    show: boolean;
    onClose: CallableFunction;
}>) {
    return (
        <dialog className={`modal ${show ? 'modal-open' : ''}`}>
            <div className="modal-box">{children}</div>
            <div className="modal-backdrop" onClick={() => onClose()}></div>
        </dialog>
    );
}
