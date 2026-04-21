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
    ajaxUrl: string;
    nonce: string;
}
const ResultsView: React.FC<Props> = ({
    surveyId,
    questions,
    answers,
    segments,
    chartType,
    onRestart,
    ajaxUrl,
    nonce
}) => {
    console.log('ResultsView props:', { questions, answers, segments, chartType });
    const [showEmailForm, setShowEmailForm] = useState(false);
    const [saveMessage, setSaveMessage] = useState('');
    // Вычисляем баллы по сегментам
    const calculateSegmentScores = () => {
        const scores: Record<string, { total: number; count: number; maxPossible: number }> = {};

        segments.forEach(seg => {
            scores[seg.id] = { total: 0, count: 0, maxPossible: 0 };
        });
        scores['uncategorized'] = { total: 0, count: 0, maxPossible: 0 };

        // В calculateSegmentScores:
        questions.forEach(question => {
            const answer = answers[question.id];
            if (!answer) return;

            const segmentId = question.segmentId || 'uncategorized';
            if (!scores[segmentId]) return;

            let score = 0;
            let maxScore = 0;

            switch (question.type) {
                case 'radio':
                    const option = question.options.find(opt => opt.id === answer);
                    score = option?.score || 0;
                    maxScore = Math.max(...question.options.map(o => o.score));
                    break;

                case 'checkbox':
                    const selectedIds = Array.isArray(answer) ? answer : [answer as string];
                    score = selectedIds.reduce((sum, id) => {
                        const opt = question.options.find(o => o.id === id);
                        return sum + (opt?.score || 0);
                    }, 0);
                    maxScore = question.options.reduce((sum, o) => sum + o.score, 0);
                    break;

                case 'true_false':
                    score = answer === 'true' ? 100 : 0;
                    maxScore = 100;
                    break;

                case 'rating':
                    score = parseInt(answer as string) * 20;
                    maxScore = 100;
                    break;

                case 'text':
                    return;
            }

            scores[segmentId].total += score;
            scores[segmentId].maxPossible += maxScore;
            scores[segmentId].count += 1;
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
                    ajaxUrl={ajaxUrl}
                    nonce={nonce}
                />
            )}

            {saveMessage && <p className="save-message">{saveMessage}</p>}
        </div>
    );
};

export default ResultsView;