//react-src/src/pages/Surveys/SurveysList.tsx
import React, { useState, useEffect } from 'react';
import Table from '../components/common/Table';
import Button from '../components/common/Button';
import type { Survey } from '../types';

const SurveysList: React.FC = () => {
    const [surveys, setSurveys] = useState<Survey[]>([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');

    useEffect(() => {
        loadSurveys();
    }, []);

    const loadSurveys = async () => {
        try {
            const response = await fetch('/chess/wp-json/survey-sphere/v1/surveys', {
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
        { key: 'name', header: 'Name' },
        {
            key: 'chartType',
            header: 'Chart Type',
            render: (s: Survey) => s.chartType || 'polarArea'
        },
        {
            key: 'isActive',
            header: 'Status',
            render: (s: Survey) => (
                <span className={`ss-badge ${s.isActive ? 'ss-badge--active' : 'ss-badge--inactive'}`}>
                    {s.isActive ? 'Active' : 'Inactive'}
                </span>
            )
        },
        {
            key: 'createdAt',
            header: 'Created',
            render: (s: Survey) => new Date(s.createdAt).toLocaleDateString()
        },
    ];

    const handleEdit = (survey: Survey) => {
        window.location.href = `admin.php?page=survey-sphere-edit&id=${survey.id}`;
    };

    const handleCreate = () => {
        window.location.href = 'admin.php?page=survey-sphere-add';
    };

    if (loading) return <div className="ss-loading">Loading surveys...</div>;

    return (
        <div className="ss-page">
            <div className="ss-page-header">
                <h1>Surveys</h1>
                <Button variant="primary" onClick={handleCreate}>
                    + Create Survey
                </Button>
            </div>

            <div className="ss-page-filters">
                <input
                    type="text"
                    placeholder="Search surveys..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="ss-search-input"
                />
            </div>

            <Table
                columns={columns}
                data={filteredSurveys}
                actions={(survey) => (
                    <div className="ss-actions">
                        <Button variant="secondary" size="small" onClick={() => handleEdit(survey)}>
                            Edit
                        </Button>
                        <Button variant="secondary" size="small">
                            Copy Shortcode
                        </Button>
                    </div>
                )}
                onRowClick={handleEdit}
            />
        </div>
    );
};

export default SurveysList;