//react-src/src/components/SurveyFrontend/ResultsView.tsx
import React, { useState } from 'react';
import ChartView from './ChartView';
import EmailForm from './EmailForm';
import type { Question, Segment } from '../../types';

interface Props {
    surveyId: string;
    questions: Question[];
    answers: Record<string, string>;
    segments: Segment[];
    chartType: string;
    onRestart: () => void;
}

const ResultsView: React.FC<Props> = ({ surveyId, questions, answers, segments, chartType, onRestart }) => {
    const [showEmailForm, setShowEmailForm] = useState(false);
    const [saveMessage, setSaveMessage] = useState('');

    // Вычисляем баллы по сегментам
    const calculateSegmentScores = () => {
        const scores: Record<string, { total: number; count: number; maxPossible: number }> = {};

        segments.forEach(seg => {
            scores[seg.id] = { total: 0, count: 0, maxPossible: 0 };
        });
        scores['uncategorized'] = { total: 0, count: 0, maxPossible: 0 };

        questions.forEach(question => {
            const answerId = answers[question.id];
            if (answerId) {
                const option = question.options.find(opt => opt.id === answerId);
                if (option) {
                    const segmentId = question.segmentId || 'uncategorized';
                    if (scores[segmentId]) {
                        scores[segmentId].total += option.score;
                        scores[segmentId].count += 1;
                        // Максимально возможный балл для этого вопроса
                        const maxScore = Math.max(...question.options.map(o => o.score));
                        scores[segmentId].maxPossible += maxScore;
                    }
                }
            }
        });

        return scores;
    };

    const segmentScores = calculateSegmentScores();

    // Вычисляем проценты от максимально возможного
    const percentages: Record<string, number> = {};
    let totalPercentage = 0;
    let segmentCount = 0;

    segments.forEach(seg => {
        const data = segmentScores[seg.id];
        if (data && data.maxPossible > 0) {
            const percentage = Math.round((data.total / data.maxPossible) * 100);
            percentages[seg.id] = Math.min(100, percentage);
        } else {
            percentages[seg.id] = 0;
        }
        totalPercentage += percentages[seg.id];
        segmentCount++;
    });

    const overallScore = segmentCount > 0 ? Math.round(totalPercentage / segmentCount) : 0;
    const getLevel = (score: number) => {
        if (score <= 25) return { name: 'Критический', color: '#dc3545', description: 'Требуется немедленное вмешательство.' };
        if (score <= 50) return { name: 'Низкий', color: '#fd7e14', description: 'Присутствуют системные проблемы.' };
        if (score <= 75) return { name: 'Средний', color: '#ffc107', description: 'Базовые процессы настроены.' };
        return { name: 'Высокий', color: '#28a745', description: 'Система работает стабильно.' };
    };

    const level = getLevel(overallScore);

    return (
        <div className="survey-results">
            <div className="results-summary">
                <div className="overall-score">
                    <div className="score-circle" style={{ borderColor: level.color }}>
                        <span className="score-value">{overallScore}%</span>
                    </div>
                    <div className="score-level" style={{ color: level.color }}>{level.name}</div>
                    <p className="score-description">{level.description}</p>
                </div>
            </div>

            <ChartView
                chartType={chartType}
                segments={segments}
                percentages={percentages}
            />

            <div className="results-actions">
                <button className="button restart-btn" onClick={onRestart}>
                    Restart Survey
                </button>
                <button className="button button-primary save-result-btn" onClick={() => setShowEmailForm(true)}>
                    Save My Result
                </button>
            </div>

            {showEmailForm && (
                <EmailForm
                    surveyId={surveyId}
                    answers={answers}
                    onClose={() => setShowEmailForm(false)}
                    onMessage={setSaveMessage}
                />
            )}

            {saveMessage && <p className="save-message">{saveMessage}</p>}
        </div>
    );
};

export default ResultsView;