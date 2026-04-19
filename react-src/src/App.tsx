//react-src/src/App.tsx
import React from 'react';
import SurveyEditor from './components/SurveyBuilder/SurveyEditor';
import QuestionsLibraryPage from './pages/QuestionsLibraryPage';
import SurveysList from './pages/SurveysList';

const App: React.FC = () => {
    // Определяем, какой контейнер присутствует на странице
    const isEditor = document.getElementById('survey-sphere-editor');
    const isQuestionsRoot = document.getElementById('survey-sphere-questions-root');

    console.log('App rendering:', { isEditor: !!isEditor, isQuestionsRoot: !!isQuestionsRoot });

    // Редактор опроса
    if (isEditor) {
        return <SurveyEditor />;
    }

    // Библиотека вопросов
    if (isQuestionsRoot) {
        return <QuestionsLibraryPage />;
    }

    // По умолчанию — список опросов (главная страница плагина)
    return <SurveysList />;
};

export default App;