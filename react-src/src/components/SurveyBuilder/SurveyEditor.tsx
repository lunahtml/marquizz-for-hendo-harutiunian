//react-src/src/components/SurveyBuilder/SurveyEditor.tsx
import React, { useState, useEffect } from 'react';
import SegmentsPanel from './SegmentsPanel';
import QuestionsLibrary from './QuestionsLibrary';
import QuestionsCanvas from './QuestionsCanvas';
import type { Segment, Question } from '../../types';

const SurveyEditor: React.FC = () => {
    const surveyId = document.getElementById('survey-sphere-root')?.dataset.surveyId || '';
    const [segments, setSegments] = useState<Segment[]>([]);
    const [surveyQuestions, setSurveyQuestions] = useState<Question[]>([]);

    useEffect(() => {
        loadSegments();
        loadSurveyQuestions();
    }, [surveyId]);

    const loadSegments = async () => {
        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/segments?survey_id=${surveyId}`, {
                credentials: 'same-origin'
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
                credentials: 'same-origin'
            });
            const data = await response.json();
            setSurveyQuestions(data.questions || []);
        } catch (error) {
            console.error('Failed to load survey questions:', error);
        }
    };

    const handleAddQuestion = async (question: Question) => {
        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/surveys/${surveyId}/questions`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ question_id: question.id })
            });

            if (response.ok) {
                const newQuestion = { ...question, segmentId: null };
                setSurveyQuestions([...surveyQuestions, newQuestion]);
            }
        } catch (error) {
            console.error('Failed to attach question:', error);
        }
    };

    const handleDropQuestion = async (questionId: string, segmentId: string | null) => {
        console.log('Dropping question:', questionId, 'to segment:', segmentId);

        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/surveys/${surveyId}/questions/${questionId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ segment_id: segmentId })
            });

            const data = await response.json();
            console.log('Update response:', data);

            if (response.ok) {
                setSurveyQuestions(prev => {
                    const updated = prev.map(q =>
                        q.id === questionId ? { ...q, segmentId } : q
                    );
                    console.log('Updated questions:', updated);
                    return updated;
                });
            }
        } catch (error) {
            console.error('Failed to update question segment:', error);
        }
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

export default SurveyEditor;