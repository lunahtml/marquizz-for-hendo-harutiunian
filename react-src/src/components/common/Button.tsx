import React from 'react';
//react-src/src/components/common/Button.tsx
interface Props extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'primary' | 'secondary' | 'danger' | 'icon';
    size?: 'small' | 'medium' | 'large';
    children: React.ReactNode;
}

const Button: React.FC<Props> = ({
    variant = 'secondary',
    size = 'medium',
    children,
    className = '',
    ...props
}) => {
    return (
        <button
            className={`ss-button ss-button--${variant} ss-button--${size} ${className}`}
            {...props}
        >
            {children}
        </button>
    );
};

export default Button;