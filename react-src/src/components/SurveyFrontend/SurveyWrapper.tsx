//react-src/src/components/SurveyFrontend/SurveyWrapper.tsx
import React, { useState, useEffect } from 'react';
import QuestionSlide from './QuestionSlide';
import ProgressBar from './ProgressBar';
import ResultsView from './ResultsView';
import type { Survey, Question, Segment } from '../../types';

interface Props {
    surveyId: string;
    surveyData: {
        survey: Survey;
        questions: Question[];
        segments: Segment[];
        ajaxUrl: string;
        nonce: string;
    };
}

const SurveyWrapper: React.FC<Props> = ({ surveyId, surveyData }) => {
    const [currentIndex, setCurrentIndex] = useState(0);
    const [answers, setAnswers] = useState<Record<string, string>>({});
    const [isCompleted, setIsCompleted] = useState(false);
    const [showResults, setShowResults] = useState(false);

    const questions = surveyData.questions;
    const totalQuestions = questions.length;

    // Загружаем сохранённые ответы из localStorage
    useEffect(() => {
        try {
            const saved = localStorage.getItem(`survey_${surveyId}`);
            if (saved) {
                const data = JSON.parse(saved);
                if (data.answers) {
                    setAnswers(data.answers);
                    if (data.completed) {
                        setIsCompleted(true);
                        setShowResults(true);
                    }
                }
            }
        } catch (e) {
            console.error('Failed to load from localStorage:', e);
        }
    }, [surveyId]);

    const handleAnswer = (questionId: string, optionId: string) => {
        setAnswers(prev => ({ ...prev, [questionId]: optionId }));
    };

    const handleNext = () => {
        if (currentIndex < totalQuestions - 1) {
            setCurrentIndex(currentIndex + 1);
        }
    };

    const handlePrev = () => {
        if (currentIndex > 0) {
            setCurrentIndex(currentIndex - 1);
        }
    };

    const handleSubmit = () => {
        // Проверяем, все ли вопросы отвечены
        const allAnswered = questions.every(q => answers[q.id]);
        if (!allAnswered) {
            alert('Please answer all questions');
            return;
        }

        // Сохраняем в localStorage
        localStorage.setItem(`survey_${surveyId}`, JSON.stringify({
            answers,
            completed: true,
            timestamp: Date.now()
        }));

        setIsCompleted(true);
        setShowResults(true);
    };

    const handleRestart = () => {
        setAnswers({});
        setCurrentIndex(0);
        setIsCompleted(false);
        setShowResults(false);
        localStorage.removeItem(`survey_${surveyId}`);
    };

    if (showResults) {
        return (
            <ResultsView
                surveyId={surveyId}
                questions={questions}
                answers={answers}
                segments={surveyData.segments}
                chartType={surveyData.survey.chartType}
                onRestart={handleRestart}
                ajaxUrl={surveyData.ajaxUrl}
                nonce={surveyData.nonce}
            />
        );
    }

    const currentQuestion = questions[currentIndex];

    return (
        <div className="survey-sphere-wrapper">
            <div className="survey-header">
                <h2>{surveyData.survey.name}</h2>
                {surveyData.survey.description && (
                    <p className="survey-description">{surveyData.survey.description}</p>
                )}
            </div>

            <ProgressBar current={currentIndex + 1} total={totalQuestions} />

            <QuestionSlide
                question={currentQuestion}
                selectedOptionId={answers[currentQuestion.id] || null}
                onAnswer={handleAnswer}
            />

            <div className="survey-navigation">
                <button
                    className="button prev-btn"
                    onClick={handlePrev}
                    disabled={currentIndex === 0}
                >
                    Previous
                </button>

                {currentIndex === totalQuestions - 1 ? (
                    <button
                        className="button button-primary submit-btn"
                        onClick={handleSubmit}
                    >
                        Show Results
                    </button>
                ) : (
                    <button
                        className="button next-btn"
                        onClick={handleNext}
                        disabled={!answers[currentQuestion.id]}
                    >
                        Next
                    </button>
                )}
            </div>
        </div>
    );
};

export default SurveyWrapper;