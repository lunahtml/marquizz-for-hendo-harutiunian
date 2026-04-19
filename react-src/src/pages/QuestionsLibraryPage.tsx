//react-src/src/pages/QuestionsLibraryPage.tsx
import React, { useState, useEffect } from 'react';
import Table from '../components/common/Table';
import Button from '../components/common/Button';
import Modal from '../components/common/Modal';
import Input from '../components/common/Input';
import type { Question, Segment, Survey } from '../types';

interface QuestionWithStats extends Question {
    optionsCount: number;
    usedInSurveys: number;
    surveyNames: string[];
    surveyIds?: number[];  // ← добавить для фильтрации
}

const QuestionsLibraryPage: React.FC = () => {
    const [questions, setQuestions] = useState<QuestionWithStats[]>([]);
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
                fetch('/chess/wp-json/survey-sphere/v1/questions?with_stats=1', { credentials: 'same-origin' }),
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
        if (!confirm('Удалить этот вопрос?')) return;

        try {
            await fetch(`/chess/wp-json/survey-sphere/v1/questions/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            await loadData();
        } catch (error) {
            console.error('Failed to delete question:', error);
            alert('Не удалось удалить вопрос');
        }
    };

    // ФИЛЬТРАЦИЯ
    const filteredQuestions = questions.filter(q => {
        const matchesSearch = q.text.toLowerCase().includes(search.toLowerCase());

        // Фильтр по сегменту
        const matchesSegment = !filterSegment || q.segmentId === filterSegment;

        // Фильтр по опросу
        let matchesSurvey = true;
        if (filterSurvey) {
            const survey = surveys.find(s => s.id === filterSurvey);
            if (survey) {
                matchesSurvey = q.surveyNames?.includes(survey.name) || false;
            }
        }

        return matchesSearch && matchesSegment && matchesSurvey;
    });

    const columns = [
        {
            key: 'text' as keyof QuestionWithStats,
            header: 'Вопрос',
            render: (q: QuestionWithStats) => (
                <div>
                    <strong>{q.text}</strong>
                    {q.segmentName && (
                        <div style={{ fontSize: '12px', color: '#64748b', marginTop: '4px' }}>
                            📁 {q.segmentName}
                        </div>
                    )}
                </div>
            )
        },
        {
            key: 'optionsCount' as keyof QuestionWithStats,
            header: 'Варианты',
            render: (q: QuestionWithStats) => (
                <span className="ss-badge">{q.optionsCount || q.options?.length || 0}</span>
            )
        },
        {
            key: 'usedInSurveys' as keyof QuestionWithStats,
            header: 'Используется в опросах',
            render: (q: QuestionWithStats) => {
                const count = q.usedInSurveys || 0;
                return (
                    <div>
                        <span className="ss-badge">{count}</span>
                        {count > 0 && q.surveyNames && (
                            <div style={{ fontSize: '12px', color: '#64748b', marginTop: '4px' }}>
                                {q.surveyNames.slice(0, 2).join(', ')}
                                {q.surveyNames.length > 2 && ` +${q.surveyNames.length - 2}`}
                            </div>
                        )}
                    </div>
                );
            }
        },
    ];

    if (loading) return <div className="ss-loading">Загрузка вопросов...</div>;

    return (
        <div className="ss-page">
            <div className="ss-page-header">
                <h1>Библиотека вопросов</h1>
                <Button variant="primary" onClick={() => setIsCreateModalOpen(true)}>
                    + Создать вопрос
                </Button>
            </div>

            <div className="ss-page-filters">
                <input
                    type="text"
                    placeholder="Поиск по тексту вопроса..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="ss-search-input"
                />
                <select
                    value={filterSegment}
                    onChange={(e) => setFilterSegment(e.target.value)}
                    className="ss-select"
                >
                    <option value="">Все сегменты</option>
                    {segments.map(s => (
                        <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                </select>
                <select
                    value={filterSurvey}
                    onChange={(e) => setFilterSurvey(e.target.value)}
                    className="ss-select"
                >
                    <option value="">Все опросы</option>
                    {surveys.map(s => (
                        <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                </select>
                <span className="ss-filter-info">
                    Найдено: {filteredQuestions.length}
                </span>
            </div>

            <Table
                columns={columns}
                data={filteredQuestions}
                actions={(question) => (
                    <div className="ss-actions">
                        <Button
                            variant="danger"
                            size="small"
                            onClick={() => handleDeleteQuestion(question.id)}
                        >
                            Удалить
                        </Button>
                    </div>
                )}
            />

            <Modal
                isOpen={isCreateModalOpen}
                onClose={() => setIsCreateModalOpen(false)}
                title="Создать новый вопрос"
                footer={
                    <>
                        <Button variant="secondary" onClick={() => setIsCreateModalOpen(false)}>
                            Отмена
                        </Button>
                        <Button variant="primary" onClick={handleCreateQuestion}>
                            Создать
                        </Button>
                    </>
                }
            >
                <Input
                    label="Текст вопроса"
                    value={newQuestionText}
                    onChange={(e) => setNewQuestionText(e.target.value)}
                    placeholder="Введите текст вопроса..."
                    autoFocus
                />
            </Modal>
        </div>
    );
};

export default QuestionsLibraryPage;