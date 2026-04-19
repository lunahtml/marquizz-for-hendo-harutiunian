//react-src/src/components/SurveyBuilder/QuestionsCanvas.tsx
import React, { useState } from 'react';
import type { Segment, Question } from '../../types';

interface Props {
    segments: Segment[];
    surveyQuestions: Question[];
    onDropQuestion: (questionId: string, segmentId: string | null) => void;
}

const QuestionsCanvas: React.FC<Props> = ({ segments, surveyQuestions, onDropQuestion }) => {
    const [draggedQuestion, setDraggedQuestion] = useState<string | null>(null);

    const handleDragStart = (questionId: string) => {
        setDraggedQuestion(questionId);
    };

    const handleDrop = (segmentId: string | null) => {
        if (draggedQuestion) {
            onDropQuestion(draggedQuestion, segmentId);
            setDraggedQuestion(null);
        }
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
    };

    // Фильтрация с проверкой segmentId
    const uncategorizedQuestions = surveyQuestions.filter(q => !q.segmentId);
    const questionsBySegment = (segmentId: string) =>
        surveyQuestions.filter(q => q.segmentId === segmentId);

    return (
        <div className="questions-canvas">
            {/* Без сегмента */}
            <div
                className="canvas-zone no-segment"
                onDrop={() => handleDrop(null)}
                onDragOver={handleDragOver}
            >
                <h4>Uncategorized</h4>
                {uncategorizedQuestions.map(question => (
                    <div
                        key={question.id}
                        className="canvas-question"
                        draggable
                        onDragStart={() => handleDragStart(question.id)}
                    >
                        {question.text}
                    </div>
                ))}
            </div>

            {/* Сегменты */}
            {segments.map(segment => (
                <div
                    key={segment.id}
                    className="canvas-zone"
                    style={{ borderLeft: `4px solid ${segment.color}` }}
                    onDrop={() => handleDrop(segment.id)}
                    onDragOver={handleDragOver}
                >
                    <h4>
                        <span>{segment.icon || '📁'}</span> {segment.name}
                    </h4>
                    {questionsBySegment(segment.id).map(question => (
                        <div
                            key={question.id}
                            className="canvas-question"
                            draggable
                            onDragStart={() => handleDragStart(question.id)}
                        >
                            {question.text}
                        </div>
                    ))}
                </div>
            ))}
        </div>
    );
};

export default QuestionsCanvas;