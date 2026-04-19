import React, { useEffect } from 'react';
//react-src/src/components/common/Modal.tsx
interface Props {
    isOpen: boolean;
    onClose: () => void;
    title: string;
    children: React.ReactNode;
    footer?: React.ReactNode;
}

const Modal: React.FC<Props> = ({ isOpen, onClose, title, children, footer }) => {
    useEffect(() => {
        const handleEsc = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
        };
        document.addEventListener('keydown', handleEsc);
        return () => document.removeEventListener('keydown', handleEsc);
    }, [onClose]);

    if (!isOpen) return null;

    return (
        <div className="ss-modal-overlay" onClick={onClose}>
            <div className="ss-modal" onClick={e => e.stopPropagation()}>
                <div className="ss-modal-header">
                    <h3>{title}</h3>
                    <button className="ss-modal-close" onClick={onClose}>&times;</button>
                </div>
                <div className="ss-modal-body">
                    {children}
                </div>
                {footer && <div className="ss-modal-footer">{footer}</div>}
            </div>
        </div>
    );
};

export default Modal;