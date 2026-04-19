//react-src/src/components/SurveyFrontend/ProgressBar.tsx
import React from 'react';

interface Props {
    current: number;
    total: number;
}

const ProgressBar: React.FC<Props> = ({ current, total }) => {
    const percentage = (current / total) * 100;

    return (
        <div className="survey-progress">
            <div className="progress-bar">
                <div className="progress-fill" style={{ width: `${percentage}%` }} />
            </div>
            <div className="progress-text">
                <span className="current-question">{current}</span> / {total}
            </div>
        </div>
    );
};

export default ProgressBar;