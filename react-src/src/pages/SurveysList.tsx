//react-src/src/pages/SurveysList.tsx
import React, { useState, useEffect } from 'react';
import Table from '../components/common/Table';
import Button from '../components/common/Button';
import type { Survey } from '../types';

interface SurveyWithStats extends Survey {
    segmentsCount: number;
    questionsCount: number;
}

const SurveysList: React.FC = () => {
    const [surveys, setSurveys] = useState<SurveyWithStats[]>([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');

    useEffect(() => {
        loadSurveys();
    }, []);

    const loadSurveys = async () => {
        try {
            const response = await fetch('/chess/wp-json/survey-sphere/v1/surveys?with_stats=1', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            setSurveys(data.surveys || []);
        } catch (error) {
            console.error('Failed to load surveys:', error);
        } finally {
            setLoading(false);
        }
    };

    const filteredSurveys = surveys.filter(s =>
        s.name.toLowerCase().includes(search.toLowerCase())
    );

    const columns = [
        {
            key: 'name' as keyof SurveyWithStats,
            header: 'Название',
            render: (s: SurveyWithStats) => (
                <div>
                    <strong>{s.name}</strong>
                    {s.description && <br />}
                    {s.description && <small style={{ color: '#64748b' }}>{s.description}</small>}
                </div>
            )
        },
        {
            key: 'chartType' as keyof SurveyWithStats,
            header: 'Тип чарта',
            render: (s: SurveyWithStats) => {
                const chartNames: Record<string, string> = {
                    polarArea: 'Polar Area',
                    radar: 'Radar',
                    doughnut: 'Doughnut',
                    bar: 'Bar'
                };
                return chartNames[s.chartType] || s.chartType;
            }
        },
        {
            key: 'segmentsCount' as keyof SurveyWithStats,
            header: 'Сегменты',
            render: (s: SurveyWithStats) => (
                <span className="ss-badge">{s.segmentsCount || 0}</span>
            )
        },
        {
            key: 'questionsCount' as keyof SurveyWithStats,
            header: 'Вопросы',
            render: (s: SurveyWithStats) => (
                <span className="ss-badge">{s.questionsCount || 0}</span>
            )
        },
        {
            key: 'isActive' as keyof SurveyWithStats,
            header: 'Статус',
            render: (s: SurveyWithStats) => (
                <span className={`ss-badge ${s.isActive ? 'ss-badge--active' : 'ss-badge--inactive'}`}>
                    {s.isActive ? 'Активен' : 'Неактивен'}
                </span>
            )
        },
    ];

    const handleEdit = (survey: SurveyWithStats) => {
        window.location.href = `admin.php?page=survey-sphere-edit&id=${survey.id}`;
    };

    const handleCreate = () => {
        window.location.href = 'admin.php?page=survey-sphere-add';
    };

    const handleCopyShortcode = (survey: SurveyWithStats) => {
        const shortcode = `[survey_sphere id="${survey.id}"]`;
        navigator.clipboard.writeText(shortcode);
        alert('Шорткод скопирован!');
    };

    if (loading) return <div className="ss-loading">Загрузка опросов...</div>;

    return (
        <div className="ss-page">
            <div className="ss-page-header">
                <h1>Опросы</h1>
                <Button variant="primary" onClick={handleCreate}>
                    + Создать опрос
                </Button>
            </div>

            <div className="ss-page-filters">
                <input
                    type="text"
                    placeholder="Поиск по названию..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="ss-search-input"
                />
                <span className="ss-filter-info">
                    Всего опросов: {surveys.length}
                </span>
            </div>

            <Table
                columns={columns}
                data={filteredSurveys}
                actions={(survey) => (
                    <div className="ss-actions">
                        <Button variant="secondary" size="small" onClick={() => handleEdit(survey)}>
                            Редактировать
                        </Button>
                        <Button variant="secondary" size="small" onClick={() => handleCopyShortcode(survey)}>
                            Копировать шорткод
                        </Button>
                    </div>
                )}
                onRowClick={handleEdit}
            />
        </div>
    );
};

export default SurveysList;