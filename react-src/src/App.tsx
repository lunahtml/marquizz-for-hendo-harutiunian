import React from 'react';
import SurveyEditor from './components/SurveyBuilder/SurveyEditor';
import QuestionsLibraryPage from './pages/QuestionsLibraryPage';
import SurveysList from './pages/SurveysList';

const App: React.FC = () => {
    const path = window.location.pathname + window.location.search;

    // Редактор опроса
    if (path.includes('survey-sphere-edit')) {
        return <SurveyEditor />;
    }

    // Библиотека вопросов
    if (path.includes('survey-sphere-questions')) {
        return <QuestionsLibraryPage />;
    }

    // Список опросов
    return <SurveysList />;
};

export default App;