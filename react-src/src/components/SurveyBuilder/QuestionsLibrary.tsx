//react-src/src/components/SurveyBuilder/QuestionsLibrary.tsx
import React, { useState, useEffect } from 'react';
import type { Question } from '../../types';

interface Props {
    surveyId: string;
    onAddQuestion: (question: Question) => void;
}

const QuestionsLibrary: React.FC<Props> = ({ surveyId, onAddQuestion }) => {
    const [questions, setQuestions] = useState<Question[]>([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [isCreating, setIsCreating] = useState(false);
    const [newQuestionText, setNewQuestionText] = useState('');

    useEffect(() => {
        loadQuestions();
    }, [surveyId]);

    const loadQuestions = async () => {
        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/questions?exclude_survey_id=${surveyId}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            console.log('Loaded questions:', data);
            setQuestions(data.questions || []);
        } catch (error) {
            console.error('Failed to load questions:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleCreateQuestion = async () => {
        if (!newQuestionText.trim()) {
            alert('Please enter a question');
            return;
        }

        try {
            const response = await fetch('/chess/wp-json/survey-sphere/v1/questions', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: newQuestionText })
            });

            const data = await response.json();

            if (response.ok) {
                // Добавляем новый вопрос в список
                const newQuestion = data.question;
                setQuestions([...questions, newQuestion]);
                setNewQuestionText('');
                setIsCreating(false);

                // Автоматически добавляем вопрос в опрос
                onAddQuestion(newQuestion);
            } else {
                alert('Failed to create question: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Failed to create question:', error);
            alert('Network error');
        }
    };

    const filteredQuestions = questions.filter(q =>
        q.text.toLowerCase().includes(search.toLowerCase())
    );

    const handleAddClick = (question: Question) => {
        console.log('Adding question:', question);
        onAddQuestion(question);
    };

    if (loading) {
        return <div className="questions-library-loading">Loading questions...</div>;
    }

    return (
        <div className="questions-library">
            <h3>Questions Library</h3>

            <input
                type="text"
                placeholder="Search questions..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="search-input"
            />

            {isCreating ? (
                <div className="create-question-form">
                    <textarea
                        value={newQuestionText}
                        onChange={(e) => setNewQuestionText(e.target.value)}
                        placeholder="Enter your question..."
                        rows={3}
                        className="ss-input"
                        autoFocus
                    />
                    <div className="create-question-actions">
                        <button
                            className="button button-primary"
                            onClick={handleCreateQuestion}
                        >
                            Save
                        </button>
                        <button
                            className="button"
                            onClick={() => {
                                setIsCreating(false);
                                setNewQuestionText('');
                            }}
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            ) : (
                <>
                    {filteredQuestions.length === 0 ? (
                        <p className="no-questions-message">
                            {search ? 'No questions match your search' : 'No available questions found'}
                        </p>
                    ) : (
                        <div className="questions-list">
                            {filteredQuestions.map(question => (
                                <div key={question.id} className="library-question-item">
                                    <span className="question-text">{question.text}</span>
                                    <button
                                        className="button button-small"
                                        onClick={() => handleAddClick(question)}
                                    >
                                        Add
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}

                    <button
                        className="button button-primary create-question-btn"
                        onClick={() => setIsCreating(true)}
                    >
                        + Create New Question
                    </button>
                </>
            )}
        </div>
    );
};

export default QuestionsLibrary;