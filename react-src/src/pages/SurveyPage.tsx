//react-src/src/pages/SurveyPage.tsx
import React from 'react';
import SurveyWrapper from '../components/SurveyFrontend/SurveyWrapper';

const SurveyPage: React.FC = () => {
    const container = document.getElementById('survey-sphere-root');
    const surveyId = container?.dataset.surveyId || '';
    const surveyData = container?.dataset.survey ? JSON.parse(container.dataset.survey) : null;

    if (!surveyId || !surveyData) {
        return <p>Survey not found</p>;
    }

    return <SurveyWrapper surveyId={surveyId} surveyData={surveyData} />;
};

export default SurveyPage;