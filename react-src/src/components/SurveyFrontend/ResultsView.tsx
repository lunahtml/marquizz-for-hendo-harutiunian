//react-src/src/components/SurveyFrontend/ResultsView.tsx
import React, { useState, useEffect } from 'react';
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
    surveyId, questions, answers, segments, chartType, onRestart, ajaxUrl, nonce
}) => {
    const [showEmailForm, setShowEmailForm] = useState(false);
    const [saveMessage, setSaveMessage] = useState('');
    const [recommendations, setRecommendations] = useState<Record<string, any>>({});

    const calculateSegmentScores = () => {
        const scores: Record<string, { total: number; count: number; maxPossible: number }> = {};
        segments.forEach(seg => { scores[seg.id] = { total: 0, count: 0, maxPossible: 0 }; });
        scores['uncategorized'] = { total: 0, count: 0, maxPossible: 0 };

        questions.forEach(question => {
            const answer = answers[question.id];
            if (!answer) return;
            const segmentId = question.segmentId || 'uncategorized';
            if (!scores[segmentId]) return;

            let score = 0, maxScore = 0;
            switch (question.type) {
                case 'radio':
                    const option = question.options.find(opt => opt.id === answer);
                    score = option?.score || 0;
                    maxScore = Math.max(...question.options.map(o => o.score));
                    break;
                case 'checkbox':
                    try {
                        const selectedIds = JSON.parse(answer);
                        score = selectedIds.reduce((sum: number, id: string) => {
                            const opt = question.options.find(o => o.id === id);
                            return sum + (opt?.score || 0);
                        }, 0);
                    } catch { score = 0; }
                    maxScore = question.options.reduce((sum, o) => sum + o.score, 0);
                    break;
                case 'true_false':
                    score = answer === 'true' ? 100 : 0;
                    maxScore = 100;
                    break;
                case 'rating':
                    score = parseInt(answer) * 20;
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
    const percentages: Record<string, number> = {};
    let totalPercentage = 0, segmentCount = 0;

    segments.forEach(seg => {
        const data = segmentScores[seg.id];
        percentages[seg.id] = data && data.maxPossible > 0
            ? Math.min(100, Math.round((data.total / data.maxPossible) * 100))
            : 0;
        totalPercentage += percentages[seg.id];
        segmentCount++;
    });

    const overallScore = segmentCount > 0 ? Math.round(totalPercentage / segmentCount) : 0;

    console.log('🔵 [ResultsView] Overall score:', overallScore);
    console.log('🔵 [ResultsView] Segment percentages:', percentages);
    console.log('🔵 [ResultsView] surveyId from props:', surveyId);

    const getLevel = (score: number) => {
        if (score <= 25) return { name: 'Критический', color: '#dc3545', description: 'Требуется немедленное вмешательство.' };
        if (score <= 50) return { name: 'Низкий', color: '#fd7e14', description: 'Присутствуют системные проблемы.' };
        if (score <= 75) return { name: 'Средний', color: '#ffc107', description: 'Базовые процессы настроены.' };
        return { name: 'Высокий', color: '#28a745', description: 'Система работает стабильно.' };
    };

    const level = getLevel(overallScore);

    const loadRecommendations = async () => {
        console.log('🔵🔵🔵 loadRecommendations CALLED');
        console.log('🔵🔵🔵 surveyId:', surveyId);
        console.log('🔵🔵🔵 overallScore:', overallScore);

        try {
            const url = `/chess/wp-json/survey-sphere/v1/recommendations?survey_id=${surveyId}`;
            console.log('🔵🔵🔵 Fetching URL:', url);

            const res = await fetch(url, { credentials: 'same-origin' });
            console.log('🔵🔵🔵 Response status:', res.status);

            const data = await res.json();
            console.log('🔵🔵🔵 Response data:', data);
            console.log('🔵 All recommendations:', data.recommendations);

            const recsBySegment: Record<string, any> = {};

            // 1. Общая рекомендация (segment_id = null) — подходящая по общему баллу
            const overallRec = data.recommendations?.find((r: any) =>
                r.segment_id === null &&
                overallScore >= r.min_score &&
                overallScore <= r.max_score
            );
            if (overallRec) {
                recsBySegment['overall'] = overallRec;
                console.log('🔵 Found overall recommendation:', overallRec);
            } else {
                console.log('🔵 No overall recommendation found');
            }

            // 2. Рекомендации по сегментам
            segments.forEach(seg => {
                const segScore = percentages[seg.id] || 0;
                console.log(`🔵 Checking segment ${seg.name} (id=${seg.id}), score=${segScore}`);

                // Сначала ищем рекомендацию для этого сегмента
                let segRec = data.recommendations?.find((r: any) => {
                    const match = r.segment_id === seg.id &&
                        segScore >= r.min_score &&
                        segScore <= r.max_score;
                    if (match) {
                        console.log(`🔵   MATCH! Found segment-specific rec: ${r.title} (${r.min_score}-${r.max_score})`);
                    }
                    return match;
                });

                // Если нет для сегмента — ищем общую по этому же баллу
                if (!segRec) {
                    console.log(`🔵   No segment-specific rec, looking for overall...`);
                    segRec = data.recommendations?.find((r: any) => {
                        const match = r.segment_id === null &&
                            segScore >= r.min_score &&
                            segScore <= r.max_score;
                        if (match) {
                            console.log(`🔵   MATCH! Found overall rec: ${r.title} (${r.min_score}-${r.max_score})`);
                        }
                        return match;
                    });
                }

                if (segRec) {
                    recsBySegment[seg.id] = segRec;
                } else {
                    console.log(`🔵   No recommendation found for segment ${seg.name}`);
                }
            });

            console.log('🔵 Final recsBySegment:', recsBySegment);
            setRecommendations(recsBySegment);
        } catch (error) {
            console.error('🔴 Failed to load recommendations:', error);
        }
    };

    useEffect(() => {
        console.log('🔵🔵🔵 useEffect triggered');
        loadRecommendations();
    }, [surveyId]); // убрали overallScore из зависимостей

    // Дополнительно: если overallScore изменился после загрузки, перезагружаем
    useEffect(() => {
        if (overallScore > 0) {
            console.log('🔵🔵🔵 overallScore changed, reloading recommendations');
            loadRecommendations();
        }
    }, [overallScore]);

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

            {/* Общая рекомендация */}
            {recommendations.overall && (
                <div className="recommendation-box overall" style={{ borderLeft: `4px solid ${level.color}` }}>
                    <h4>📊 Общая рекомендация: {recommendations.overall.title}</h4>
                    <p>{recommendations.overall.description}</p>
                    {recommendations.overall.action_text && (
                        <a href={recommendations.overall.action_url || '#'} className="button button-primary" target="_blank" rel="noopener noreferrer">
                            {recommendations.overall.action_text}
                        </a>
                    )}
                </div>
            )}

            {/* Рекомендации по сегментам */}
            {segments.map(seg => {
                const rec = recommendations[seg.id];
                if (!rec) return null;

                const isOverall = rec.segment_id === null;

                return (
                    <div key={seg.id} className="recommendation-box segment" style={{ borderLeft: `4px solid ${seg.color}` }}>
                        <h4>
                            <span style={{ backgroundColor: seg.color + '20', padding: '2px 8px', borderRadius: '4px', marginRight: '8px' }}>
                                📁 {seg.name}
                            </span>
                            {rec.title}
                            {isOverall && <span style={{ marginLeft: '8px', fontSize: '12px', color: '#64748b' }}>(общая)</span>}
                        </h4>
                        <p>{rec.description}</p>
                        {rec.action_text && (
                            <a href={rec.action_url || '#'} className="button button-primary" target="_blank" rel="noopener noreferrer">
                                {rec.action_text}
                            </a>
                        )}
                    </div>
                );
            })}

            {!recommendations.overall && Object.keys(recommendations).length === 0 && (
                <p className="no-recommendation" style={{ textAlign: 'center', color: '#64748b', padding: '20px' }}>
                    Нет рекомендаций для вашего результата.
                </p>
            )}

            <ChartView chartType={chartType} segments={segments} percentages={percentages} />

            <div className="results-actions">
                <button className="button restart-btn" onClick={onRestart}>Restart Survey</button>
                <button className="button button-primary save-result-btn" onClick={() => setShowEmailForm(true)}>Save My Result</button>
            </div>

            {showEmailForm && (
                <EmailForm surveyId={surveyId} answers={answers} onClose={() => setShowEmailForm(false)}
                    onMessage={setSaveMessage} ajaxUrl={ajaxUrl} nonce={nonce} />
            )}
            {saveMessage && <p className="save-message">{saveMessage}</p>}
        </div>
    );
};

export default ResultsView;