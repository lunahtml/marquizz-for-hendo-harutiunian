//react-src/src/components/SurveyBuilder/SurveyEditor.tsx
import React, { useState, useEffect } from 'react';
import SegmentsPanel from './SegmentsPanel';
import QuestionsLibrary from './QuestionsLibrary';
import QuestionsCanvas from './QuestionsCanvas';
import RecommendationsPanel from './RecommendationsPanel';
import type { Segment, Question } from '../../types';

const SurveyEditor: React.FC = () => {
    const surveyId = document.getElementById('survey-sphere-editor')?.dataset.surveyId || '';
    const [segments, setSegments] = useState<Segment[]>([]);
    const [surveyQuestions, setSurveyQuestions] = useState<Question[]>([]);
    const [chartType, setChartType] = useState<string>('polarArea');
    const [editingQuestion, setEditingQuestion] = useState<Question | null>(null);
    const [activeTab, setActiveTab] = useState<'questions' | 'recommendations'>('questions');

    useEffect(() => {
        loadSurvey();
        loadSegments();
        loadSurveyQuestions();
    }, [surveyId]);

    const loadSurvey = async () => {
        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/surveys/${surveyId}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            setChartType(data.chartType || 'polarArea');
        } catch (error) {
            console.error('Failed to load survey:', error);
        }
    };

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

    const handleChartTypeChange = async (newType: string) => {
        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/surveys/${surveyId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chart_type: newType })
            });
            if (response.ok) {
                setChartType(newType);
            }
        } catch (error) {
            console.error('Failed to update chart type:', error);
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
        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/surveys/${surveyId}/questions/${questionId}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ segment_id: segmentId })
            });

            if (response.ok) {
                setSurveyQuestions(prev =>
                    prev.map(q => q.id === questionId ? { ...q, segmentId } : q)
                );
            }
        } catch (error) {
            console.error('Failed to update question segment:', error);
        }
    };

    const handleSaveQuestion = async (question: Question) => {
        try {
            await fetch(`/chess/wp-json/survey-sphere/v1/questions/${question.id}`, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: question.text, type: question.type })
            });

            for (const opt of question.options) {
                if (opt.id && !opt.id.startsWith('rating-') && opt.id !== 'true' && opt.id !== 'false') {
                    await fetch(`/chess/wp-json/survey-sphere/v1/options/${opt.id}`, {
                        method: 'PUT',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ text: opt.text, score: opt.score })
                    });
                } else if (!opt.id || opt.id.startsWith('rating-') || opt.id === 'true' || opt.id === 'false') {
                    const response = await fetch(`/chess/wp-json/survey-sphere/v1/questions/${question.id}/options`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ text: opt.text, score: opt.score })
                    });
                    const data = await response.json();
                    opt.id = data.option.id;
                }
            }

            setSurveyQuestions(prev => prev.map(q => q.id === question.id ? question : q));
            setEditingQuestion(null);
        } catch (error) {
            console.error('Failed to save question:', error);
        }
    };

    return (
        <div className="survey-sphere-app">
            <div className="survey-settings">
                <label htmlFor="chart-type-select"><strong>Chart Type:</strong></label>
                <select
                    id="chart-type-select"
                    value={chartType}
                    onChange={(e) => handleChartTypeChange(e.target.value)}
                >
                    <option value="polarArea">Polar Area Chart</option>
                    <option value="radar">Radar Chart</option>
                    <option value="doughnut">Doughnut Chart</option>
                    <option value="bar">Bar Chart</option>
                </select>
            </div>

            <div className="editor-tabs">
                <button className={activeTab === 'questions' ? 'active' : ''} onClick={() => setActiveTab('questions')}>
                    Questions
                </button>
                <button className={activeTab === 'recommendations' ? 'active' : ''} onClick={() => setActiveTab('recommendations')}>
                    Recommendations
                </button>
            </div>

            {activeTab === 'questions' ? (
                <div className="editor-layout">
                    <div className="editor-sidebar">
                        <SegmentsPanel
                            surveyId={surveyId}
                            segments={segments}
                            onSegmentsChange={setSegments}
                        />
                    </div>
                    <div className="editor-main">
                        <QuestionsLibrary surveyId={surveyId} onAddQuestion={handleAddQuestion} />
                        <QuestionsCanvas
                            segments={segments}
                            surveyQuestions={surveyQuestions}
                            onDropQuestion={handleDropQuestion}
                            onEditQuestion={setEditingQuestion}
                        />
                    </div>
                </div>
            ) : (
                <RecommendationsPanel surveyId={surveyId} segments={segments} />
            )}

            {editingQuestion && (
                <QuestionEditor
                    question={editingQuestion}
                    onSave={handleSaveQuestion}
                    onClose={() => setEditingQuestion(null)}
                />
            )}
        </div>
    );
};

const QuestionEditor: React.FC<{
    question: Question;
    onSave: (q: Question) => void;
    onClose: () => void;
}> = ({ question, onSave, onClose }) => {
    const [text, setText] = useState(question.text);
    const [options, setOptions] = useState(question.options || []);
    const [localType, setLocalType] = useState<Question['type']>(question.type || 'radio');

    const handleTypeChange = (newType: Question['type']) => {
        setLocalType(newType);
        let newOptions = [...options];
        if (newType === 'true_false') {
            newOptions = [
                { id: 'true', text: 'Да', score: 100 },
                { id: 'false', text: 'Нет', score: 0 }
            ];
        } else if (newType === 'rating') {
            newOptions = [1, 2, 3, 4, 5].map(v => ({
                id: `rating-${v}`,
                text: String(v),
                score: v * 20
            }));
        } else if (newType === 'text') {
            newOptions = [];
        }
        setOptions(newOptions);
    };

    const addOption = () => {
        if (localType === 'radio' || localType === 'checkbox') {
            setOptions([...options, { id: '', text: '', score: 0 }]);
        }
    };

    const updateOption = (index: number, field: string, value: string | number) => {
        const updated = [...options];
        updated[index] = { ...updated[index], [field]: value };
        setOptions(updated);
    };

    const removeOption = (index: number) => {
        setOptions(options.filter((_, i) => i !== index));
    };

    const handleSave = () => {
        onSave({ ...question, text, type: localType, options });
    };

    const showOptionsEditor = localType === 'radio' || localType === 'checkbox';

    return (
        <div className="question-editor-overlay" onClick={onClose}>
            <div className="question-editor-modal" onClick={e => e.stopPropagation()}>
                <h3>Edit Question</h3>

                <div className="question-type-selector">
                    <label>Question Type</label>
                    <select value={localType} onChange={(e) => handleTypeChange(e.target.value as Question['type'])}>
                        <option value="radio">Одиночный выбор</option>
                        <option value="checkbox">Множественный выбор</option>
                        <option value="true_false">Да/Нет</option>
                        <option value="text">Текстовый ответ</option>
                        <option value="rating">Оценка 1-5</option>
                    </select>
                </div>

                <label>Question Text</label>
                <textarea value={text} onChange={e => setText(e.target.value)} />

                {showOptionsEditor && (
                    <>
                        <label>Options</label>
                        <div className="options-list">
                            {options.map((opt, i) => (
                                <div key={i} className="option-row">
                                    <input type="text" value={opt.text} placeholder="Option text"
                                        onChange={e => updateOption(i, 'text', e.target.value)} />
                                    <input type="number" value={opt.score} placeholder="Score" step="0.1"
                                        onChange={e => updateOption(i, 'score', parseFloat(e.target.value))} />
                                    <button onClick={() => removeOption(i)}>✕</button>
                                </div>
                            ))}
                        </div>
                        <button className="add-option-btn" onClick={addOption}>+ Add Option</button>
                    </>
                )}

                <div className="modal-actions">
                    <button className="button button-primary" onClick={handleSave}>Save</button>
                    <button className="button" onClick={onClose}>Cancel</button>
                </div>
            </div>
        </div>
    );
};

export default SurveyEditor;