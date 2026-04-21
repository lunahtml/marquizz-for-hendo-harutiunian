//react-src/src/components/SurveyFrontend/QuestionSlide.tsx
import React from 'react';
import type { Question } from '../../types';

interface Props {
    question: Question;
    selectedOptionId: string | null;
    selectedOptionIds?: string[];
    onAnswer: (questionId: string, optionId: string | string[]) => void;
}

const QuestionSlide: React.FC<Props> = ({ question, selectedOptionId, selectedOptionIds = [], onAnswer }) => {
    const renderInput = () => {
        switch (question.type) {
            case 'radio':
                return question.options.map(option => (
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
                ));

            case 'checkbox':
                return question.options.map(option => (
                    <label key={option.id} className="option-item">
                        <input
                            type="checkbox"
                            value={option.id}
                            checked={selectedOptionIds.includes(option.id)}
                            onChange={(e) => {
                                const newIds = e.target.checked
                                    ? [...selectedOptionIds, option.id]
                                    : selectedOptionIds.filter(id => id !== option.id);
                                onAnswer(question.id, newIds);
                            }}
                        />
                        <span className="option-text">{option.text}</span>
                    </label>
                ));

            case 'true_false':
                return (
                    <>
                        <label className="option-item">
                            <input
                                type="radio"
                                name={`question-${question.id}`}
                                value="true"
                                checked={selectedOptionId === 'true'}
                                onChange={() => onAnswer(question.id, 'true')}
                            />
                            <span className="option-text">Да</span>
                        </label>
                        <label className="option-item">
                            <input
                                type="radio"
                                name={`question-${question.id}`}
                                value="false"
                                checked={selectedOptionId === 'false'}
                                onChange={() => onAnswer(question.id, 'false')}
                            />
                            <span className="option-text">Нет</span>
                        </label>
                    </>
                );

            case 'text':
                return (
                    <input
                        type="text"
                        className="ss-input"
                        placeholder="Введите ваш ответ..."
                        value={selectedOptionId || ''}
                        onChange={(e) => onAnswer(question.id, e.target.value)}
                    />
                );

            case 'rating':
                return (
                    <div className="rating-options">
                        {[1, 2, 3, 4, 5].map(value => (
                            <label key={value} className="rating-item">
                                <input
                                    type="radio"
                                    name={`question-${question.id}`}
                                    value={String(value)}
                                    checked={selectedOptionId === String(value)}
                                    onChange={() => onAnswer(question.id, String(value))}
                                />
                                <span>{value}</span>
                            </label>
                        ))}
                    </div>
                );

            default:
                return null;
        }
    };

    const segmentBgColor = question.segmentColor ? question.segmentColor + '20' : '#e2e8f020';
    const segmentTextColor = question.segmentColor || '#64748b';

    return (
        <div className="question-slide">
            {question.segmentName && (
                <div className="segment-badge" style={{ backgroundColor: segmentBgColor, color: segmentTextColor }}>
                    📁 {question.segmentName}
                </div>
            )}
            <div className="question-header">
                <h3>{question.text}</h3>
            </div>
            <div className="options-list">
                {renderInput()}
            </div>
        </div>
    );
};

export default QuestionSlide;