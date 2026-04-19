//react-src/src/App.tsx
import React, { useState, useEffect } from 'react';
import SegmentsPanel from './components/SurveyBuilder/SegmentsPanel';
import QuestionsLibrary from './components/SurveyBuilder/QuestionsLibrary';
import QuestionsCanvas from './components/SurveyBuilder/QuestionsCanvas';
import type { Segment, Question } from './types';

const App: React.FC = () => {
    const surveyId = document.getElementById('survey-sphere-root')?.dataset.surveyId || '';
    const [segments, setSegments] = useState<Segment[]>([]);
    const [surveyQuestions, setSurveyQuestions] = useState<Question[]>([]);

    useEffect(() => {
        loadSegments();
        loadSurveyQuestions();
    }, [surveyId]);

    const loadSegments = async () => {
        try {
            const nonce = (window as any).surveySphereAdmin?.nonce || '';
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/segments?survey_id=${surveyId}`, {
                credentials: 'same-origin',
                headers: { 'X-WP-Nonce': nonce }
            });
            const data = await response.json();
            setSegments(data.segments || []);
        } catch (error) {
            console.error('Failed to load segments:', error);
        }
    };

    const loadSurveyQuestions = async () => {
        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/surveys/${surveyId}/questions`, {
                credentials: 'same-origin',
                headers: { 'X-WP-Nonce': (window as any).wpApiSettings?.nonce || '' }
            });
            const data = await response.json();
            // Добавляем segmentId к вопросам (пока мок)
            const questionsWithSegment = (data.questions || []).map((q: Question) => ({
                ...q,
                segmentId: null
            }));
            setSurveyQuestions(questionsWithSegment);
        } catch (error) {
            console.error('Failed to load survey questions:', error);
        }
    };

    const handleAddQuestion = (question: Question) => {
        const newQuestion: Question = { ...question, segmentId: null };
        setSurveyQuestions([...surveyQuestions, newQuestion]);
        // TODO: Сохранить через API
    };

    const handleDropQuestion = (questionId: string, segmentId: string | null) => {
        setSurveyQuestions(prev =>
            prev.map(q => q.id === questionId ? { ...q, segmentId } : q)
        );
        // TODO: Сохранить через API
    };

    return (
        <div className="survey-sphere-app">
            <div className="editor-layout">
                <div className="editor-sidebar">
                    <SegmentsPanel
                        surveyId={surveyId}
                        segments={segments}
                        onSegmentsChange={setSegments}
                    />
                </div>

                <div className="editor-main">
                    <QuestionsLibrary
                        surveyId={surveyId}
                        onAddQuestion={handleAddQuestion}
                    />

                    <QuestionsCanvas
                        segments={segments}
                        surveyQuestions={surveyQuestions}
                        onDropQuestion={handleDropQuestion}
                    />
                </div>
            </div>
        </div>
    );
};

export default App;