import React from 'react';
import SurveyEditor from './components/SurveyBuilder/SurveyEditor';
import QuestionsLibraryPage from './pages/QuestionsLibraryPage';
import SurveysList from './pages/SurveysList';
import SurveyPage from './pages/SurveyPage';

const App: React.FC = () => {
    const path = window.location.pathname + window.location.search;

    if (path.includes('survey-sphere-edit')) {
        return <SurveyEditor />;
    }

    if (path.includes('survey-sphere-questions')) {
        return <QuestionsLibraryPage />;
    }

    // Фронтенд — страница с шорткодом
    if (document.getElementById('survey-sphere-root')?.dataset.surveyId) {
        return <SurveyPage />;
    }

    return <SurveysList />;
};

export default App;