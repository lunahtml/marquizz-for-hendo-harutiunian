//react-src/src/components/SurveyBuilder/SegmentsPanel.tsx
import React, { useState } from 'react';
import { segmentsApi } from '../../services/api';
import type { Segment } from '../../types';

interface Props {
    surveyId: string;
    segments: Segment[];                    // ← получаем из пропсов
    onSegmentsChange: (segments: Segment[]) => void;  // ← колбэк для обновления
}

const SegmentsPanel: React.FC<Props> = ({ surveyId, segments, onSegmentsChange }) => {
    const [newSegmentName, setNewSegmentName] = useState('');
    const [newSegmentColor, setNewSegmentColor] = useState('#36A2EB');

    const addSegment = async () => {
        if (!newSegmentName.trim()) return;

        try {
            const response = await segmentsApi.create({
                survey_id: surveyId,
                name: newSegmentName,
                color: newSegmentColor,
            }) as any;

            onSegmentsChange([...segments, response.segment]);  // ← обновляем через колбэк
            setNewSegmentName('');
        } catch (error) {
            console.error('Failed to create segment:', error);
        }
    };

    return (
        <div className="segments-panel">
            <h3>Segments</h3>

            <div className="segments-list">
                {segments.map(segment => (
                    <div
                        key={segment.id}
                        className="segment-item"
                        style={{ borderLeftColor: segment.color }}
                    >
                        <span className="segment-icon">{segment.icon || '📁'}</span>
                        <span className="segment-name">{segment.name}</span>
                    </div>
                ))}
            </div>

            <div className="add-segment">
                <input
                    type="text"
                    value={newSegmentName}
                    onChange={(e) => setNewSegmentName(e.target.value)}
                    placeholder="New segment name"
                />
                <input
                    type="color"
                    value={newSegmentColor}
                    onChange={(e) => setNewSegmentColor(e.target.value)}
                />
                <button onClick={addSegment}>Add Segment</button>
            </div>
        </div>
    );
};

export default SegmentsPanel;