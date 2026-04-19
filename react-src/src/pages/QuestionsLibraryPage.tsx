//react-src/src/pages/Questions/QuestionsLibraryPage.tsx
import React, { useState, useEffect } from 'react';
import Table from '../components/common/Table';
import Button from '../components/common/Button';
import Modal from '../components/common/Modal';
import Input from '../components/common/Input';
import type { Question, Segment, Survey } from '../types';

const QuestionsLibraryPage: React.FC = () => {
    const [questions, setQuestions] = useState<Question[]>([]);
    const [segments, setSegments] = useState<Segment[]>([]);
    const [surveys, setSurveys] = useState<Survey[]>([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [filterSegment, setFilterSegment] = useState<string>('');
    const [filterSurvey, setFilterSurvey] = useState<string>('');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [newQuestionText, setNewQuestionText] = useState('');

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        try {
            const [questionsRes, segmentsRes, surveysRes] = await Promise.all([
                fetch('/chess/wp-json/survey-sphere/v1/questions', { credentials: 'same-origin' }),
                fetch('/chess/wp-json/survey-sphere/v1/segments', { credentials: 'same-origin' }),
                fetch('/chess/wp-json/survey-sphere/v1/surveys', { credentials: 'same-origin' })
            ]);

            const questionsData = await questionsRes.json();
            const segmentsData = await segmentsRes.json();
            const surveysData = await surveysRes.json();

            setQuestions(questionsData.questions || []);
            setSegments(segmentsData.segments || []);
            setSurveys(surveysData.surveys || []);
        } catch (error) {
            console.error('Failed to load data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleCreateQuestion = async () => {
        if (!newQuestionText.trim()) return;

        try {
            const response = await fetch('/chess/wp-json/survey-sphere/v1/questions', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: newQuestionText })
            });

            if (response.ok) {
                await loadData();
                setIsCreateModalOpen(false);
                setNewQuestionText('');
            }
        } catch (error) {
            console.error('Failed to create question:', error);
        }
    };

    const handleDeleteQuestion = async (id: string) => {
        if (!confirm('Delete this question?')) return;

        try {
            await fetch(`/chess/wp-json/survey-sphere/v1/questions/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            await loadData();
        } catch (error) {
            console.error('Failed to delete question:', error);
        }
    };

    const filteredQuestions = questions.filter(q => {
        const matchesSearch = q.text.toLowerCase().includes(search.toLowerCase());
        // TODO: фильтрация по сегменту и опросу когда будет API
        return matchesSearch;
    });

    const columns = [
        { key: 'text', header: 'Question' },
        {
            key: 'options',
            header: 'Options',
            render: (q: Question) => q.options?.length || 0
        },
        {
            key: 'usage',
            header: 'Used in',
            render: () => '-' // TODO: добавить количество опросов
        },
    ];

    if (loading) return <div className="ss-loading">Loading questions...</div>;

    return (
        <div className="ss-page">
            <div className="ss-page-header">
                <h1>Questions Library</h1>
                <Button variant="primary" onClick={() => setIsCreateModalOpen(true)}>
                    + Create Question
                </Button>
            </div>

            <div className="ss-page-filters">
                <input
                    type="text"
                    placeholder="Search questions..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="ss-search-input"
                />
                <select
                    value={filterSegment}
                    onChange={(e) => setFilterSegment(e.target.value)}
                    className="ss-select"
                >
                    <option value="">All Segments</option>
                    {segments.map(s => (
                        <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                </select>
                <select
                    value={filterSurvey}
                    onChange={(e) => setFilterSurvey(e.target.value)}
                    className="ss-select"
                >
                    <option value="">All Surveys</option>
                    {surveys.map(s => (
                        <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                </select>
            </div>

            <Table
                columns={columns}
                data={filteredQuestions}
                actions={(question) => (
                    <div className="ss-actions">
                        <Button variant="secondary" size="small">
                            Edit
                        </Button>
                        <Button
                            variant="danger"
                            size="small"
                            onClick={() => handleDeleteQuestion(question.id)}
                        >
                            Delete
                        </Button>
                    </div>
                )}
            />

            <Modal
                isOpen={isCreateModalOpen}
                onClose={() => setIsCreateModalOpen(false)}
                title="Create New Question"
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setIsCreateModalOpen(false)}>
                            Cancel
                        </Button>
                        <Button variant="primary" onClick={handleCreateQuestion}>
                            Create
                        </Button>
                    </>
                }
            >
                <Input
                    label="Question Text"
                    value={newQuestionText}
                    onChange={(e) => setNewQuestionText(e.target.value)}
                    placeholder="Enter your question..."
                    autoFocus
                />
            </Modal>
        </div>
    );
};

export default QuestionsLibraryPage;