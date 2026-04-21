//react-src/src/components/SurveyBuilder/RecommendationsPanel.tsx
import React, { useState, useEffect } from 'react';
import type { Segment } from '../../types';

interface Recommendation {
    id: string;
    min_score: number;
    max_score: number;
    title: string;
    description: string;
    action_text: string;
    action_url: string;
    segment_id?: string;
}

interface Props {
    surveyId: string;
    segments: Segment[];
}

const RecommendationsPanel: React.FC<Props> = ({ surveyId, segments }) => {
    const [recommendations, setRecommendations] = useState<Recommendation[]>([]);
    const [selectedSegment, setSelectedSegment] = useState<string>('');
    const [loading, setLoading] = useState(true);
    const [editing, setEditing] = useState<Recommendation | null>(null);
    const [isCreating, setIsCreating] = useState(false);

    const [formData, setFormData] = useState({
        title: '',
        description: '',
        min_score: 0,
        max_score: 25,
        action_text: '',
        action_url: '',
        segment_id: ''
    });

    useEffect(() => {
        loadRecommendations();
    }, [surveyId, selectedSegment]);

    const loadRecommendations = async () => {
        setLoading(true);
        try {
            let url = `/chess/wp-json/survey-sphere/v1/recommendations?survey_id=${surveyId}`;
            if (selectedSegment) url += `&segment_id=${selectedSegment}`;

            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            setRecommendations(data.recommendations || []);
        } catch (error) {
            console.error('Failed to load recommendations:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSave = async () => {
        if (!formData.title.trim()) {
            alert('Title is required');
            return;
        }

        const payload: any = {
            survey_id: surveyId,
            title: formData.title,
            description: formData.description,
            min_score: formData.min_score,
            max_score: formData.max_score,
            action_text: formData.action_text,
            action_url: formData.action_url,
        };

        // Отправляем segment_id только если он выбран
        if (formData.segment_id) {
            payload.segment_id = formData.segment_id;
        }

        try {
            const url = editing
                ? `/chess/wp-json/survey-sphere/v1/recommendations/${editing.id}`
                : '/chess/wp-json/survey-sphere/v1/recommendations';

            const res = await fetch(url, {
                method: editing ? 'PUT' : 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                await loadRecommendations();
                resetForm();
            } else {
                const error = await res.json();
                alert('Failed to save: ' + (error.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Failed to save recommendation:', error);
            alert('Network error');
        }
    };
    const handleDelete = async (id: string) => {
        if (!confirm('Delete this recommendation?')) return;

        try {
            await fetch(`/chess/wp-json/survey-sphere/v1/recommendations/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            loadRecommendations();
        } catch (error) {
            console.error('Failed to delete recommendation:', error);
        }
    };

    const handleEdit = (rec: any) => {
        setEditing(rec);
        setFormData({
            title: rec.title || '',
            description: rec.description || '',
            min_score: rec.min_score || 0,
            max_score: rec.max_score || 25,
            action_text: rec.action_text || '',
            action_url: rec.action_url || '',
            segment_id: rec.segment_id || ''
        });
        setIsCreating(true);
    };

    const resetForm = () => {
        setEditing(null);
        setIsCreating(false);
        setFormData({
            title: '',
            description: '',
            min_score: 0,
            max_score: 25,
            action_text: '',
            action_url: '',
            segment_id: ''
        });
    };

    const getLevelColor = (min: number, max: number) => {
        if (max <= 25) return '#dc3545';
        if (max <= 50) return '#fd7e14';
        if (max <= 75) return '#ffc107';
        return '#28a745';
    };

    if (loading) return <div className="ss-loading">Loading recommendations...</div>;

    return (
        <div className="recommendations-panel">
            <div className="panel-header">
                <h3>Recommendations</h3>
                <button className="button button-primary" onClick={() => setIsCreating(true)}>
                    + Add Recommendation
                </button>
            </div>

            <div className="filter-bar">
                <select value={selectedSegment} onChange={(e) => setSelectedSegment(e.target.value)}>
                    <option value="">All Segments</option>
                    {segments.map(seg => (
                        <option key={seg.id} value={seg.id}>{seg.name}</option>
                    ))}
                </select>
            </div>

            {isCreating && (
                <div className="recommendation-form">
                    <h4>{editing ? 'Edit' : 'New'} Recommendation</h4>

                    <label>Segment (optional)</label>
                    <select value={formData.segment_id} onChange={(e) => setFormData({ ...formData, segment_id: e.target.value })}>
                        <option value="">All Segments</option>
                        {segments.map(seg => (
                            <option key={seg.id} value={seg.id}>{seg.name}</option>
                        ))}
                    </select>

                    <label>Score Range</label>
                    <div className="score-range">
                        <input type="number" value={formData.min_score} min="0" max="100"
                            onChange={(e) => setFormData({ ...formData, min_score: parseInt(e.target.value) })} />
                        <span>—</span>
                        <input type="number" value={formData.max_score} min="0" max="100"
                            onChange={(e) => setFormData({ ...formData, max_score: parseInt(e.target.value) })} />
                        <span>%</span>
                    </div>

                    <label>Title</label>
                    <input type="text" value={formData.title} placeholder="e.g., Critical Level"
                        onChange={(e) => setFormData({ ...formData, title: e.target.value })} />

                    <label>Description</label>
                    <textarea value={formData.description} rows={3} placeholder="Detailed recommendation..."
                        onChange={(e) => setFormData({ ...formData, description: e.target.value })} />

                    <label>Action Button Text (optional)</label>
                    <input type="text" value={formData.action_text} placeholder="e.g., Book a consultation"
                        onChange={(e) => setFormData({ ...formData, action_text: e.target.value })} />

                    <label>Action URL (optional)</label>
                    <input type="text" value={formData.action_url} placeholder="https://..."
                        onChange={(e) => setFormData({ ...formData, action_url: e.target.value })} />

                    <div className="form-actions">
                        <button className="button button-primary" onClick={handleSave}>Save</button>
                        <button className="button" onClick={resetForm}>Cancel</button>
                    </div>
                </div>
            )}

            <div className="recommendations-list">
                {recommendations.length === 0 ? (
                    <p className="no-items">No recommendations yet.</p>
                ) : (
                    recommendations.map(rec => (
                        <div key={rec.id} className="recommendation-item" style={{ borderLeftColor: getLevelColor(rec.min_score, rec.max_score) }}>
                            <div className="item-header">
                                <span className="score-badge">{rec.min_score}% – {rec.max_score}%</span>
                                <strong>{rec.title}</strong>
                                <div className="item-actions">
                                    <button className="button button-small" onClick={() => handleEdit(rec)}>Edit</button>
                                    <button className="button button-small button-danger" onClick={() => handleDelete(rec.id)}>Delete</button>
                                </div>
                            </div>
                            {rec.description && <p className="item-description">{rec.description}</p>}
                            {rec.action_text && <span className="item-action">{rec.action_text} →</span>}
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};

export default RecommendationsPanel;