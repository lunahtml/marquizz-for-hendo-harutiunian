//react-src/src/components/SurveyFrontend/QuestionSlide.tsx
import React from 'react';
import type { Question } from '../../types';

interface Props {
    question: Question;
    selectedOptionId: string | null;
    onAnswer: (questionId: string, optionId: string) => void;
}

const QuestionSlide: React.FC<Props> = ({ question, selectedOptionId, onAnswer }) => {
    return (
        <div className="question-slide">
            {question.segmentName && (
                <div
                    className="segment-badge"
                    style={{
                        backgroundColor: (question.segmentColor || '#e2e8f0') + '20',
                        color: question.segmentColor || '#64748b',
                        border: `1px solid ${question.segmentColor || '#e2e8f0'}`
                    }}
                >
                    📁 {question.segmentName}
                </div>
            )}

            <div className="question-header">
                <h3>{question.text}</h3>
            </div>

            <div className="options-list">
                {question.options.map(option => (
                    <label key={option.id} className="option-item">
                        <input
                            type="radio"
                            name={`question-${question.id}`}
                            value={option.id}
                            checked={selectedOptionId === option.id}
                            onChange={() => onAnswer(question.id, option.id)}
                        />
                        <span className="option-text">{option.text}</span>
                    </label>
                ))}
            </div>
        </div>
    );
};

export default QuestionSlide;