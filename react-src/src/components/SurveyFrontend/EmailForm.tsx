//react-src/src/components/SurveyFrontend/EmailForm.tsx
import React, { useState } from 'react';

interface Props {
    surveyId: string;
    answers: Record<string, string>;
    onClose: () => void;
    onMessage: (msg: string) => void;
    ajaxUrl: string;
    nonce: string;
}

const EmailForm: React.FC<Props> = ({ surveyId, answers, onClose, onMessage, ajaxUrl, nonce }) => {
    const [email, setEmail] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            onMessage('Please enter a valid email');
            return;
        }

        setLoading(true);

        const formData = new FormData();
        formData.append('action', 'survey_sphere_submit_attempt');
        formData.append('_wpnonce', nonce);
        formData.append('email', email);
        formData.append('survey_id', surveyId);

        Object.entries(answers).forEach(([qId, optId]) => {
            formData.append(`answers[${qId}]`, optId);
        });

        try {
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                onMessage('Result saved!');
                setTimeout(onClose, 1500);
            } else {
                onMessage(data.data?.message || 'Error saving result');
            }
        } catch (err) {
            onMessage('Network error');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="save-email-form">
            <form onSubmit={handleSubmit}>
                <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Your email"
                    required
                    disabled={loading}
                />
                <button type="submit" className="button button-primary" disabled={loading}>
                    {loading ? 'Saving...' : 'Save'}
                </button>
                <button type="button" className="button" onClick={onClose} disabled={loading}>
                    Cancel
                </button>
            </form>
        </div>
    );
};

export default EmailForm;