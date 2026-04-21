//react-src/src/pages/AnalyticsDashboard.tsx
import React, { useState, useEffect } from 'react';
import Table from '../components/common/Table';

interface SummaryStats {
    total_attempts: number;
    total_respondents: number;
    total_surveys: number;
    attempts_by_day: Array<{ date: string; count: number }>;
    top_surveys: Array<{ name: string; attempts: number }>;
}

interface Attempt {
    id: string;
    created_at: string;
    survey: { id: string; name: string };
    respondent: { email: string; name: string };
}

const AnalyticsDashboard: React.FC = () => {
    console.log('=== AnalyticsDashboard component function called ===');
    const [summary, setSummary] = useState<SummaryStats | null>(null);
    const [attempts, setAttempts] = useState<Attempt[]>([]);
    const [loading, setLoading] = useState(true);
    const [selectedAttempt, setSelectedAttempt] = useState<any>(null);

    useEffect(() => {
        const loadAll = async () => {
            try {
                await Promise.all([loadSummary(), loadAttempts()]);
            } catch (error) {
                console.error('Failed to load analytics:', error);
            } finally {
                setLoading(false);
            }
        };
        loadAll();
    }, []);

    const loadSummary = async () => {
        const res = await fetch('/chess/wp-json/survey-sphere/v1/analytics/summary', {
            credentials: 'same-origin'
        });
        const data = await res.json();
        console.log('Summary:', data);
        setSummary(data);
    };

    const loadAttempts = async () => {
        const res = await fetch('/chess/wp-json/survey-sphere/v1/analytics/attempts?per_page=50', {
            credentials: 'same-origin'
        });
        const data = await res.json();
        console.log('Attempts:', data);
        setAttempts(data.attempts || []);
    };
    console.log('summary state:', summary);
    console.log('attempts state:', attempts);
    const viewAttemptDetails = async (id: string) => {
        console.log('Viewing attempt:', id);
        try {
            const res = await fetch(`/chess/wp-json/survey-sphere/v1/analytics/attempt/${id}`, {
                credentials: 'same-origin'
            });
            console.log('Response status:', res.status);
            const data = await res.json();
            console.log('Attempt details:', data);
            setSelectedAttempt(data);
        } catch (error) {
            console.error('Failed to load attempt details:', error);
        }
    };

    const columns = [
        { key: 'created_at' as keyof Attempt, header: 'Дата', render: (a: Attempt) => new Date(a.created_at).toLocaleString() },
        { key: 'survey' as keyof Attempt, header: 'Опрос', render: (a: Attempt) => a.survey.name },
        { key: 'respondent' as keyof Attempt, header: 'Респондент', render: (a: Attempt) => a.respondent.email || a.respondent.name },
    ];

    if (loading) return <div className="ss-loading">Загрузка аналитики...</div>;

    return (
        <div className="ss-page analytics-dashboard">
            <h1>Аналитика</h1>

            {/* Сводка */}
            <div className="stats-cards">
                <div className="stat-card">
                    <span className="stat-value">{summary?.total_attempts || 0}</span>
                    <span className="stat-label">Всего попыток</span>
                </div>
                <div className="stat-card">
                    <span className="stat-value">{summary?.total_respondents || 0}</span>
                    <span className="stat-label">Уникальных респондентов</span>
                </div>
                <div className="stat-card">
                    <span className="stat-value">{summary?.total_surveys || 0}</span>
                    <span className="stat-label">Активных опросов</span>
                </div>
            </div>

            {/* Таблица попыток */}
            <div className="attempts-section">
                <h2>Последние попытки</h2>
                <Table
                    columns={columns}
                    data={attempts}
                    actions={(a) => (
                        <button className="button button-small" onClick={() => viewAttemptDetails(a.id)}>
                            Детали
                        </button>
                    )}
                />
            </div>

            {/* Модальное окно с деталями */}
            {selectedAttempt && (
                <div className="modal-overlay" onClick={() => setSelectedAttempt(null)}>
                    <div className="modal-content" onClick={e => e.stopPropagation()}>
                        <h3>Детали попытки</h3>
                        <p><strong>Опрос:</strong> {selectedAttempt.survey?.name}</p>
                        <p><strong>Респондент:</strong> {selectedAttempt.respondent?.email}</p>
                        <p><strong>Общий балл:</strong> {selectedAttempt.overall_score}%</p>

                        <h4>Ответы:</h4>
                        <table className="ss-table">
                            <thead>
                                <tr><th>Вопрос</th><th>Ответ</th><th>Балл</th><th>Сегмент</th></tr>
                            </thead>
                            <tbody>
                                {selectedAttempt.answers?.map((a: any, i: number) => (
                                    <tr key={i}>
                                        <td>{a.question}</td>
                                        <td>{a.answer}</td>
                                        <td>{a.score}</td>
                                        <td>{a.segment || '—'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <button className="button" onClick={() => setSelectedAttempt(null)}>Закрыть</button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default AnalyticsDashboard;