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

    useEffect(() => {
        loadQuestions();
    }, [surveyId]);

    const loadQuestions = async () => {
        try {
            const response = await fetch(`/chess/wp-json/survey-sphere/v1/questions?exclude_survey_id=${surveyId}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            console.log('Loaded questions:', data); // Для отладки
            setQuestions(data.questions || []);
        } catch (error) {
            console.error('Failed to load questions:', error);
        } finally {
            setLoading(false);
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

            <button className="button button-primary create-question-btn">
                + Create New Question
            </button>
        </div>
    );
};

export default QuestionsLibrary;