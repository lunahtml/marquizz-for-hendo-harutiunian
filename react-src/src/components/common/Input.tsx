import React from 'react';
//react-src/src/components/common/Input.tsx
interface Props extends React.InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
}

const Input: React.FC<Props> = ({ label, error, className = '', ...props }) => {
    return (
        <div className="ss-input-wrapper">
            {label && <label className="ss-input-label">{label}</label>}
            <input className={`ss-input ${error ? 'ss-input--error' : ''} ${className}`} {...props} />
            {error && <span className="ss-input-error">{error}</span>}
        </div>
    );
};

export default Input;