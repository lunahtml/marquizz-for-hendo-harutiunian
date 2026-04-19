//react-src/src/components/SurveyFrontend/OptionsList.tsx
import React from 'react';
import type { Option } from '../../types';

interface Props {
    options: Option[];
    name: string;
    selectedId: string | null;
    onChange: (optionId: string) => void;
}

const OptionsList: React.FC<Props> = ({ options, name, selectedId, onChange }) => {
    return (
        <div className="options-list">
            {options.map(option => (
                <label key={option.id} className="option-item">
                    <input
                        type="radio"
                        name={name}
                        value={option.id}
                        checked={selectedId === option.id}
                        onChange={() => onChange(option.id)}
                    />
                    <span className="option-text">{option.text}</span>
                </label>
            ))}
        </div>
    );
};

export default OptionsList;